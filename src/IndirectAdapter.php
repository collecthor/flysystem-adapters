<?php
declare(strict_types=1);

namespace Collecthor\FlySystem;

use League\Flysystem\Config;
use League\Flysystem\FileAttributes;
use League\Flysystem\FilesystemAdapter;

abstract class IndirectAdapter implements FilesystemAdapter
{

    protected function preparePath(string $path): string
    {
        return $path;
    }

    abstract protected function getAdapter(string $rawPath, string $preparedPath): FilesystemAdapter;


    public function fileExists(string $path): bool
    {
        $preparedPath = $this->preparePath($path);
        return $this->getAdapter($path, $preparedPath)->fileExists($preparedPath);
    }

    public function directoryExists(string $path): bool
    {
        $preparedPath = $this->preparePath($path);
        return $this->getAdapter($path, $preparedPath)->directoryExists($preparedPath);
    }

    public function write(string $path, string $contents, Config $config): void
    {
        $preparedPath = $this->preparePath($path);
        $this->getAdapter($path, $preparedPath)->write($preparedPath, $contents, $config);
    }

    public function writeStream(string $path, $contents, Config $config): void
    {
        $preparedPath = $this->preparePath($path);
        $this->getAdapter($path, $preparedPath)->writeStream($preparedPath, $contents, $config);
    }

    public function read(string $path): string
    {
        $preparedPath = $this->preparePath($path);
        return $this->getAdapter($path, $preparedPath)->read($preparedPath);
    }

    public function readStream(string $path)
    {
        $preparedPath = $this->preparePath($path);
        return $this->getAdapter($path, $preparedPath)->readStream($preparedPath);
    }

    public function delete(string $path): void
    {
        $preparedPath = $this->preparePath($path);
        $this->getAdapter($path, $preparedPath)->delete($preparedPath);
    }

    public function deleteDirectory(string $path): void
    {
        $preparedPath = $this->preparePath($path);
        $this->getAdapter($path, $preparedPath)->deleteDirectory($preparedPath);
    }

    public function createDirectory(string $path, Config $config): void
    {
        $preparedPath = $this->preparePath($path);
        $this->getAdapter($path, $preparedPath)->createDirectory($preparedPath, $config);
    }

    public function setVisibility(string $path, string $visibility): void
    {
        $preparedPath = $this->preparePath($path);
        $this->getAdapter($path, $preparedPath)->setVisibility($preparedPath, $visibility);
    }

    public function visibility(string $path): FileAttributes
    {
        $preparedPath = $this->preparePath($path);
        return $this->getAdapter($path, $preparedPath)->visibility($preparedPath);
    }

    public function mimeType(string $path): FileAttributes
    {
        $preparedPath = $this->preparePath($path);
        return $this->getAdapter($path, $preparedPath)->mimeType($preparedPath);
    }

    public function lastModified(string $path): FileAttributes
    {
        $preparedPath = $this->preparePath($path);
        return $this->getAdapter($path, $preparedPath)->lastModified($preparedPath);
    }

    public function fileSize(string $path): FileAttributes
    {
        $preparedPath = $this->preparePath($path);
        return $this->getAdapter($path, $preparedPath)->fileSize($preparedPath);
    }

    public function listContents(string $path, bool $deep): iterable
    {
        $preparedPath = $this->preparePath($path);
        return $this->getAdapter($path, $preparedPath)->listContents($preparedPath, $deep);
    }

    public function move(string $source, string $destination, Config $config): void
    {
        $preparedSource = $this->preparePath($source);
        $preparedDestination = $this->preparePath($destination);
        $this->getAdapter($source, $preparedSource)->move($preparedSource, $preparedDestination, $config);
    }

    public function copy(string $source, string $destination, Config $config): void
    {
        $preparedSource = $this->preparePath($source);
        $preparedDestination = $this->preparePath($destination);
        $this->getAdapter($source, $preparedSource)->copy($preparedSource, $preparedDestination, $config);
    }
}
