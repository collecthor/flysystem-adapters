<?php

declare(strict_types=1);

namespace Collecthor\FlySystem;

use League\Flysystem\Config;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\StorageAttributes;

/**
 *
 */
class DirectoryViaPlaceholderFileAdapter extends IndirectAdapter implements FilesystemAdapter
{
    public function __construct(private readonly FilesystemAdapter $base, private readonly string $placeHolderName = '.directory')
    {
    }

    protected function getAdapter(string $rawPath, string $preparedPath): FilesystemAdapter
    {
        return $this->base;
    }

    public function createDirectory(string $path, Config $config): void
    {
        $this->write(rtrim($path, '/') . "/" . $this->placeHolderName, '', $config);
    }

    public function directoryExists(string $path): bool
    {
        return parent::directoryExists($path) || $this->fileExists("$path/$this->placeHolderName");
    }

    /**
     * Iteration will usually support directories even on adapters that don't have that concept.
     * They'll show a prefix for a filename as a directory automatically.
     */
    public function listContents(string $path, bool $deep): iterable
    {
        foreach (parent::listContents($path, $deep) as $entry) {
            if ($entry->isFile() && str_ends_with($entry->path(), $this->placeHolderName)) {
                // This is the placeholder file, we skip it.
            } else {
                yield $entry;
            }
        }
    }
}
