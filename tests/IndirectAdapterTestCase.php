<?php

declare(strict_types=1);

namespace Collecthor\FlySystem\Tests;

use League\Flysystem\AdapterTestUtilities\FilesystemAdapterTestCase;
use League\Flysystem\Config;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\StorageAttributes;
use League\Flysystem\UnableToGeneratePublicUrl;

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
            $this->assertArrayHasKey($entry->path(), $expectedValue, "Found unexpected path '{$entry->path()}' in listing");
            $this->assertSame($expectedValue[$entry->path()], $entry->type());
            unset($expectedValue[$entry->path()]);
        }

        $this->assertEmpty($expectedValue, 'Some expected entries where not found');
    }

    /**
     * Patched parent tests to deal with different number of initial entries
     * @test
     */
    final public function listing_a_toplevel_directory(): void
    {
        $initialCount = iterator_count($this->adapter()->listContents('', true));
        $this->givenWeHaveAnExistingFile('path1.txt');
        $this->givenWeHaveAnExistingFile('path2.txt');

        $this->runScenario(function () use ($initialCount) {
            $contents = iterator_to_array($this->adapter()->listContents('', true));

            $this->assertCount($initialCount + 2, $contents);
        });
    }

    /**
     * Patched parent tests to deal with different number of initial entries
     * @test
     */
    final public function listing_contents_recursive(): void
    {
        $this->runScenario(function () {
            $adapter = $this->adapter();
            $initialCount = iterator_count($adapter->listContents('', true));

            $adapter->createDirectory('path', new Config());
            $adapter->write('path/file.txt', 'string', new Config());

            $listing = $adapter->listContents('', true);
            /** @var StorageAttributes[] $items */
            $items = iterator_to_array($listing);
            $this->assertCount($initialCount + 2, $items, $this->formatIncorrectListingCount($items));
        });
    }

    /**
     * @test
     */
    public function checking_if_a_directory_exists_after_creating_it(): void
    {
        $this->runScenario(function () {
            $adapter = $this->adapter();
            $initialCount = iterator_count($adapter->listContents('/', false));
            $adapter->createDirectory('explicitly-created-directory', new Config());
            self::assertTrue($adapter->directoryExists('explicitly-created-directory'));
            $adapter->deleteDirectory('explicitly-created-directory');
            self::assertCount($initialCount, iterator_to_array($adapter->listContents('/', false), false));
            self::assertFalse($adapter->directoryExists('explicitly-created-directory'));
        });
    }

    public function generating_a_temporary_url(): void
    {
        $this->markTestSkipped('Indirect adapters forward this to their underlying adapter');
    }

    public function generating_a_public_url(): void
    {
        $this->markTestSkipped('Indirect adapters forward this to their underlying adapter');
    }
}
