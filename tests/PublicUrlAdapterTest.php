<?php

declare(strict_types=1);


use Collecthor\FlySystem\MoveOverwriteAdapter;
use Collecthor\FlySystem\PublicUrlAdapter;
use Collecthor\FlySystem\Tests\IndirectAdapterTestCase;
use League\Flysystem\Config;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\InMemory\InMemoryFilesystemAdapter;
use League\Flysystem\UnableToMoveFile;
use League\Flysystem\UrlGeneration\PrefixPublicUrlGenerator;
use League\Flysystem\UrlGeneration\PublicUrlGenerator;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(MoveOverwriteAdapter::class)]
final class PublicUrlAdapterTest extends IndirectAdapterTestCase
{
    public function testPublicUrl(): void
    {
        $adapter = $this->adapter();
        $config = new Config();
        $adapter->write('a', 'test1', $config);


        $this->assertInstanceOf(PublicUrlGenerator::class, $adapter);
        $this->assertSame('http://test/a', $adapter->publicUrl('a', new Config()));
    }

    protected static function createFilesystemAdapter(): FilesystemAdapter
    {
        return new PublicUrlAdapter(new InMemoryFilesystemAdapter(), new PrefixPublicUrlGenerator('http://test'));
    }
}
