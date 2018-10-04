--TEST--
PEAR_PackageFile_Parser_v2->addExtensionInstallCondition()
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
$phpunit->assertEquals('pear.php.net', $pf->getChannel(), 'pre-set');
$pfa = &$pf->getRW();
$pf = &$pfa;
$pf->clearContents();
$pf->setPackageType('extsrc');
$pf->addFile('', 'foo.c', array('role' => 'src'));
$pf->addConfigureOption('me', 'is');
$pf->setProvidesExtension('foo');
$phpunit->assertEquals(array (
  'configureoption' => 
  array (
    'attribs' => 
    array (
      'name' => 'me',
      'prompt' => 'is',
    ),
  ),
), $pf->getReleases(), 'first');
$result = $pf->validate(PEAR_VALIDATE_NORMAL);
$phpunit->assertEquals(array(), $fakelog->getLog(), 'normal validate empty log');
$result = $pf->validate(PEAR_VALIDATE_INSTALLING);
$phpunit->assertEquals(array(), $fakelog->getLog(), 'installing validate empty log');
$result = $pf->validate(PEAR_VALIDATE_DOWNLOADING);
$phpunit->assertEquals(array(), $fakelog->getLog(), 'downloading validate empty log');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
