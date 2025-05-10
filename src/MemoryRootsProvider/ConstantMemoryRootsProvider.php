<?php declare(strict_types = 1);

namespace ShipMonk\MemoryScanner\MemoryRootsProvider;

use ShipMonk\MemoryScanner\MemoryRootsProvider;
use function get_defined_constants;

final class ConstantMemoryRootsProvider implements MemoryRootsProvider
{

    public function getRoots(): array
    {
        $roots = [];

        foreach (get_defined_constants(categorize: true)['user'] ?? [] as $constantName => $constantValue) {
            $roots["global constant {$constantName}"] = $constantValue;
        }

        return $roots;
    }

}
