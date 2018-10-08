--TEST--
PEAR_PackageFile_v2->setUsestask()/getUsestask()/resetUsestask()
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
$phpunit->assertEquals(false, $pf->getUsestask(), 'pre-set');
$pf->addUsestask('hello', 'http://blah.something/Package-1.0');
$phpunit->assertEquals(array(
  'task' => 'hello',
  'uri' => 'http://blah.something/Package-1.0',
), $pf->getUsestask(), 'set 1 failed');
$result = $pf->validate(PEAR_VALIDATE_NORMAL);
$phpunit->assertEquals(array(), $fakelog->getLog(), 'normal validate empty log');
$phpunit->assertNoErrors('after validation');

$pf->resetUsestask();
$phpunit->assertEquals(false, $pf->getUsestask(), 'clear');
$pf->addUsestask('hello', 'Package', 'example.com');
$phpunit->assertEquals(array(
  'task' => 'hello',
  'package' => 'Package',
  'channel' => 'example.com',
), $pf->getUsestask(), 'set 1 failed');
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
