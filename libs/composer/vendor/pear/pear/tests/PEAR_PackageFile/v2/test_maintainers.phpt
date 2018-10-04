--TEST--
PEAR_PackageFile_Parser_v2 maintainer management
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
}
$phpunit->assertNoErrors('valid xml parse');
$phpunit->assertIsa('PEAR_PackageFile_v2', $pf, 'return of valid parse');
$pfa = &$pf->getRW();
$pf = &$pfa;
$phpunit->assertEquals(array (
  0 => 
  array (
    'name' => 'Greg Beaver',
    'email' => 'cellog@php.net',
    'active' => 'yes',
    'handle' => 'cellog',
    'role' => 'lead',
  ),
), $pf->getMaintainers(), 'wrong maintainers');
$pf->addMaintainer('lead', 'fake', 'Fake Faker', 'fake@example.com');
$pf->validate();
$phpunit->assertNoErrors('first');
$pf->updateMaintainer('developer', 'cellog', 'Greg Beaver', 'cellog@php.net');
$phpunit->showall();
$phpunit->assertEquals(array (
  0 => 
  array (
    'name' => 'Fake Faker',
    'email' => 'fake@example.com',
    'active' => 'yes',
    'handle' => 'fake',
    'role' => 'lead',
  ),
  1 => 
  array (
    'name' => 'Greg Beaver',
    'email' => 'cellog@php.net',
    'active' => 'yes',
    'handle' => 'cellog',
    'role' => 'developer',
  ),
), $pf->getMaintainers(), 'wrong maintainers, after update');
$pf->updateMaintainer('lead', 'test', 'Greg Beaver', 'cellog@php.net');
$phpunit->assertEquals(array (
  0 => 
  array (
    'name' => 'Fake Faker',
    'email' => 'fake@example.com',
    'active' => 'yes',
    'handle' => 'fake',
    'role' => 'lead',
  ),
  1 => 
  array (
    'name' => 'Greg Beaver',
    'email' => 'cellog@php.net',
    'active' => 'yes',
    'handle' => 'test',
    'role' => 'lead',
  ),
  2 => 
  array (
    'name' => 'Greg Beaver',
    'email' => 'cellog@php.net',
    'active' => 'yes',
    'handle' => 'cellog',
    'role' => 'developer',
  ),
), $pf->getMaintainers(), 'wrong maintainers, after update as add');
$pf->addMaintainer('contributor', 'test2', 'Greg Beaver', 'cellog@php.net');
$phpunit->assertEquals(array (
  0 => 
  array (
    'name' => 'Fake Faker',
    'email' => 'fake@example.com',
    'active' => 'yes',
    'handle' => 'fake',
    'role' => 'lead',
  ),
  1 => 
  array (
    'name' => 'Greg Beaver',
    'email' => 'cellog@php.net',
    'active' => 'yes',
    'handle' => 'test',
    'role' => 'lead',
  ),
  2 => 
  array (
    'name' => 'Greg Beaver',
    'email' => 'cellog@php.net',
    'active' => 'yes',
    'handle' => 'cellog',
    'role' => 'developer',
  ),
  3 => 
  array (
    'name' => 'Greg Beaver',
    'email' => 'cellog@php.net',
    'active' => 'yes',
    'handle' => 'test2',
    'role' => 'contributor',
  ),
), $pf->getMaintainers(), 'wrong maintainers, after add');
$phpunit->assertFalse($pf->deleteMaintainer('scrooge'), 'invalid delete');
$phpunit->assertTrue($pf->deleteMaintainer('cellog'), 'valid delete');
$phpunit->assertEquals(array (
  0 => 
  array (
    'name' => 'Fake Faker',
    'email' => 'fake@example.com',
    'active' => 'yes',
    'handle' => 'fake',
    'role' => 'lead',
  ),
  1 => 
  array (
    'name' => 'Greg Beaver',
    'email' => 'cellog@php.net',
    'active' => 'yes',
    'handle' => 'test',
    'role' => 'lead',
  ),
  2 => 
  array (
    'name' => 'Greg Beaver',
    'email' => 'cellog@php.net',
    'active' => 'yes',
    'handle' => 'test2',
    'role' => 'contributor',
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
