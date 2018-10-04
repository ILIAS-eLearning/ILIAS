--TEST--
PEAR_PackageFile_Parser_v2->setPhpInstallCondition
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
$phpunit->showall();
$pf->setPhpInstallCondition('4.0', '6.5');
$phpunit->assertEquals(array (
  0 => 
  array (
    'installconditions' => 
    array (
      'os' => 
      array (
        'name' => 'windows',
      ),
    ),
    'filelist' => 
    array (
      'install' => 
      array (
        0 => 
        array (
          'attribs' => 
          array (
            'as' => 'another.php',
            'name' => 'test/test3.php',
          ),
        ),
        1 => 
        array (
          'attribs' => 
          array (
            'as' => 'hi.php',
            'name' => 'test/test2.php',
          ),
        ),
      ),
    ),
  ),
  1 => 
  array (
    'installconditions' => 
    array (
      'php' => 
      array (
        'min' => '4.0',
        'max' => '6.5',
      ),
    ),
    'filelist' => 
    array (
      'install' => 
      array (
        'attribs' => 
        array (
          'as' => 'hi.php',
          'name' => 'test/test2.php',
        ),
      ),
      'ignore' => 
      array (
        'attribs' => 
        array (
          'name' => 'test/test3.php',
        ),
      ),
    ),
  ),
), $pf->getReleases(), 'first set');
$pf->setPhpInstallCondition('4.0', '6.5', array('4.3.8', '4.3.2'));
$phpunit->assertEquals(array (
  0 => 
  array (
    'installconditions' => 
    array (
      'os' => 
      array (
        'name' => 'windows',
      ),
    ),
    'filelist' => 
    array (
      'install' => 
      array (
        0 => 
        array (
          'attribs' => 
          array (
            'as' => 'another.php',
            'name' => 'test/test3.php',
          ),
        ),
        1 => 
        array (
          'attribs' => 
          array (
            'as' => 'hi.php',
            'name' => 'test/test2.php',
          ),
        ),
      ),
    ),
  ),
  1 => 
  array (
    'installconditions' => 
    array (
      'php' => 
      array (
        'min' => '4.0',
        'max' => '6.5',
        'exclude' => 
        array (
          0 => '4.3.8',
          1 => '4.3.2',
        ),
      ),
    ),
    'filelist' => 
    array (
      'install' => 
      array (
        'attribs' => 
        array (
          'as' => 'hi.php',
          'name' => 'test/test2.php',
        ),
      ),
      'ignore' => 
      array (
        'attribs' => 
        array (
          'name' => 'test/test3.php',
        ),
      ),
    ),
  ),
), $pf->getReleases(), 'first set');
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
