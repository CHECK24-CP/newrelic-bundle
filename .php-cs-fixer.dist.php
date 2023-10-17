<?php

declare(strict_types=1);

$header = <<<'EOF'
This file is part of CHECK24 New Relic bundle.

(c) CHECK24 - Radhi Guennichi <mohamed.guennichi@check24.de>

For the full copyright and license information, please view the LICENSE
file that was distributed with this source code.
EOF;

$finder = (new PhpCsFixer\Finder())
    ->in([__DIR__ . '/src', __DIR__ . '/tests', __DIR__ . '/config'])
;

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@Symfony' => true,
        '@Symfony:risky' => true,
        'array_syntax' => ['syntax' => 'short'],
        'concat_space' => ['spacing' => 'one'],
        'phpdoc_align' => ['align' => 'left'],
        'ordered_imports' => true,
        'header_comment' => ['header' => $header],
        'trailing_comma_in_multiline' => ['elements' => ['arrays', 'arguments', 'parameters', 'match']],
        'yoda_style' => true,
        'declare_strict_types' => true,
        'native_function_invocation' => true,
        'linebreak_after_opening_tag' => true,
        'modernize_types_casting' => true,
        'no_superfluous_elseif' => true,
        'no_useless_else' => true,
        'phpdoc_order' => true,
        'psr_autoloading' => true,
    ])
    ->setFinder($finder)
;
