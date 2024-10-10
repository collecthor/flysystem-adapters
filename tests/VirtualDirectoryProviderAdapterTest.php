<?php

declare(strict_types=1);

use Collecthor\FlySystem\IndirectAdapter;
use Collecthor\FlySystem\LazyDirectoryProvider;
use Collecthor\FlySystem\Tests\IndirectAdapterTestCase;
use Collecthor\FlySystem\VirtualDirectoryProviderAdapter;
use League\Flysystem\Config;
use League\Flysystem\DirectoryAttributes;
use League\Flysystem\FileAttributes;
use League\Flysystem\InMemory\InMemoryFilesystemAdapter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;

#[CoversClass(VirtualDirectoryProviderAdapter::class)]
#[UsesClass(IndirectAdapter::class)]
#[UsesClass(LazyDirectoryProvider::class)]
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
        $provider = new LazyDirectoryProvider(static fn() => $directories, false);
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

    public function testLazyLoading(): void
    {
        $memoryAdapter = new InMemoryFilesystemAdapter();
        $memoryAdapter->createDirectory('test123', new Config());
        $memoryAdapter->createDirectory('test1234', new Config());
        $provider = new LazyDirectoryProvider(static fn() => throw new \RuntimeException('Not implemented'), false);
        $virtualDirectoryAdapter = new VirtualDirectoryProviderAdapter($memoryAdapter, 'test123/', $provider);

        $this->assertFalse($virtualDirectoryAdapter->directoryExists('test'));
        $this->assertTrue($virtualDirectoryAdapter->directoryExists('test1234'));
        $this->assertTrue($virtualDirectoryAdapter->directoryExists('test123'));
    }
}
