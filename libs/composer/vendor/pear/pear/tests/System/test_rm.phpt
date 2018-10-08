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

// setup

// Single directory creation
System::mkDir('rm_singledir');
if( !is_dir('rm_singledir') ){
    print "System::mkDir('rm_singledir'); failed\n";
}
System::rm('rm_singledir');

// Multiple directory creation
System::mkDir('rm_dir1 rm_dir2 rm_dir3');
if (!@is_dir('rm_dir1') || !@is_dir('rm_dir2') || !@is_dir('rm_dir3')) {
    print "System::mkDir('rm_dir1 rm_dir2 rm_dir3'); failed\n";
}

// Parent creation without "-p" fail
if (@System::mkDir("rm_dir4{$sep}rm_dir3")) {
    print "System::mkDir(\"rm_dir4{$sep}rm_dir3\") did not failed\n";
}

// Create a directory which is a file already fail
touch('rm_file4');
$res = @System::mkDir('rm_file4 rm_dir5');
if ($res) {
    print "System::mkDir('rm_file4 rm_dir5') did not failed\n";
}
if (!@is_dir('rm_dir5')) {
    print "System::mkDir('rm_file4 rm_dir5') failed\n";
}

// Parent directory creation
System::mkDir("-p rm_dir2{$sep}rm_dir21 rm_dir6{$sep}rm_dir61{$sep}rm_dir611");
if (!@is_dir("rm_dir2{$sep}rm_dir21") || !@is_dir("rm_dir6{$sep}rm_dir61{$sep}rm_dir611")) {
    print "System::mkDir(\"-p rm_dir2{$sep}rm_dir21 rm_dir6{$sep}rm_dir61{$sep}rm_dir611\")); failed\n";
}

/*******************
        rm
********************/
echo "Testing: rm\n";

if (OS_WINDOWS) {
    mkdir('rm_dir1\\oops');
} else {
    mkdir('rm_dir1/oops');
}

// Try to delete a dir without "-r" option
if (@System::rm('rm_dir1')) {
    print "System::rm('rm_dir1') did not fail\n";
}

// Multiple and recursive delete
$del = "rm_dir1 rm_dir2 rm_dir3 rm_file4 rm_dir5 rm_dir6";
if (!@System::rm("-r $del")) {
    print "System::rm(\"-r $del\") failed\n";
}

print "end\n";
?>
--EXPECT--
Testing: rm
end
