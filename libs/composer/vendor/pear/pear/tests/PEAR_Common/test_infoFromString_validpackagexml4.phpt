--TEST--
PEAR_Common::infoFromString test (valid xml, valid package.xml 3)
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
$ret = $common->infoFromString('<?xml version="1.0" encoding="ISO-8859-1" ?>' .
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
  <changelog>
    <release>
      <version>0.1</version>
      <date>2003-07-21</date>
      <license>PHP License</license>
      <state>alpha</state>
      <notes>First release of test</notes>
    </release>
    <release>
      <version>0.2</version>
      <date>2003-07-21</date>
      <license>PHP License</license>
      <state>alpha</state>
      <notes>Generation of package.xml from scratch is now supported.  In addition,
generation of &lt;provides&gt; is supported and so is addition of
maintainers and configure options

- Fixed a bug in &lt;release&gt; generation
- Added _addProvides() to generate a &lt;provides&gt; section</notes>
    </release>
   </changelog>
</package>');

$phpunit->assertNoErrors('parsing');
$phpunit->showall();
$phpunit->assertEquals(array (
  'provides' => 
  array (
    'class;furngy' => 
    array (
      'type' => 'class',
      'name' => 'furngy',
      'explicit' => true,
    ),
  ),
  'filelist' => 
  array (
    'package.dtd' => 
    array (
      'role' => 'data',
    ),
    'template.spec' => 
    array (
      'role' => 'data',
    ),
    'PEAR.php' => 
    array (
      'role' => 'php',
    ),
    'System.php' => 
    array (
      'role' => 'php',
    ),
    'PEAR/Autoloader.php' => 
    array (
      'role' => 'php',
    ),
    'PEAR/Command.php' => 
    array (
      'role' => 'php',
    ),
    'PEAR/Command/Auth.php' => 
    array (
      'role' => 'php',
    ),
    'PEAR/Command/Build.php' => 
    array (
      'role' => 'php',
    ),
    'PEAR/Command/Common.php' => 
    array (
      'role' => 'php',
    ),
    'PEAR/Command/Config.php' => 
    array (
      'role' => 'php',
    ),
    'PEAR/Command/Install.php' => 
    array (
      'role' => 'php',
    ),
    'PEAR/Command/Package.php' => 
    array (
      'role' => 'php',
    ),
    'PEAR/Command/Registry.php' => 
    array (
      'role' => 'php',
    ),
    'PEAR/Command/Remote.php' => 
    array (
      'role' => 'php',
    ),
    'PEAR/Command/Mirror.php' => 
    array (
      'role' => 'php',
    ),
    'PEAR/Common.php' => 
    array (
      'role' => 'php',
    ),
    'PEAR/Config.php' => 
    array (
      'role' => 'php',
    ),
    'PEAR/Dependency.php' => 
    array (
      'role' => 'php',
    ),
    'PEAR/Frontend/CLI.php' => 
    array (
      'role' => 'php',
    ),
    'PEAR/Builder.php' => 
    array (
      'role' => 'php',
    ),
    'PEAR/Installer.php' => 
    array (
      'role' => 'php',
    ),
    'PEAR/Packager.php' => 
    array (
      'role' => 'php',
    ),
    'PEAR/Registry.php' => 
    array (
      'role' => 'php',
    ),
    'PEAR/Remote.php' => 
    array (
      'role' => 'php',
    ),
    'OS/Guess.php' => 
    array (
      'role' => 'php',
    ),
    'scripts/pear.sh' => 
    array (
      'role' => 'script',
      'install-as' => 'pear',
      'baseinstalldir' => '/',
      'replacements' => 
      array (
        0 => 
        array (
          'from' => '@php_bin@',
          'to' => 'php_bin',
          'type' => 'pear-config',
        ),
        1 => 
        array (
          'from' => '@php_dir@',
          'to' => 'php_dir',
          'type' => 'pear-config',
        ),
        2 => 
        array (
          'from' => '@pear_version@',
          'to' => 'version',
          'type' => 'package-info',
        ),
        3 => 
        array (
          'from' => '@include_path@',
          'to' => 'php_dir',
          'type' => 'pear-config',
        ),
      ),
    ),
    'scripts/pear.bat' => 
    array (
      'role' => 'script',
      'platform' => 'windows',
      'install-as' => 'pear.bat',
      'baseinstalldir' => '/',
      'replacements' => 
      array (
        0 => 
        array (
          'from' => '@bin_dir@',
          'to' => 'bin_dir',
          'type' => 'pear-config',
        ),
        1 => 
        array (
          'from' => '@php_bin@',
          'to' => 'php_bin',
          'type' => 'pear-config',
        ),
        2 => 
        array (
          'from' => '@include_path@',
          'to' => 'php_dir',
          'type' => 'pear-config',
        ),
      ),
    ),
    'scripts/pearcmd.php' => 
    array (
      'role' => 'php',
      'install-as' => 'pearcmd.php',
      'baseinstalldir' => '/',
      'replacements' => 
      array (
        0 => 
        array (
          'from' => '@php_bin@',
          'to' => 'php_bin',
          'type' => 'pear-config',
        ),
        1 => 
        array (
          'from' => '@php_dir@',
          'to' => 'php_dir',
          'type' => 'pear-config',
        ),
        2 => 
        array (
          'from' => '@pear_version@',
          'to' => 'version',
          'type' => 'package-info',
        ),
        3 => 
        array (
          'from' => '@include_path@',
          'to' => 'php_dir',
          'type' => 'pear-config',
        ),
      ),
    ),
  ),
  'xsdversion' => '1.0',
  'package' => 'test',
  'summary' => 'PEAR test',
  'description' => 'The test
',
  'release_license' => 'PHP License',
  'maintainers' => 
  array (
    0 => 
    array (
      'handle' => 'test',
      'role' => 'lead',
      'name' => 'test tester',
      'email' => 'test@php.net',
    ),
  ),
  'version' => '1.3b4',
  'release_date' => '2003-11-17',
  'release_state' => 'beta',
  'release_notes' => 'test
',
  'release_deps' => 
  array (
    1 => 
    array (
      'type' => 'ext',
      'rel' => 'has',
      'optional' => 'yes',
      'name' => 'xmlrpc',
    ),
  ),
  'configure_options' => 
  array (
    0 => 
    array (
      'name' => 'test',
      'prompt' => 'The prompt test',
      'default' => 'foo',
    ),
  ),
  'changelog' => 
  array (
    0 => 
    array (
      'version' => '0.1',
      'release_date' => '2003-07-21',
      'release_license' => 'PHP License',
      'release_state' => 'alpha',
      'release_notes' => 'First release of test
',
    ),
    1 => 
    array (
      'version' => '0.2',
      'release_date' => '2003-07-21',
      'release_license' => 'PHP License',
      'release_state' => 'alpha',
      'release_notes' => 'Generation of package.xml from scratch is now supported.  In addition,
generation of <provides> is supported and so is addition of
maintainers and configure options

- Fixed a bug in <release> generation
- Added _addProvides() to generate a <provides> section
',
    ),
  ),
), $ret, 'return');

echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
