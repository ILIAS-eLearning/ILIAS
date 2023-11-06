<?php

require_once __DIR__ . '/../../vendor/composer/vendor/autoload.php';

$finder = PhpCsFixer\Finder::create()
	->exclude(array(
		__DIR__ . '/../../setup/sql',
		__DIR__ . '/example'
	))
	->in(array(
		__DIR__ .  '/../../cli',
		__DIR__ .  '/../../components/ILIAS'
	))
;

return (new PhpCsFixer\Config())
	->setRules([
        '@PSR12' => true,
        'strict_param' => false,
        'concat_space' => ['spacing' => 'one'],
        'function_typehint_space' => true,
        'function_declaration' => ['closure_fn_spacing' => 'none'],
        'binary_operator_spaces' => ['default' => 'single_space'],
        // 'types_spaces' => ['space' => 'single'],
	])
	->setFinder($finder);
