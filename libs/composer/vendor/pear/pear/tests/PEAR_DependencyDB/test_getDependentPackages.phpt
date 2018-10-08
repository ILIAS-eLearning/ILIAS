--TEST--
PEAR_DependencyDB->getDependentPackages()
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
$at = $db->getDependentPackages($p);
function atsort($a, $b)
{
    return strcasecmp($a['package'], $b['package']);
}
usort($at, 'atsort');
$phpunit->assertEquals(array (
  0 => 
  array (
    'channel' => 'pear.php.net',
    'package' => 'db',
  ),
  1 => 
  array (
    'channel' => 'pear.php.net',
    'package' => 'http',
  ),
  2 => 
  array (
    'channel' => 'pear.php.net',
    'package' => 'liveuser',
  ),
  3 => 
  array (
    'channel' => 'pear.php.net',
    'package' => 'mdb2',
  ),
  4 => 
  array (
    'channel' => 'pear.php.net',
    'package' => 'php_archive',
  ),
  5 => 
  array (
    'channel' => 'pear.php.net',
    'package' => 'xml_parser',
  ),
  6 => 
  array (
    'channel' => 'pear.php.net',
    'package' => 'xml_serializer',
  ),
  7 => 
  array (
    'channel' => 'pear.php.net',
    'package' => 'xml_util',
  ),
), $at, 'PEAR');
$p = array('package' => 'LiveUser', 'channel' => 'pear.php.net');
$phpunit->assertEquals(false, $db->getDependentPackages($p), 'LiveUser');
$p = array('package' => 'Slonk', 'channel' => 'pear.php.net');
$phpunit->assertEquals(false, $db->getDependentPackages($p), 'Slonk');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
