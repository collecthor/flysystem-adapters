<?php

declare(strict_types=1);

namespace Collecthor\FlySystem;

use League\Flysystem\Config;
use League\Flysystem\FileAttributes;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\StorageAttributes;
use League\Flysystem\UnableToGeneratePublicUrl;
use League\Flysystem\UnableToReadFile;
use League\Flysystem\UrlGeneration\PublicUrlGenerator;

/**
 * Encrypts every file with XChaCha20-Poly1305 before it is handed to the underlying adapter and
 * decrypts it again when it is read back.
 *
 * Stored files use the following body format:
 *
 *     [version:1 byte][nonce:24 bytes][ciphertext + tag]
 *
 * The additional authenticated data is path independent, which means files keep their ciphertext
 * when they are moved or copied.
 */
final readonly class EncryptionAdapter extends IndirectAdapter implements FilesystemAdapter, PublicUrlGenerator
{
    public const VERSION = 1;

    /**
     * One byte for the version and 24 bytes for the nonce, preceding the ciphertext.
     */
    private const PREFIX_SIZE = 1 + SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_NPUBBYTES;

    /**
     * Total number of bytes a stored file is larger than its plaintext: the version, the nonce and
     * the Poly1305 tag that is appended to the ciphertext.
     */
    public const HEADER_SIZE = self::PREFIX_SIZE + SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_ABYTES;

    /**
     * Fixed additional authenticated data. It is intentionally not derived from the path so that
     * moving or copying an encrypted file does not invalidate it.
     */
    private const ADDITIONAL_DATA = 'collecthor/flysystem-adapters/encryption';

    public function __construct(
        private FilesystemAdapter $base,
        private string $key,
    ) {
        if (strlen($this->key) !== SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_KEYBYTES) {
            throw new \InvalidArgumentException(
                'The encryption key must be exactly '
                . SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_KEYBYTES . ' bytes long',
            );
        }
    }

    protected function getAdapter(string $rawPath, string $preparedPath): FilesystemAdapter
    {
        return $this->base;
    }

    public function write(string $path, string $contents, Config $config): void
    {
        parent::write($path, $this->encrypt($contents), $config);
    }

    /**
     * @param resource $contents
     */
    public function writeStream(string $path, $contents, Config $config): void
    {
        $stream = self::stringToStream($this->encrypt(self::readAll($contents)));
        try {
            parent::writeStream($path, $stream, $config);
        } finally {
            fclose($stream);
        }
    }

    public function read(string $path): string
    {
        return $this->decrypt($path, parent::read($path));
    }

    /**
     * @return resource
     */
    public function readStream(string $path)
    {
        $ciphertextStream = parent::readStream($path);
        try {
            $ciphertext = self::readAll($ciphertextStream);
        } finally {
            fclose($ciphertextStream);
        }

        return self::stringToStream($this->decrypt($path, $ciphertext));
    }

    /**
     * The underlying adapter reports the size of the ciphertext. Since the header has a fixed size
     * it is subtracted so that the reported size matches the original contents.
     */
    public function fileSize(string $path): FileAttributes
    {
        return self::stripHeader(parent::fileSize($path));
    }

    /**
     * @return iterable<StorageAttributes>
     */
    public function listContents(string $path, bool $deep): iterable
    {
        foreach (parent::listContents($path, $deep) as $entry) {
            yield $entry instanceof FileAttributes ? self::stripHeader($entry) : $entry;
        }
    }

    public function publicUrl(string $path, Config $config): string
    {
        throw UnableToGeneratePublicUrl::noGeneratorConfigured(
            $path,
            'Encrypted files cannot be exposed through a public URL because that would serve ciphertext',
        );
    }

    private function encrypt(string $plaintext): string
    {
        $nonce = random_bytes(SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_NPUBBYTES);

        return chr(self::VERSION) . $nonce . sodium_crypto_aead_xchacha20poly1305_ietf_encrypt(
            $plaintext,
            self::ADDITIONAL_DATA,
            $nonce,
            $this->key,
        );
    }

    private function decrypt(string $path, string $payload): string
    {
        if (strlen($payload) < self::HEADER_SIZE) {
            throw UnableToReadFile::fromLocation($path, 'The stored file is too short to contain an encrypted header');
        }

        $version = ord($payload[0]);
        if ($version !== self::VERSION) {
            throw UnableToReadFile::fromLocation($path, 'Unsupported encryption version ' . $version);
        }

        $nonce = substr($payload, 1, SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_NPUBBYTES);
        $ciphertext = substr($payload, self::PREFIX_SIZE);

        $plaintext = sodium_crypto_aead_xchacha20poly1305_ietf_decrypt(
            $ciphertext,
            self::ADDITIONAL_DATA,
            $nonce,
            $this->key,
        );

        if ($plaintext === false) {
            throw UnableToReadFile::fromLocation($path, 'The stored file could not be decrypted');
        }

        return $plaintext;
    }

    private static function stripHeader(FileAttributes $attributes): FileAttributes
    {
        $size = $attributes->fileSize();
        if ($size === null) {
            return $attributes;
        }

        return new FileAttributes(
            $attributes->path(),
            max(0, $size - self::HEADER_SIZE),
            $attributes->visibility(),
            $attributes->lastModified(),
            $attributes->mimeType(),
            $attributes->extraMetadata(),
        );
    }

    /**
     * @param resource $stream
     */
    private static function readAll($stream): string
    {
        $contents = stream_get_contents($stream);
        if ($contents === false) {
            throw new \RuntimeException('Unable to read the stream contents');
        }

        return $contents;
    }

    /**
     * @return resource
     */
    private static function stringToStream(string $contents)
    {
        $stream = fopen('php://temp', 'r+b');
        if ($stream === false) {
            throw new \RuntimeException('Unable to open a temporary stream');
        }

        fwrite($stream, $contents);
        rewind($stream);

        return $stream;
    }
}
