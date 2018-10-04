--TEST--
PEAR_PackageFile_Parser_v1->setDate() invalid
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
$phpunit->assertNoErrors('valid xml parse');
$phpunit->assertIsa('PEAR_PackageFile_v1', $pf, 'return of valid parse');
$phpunit->assertEquals('2004-10-10', $pf->getDate(), 'pre-set');
$pf->setDate('');
$phpunit->assertEquals('', $pf->getDate(), 'set failed');
$result = $pf->validate(PEAR_VALIDATE_NORMAL);
$phpunit->assertErrors(array(
    array('package' => 'PEAR_PackageFile_v1',
        'message' => 'No release date found')
        ), 'after validation 1');
$phpunit->assertNotTrue($result, 'return 1' );
$phpunit->assertEquals(array(), $fakelog->getLog(), 'normal validate empty log 1');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
