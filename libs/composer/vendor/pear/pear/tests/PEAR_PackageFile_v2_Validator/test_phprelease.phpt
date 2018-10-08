--TEST--
PEAR_PackageFile_Parser_v2_Validator->validate(), phprelease tag validation
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
    'test_release'. DIRECTORY_SEPARATOR . 'package.xml';
$pf = $parser->parse(implode('', file($pathtopackagexml)), $pathtopackagexml);
$phpunit->assertIsa('PEAR_PackageFile_v2', $pf, 'ret');
$pf->validate();
$phpunit->assertErrors(array(
    array('package' => 'PEAR_PackageFile_v2', 'message' => '<phprelease> packages cannot use <providesextension>, only extbinrelease, extsrcrelease, zendextsrcrelease, and zendextbinrelease can provide a PHP extension'),
    array('package' => 'PEAR_PackageFile_v2', 'message' => '<phprelease> packages cannot specify a source code package, only extension binaries may use the <srcpackage> tag'),
), '1');

$pathtopackagexml = dirname(__FILE__)  . DIRECTORY_SEPARATOR .
    'test_release'. DIRECTORY_SEPARATOR . 'package2.xml';
$pf = $parser->parse(implode('', file($pathtopackagexml)), $pathtopackagexml);
$phpunit->assertIsa('PEAR_PackageFile_v2', $pf, 'ret');
$pf->validate();
$phpunit->assertErrors(array(
    array('package' => 'PEAR_PackageFile_v2', 'message' => '<phprelease> packages cannot use <providesextension>, only extbinrelease, extsrcrelease, zendextsrcrelease, and zendextbinrelease can provide a PHP extension'),
    array('package' => 'PEAR_PackageFile_v2', 'message' => '<phprelease> packages cannot specify a source code package, only extension binaries may use the <srcpackage> tag'),
), '2');

$pathtopackagexml = dirname(__FILE__)  . DIRECTORY_SEPARATOR .
    'test_release'. DIRECTORY_SEPARATOR . 'package3.xml';
$pf = $parser->parse(implode('', file($pathtopackagexml)), $pathtopackagexml);
$phpunit->assertIsa('PEAR_PackageFile_v2', $pf, 'ret');
$pf->validate();
$phpunit->assertErrorsF(array(
    array('package' => 'PEAR_PackageFile_v2', 'message' => 'File "1" in directory "<dir name="/">" has invalid role "src", should be one of cfg, data, doc, man, php, script, test, www'),
    array('package' => 'PEAR_PackageFile_v2', 'message' => 'File "6" in directory "<dir name="/">" has invalid role "ext", should be one of cfg, data, doc, man, php, script, test, www'),
), '3');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
