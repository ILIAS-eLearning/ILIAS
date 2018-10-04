--TEST--
System commands tests
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip ';
}
if (!($fp = @fsockopen('pear.php.net', 80))) {
    echo 'skip internet is down';
}
@fclose($fp);
?>
--FILE--
<?php

require_once 'System.php';

 /*******************
         cat
 ********************/
echo "Testing: cat online\n";
$catfile = System::mktemp('tst');

// Concat from url wrapper
$cat = 'http://www.php.net/ http://pear.php.net/ > ' . $catfile;
if (!System::cat($cat)) {
    print "System::cat('$cat') failed\n";
}

// Clean up
unlink($catfile);

print "end\n";
?>
--EXPECT--
Testing: cat online
end
