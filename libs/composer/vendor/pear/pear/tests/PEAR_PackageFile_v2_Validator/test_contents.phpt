--TEST--
PEAR_PackageFile_Parser_v2_Validator->validate(), contents tag tests
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
    'test_filelist'. DIRECTORY_SEPARATOR . 'package.xml';
$pf = $parser->parse(implode('', file($pathtopackagexml)), $pathtopackagexml);
$phpunit->assertIsa('PEAR_PackageFile_v2', $pf, 'ret');
$pf->validate();
$phpunit->assertErrors(array(
    array('package' => 'PEAR_PackageFile_v2', 'message' => 'tag <dir> has no attributes in context "<dir name="PEAR">"'),
    array('package' => 'PEAR_PackageFile_v2', 'message' => 'tag <dir> in context "<dir name="Installer">" has no attribute "name"'),
    array('package' => 'PEAR_PackageFile_v2', 'message' => 'Invalid tag order in <dir name="*unknown*">, found <ignore> expected one of "dir, file"'),
    array('package' => 'PEAR_PackageFile_v2', 'message' => 'Invalid tag order in <dir name="*unknown*">, found <install> expected one of "file"'),
    array('package' => 'PEAR_PackageFile_v2', 'message' => 'Unknown task "tasks:nobodylikesme" passed in file <file name="PEAR/Installer/Test.php">'),
    array('package' => 'PEAR_PackageFile_v2', 'message' => '<ignore> is not allowed inside global <contents>, only inside <phprelease>/<extbinrelease>/<zendextbinrelease>, use <dir> and <file> only'),
    array('package' => 'PEAR_PackageFile_v2', 'message' => '<install> is not allowed inside global <contents>, only inside <phprelease>/<extbinrelease>/<zendextbinrelease>, use <dir> and <file> only'),
), '1');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
