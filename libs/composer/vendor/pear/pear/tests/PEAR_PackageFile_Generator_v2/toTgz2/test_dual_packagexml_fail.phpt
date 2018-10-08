--TEST--
PEAR_PackageFile_Generator_v2->toTgz2() (dual package.xml version for BC, failure)
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php


$save____dir = getcwd();
require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'setup.php.inc';
chdir($temp_path);
require_once 'PEAR/Packager.php';
require_once 'PEAR/PackageFile/Parser/v1.php';

$v1parser = new PEAR_PackageFile_Parser_v1;
$v1parser->setConfig($config);
$v1parser->setLogger($fakelog);
$pf1 = &$v1parser->parse(implode('', file(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'packagefiles' .
    DIRECTORY_SEPARATOR . 'invalidv1.xml')), dirname(__FILE__) . DIRECTORY_SEPARATOR . 'packagefiles' .
    DIRECTORY_SEPARATOR . 'invalidv1.xml');
$v1generator = &$pf1->getDefaultGenerator();
$pf = $parser->parse(implode('', file(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'packagefiles' .
    DIRECTORY_SEPARATOR . 'failphprelease.xml')), dirname(__FILE__) . DIRECTORY_SEPARATOR . 'packagefiles' .
    DIRECTORY_SEPARATOR . 'failphprelease.xml');
$generator = &$pf->getDefaultGenerator();
$packager = new PEAR_Packager;
mkdir($temp_path . DIRECTORY_SEPARATOR . 'gron');
$e = $generator->toTgz2($packager, $pf1, true, $temp_path . DIRECTORY_SEPARATOR . 'gron');

$phpunit->assertErrors(array(
    array('package' => 'PEAR_PackageFile_v2', 'message' => 'package.xml 1.0 file "validv1.xml" is not present in <contents>'),
    array('package' => 'PEAR_PackageFile_v2', 'message' => 'package.xml 1.0 has unmatched extra maintainers "double"'),
    array('package' => 'PEAR_PackageFile_v2', 'message' => 'package.xml 2.0 has unmatched extra maintainers "somebody, somebodyelse"'),
    array('package' => 'PEAR_PackageFile_v2', 'message' => 'package.xml 1.0 summary "foo1" does not match "foo"'),
    array('package' => 'PEAR_PackageFile_v2', 'message' => 'package.xml 1.0 state "beta" does not match "alpha"'),
    array('package' => 'PEAR_PackageFile_v2', 'message' => 'package.xml 1.0 package "foo" does not match "foo1"'),
    array('package' => 'PEAR_PackageFile_v2', 'message' => 'package.xml 1.0 maintainer "single" email "joe@example.com" does not match "joz@example.com"'),
    array('package' => 'PEAR_PackageFile_v2', 'message' => 'package.xml 1.0 maintainer "single" name "person" does not match "personally"'),
    array('package' => 'PEAR_PackageFile_v2', 'message' => 'package.xml 1.0 version "1.2.0a11" does not match "1.2.0a1"'),
    array('package' => 'PEAR_PackageFile_v2', 'message' => "package.xml 1.0 description \"foo\nhi there1\" does not match \"foo\nhi there\""),
    array('package' => 'PEAR_PackageFile_v2', 'message' => 'package.xml 1.0 release notes "here are the
multi-1ine
..." do not match "here are the
multi-line
..."'),
    array('package' => 'PEAR_Error', 'message' => 'PEAR_Packagefile_v2::toTgz: "invalidv1.xml" is not equivalent to "failphprelease.xml"'),
), 'errors');

$phpunit->assertEquals(array (), $fakelog->getLog(), 'packaging log');
chdir($save____dir);

echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(dirname(__FILE__)) . '/teardown.php.inc';
?>
--EXPECT--
tests done
