<?php

declare(strict_types=1);

namespace Collecthor\FlySystem\Tests;

use Collecthor\FlySystem\DirectoryViaPlaceholderFileAdapter;
use League\Flysystem\Config;
use League\Flysystem\DirectoryAttributes;
use League\Flysystem\FileAttributes;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\InMemory\InMemoryFilesystemAdapter;
use League\Flysystem\UnableToRetrieveMetadata;

/**
 * @covers \Collecthor\FlySystem\DirectoryViaPlaceholderFileAdapter
 * @uses \Collecthor\FlySystem\IndirectAdapter
 */
class DirectoryViaPlaceholderFileAdapterTest extends IndirectAdapterTestCase
{
    public static function clearFilesystemAdapterCache(): void
    {
        parent::clearFilesystemAdapterCache();
    }

    protected static function createFilesystemAdapter(): FilesystemAdapter
    {
        $adapterWithoutCreateDirectory = new class extends InMemoryFilesystemAdapter {
            public function createDirectory(string $path, Config $config): never
            {
                throw new \Exception('This should not be called');
            }
        };
        return new DirectoryViaPlaceholderFileAdapter($adapterWithoutCreateDirectory);
    }

    public function testThatPlaceholderDirectoriesDontHaveSize(): void
    {
        $adapter = $this->adapter();
        $adapter->createDirectory('test', new Config());
        $this->expectException(UnableToRetrieveMetadata::class);
        $adapter->fileSize('test');
    }

    public function testThatPlaceholderDirectoriesDontHaveMimeType(): void
    {
        $adapter = $this->adapter();
        $adapter->createDirectory('test', new Config());
        $this->expectException(UnableToRetrieveMetadata::class);
        $adapter->mimeType('test');
    }

    public function testThatPlaceholderDirectoriesDontHaveLastModified(): void
    {
        $adapter = $this->adapter();
        $adapter->createDirectory('test', new Config());
        $this->expectException(UnableToRetrieveMetadata::class);
        $adapter->lastModified('test');
    }

    public function testAdapterWithoutSupportForDirectories(): void
    {
        $baseAdapter = $this->getMockBuilder(FilesystemAdapter::class)->getMock();
        $baseAdapter->expects($this->never())->method('createDirectory');

        $list = [
            new FileAttributes('test/.directory'),
        ];
        $baseAdapter->expects($this->once())->method('listContents')->willReturn($list);

        $subject = new DirectoryViaPlaceholderFileAdapter($baseAdapter);

        $this->assertListingsAreTheSame([
            new DirectoryAttributes('test'),
        ], $subject->listContents('', false));
    }
}
