--TEST--
PEAR_PackageFile_Parser_v2->addPackageDepWithURI
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

$pf->addPackageDepWithURI('required', 'first', 'http://www.example.com/p.tgz');
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
      'uri' => 'http://www.example.com/p.tgz',
    ),
  ),
), $pf->getDeps(true), 'add required');
$pf->addPackageDepWithURI('optional', 'second', 'http://www.example.com/s.tgz');
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
      'uri' => 'http://www.example.com/p.tgz',
    ),
  ),
  'optional' =>
  array (
    'package' =>
    array (
      'name' => 'second',
      'uri' => 'http://www.example.com/s.tgz',
    ),
  ),
), $pf->getDeps(true), 'add optional');
//$pf = new PEAR_PackageFile_v2;
$pf->addPackageDepWithURI('required', 'gronk', 'http://www.example.com/fe.tgz');
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
      array (
        'name' => 'first',
        'uri' => 'http://www.example.com/p.tgz',
      ),
      array (
        'name' => 'gronk',
        'uri' => 'http://www.example.com/fe.tgz',
      ),
    ),
  ),
  'optional' =>
  array (
    'package' =>
    array (
      'name' => 'second',
      'uri' => 'http://www.example.com/s.tgz',
    ),
  ),
), $pf->getDeps(true), 'add 2nd required');
$pf->addPackageDepWithURI('optional', 'gronko', 'http://www.example.com/ho.tgz', 'bloba');
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
      array (
        'name' => 'first',
        'uri' => 'http://www.example.com/p.tgz',
      ),
      array (
        'name' => 'gronk',
        'uri' => 'http://www.example.com/fe.tgz',
      ),
    ),
  ),
  'optional' =>
  array (
    'package' =>
    array (
      array (
        'name' => 'second',
        'uri' => 'http://www.example.com/s.tgz',
      ),
      array (
        'name' => 'gronko',
        'uri' => 'http://www.example.com/ho.tgz',
        'providesextension' => 'bloba',
      ),
    ),
  ),
), $pf->getDeps(true), 'add 2nd optional');

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
