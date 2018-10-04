--TEST--
PEAR_PackageFile_Parser_v2_Validator->validate(), <dependencies> <group> tests
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
    'test_groupdependencies'. DIRECTORY_SEPARATOR . 'package.xml';
$pf = $parser->parse(implode('', file($pathtopackagexml)), $pathtopackagexml);
$phpunit->assertIsa('PEAR_PackageFile_v2', $pf, 'ret');
$pf->validate();
$phpunit->assertErrors(array(
    array('package' => 'PEAR_PackageFile_v2', 'message' => 'tag <group> has no attributes in context "<dependencies>"'),
), '1');

$pathtopackagexml = dirname(__FILE__)  . DIRECTORY_SEPARATOR .
    'test_groupdependencies'. DIRECTORY_SEPARATOR . 'package2.xml';
$pf = $parser->parse(implode('', file($pathtopackagexml)), $pathtopackagexml);
$phpunit->assertIsa('PEAR_PackageFile_v2', $pf, 'ret');
$pf->validate();
$phpunit->assertErrors(array(
    array('package' => 'PEAR_PackageFile_v2', 'message' => 'tag <group> in context "<dependencies>" has no attribute "name"'),
    array('package' => 'PEAR_PackageFile_v2', 'message' => 'tag <group> in context "<dependencies>" has no attribute "hint"'),
), '2');

$pathtopackagexml = dirname(__FILE__)  . DIRECTORY_SEPARATOR .
    'test_groupdependencies'. DIRECTORY_SEPARATOR . 'package3.xml';
$pf = $parser->parse(implode('', file($pathtopackagexml)), $pathtopackagexml);
$phpunit->assertIsa('PEAR_PackageFile_v2', $pf, 'ret');
$pf->validate();
$phpunit->assertErrors(array(
    array('package' => 'PEAR_PackageFile_v2', 'message' => 'Invalid tag order in <group>, found <package> expected one of "subpackage, extension"'),
    array('package' => 'PEAR_PackageFile_v2', 'message' => 'Invalid tag order in <group>, found <os> expected one of "extension"'),
    array('package' => 'PEAR_PackageFile_v2', 'message' => 'Invalid tag order in <group>, found <arch> expected one of "extension"'),
    array('package' => 'PEAR_PackageFile_v2', 'message' => 'Invalid tag order in <group>, found <php> expected one of "extension"'),
    array('package' => 'PEAR_PackageFile_v2', 'message' => 'Invalid tag order in <group>, found <pearinstaller> expected one of "extension"'),
), 'test the out of order stuff');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
