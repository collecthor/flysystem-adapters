<?php

declare(strict_types=1);

namespace Collecthor\FlySystem\Tests;

use Collecthor\FlySystem\EncryptionAdapter;
use Collecthor\FlySystem\IndirectAdapter;
use League\Flysystem\Config;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\InMemory\InMemoryFilesystemAdapter;
use League\Flysystem\UnableToGeneratePublicUrl;
use League\Flysystem\UnableToReadFile;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;

#[CoversClass(EncryptionAdapter::class)]
#[UsesClass(IndirectAdapter::class)]
final class EncryptionAdapterTest extends IndirectAdapterTestCase
{
    private const KEY = '0123456789abcdef0123456789abcdef';

    public static function clearFilesystemAdapterCache(): void
    {
        parent::clearFilesystemAdapterCache();
    }

    protected static function createFilesystemAdapter(): FilesystemAdapter
    {
        return new EncryptionAdapter(new InMemoryFilesystemAdapter(), self::KEY);
    }

    public function testPublicUrlThrows(): void
    {
        $adapter = $this->adapter();
        $adapter->write('path.txt', 'contents', new Config());

        $this->expectException(UnableToGeneratePublicUrl::class);

        $adapter->publicUrl('path.txt', new Config());
    }

    public function testStoredContentsAreEncrypted(): void
    {
        $base = new InMemoryFilesystemAdapter();
        $adapter = new EncryptionAdapter($base, self::KEY);

        $adapter->write('path.txt', 'contents', new Config());

        self::assertNotSame('contents', $base->read('path.txt'));
        self::assertSame('contents', $adapter->read('path.txt'));
        self::assertSame(strlen('contents'), $adapter->fileSize('path.txt')->fileSize());
    }

    public function testRoundTrip(): void
    {
        $adapter = $this->adapter();
        $contents = random_bytes(1024);

        $adapter->write('path.bin', $contents, new Config());

        self::assertSame($contents, $adapter->read('path.bin'));

        $stream = $adapter->readStream('path.bin');
        self::assertIsResource($stream);
        self::assertSame($contents, stream_get_contents($stream));
        fclose($stream);
    }

    public function testRoundTripThroughStreams(): void
    {
        $adapter = $this->adapter();
        $writeStream = fopen('php://temp', 'r+b');
        self::assertIsResource($writeStream);
        fwrite($writeStream, 'contents');
        rewind($writeStream);

        $adapter->writeStream('path.txt', $writeStream, new Config());
        fclose($writeStream);

        self::assertSame('contents', $adapter->read('path.txt'));
    }

    public function testEmptyFileRoundTrip(): void
    {
        $adapter = $this->adapter();

        $adapter->write('empty.txt', '', new Config());

        self::assertSame('', $adapter->read('empty.txt'));
        self::assertSame(0, $adapter->fileSize('empty.txt')->fileSize());
    }

    public function testOverwritingAFileReplacesTheCiphertext(): void
    {
        $adapter = $this->adapter();

        $adapter->write('path.txt', 'first', new Config());
        $adapter->write('path.txt', 'second', new Config());

        self::assertSame('second', $adapter->read('path.txt'));
    }

    public function testAnIncorrectKeyCannotDecryptAFile(): void
    {
        $base = new InMemoryFilesystemAdapter();
        $writer = new EncryptionAdapter($base, self::KEY);
        $writer->write('path.txt', 'contents', new Config());

        $reader = new EncryptionAdapter($base, str_repeat('x', 32));

        $this->expectException(UnableToReadFile::class);

        $reader->read('path.txt');
    }

    public function testMoveAndCopyStayDecryptable(): void
    {
        $adapter = $this->adapter();
        $adapter->write('source.txt', 'contents', new Config());

        $adapter->copy('source.txt', 'copy.txt', new Config());
        $adapter->move('source.txt', 'moved.txt', new Config());

        self::assertSame('contents', $adapter->read('copy.txt'));
        self::assertSame('contents', $adapter->read('moved.txt'));
        self::assertFalse($adapter->fileExists('source.txt'));
    }
}
