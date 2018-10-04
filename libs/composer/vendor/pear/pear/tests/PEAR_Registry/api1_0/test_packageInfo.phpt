--TEST--
PEAR_Registry->packageInfo() v1.0
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'setup.php.inc';
$subreg = new PEAR_Registry($statedir);
$phpunit->assertEquals(array(), $reg->packageInfo(), 'initial packageinfo generic');
$phpunit->assertNull($reg->packageInfo('foo'), 'non-existent package');
$phpunit->assertTrue($reg->addPackage("pkg1", array("name" => "pkg1", "version" => "1.0", "filelist" => $files1, 'maintainers' => array())), 'add pkg1 return');
$phpunit->assertPackageinfoEquals(array (
  0 => 
  array (
    'name' => 'pkg1',
    'version' => '1.0',
    'filelist' => 
    array (
      'pkg1-1.php' => 
      array (
        'role' => 'php',
      ),
      'pkg1-2.php' => 
      array (
        'role' => 'php',
        'baseinstalldir' => 'pkg1',
      ),
    ),
    'maintainers' => array(),
    '_lastmodified' => 1097585674,
  ),
), $reg->packageInfo(), 'packageinfo pkg1 generic');
$phpunit->assertPackageinfoEquals(array (
    'name' => 'pkg1',
    'version' => '1.0',
    'filelist' => 
    array (
      'pkg1-1.php' => 
      array (
        'role' => 'php',
      ),
      'pkg1-2.php' => 
      array (
        'role' => 'php',
        'baseinstalldir' => 'pkg1',
      ),
    ),
    'maintainers' => array(),
    '_lastmodified' => 1097585674,
  ), $reg->packageInfo('pkg1'), 'packageinfo pkg1 specific');

$phpunit->assertTrue($reg->addPackage("pkg2", array("name" => "pkg2", "version" => "2.0", "filelist" => $files2, 'maintainers' => array())), 'add pkg2 return');
$phpunit->assertPackageinfoEquals(array (
  0 => 
  array (
    'name' => 'pkg1',
    'version' => '1.0',
    'filelist' => 
    array (
      'pkg1-1.php' => 
      array (
        'role' => 'php',
      ),
      'pkg1-2.php' => 
      array (
        'role' => 'php',
        'baseinstalldir' => 'pkg1',
      ),
    ),
    'maintainers' => array(),
    '_lastmodified' => 1097585674,
  ),
  1 => 
  array (
    'name' => 'pkg2',
    'version' => '2.0',
    'filelist' => 
    array (
      'pkg2-1.php' => 
      array (
        'role' => 'php',
      ),
      'pkg2-2.php' => 
      array (
        'role' => 'php',
        'baseinstalldir' => 'pkg2',
      ),
    ),
    'maintainers' => array(),
    '_lastmodified' => 1097586528,
  ),
), $reg->packageInfo(), 'packageinfo pkg1+2 generic');
$phpunit->assertPackageinfoEquals(array (
    'name' => 'pkg2',
    'version' => '2.0',
    'filelist' => 
    array (
      'pkg2-1.php' => 
      array (
        'role' => 'php',
      ),
      'pkg2-2.php' => 
      array (
        'role' => 'php',
        'baseinstalldir' => 'pkg2',
      ),
    ),
    'maintainers' => array(),
    '_lastmodified' => 1097585674,
  ), $reg->packageInfo('pkg2'), 'packageinfo pkg2 specific');
$phpunit->assertTrue($reg->addPackage("pkg3", array("name" => "pkg3", "version" => "3.0", "filelist" => $files3, 'maintainers' => array())), 'add pkg3 return');
$phpunit->assertPackageinfoEquals(array (
  0 => 
  array (
    'name' => 'pkg1',
    'version' => '1.0',
    'filelist' => 
    array (
      'pkg1-1.php' => 
      array (
        'role' => 'php',
      ),
      'pkg1-2.php' => 
      array (
        'role' => 'php',
        'baseinstalldir' => 'pkg1',
      ),
    ),
    'maintainers' => array(),
    '_lastmodified' => 1097585674,
  ),
  1 => 
  array (
    'name' => 'pkg2',
    'version' => '2.0',
    'filelist' => 
    array (
      'pkg2-1.php' => 
      array (
        'role' => 'php',
      ),
      'pkg2-2.php' => 
      array (
        'role' => 'php',
        'baseinstalldir' => 'pkg2',
      ),
    ),
    'maintainers' => array(),
    '_lastmodified' => 1097586528,
  ),
  2 =>
  array (
    'name' => 'pkg3',
    'version' => '3.0',
    'filelist' => 
    array (
      'pkg3-1.php' => 
      array (
        'role' => 'php',
      ),
      'pkg3-2.php' => 
      array (
        'role' => 'php',
        'baseinstalldir' => 'pkg3',
      ),
    ),
    'maintainers' => array(),
    '_lastmodified' => 1097585674,
  ),
), $reg->packageInfo(), 'packageinfo pkg1+2+3 generic');
$phpunit->assertPackageinfoEquals(array (
    'name' => 'pkg3',
    'version' => '3.0',
    'filelist' => 
    array (
      'pkg3-1.php' => 
      array (
        'role' => 'php',
      ),
      'pkg3-2.php' => 
      array (
        'role' => 'php',
        'baseinstalldir' => 'pkg3',
      ),
    ),
    'maintainers' => array(),
    '_lastmodified' => 1097585674,
  ), $reg->packageInfo('pkg3'), 'packageinfo pkg3 specific');

$phpunit->assertPackageinfoEquals(array (
  0 => 
  array (
    'name' => 'pkg1',
    'version' => '1.0',
    'filelist' => 
    array (
      'pkg1-1.php' => 
      array (
        'role' => 'php',
      ),
      'pkg1-2.php' => 
      array (
        'role' => 'php',
        'baseinstalldir' => 'pkg1',
      ),
    ),
    'maintainers' => array(),
    '_lastmodified' => 1097585674,
  ),
  1 => 
  array (
    'name' => 'pkg2',
    'version' => '2.0',
    'filelist' => 
    array (
      'pkg2-1.php' => 
      array (
        'role' => 'php',
      ),
      'pkg2-2.php' => 
      array (
        'role' => 'php',
        'baseinstalldir' => 'pkg2',
      ),
    ),
    'maintainers' => array(),
    '_lastmodified' => 1097586528,
  ),
  2 =>
  array (
    'name' => 'pkg3',
    'version' => '3.0',
    'filelist' => 
    array (
      'pkg3-1.php' => 
      array (
        'role' => 'php',
      ),
      'pkg3-2.php' => 
      array (
        'role' => 'php',
        'baseinstalldir' => 'pkg3',
      ),
    ),
    'maintainers' => array(),
    '_lastmodified' => 1097585674,
  ),
), $subreg->packageInfo(), 'packageinfo pkg1+2+3 generic, another object');
$phpunit->assertPackageinfoEquals(array (
    'name' => 'pkg3',
    'version' => '3.0',
    'filelist' => 
    array (
      'pkg3-1.php' => 
      array (
        'role' => 'php',
      ),
      'pkg3-2.php' => 
      array (
        'role' => 'php',
        'baseinstalldir' => 'pkg3',
      ),
    ),
    'maintainers' => array(),
    '_lastmodified' => 1097585674,
  ), $subreg->packageInfo('pkg3'), 'packageinfo pkg3 specific, another object');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
