<?php

declare(strict_types=1);

namespace Collecthor\FlySystem\Tests;

use Collecthor\FlySystem\LazyDirectoryProvider;
use League\Flysystem\FileAttributes;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(LazyDirectoryProvider::class)]
class LazyDirectoryProviderTest extends TestCase
{
    public function testLoadImmediately(): void
    {
        $loaded = false;
        $provider = new LazyDirectoryProvider(
            loader: function () use (&$loaded) {
                $loaded = true;
                return [];
            },
            loadImmediately: true,
        );
        $this->assertTrue($loaded);
    }

    public function testArrayIsReturned(): void
    {
        $values = [
            'a' => new FileAttributes('a'),
            'b' => new FileAttributes('b'),
            'c' => new FileAttributes('c'),
            'd' => new FileAttributes('d'),
            'e' => new FileAttributes('e'),
        ];
        /**
         * @return Array<string, FileAttributes>
         */
        $loader = function () use ($values): array {
            return $values;
        };
        $provider = new LazyDirectoryProvider(
            loader: $loader,
        );

        self::assertSame($values, iterator_to_array($provider->getIterator()));
    }

    public function testHasTopLevelDirectory(): void
    {
        $values = [
            'a' => new FileAttributes('a'),
            'b' => new FileAttributes('b'),
            'c' => new FileAttributes('c'),
            'd' => new FileAttributes('d'),
            'e' => new FileAttributes('e'),
        ];
        /**
         * @return Array<string, FileAttributes>
         */
        $loader = function () use ($values): array {
            return $values;
        };
        $provider = new LazyDirectoryProvider(
            loader: $loader,
        );

        self::assertTrue($provider->hasTopLevelDirectory('a'));
        self::assertFalse($provider->hasTopLevelDirectory('x'));
    }
}
