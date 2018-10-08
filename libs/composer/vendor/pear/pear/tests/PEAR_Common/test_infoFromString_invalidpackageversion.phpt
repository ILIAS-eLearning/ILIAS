--TEST--
PEAR_Common::infoFromString test (valid xml, invalid package version)
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
if (!function_exists('token_get_all')) {
    echo 'skip';
}
?>
--FILE--
<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'setup.php.inc';

$php5 = version_compare(phpversion(), '5.0.0', '>=');
$ret = $common->infoFromString('<?xml version="1.0" encoding="ISO-8859-1" ?>' .
    '<package version="10000000"></package>');

$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error',
        'message' => 'package.xml file "" has unsupported package.xml <package> version "10000000"'),
        ), 'error message');
$phpunit->assertIsa('PEAR_Error', $ret, 'return');

echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
