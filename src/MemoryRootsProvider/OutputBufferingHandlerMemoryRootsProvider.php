<?php declare(strict_types = 1);

namespace ShipMonk\MemoryScanner\MemoryRootsProvider;

use ShipMonk\MemoryScanner\MemoryRootsProvider;
use function ob_list_handlers;

final class OutputBufferingHandlerMemoryRootsProvider implements MemoryRootsProvider
{

    public function getRoots(): array
    {
        $roots = [];

        foreach (ob_list_handlers() as $index => $autoloadFunction) {
            $roots["output buffering handler #{$index}"] = $autoloadFunction;
        }

        return $roots;
    }

}
