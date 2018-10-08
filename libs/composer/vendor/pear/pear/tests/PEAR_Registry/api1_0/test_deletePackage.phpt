--TEST--
PEAR_Registry->deletePackage() v1.0
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

$reg->addPackage("pkg2", array("name" => "pkg2", "version" => "2.0", "filelist" => $files2, 'maintainers' => array()));
$reg->addPackage("pkg3", array("name" => "pkg3", "version" => "3.0", "filelist" => $files3, 'maintainers' => array()));
$phpunit->assertRegEquals('dumping registry...
channel __uri:
channel doc.php.net:
channel pear.php.net:
pkg2: version="2.0" filelist=array(pkg2-1.php[role=php],pkg2-2.php[role=php,baseinstalldir=pkg2]) maintainers="Array" _lastmodified is set
pkg3: version="3.0" filelist=array(pkg3-1.php[role=php],pkg3-2.php[role=php,baseinstalldir=pkg3]) maintainers="Array" _lastmodified is set
channel pecl.php.net:
dump done
', $reg, 'after adding pkg2 and pkg3');

$phpunit->assertTrue($reg->deletePackage("pkg2"), 'first delete');
$phpunit->assertRegEquals('dumping registry...
channel __uri:
channel doc.php.net:
channel pear.php.net:
pkg3: version="3.0" filelist=array(pkg3-1.php[role=php],pkg3-2.php[role=php,baseinstalldir=pkg3]) maintainers="Array" _lastmodified is set
channel pecl.php.net:
dump done
', $reg, 'after first delete');

$phpunit->assertFalse($reg->deletePackage("pkg2"), 'second delete');
$phpunit->assertRegEquals('dumping registry...
channel __uri:
channel doc.php.net:
channel pear.php.net:
pkg3: version="3.0" filelist=array(pkg3-1.php[role=php],pkg3-2.php[role=php,baseinstalldir=pkg3]) maintainers="Array" _lastmodified is set
channel pecl.php.net:
dump done
', $reg, 'after second delete');

$subreg = new PEAR_Registry($statedir);
$phpunit->assertRegEquals('dumping registry...
channel __uri:
channel doc.php.net:
channel pear.php.net:
pkg3: version="3.0" filelist=array(pkg3-1.php[role=php],pkg3-2.php[role=php,baseinstalldir=pkg3]) maintainers="Array" _lastmodified is set
channel pecl.php.net:
dump done
', $subreg, 'after second delete, new object');

$phpunit->assertTrue($reg->deletePackage("pkg3"), 'third delete');
$phpunit->assertRegEquals('dumping registry...
channel __uri:
channel doc.php.net:
channel pear.php.net:
channel pecl.php.net:
dump done
', $reg, 'after third delete');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
