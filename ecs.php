<?php

declare(strict_types=1);

// ecs.php
use PhpCsFixer\Fixer\ArrayNotation\ArraySyntaxFixer;
use PhpCsFixer\Fixer\ListNotation\ListSyntaxFixer;
use PhpCsFixer\Fixer\Whitespace\MethodChainingIndentationFixer;
use Symplify\CodingStandard\Fixer\Spacing\MethodChainingNewlineFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;

return ECSConfig::configure()
    ->withParallel()
    ->withPaths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])->withRootFiles()
    ->withConfiguredRule(ArraySyntaxFixer::class, ['syntax' => 'short'])
    ->withPreparedSets(psr12: true, spaces: true, perCs: true)
    ->withRules([ListSyntaxFixer::class])
    ->withSkip([
        MethodChainingIndentationFixer::class,
        MethodChainingNewlineFixer::class,
    ])
;
