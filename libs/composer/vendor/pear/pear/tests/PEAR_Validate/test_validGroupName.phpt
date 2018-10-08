--TEST--
PEAR_Validate::validGroupName()
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'setup.php.inc';
$res = PEAR_Validate::validGroupName('foo');
$phpunit->assertTrue($res, 'foo');
$res = PEAR_Validate::validGroupName('fooOO');
$phpunit->assertTrue($res, 'fooOO');
$res = PEAR_Validate::validGroupName('f9ooOO');
$phpunit->assertTrue($res, 'f9ooOO');
$res = PEAR_Validate::validGroupName('f9oo_OO');
$phpunit->assertTrue($res, 'f9oo_OO');
$res = PEAR_Validate::validGroupName('f9oo_OO2');
$phpunit->assertTrue($res, 'f9oo_OO2');
$res = PEAR_Validate::validGroupName('F9oo_OO2');
$phpunit->assertTrue($res, 'F9oo_OO2');

$res = PEAR_Validate::validGroupName('_F9oo_OO2');
$phpunit->assertFalse($res, '_F9oo_OO2');
$res = PEAR_Validate::validGroupName('2_F9oo_OO2');
$phpunit->assertFalse($res, '2_F9oo_OO2');
$res = PEAR_Validate::validGroupName('foo.com_Validate');
$phpunit->assertFalse($res, 'foo.com_Validate');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
