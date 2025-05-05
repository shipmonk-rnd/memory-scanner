<?php declare(strict_types = 1);

namespace ShipMonk\MemoryScanner\MemoryRootsProvider;

use ShipMonk\MemoryScanner\MemoryRootsProvider;
use function array_reverse;
use function count;
use function restore_error_handler;
use function set_error_handler;

final class ErrorHandlerMemoryRootsProvider implements MemoryRootsProvider
{

    public function getRoots(): array
    {
        $roots = [];

        while (true) {
            $errorHandler = $this->getErrorHandler();

            if ($errorHandler === null) {
                break;
            }

            $rootIndex = count($roots);
            $roots["error handler #{$rootIndex}"] = $errorHandler;
            restore_error_handler();
        }

        unset($errorHandler);

        foreach (array_reverse($roots) as $errorHandler) {
            set_error_handler($errorHandler);
        }

        return $roots;
    }

    private function getErrorHandler(): ?callable
    {
        $errorHandler = set_error_handler(null);
        restore_error_handler();

        return $errorHandler;
    }

}
