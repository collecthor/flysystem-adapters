<?php

declare(strict_types=1);

namespace Collecthor\FlySystem\Tests;

use Collecthor\FlySystem\IndirectAdapter;
use League\Flysystem\Config;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\InMemory\InMemoryFilesystemAdapter;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(IndirectAdapter::class)]
class IndirectAdapterTest extends IndirectAdapterTestCase
{
    protected static function createFilesystemAdapter(): FilesystemAdapter
    {
        return new readonly class extends IndirectAdapter {
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

    public function testForwardsToTheUnderlyingAdapter(): void
    {
        $adapter = $this->adapter();
        $adapter->write('path.txt', 'contents', new Config());

        self::assertTrue($adapter->fileExists('path.txt'));
        self::assertSame('contents', $adapter->read('path.txt'));
    }
}
