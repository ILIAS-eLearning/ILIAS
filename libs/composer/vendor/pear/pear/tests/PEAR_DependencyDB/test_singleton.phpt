--TEST--
PEAR_DependencyDB::singleton()
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
$a = &PEAR_DependencyDB::singleton($config);
$b = new PEAR_DependencyDB;
$phpunit->assertNotSame($a, $b, 'singleton 1');
$c = &PEAR_DependencyDB::singleton($config);
$c->hi = 1;
$phpunit->assertEquals(1, @$a->hi, 'singleton 2');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
