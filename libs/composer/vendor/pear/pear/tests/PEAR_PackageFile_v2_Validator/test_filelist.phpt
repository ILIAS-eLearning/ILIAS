--TEST--
PEAR_PackageFile_Parser_v2_Validator->validate(), filelist tag validation
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
    'test_releasefilelist'. DIRECTORY_SEPARATOR . 'package.xml';
$pf = $parser->parse(implode('', file($pathtopackagexml)), $pathtopackagexml);
$phpunit->assertIsa('PEAR_PackageFile_v2', $pf, 'ret');
$pf->validate();
$phpunit->assertErrors(array(
    array('package' => 'PEAR_PackageFile_v2', 'message' => 'tag <install> has no attributes in context "<filelist>"'),
    array('package' => 'PEAR_PackageFile_v2', 'message' => 'tag <install> in context "<filelist>" has no attribute "as"'),
    array('package' => 'PEAR_PackageFile_v2', 'message' => 'tag <install> in context "<filelist>" has no attribute "name"'),
    array('package' => 'PEAR_PackageFile_v2', 'message' => 'tag <ignore> has no attributes in context "<filelist>"'),
), '1');

$pathtopackagexml = dirname(__FILE__)  . DIRECTORY_SEPARATOR .
    'test_releasefilelist'. DIRECTORY_SEPARATOR . 'package2.xml';
$pf = $parser->parse(implode('', file($pathtopackagexml)), $pathtopackagexml);
$phpunit->assertIsa('PEAR_PackageFile_v2', $pf, 'ret');
$pf->validate();
$phpunit->assertErrors(array(
    array('package' => 'PEAR_PackageFile_v2', 'message' => '<install name="nothere"> is invalid, file is not in <contents>'),
    array('package' => 'PEAR_PackageFile_v2', 'message' => '<ignore name="nothere"> is invalid, file is not in <contents>'),
), '2');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
