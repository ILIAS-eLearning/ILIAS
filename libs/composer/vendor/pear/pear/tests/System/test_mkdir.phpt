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
        mkDir
********************/
echo "Testing: MkDir\n";

// Single directory creation
System::mkDir('mkdir_singledir');
if( !is_dir('mkdir_singledir') ){
    print "System::mkDir('mkdir_singledir'); failed\n";
}
System::rm('mkdir_singledir');

// Multiple directory creation
System::mkDir('mkdir_dir1 mkdir_dir2 mkdir_dir3');
if (!@is_dir('mkdir_dir1') || !@is_dir('mkdir_dir2') || !@is_dir('mkdir_dir3')) {
    print "System::mkDir('mkdir_dir1 mkdir_dir2 mkdir_dir3'); failed\n";
}

// Parent creation without "-p" fail
if (@System::mkDir("mkdir_dir4{$sep}mkdir_dir3")) {
    print "System::mkDir(\"mkdir_dir4{$sep}mkdir_dir3\") did not failed\n";
}

// Create a directory which is a file already fail
touch('mkdir_file4');
$res = @System::mkDir('mkdir_file4 mkdir_dir5');
if ($res) {
    print "System::mkDir('mkdir_file4 mkdir_dir5') did not failed\n";
}
if (!@is_dir('mkdir_dir5')) {
    print "System::mkDir('mkdir_file4 mkdir_dir5') failed\n";
}

// Parent directory creation
System::mkDir("-p mkdir_dir2{$sep}mkdir_dir21 mkdir_dir6{$sep}mkdir_dir61{$sep}mkdir_dir611");
if (!@is_dir("mkdir_dir2{$sep}mkdir_dir21") || !@is_dir("mkdir_dir6{$sep}mkdir_dir61{$sep}mkdir_dir611")) {
    print "System::mkDir(\"-p mkdir_dir2{$sep}mkdir_dir21 mkdir_dir6{$sep}mkdir_dir61{$sep}mkdir_dir611\")); failed\n";
}



// Cleanup

if (OS_WINDOWS) {
    mkdir('mkdir_dir1\\oops');
} else {
    mkdir('mkdir_dir1/oops');
}

// Try to delete a dir without "-r" option
if (@System::rm('mkdir_dir1')) {
    print "System::rm('mkdir_dir1') did not fail\n";
}

// Multiple and recursive delete
$del = "mkdir_dir1 mkdir_dir2 mkdir_dir3 mkdir_file4 mkdir_dir5 mkdir_dir6";
if (!@System::rm("-r $del")) {
    print "System::rm(\"-r $del\") failed\n";
}

print "end\n";
?>
--EXPECT--
Testing: MkDir
end
