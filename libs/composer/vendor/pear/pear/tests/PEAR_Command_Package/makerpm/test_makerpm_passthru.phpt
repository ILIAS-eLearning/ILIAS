--TEST--
makerpm with fall-through to PEAR_Command_Packaging
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
if (!@include_once 'PEAR/Command/Packaging.php') {
    echo 'skip: requires PEAR_Command_Packaging package';
}
?>
--FILE--
<?php

require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'setup.php.inc';

// This test requires PEAR_Command_Packaging to be installed to work

copy(dirname(__FILE__) . '/packagefiles/Net_SMTP-1.2.8.tgz', $temp_path . DIRECTORY_SEPARATOR . 'Net_SMTP-1.2.8.tgz');
chdir($temp_path);

$ret = $command->run('makerpm', array(), array('Net_SMTP-1.2.8.tgz'));

$phpunit->assertNoErrors('ret OK');
$phpunit->showall();
$phpunit->assertEquals(array(
    'info' => 'PEAR_Command_Packaging is installed; using newer "make-rpm-spec" command instead',
    'cmd' => 'no command'
), array_shift($fakelog->getLog()),'descriptive output about passthru');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(dirname(__FILE__)) . '/teardown.php.inc';
?>
--EXPECT--
tests done
