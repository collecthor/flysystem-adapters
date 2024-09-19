<?php

declare(strict_types=1);

namespace Collecthor\FlySystem\Tests;

use Collecthor\FlySystem\IndirectAdapter;
use Collecthor\FlySystem\VirtualDirectoryListWithMetaDataAdapter;
use League\Flysystem\Config;
use League\Flysystem\DirectoryAttributes;
use League\Flysystem\FileAttributes;
use League\Flysystem\InMemory\InMemoryFilesystemAdapter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;

#[CoversClass(VirtualDirectoryListWithMetaDataAdapter::class)]
#[UsesClass(IndirectAdapter::class)]
final class VirtualDirectoryListWithMetaDataAdapterTest extends IndirectAdapterTestCase
{
    public static function clearFilesystemAdapterCache(): void
    {
        parent::clearFilesystemAdapterCache();
    }

    protected static function createFilesystemAdapter(): VirtualDirectoryListWithMetaDataAdapter
    {
        $memoryAdapter = new InMemoryFilesystemAdapter();
        $memoryAdapter->createDirectory('test123', new Config());
        return new VirtualDirectoryListWithMetaDataAdapter($memoryAdapter, 'test123/', [
            'abc' => ['meta' => 'cool'],
            'def' => ['meta' => 'stuff'],
        ]);
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

    public function testPathWithoutTrailingSlash(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new VirtualDirectoryListWithMetaDataAdapter(new InMemoryFilesystemAdapter(), path: 'test', directories: []);

    }

    public function testDirectoryWithSlash(): void
    {
        new VirtualDirectoryListWithMetaDataAdapter(new InMemoryFilesystemAdapter(), path: 'test/', directories: []);
        $this->expectException(\InvalidArgumentException::class);
        new VirtualDirectoryListWithMetaDataAdapter(new InMemoryFilesystemAdapter(), path: 'test/', directories: ['abc/def' => []]);

    }
}
