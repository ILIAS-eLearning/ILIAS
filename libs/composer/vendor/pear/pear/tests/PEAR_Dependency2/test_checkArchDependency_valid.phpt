--TEST--
PEAR_Dependency2->checkArchDependency() valid
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'setup.php.inc';
$dep = new test_PEAR_Dependency2($config, array(), array('channel' => 'pear.php.net',
    'package' => 'mine'), PEAR_VALIDATE_INSTALLING);
$phpunit->assertNoErrors('create 1');

$dep->setArch('FreeBSD host.example.com 4.5-STABLE FreeBSD 4.5-STABLE #0: Wed Feb  6 23:59:23 CET 2002     root@example.com:/usr/src/sys/compile/CONFIG  i386', 'glibc1.2');
$result = $dep->validateArchDependency(array('pattern' => 'freebsd-*-i386'));
$phpunit->assertNoErrors('foo');
$phpunit->assertTrue($result, 'foo');

$dep = new test_PEAR_Dependency2($config, array('nodeps' => true), array('channel' => 'pear.php.net',
    'package' => 'mine'), PEAR_VALIDATE_INSTALLING);
$phpunit->assertNoErrors('create 1');

$dep->setArch('FreeBSD host.example.com 4.5-STABLE FreeBSD 4.5-STABLE #0: Wed Feb  6 23:59:23 CET 2002     root@example.com:/usr/src/sys/compile/CONFIG  i386', 'glibc1.2');
$result = $dep->validateArchDependency(array('pattern' => 'freebsd-*-i386'));
$phpunit->assertNoErrors('foo');
$phpunit->assertTrue($result, 'foo');

$dep = new test_PEAR_Dependency2($config, array('force' => true), array('channel' => 'pear.php.net',
    'package' => 'mine'), PEAR_VALIDATE_INSTALLING);
$phpunit->assertNoErrors('create 1');

$dep->setArch('FreeBSD host.example.com 4.5-STABLE FreeBSD 4.5-STABLE #0: Wed Feb  6 23:59:23 CET 2002     root@example.com:/usr/src/sys/compile/CONFIG  i386', 'glibc1.2');
$result = $dep->validateArchDependency(array('pattern' => 'freebsd-*-i386'));
$phpunit->assertNoErrors('foo');
$phpunit->assertTrue($result, 'foo');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
