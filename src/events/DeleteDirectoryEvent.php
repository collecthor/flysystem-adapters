<?php

declare(strict_types=1);

namespace Collecthor\FlySystem\events;

use League\Flysystem\Config;
use League\Flysystem\FilesystemAdapter;

/**
 * @codeCoverageIgnore
 */
final readonly class DeleteDirectoryEvent
{
    public function __construct(
        public FilesystemAdapter $adapter,
        public bool $before,
        public string $path,
    ) {}
}
