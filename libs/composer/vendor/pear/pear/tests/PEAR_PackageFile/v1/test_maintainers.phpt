--TEST--
PEAR_PackageFile_Parser_v1 maintainer management
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
function assertValidation(&$pf)
{
    global $phpunit, $fakelog;
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
}
$phpunit->assertNoErrors('valid xml parse');
$phpunit->assertIsa('PEAR_PackageFile_v1', $pf, 'return of valid parse');
$phpunit->assertEquals(array (
  0 => 
  array (
    'handle' => 'cellog',
    'role' => 'lead',
    'email' => 'cellog@php.net',
    'name' => 'Greg Beaver',
  ),
), $pf->getMaintainers(), 'wrong maintainers');
$pf->updateMaintainer('developer', 'cellog', 'Greg Beaver', 'cellog@php.net');
$phpunit->assertEquals(array (
  0 => 
  array (
    'handle' => 'cellog',
    'role' => 'developer',
    'email' => 'cellog@php.net',
    'name' => 'Greg Beaver',
  ),
), $pf->getMaintainers(), 'wrong maintainers, after update');
$pf->updateMaintainer('lead', 'test', 'Greg Beaver', 'cellog@php.net');
$phpunit->assertEquals(array (
  0 => 
  array (
    'handle' => 'cellog',
    'role' => 'developer',
    'email' => 'cellog@php.net',
    'name' => 'Greg Beaver',
  ),
  1 => 
  array (
    'handle' => 'test',
    'role' => 'lead',
    'email' => 'cellog@php.net',
    'name' => 'Greg Beaver',
  ),
), $pf->getMaintainers(), 'wrong maintainers, after update as add');
$pf->addMaintainer('contributor', 'test2', 'Greg Beaver', 'cellog@php.net');
$phpunit->assertEquals(array (
  0 => 
  array (
    'handle' => 'cellog',
    'role' => 'developer',
    'email' => 'cellog@php.net',
    'name' => 'Greg Beaver',
  ),
  1 => 
  array (
    'handle' => 'test',
    'role' => 'lead',
    'email' => 'cellog@php.net',
    'name' => 'Greg Beaver',
  ),
  2 => 
  array (
    'handle' => 'test2',
    'role' => 'contributor',
    'email' => 'cellog@php.net',
    'name' => 'Greg Beaver',
  ),
), $pf->getMaintainers(), 'wrong maintainers, after add');
$phpunit->assertFalse($pf->deleteMaintainer('scrooge'), 'invalid delete');
$phpunit->assertTrue($pf->deleteMaintainer('cellog'), 'valid delete');
$phpunit->assertEquals(array (
  0 => 
  array (
    'handle' => 'test',
    'role' => 'lead',
    'email' => 'cellog@php.net',
    'name' => 'Greg Beaver',
  ),
  1 => 
  array (
    'handle' => 'test2',
    'role' => 'contributor',
    'email' => 'cellog@php.net',
    'name' => 'Greg Beaver',
  ),
), $pf->getMaintainers(), 'wrong maintainers, after delete');
assertValidation($pf);
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
