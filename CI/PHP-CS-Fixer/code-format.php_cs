<?php

require_once __DIR__ . '/../../libs/composer/vendor/autoload.php';

$finder = PhpCsFixer\Finder::create()
	->exclude(__DIR__ . '/../../setup/sql')
	->in(array(
		__DIR__ .  '/../../cron',
		__DIR__ .  '/../../include',
		__DIR__ .  '/../../Modules',
		__DIR__ .  '/../../Services',
		__DIR__ .  '/../../setup',
		__DIR__ .  '/../../src',
		__DIR__ .  '/../../tests'
	))
;

return PhpCsFixer\Config::create()
	->setRules([
        '@PSR2' => true,
        'strict_param' => false,
        'cast_spaces' => true,
        'concat_space' => ['spacing' => 'one'],
        'binary_operator_spaces' => true,
        'unary_operator_spaces' => true,
        'function_typehint_space' => true,
        'return_type_declaration' => ['space_before' => 'one'],
        'whitespace_after_comma_in_array' => true,
	])
	->setFinder($finder);
