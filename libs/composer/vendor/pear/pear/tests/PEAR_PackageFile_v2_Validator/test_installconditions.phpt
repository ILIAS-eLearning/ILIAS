--TEST--
PEAR_PackageFile_Parser_v2_Validator->validate(), installconditions tag validation
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
    'test_installconditions'. DIRECTORY_SEPARATOR . 'package.xml';
$pf = $parser->parse(implode('', file($pathtopackagexml)), $pathtopackagexml);
$phpunit->assertIsa('PEAR_PackageFile_v2', $pf, 'ret');
$pf->validate();
$phpunit->assertErrors(array(
    array('package' => 'PEAR_PackageFile_v2', 'message' => 'Invalid tag order in <installcondition><os>, found <> expected one of "name"'),
    array('package' => 'PEAR_PackageFile_v2', 'message' => 'Invalid tag order in <installcondition><arch>, found <name> expected one of "pattern"'),
    array('package' => 'PEAR_PackageFile_v2', 'message' => 'Invalid tag order in <extbinrelease><installconditions>, found <php> expected one of "arch"'),
    array('package' => 'PEAR_PackageFile_v2', 'message' => 'Version type <installcondition><extension><name>test</name><min> is not a valid version (1.$0)'),
    array('package' => 'PEAR_PackageFile_v2', 'message' => 'Invalid tag order in <extbinrelease><installconditions>, found <extension> expected one of "os, arch"'),
    array('package' => 'PEAR_PackageFile_v2', 'message' => 'Invalid tag order in <extbinrelease><installconditions>, found <package> expected one of "php, extension, os, arch"'),
    array('package' => 'PEAR_PackageFile_v2', 'message' => 'Invalid tag order in <extbinrelease><installconditions>, found <php> expected one of "extension, os, arch"'),
    array('package' => 'PEAR_PackageFile_v2', 'message' => 'Invalid tag order in <extbinrelease><installconditions>, found <php> expected one of "arch"'),
), '1');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
