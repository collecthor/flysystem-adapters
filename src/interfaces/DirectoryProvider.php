<?php

declare(strict_types=1);

namespace Collecthor\FlySystem\interfaces;

use IteratorAggregate;
use League\Flysystem\DirectoryAttributes;

/**
 * @extends IteratorAggregate<string, DirectoryAttributes>
 */
interface DirectoryProvider extends IteratorAggregate
{
    public function hasTopLevelDirectory(string $name): bool;
}
