<?php

declare(strict_types=1);

// ecs.php
use PhpCsFixer\Fixer\ArrayNotation\ArraySyntaxFixer;
use PhpCsFixer\Fixer\ListNotation\ListSyntaxFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;
use Symplify\EasyCodingStandard\ValueObject\Option;
use Symplify\EasyCodingStandard\ValueObject\Set\SetList;

return ECSConfig::configure()
    ->withParallel()
    ->withPaths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])->withRootFiles()
    ->withConfiguredRule(ArraySyntaxFixer::class, ['syntax' => 'short'])
    ->withPreparedSets(psr12: true, spaces: true, strict: true)
    ->withPhpCsFixerSets(perCS20: true)
    ->withRules([ListSyntaxFixer::class])
;
