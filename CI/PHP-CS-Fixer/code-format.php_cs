<?php

require_once __DIR__ . '/../../libs/composer/vendor/autoload.php';

$finder = PhpCsFixer\Finder::create()
	->exclude(array(
		__DIR__ . '/../../setup/sql'
	))
	->in(array(
		__DIR__ .  '/../../src',
	))
;

return PhpCsFixer\Config::create()
	->setRules([
        '@PSR2' => true,
        'strict_param' => false,
        'cast_spaces' => true,
        'concat_space' => ['spacing' => 'one'],
        'unary_operator_spaces' => true,
        'function_typehint_space' => true,
        'return_type_declaration' => ['space_before' => 'one'],
        'binary_operator_spaces' => [
         'operators' => ['=' => 'align']
         ],
         'ordered_imports' => [
          'sort_algorithm' => 'alpha'
         ],
         'no_extra_consecutive_blank_lines' => true
	])
	->setFinder($finder);
