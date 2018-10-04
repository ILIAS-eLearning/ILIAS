--TEST--
PEAR_PackageFile_Parser_v2->addGroupPackageDepWithChannel
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

$a = $pf->addGroupPackageDepWithChannel('package', 'frong', 'first', 'test');
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

$pf->addDependencyGroup('frong', 'frong group');

$a = $pf->addGroupPackageDepWithChannel('subpackage', 'frong', 'first', 'test');

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
    'subpackage' => 
    array (
      'name' => 'first',
      'channel' => 'test',
    ),
  ),
), $pf->getDeps(true), 'add 2');


$result = $pf->validate(PEAR_VALIDATE_NORMAL);
$phpunit->assertNoErrors('after validation single');
$phpunit->assertEquals(array(), $fakelog->getLog(), 'normal validate single empty log');

$a = $pf->addGroupPackageDepWithChannel('package', 'frong', 'gronk', 'foo', '1.0', '2.0', '1.3', array('1.5', '2.4'));

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
    'package' => 
    array (
      'name' => 'gronk',
      'channel' => 'foo',
      'min' => '1.0',
      'max' => '2.0',
      'recommended' => '1.3',
      'exclude' => 
      array (
        0 => '1.5',
        1 => '2.4',
      ),
    ),
    'subpackage' => 
    array (
      'name' => 'first',
      'channel' => 'test',
    ),
  ),
), $pf->getDeps(true), 'add 3');

$a = $pf->addGroupPackageDepWithChannel('subpackage', 'frong', 'groo', 'test');
$phpunit->assertTrue($a, 'add 4 failed and should not');
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
    'package' => 
    array (
      'name' => 'gronk',
      'channel' => 'foo',
      'min' => '1.0',
      'max' => '2.0',
      'recommended' => '1.3',
      'exclude' => 
      array (
        0 => '1.5',
        1 => '2.4',
      ),
    ),
    'subpackage' => 
    array (
      array (
        'name' => 'first',
        'channel' => 'test',
      ),
      array (
        'name' => 'groo',
        'channel' => 'test',
      ),
    ),
  ),
), $pf->getDeps(true), 'add 4');

$pf->addDependencyGroup('blah', 'blah');

$a = $pf->addGroupPackageDepWithChannel('subpackage', 'blah', 'groo', 'test');
$phpunit->assertTrue($a, 'add 5 failed and should not');
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
    0 => 
    array (
      'attribs' => 
      array (
        'name' => 'frong',
        'hint' => 'frong group',
      ),
      'package' => 
      array (
        'name' => 'gronk',
        'channel' => 'foo',
        'min' => '1.0',
        'max' => '2.0',
        'recommended' => '1.3',
        'exclude' => 
        array (
          0 => '1.5',
          1 => '2.4',
        ),
      ),
      'subpackage' => 
      array (
        0 => 
        array (
          'name' => 'first',
          'channel' => 'test',
        ),
        1 => 
        array (
          'name' => 'groo',
          'channel' => 'test',
        ),
      ),
    ),
    1 => 
    array (
      'attribs' => 
      array (
        'name' => 'blah',
        'hint' => 'blah',
      ),
      'subpackage' => 
      array (
        'name' => 'groo',
        'channel' => 'test',
      ),
    ),
  ),
), $pf->getDeps(true), 'add 5');

$result = $pf->validate(PEAR_VALIDATE_NORMAL);
$phpunit->assertNoErrors('after validation');
$a = $pf->addGroupPackageDepWithChannel('subpackage', 'blah', 'groo', 'test', false, false, false, false, 'bloba');
$phpunit->assertFalse($a, 'subpackage fake did not work');
$a = $pf->addGroupPackageDepWithChannel('package', 'blah', 'groooorg', 'test', false, false, false, false, 'bloba');
$phpunit->assertTrue($a, 'package provides did not work');
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
    0 => 
    array (
      'attribs' => 
      array (
        'name' => 'frong',
        'hint' => 'frong group',
      ),
      'package' => 
      array (
        'name' => 'gronk',
        'channel' => 'foo',
        'min' => '1.0',
        'max' => '2.0',
        'recommended' => '1.3',
        'exclude' => 
        array (
          0 => '1.5',
          1 => '2.4',
        ),
      ),
      'subpackage' => 
      array (
        0 => 
        array (
          'name' => 'first',
          'channel' => 'test',
        ),
        1 => 
        array (
          'name' => 'groo',
          'channel' => 'test',
        ),
      ),
    ),
    1 => 
    array (
      'attribs' => 
      array (
        'name' => 'blah',
        'hint' => 'blah',
      ),
      'package' =>
      array (
        'name' => 'groooorg',
        'channel' => 'test',
        'providesextension' => 'bloba',
      ),
      'subpackage' => 
      array (
        'name' => 'groo',
        'channel' => 'test',
      ),
    ),
  ),
), $pf->getDependencies(), 'provides');

$phpunit->assertEquals(array(), $fakelog->getLog(), 'normal validate empty log');
$result = $pf->validate(PEAR_VALIDATE_INSTALLING);
$phpunit->assertNoErrors('after validation');
$phpunit->assertEquals(array(), $fakelog->getLog(), 'installing validate empty log');
$result = $pf->validate(PEAR_VALIDATE_DOWNLOADING);
$phpunit->assertNoErrors('after validation');
$phpunit->assertEquals(array(), $fakelog->getLog(), 'downloading validate empty log');
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
