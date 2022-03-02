<?php

declare(strict_types=1);

namespace Collecthor\FlySystem;

use League\Flysystem\FilesystemAdapter;
use League\Flysystem\PathPrefixer;
use League\Flysystem\UnableToMountFilesystem;
use League\Flysystem\UnableToResolveFilesystemMount;

/**
 * Strips a prefix from all paths given to the adapter.
 * If the path does not start with the prefix an exception is thrown.
 */
class StripPrefixAdapter extends IndirectAdapter implements FilesystemAdapter
{
    private PathPrefixer $pathPrefixer;

    public function __construct(private FilesystemAdapter $base, private string $prefix)
    {
        // This is required because if we don't enforce it directory listings won't work as expected.
        if (!str_ends_with($prefix, '/')) {
            throw new \InvalidArgumentException('Prefix must end with /');
        }
        $this->pathPrefixer = new PathPrefixer($prefix);
    }

    protected function preparePath(string $path): string
    {
        if (!str_starts_with($path, $this->prefix)) {
            throw new \Exception("Invalid path");
        }
        return $this->pathPrefixer->stripPrefix($path);
    }


    public function listContents(string $path, bool $deep): iterable
    {
        foreach (parent::listContents($path, $deep) as $entry) {
            yield $entry->withPath($this->pathPrefixer->prefixPath($entry->path()));
        };
    }

    protected function getAdapter(string $rawPath, string $preparedPath): FilesystemAdapter
    {
        return $this->base;
    }
}
