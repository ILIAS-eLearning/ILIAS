<?php

require_once __DIR__ . '/libs/composer/vendor/autoload.php';

$finder = PhpCsFixer\Finder::create()
	->in(array(
		__DIR__ . '/cron',
		__DIR__ . '/include',
		__DIR__ . '/Modules',
		__DIR__ . '/Services',
		__DIR__ . '/setup',
		__DIR__ . '/src',
		__DIR__ . '/tests'
	))
;

return PhpCsFixer\Config::create()
	->setRules([
		'@PSR2' => true,
		'strict_param' => false,
		'array_syntax' => ['syntax' => 'short'],
	])
	->setFinder($finder);
