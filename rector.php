<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use RectorLaravel\Set\LaravelSetList;
use Rector\Php83\Rector\ClassMethod\AddOverrideAttributeToOverriddenMethodsRector;


return RectorConfig::configure()
    ->withPaths([
	    __DIR__ . '/src',
	    __DIR__ . '/tests',
    ])
    ->withPhpSets(php82: true)
	->withPreparedSets(
		deadCode: true,
		codeQuality: true,
//		typeDeclarations: true,
		privatization: true,
		earlyReturn: true,
		strictBooleans: true,
	)
	->withSkip([
		AddOverrideAttributeToOverriddenMethodsRector::class,
	])
    ->withSets([
        LaravelSetList::LARAVEL_110,
    ])
    ->withTypeCoverageLevel(5)
//    ->withDeadCodeLevel(5)
//    ->withCodeQualityLevel(5)
	;
