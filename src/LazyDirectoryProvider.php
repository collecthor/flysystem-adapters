<?php

declare(strict_types=1);

namespace Collecthor\FlySystem;

use ArrayObject;
use Closure;
use Collecthor\FlySystem\interfaces\DirectoryProvider;
use League\Flysystem\DirectoryAttributes;
use Traversable;

/**
 * Allows you to lazily provide directories for use with VirtualDirectoryListAdapter
 */
final class LazyDirectoryProvider implements DirectoryProvider
{
    /**
     * @var ArrayObject<string, DirectoryAttributes>|null
     */
    private ArrayObject|null $directories = null;

    /**
     * @param Closure(): iterable<string, DirectoryAttributes> $loader
     * @param bool $loadImmediately when provided will immediately attempt to call the loader, this can be used for testing to get cleaner traces.
     */
    public function __construct(
        private readonly Closure $loader,
        bool $loadImmediately = false
    ) {
        if ($loadImmediately) {
            $this->getDirectories();
        }
    }

    /**
     * @return ArrayObject<string, DirectoryAttributes>
     */
    private function getDirectories(): ArrayObject
    {
        if (!isset($this->directories)) {
            /** @var ArrayObject<string, DirectoryAttributes> $directories */
            $directories = new ArrayObject();
            foreach (($this->loader)() as $name => $item) {
                $directories[$name] = $item;
            }
            $this->directories = $directories;
        }
        return $this->directories;
    }


    public function getIterator(): Traversable
    {
        return $this->getDirectories();
    }

    public function hasTopLevelDirectory(string $name): bool
    {
        return $this->getDirectories()->offsetExists($name);
    }
}
