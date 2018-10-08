--TEST--
PEAR_PackageFile_v1->validate() test, no file role
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
    <version>1.3b4</version>
    <date>2003-11-17</date>
    <state>beta</state>
    <notes>test</notes>
    <provides type="class" name="furngy" />
    <deps>
          <dep type="ext" rel="has" optional="yes">xmlrpc</dep>
    </deps>
    <filelist>
      <file name="package.dtd"/>
    </filelist>

</release>
</package>',    
    'package.xml');

$ret = $pf->validate(PEAR_VALIDATE_NORMAL);
$phpunit->assertErrors(array(
    array('package' => 'PEAR_PackageFile_v1',
        'message' => 'File "package.dtd" has no role, expecting one of "php, ext, test, doc, data, src, script"')
), 'error message');
$phpunit->assertNotTrue($ret, 'return');

$pf = &$parser->parse('<?xml version="1.0" encoding="ISO-8859-1" ?>' .
    '<package version="1.0"><name>test</name><summary>PEAR test</summary>' . 
    '<description>The test</description><license>PHP License</license>  <maintainers>
    <maintainer>
      <user>test</user>
      <role>lead</role>
      <name>test tester</name>
      <email>test@php.net</email>
    </maintainer></maintainers><release>
    <version>1.3b4</version>
    <date>2003-11-17</date>
    <state>beta</state>
    <notes>test</notes>
    <provides type="class" name="furngy" />
    <deps>
          <dep type="ext" rel="has" optional="yes">xmlrpc</dep>
    </deps>
    <filelist>
      <file name="package.dtd" role="grnok"/>
    </filelist>

</release>
</package>',    
    'package.xml');

$ret = $pf->validate(PEAR_VALIDATE_NORMAL);
$phpunit->assertErrors(array(
    array('package' => 'PEAR_PackageFile_v1',
        'message' => 'File "package.dtd" has invalid role "grnok", expecting one of "php, ext, test, doc, data, src, script"')
), 'error message 2');
$phpunit->assertNotTrue($ret, 'return 2');

echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
