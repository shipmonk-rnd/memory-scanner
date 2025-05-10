<?php declare(strict_types = 1);

namespace ShipMonk\MemoryScanner\MemoryRootsProvider;

use ShipMonk\MemoryScanner\MemoryRootsProvider;
use function extension_loaded;
use function pcntl_signal_get_handler;

final class SignalHandlerMemoryRootsProvider implements MemoryRootsProvider
{

    public function getRoots(): array
    {
        $roots = [];

        if (extension_loaded('pcntl')) {
            for ($signalNumber = 1; $signalNumber < 32; $signalNumber++) {
                $roots["signal handler for signal {$signalNumber}"] = pcntl_signal_get_handler($signalNumber);
            }
        }

        return $roots;
    }

}
