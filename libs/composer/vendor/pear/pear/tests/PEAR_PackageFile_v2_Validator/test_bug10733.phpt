--TEST--
PEAR_PackageFile_Parser_v2_Validator->validate() Bug #10733
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
    'test_bug10733'. DIRECTORY_SEPARATOR . 'package.xml';
$pf = $parser->parse(implode('', file($pathtopackagexml)), $pathtopackagexml);
$pf->validate(PEAR_VALIDATE_DOWNLOADING);
$phpunit->assertErrors(array(
    array('package' => 'PEAR_PackageFile_v2', 'message' => 'tag <file> has no attributes in context "<dir name="/">"'),
    array('package' => 'PEAR_PackageFile_v2', 'message' => 'Old-style <file>name</file> is not allowed.  Use<file name="name" role="role"/>'),
)
, 'e');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
