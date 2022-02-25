<?php
declare(strict_types=1);

namespace Collecthor\FlySystem\Tests;

use Collecthor\FlySystem\AddPrefixAdapter;
use Collecthor\FlySystem\StripPrefixAdapter;
use League\Flysystem\AdapterTestUtilities\FilesystemAdapterTestCase;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\InMemory\InMemoryFilesystemAdapter;

/**
 * @covers \Collecthor\FlySystem\StripPrefixAdapter
 * @uses \Collecthor\FlySystem\AddPrefixAdapter
 * @uses \Collecthor\FlySystem\IndirectAdapter
 */
class StripPrefixAdapterTest extends FilesystemAdapterTestCase
{

    protected static function createFilesystemAdapter(): FilesystemAdapter
    {
        $prefix = 'abcdef/';
        return new AddPrefixAdapter(new StripPrefixAdapter(new InMemoryFilesystemAdapter(), $prefix), $prefix);
    }
}
