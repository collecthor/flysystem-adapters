<?php

declare(strict_types=1);

namespace Collecthor\FlySystem;

use League\Flysystem\FileAttributes;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\UrlGeneration\PublicUrlGenerator;

/**
 * This adapter will cache the results of any of the metadata retrievers and reuse them.
 * Use this only for adapters that retrieve all metadata for calls to `lastModified` and `mimeType` etc
 */
final class MetadataCachingAdapter implements FilesystemAdapter, PublicUrlGenerator
{
    use IndirectAdapterTrait;
    /**
     * @var array<string, FileAttributes>
     */
    private array $fileAttributeCache = [];
    public function __construct(private readonly FilesystemAdapter $base)
    {
    }

    protected function getAdapter(string $rawPath, string $preparedPath): FilesystemAdapter
    {
        return $this->base;
    }

    public function lastModified(string $path): FileAttributes
    {
        //test
        if (!isset($this->fileAttributeCache[$path])) {
            $this->fileAttributeCache[$path] = $this->getAdapter($path, $this->preparePath($path))->lastModified($path);
        }

        return $this->fileAttributeCache[$path];
    }

    public function visibility(string $path): FileAttributes
    {
        if (!isset($this->fileAttributeCache[$path])) {
            $this->fileAttributeCache[$path] = $this->getAdapter($path, $this->preparePath($path))->visibility($path);
        }

        return $this->fileAttributeCache[$path];
    }

    public function mimeType(string $path): FileAttributes
    {
        if (!isset($this->fileAttributeCache[$path])) {
            $this->fileAttributeCache[$path] = $this->getAdapter($path, $this->preparePath($path))->mimeType($path);
        }

        return $this->fileAttributeCache[$path];
    }

    public function fileSize(string $path): FileAttributes
    {
        if (!isset($this->fileAttributeCache[$path])) {
            $this->fileAttributeCache[$path] = $this->getAdapter($path, $this->preparePath($path))->fileSize($path);
        }

        return $this->fileAttributeCache[$path];
    }
}
