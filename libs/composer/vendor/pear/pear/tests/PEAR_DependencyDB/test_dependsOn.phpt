--TEST--
PEAR_DependencyDB->dependsOn()
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
copyItem('registry'); // setup
copyItem('db'); // faster setup (no rebuild)
$db = new PEAR_DependencyDB;
$db->setConfig($config);
$phpunit->assertTrue($db->dependsOn(array('channel' => 'pear.php.net',
    'package' => 'SOAP'), array('channel' => 'pear.php.net', 'package' => 'Net_DIME')),
    'deep');
$phpunit->assertFalse($db->dependsOn(array('channel' => 'pear.php.net',
    'package' => 'Net_DIME'), array('channel' => 'pear.php.net', 'package' => 'SOAP')),
    'reverse deep');
$phpunit->assertTrue($db->dependsOn(array('channel' => 'pear.php.net',
    'package' => 'PEAR_PackageFileManager'),
    array('channel' => 'pear.php.net', 'package' => 'PEAR')),
    'shallow');
$phpunit->assertFalse($db->dependsOn(array('channel' => 'pear.php.net',
    'package' => 'PEAR'),
    array('channel' => 'pear.php.net', 'package' => 'PEAR_PackageFileManager')),
    'shallow bad');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
