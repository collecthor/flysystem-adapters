<?php

declare(strict_types=1);

namespace Collecthor\FlySystem;

use League\Flysystem\Config;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\UnableToMoveFile;
use League\Flysystem\UrlGeneration\PublicUrlGenerator;

/**
 * This adapter wraps underlying adapters which do not properly implement the move semantics.
 * It will try a move, if it fails it will check if the target exists and if so remove it and retry.
 */
final readonly class MoveOverwriteAdapter implements FilesystemAdapter, PublicUrlGenerator
{
    use IndirectAdapterTrait;

    public function __construct(private FilesystemAdapter $base)
    {
    }


    public function move(string $source, string $destination, Config $config): void
    {
        try {
            $this->base->move($source, $destination, $config);
        } catch (UnableToMoveFile $exception) {
            // Find out if it's a directory or a file.
            if ($this->base->fileExists($destination)) {
                $this->base->delete($destination);
                $this->base->move($source, $destination, $config);
            } else {
                throw $exception;
            }
        }
    }

    protected function getAdapter(string $rawPath, string $preparedPath): FilesystemAdapter
    {
        return $this->base;
    }
}
