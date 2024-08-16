<?php

declare(strict_types=1);

namespace Collecthor\FlySystem\Tests;

use Collecthor\FlySystem\MetadataCachingAdapter;
use League\Flysystem\FileAttributes;
use League\Flysystem\FilesystemAdapter;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Collecthor\FlySystem\MetadataCachingAdapter
 * @uses \Collecthor\FlySystem\IndirectAdapter
 */
final class MetadataCachingAdapterTest extends TestCase
{
    public static function metadataTypes(): array
    {
        return [
            ['lastModified'],
            ['fileSize'],
            ['visibility'],
            ['mimeType'],

        ];
    }

    public static function multipleMetadataTypes(): iterable
    {
        foreach (self::metadataTypes() as [$type1]) {
            foreach (self::metadataTypes() as [$type2]) {
                if ($type1 !== $type2) {
                    yield [$type1, $type2];
                }
            }
        }
    }

    /**
     * @dataProvider metadataTypes
     */
    public function testMetadataCaching(string $type): void
    {
        $base = $this->getMockBuilder(FilesystemAdapter::class)->getMock();
        $result = new FileAttributes('');
        $base->expects(self::once())->method($type)->willReturn($result);

        $subject = new MetadataCachingAdapter($base);

        $this->assertSame($result, $subject->$type('/'));
        $this->assertSame($result, $subject->$type('/'));
    }

    /**
     * @dataProvider multipleMetadataTypes
     */
    public function testMetadataIsCachedForTheDifferentTypes(string $type1, string $type2): void
    {
        $base = $this->getMockBuilder(FilesystemAdapter::class)->getMock();
        $result = new FileAttributes('');
        $base->expects(self::once())->method($type1)->willReturn($result);
        $base->expects(self::never())->method($type2)->willReturn($result);

        $subject = new MetadataCachingAdapter($base);

        $this->assertSame($result, $subject->$type1('/'));
        $this->assertSame($result, $subject->$type2('/'));
    }
}
