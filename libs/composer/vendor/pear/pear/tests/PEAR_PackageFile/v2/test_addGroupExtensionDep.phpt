--TEST--
PEAR_PackageFile_Parser_v2->addGroupExtensionDep
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

$a = $pf->addGroupExtensionDep('frong', 'first');
$phpunit->assertFalse($a, 'add succeeded and should not');
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

$result = $pf->validate(PEAR_VALIDATE_NORMAL);
$phpunit->assertNoErrors('after validation single');
$phpunit->assertEquals(array(), $fakelog->getLog(), 'normal validate single empty log');

$pf->addDependencyGroup('frong', 'frong group');

$a = $pf->addGroupExtensionDep('frong', 'first');

$phpunit->assertTrue($a, 'add 2 failed and should not');
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
  'group' => 
  array (
    'attribs' => 
    array (
      'name' => 'frong',
      'hint' => 'frong group',
    ),
    'extension' => 
    array (
      'name' => 'first',
    ),
  ),
), $pf->getDeps(true), 'add 2');
$a = $pf->addGroupExtensionDep('frong', 'gronk', '1.0', '2.0', '1.3', array('1.5', '2.4'));

$phpunit->assertTrue($a, 'add 3 failed and should not');
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
  'group' => 
  array (
    'attribs' => 
    array (
      'name' => 'frong',
      'hint' => 'frong group',
    ),
    'extension' => 
    array (
      array (
        'name' => 'first',
      ),
      array (
        'name' => 'gronk',
        'min' => '1.0',
        'max' => '2.0',
        'recommended' => '1.3',
        'exclude' => 
        array (
          0 => '1.5',
          1 => '2.4',
        ),
      ),
    ),
  ),
), $pf->getDeps(true), 'add 3');

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
