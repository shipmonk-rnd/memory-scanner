<?php declare(strict_types = 1);

namespace ShipMonk\MemoryScanner;

/**
 * @template T of object
 */
interface InternalObjectExporter
{

    /**
     * @return class-string<T>
     */
    public function getClassName(): string;

    /**
     * @param T $object
     * @return array<string, mixed>
     */
    public function getProperties(object $object): array;

}
