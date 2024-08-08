<?php

declare(strict_types=1);

namespace Collecthor\FlySystem;

use League\Flysystem\Config;
use League\Flysystem\FileAttributes;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\UnableToGeneratePublicUrl;
use League\Flysystem\UrlGeneration\PublicUrlGenerator;

/**
 * Indirect adapter implements a simple pattern that allows extending classes to provide a concrete adapter for any call
 * as well as rewrite the path passed to such calls.
 */

abstract readonly class IndirectAdapter implements FilesystemAdapter, PublicUrlGenerator
{
    use IndirectAdapterTrait;
}
