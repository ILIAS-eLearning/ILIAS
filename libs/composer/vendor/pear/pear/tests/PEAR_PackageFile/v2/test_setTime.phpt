--TEST--
PEAR_PackageFile_Parser_v2->setTime()
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
$pf->flattenFilelist();
$phpunit->assertNoErrors('valid xml parse');
$phpunit->assertIsa('PEAR_PackageFile_v2', $pf, 'return of valid parse');
$phpunit->assertFalse($pf->getTime(), 'pre-set');
$pf->setTime('12:34:56');
$phpunit->assertEquals('12:34:56', $pf->getTime(), 'set failed');
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
    array('package' => 'PEAR_PackageFile_v2', 'message' => 'Channel validator warning: field "date" - Release Date "2004-10-10" is not today')
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
