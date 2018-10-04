--TEST--
PEAR_PackageFile_Parser_v2 file subpackage identification
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'setup.php.inc';
$pathtopackagexml = dirname(__FILE__)  . DIRECTORY_SEPARATOR .
    'Parser'. DIRECTORY_SEPARATOR .
    'test_basicparse'. DIRECTORY_SEPARATOR . 'package2.xml';
$pf = $parser->parse(implode('', file($pathtopackagexml)), $pathtopackagexml);
$pfa = &$pf->getRW();
$pf = &$pfa;
$pf->flattenFilelist();
$phpunit->assertNoErrors('valid xml parse');
$phpunit->assertIsa('PEAR_PackageFile_v2', $pf, 'return of valid parse');
$phpunit->assertEquals('pear.php.net', $pf->getChannel(), 'pre-set');
$pf2 = new PEAR_PackageFile_v2_rw;
$pf2->setConfig($config);
$pf2->addSubpackageDepWithChannel('required', 'foo', 'blah');
$phpunit->assertFalse($pf2->isSubpackage($pf), 'blah/foo -> pear.php.net/PEAR');
$phpunit->assertFalse($pf->isSubpackage($pf2), 'blah/foo -> pear.php.net/PEAR reverse');
$phpunit->assertFalse($pf2->isSubpackageOf($pf), 'blah/foo -> pear.php.net/PEAR is');
$phpunit->assertFalse($pf->isSubpackageOf($pf2), 'blah/foo -> pear.php.net/PEAR reverse is');

$pf2->clearDeps();
$pf2->addSubpackageDepWithChannel('required', 'teSt', 'pear.php.net');
$phpunit->assertTrue($pf2->isSubpackage($pf), 'ok 1 case-insensitive');
$phpunit->assertFalse($pf->isSubpackage($pf2), 'ok 2 case-insensitive');
$phpunit->assertFalse($pf2->isSubpackageOf($pf), 'ok 3 case-insensitive');
$phpunit->assertTrue($pf->isSubpackageOf($pf2), 'ok 4 case-insensitive');

$pf2->clearDeps();
$pf2->addSubpackageDepWithChannel('optional', 'teSt', 'pear.php.net');
$phpunit->assertTrue($pf2->isSubpackage($pf), 'ok 1 case-insensitive');
$phpunit->assertFalse($pf->isSubpackage($pf2), 'ok 2 case-insensitive');
$phpunit->assertFalse($pf2->isSubpackageOf($pf), 'ok 3 case-insensitive');
$phpunit->assertTrue($pf->isSubpackageOf($pf2), 'ok 4 case-insensitive');

$pf2->clearDeps();
$pf2->addDependencyGroup('foo', 'foo group');
$pf2->addGroupPackageDepWithChannel('subpackage', 'foo', 'Test', 'pear.php.net');
$phpunit->assertTrue($pf2->isSubpackage($pf), 'ok 1 case-insensitive');
$phpunit->assertFalse($pf->isSubpackage($pf2), 'ok 2 case-insensitive');
$phpunit->assertFalse($pf2->isSubpackageOf($pf), 'ok 3 case-insensitive');
$phpunit->assertTrue($pf->isSubpackageOf($pf2), 'ok 4 case-insensitive');

$pf->setUri('http://www.example.com/blah.tgz');
$pf2->clearDeps();
$pf2->addSubpackageDepWithURI('required', 'teSt', 'http://www.example.com/foo.tgz');
$phpunit->assertFalse($pf2->isSubpackage($pf), 'ok 1 case-insensitive');
$phpunit->assertFalse($pf->isSubpackage($pf2), 'ok 2 case-insensitive');
$phpunit->assertFalse($pf2->isSubpackageOf($pf), 'ok 3 case-insensitive');
$phpunit->assertFalse($pf->isSubpackageOf($pf2), 'ok 4 case-insensitive');

$pf2->clearDeps();
$pf2->addSubpackageDepWithURI('required', 'teSt', 'http://www.example.com/blah.tgz');
$phpunit->assertTrue($pf2->isSubpackage($pf), 'ok 1 case-insensitive');
$phpunit->assertFalse($pf->isSubpackage($pf2), 'ok 2 case-insensitive');
$phpunit->assertFalse($pf2->isSubpackageOf($pf), 'ok 3 case-insensitive');
$phpunit->assertTrue($pf->isSubpackageOf($pf2), 'ok 4 case-insensitive');

$pf2->clearDeps();
$pf2->addSubpackageDepWithURI('optional', 'teSt', 'http://www.example.com/blah.tgz');
$phpunit->assertTrue($pf2->isSubpackage($pf), 'ok 1 case-insensitive');
$phpunit->assertFalse($pf->isSubpackage($pf2), 'ok 2 case-insensitive');
$phpunit->assertFalse($pf2->isSubpackageOf($pf), 'ok 3 case-insensitive');
$phpunit->assertTrue($pf->isSubpackageOf($pf2), 'ok 4 case-insensitive');

$pf2->clearDeps();
$pf2->addDependencyGroup('foo', 'foo group');
$pf2->addGroupPackageDepWithURI('subpackage', 'foo', 'tesT', 'http://www.example.com/blah.tgz');
$phpunit->assertTrue($pf2->isSubpackage($pf), 'ok 1 case-insensitive');
$phpunit->assertFalse($pf->isSubpackage($pf2), 'ok 2 case-insensitive');
$phpunit->assertFalse($pf2->isSubpackageOf($pf), 'ok 3 case-insensitive');
$phpunit->assertTrue($pf->isSubpackageOf($pf2), 'ok 4 case-insensitive');

echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
