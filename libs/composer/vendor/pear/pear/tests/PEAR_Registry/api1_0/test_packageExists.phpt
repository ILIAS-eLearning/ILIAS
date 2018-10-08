--TEST--
PEAR_Registry->packageExists() v1.0
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

$phpunit->assertFalse($reg->packageExists('pkg1'), 'before create');

$subreg = new PEAR_Registry($statedir);

$reg->addPackage("pkg1", array("name" => "pkg1", "version" => "1.0", "filelist" => $files1, 'maintainers' => array()));
$phpunit->assertRegEquals('dumping registry...
channel __uri:
channel doc.php.net:
channel pear.php.net:
pkg1: version="1.0" filelist=array(pkg1-1.php[role=php],pkg1-2.php[role=php,baseinstalldir=pkg1]) maintainers="Array" _lastmodified is set
channel pecl.php.net:
dump done
', $reg, 'After adding pkg1');

$phpunit->assertTrue($reg->packageExists('pkg1'), 'after create');

$phpunit->assertRegEquals('dumping registry...
channel __uri:
channel doc.php.net:
channel pear.php.net:
pkg1: version="1.0" filelist=array(pkg1-1.php[role=php],pkg1-2.php[role=php,baseinstalldir=pkg1]) maintainers="Array" _lastmodified is set
channel pecl.php.net:
dump done
', $subreg, 'After adding pkg1, new registry object');
$phpunit->assertTrue($subreg->packageExists('pkg1'), 'after create, subreg');
echo "tests done";
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
