--TEST--
clear-cache command
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php

require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'setup.php.inc';
$rest = new test_PEAR_REST($config);
$rest->saveCache('http://www.example.com/hi', 'hi', array('hi', date('r')));
$rest->saveCache('http://www.example.com/hi2', 'hi2', array('hi2', date('r')));
$e = $command->run('clear-cache', array(), array());
$phpunit->assertNoErrors('clear-cache');
$phpunit->showall();
$phpunit->assertEquals(array (
  0 =>
  array (
    'info' => 'reading directory ' . $config->get('cache_dir') . '
4 cache entries cleared',
    'cmd' => 'clear-cache',
  ),
), $fakelog->getLog(), 'clear-cache log');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(dirname(__FILE__)) . '/teardown.php.inc';
?>
--EXPECT--
tests done
