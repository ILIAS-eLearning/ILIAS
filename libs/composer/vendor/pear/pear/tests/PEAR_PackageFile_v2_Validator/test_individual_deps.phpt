--TEST--
PEAR_PackageFile_Parser_v2_Validator->validate(), individual dependency type tests
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'setup.php.inc';
$pathtopackagexml = dirname(__FILE__)  . DIRECTORY_SEPARATOR .
    'test_dependencies'. DIRECTORY_SEPARATOR . 'package-ind.xml';
$pf = $parser->parse(implode('', file($pathtopackagexml)), $pathtopackagexml);
$phpunit->assertIsa('PEAR_PackageFile_v2', $pf, 'ret');
$pf->validate();
$phpunit->assertErrors(array(
// required
// package
    array('package' => 'PEAR_PackageFile_v2', 'message' => 'Invalid tag order in <dependencies><required><package><name>blh</name>, found <moo> expected one of "exclude, nodefault, providesextension"'),
    array('package' => 'PEAR_PackageFile_v2', 'message' => 'Invalid tag order in <dependencies><required><package><name>blh2</name>, found <min> expected one of "providesextension"'),
    array('package' => 'PEAR_PackageFile_v2', 'message' => '<dependencies><required><package><name>blh2</name>: dependencies with a <uri> tag cannot have any versioning information'),
    array('package' => 'PEAR_PackageFile_v2', 'message' => '<dependencies><required><package><name>nouri</name>: channel cannot be __uri, this is a pseudo-channel reserved for uri dependencies only'),

// subpackage

    array('package' => 'PEAR_PackageFile_v2', 'message' => 'Invalid tag order in <dependencies><required><subpackage><name>blh</name>, found <moo> expected one of "exclude, nodefault, providesextension"'),
    array('package' => 'PEAR_PackageFile_v2', 'message' => 'Invalid tag order in <dependencies><required><subpackage><name>blh3</name>, found <recommended> expected one of "max, exclude, conflicts"'),
    array('package' => 'PEAR_PackageFile_v2', 'message' => 'Subpackage dependency "blh3" cannot use <conflicts/>, only package dependencies can use this tag'),
    array('package' => 'PEAR_PackageFile_v2', 'message' => 'Invalid tag order in <dependencies><required><subpackage><name>blh2</name>, found <min> expected one of "providesextension"'),
    array('package' => 'PEAR_PackageFile_v2', 'message' => '<dependencies><required><subpackage><name>blh2</name>: dependencies with a <uri> tag cannot have any versioning information'),

// extension

    array('package' => 'PEAR_PackageFile_v2', 'message' => 'Invalid tag order in <dependencies><required><extension><name>blh</name>, found <moo> expected one of "exclude"'),
    array('package' => 'PEAR_PackageFile_v2', 'message' => 'Invalid tag order in <dependencies><required><extension><name>blh2</name>, found <recommended> expected one of "max, exclude, conflicts"'),

// php

    array('package' => 'PEAR_PackageFile_v2', 'message' => 'Invalid tag order in <dependencies><required><php>, found <moo> expected one of "exclude"'),

// pearinstaller

    array('package' => 'PEAR_PackageFile_v2', 'message' => 'Invalid tag order in <dependencies><required><pearinstaller>, found <moo> expected one of "exclude"'),

// os

    array('package' => 'PEAR_PackageFile_v2', 'message' => 'Invalid tag order in <dependencies><required><os>, found <moo> expected one of "conflicts"'),

// arch

    array('package' => 'PEAR_PackageFile_v2', 'message' => 'Invalid tag order in <dependencies><required><arch>, found <moo> expected one of "conflicts"'),

// optional
// package

    array('package' => 'PEAR_PackageFile_v2', 'message' => 'Invalid tag order in <dependencies><optional><package><name>blh</name>, found <moo> expected one of "exclude, nodefault, providesextension"'),
    array('package' => 'PEAR_PackageFile_v2', 'message' => 'Invalid tag order in <dependencies><optional><package><name>blh2</name>, found <min> expected one of "providesextension"'),
    array('package' => 'PEAR_PackageFile_v2', 'message' => '<dependencies><optional><package><name>blh2</name>: dependencies with a <uri> tag cannot have any versioning information'),

// subpackage

    array('package' => 'PEAR_PackageFile_v2', 'message' => 'Invalid tag order in <dependencies><optional><subpackage><name>blh</name>, found <moo> expected one of "exclude, nodefault, providesextension"'),
    array('package' => 'PEAR_PackageFile_v2', 'message' => 'Invalid tag order in <dependencies><optional><subpackage><name>blh3</name>, found <recommended> expected one of "max, exclude, conflicts"'),
    array('package' => 'PEAR_PackageFile_v2', 'message' => 'Subpackage dependency "blh3" cannot use <conflicts/>, only package dependencies can use this tag'),
    array('package' => 'PEAR_PackageFile_v2', 'message' => 'Invalid tag order in <dependencies><optional><subpackage><name>blh2</name>, found <min> expected one of "providesextension"'),
    array('package' => 'PEAR_PackageFile_v2', 'message' => '<dependencies><optional><subpackage><name>blh2</name>: dependencies with a <uri> tag cannot have any versioning information'),

// extension

    array('package' => 'PEAR_PackageFile_v2', 'message' => 'Invalid tag order in <dependencies><optional><extension><name>blh</name>, found <moo> expected one of "exclude"'),
    array('package' => 'PEAR_PackageFile_v2', 'message' => 'Invalid tag order in <dependencies><optional><extension><name>blh2</name>, found <recommended> expected one of "max, exclude, conflicts"'),

// group
// package

    array('package' => 'PEAR_PackageFile_v2', 'message' => 'Invalid tag order in <dependencies><group name="remoteinstall"><package><name>blh</name>, found <moo> expected one of "exclude, nodefault, providesextension"'),
    array('package' => 'PEAR_PackageFile_v2', 'message' => 'Invalid tag order in <dependencies><group name="remoteinstall"><package><name>blh2</name>, found <min> expected one of "providesextension"'),
    array('package' => 'PEAR_PackageFile_v2', 'message' => '<dependencies><group name="remoteinstall"><package><name>blh2</name>: dependencies with a <uri> tag cannot have any versioning information'),

// subpackage

    array('package' => 'PEAR_PackageFile_v2', 'message' => 'Invalid tag order in <dependencies><group name="remoteinstall"><subpackage><name>blh</name>, found <moo> expected one of "exclude, nodefault, providesextension"'),
    array('package' => 'PEAR_PackageFile_v2', 'message' => 'Invalid tag order in <dependencies><group name="remoteinstall"><subpackage><name>blh3</name>, found <recommended> expected one of "max, exclude, conflicts"'),
    array('package' => 'PEAR_PackageFile_v2', 'message' => 'Subpackage dependency "blh3" cannot use <conflicts/>, only package dependencies can use this tag'),
    array('package' => 'PEAR_PackageFile_v2', 'message' => 'Invalid tag order in <dependencies><group name="remoteinstall"><subpackage><name>blh2</name>, found <min> expected one of "providesextension"'),
    array('package' => 'PEAR_PackageFile_v2', 'message' => '<dependencies><group name="remoteinstall"><subpackage><name>blh2</name>: dependencies with a <uri> tag cannot have any versioning information'),

// extension

    array('package' => 'PEAR_PackageFile_v2', 'message' => 'Invalid tag order in <dependencies><group name="remoteinstall"><extension><name>blh</name>, found <moo> expected one of "exclude"'),
    array('package' => 'PEAR_PackageFile_v2', 'message' => 'Invalid tag order in <dependencies><group name="remoteinstall"><extension><name>blh2</name>, found <recommended> expected one of "max, exclude, conflicts"'),


), '1');

echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
