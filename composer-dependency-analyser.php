<?php declare(strict_types = 1);

use ShipMonk\ComposerDependencyAnalyser\Config\Configuration;
use ShipMonk\ComposerDependencyAnalyser\Config\ErrorType;

$config = new Configuration();
$config->ignoreErrorsOnExtensions(['ext-pcntl', 'ext-xdebug'], [ErrorType::SHADOW_DEPENDENCY]);

return $config;
