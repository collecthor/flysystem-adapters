<?php

declare(strict_types=1);

use Collecthor\FlySystem\LazyDirectoryProvider;
use Collecthor\FlySystem\Tests\IndirectAdapterTestCase;
use Collecthor\FlySystem\VirtualDirectoryListAdapter;
use Collecthor\FlySystem\VirtualDirectoryProviderAdapter;
use League\Flysystem\Config;
use League\Flysystem\DirectoryAttributes;
use League\Flysystem\FileAttributes;
use League\Flysystem\InMemory\InMemoryFilesystemAdapter;

/**
 * @covers \Collecthor\FlySystem\VirtualDirectoryProviderAdapter
 * @uses \Collecthor\FlySystem\IndirectAdapter
 * @uses \Collecthor\FlySystem\LazyDirectoryProvider
 */
final class VirtualDirectoryProviderAdapterTest extends IndirectAdapterTestCase
{
    public static function clearFilesystemAdapterCache(): void
    {
        IndirectAdapterTestCase::clearFilesystemAdapterCache();
    }

    protected static function createFilesystemAdapter(): VirtualDirectoryProviderAdapter
    {
        $memoryAdapter = new InMemoryFilesystemAdapter();
        $memoryAdapter->createDirectory('test123', new Config());
        $directories = [
            'abc' => new DirectoryAttributes('test123/abc'),
            'def' => new DirectoryAttributes('test123/def'),

        ];
        $provider = new LazyDirectoryProvider(static fn() => $directories, true);
        return new VirtualDirectoryProviderAdapter($memoryAdapter, 'test123/', $provider);
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

    public function testRootDirectoryMetaData(): void
    {
        $meta = [
            'obj' => new \stdClass(),
        ];
        $directoryProvider = new LazyDirectoryProvider(static fn() => []);
        $adapter = new VirtualDirectoryProviderAdapter(new InMemoryFilesystemAdapter(), path: 'test/', directories: $directoryProvider, rootMetadata: $meta);
        $contents = iterator_to_array($adapter->listContents('test', false));
        $this->assertCount(1, $contents);
        $directoryAttributes = $contents[0];
        $this->assertInstanceOf(DirectoryAttributes::class, $directoryAttributes);
        $this->assertSame($meta, $directoryAttributes->extraMetadata());
    }

    public function testPathWithoutTrailingSlash(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new VirtualDirectoryProviderAdapter(new InMemoryFilesystemAdapter(), path: 'test', directories: new LazyDirectoryProvider(static fn() => []));
    }
}
