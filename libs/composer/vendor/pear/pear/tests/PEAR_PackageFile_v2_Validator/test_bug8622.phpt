--TEST--
PEAR_PackageFile_Parser_v2_Validator->validate(), individual dependency type tests
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
    'test_dependencies'. DIRECTORY_SEPARATOR . 'package8.xml';
$pf = $parser->parse(implode('', file($pathtopackagexml)), $pathtopackagexml);
$phpunit->assertIsa('PEAR_PackageFile_v2', $pf, 'ret');
$pf->validate();
$phpunit->assertErrors(array(
    array('package' => 'PEAR_PackageFile_v2', 'message' => 'Version type <<dependencies><required><php><exclude>> is not a valid version ($6)'),
), '1');
$pathtopackagexml = dirname(__FILE__)  . DIRECTORY_SEPARATOR .
    'test_dependencies'. DIRECTORY_SEPARATOR . 'package9.xml';
$pf = $parser->parse(implode('', file($pathtopackagexml)), $pathtopackagexml);
$phpunit->assertIsa('PEAR_PackageFile_v2', $pf, 'ret');
$pf->validate();
$phpunit->assertErrors(array(
    array('package' => 'PEAR_PackageFile_v2', 'message' => 'Version type <<dependencies><required><php><exclude>> is not a valid version ($6)'),
    array('package' => 'PEAR_PackageFile_v2', 'message' => 'Version type <<dependencies><required><php><exclude>> is not a valid version ($6364)'),
), '2');

echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
