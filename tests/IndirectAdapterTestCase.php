<?php

declare(strict_types=1);

namespace Collecthor\FlySystem\Tests;

use League\Flysystem\AdapterTestUtilities\FilesystemAdapterTestCase;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\StorageAttributes;

abstract class IndirectAdapterTestCase extends FilesystemAdapterTestCase
{
    final protected function assertListingLength(int $expected, FilesystemAdapter $adapter, string $path, bool $deep = false): void
    {
        $listing = [];
        foreach ($adapter->listContents($path, $deep) as $entry) {
            $listing[] = $entry->path();
        }
        $this->assertCount($expected, $listing, "Failed asserting that listing has length $expected: " . print_r($listing, true));
    }

    /**
     * @param iterable<StorageAttributes> $expected
     * @param iterable<StorageAttributes> $actual
     * @return void
     */
    final protected function assertListingsAreTheSame(iterable $expected, iterable $actual): void
    {
        $expectedValue = [];
        foreach ($expected as $entry) {
            $expectedValue[$entry->path()] = $entry->type();
        }
        foreach ($actual as $entry) {
            $this->assertArrayHasKey($entry->path(), $expectedValue);
            $this->assertSame($expectedValue[$entry->path()], $entry->type());
        }
    }
}
