<?php declare(strict_types = 1);

namespace ShipMonk\MemoryScanner\InternalObjectExporter;

use Closure;
use ReflectionFunction;
use ShipMonk\MemoryScanner\InternalObjectExporter;
use function array_diff_key;

/**
 * @implements InternalObjectExporter<Closure>
 */
final class ClosureExporter implements InternalObjectExporter
{

    public function getClassName(): string
    {
        return Closure::class;
    }

    public function getProperties(object $object): array
    {
        $reflection = new ReflectionFunction($object);

        return [
            'this' => $reflection->getClosureThis(),
            'use' => $reflection->getClosureUsedVariables(),
            'static' => array_diff_key($reflection->getStaticVariables(), $reflection->getClosureUsedVariables()),
        ];
    }

}
