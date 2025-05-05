<?php declare(strict_types = 1);

namespace ShipMonk\MemoryScanner\InternalObjectExporter;

use ReflectionFiber;
use ShipMonk\MemoryScanner\InternalObjectExporter;

/**
 * @implements InternalObjectExporter<ReflectionFiber>
 */
final class ReflectionFiberExporter implements InternalObjectExporter
{

    public function getClassName(): string
    {
        return ReflectionFiber::class;
    }

    public function getProperties(object $object): array
    {
        return [
            'callable' => $object->getCallable(),
            'trace' => $object->getTrace(),
        ];
    }

}
