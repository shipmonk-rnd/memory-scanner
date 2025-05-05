<?php declare(strict_types = 1);

namespace ShipMonk\MemoryScanner;

final class ObjectReference
{

    /**
     * @param list<string|int> $path
     */
    public function __construct(
        public readonly ?object $source,
        public readonly array $path,
    )
    {
    }

}
