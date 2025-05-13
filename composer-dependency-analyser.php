<?php declare(strict_types = 1);

use ShipMonk\ComposerDependencyAnalyser\Config\Configuration;
use ShipMonk\ComposerDependencyAnalyser\Config\ErrorType;

$config = new Configuration();
$config->ignoreErrorsOnExtensions(['ext-pcntl', 'ext-xdebug'], [ErrorType::SHADOW_DEPENDENCY]);

$config->ignoreErrorsOnPackagesAndPaths(
    packages: ['phpunit/phpunit', 'symfony/dependency-injection'],
    paths: [__DIR__ . '/src/Bridge/PHPUnit/ObjectDeallocationCheckerKernelTestCaseTrait.php'],
    errorTypes: [ErrorType::DEV_DEPENDENCY_IN_PROD],
);

return $config;
