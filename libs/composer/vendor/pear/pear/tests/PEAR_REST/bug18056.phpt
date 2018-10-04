--TEST--
Bug #18056: prevent symlink attacks when writing cache files
--SKIPIF--
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
if (strtolower(substr(PHP_OS, 0, 3)) == 'win'
    && 0 > version_compare(PHP_VERSION, '5.3.0')
) {
    echo 'skip symlink() function only works in PHP 5.3+ under Windows';
}
--FILE--
<?php
require_once dirname(dirname(__FILE__)) . '/phpt_test.php.inc';
require_once 'PEAR/REST.php';

PEAR::staticPushErrorHandling(PEAR_ERROR_PRINT);

$rest     = new PEAR_REST($config);
$temp_dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'pear-rest';
mkdir($temp_dir);

$file     = $temp_dir . DIRECTORY_SEPARATOR . 'foo';
$symlink  = $temp_dir . DIRECTORY_SEPARATOR . 'bar';

$rest->saveCacheFile($file, 'Initial contents');
echo file_get_contents($file) . "\n";

$rest->saveCacheFile($file, 'Updated contents');
echo file_get_contents($file) . "\n";

symlink($file, $symlink);

PEAR::staticPopErrorHandling();

if (PEAR::isError($e = $rest->saveCacheFile($symlink, 'Updated through symlink'))
    && preg_match('/^SECURITY ERROR/', $e->getMessage())
) {
    echo "SECURITY ERROR returned\n";
} else {
    echo "Did not get expected SECURITY ERROR\n";
}

echo file_get_contents($file) . "\n";

unlink($symlink);
unlink($file);
rmdir($temp_dir);


?>
--EXPECT--
Initial contents
Updated contents
SECURITY ERROR returned
Updated contents
