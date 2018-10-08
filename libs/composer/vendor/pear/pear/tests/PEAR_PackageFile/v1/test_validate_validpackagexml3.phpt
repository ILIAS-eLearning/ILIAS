--TEST--
PEAR_PackageFile_v1->validate() test (valid package.xml 3)
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
      <file role="data" name="package.dtd"/>
      <file role="data" name="template.spec"/>
      <file role="php" name="PEAR.php"/>
      <file role="php" name="System.php"/>
      <dir name="PEAR">
        <file role="php" name="Autoloader.php"/>
        <file role="php" name="Command.php"/>
        <dir name="Command">
          <file role="php" name="Auth.php"/>
          <file role="php" name="Build.php"/>
          <file role="php" name="Common.php"/>
          <file role="php" name="Config.php"/>
          <file role="php" name="Install.php"/>
          <file role="php" name="Package.php"/>
          <file role="php" name="Registry.php"/>
          <file role="php" name="Remote.php"/>
          <file role="php" name="Mirror.php"/>
        </dir>
        <file role="php" name="Common.php"/>
        <file role="php" name="Config.php"/>
        <file role="php" name="Dependency.php"/>
        <dir name="Frontend">
          <file role="php" name="CLI.php"/>
        </dir>
        <file role="php" name="Builder.php"/>
        <file role="php" name="Installer.php"/>
        <file role="php" name="Packager.php"/>
        <file role="php" name="Registry.php"/>
        <file role="php" name="Remote.php"/>
      </dir>
      <dir name="OS">
        <file role="php" name="Guess.php"/>
      </dir>
      <dir name="scripts" baseinstalldir="/">
        <file role="script" install-as="pear" name="pear.sh">
          <replace from="@php_bin@" to="php_bin" type="pear-config"/>
          <replace from="@php_dir@" to="php_dir" type="pear-config"/>
          <replace from="@pear_version@" to="version" type="package-info"/>
          <replace from="@include_path@" to="php_dir" type="pear-config"/>
        </file>
        <file role="script" platform="windows" install-as="pear.bat" name="pear.bat">
        <replace from="@bin_dir@" to="bin_dir" type="pear-config"/>
        <replace from="@php_bin@" to="php_bin" type="pear-config"/>
        <replace from="@include_path@" to="php_dir" type="pear-config"/>
        </file>
        <file role="php" install-as="pearcmd.php" name="pearcmd.php">
          <replace from="@php_bin@" to="php_bin" type="pear-config"/>
          <replace from="@php_dir@" to="php_dir" type="pear-config"/>
          <replace from="@pear_version@" to="version" type="package-info"/>
          <replace from="@include_path@" to="php_dir" type="pear-config"/>
        </file>
      </dir>
    </filelist>
    <configureoptions>
     <configureoption name="test" prompt="The prompt test" default="foo" />
    </configureoptions>
</release>
</package>',    
    'package.xml');
$phpunit->assertNoErrors('after parse');
$ret = $pf->validate(PEAR_VALIDATE_NORMAL);
$phpunit->assertNoErrors('error message');
$phpunit->assertNotFalse($ret, 'return');

echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
