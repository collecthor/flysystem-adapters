<?php

declare(strict_types=1);

namespace Collecthor\FlySystem\events;

use League\Flysystem\Config;
use League\Flysystem\FilesystemAdapter;

final readonly class WriteStreamEvent
{
    /**
     * @param FilesystemAdapter $adapter
     * @param bool $before
     * @param string $path
     * @param resource $contents
     * @param Config $config
     */
    public function __construct(
        public FilesystemAdapter $adapter,
        public bool $before,
        public string $path,
        public mixed $contents,
        public Config $config
    ) {
    }
}
