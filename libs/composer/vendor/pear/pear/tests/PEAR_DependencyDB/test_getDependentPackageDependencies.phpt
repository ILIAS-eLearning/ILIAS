--TEST--
PEAR_DependencyDB->getDependentPackageDependencies()
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
$app = $db->getDependentPackageDependencies($p);
ksort($app['pear.php.net']);
$phpunit->assertEquals(array (
  'pear.php.net' => 
  array (
    'db' => 
    array (
      0 => 
      array (
        'dep' => 
        array (
          'name' => 'PEAR',
          'channel' => 'pear.php.net',
          'min' => '1.0b1',
        ),
        'type' => 'required',
        'group' => false,
      ),
    ),
    'http' => 
    array (
      0 => 
      array (
        'dep' => 
        array (
          'name' => 'PEAR',
          'channel' => 'pear.php.net',
        ),
        'type' => 'required',
        'group' => false,
      ),
    ),
    'liveuser' => 
    array (
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
    ),
    'mdb2' => 
    array (
      0 => 
      array (
        'dep' => 
        array (
          'name' => 'PEAR',
          'channel' => 'pear.php.net',
          'min' => '1.0b1',
        ),
        'type' => 'required',
        'group' => false,
      ),
    ),
    'php_archive' => 
    array (
      0 => 
      array (
        'dep' => 
        array (
          'name' => 'PEAR',
          'channel' => 'pear.php.net',
          'min' => '1.3.5',
        ),
        'type' => 'required',
        'group' => false,
      ),
    ),
    'xml_parser' => 
    array (
      0 => 
      array (
        'dep' => 
        array (
          'name' => 'PEAR',
          'channel' => 'pear.php.net',
        ),
        'type' => 'required',
        'group' => false,
      ),
    ),
    'xml_serializer' => 
    array (
      0 => 
      array (
        'dep' => 
        array (
          'name' => 'PEAR',
          'channel' => 'pear.php.net',
        ),
        'type' => 'required',
        'group' => false,
      ),
    ),
    'xml_util' => 
    array (
      0 => 
      array (
        'dep' => 
        array (
          'name' => 'PEAR',
          'channel' => 'pear.php.net',
        ),
        'type' => 'required',
        'group' => false,
      ),
    ),
  ),
), $app, 'PEAR');
$p = array('package' => 'LiveUser', 'channel' => 'pear.php.net');
$phpunit->assertEquals(false, $db->getDependentPackageDependencies($p), 'LiveUser');
$p = array('package' => 'Slonk', 'channel' => 'pear.php.net');
$phpunit->assertEquals(false, $db->getDependentPackageDependencies($p), 'Slonk');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
