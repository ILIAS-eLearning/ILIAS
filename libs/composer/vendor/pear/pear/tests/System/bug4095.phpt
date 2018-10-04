--TEST--
System test Bug #4095: System::rm does not handle links correctly
--SKIPIF--
<?php
if (strtolower(substr(PHP_OS, 0, 3)) == 'win') {
    echo 'skip can only run test on non-windows machines';
}
?>
--FILE--
<?php
`mkdir /tmp/system_link_test`;
`touch /tmp/system_link_test/0`;
`touch /tmp/system_link_test/1`;
`mkdir /tmp/system_link_test/sub1`;
`mkdir /tmp/system_link_test/sub1/sub2`;
`ln -s /tmp/system_link_test/sub1 /tmp/system_link_test/link2`;

require_once 'System.php';
System::rm(array('-r','/tmp/system_link_test'));
if (file_exists('/tmp/system_link_test')) {
	echo "TEST FAILED";
	exit;
}
echo "TEST SUCCEEDED";
?>
--EXPECT--
TEST SUCCEEDED
