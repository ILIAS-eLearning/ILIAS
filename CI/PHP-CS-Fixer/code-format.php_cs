<?php

require_once __DIR__ . '/../../libs/composer/vendor/autoload.php';

$finder = PhpCsFixer\Finder::create()
	->exclude(array(
		__DIR__ . '/../../setup/sql',
		__DIR__ . '/example'
	))
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

return (new PhpCsFixer\Config())
	->setRules([
        '@PSR12' => true,
        'strict_param' => false,
        'concat_space' => ['spacing' => 'one'],
        'function_typehint_space' => true
	])
	->setFinder($finder);
