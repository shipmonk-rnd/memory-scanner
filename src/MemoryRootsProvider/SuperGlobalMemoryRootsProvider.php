<?php declare(strict_types = 1);

namespace ShipMonk\MemoryScanner\MemoryRootsProvider;

use ShipMonk\MemoryScanner\MemoryRootsProvider;

final class SuperGlobalMemoryRootsProvider implements MemoryRootsProvider
{

    public function getRoots(): array
    {
        return [
            'superglobal variable $GLOBALS' => $GLOBALS,
            'superglobal variable $_POST' => $_POST,
            'superglobal variable $_GET' => $_GET,
            'superglobal variable $_SERVER' => $_SERVER,
            'superglobal variable $_SESSION' => $_SESSION ?? null,
            'superglobal variable $_ENV' => $_ENV ?? null, // @phpstan-ignore nullCoalesce.variable
            'superglobal variable $_COOKIE' => $_COOKIE,
            'superglobal variable $_FILES' => $_FILES,
            'superglobal variable $_REQUEST' => $_REQUEST,
        ];
    }

}
