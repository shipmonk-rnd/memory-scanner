<?php declare(strict_types = 1);

namespace ShipMonk\MemoryScanner\MemoryRootsProvider;

use ShipMonk\MemoryScanner\MemoryRootsProvider;
use function ob_list_handlers;

final class OutputBufferingHandlerMemoryRootsProvider implements MemoryRootsProvider
{

    public function getRoots(): array
    {
        return ob_list_handlers();
    }

}
