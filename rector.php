<?php

declare(strict_types = 1);

use Rector\Config\RectorConfig;
use Rector\CodingStyle\Rector\If_\NullableCompareToNullRector;
use Rector\CodeQuality\Rector\If_\ObjectExplicitBoolCompareRector;
use Rector\CodingStyle\Rector\Encapsed\EncapsedStringsToSprintfRector;
use Rector\DeadCode\Rector\Return_\RemoveDeadConditionAboveReturnRector;
use Rector\PHPUnit\CodeQuality\Rector\Class_\AddSeeTestAnnotationRector;
use Rector\CodingStyle\Rector\ClassLike\NewlineBetweenClassLikeStmtsRector;
use Rector\CodeQuality\Rector\Identical\FlipTypeControlToUseExclusiveTypeRector;

return RectorConfig::configure()
	->withPaths([__DIR__.'/src', __DIR__.'/tests'])
	->withPreparedSets(
		deadCode: true,
		codeQuality: true,
		codingStyle: true,
		typeDeclarations: true,
		typeDeclarationDocblocks: true,
		privatization: true,
		naming: true,
		namedArgs: true,
		instanceOf: true,
		if: true,
		earlyReturn: true,
		rectorPreset: true,
		phpunitCodeQuality: true,
		phpunitNarrowAsserts: true,
		phpunitMockToStub: true,
	)
	->withSkip([
		// conflicts with phpstan-strict-rules' ban on dynamic calls to static methods (PHPUnit's assert* are static)
		EncapsedStringsToSprintfRector::class,

		// eats guard clauses sitting above a stubbed-out return, e.g. the Missing check in TlvAttribute::dehydrate()
		RemoveDeadConditionAboveReturnRector::class,

		// formatting is php-cs-fixer's job, and its @PhpCsFixer set deliberately doesn't separate properties
		NewlineBetweenClassLikeStmtsRector::class,

		// all three rewrite the idiomatic `if (!$x = maybeNull())` assignment guard into a noisier instanceof/!== null check
		FlipTypeControlToUseExclusiveTypeRector::class,
		NullableCompareToNullRector::class,
		ObjectExplicitBoolCompareRector::class,

		// couples src docblocks to test class names
		AddSeeTestAnnotationRector::class,
	])
;
