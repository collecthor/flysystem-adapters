<?php

declare(strict_types=1);

namespace Collecthor\FlySystem\Tests;

use Collecthor\FlySystem\IndirectAdapter;
use League\Flysystem\AdapterTestUtilities\FilesystemAdapterTestCase;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\InMemory\InMemoryFilesystemAdapter;

/**
 * @covers \Collecthor\FlySystem\IndirectAdapter
 * @uses \League\Flysystem\InMemory\InMemoryFilesystemAdapter
 */
class IndirectAdapterTest extends IndirectAdapterTestCase
{
    protected static function createFilesystemAdapter(): FilesystemAdapter
    {
        return new class() extends IndirectAdapter {
            private FilesystemAdapter $adapter;

            public function __construct()
            {
                $this->adapter = new InMemoryFilesystemAdapter();
            }
            protected function getAdapter(string $rawPath, string $preparedPath): FilesystemAdapter
            {
                return $this->adapter;
            }
        };
    }
}
