<?php
declare(strict_types=1);

namespace Collecthor\FlySystem;

use League\Flysystem\FilesystemAdapter;
use League\Flysystem\PathPrefixer;

/**
 * Adds a prefix to all paths given to the adapter.
 */
class AddPrefixAdapter extends IndirectAdapter implements FilesystemAdapter
{
    private PathPrefixer $pathPrefixer;

    public function __construct(private FilesystemAdapter $base, string $prefix)
    {
        // This is required because if we don't enforce it directory listings won't work as expected.
        if (!str_ends_with($prefix, '/')) {
            throw new \InvalidArgumentException('Prefix must end with /');
        }
        $this->pathPrefixer = new PathPrefixer($prefix);
    }

    protected function preparePath(string $path): string
    {
        return $this->pathPrefixer->prefixPath($path);
    }


    public function listContents(string $path, bool $deep): iterable
    {
        foreach($this->base->listContents($this->pathPrefixer->prefixDirectoryPath($path), $deep) as $key => $entry) {
            yield $key => $entry->withPath($this->pathPrefixer->stripPrefix($entry->path()));
        };
    }

    protected function getAdapter(string $rawPath, string $preparedPath): FilesystemAdapter
    {
        return $this->base;
    }
}
