<?php

declare(strict_types=1);

namespace Collecthor\FlySystem\Tests;

use Collecthor\FlySystem\OverlayAdapter;
use Generator;
use League\Flysystem\Config;
use League\Flysystem\DirectoryAttributes;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\InMemory\InMemoryFilesystemAdapter;
use League\Flysystem\StorageAttributes;

/**
 * @covers \Collecthor\FlySystem\OverlayAdapter
 * @uses \Collecthor\FlySystem\IndirectAdapter
 * @uses \Collecthor\FlySystem\StripPrefixAdapter
 */
class OverlayAdapterTest extends IndirectAdapterTestCase
{
    protected static function createFilesystemAdapter(): FilesystemAdapter
    {
        return new OverlayAdapter(new InMemoryFilesystemAdapter(), new InMemoryFilesystemAdapter(), 'overlay/');
    }


    public function testFileIsPutInOverlay(): void
    {
        $base = new InMemoryFilesystemAdapter();
        $overlay = new InMemoryFilesystemAdapter();

        $combined = new OverlayAdapter($base, $overlay, 'overlay/');
        $path = 'overlay/Test1';
        $combined->write($path, 'test123', new Config());
        self::assertTrue($combined->fileExists($path));
        self::assertFalse($base->fileExists($path));
        self::assertTrue($overlay->fileExists('Test1'));
    }

    public function testDirectoryIsPutInOverlay(): void
    {
        $base = new InMemoryFilesystemAdapter();
        $overlay = new InMemoryFilesystemAdapter();

        $combined = new OverlayAdapter($base, $overlay, 'overlay/');
        $path = 'overlay/abc';
        $combined->createDirectory($path, new Config());
        self::assertTrue($combined->directoryExists($path));
        self::assertFalse($base->directoryExists($path));
        self::assertTrue($overlay->directoryExists('abc'));
    }

    public function testFileIsPutInBase(): void
    {
        $base = new InMemoryFilesystemAdapter();
        $overlay = new InMemoryFilesystemAdapter();

        $combined = new OverlayAdapter($base, $overlay, 'overlay/');
        $path = 'baseTest1';
        $combined->write($path, 'test123', new Config());
        self::assertTrue($combined->fileExists($path));
        self::assertTrue($base->fileExists($path));
        self::assertFalse($overlay->fileExists($path));
    }

    public function testDirectoryIsPutInBase(): void
    {
        $base = new InMemoryFilesystemAdapter();
        $overlay = new InMemoryFilesystemAdapter();

        $combined = new OverlayAdapter($base, $overlay, 'overlay/');

        $path = 'base/abc';
        $combined->createDirectory($path, new Config());
        self::assertTrue($combined->directoryExists($path));
        self::assertTrue($base->directoryExists($path));
        self::assertFalse($overlay->directoryExists($path));
    }

    public function testMoveBetweenAdaptersThrowsException(): void
    {
        $this->markTestIncomplete('TODO');
    }

    public function testCopyBetweenAdaptersThrowsException(): void
    {
        $this->markTestIncomplete('TODO');
    }

    /**
     * @dataProvider prefixProvider
     */
    public function testOverlayDirectoryExists(string $path): void
    {
        $base = new InMemoryFilesystemAdapter();
        $overlay = new InMemoryFilesystemAdapter();

        $combined = new OverlayAdapter($base, $overlay, $path . '/');

        while ($path !== ".") {
            self::assertTrue($combined->directoryExists($path), "Failed asserting directory $path exists");
            $path = dirname($path);
        }
    }

    /**
     * @dataProvider prefixProvider
     */
    public function testOverlayDirectoriesShowInListing(string $path): void
    {
        $base = new InMemoryFilesystemAdapter();
        $overlay = new InMemoryFilesystemAdapter();

        $combined = new OverlayAdapter($base, $overlay, $path . '/');

        $directoryCount = count(explode('/', $path));

        $this->assertListingLength($directoryCount, $combined, '', true);

        $expected = [];
        $testPath = '';
        foreach (explode('/', rtrim($path, '/')) as $i => $part) {
            $testPath = ltrim("{$testPath}/{$part}", '/');
            $expected[] = new DirectoryAttributes($testPath);
        }
        $this->assertListingsAreTheSame($expected, $combined->listContents('', true));
    }

    /**
     * @dataProvider prefixProvider
     */
    public function testDeepListingIncludesBoth(string $path): void
    {
        $base = new InMemoryFilesystemAdapter();
        $overlay = new InMemoryFilesystemAdapter();

        $combined = new OverlayAdapter($base, $overlay, $path . '/');

        $directoryCount = count(explode('/', $path));

        $this->assertListingLength(0, $overlay, '/');
        $this->assertListingLength($directoryCount, $combined, '/', true);
        $overlay->write("testfile", 'abc', new Config());

        $this->assertListingLength(1, $overlay, '/', true);
        $this->assertListingLength(1 + $directoryCount, $combined, '/', true);

        $this->assertListingLength(1, $combined, $path, true);
    }

    /**
     * @dataProvider prefixProvider
     */
    public function testDeepListingIncludesBothStub(string $path): void
    {
        $base = new InMemoryFilesystemAdapter();

        $overlay = $this->getMockBuilder(FilesystemAdapter::class)->getMock();
        $overlay->expects($this->once())->method('listContents')->with('')->willReturn([]);

        $combined = new OverlayAdapter($base, $overlay, $path . '/');
        $this->assertSameSize(explode('/', $path), iterator_to_array($combined->listContents('', true)));
    }

    /**
      * @dataProvider prefixProvider
      */
    public function testDeepListingIncludesBothStub2(string $path): void
    {
        $base = new InMemoryFilesystemAdapter();


        $overlay = $this->getMockBuilder(FilesystemAdapter::class)->getMock();
        $overlay->expects($this->once())->method('listContents')->with('')->willReturn([]);

        $combined = new OverlayAdapter($base, $overlay, $path . '/');

        $this->assertListingLength(count(explode('/', $path)), $combined, '/', true);
    }

    public function prefixProvider(): iterable
    {
        yield "second level directory" => ["some/overlay"];
        yield "top level directory" => ["overlay"];
    }

    /**
     * @dataProvider prefixProvider
     */
    public function testListingIncludesBoth(string $path): void
    {
        $base = new InMemoryFilesystemAdapter();
        $overlay = new InMemoryFilesystemAdapter();

        $combined = new OverlayAdapter($base, $overlay, $path . '/');

        $this->assertListingLength(1, $combined, '/');

        $overlay->write("testfile", 'abc', new Config());

        $this->assertListingLength(1, $combined, '/');

        // Get path components.
        $components = explode('/', $path);
        $this->assertListingLength(count($components) + 1, $combined, '/', true);
    }

    public function testListingEmptyPath(): void
    {
        $base = new InMemoryFilesystemAdapter();
        $overlay = new InMemoryFilesystemAdapter();

        $combined = new OverlayAdapter($base, $overlay, 'mount/');

        $this->assertListingLength(1, $combined, '/');
        $this->assertListingLength(1, $combined, '');
    }
    public function filenameProvider(): Generator
    {
        yield from parent::filenameProvider();
        yield "a nested path inside the overlay" => ["overlay/test/abc"];
    }

    public function clearStorage(): void
    {
        parent::clearFilesystemAdapterCache();
    }

    public function testListingEmptyPath(): void
    {
        $base = new InMemoryFilesystemAdapter();
        $overlay = new InMemoryFilesystemAdapter();

        $combined = new OverlayAdapter($base, $overlay, 'mount/');

        $this->assertListingLength(1, $combined, '/');
        $this->assertListingLength(1, $combined, '');
    }

}
