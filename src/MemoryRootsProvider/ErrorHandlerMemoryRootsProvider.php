<?php declare(strict_types = 1);

namespace ShipMonk\MemoryScanner\MemoryRootsProvider;

use ShipMonk\MemoryScanner\MemoryRootsProvider;
use function count;
use function restore_error_handler;
use function set_error_handler;

final class ErrorHandlerMemoryRootsProvider implements MemoryRootsProvider
{

    /**
     * @return list<callable>
     */
    public function getRoots(): array
    {
        $roots = [];

        while (true) {
            $errorHandler = $this->getErrorHandler();

            if ($errorHandler === null) {
                break;
            }

            $roots[] = $errorHandler;
            restore_error_handler();
        }

        for ($i = count($roots) - 1; $i >= 0; $i--) {
            set_error_handler($roots[$i]);
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
