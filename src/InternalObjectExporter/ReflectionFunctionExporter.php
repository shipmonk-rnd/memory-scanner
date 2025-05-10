<?php declare(strict_types = 1);

namespace ShipMonk\MemoryScanner\InternalObjectExporter;

use ReflectionFunction;
use ShipMonk\MemoryScanner\InternalObjectExporter;

/**
 * @implements InternalObjectExporter<ReflectionFunction>
 */
final class ReflectionFunctionExporter implements InternalObjectExporter
{

    public function getClassName(): string
    {
        return ReflectionFunction::class;
    }

    public function getProperties(object $object): array
    {
        return [
            'closure' => $object->getClosure(),
        ];
    }

}
