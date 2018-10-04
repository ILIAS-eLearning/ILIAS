--TEST--
PEAR_PackageFile_Parser_v2->dependsOn()
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'setup.php.inc';
$pf = new PEAR_PackageFile_v2_rw;
$pf->addPackageDepWithChannel('required', 'test', 'pear.php.net');
$pf->addPackageDepWithChannel('required', 'PEAR_Server', 'pear.chiaraquartet.net');
$phpunit->assertTrue($pf->dependsOn('TesT', 'pear.php.net'), 'first test');
$phpunit->assertFalse($pf->dependsOn('TesT', 'pear.poop.net'), 'poop');
$phpunit->assertFalse($pf->dependsOn('foo', 'pear.php.net'), 'foo');
$phpunit->assertTrue($pf->dependsOn('pear_server', 'pear.chiaraquartet.net'), 'pear_server');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
