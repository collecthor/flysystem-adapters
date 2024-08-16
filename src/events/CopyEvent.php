<?php

declare(strict_types=1);

namespace Collecthor\FlySystem\events;

use League\Flysystem\Config;
use League\Flysystem\FilesystemAdapter;
use PHPUnit\Framework\Attributes\CodeCoverageIgnore;

/**
 * @codeCoverageIgnore
 */
final readonly class CopyEvent
{
    public function __construct(
        public FilesystemAdapter $adapter,
        public bool $before,
        public string $source,
        public string $destination,
        public Config $config,
    ) {}
}
