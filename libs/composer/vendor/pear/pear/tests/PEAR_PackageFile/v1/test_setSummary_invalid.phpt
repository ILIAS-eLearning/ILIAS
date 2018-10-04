--TEST--
PEAR_PackageFile_Parser_v1->setSummary() invalid
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
$phpunit->assertEquals('test', $pf->getSummary(), 'pre-set');
$pf->setSummary('');
$phpunit->assertEquals('', $pf->getSummary(), 'set failed');
$result = $pf->validate(PEAR_VALIDATE_NORMAL);
$phpunit->assertErrors(array(
    array('package' => 'PEAR_PackageFile_v1',
        'message' => 'No summary found')
        ), 'after validation 1');
$phpunit->assertNotTrue($result, 'return 1' );
$phpunit->assertEquals(array(), $fakelog->getLog(), 'normal validate empty log 1');

$pf->setSummary("multi\nline");
$phpunit->assertEquals("multi\nline", $pf->getSummary(), 'set failed');
$result = $pf->validate(PEAR_VALIDATE_NORMAL);
$phpunit->assertErrors(array(
    array('package' => 'PEAR_PackageFile_v1',
        'message' => 'Summary should be on one line')
        ), 'after validation 2');
$phpunit->assertNotFalse($result, 'return 2' );
$phpunit->assertEquals(array(), $fakelog->getLog(), 'normal validate empty log 2');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
