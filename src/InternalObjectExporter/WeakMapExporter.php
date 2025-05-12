<?php declare(strict_types = 1);

namespace ShipMonk\MemoryScanner\InternalObjectExporter;

use ShipMonk\MemoryScanner\InternalObjectExporter;
use WeakMap;

/**
 * @implements InternalObjectExporter<WeakMap<object, mixed>>
 */
final class WeakMapExporter implements InternalObjectExporter
{

    public function getClassName(): string
    {
        return WeakMap::class;
    }

    public function getProperties(object $object): array
    {
        $values = [];

        foreach ($object as $value) {
            $values[] = $value;
        }

        return [
            'values' => $values,
        ];
    }

}
