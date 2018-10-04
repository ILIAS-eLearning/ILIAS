<?php

$server = 'pear.Chiara';
//$server = 'test.pear.php.net';
$temp_path = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'testinstallertemp';
function cleanall($dir = null)
{
    if ($dir !== null) {
        $statedir = $dir;
    } else {
        $statedir = $GLOBALS['statedir'];
    }
    clearstatcache();
    if (file_exists($statedir)) {
        $dp = opendir($statedir);
        while ($ent = readdir($dp)) {
            if (in_array($ent, array('.', '..'))) {
                continue;
            }
            if (is_dir($statedir . DIRECTORY_SEPARATOR . $ent)) {
                cleanall($statedir . DIRECTORY_SEPARATOR . $ent);
                continue;
            }
            unlink($statedir . DIRECTORY_SEPARATOR . $ent);
        }
        closedir($dp);
        rmdir($statedir);
    }
}
cleanall($temp_path);
?>
