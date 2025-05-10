<?php declare(strict_types = 1);

namespace ShipMonk\MemoryScanner;

interface MemoryRootsProvider
{

    /**
     * @return array<string, mixed>
     */
    public function getRoots(): array;

}
