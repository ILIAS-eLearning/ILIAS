--TEST--
System commands tests
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip ';
}
?>
--FILE--
<?php

require_once 'System.php';

/*******************
        which
********************/
echo "Testing: which\n";

if (OS_UNIX) {
    $ls = trim(`which ls`);
    if (System::which('ls') != $ls) {
        print "System::which('ls') 1 failed\n";
        var_dump($ls, System::which('ls'));
    }
    if (System::which($ls) != $ls) {
        print "System::which('$ls') 2 failed\n";
        var_dump($ls, System::which('ls'));
    }
} elseif (OS_WINDOWS) {
    $sysroot = getenv('SystemRoot') . '\\system32\\';
    if (strcasecmp(System::which('cmd'), $sysroot . 'cmd.exe') != 0) {
        print "System::which('cmd') failed\n";
    }
    if (strcasecmp(System::which('cmd.exe'), $sysroot . 'cmd.exe') != 0) {
        print "System::which('cmd.exe') failed\n";
    }
    if (strcasecmp(System::which($sysroot . 'cmd.exe'),
            $sysroot . 'cmd.exe') != 0) {
        print 'System::which(' . $sysroot . "cmd.exe') failed\n";
    }
    if (strcasecmp(System::which($sysroot . 'cmd'),
            $sysroot . 'cmd.exe') != 0) {
        print 'System::which(' . $sysroot . "cmd') failed\n";
    }
}

if (System::which('i_am_not_a_command')) {
    print "System::which('i_am_not_a_command') did not failed\n";
}
// Missing tests for safe mode constraint...

print "end\n";
?>
--EXPECT--
Testing: which
end
