--TEST--
PEAR_PackageFile_Parser_v2_Validator->validate(), compatible tag tests
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
    'test_compatible'. DIRECTORY_SEPARATOR . 'package.xml';
$pf = $parser->parse(implode('', file($pathtopackagexml)), $pathtopackagexml);
$phpunit->assertIsa('PEAR_PackageFile_v2', $pf, 'ret');
$pf->validate();
$phpunit->assertErrors(array(
    array('package' => 'PEAR_PackageFile_v2', 'message' => 'Invalid tag order in <compatible>, found <> expected one of "name"'),
    array('package' => 'PEAR_PackageFile_v2', 'message' => 'Invalid tag order in <compatible><name>foo1</name>, found <> expected one of "channel"'),
    array('package' => 'PEAR_PackageFile_v2', 'message' => 'Invalid tag order in <compatible><name>foo2</name>, found <> expected one of "min"'),
    array('package' => 'PEAR_PackageFile_v2', 'message' => 'Invalid tag order in <compatible><name>foo3</name>, found <> expected one of "max"'),
    array('package' => 'PEAR_PackageFile_v2', 'message' => 'Invalid tag order in <compatible><name>foo4</name>, found <moo> expected one of "exclude"'),
    array('package' => 'PEAR_PackageFile_v2', 'message' => 'Invalid tag order in <compatible><name>foo5</name>, found <moo> expected one of "exclude"'),
    array('package' => 'PEAR_PackageFile_v2', 'message' => 'Invalid tag order in <compatible><name>foo6</name>, found <moo> expected one of "exclude"'),
    array('package' => 'PEAR_PackageFile_v2', 'message' => 'Version type <compatible><name>foo8</name><min> is not a valid version (#1.0)'),
    array('package' => 'PEAR_PackageFile_v2', 'message' => 'Version type <compatible><name>foo8</name><max> is not a valid version (4.a0)'),
    array('package' => 'PEAR_PackageFile_v2', 'message' => 'Version type <compatible><name>foo8</name><exclude> is not a valid version (2.0^)'),
), '1');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
