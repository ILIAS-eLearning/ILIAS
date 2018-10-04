--TEST--
PEAR_PackageFile_Parser_v2_Validator->validate(), extsrcrelease tag validation
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
    'test_release'. DIRECTORY_SEPARATOR . 'package7.xml';
$pf = $parser->parse(implode('', file($pathtopackagexml)), $pathtopackagexml);
$phpunit->assertIsa('PEAR_PackageFile_v2', $pf, 'ret');
$pf->validate();
$phpunit->assertErrors(array(
    array('package' => 'PEAR_PackageFile_v2', 'message' => 'File "1" in directory "<dir name="/">" has invalid role "src", should be one of cfg, data, doc, ext, man, php, script, test, www'),
    array('package' => 'PEAR_PackageFile_v2', 'message' => '<extbinrelease> packages must use <providesextension> to indicate which PHP extension is provided'),
    array('package' => 'PEAR_PackageFile_v2', 'message' => '<extbinrelease>/<zendextbinrelease> packages must specify a source code package with <srcpackage>'),
), '1');

$pathtopackagexml = dirname(__FILE__)  . DIRECTORY_SEPARATOR .
    'test_release'. DIRECTORY_SEPARATOR . 'package8.xml';
$pf = $parser->parse(implode('', file($pathtopackagexml)), $pathtopackagexml);
$phpunit->assertIsa('PEAR_PackageFile_v2', $pf, 'ret');
$pf->validate();
$phpunit->assertErrors(array(
    array('package' => 'PEAR_PackageFile_v2', 'message' => '<extbinrelease>/<zendextbinrelease> packages must specify a source code package with <srcpackage>'),
), '2');

$pathtopackagexml = dirname(__FILE__)  . DIRECTORY_SEPARATOR .
    'test_release'. DIRECTORY_SEPARATOR . 'package9.xml';
$pf = $parser->parse(implode('', file($pathtopackagexml)), $pathtopackagexml);
$phpunit->assertIsa('PEAR_PackageFile_v2', $pf, 'ret');
$pf->validate();
$phpunit->assertErrors(array(
    array('package' => 'PEAR_PackageFile_v2', 'message' => '<extbinrelease>/<zendextbinrelease> packages must specify a source code package with <srcuri>'),
    array('package' => 'PEAR_PackageFile_v2', 'message' => '<extbinrelease>/<zendextbinrelease> packages must specify a source code package with <srcuri>'),
    array('package' => 'PEAR_PackageFile_v2', 'message' => 'Invalid tag order in <installcondition><os>, found <> expected one of "name"'),
), '3');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
