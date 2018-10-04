--TEST--
PEAR_PackageFile_v2->setUsesrole()/getUsesrole()/resetUsesrole()
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
$phpunit->assertEquals(false, $pf->getUsesrole(), 'pre-set');
$pf->addUsesrole('hello', 'http://blah.something/Package-1.0');
$phpunit->assertEquals(array(
  'role' => 'hello',
  'uri' => 'http://blah.something/Package-1.0',
), $pf->getUsesrole(), 'set 1 failed');
$result = $pf->validate(PEAR_VALIDATE_NORMAL);
$phpunit->assertEquals(array(), $fakelog->getLog(), 'normal validate empty log');
$phpunit->assertNoErrors('after validation');

$pf->resetUsesrole();
$phpunit->assertEquals(false, $pf->getUsesrole(), 'clear');
$pf->addUsesrole('hello', 'Package', 'example.com');
$phpunit->assertEquals(array(
  'role' => 'hello',
  'package' => 'Package',
  'channel' => 'example.com',
), $pf->getUsesrole(), 'set 1 failed');
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
