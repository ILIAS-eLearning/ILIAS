<?php

$finder = PhpCsFixer\Finder::create()
	->exclude(array(
		__DIR__ . '/../../components/ILIAS/setup_/sql',
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
        'cast_spaces' => true,
        'concat_space' => ['spacing' => 'one'],
        'type_declaration_spaces' => true,
        'function_declaration' => ['closure_fn_spacing' => 'none'],
        'binary_operator_spaces' => ['default' => 'single_space'],
        // 'types_spaces' => ['space' => 'single'],
	])
	->setFinder($finder);
