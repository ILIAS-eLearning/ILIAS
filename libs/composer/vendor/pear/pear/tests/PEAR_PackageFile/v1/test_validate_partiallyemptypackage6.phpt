--TEST--
PEAR_PackageFile_v1->validate() test (valid xml, partially empty package.xml 6)
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
if (!function_exists('token_get_all')) {
    echo 'skip';
}
?>
--FILE--
<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'setup.php.inc';

$php5 = version_compare(phpversion(), '5.0.0', '>=');
$pf = &$parser->parse('<?xml version="1.0" encoding="ISO-8859-1" ?>' .
    '<package version="1.0"><name>test</name><summary>PEAR test</summary>' . 
    '<description>The test</description><license>PHP License</license>  <maintainers>
    <maintainer>
      <user>test</user>
      <role>lead</role>
      <name>test tester</name>
      <email>test@php.net</email>
    </maintainer></maintainers><release>
    <version>1.3b4</version></release>
</package>',    
    'package.xml');

$ret = $pf->validate(PEAR_VALIDATE_NORMAL);
$phpunit->assertErrors(array(
    array('package' => 'PEAR_PackageFile_v1',
        'message' => 'No release state found'),
    array('package' => 'PEAR_PackageFile_v1',
        'message' => 'No release date found'),
    array('package' => 'PEAR_PackageFile_v1',
        'message' => 'No release notes found'),
    array('package' => 'PEAR_PackageFile_v1',
        'message' => 'No files in <filelist> section of package.xml'),
        ), 'error message');
$phpunit->assertNotTrue($ret, 'return');

echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
