<?php

declare(strict_types=1);

namespace Collecthor\FlySystem\Tests;

use Collecthor\FlySystem\AddPrefixAdapter;
use Collecthor\FlySystem\DirectoryViaPlaceholderFileAdapter;
use League\Flysystem\AdapterTestUtilities\FilesystemAdapterTestCase;
use League\Flysystem\Config;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\InMemory\InMemoryFilesystemAdapter;

/**
 * @covers \Collecthor\FlySystem\DirectoryViaPlaceholderFileAdapter
 * @uses \Collecthor\FlySystem\IndirectAdapter
 */
class DirectoryViaPlaceholderFileAdapterTest extends FilesystemAdapterTestCase
{
    public static function clearFilesystemAdapterCache(): void
    {
        parent::clearFilesystemAdapterCache();
    }


    protected static function createFilesystemAdapter(): FilesystemAdapter
    {
        $adapterWithoutCreateDirectory = new class() extends InMemoryFilesystemAdapter {
            public function createDirectory(string $path, Config $config): void
            {
                // Do nothing.
            }
        };
        return new DirectoryViaPlaceholderFileAdapter($adapterWithoutCreateDirectory);
    }
}
