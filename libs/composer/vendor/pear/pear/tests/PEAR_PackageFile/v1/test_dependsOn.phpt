--TEST--
PEAR_PackageFile_Parser_v1->dependsOn()
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'setup.php.inc';
$pf = new PEAR_PackageFile_v1;
$pf->addPackageDep('test', '1.0', 'ge');
$phpunit->assertTrue($pf->dependsOn('TesT', 'pear.php.net'), 'first test');
$phpunit->assertFalse($pf->dependsOn('TesT', 'pear.poop.net'), 'second test');
$phpunit->assertFalse($pf->dependsOn('foo', 'pear.php.net'), 'last test');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
