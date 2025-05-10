<?php declare(strict_types = 1);

namespace ShipMonk\MemoryScanner\MemoryRootsProvider;

use ShipMonk\MemoryScanner\MemoryRootsProvider;
use function spl_autoload_functions;

final class AutoloadMemoryRootsProvider implements MemoryRootsProvider
{

    public function getRoots(): array
    {
        $roots = [];

        foreach (spl_autoload_functions() as $index => $autoloadFunction) {
            $roots["autoload function #{$index}"] = $autoloadFunction;
        }

        return $roots;
    }

}
