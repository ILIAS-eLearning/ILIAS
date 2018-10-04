--TEST--
PEAR_Validate::validVersion()
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'setup.php.inc';
$res = PEAR_Validate::validVersion('1');
$phpunit->assertTrue($res, '1');
$res = PEAR_Validate::validVersion('1.0');
$phpunit->assertTrue($res, '1.0');
$res = PEAR_Validate::validVersion('1.0.0');
$phpunit->assertTrue($res, '1.0.0');

$res = PEAR_Validate::validVersion('1RC1');
$phpunit->assertTrue($res, '1RC1');
$res = PEAR_Validate::validVersion('1.0RC1');
$phpunit->assertTrue($res, '1.0RC1');
$res = PEAR_Validate::validVersion('1.0.234RC1');
$phpunit->assertTrue($res, '1.0.234RC1');
$res = PEAR_Validate::validVersion('1.0a1');
$phpunit->assertTrue($res, '1.0a1');
$res = PEAR_Validate::validVersion('1.0a');
$phpunit->assertTrue($res, '1.0a');

$res = PEAR_Validate::validVersion('1231.0a');
$phpunit->assertTrue($res, '1231.0a');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
