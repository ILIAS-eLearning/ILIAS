--TEST--
PEAR_Registry->checkFilemap() v1.0
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

$reg->addPackage("pkg3", array("name" => "pkg3", "version" => "3.0", "filelist" => $files3, 'maintainers' => array()));
$phpunit->assertRegEquals('dumping registry...
channel __uri:
channel doc.php.net:
channel pear.php.net:
pkg3: version="3.0" filelist=array(pkg3-1.php[role=php],pkg3-2.php[role=php,baseinstalldir=pkg3]) maintainers="Array" _lastmodified is set
channel pecl.php.net:
dump done
', $reg, 'after adding pkg3');

$reg->updatePackage("pkg3", array("version" => "3.1b1", "status" => "beta"));
$phpunit->assertRegEquals('dumping registry...
channel __uri:
channel doc.php.net:
channel pear.php.net:
pkg3: version="3.1b1" filelist=array(pkg3-1.php[role=php],pkg3-2.php[role=php,baseinstalldir=pkg3]) maintainers="Array" _lastmodified is set status="beta"
channel pecl.php.net:
dump done
', $reg, 'after update of pkg3');

$testing = $reg->checkFilemap(array_merge($files3, $files2), 'pkg3');
$phpunit->assertEquals(array(
  'pkg3-1.php' => 'pkg3',
  'pkg3' . DIRECTORY_SEPARATOR . 'pkg3-2.php' => 'pkg3',
),
    $testing, '');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
