--TEST--
PEAR_PackageFile_Parser_v2->addFile
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
$phpunit->assertNoErrors('valid xml parse');
$phpunit->assertIsa('PEAR_PackageFile_v2', $pf, 'return of valid parse');
$pfa = &$pf->getRW();
$pf = &$pfa;
$pf->clearContents();
$pf->setPackageType('php');
$pf->addFile('foo\\test/me', 'file.php', array('role' => 'php'));
$pf->addFile('foo', 'file.php', array('role' => 'php'));
$pf->addFile('', 'file.php', array('role' => 'php'));
$pf->addFile('/', 'pusho.php', array('role' => 'php'));
$phpunit->assertEquals(array (
  'dir' => 
  array (
    'attribs' => 
    array (
      'name' => '/',
    ),
    'file' => 
    array (
      0 => 
      array (
        'attribs' => 
        array (
          'role' => 'php',
          'name' => 'foo/test/me/file.php',
        ),
      ),
      1 => 
      array (
        'attribs' => 
        array (
          'role' => 'php',
          'name' => 'foo/file.php',
        ),
      ),
      2 => 
      array (
        'attribs' => 
        array (
          'role' => 'php',
          'name' => 'file.php',
        ),
      ),
      3 => 
      array (
        'attribs' => 
        array (
          'role' => 'php',
          'name' => 'pusho.php',
        ),
      ),
    ),
  ),
), $pf->getContents(), 'contents');

$result = $pf->validate(PEAR_VALIDATE_NORMAL);
$phpunit->assertEquals(array(), $fakelog->getLog(), 'normal validate empty log');
$phpunit->assertNoErrors('after validation');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
