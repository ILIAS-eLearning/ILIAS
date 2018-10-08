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

$sep = DIRECTORY_SEPARATOR;

/*******************
        mktemp
********************/
echo "Testing: mktemp\n";

// Create a temporal file with "tst" as filename prefix
$tmpfile = System::mktemp('tst');
$tmpenv  = rtrim(realpath(System::tmpDir()), '\/');
if (!@is_file($tmpfile) || (0 !== strpos($tmpfile, "{$tmpenv}{$sep}tst"))) {
    print "System::mktemp('tst') failed\n";
    var_dump(is_file($tmpfile), $tmpfile, "{$tmpenv}{$sep}tst", (0 !== strpos($tmpfile, "{$tmpenv}{$sep}tst")));
}

// Create a temporal dir in "mktemp_dir1" with default prefix "tmp"
$tmpdir = System::mktemp('-d -t mktemp_dir1');
if (!@is_dir($tmpdir) || (false === strpos($tmpdir, "mktemp_dir1{$sep}tmp"))) {
    print "System::mktemp('-d -t mktemp_dir1') failed\n";
    var_dump(is_dir($tmpdir), $tmpdir, "mktemp_dir1{$sep}tmp", (false === strpos($tmpdir, "mktemp_dir1{$sep}tmp")));
}

System::rm('-r mktemp_dir1');

print "end\n";
?>
--EXPECT--
Testing: mktemp
end
