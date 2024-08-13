<?php

declare(strict_types=1);

namespace Collecthor\FlySystem;

use League\Flysystem\FilesystemAdapter;

final class AdapterStack
{
    private bool $finalized = false;
    public function __construct(private FilesystemAdapter $adapter)
    {
    }

    /**
     * @param \Closure(FileSystemAdapter):FilesystemAdapter $closure
     * @return $this
     */
    public function append(\Closure $closure): self
    {
        if ($this->finalized) {
            throw new \RuntimeException('This stack is finalized');
        }
        $this->adapter = $closure($this->adapter);
        return $this;
    }

    public function finalize(): FilesystemAdapter
    {
        $this->finalized = true;
        return $this->adapter;
    }
}
