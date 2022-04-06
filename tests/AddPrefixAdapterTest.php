<?php

declare(strict_types=1);

namespace Collecthor\FlySystem\Tests;

use Collecthor\FlySystem\AddPrefixAdapter;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\InMemory\InMemoryFilesystemAdapter;

/**
 * @covers \Collecthor\FlySystem\AddPrefixAdapter
 * @uses \Collecthor\FlySystem\IndirectAdapter
 */
class AddPrefixAdapterTest extends IndirectAdapterTestCase
{
    public static function clearFilesystemAdapterCache(): void
    {
        parent::clearFilesystemAdapterCache();
    }


    protected static function createFilesystemAdapter(): FilesystemAdapter
    {
        return new AddPrefixAdapter(new InMemoryFilesystemAdapter(), 'test123/');
    }
}
