<?php

declare(strict_types=1);

namespace Collecthor\FlySystem;

use League\Flysystem\Config;
use League\Flysystem\DirectoryAttributes;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\PathPrefixer;
use League\Flysystem\UnableToDeleteFile;
use League\Flysystem\UnableToMoveFile;

/**
 * This adapter mounts one adapter as an overlay on top of another based on a prefix
 */
final readonly class OverlayAdapter extends IndirectAdapter
{
    private FilesystemAdapter $overlay;

    /**
     * @var array<string, bool>
     */
    private array $virtualDirectories;

    public function __construct(
        private FilesystemAdapter $base,
        FilesystemAdapter $overlay,
        private string $prefix,
    ) {
        // This is required because if we don't enforce it directory listings won't work as expected.
        if (! str_ends_with($this->prefix, '/')) {
            throw new \InvalidArgumentException('Prefix must end with /');
        }

        // The overlay is prefixed with the prefix.
        $this->overlay = new StripPrefixAdapter($overlay, $this->prefix);

        $virtualPath = "";
        $virtualDirectories = [];
        foreach (explode('/', trim($this->prefix, '/')) as $node) {
            $virtualPath = "$virtualPath/$node";
            $virtualDirectories[ltrim($virtualPath, '/')] = true;
        };
        $this->virtualDirectories = $virtualDirectories;
    }

    protected function getAdapter(string $rawPath, string $preparedPath): FilesystemAdapter
    {
        if (str_starts_with($rawPath, rtrim($this->prefix, '/'))) {
            $result = $this->overlay;
        } else {
            $result = $this->base;
        }
        return $result;
    }

    public function listContents(string $path, bool $deep): iterable
    {
        /**
         * In case a deep listing is requested we should check if the base path is a parent of the overlay directory.
         * If this is the case we need to put in the overlay.
         */
        $preparedPath = $this->preparePath($path);
        $primary = $this->getAdapter($path, $preparedPath);
        if ($primary === $this->overlay) {
            yield from $primary->listContents($path, $deep);
            return;
        }

        $overlayParent = dirname("/{$this->prefix}");

        $virtualDirectories = $this->virtualDirectories;

        // The primary adapter is the base adapter.
        foreach ($primary->listContents($path, $deep) as $entry) {
            yield $entry;
            if ($entry->isDir()) {
                unset($virtualDirectories[$entry->path()]);
            }
        }

        /**
         * We're doing a deep listing of an ancestor of the overlay.
         * This means we can fully include all entries from the overlay,
         * so we list it from its prefix, which will be stripped.
         */
        if (($deep && str_starts_with($overlayParent, $path))) {
            yield from $this->overlay->listContents($this->prefix, true);
        }

        foreach ($virtualDirectories as $virtualDirectory => $_) {
            if (
                ($deep && $this->pathIsAncestorOf($path, $virtualDirectory))
                || $this->pathIsParentOf($path, $virtualDirectory)) {
                yield new DirectoryAttributes($virtualDirectory);
            }
        }
    }

    private function pathIsAncestorOf(string $possibleAncestor, string $path): bool
    {
        // Root
        if (in_array($possibleAncestor, ["/", ""], true)) {
            return true;
        }
        return str_starts_with($path, $possibleAncestor);
    }

    private function pathIsParentOf(string $possibleParent, string $path): bool
    {
        if (in_array($possibleParent, ["/", ""], true) && ! str_contains($path, '/')) {
            return true;
        }
        if (dirname($path) === $possibleParent) {
            return true;
        }

        return false;
    }

    private function checkSourceAdapterMatchesDestination(string $source, string $destination): FilesystemAdapter
    {
        $preparedSource = $this->preparePath($source);
        $preparedDestination = $this->preparePath($destination);
        $sourceAdapter = $this->getAdapter($source, $preparedSource);
        $destinationAdapter = $this->getAdapter($destination, $preparedDestination);
        if ($sourceAdapter !== $destinationAdapter) {
            throw UnableToMoveFile::fromLocationTo($source, $destination);
        }
        return $sourceAdapter;
    }

    public function move(string $source, string $destination, Config $config): void
    {
        $this->checkSourceAdapterMatchesDestination($source, $destination)->move($source, $destination, $config);
    }

    public function deleteDirectory(string $path): void
    {
        if (isset($this->virtualDirectories[rtrim($path, '/')])) {
            throw UnableToDeleteFile::atLocation($path, "This path leads to the overlay");
        }
        parent::deleteDirectory($path);
    }

    public function directoryExists(string $path): bool
    {
        return isset($this->virtualDirectories[rtrim($path, '/')]) || parent::directoryExists($path);
    }

    public function copy(string $source, string $destination, Config $config): void
    {
        $this->checkSourceAdapterMatchesDestination($source, $destination)->copy($source, $destination, $config);
    }
}
