<?php

declare(strict_types=1);

namespace Collecthor\FlySystem\events;

use League\Flysystem\FilesystemAdapter;

readonly class AdapterEvent
{
    public function __construct(
        public FilesystemAdapter $adapter,
        public bool $before = true,
    ) {
    }
}
