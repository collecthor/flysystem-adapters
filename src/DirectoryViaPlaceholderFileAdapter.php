<?php

declare(strict_types=1);

namespace Collecthor\FlySystem;

use League\Flysystem\Config;
use League\Flysystem\DirectoryAttributes;
use League\Flysystem\FileAttributes;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\UnableToRetrieveMetadata;
use League\Flysystem\Visibility;

/**
 * Implements directory support for any adapter utilizing a placeholder file.
 * On iteration of directory contents the placeholder file is skipped, it is assumed any adapter not supporting directories
 * will still correctly emit a directory node for any path prefix that contains a file.
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

    public function lastModified(string $path): FileAttributes
    {
        if ($this->fileExists("$path/{$this->placeHolderName}")) {
            throw UnableToRetrieveMetadata::mimeType($path, "This adapter does not support directory modification times");
        } else {
            return parent::lastModified($path);
        }
    }

    public function mimeType(string $path): FileAttributes
    {
        if ($this->fileExists("$path/{$this->placeHolderName}")) {
            throw UnableToRetrieveMetadata::mimeType($path, "Directories dont have mime types");
        }
        return parent::mimeType($path);
    }

    public function fileSize(string $path): FileAttributes
    {
        if ($this->fileExists("$path/{$this->placeHolderName}")) {
            throw UnableToRetrieveMetadata::fileSize($path, "Directories don't have file sizes");
        } else {
            return parent::fileSize($path);
        }
    }


    public function directoryExists(string $path): bool
    {
        return parent::directoryExists($path) || $this->fileExists("$path/{$this->placeHolderName}");
    }

    /**
     * Iteration will usually support directories even on adapters that don't have that concept.
     * They'll show a prefix for a filename as a directory automatically.
     */
    public function listContents(string $path, bool $deep): iterable
    {
        $directories = [];
        foreach (parent::listContents($path, $deep) as $entry) {
            if ($entry->isDir()) {
                $directories[$entry->path()] = true;
            }
            // Adapter native directories
            if ($entry->isFile() && str_ends_with($entry->path(), $this->placeHolderName)) {
                if (isset($directories[dirname($entry->path())])) {
                    continue;
                }
                $directoryEntry = new DirectoryAttributes(dirname($entry->path()), Visibility::PRIVATE, $entry->lastModified(), $entry->extraMetadata());
                $directories[$directoryEntry->path()] = true;
                yield $directoryEntry;
            } else {
                yield $entry;
            }
        }
    }
}
