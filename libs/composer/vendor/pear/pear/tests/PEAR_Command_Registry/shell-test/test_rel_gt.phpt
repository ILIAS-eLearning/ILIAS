--TEST--
shell-test command, rel gt
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php

$ds = DIRECTORY_SEPARATOR;
require_once dirname(dirname(__FILE__)) . $ds . 'setup.php.inc';
$reg = $config->getRegistry();
$pkg = new PEAR_PackageFile($config);
$file = dirname(__FILE__) . $ds . $ds. 'packagefiles' . $ds . 'package2.xml';
$info = $pkg->fromPackageFile($file, PEAR_VALIDATE_NORMAL);
$reg->addPackage2($info);
$e = $command->run('shell-test', array(), array('PEAR', 'gt', '1.3.9'));
$phpunit->assertNoErrors('ok');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(dirname(__FILE__)) . '/teardown.php.inc';
?>
--RETURNS--
0
--EXPECT--
tests done
