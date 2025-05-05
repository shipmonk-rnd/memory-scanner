<?php declare(strict_types = 1);

namespace ShipMonk\MemoryScanner\MemoryRootsProvider;

use ShipMonk\MemoryScanner\MemoryRootsProvider;
use function spl_autoload_functions;

final class AutoloadMemoryRootsProvider implements MemoryRootsProvider
{

    /**
     * @return list<mixed>
     */
    public function getRoots(): array
    {
        return spl_autoload_functions();
    }

}
