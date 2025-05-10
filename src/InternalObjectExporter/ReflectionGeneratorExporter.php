<?php declare(strict_types = 1);

namespace ShipMonk\MemoryScanner\InternalObjectExporter;

use ReflectionGenerator;
use ShipMonk\MemoryScanner\InternalObjectExporter;

/**
 * @implements InternalObjectExporter<ReflectionGenerator>
 */
final class ReflectionGeneratorExporter implements InternalObjectExporter
{

    public function getClassName(): string
    {
        return ReflectionGenerator::class;
    }

    public function getProperties(object $object): array
    {
        return [
            'function' => $object->getFunction(),
            'this' => $object->getThis(),
            'trace' => $object->getTrace(),
        ];
    }

}
