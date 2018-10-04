--TEST--
PEAR_PackageFile_Parser_v2->addPackageDepWithChannel
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
$pfa = &$pf->getRW();
$pf = &$pfa;
$phpunit->assertEquals(array (
  'required' => 
  array (
    'php' => 
    array (
      'min' => '4.3.6',
      'max' => '6.0.0',
    ),
    'pearinstaller' => 
    array (
      'min' => '1.4.0a1',
    ),
  ),
), $pf->getDeps(true), 'basic');

$pf->addPackageDepWithChannel('required', 'first', 'test');
$phpunit->assertEquals(array (
  'required' => 
  array (
    'php' => 
    array (
      'min' => '4.3.6',
      'max' => '6.0.0',
    ),
    'pearinstaller' => 
    array (
      'min' => '1.4.0a1',
    ),
    'package' => 
    array (
      'name' => 'first',
      'channel' => 'test',
    ),
  ),
), $pf->getDeps(true), 'add required');
$pf->addPackageDepWithChannel('optional', 'second', 'test');
$phpunit->assertEquals(array (
  'required' => 
  array (
    'php' => 
    array (
      'min' => '4.3.6',
      'max' => '6.0.0',
    ),
    'pearinstaller' => 
    array (
      'min' => '1.4.0a1',
    ),
    'package' => 
    array (
      'name' => 'first',
      'channel' => 'test',
    ),
  ),
  'optional' =>
  array (
    'package' =>
    array (
      'name' => 'second',
      'channel' => 'test',
    ),
  ),
), $pf->getDeps(true), 'add optional');
//$pf = new PEAR_PackageFile_v2;
$pf->addPackageDepWithChannel('required', 'gronk', 'foo', '1.0', '2.0', '1.3', array('1.5', '2.4'));
$phpunit->assertEquals(array (
  'required' => 
  array (
    'php' => 
    array (
      'min' => '4.3.6',
      'max' => '6.0.0',
    ),
    'pearinstaller' => 
    array (
      'min' => '1.4.0a1',
    ),
    'package' => 
    array (
      array(
        'name' => 'first',
        'channel' => 'test',
      ),
      array(
        'name' => 'gronk',
        'channel' => 'foo',
        'min' => '1.0',
        'max' => '2.0',
        'recommended' => '1.3',
        'exclude' =>
        array(
          0 => '1.5',
          1 => '2.4',
        ),
      ),
    ),
  ),
  'optional' =>
  array (
    'package' =>
    array (
      'name' => 'second',
      'channel' => 'test',
    ),
  ),
), $pf->getDeps(true), 'add required with the works');
$pf->addPackageDepWithChannel('optional', 'gronko', 'fooo', '1.0', '2.0', '1.3', array('2.4'), 'bloba');
$phpunit->assertEquals(array (
  'required' => 
  array (
    'php' => 
    array (
      'min' => '4.3.6',
      'max' => '6.0.0',
    ),
    'pearinstaller' => 
    array (
      'min' => '1.4.0a1',
    ),
    'package' => 
    array (
      array(
        'name' => 'first',
        'channel' => 'test',
      ),
      array(
        'name' => 'gronk',
        'channel' => 'foo',
        'min' => '1.0',
        'max' => '2.0',
        'recommended' => '1.3',
        'exclude' =>
        array(
          0 => '1.5',
          1 => '2.4',
        ),
      ),
    ),
  ),
  'optional' =>
  array (
    'package' =>
    array(
      array (
        'name' => 'second',
        'channel' => 'test',
      ),
      array(
        'name' => 'gronko',
        'channel' => 'fooo',
        'min' => '1.0',
        'max' => '2.0',
        'recommended' => '1.3',
        'exclude' => '2.4',
        'providesextension' => 'bloba',
      ),
    ),
  ),
), $pf->getDeps(true), 'add optional with the works');

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
