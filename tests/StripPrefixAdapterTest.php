<?php

declare(strict_types=1);

namespace Collecthor\FlySystem\Tests;

use Collecthor\FlySystem\AddPrefixAdapter;
use Collecthor\FlySystem\StripPrefixAdapter;
use League\Flysystem\AdapterTestUtilities\FilesystemAdapterTestCase;
use League\Flysystem\Config;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\InMemory\InMemoryFilesystemAdapter;

/**
 * @covers \Collecthor\FlySystem\StripPrefixAdapter
 * @uses \Collecthor\FlySystem\AddPrefixAdapter
 * @uses \Collecthor\FlySystem\IndirectAdapter
 */
class StripPrefixAdapterTest extends IndirectAdapterTestCase
{
    protected static function createFilesystemAdapter(): FilesystemAdapter
    {
        $prefix = 'abcdef/';
        return new AddPrefixAdapter(new StripPrefixAdapter(new InMemoryFilesystemAdapter(), $prefix), $prefix);
    }

    public function testListWithoutTrailingSlash(): void
    {
        $adapter = new StripPrefixAdapter(new InMemoryFilesystemAdapter(), 'test/');
        $adapter->write('test/abc', 'test', new Config());
        $adapter->write('test/def', 'test', new Config());

        $this->assertListingsAreTheSame($adapter->listContents('test', false), $adapter->listContents('test/', false));
    }
}
