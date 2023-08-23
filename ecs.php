<?php

declare(strict_types=1);

// ecs.php
use PhpCsFixer\Fixer\ArrayNotation\ArraySyntaxFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;
use Symplify\EasyCodingStandard\ValueObject\Option;
use Symplify\EasyCodingStandard\ValueObject\Set\SetList;

return static function (ECSConfig $config): void {
    // Parallel
    $config->parallel();

    // Paths
    $config->paths([__DIR__ . '/src', __DIR__ . '/tests', __DIR__ . '/ecs.php']);

    // A. full sets
    $config->import(SetList::PSR_12);

    // B. standalone rule
    $config->ruleWithConfiguration(ArraySyntaxFixer::class, [
        'syntax' => 'short',
    ]);
};
