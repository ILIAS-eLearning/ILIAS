--TEST--
PEAR_Validate->validPackageName()
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'setup.php.inc';
$res = $val->validPackageName('foo');
$phpunit->assertTrue($res, 'foo');
$res = $val->validPackageName('fooOO');
$phpunit->assertTrue($res, 'fooOO');
$res = $val->validPackageName('f9ooOO');
$phpunit->assertTrue($res, 'f9ooOO');
$res = $val->validPackageName('f9oo_OO');
$phpunit->assertTrue($res, 'f9oo_OO');
$res = $val->validPackageName('f9oo_OO2');
$phpunit->assertTrue($res, 'f9oo_OO2');
$res = $val->validPackageName('F9oo_OO2');
$phpunit->assertTrue($res, 'F9oo_OO2');
$res = $val->validPackageName('foo.com_Validate', 'foo.com_Validate');
$phpunit->assertTrue($res, 'foo.com_Validate valid');

$res = $val->validPackageName('_F9oo_OO2');
$phpunit->assertFalse($res, '_F9oo_OO2');
$res = $val->validPackageName('2_F9oo_OO2');
$phpunit->assertFalse($res, '2_F9oo_OO2');
$res = $val->validPackageName('foo.com_Validate');
$phpunit->assertFalse($res, 'foo.com_Validate');
$res = $val->validPackageName('foo.com_Validate', 'grnok');
$phpunit->assertFalse($res, 'foo.com_Validate grnok');
$res = $val->validPackageName('.foo.com_Validate', '.foo.com_Validate');
$phpunit->assertFalse($res, '.foo.com_Validate invalid');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
