<?php declare(strict_types = 1);

namespace ShipMonk\MemoryScanner\MemoryRootsProvider;

use ShipMonk\MemoryScanner\MemoryRootsProvider;

final class SuperGlobalMemoryRootsProvider implements MemoryRootsProvider
{

    public function getRoots(): array
    {
        return [
            '$GLOBALS' => $GLOBALS,
            '$_POST' => $_POST,
            '$_GET' => $_GET,
            '$_SERVER' => $_SERVER,
            '$_SESSION' => $_SESSION ?? null,
            '$_ENV' => $_ENV ?? null, // @phpstan-ignore nullCoalesce.variable
            '$_COOKIE' => $_COOKIE,
            '$_FILES' => $_FILES,
            '$_REQUEST' => $_REQUEST,
        ];
    }

}
