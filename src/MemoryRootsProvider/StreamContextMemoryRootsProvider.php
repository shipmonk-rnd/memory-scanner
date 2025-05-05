<?php declare(strict_types = 1);

namespace ShipMonk\MemoryScanner\MemoryRootsProvider;

use ShipMonk\MemoryScanner\MemoryRootsProvider;
use function stream_context_get_default;
use function stream_context_get_params;

final class StreamContextMemoryRootsProvider implements MemoryRootsProvider
{

    public function getRoots(): array
    {
        return stream_context_get_params(stream_context_get_default());
    }

}
