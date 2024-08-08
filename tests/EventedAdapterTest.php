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
use League\Flysystem\Config;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\InMemory\InMemoryFilesystemAdapter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use Psr\EventDispatcher\EventDispatcherInterface;

#[CoversClass(EventedAdapter::class)]
#[UsesClass(DeleteEvent::class)]
#[UsesClass(WriteStreamEvent::class)]
#[UsesClass(DeleteDirectoryEvent::class)]
#[UsesClass(MoveEvent::class)]
#[UsesClass(WriteEvent::class)]
#[UsesClass(CopyEvent::class)]
final class EventedAdapterTest extends IndirectAdapterTestCase
{
    /**
     * @return array{0:EventedAdapter, 1: ArrayObject}
     */
    private function createAdapter(): array
    {
        $events = new ArrayObject();



        $dispatcher = new readonly class($events) implements EventDispatcherInterface {
            public function __construct(private ArrayObject $events)
            {
            }
            public function dispatch(object $event): void
            {
                $this->events->append($event);
            }
        };
        return [new EventedAdapter(new InMemoryFilesystemAdapter(), $dispatcher), $events];
    }

    public function testMovingTriggersEvents(): void
    {
        [$adapter, $events] = $this->createAdapter();
        $adapter->write('source', 'abc', new Config());
        $events->exchangeArray([]);
        $adapter->move('source', 'destination', new Config());
        self::assertCount(2, $events);
        [$before, $after] = $events;
        self::assertInstanceOf(MoveEvent::class, $before);
        self::assertTrue($before->before);
        self::assertInstanceOf(MoveEvent::class, $after);
        self::assertFalse($after->before);
    }

    public function testWriteTriggersEvents(): void
    {
        [$adapter, $events] = $this->createAdapter();
        $adapter->write('source', 'abc', new Config());
        self::assertCount(2, $events);
        [$before, $after] = $events;
        self::assertInstanceOf(WriteEvent::class, $before);
        self::assertTrue($before->before);
        self::assertInstanceOf(WriteEvent::class, $after);
        self::assertFalse($after->before);
    }


    protected static function createFilesystemAdapter(): FilesystemAdapter
    {
        $dispatcher = new readonly class() implements EventDispatcherInterface {
            public function dispatch(object $event): void
            {
            }
        };
        return new EventedAdapter(new InMemoryFilesystemAdapter(), $dispatcher);
    }
}
