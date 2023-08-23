<?php

declare(strict_types=1);

namespace Collecthor\FlySystem;

use League\Flysystem\Config;
use League\Flysystem\DirectoryAttributes;
use League\Flysystem\FileAttributes;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\PathPrefixer;
use League\Flysystem\StorageAttributes;
use League\Flysystem\Visibility;

/**
 * This adapter allows you to add virtual directories to a specific path on an underlying adapter.
 * The use case is a scenario where say you want to have a remote directory for each record in a local data store.
 * Querying the list of remote directories can be significantly slower than the local data store; this adapter allows you
 * to override the directory list without affecting other operations.
 */
class VirtualDirectoryListWithMetaDataAdapter extends IndirectAdapter
{
    /**
     * @var array<string, array<mixed>>
     */
    private readonly array $directories;

    private readonly string $path;

    /**
     * @param FilesystemAdapter $adapter
     * @param string $path
     * @param array<string, array<mixed>> $directories
     */
    public function __construct(private readonly FilesystemAdapter $adapter, string $path, array $directories)
    {
        if (!str_ends_with($path, '/')) {
            throw new \InvalidArgumentException('Path must end with /');
        }
        $this->path = rtrim($path, '/');
        $paths = [];
        foreach ($directories as $directoryPath => $meta) {
            if (str_contains($directoryPath, '/')) {
                throw new \InvalidArgumentException('Directories must not contain /');
            }
            $paths[$directoryPath] = $meta;
        }
        $this->directories = $paths;
    }
    protected function getAdapter(string $rawPath, string $preparedPath): FilesystemAdapter
    {
        return $this->adapter;
    }

    public function directoryExists(string $path): bool
    {
        return isset($this->directories["{$this->path}/$path"]) || parent::directoryExists($path);
    }

    public function listContents(string $path, bool $deep): iterable
    {
        $yieldedPaths = [];
        if (rtrim($path, '/') === $this->path
            || $deep && $path === ''
            || $deep && str_starts_with($this->path, rtrim($path, '/') . '/')
        ) {
            yield new DirectoryAttributes($this->path, Visibility::PUBLIC);
            $yieldedPaths[$this->path] = true;
            foreach ($this->directories as $directoryName => $meta) {
                $directoryPath = "{$this->path}/$directoryName";
                $yieldedPaths[$directoryPath] = true;
                yield new DirectoryAttributes($directoryPath, Visibility::PUBLIC, extraMetadata: $meta);
            }
        }

        if ($deep || $path !== $this->path) {
            foreach (parent::listContents($path, $deep) as $storageAttributes) {
                // Skip already yielded paths.
                if ($storageAttributes->isDir() && isset($yieldedPaths[$storageAttributes->path()])) {
                    continue;
                }
                yield $storageAttributes;
            }
        }
    }
}
