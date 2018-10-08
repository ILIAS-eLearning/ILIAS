--TEST--
PEAR_DependencyDB->getDependencies()
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'setup.php.inc';
copyItem('registry'); //setup for nice clean rebuild
$db = &PEAR_DependencyDB::singleton($config);
$db->rebuildDB();
$p = array('package' => 'PEAR', 'channel' => 'pear.php.net');
$phpunit->assertEquals(array (
  0 => 
  array (
    'dep' => 
    array (
      'name' => 'Archive_Tar',
      'channel' => 'pear.php.net',
      'min' => '1.1',
      'recommended' => '1.3.1',
      'exclude' => '1.3.0',
    ),
    'type' => 'required',
    'group' => false,
  ),
  1 => 
  array (
    'dep' => 
    array (
      'name' => 'Console_Getopt',
      'channel' => 'pear.php.net',
      'min' => '1.2',
      'recommended' => '1.2',
    ),
    'type' => 'required',
    'group' => false,
  ),
  2 => 
  array (
    'dep' => 
    array (
      'name' => 'XML_RPC',
      'channel' => 'pear.php.net',
      'min' => '1.4.0',
      'recommended' => '1.4.1',
    ),
    'type' => 'required',
    'group' => false,
  ),
  3 => 
  array (
    'dep' => 
    array (
      'name' => 'PEAR_Frontend_Web',
      'channel' => 'pear.php.net',
      'max' => '0.5.0',
      'exclude' => '0.5.0',
      'conflicts' => '',
    ),
    'type' => 'required',
    'group' => false,
  ),
  4 => 
  array (
    'dep' => 
    array (
      'name' => 'PEAR_Frontend_Gtk',
      'channel' => 'pear.php.net',
      'max' => '0.4.0',
      'exclude' => '0.4.0',
      'conflicts' => '',
    ),
    'type' => 'required',
    'group' => false,
  ),
  5 => 
  array (
    'dep' => 
    array (
      'name' => 'PEAR_Frontend_Web',
      'channel' => 'pear.php.net',
      'min' => '0.5.0',
    ),
    'type' => 'optional',
    'group' => 'webinstaller',
  ),
  6 => 
  array (
    'dep' => 
    array (
      'name' => 'PEAR_Frontend_Gtk',
      'channel' => 'pear.php.net',
      'min' => '0.4.0',
    ),
    'type' => 'optional',
    'group' => 'gtkinstaller',
  ),
), $db->getDependencies($p), 'PEAR');
$p = array('package' => 'LiveUser', 'channel' => 'pear.php.net');
$phpunit->assertEquals(array (
  0 => 
  array (
    'dep' => 
    array (
      'name' => 'PEAR',
      'channel' => 'pear.php.net',
      'min' => '1.3.3',
    ),
    'type' => 'required',
    'group' => false,
  ),
  1 => 
  array (
    'dep' => 
    array (
      'name' => 'Event_Dispatcher',
      'channel' => 'pear.php.net',
    ),
    'type' => 'required',
    'group' => false,
  ),
  2 => 
  array (
    'dep' => 
    array (
      'name' => 'Log',
      'channel' => 'pear.php.net',
      'min' => '1.7.0',
    ),
    'type' => 'optional',
    'group' => false,
  ),
  3 => 
  array (
    'dep' => 
    array (
      'name' => 'DB',
      'channel' => 'pear.php.net',
      'min' => '1.6.0',
    ),
    'type' => 'optional',
    'group' => false,
  ),
  4 => 
  array (
    'dep' => 
    array (
      'name' => 'MDB',
      'channel' => 'pear.php.net',
      'min' => '1.1.4',
    ),
    'type' => 'optional',
    'group' => false,
  ),
  5 => 
  array (
    'dep' => 
    array (
      'name' => 'MDB2',
      'channel' => 'pear.php.net',
      'min' => '2.0.0beta4',
    ),
    'type' => 'optional',
    'group' => false,
  ),
  6 => 
  array (
    'dep' => 
    array (
      'name' => 'MDB2_Schema',
      'channel' => 'pear.php.net',
    ),
    'type' => 'optional',
    'group' => false,
  ),
  7 => 
  array (
    'dep' => 
    array (
      'name' => 'XML_Tree',
      'channel' => 'pear.php.net',
    ),
    'type' => 'optional',
    'group' => false,
  ),
  8 => 
  array (
    'dep' => 
    array (
      'name' => 'Crypt_RC4',
      'channel' => 'pear.php.net',
    ),
    'type' => 'optional',
    'group' => false,
  ),
), $db->getDependencies($p), 'LiveUser');
$p = array('package' => 'Slonk', 'channel' => 'pear.php.net');
$phpunit->assertEquals(false, $db->getDependencies($p), 'Slonk');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
