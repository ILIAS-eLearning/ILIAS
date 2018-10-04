--TEST--
PEAR_PackageFile_Parser_v2_Validator->validate(), contents tag tests, bundle package
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
    'test_filelist'. DIRECTORY_SEPARATOR . 'package2.xml';
$pf = $parser->parse(implode('', file($pathtopackagexml)), $pathtopackagexml);
$phpunit->assertIsa('PEAR_PackageFile_v2', $pf, 'ret');
$pf->validate();
$phpunit->assertNoErrors('good one');

$pathtopackagexml = dirname(__FILE__)  . DIRECTORY_SEPARATOR .
    'test_filelist'. DIRECTORY_SEPARATOR . 'package3.xml';
$pf = $parser->parse(implode('', file($pathtopackagexml)), $pathtopackagexml);
$phpunit->assertIsa('PEAR_PackageFile_v2', $pf, 'ret');
$pf->validate();
$phpunit->assertErrors(array(
    array('package' => 'PEAR_PackageFile_v2', 'message' => 'At least 2 packages must be bundled in a bundle package'),
), 'not enough bundledpackages');

$pathtopackagexml = dirname(__FILE__)  . DIRECTORY_SEPARATOR .
    'test_filelist'. DIRECTORY_SEPARATOR . 'package4.xml';
$pf = $parser->parse(implode('', file($pathtopackagexml)), $pathtopackagexml);
$phpunit->assertIsa('PEAR_PackageFile_v2', $pf, 'ret');
$pf->validate();
$phpunit->assertErrors(array(
    array('package' => 'PEAR_PackageFile_v2', 'message' => 'No <bundledpackage> tag was found in <contents>, required for bundle packages'),
), 'no bundledpackages');

$pathtopackagexml = dirname(__FILE__)  . DIRECTORY_SEPARATOR .
    'test_filelist'. DIRECTORY_SEPARATOR . 'package5.xml';
$pf = $parser->parse(implode('', file($pathtopackagexml)), $pathtopackagexml);
$phpunit->assertIsa('PEAR_PackageFile_v2', $pf, 'ret');
$pf->validate();
$phpunit->assertErrors(array(
    array('package' => 'PEAR_PackageFile_v2', 'message' => '<bundledpackage> tags must contain only the filename of a package release in the bundle'),
    array('package' => 'PEAR_PackageFile_v2', 'message' => '<bundledpackage> tags must contain only the filename of a package release in the bundle'),
), 'invalid content');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
