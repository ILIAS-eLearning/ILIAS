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
         cat
 ********************/
echo "Testing: cat offline\n";

if (!function_exists('file_put_contents')) {
    function file_put_contents($file, $text) {
        $fd = fopen($file, 'wb');
        fputs($fd, $text);
        fclose($fd);
    }
}
$catdir  = uniqid('foobar');
$catfile = $catdir.$sep.basename(System::mktemp("-t {$catdir} tst"));

// Create temp files
$tmpfile = array();
$totalfiles = 3;
for ($i = 0; $i < $totalfiles + 1; ++$i) {
    $tmpfile[] = $catdir.$sep.basename(System::mktemp("-t {$catdir} tst"));
    file_put_contents($tmpfile[$i], 'FILE ' . $i);
}

// Concat in new file
for ($i = $totalfiles; $i > 0; --$i) {
    $cat = '';
    $expected = '';
    for ($j = $i; $j > 0; --$j) {
        $cat .= $tmpfile[$j] . ' ';
        $expected .= 'FILE ' . $j;
    }
    $cat .= '> ' . $catfile;
    System::cat($cat);
    if (file_get_contents($catfile) != $expected) {
        print "System::cat(> '$cat') failed\n";
    }
}

// Concat append to file
for ($i = $totalfiles; $i > 0; --$i) {
    $cat = '';
    for ($j = $i; $j > 0; --$j) {
        $cat .= $tmpfile[$j] . ' ';
        $expected .= 'FILE ' . $j;
    }
    $cat .= '>> ' . $catfile;
    System::cat($cat);
    if (file_get_contents($catfile) != $expected) {
        print "System::cat(>> '$cat') failed\n";
    }
}

// Concat to string
for ($i = $totalfiles; $i > 0; --$i) {
    $cat = '';
    $expected = '';
    for ($j = $i; $j > 0; --$j) {
        $cat .= $tmpfile[$j] . ' ';
        $expected .= 'FILE ' . $j;
    }
    if (System::cat($cat) != $expected) {
        print "System::cat('$cat') failed\n";
    }
}

// Concat by array to string
for ($i = $totalfiles; $i > 0; --$i) {
    $cat = array();
    $expected = '';
    for ($j = $i; $j > 0; --$j) {
        $cat[] = $tmpfile[$j];
        $expected .= 'FILE ' . $j;
    }
    if (System::cat($cat) != $expected) {
        print "System::cat(Array) failed\n";
    }
}
// Concat by array in new file
for ($i = $totalfiles; $i > 0; --$i) {
    $cat = array();
    $expected = '';
    for ($j = $i; $j > 0; --$j) {
        $cat[] = $tmpfile[$j];
        $expected .= 'FILE ' . $j;
    }
    $cat[] = '>';
    $cat[] = $catfile;
    System::cat($cat);
    if (file_get_contents($catfile) != $expected) {
        print "System::cat(Array > $catfile) failed\n";
    }
}

// Concat by array append to file
for ($i = $totalfiles; $i > 0; --$i) {
    $cat = array();
    for ($j = $i; $j > 0; --$j) {
        $cat[] = $tmpfile[$j];
        $expected .= 'FILE ' . $j;
    }
    $cat[] = '>>';
    $cat[] = $catfile;
    System::cat($cat);
    if (file_get_contents($catfile) != $expected) {
        print "System::cat(Array >> $catfile) failed\n";
    }
}

// Clean up
for ($i = 0; $i < $totalfiles + 1; ++$i) {
    unlink($tmpfile[$i]);
}
unlink($catfile);

// Concat to files with space in names
$catfile1 = $catdir.$sep.basename(System::mktemp("-t {$catdir} tst"));
$catfile  = $catfile1.' space in filename';

// Create temp files with space in names
$tmpfile  = array();
$tmpfile1 = array();
$totalfiles = 3;
for ($i = 0; $i < $totalfiles + 1; ++$i) {
    $tmpfile1[$i] = $catdir.$sep.basename(System::mktemp("-t {$catdir} tst"));
    $tmpfile[$i]  = $tmpfile1[$i].' space in filename';
    file_put_contents($tmpfile[$i], 'FILE ' . $i);
}

// Concat by array in new file with space in names
for ($i = $totalfiles; $i > 0; --$i) {
    $cat = array();
    $expected = '';
    for ($j = $i; $j > 0; --$j) {
        $cat[] = $tmpfile[$j];
        $expected .= 'FILE ' . $j;
    }
    $cat[] = '>';
    $cat[] = $catfile;
    System::cat($cat);
    if (file_get_contents($catfile) != $expected) {
        print "System::cat(Array > $catfile) with space in names failed\n";
    }
}

// Clean up
for ($i = 0; $i < $totalfiles + 1; ++$i) {
    unlink($tmpfile[$i]);
    unlink($tmpfile1[$i]);
}
unlink($catfile);
unlink($catfile1);

rmdir($catdir);

print "end\n";
?>
--EXPECT--
Testing: cat offline
end
