<?php

declare(strict_types=1);

namespace Collecthor\FlySystem;

use League\Flysystem\Config;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\UrlGeneration\PublicUrlGenerator;

final readonly class PublicUrlAdapter implements FilesystemAdapter, PublicUrlGenerator
{
    use IndirectAdapterTrait;

    public function __construct(private FilesystemAdapter $adapter, private PublicUrlGenerator $urlGenerator)
    {
    }


    protected function getAdapter(string $rawPath, string $preparedPath): FilesystemAdapter
    {
        return $this->adapter;
    }

    public function publicUrl(string $path, Config $config): string
    {
        return $this->urlGenerator->publicUrl($path, $config);
    }
}
