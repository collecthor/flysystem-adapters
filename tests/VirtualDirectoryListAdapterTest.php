<?php

declare(strict_types=1);

namespace Collecthor\FlySystem\Tests;

use Collecthor\FlySystem\VirtualDirectoryListAdapter;
use League\Flysystem\Config;
use League\Flysystem\DirectoryAttributes;
use League\Flysystem\FileAttributes;
use League\Flysystem\InMemory\InMemoryFilesystemAdapter;
use League\Flysystem\StorageAttributes;

/**
 * @covers \Collecthor\FlySystem\VirtualDirectoryListAdapter
 * @uses \Collecthor\FlySystem\IndirectAdapter
 */
class VirtualDirectoryListAdapterTest extends IndirectAdapterTestCase
{
    public static function clearFilesystemAdapterCache(): void
    {
        parent::clearFilesystemAdapterCache();
    }

    protected static function createFilesystemAdapter(): VirtualDirectoryListAdapter
    {
        $memoryAdapter = new InMemoryFilesystemAdapter();
        $memoryAdapter->createDirectory('test123', new Config());
        return new VirtualDirectoryListAdapter($memoryAdapter, 'test123/', ['abc', 'def']);
    }

    public function testListVirtualDirectoriesWhenPrimaryIsEmpty(): void
    {
        $adapter = $this->adapter();
        $this->assertListingLength(3, $adapter, 'test123/', false);
        $this->assertListingsAreTheSame([
            new DirectoryAttributes('test123'),
            new DirectoryAttributes('test123/abc'),
            new DirectoryAttributes('test123/def'),
        ], $adapter->listContents('test123', false));
    }

    public function testListVirtualDirectoriesPrimaryNonEmpty(): void
    {
        $adapter = $this->adapter();
        $this->assertListingLength(3, $adapter, 'test123/', false);
        $this->assertListingLength(3, $adapter, 'test123/', true);

        $this->givenWeHaveAnExistingFile('test123/abc/test', 'cool');
        $this->assertListingLength(3, $adapter, 'test123/', false);
        $this->assertListingLength(4, $adapter, 'test123/', true);

        $this->assertListingsAreTheSame([
            new DirectoryAttributes('test123'),
            new DirectoryAttributes('test123/abc'),
            new DirectoryAttributes('test123/def'),
        ], $adapter->listContents('test123', false));

        $this->assertListingsAreTheSame([
            new DirectoryAttributes('test123'),
            new DirectoryAttributes('test123/abc'),
            new DirectoryAttributes('test123/def'),
            new FileAttributes('test123/abc/test'),

        ], $adapter->listContents('test123', true));
    }

    public function testListVirtualDirectoriesDeep(): void
    {
        $adapter = $this->adapter();
        $this->assertListingLength(3, $adapter, '', true);

        $this->assertListingsAreTheSame([
            new DirectoryAttributes('test123'),
            new DirectoryAttributes('test123/abc'),
            new DirectoryAttributes('test123/def'),
        ], $adapter->listContents('test123', true));
    }
}
