--TEST--
PEAR_Registry->addPackage() v1.0
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

$phpunit->assertTrue($reg->addPackage("pkg1", array("name" => "pkg1", "version" => "1.0", "filelist" => $files1, 'maintainers' => array())), 'add pkg1 return');
$phpunit->assertRegEquals('dumping registry...
channel __uri:
channel doc.php.net:
channel pear.php.net:
pkg1: version="1.0" filelist=array(pkg1-1.php[role=php],pkg1-2.php[role=php,baseinstalldir=pkg1]) maintainers="Array" _lastmodified is set
channel pecl.php.net:
dump done
', $reg, 'After adding pkg1');

$subreg = new PEAR_Registry($statedir);
$phpunit->assertRegEquals('dumping registry...
channel __uri:
channel doc.php.net:
channel pear.php.net:
pkg1: version="1.0" filelist=array(pkg1-1.php[role=php],pkg1-2.php[role=php,baseinstalldir=pkg1]) maintainers="Array" _lastmodified is set
channel pecl.php.net:
dump done
', $subreg, 'After adding pkg1, new registry object');
unset($subreg);

$phpunit->assertTrue($reg->addPackage("pkg2", array("name" => "pkg2", "version" => "2.0", "filelist" => $files2, 'maintainers' => array())), 'add pkg2 return');
$phpunit->assertTrue($reg->addPackage("pkg3", array("name" => "pkg3", "version" => "3.0", "filelist" => $files3, 'maintainers' => array())), 'add pkg3 return');
$phpunit->assertRegEquals('dumping registry...
channel __uri:
channel doc.php.net:
channel pear.php.net:
pkg1: version="1.0" filelist=array(pkg1-1.php[role=php],pkg1-2.php[role=php,baseinstalldir=pkg1]) maintainers="Array" _lastmodified is set
pkg2: version="2.0" filelist=array(pkg2-1.php[role=php],pkg2-2.php[role=php,baseinstalldir=pkg2]) maintainers="Array" _lastmodified is set
pkg3: version="3.0" filelist=array(pkg3-1.php[role=php],pkg3-2.php[role=php,baseinstalldir=pkg3]) maintainers="Array" _lastmodified is set
channel pecl.php.net:
dump done
', $reg, 'after adding pkg2 and pkg3');

$subreg = new PEAR_Registry($statedir);
$phpunit->assertRegEquals('dumping registry...
channel __uri:
channel doc.php.net:
channel pear.php.net:
pkg1: version="1.0" filelist=array(pkg1-1.php[role=php],pkg1-2.php[role=php,baseinstalldir=pkg1]) maintainers="Array" _lastmodified is set
pkg2: version="2.0" filelist=array(pkg2-1.php[role=php],pkg2-2.php[role=php,baseinstalldir=pkg2]) maintainers="Array" _lastmodified is set
pkg3: version="3.0" filelist=array(pkg3-1.php[role=php],pkg3-2.php[role=php,baseinstalldir=pkg3]) maintainers="Array" _lastmodified is set
channel pecl.php.net:
dump done
', $subreg, 'after adding pkg2 and pkg3, new registry object');
$phpunit->assertFalse($reg->addPackage('pkg1', array()), 'bad addpackage');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
