--TEST--
PEAR_PackageFile_Parser_v2->getFileContents
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'setup.php.inc';
$pathtopackagetgz = dirname(__FILE__)  . DIRECTORY_SEPARATOR .
    'test_getFileContents'. DIRECTORY_SEPARATOR . 'test-1.4.0a1.tgz';
$pathtopackagexml = dirname(__FILE__)  . DIRECTORY_SEPARATOR .
    'test_getFileContents'. DIRECTORY_SEPARATOR . 'package2.xml';
require_once 'PEAR/PackageFile.php';
$pkg = new PEAR_PackageFile($config);
$pf = &$pkg->fromTgzFile($pathtopackagetgz, PEAR_VALIDATE_NORMAL);
$phpunit->assertNoErrors('valid xml parse');
$phpunit->assertIsa('PEAR_PackageFile_v2', $pf, 'return of valid parse');
$phpunit->assertEquals('this is php!', $pf->getFileContents('test.php'), 'wrong contents');
$phpunit->assertEquals('hello there girly-man', $pf->getFileContents('template.spec'), 'wrong contents');
$pf = &$pkg->fromPackageFile($pathtopackagexml, PEAR_VALIDATE_NORMAL);
$phpunit->assertNoErrors('valid xml parse');
$phpunit->assertIsa('PEAR_PackageFile_v2', $pf, 'return of valid parse');
$phpunit->assertEquals('this is php!', $pf->getFileContents('test.php'), 'wrong contents');
$phpunit->assertEquals('hello there girly-man', $pf->getFileContents('template.spec'), 'wrong contents');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
