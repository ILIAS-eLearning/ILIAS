--TEST--
PEAR_Registry->updatePackage() v1.0
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
$statedir = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'registry_tester';
if (file_exists($statedir)) {
    // don't delete existing directories!
    echo 'skip';
}
?>
--FILE--
<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'setup.php.inc';
$subreg = new PEAR_Registry($statedir);
$phpunit->assertRegEquals('dumping registry...
channel __uri:
channel doc.php.net:
channel pear.php.net:
channel pecl.php.net:
dump done
', $reg, 'Initial dump is incorrect');

$reg->addPackage("pkg1", array("name" => "pkg1", "version" => "1.0", "filelist" => $files1, 'maintainers' => array()));
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

$reg->addPackage("pkg2", array("name" => "pkg2", "version" => "2.0", "filelist" => $files2, 'maintainers' => array()));
$reg->addPackage("pkg3", array("name" => "pkg3", "version" => "3.0", "filelist" => $files3, 'maintainers' => array()));
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

$phpunit->assertTrue($reg->updatePackage("pkg2", array("version" => "2.1", 'maintainers' => array())),
    'update pkg2 return');

$phpunit->assertRegEquals('dumping registry...
channel __uri:
channel doc.php.net:
channel pear.php.net:
pkg1: version="1.0" filelist=array(pkg1-1.php[role=php],pkg1-2.php[role=php,baseinstalldir=pkg1]) maintainers="Array" _lastmodified is set
pkg2: version="2.1" filelist=array(pkg2-1.php[role=php],pkg2-2.php[role=php,baseinstalldir=pkg2]) maintainers="Array" _lastmodified is set
pkg3: version="3.0" filelist=array(pkg3-1.php[role=php],pkg3-2.php[role=php,baseinstalldir=pkg3]) maintainers="Array" _lastmodified is set
channel pecl.php.net:
dump done
', $reg, 'after updating pkg2');
$phpunit->assertRegEquals('dumping registry...
channel __uri:
channel doc.php.net:
channel pear.php.net:
pkg1: version="1.0" filelist=array(pkg1-1.php[role=php],pkg1-2.php[role=php,baseinstalldir=pkg1]) maintainers="Array" _lastmodified is set
pkg2: version="2.1" filelist=array(pkg2-1.php[role=php],pkg2-2.php[role=php,baseinstalldir=pkg2]) maintainers="Array" _lastmodified is set
pkg3: version="3.0" filelist=array(pkg3-1.php[role=php],pkg3-2.php[role=php,baseinstalldir=pkg3]) maintainers="Array" _lastmodified is set
channel pecl.php.net:
dump done
', $subreg, 'after updating pkg2, new registry object');

$phpunit->assertTrue($reg->updatePackage("pkg3", array("version" => "3.1b1", "status" => "beta", 'maintainers' => array()))
    , 'update pkg3 return');
$phpunit->assertRegEquals('dumping registry...
channel __uri:
channel doc.php.net:
channel pear.php.net:
pkg1: version="1.0" filelist=array(pkg1-1.php[role=php],pkg1-2.php[role=php,baseinstalldir=pkg1]) maintainers="Array" _lastmodified is set
pkg2: version="2.1" filelist=array(pkg2-1.php[role=php],pkg2-2.php[role=php,baseinstalldir=pkg2]) maintainers="Array" _lastmodified is set
pkg3: version="3.1b1" filelist=array(pkg3-1.php[role=php],pkg3-2.php[role=php,baseinstalldir=pkg3]) maintainers="Array" _lastmodified is set status="beta"
channel pecl.php.net:
dump done
', $reg, 'after updating pkg3 1');

$phpunit->assertTrue($reg->updatePackage("pkg3", array("filelist" => $files3_new)),
    'second update pkg3 return');
$phpunit->assertRegEquals('dumping registry...
channel __uri:
channel doc.php.net:
channel pear.php.net:
pkg1: version="1.0" filelist=array(pkg1-1.php[role=php],pkg1-2.php[role=php,baseinstalldir=pkg1]) maintainers="Array" _lastmodified is set
pkg2: version="2.1" filelist=array(pkg2-1.php[role=php],pkg2-2.php[role=php,baseinstalldir=pkg2]) maintainers="Array" _lastmodified is set
pkg3: version="3.1b1" filelist=array(pkg3-3.php[role=php,baseinstalldir=pkg3],pkg3-4.php[role=php]) maintainers="Array" _lastmodified is set status="beta"
channel pecl.php.net:
dump done
', $reg, 'after updating pkg3 2');
echo "tests done";
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
