--TEST--
PEAR_PackageFile_Generator_v1->toXml() failure
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php

require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'setup.php.inc';
$pf = new PEAR_PackageFile_v1;
$pf->setConfig($config);

$generator = &$pf->getDefaultGenerator();
$e = $generator->toXml();
$phpunit->assertErrors(array(
    array('package' => 'PEAR_PackageFile_v1', 'message' => 'Missing Package Name'),
    array('package' => 'PEAR_PackageFile_v1', 'message' => 'No summary found'),
    array('package' => 'PEAR_PackageFile_v1', 'message' => 'Missing description'),
    array('package' => 'PEAR_PackageFile_v1', 'message' => 'Missing license'),
    array('package' => 'PEAR_PackageFile_v1', 'message' => 'No release version found'),
    array('package' => 'PEAR_PackageFile_v1', 'message' => 'No release state found'),
    array('package' => 'PEAR_PackageFile_v1', 'message' => 'No maintainers found, at least one must be defined'),
    array('package' => 'PEAR_PackageFile_v1', 'message' => 'No files in <filelist> section of package.xml'),
    array('package' => 'PEAR_PackageFile_v1', 'message' => 'No release notes found'),
), 'bad');
$phpunit->assertFalse($e, 'false');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(dirname(__FILE__)) . '/teardown.php.inc';
?>
--EXPECT--
tests done
