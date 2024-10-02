<?php

$finder = (new PhpCsFixer\Finder())
    ->in([__DIR__ . '/src', __DIR__ . '/tests'])
;

return (new PhpCsFixer\Config())
    ->setRules([
        '@PSR2' => true,
        '@Symfony' => true,
        'concat_space' => ['spacing' => 'one'],
        'global_namespace_import' => ['import_classes' => true, 'import_constants' => true, 'import_functions' => false],
        'no_extra_blank_lines' => [
            'tokens' => [
                'case', 'continue', 'curly_brace_block', 'default', 'extra', 'parenthesis_brace_block', 'return',
                'square_brace_block', 'switch', 'throw', 'use', 'use_trait',
            ]
        ],
        'trailing_comma_in_multiline' => ['elements' => ['arrays', 'match', 'parameters']],
        'ordered_imports' => ['imports_order' => ['class', 'const', 'function'], 'sort_algorithm' => 'alpha'],
        'phpdoc_order' => true,
        'phpdoc_to_comment' => ['ignored_tags' => ['todo', 'var']],
        'phpdoc_trim_consecutive_blank_line_separation' => true,
        'single_line_throw' => false,
        'yoda_style' => ['equal' => false, 'identical' => false, 'less_and_greater' => false],
    ])
    ->setFinder($finder)
;
