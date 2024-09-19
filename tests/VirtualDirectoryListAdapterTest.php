<?php

declare(strict_types=1);

namespace Collecthor\FlySystem\Tests;

use Collecthor\FlySystem\IndirectAdapter;
use Collecthor\FlySystem\VirtualDirectoryListAdapter;
use League\Flysystem\Config;
use League\Flysystem\DirectoryAttributes;
use League\Flysystem\FileAttributes;
use League\Flysystem\InMemory\InMemoryFilesystemAdapter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;

#[CoversClass(VirtualDirectoryListAdapter::class)]
#[UsesClass(IndirectAdapter::class)]
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

    public function testPathWithoutTrailingSlash(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new VirtualDirectoryListAdapter(new InMemoryFilesystemAdapter(), path: 'test', directories: []);

    }

    public function testDirectoryWithSlash(): void
    {
        new VirtualDirectoryListAdapter(new InMemoryFilesystemAdapter(), path: 'test/', directories: []);
        $this->expectException(\InvalidArgumentException::class);
        new VirtualDirectoryListAdapter(new InMemoryFilesystemAdapter(), path: 'test/', directories: ['abc/def']);

    }

    public function testRootDirectoryMetaData(): void
    {
        $meta = [
            'obj' => new \stdClass(),
        ];
        $adapter = new VirtualDirectoryListAdapter(new InMemoryFilesystemAdapter(), path: 'test/', directories: [], rootMetadata: $meta);
        $contents = iterator_to_array($adapter->listContents('test', false));
        $this->assertCount(1, $contents);
        $directoryAttributes = $contents[0];
        $this->assertInstanceOf(DirectoryAttributes::class, $directoryAttributes);
        $this->assertSame($meta, $directoryAttributes->extraMetadata());
    }
}
