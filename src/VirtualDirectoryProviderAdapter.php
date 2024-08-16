<?php

declare(strict_types=1);

namespace Collecthor\FlySystem;

use Collecthor\FlySystem\interfaces\DirectoryProvider;
use League\Flysystem\DirectoryAttributes;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\Visibility;

/**
 * This adapter allows you to add virtual directories to a specific path on an underlying adapter.
 * The use case is a scenario where say you want to have a remote directory for each record in a local data store.
 * Querying the list of remote directories can be significantly slower than the local data store; this adapter allows you
 * to override the directory list without affecting other operations.
 */
final readonly class VirtualDirectoryProviderAdapter extends IndirectAdapter
{
    private string $path;

    public function __construct(
        private FilesystemAdapter $adapter,
        string $path,
        private DirectoryProvider $directories,
    ) {
        if (! str_ends_with($path, '/')) {
            throw new \InvalidArgumentException('Path must end with /');
        }
        $this->path = rtrim($path, '/');
    }

    protected function getAdapter(string $rawPath, string $preparedPath): FilesystemAdapter
    {
        return $this->adapter;
    }

    public function directoryExists(string $path): bool
    {
        return $this->directories->hasTopLevelDirectory($path) || parent::directoryExists($path);
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
            foreach ($this->directories as $directory) {
                $directoryPath = $directory->path();
                $yieldedPaths[$directoryPath] = true;
                yield $directory;
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
