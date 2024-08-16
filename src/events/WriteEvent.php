<?php

declare(strict_types=1);

namespace Collecthor\FlySystem\events;

use League\Flysystem\Config;
use League\Flysystem\FilesystemAdapter;

/**
 * @codeCoverageIgnore
 */
final readonly class WriteEvent
{
    public function __construct(
        public FilesystemAdapter $adapter,
        public bool $before,
        public string $path,
        public string $contents,
        public Config $config,
    ) {}
}
