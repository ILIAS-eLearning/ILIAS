--TEST--
PEAR_PackageFile_Parser_v1 maintainer management, invalid
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
    'test_basicparse'. DIRECTORY_SEPARATOR . 'package.xml';
$pf = &$parser->parse(implode('', file($pathtopackagexml)), $pathtopackagexml);
$pf->clearMaintainers();
$phpunit->assertFalse($pf->getMaintainers(), 'clear failed');
$result = $pf->validate(PEAR_VALIDATE_NORMAL);
$phpunit->assertErrors(array(
    array('package' => 'PEAR_PackageFile_v1',
        'message' => 'No maintainers found, at least one must be defined')
        ), 'after validation 1');
$phpunit->assertNotTrue($result, 'return 1' );
$phpunit->assertEquals(array(), $fakelog->getLog(), 'normal validate empty log 1');

$pf->addMaintainer('lead', '', 'greg', 'greg@example.com');
$result = $pf->validate(PEAR_VALIDATE_NORMAL);
$phpunit->assertErrors(array(
    array('package' => 'PEAR_PackageFile_v1',
        'message' => 'Maintainer 1 has no handle (user ID at channel server)')
        ), 'after validation 2');
$phpunit->assertNotTrue($result, 'return 2' );
$phpunit->assertEquals(array(), $fakelog->getLog(), 'normal validate empty log 2');
$pf->clearMaintainers();

$pf->addMaintainer('', 'foo', 'greg', 'greg@example.com');
$result = $pf->validate(PEAR_VALIDATE_NORMAL);
$phpunit->assertErrors(array(
    array('package' => 'PEAR_PackageFile_v1',
        'message' => 'Maintainer 1 has no role'),
    array('package' => 'PEAR_PackageFile_v1',
        'message' => 'Package must have at least one lead maintainer')
        ), 'after validation 3');
$phpunit->assertNotTrue($result, 'return 3' );
$phpunit->assertEquals(array(), $fakelog->getLog(), 'normal validate empty log 3');
$pf->clearMaintainers();

$pf->addMaintainer('lead', 'foo', '', 'greg@example.com');
$result = $pf->validate(PEAR_VALIDATE_NORMAL);
$phpunit->assertErrors(array(
    array('package' => 'PEAR_PackageFile_v1',
        'message' => 'Maintainer 1 has no name')
        ), 'after validation 4');
$phpunit->assertNotTrue($result, 'return 4' );
$phpunit->assertEquals(array(), $fakelog->getLog(), 'normal validate empty log 4');
$pf->clearMaintainers();

$pf->addMaintainer('lead', 'foo', 'greg', '');
$result = $pf->validate(PEAR_VALIDATE_NORMAL);
$phpunit->assertErrors(array(
    array('package' => 'PEAR_PackageFile_v1',
        'message' => 'Maintainer 1 has no email')
        ), 'after validation 5');
$phpunit->assertNotTrue($result, 'return 5' );
$phpunit->assertEquals(array(), $fakelog->getLog(), 'normal validate empty log 5');
$pf->clearMaintainers();
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
