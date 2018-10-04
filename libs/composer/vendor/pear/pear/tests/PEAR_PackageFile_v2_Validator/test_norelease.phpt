--TEST--
PEAR_PackageFile_Parser_v2_Validator->validate(), no <phprelease>, <extsrcrelease>, <extbinrelease> or <bundle>
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
    'test_norelease'. DIRECTORY_SEPARATOR . 'package.xml';
$pf = $parser->parse(implode('', file($pathtopackagexml)), $pathtopackagexml);
$phpunit->assertIsa('PEAR_PackageFile_v2', $pf, 'ret');
$pf->validate();
$phpunit->assertErrors(array(
    array('package' => 'PEAR_PackageFile_v2', 'message' => 'Invalid tag order in <package>, found <changelog> expected one of "usesrole, usestask, providesextension, srcpackage, srcuri, phprelease, extsrcrelease, extbinrelease, zendextsrcrelease, zendextbinrelease, bundle"'),
), 'no release');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
