--TEST--
PEAR_Registry->listPackages() v1.0
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'setup.php.inc';
$phpunit->assertRegEquals('dumping registry...
channel __uri:
channel doc.php.net:
channel pear.php.net:
channel pecl.php.net:
dump done
', $reg, 'Initial dump is incorrect');
$phpunit->assertEquals(array (
), $reg->listPackages(), 'list packages initial');

$phpunit->assertTrue($reg->addPackage("pkg1", array("name" => "pkg1", "version" => "1.0", "filelist" => $files1, 'maintainers' => array())), 'add pkg1 return');

$subreg = new PEAR_Registry($statedir);
$phpunit->assertEquals(array (
  0 => 'pkg1',
), $reg->listPackages(), 'list packages after adding pkg1');
$phpunit->assertEquals(array (
  0 => 'pkg1',
), $subreg->listPackages(), 'list packages after adding pkg1, new object');

$phpunit->assertTrue($reg->addPackage("pkg2", array("name" => "pkg2", "version" => "2.0", "filelist" => $files2, 'maintainers' => array())), 'add pkg2 return');
$phpunit->assertTrue($reg->addPackage("pkg3", array("name" => "pkg3", "version" => "3.0", "filelist" => $files3, 'maintainers' => array())), 'add pkg3 return');

$phpunit->assertUnorderedArray(array (
  0 => 'pkg1',
  1 => 'pkg2',
  2 => 'pkg3',
), $reg->listPackages(), 'list packages after adding 2 and 3');

$phpunit->assertUnorderedArray(array (
  0 => 'pkg1',
  1 => 'pkg2',
  2 => 'pkg3',
), $subreg->listPackages(), 'list packages after adding 2 and 3, new object');
$phpunit->assertTrue($reg->deletePackage('pkg1'), 'delete pkg1');

$phpunit->assertUnorderedArray(array (
  0 => 'pkg2',
  1 => 'pkg3',
), $reg->listPackages(), 'list packages after deleting 1');

$phpunit->assertUnorderedArray(array (
  0 => 'pkg2',
  1 => 'pkg3',
), $subreg->listPackages(), 'list packages after deleting 1, new object');

echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
