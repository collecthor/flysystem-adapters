<?php

declare(strict_types=1);

namespace Collecthor\FlySystem\Tests;

use League\Flysystem\AdapterTestUtilities\FilesystemAdapterTestCase;
use League\Flysystem\Config;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\StorageAttributes;
use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

abstract class IndirectAdapterTestCase extends FilesystemAdapterTestCase
{
    /**
     * The parent method is annotated with "@after", which PHPUnit 12 no longer reads, so without
     * this attribute storage would leak between tests within the same class.
     */
    #[Override]
    #[After]
    public function cleanupAdapter(): void
    {
        parent::cleanupAdapter();
    }

    /**
     * PHPUnit no longer reads the parent's "@test" annotations, so every test defined by the parent
     * test case is re-declared here with the "#[Test]" attribute. This assertion fails whenever the
     * parent gains (or loses) a "@test" method, so new tests cannot be silently skipped.
     */
    #[Test]
    public function all_parent_tests_are_overridden(): void
    {
        $parentFile = (new \ReflectionClass(FilesystemAdapterTestCase::class))->getFileName();
        self::assertIsString($parentFile);

        $contents = file_get_contents($parentFile);
        self::assertIsString($contents);

        self::assertSame(
            49,
            substr_count($contents, '@test'),
            'The parent test case changed its number of "@test" methods; update the overrides in this class.',
        );
    }

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
     */
    #[Override]
    #[Test]
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
     */
    #[Override]
    #[Test]
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

    #[Override]
    #[Test]
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

    #[Override]
    #[Test]
    public function generating_a_temporary_url(): void
    {
        $this->markTestSkipped('Indirect adapters forward this to their underlying adapter');
    }

    #[Override]
    #[Test]
    public function generating_a_public_url(): void
    {
        $this->markTestSkipped('Indirect adapters forward this to their underlying adapter');
    }

    #[Override]
    #[Test]
    public function writing_and_reading_with_string(): void
    {
        parent::writing_and_reading_with_string();
    }

    #[Override]
    #[Test]
    public function writing_a_file_with_a_stream(): void
    {
        parent::writing_a_file_with_a_stream();
    }

    #[Override]
    #[Test]
    #[DataProvider('filenameProvider')]
    public function writing_and_reading_files_with_special_path(string $path): void
    {
        parent::writing_and_reading_files_with_special_path($path);
    }

    #[Override]
    #[Test]
    public function writing_a_file_with_an_empty_stream(): void
    {
        parent::writing_a_file_with_an_empty_stream();
    }

    #[Override]
    #[Test]
    public function listing_a_directory_named_0(): void
    {
        parent::listing_a_directory_named_0();
    }

    #[Override]
    #[Test]
    public function reading_a_file(): void
    {
        parent::reading_a_file();
    }

    #[Override]
    #[Test]
    public function reading_a_file_with_a_stream(): void
    {
        parent::reading_a_file_with_a_stream();
    }

    #[Override]
    #[Test]
    public function overwriting_a_file(): void
    {
        parent::overwriting_a_file();
    }

    #[Override]
    #[Test]
    public function a_file_exists_only_when_it_is_written_and_not_deleted(): void
    {
        parent::a_file_exists_only_when_it_is_written_and_not_deleted();
    }

    #[Override]
    #[Test]
    public function listing_contents_shallow(): void
    {
        parent::listing_contents_shallow();
    }

    #[Override]
    #[Test]
    public function checking_if_a_non_existing_directory_exists(): void
    {
        parent::checking_if_a_non_existing_directory_exists();
    }

    #[Override]
    #[Test]
    public function checking_if_a_directory_exists_after_writing_a_file(): void
    {
        parent::checking_if_a_directory_exists_after_writing_a_file();
    }

    #[Override]
    #[Test]
    public function fetching_file_size(): void
    {
        parent::fetching_file_size();
    }

    #[Override]
    #[Test]
    public function setting_visibility(): void
    {
        parent::setting_visibility();
    }

    #[Override]
    #[Test]
    public function fetching_file_size_of_a_directory(): void
    {
        parent::fetching_file_size_of_a_directory();
    }

    #[Override]
    #[Test]
    public function fetching_file_size_of_non_existing_file(): void
    {
        parent::fetching_file_size_of_non_existing_file();
    }

    #[Override]
    #[Test]
    public function fetching_last_modified_of_non_existing_file(): void
    {
        parent::fetching_last_modified_of_non_existing_file();
    }

    #[Override]
    #[Test]
    public function fetching_visibility_of_non_existing_file(): void
    {
        parent::fetching_visibility_of_non_existing_file();
    }

    #[Override]
    #[Test]
    public function fetching_the_mime_type_of_an_svg_file(): void
    {
        parent::fetching_the_mime_type_of_an_svg_file();
    }

    #[Override]
    #[Test]
    public function fetching_mime_type_of_non_existing_file(): void
    {
        parent::fetching_mime_type_of_non_existing_file();
    }

    #[Override]
    #[Test]
    public function fetching_unknown_mime_type_of_a_file(): void
    {
        parent::fetching_unknown_mime_type_of_a_file();
    }

    #[Override]
    #[Test]
    public function writing_and_reading_with_streams(): void
    {
        parent::writing_and_reading_with_streams();
    }

    #[Override]
    #[Test]
    public function setting_visibility_on_a_file_that_does_not_exist(): void
    {
        parent::setting_visibility_on_a_file_that_does_not_exist();
    }

    #[Override]
    #[Test]
    public function copying_a_file(): void
    {
        parent::copying_a_file();
    }

    #[Override]
    #[Test]
    public function copying_a_file_that_does_not_exist(): void
    {
        parent::copying_a_file_that_does_not_exist();
    }

    #[Override]
    #[Test]
    public function copying_a_file_again(): void
    {
        parent::copying_a_file_again();
    }

    #[Override]
    #[Test]
    public function moving_a_file(): void
    {
        parent::moving_a_file();
    }

    #[Override]
    #[Test]
    public function file_exists_on_directory_is_false(): void
    {
        parent::file_exists_on_directory_is_false();
    }

    #[Override]
    #[Test]
    public function directory_exists_on_file_is_false(): void
    {
        parent::directory_exists_on_file_is_false();
    }

    #[Override]
    #[Test]
    public function reading_a_file_that_does_not_exist(): void
    {
        parent::reading_a_file_that_does_not_exist();
    }

    #[Override]
    #[Test]
    public function moving_a_file_that_does_not_exist(): void
    {
        parent::moving_a_file_that_does_not_exist();
    }

    #[Override]
    #[Test]
    public function trying_to_delete_a_non_existing_file(): void
    {
        parent::trying_to_delete_a_non_existing_file();
    }

    #[Override]
    #[Test]
    public function checking_if_files_exist(): void
    {
        parent::checking_if_files_exist();
    }

    #[Override]
    #[Test]
    public function fetching_last_modified(): void
    {
        parent::fetching_last_modified();
    }

    #[Override]
    #[Test]
    public function failing_to_read_a_non_existing_file_into_a_stream(): void
    {
        parent::failing_to_read_a_non_existing_file_into_a_stream();
    }

    #[Override]
    #[Test]
    public function failing_to_read_a_non_existing_file(): void
    {
        parent::failing_to_read_a_non_existing_file();
    }

    #[Override]
    #[Test]
    public function creating_a_directory(): void
    {
        parent::creating_a_directory();
    }

    #[Override]
    #[Test]
    public function copying_a_file_with_collision(): void
    {
        parent::copying_a_file_with_collision();
    }

    #[Override]
    #[Test]
    public function moving_a_file_with_collision(): void
    {
        parent::moving_a_file_with_collision();
    }

    #[Override]
    #[Test]
    public function copying_a_file_with_same_destination(): void
    {
        parent::copying_a_file_with_same_destination();
    }

    #[Override]
    #[Test]
    public function moving_a_file_with_same_destination(): void
    {
        parent::moving_a_file_with_same_destination();
    }

    #[Override]
    #[Test]
    public function get_checksum(): void
    {
        parent::get_checksum();
    }

    #[Override]
    #[Test]
    public function cannot_get_checksum_for_non_existent_file(): void
    {
        parent::cannot_get_checksum_for_non_existent_file();
    }

    #[Override]
    #[Test]
    public function cannot_get_checksum_for_directory(): void
    {
        parent::cannot_get_checksum_for_directory();
    }
}
