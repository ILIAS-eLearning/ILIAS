--TEST--
PEAR_PackageFile_Parser_v1 dependencies
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
    'test_basicparse'. DIRECTORY_SEPARATOR . 'package.xml';
$pf = &$parser->parse(implode('', file($pathtopackagexml)), $pathtopackagexml);
$phpunit->assertNoErrors('valid xml parse');
$phpunit->assertIsa('PEAR_PackageFile_v1', $pf, 'return of valid parse');
$phpunit->showall();
$phpunit->assertFalse($pf->getDeps(), 'pre-set');
//$pf = new PEAR_PackageFile_v1;
$pf->addPhpDep('4.5.6', 'ge');
$phpunit->assertEquals(array (
  0 => 
  array (
    'type' => 'php',
    'rel' => 'ge',
    'version' => '4.5.6',
  ),
), $pf->getDeps(), 'php set failed');
$pf->clearDeps();
$phpunit->assertFalse($pf->getDeps(), 'cleardeps');
$pf->addPackageDep('foo', '1.0', 'not');
$phpunit->assertEquals(array (
  0 => 
  array (
    'type' => 'pkg',
    'name' => 'foo',
    'rel' => 'not',
    'optional' => 'no',
  ),
), $pf->getDeps(), 'package set failed');
$pf->clearDeps();
$pf->addExtensionDep('flah', '2.0', 'le');
$phpunit->assertEquals(array (
  0 => 
  array (
    'type' => 'ext',
    'name' => 'flah',
    'rel' => 'le',
    'version' => '2.0',
    'optional' => 'no',
  ),
), $pf->getDeps(), 'extension set failed');
$result = $pf->validate(PEAR_VALIDATE_NORMAL);
$phpunit->assertEquals(array(), $fakelog->getLog(), 'normal validate empty log');
$phpunit->assertNoErrors('after validation');
$result = $pf->validate(PEAR_VALIDATE_INSTALLING);
$phpunit->assertEquals(array(), $fakelog->getLog(), 'installing validate empty log');
$phpunit->assertNoErrors('after validation');
$result = $pf->validate(PEAR_VALIDATE_DOWNLOADING);
$phpunit->assertEquals(array(), $fakelog->getLog(), 'downloading validate empty log');
$phpunit->assertNoErrors('after validation');
$result = $pf->validate(PEAR_VALIDATE_PACKAGING);
$phpunit->assertErrors(array(
    array('package' => 'PEAR_PackageFile_v1', 'message' => 'Channel validator error: field "date" - Release Date "2004-10-10" is not today')
), 'after full packaging validation');
$phpunit->assertEquals(array (
  0 => 
  array (
    0 => 1,
    1 => 'Analyzing test/test.php',
  ),
  1 => 
  array (
    0 => 1,
    1 => 'Analyzing test/test2.php',
  ),
  2 => 
  array (
    0 => 1,
    1 => 'Analyzing test/test3.php',
  ),
), $fakelog->getLog(), 'packaging validate full log');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
