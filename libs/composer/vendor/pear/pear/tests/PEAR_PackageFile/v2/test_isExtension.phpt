--TEST--
PEAR_PackageFile_Parser_v2->isExtension()
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'setup.php.inc';
$pf2 = new PEAR_PackageFile_v2_rw;
$a = $pf2->setPackageType('php');
$phpunit->assertTrue($a, 'first try');
$phpunit->assertFalse($pf2->isExtension('blah'), 'php type');
$a = $pf2->setPackageType('bundle');
$phpunit->assertTrue($a, 'second try');
$phpunit->assertFalse($pf2->isExtension('blah'), 'bundle type');
$a = $pf2->setPackageType('extbin');
$phpunit->assertTrue($a, 'third try');
$a = $pf2->setProvidesExtension('ablah');
$phpunit->assertTrue($a, 'bad set');
$phpunit->assertFalse($pf2->isExtension('blah'), 'extbin, bad');
$a = $pf2->setProvidesExtension('blah');
$phpunit->assertTrue($a, 'good set');
$phpunit->assertTrue($pf2->isExtension('blah'), 'extbin, good');

$a = $pf2->setPackageType('extsrc');
$phpunit->assertTrue($a, 'third try');
$a = $pf2->setProvidesExtension('ablah');
$phpunit->assertTrue($a, 'bad set s');
$phpunit->assertFalse($pf2->isExtension('blah'), 'extsrc, bad');
$a = $pf2->setProvidesExtension('blah');
$phpunit->assertTrue($a, 'good set s');
$phpunit->assertTrue($pf2->isExtension('blah'), 'extsrc, good');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
