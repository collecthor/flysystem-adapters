<?php

declare(strict_types=1);

namespace Collecthor\FlySystem\Tests;

use ArrayObject;
use Collecthor\FlySystem\EventedAdapter;
use Collecthor\FlySystem\events\CopyEvent;
use Collecthor\FlySystem\events\DeleteDirectoryEvent;
use Collecthor\FlySystem\events\DeleteEvent;
use Collecthor\FlySystem\events\MoveEvent;
use Collecthor\FlySystem\events\WriteEvent;
use Collecthor\FlySystem\events\WriteStreamEvent;
use Collecthor\FlySystem\MoveOverwriteAdapter;
use League\Flysystem\Config;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\InMemory\InMemoryFilesystemAdapter;
use League\Flysystem\UnableToMoveFile;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use Psr\EventDispatcher\EventDispatcherInterface;

#[CoversClass(MoveOverwriteAdapter::class)]
final class MoveOverwriteAdapterTest extends IndirectAdapterTestCase
{
    public function testMoveToExistingFileWithoutAdapter(): void
    {
        $adapter = $this->adapter();
        $adapter = new InMemoryFilesystemAdapter();
        $config = new Config();
        $adapter->write('a', 'test1', $config);
        $adapter->write('b', 'test2', $config);

        $this->expectException(UnableToMoveFile::class);
        $adapter->move('a', 'b', $config);
    }

    public function testMoveToExistingFile(): void
    {
        $adapter = $this->adapter();
        $config = new Config();
        $adapter->write('a', 'test1', $config);
        $adapter->write('b', 'test2', $config);

        $adapter->move('a', 'b', $config);


        self::assertSame('test1', $adapter->read('b'));
    }
    protected static function createFilesystemAdapter(): FilesystemAdapter
    {
        return new MoveOverwriteAdapter(new InMemoryFilesystemAdapter());
    }
}
