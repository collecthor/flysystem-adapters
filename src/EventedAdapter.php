<?php

declare(strict_types=1);

namespace Collecthor\FlySystem;

use Collecthor\FlySystem\events\CopyEvent;
use Collecthor\FlySystem\events\DeleteDirectoryEvent;
use Collecthor\FlySystem\events\DeleteEvent;
use Collecthor\FlySystem\events\MoveEvent;
use Collecthor\FlySystem\events\WriteEvent;
use Collecthor\FlySystem\events\WriteStreamEvent;
use League\Flysystem\Config;
use League\Flysystem\FileAttributes;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\UnableToGeneratePublicUrl;
use League\Flysystem\UrlGeneration\PublicUrlGenerator;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * Indirect adapter implements a simple pattern that allows extending classes to provide a concrete adapter for any call
 * as well as rewrite the path passed to such calls.
 */
final readonly class EventedAdapter implements FilesystemAdapter, PublicUrlGenerator
{
    public function __construct(
        private FilesystemAdapter $adapter,
        private EventDispatcherInterface $dispatcher,
    ) {}

    public function fileExists(string $path): bool
    {
        return $this->adapter->fileExists($path);
    }

    public function directoryExists(string $path): bool
    {
        return $this->adapter->directoryExists($path);
    }

    public function write(string $path, string $contents, Config $config): void
    {
        $this->dispatcher->dispatch(new WriteEvent($this->adapter, true, $path, $contents, $config));
        $this->adapter->write($path, $contents, $config);
        $this->dispatcher->dispatch(new WriteEvent($this->adapter, false, $path, $contents, $config));
    }

    public function writeStream(string $path, $contents, Config $config): void
    {
        $this->dispatcher->dispatch(new WriteStreamEvent($this->adapter, true, $path, $contents, $config));
        $this->adapter->writeStream($path, $contents, $config);
        $this->dispatcher->dispatch(new WriteStreamEvent($this->adapter, false, $path, $contents, $config));
    }

    public function read(string $path): string
    {
        return $this->adapter->read($path);
    }

    public function readStream(string $path)
    {
        return $this->adapter->readStream($path);
    }

    public function delete(string $path): void
    {
        $this->dispatcher->dispatch(new DeleteEvent($this->adapter, true, $path));
        $this->adapter->delete($path);
        $this->dispatcher->dispatch(new DeleteEvent($this->adapter, false, $path));
    }

    public function deleteDirectory(string $path): void
    {
        $this->dispatcher->dispatch(new DeleteDirectoryEvent($this->adapter, true, $path));
        $this->adapter->deleteDirectory($path);
        $this->dispatcher->dispatch(new DeleteDirectoryEvent($this->adapter, false, $path));
    }

    public function createDirectory(string $path, Config $config): void
    {
        $this->adapter->createDirectory($path, $config);
    }

    public function setVisibility(string $path, string $visibility): void
    {
        $this->adapter->setVisibility($path, $visibility);
    }

    public function visibility(string $path): FileAttributes
    {
        return $this->adapter->visibility($path);
    }

    public function mimeType(string $path): FileAttributes
    {
        return $this->adapter->mimeType($path);
    }

    public function lastModified(string $path): FileAttributes
    {
        return $this->adapter->lastModified($path);
    }

    public function fileSize(string $path): FileAttributes
    {
        return $this->adapter->fileSize($path);
    }

    public function listContents(string $path, bool $deep): iterable
    {
        return $this->adapter->listContents($path, $deep);
    }

    public function move(string $source, string $destination, Config $config): void
    {
        $this->dispatcher->dispatch(new MoveEvent($this->adapter, true, $source, $destination, $config));
        $this->adapter->move($source, $destination, $config);
        $this->dispatcher->dispatch(new MoveEvent($this->adapter, false, $source, $destination, $config));
    }

    public function copy(string $source, string $destination, Config $config): void
    {
        $this->dispatcher->dispatch(new CopyEvent($this->adapter, true, $source, $destination, $config));
        $this->adapter->copy($source, $destination, $config);
        $this->dispatcher->dispatch(new CopyEvent($this->adapter, false, $source, $destination, $config));
    }

    public function publicUrl(string $path, Config $config): string
    {
        if ($this->adapter instanceof PublicUrlGenerator) {
            return $this->adapter->publicUrl($path, $config);
        }
        throw UnableToGeneratePublicUrl::noGeneratorConfigured($path, "Underlying adapter does not support public URL generation");
    }
}
