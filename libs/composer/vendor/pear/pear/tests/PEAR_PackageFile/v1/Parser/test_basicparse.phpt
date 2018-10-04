--TEST--
PEAR_PackageFile_Parser_v1->parse()
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
    'test_basicparse'. DIRECTORY_SEPARATOR . 'package.xml';
$result = &$parser->parse(implode('', file($pathtopackagexml)), $pathtopackagexml);
$phpunit->assertNoErrors('valid xml parse');
$phpunit->assertIsa('PEAR_PackageFile_v1', $result, 'return of valid parse');
$phpunit->assertEquals(array(), $fakelog->getLog(), 'valid xml parse empty log');
echo 'tests done';
?>
--EXPECT--
tests done
