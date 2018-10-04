--TEST--
PEAR_Installer_Role::registerRoles()
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
} else {
    echo 'info low-level test, could fail and still be OK';
}
?>
--FILE--
<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'setup.php.inc';
PEAR_Installer_Role::registerRoles(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'roles');
$phpunit->assertEquals(array (
  'PEAR_Installer_Role_Dataf' => 
  array (
    'attribs' => 
    array (
      'version' => '1.0',
    ),
    'releasetypes' => 
    array (
      0 => 'php',
      1 => 'extsrc',
      2 => 'extbin',
    ),
    'installable' => '1',
    'locationconfig' => 'data_dir',
    'honorsbaseinstall' => '',
    'unusualbaseinstall' => '',
    'phpfile' => '',
    'executable' => '',
    'phpextension' => '',
    'config_vars' => '',
  ),
  'PEAR_Installer_Role_Docf' => 
  array (
    'attribs' => 
    array (
      'version' => '1.0',
    ),
    'releasetypes' => 
    array (
      0 => 'php',
      1 => 'extsrc',
      2 => 'extbin',
    ),
    'installable' => '1',
    'locationconfig' => 'doc_dir',
    'honorsbaseinstall' => '',
    'unusualbaseinstall' => '',
    'phpfile' => '',
    'executable' => '',
    'phpextension' => '',
    'config_vars' => '',
  ),
  'PEAR_Installer_Role_Extf' => 
  array (
    'attribs' => 
    array (
      'version' => '1.0',
    ),
    'releasetypes' => 
    array (
      0 => 'extbin',
    ),
    'installable' => '1',
    'locationconfig' => 'ext_dir',
    'honorsbaseinstall' => '1',
    'unusualbaseinstall' => '',
    'phpfile' => '',
    'executable' => '',
    'phpextension' => '1',
    'config_vars' => '',
  ),
  'PEAR_Installer_Role_Phpf' => 
  array (
    'attribs' => 
    array (
      'version' => '1.0',
    ),
    'releasetypes' => 
    array (
      0 => 'php',
    ),
    'installable' => '1',
    'locationconfig' => 'php_dir',
    'honorsbaseinstall' => '1',
    'unusualbaseinstall' => '',
    'phpfile' => '1',
    'executable' => '',
    'phpextension' => '',
    'config_vars' => '',
  ),
  'PEAR_Installer_Role_Scriptf' => 
  array (
    'attribs' => 
    array (
      'version' => '1.0',
    ),
    'releasetypes' => 
    array (
      0 => 'php',
      1 => 'extsrc',
      2 => 'extbin',
    ),
    'installable' => '1',
    'locationconfig' => 'bin_dir',
    'honorsbaseinstall' => '1',
    'unusualbaseinstall' => '',
    'phpfile' => '',
    'executable' => '1',
    'phpextension' => '',
    'config_vars' => '',
  ),
  'PEAR_Installer_Role_Srcf' => 
  array (
    'attribs' => 
    array (
      'version' => '1.0',
    ),
    'releasetypes' => 
    array (
      0 => 'extsrc',
    ),
    'installable' => '',
    'locationconfig' => '',
    'honorsbaseinstall' => '',
    'unusualbaseinstall' => '',
    'phpfile' => '',
    'executable' => '',
    'config_vars' => '',
  ),
  'PEAR_Installer_Role_Testf' => 
  array (
    'attribs' => 
    array (
      'version' => '1.0',
    ),
    'releasetypes' => 
    array (
      0 => 'php',
      1 => 'extsrc',
      2 => 'extbin',
    ),
    'installable' => '1',
    'locationconfig' => 'test_dir',
    'honorsbaseinstall' => '',
    'unusualbaseinstall' => '',
    'phpfile' => '',
    'executable' => '',
    'phpextension' => '',
    'config_vars' => '',
  ),
), $GLOBALS['_PEAR_INSTALLER_ROLES'], 'registered');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
