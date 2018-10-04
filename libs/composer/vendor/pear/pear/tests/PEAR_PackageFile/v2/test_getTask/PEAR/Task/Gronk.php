<?php
require_once 'PEAR/Task/Common.php';
class PEAR_Task_Gronk extends PEAR_Task_Common
{
    var $type = 'simple';
    var $_replacements;

    /**
     * Validate the raw xml at parsing-time.
     * @param PEAR_PackageFile_v2
     * @param array raw, parsed xml
     * @param PEAR_Config
     */
    public static function validateXml($pkg, $xml, $config, $fileXml)
    {
        if ($xml != array()) {
            return array(PEAR_TASK_ERROR_INVALID);
        }
    }

    /**
     * Initialize a task instance with the parameters
     * @param array raw, parsed xml
     * @param unused
     */
    function init($xml, $attribs, $lastVersion)
    {
    }

    /**
     * Replace all line endings with line endings customized for the current OS
     *
     * See validateXml() source for the complete list of allowed fields
     * @param PEAR_PackageFile_v1|PEAR_PackageFile_v2
     * @param string file contents
     * @param string the eventual final file location (informational only)
     * @return string|false|PEAR_Error false to skip this file, PEAR_Error to fail
     *         (use $this->throwError), otherwise return the new contents
     */
    function startSession($pkg, $contents, $dest)
    {
        $this->installer->log(3, "replacing all line endings with PHP_EOL in $dest");
        if (defined('PHP_EOL')) {
            $eol = PHP_EOL;
        } else {
            if (strtolower(substr(PHP_OS, 0, 3)) == 'win') {
                $eol = "\r\n";
            } elseif (strpos(php_uname(), 'Darwin') !== false) {
                $eol = "\r";
            } else {
                $eol = "\n";
            }
        }
        return preg_replace("/\r\n|\n\r|\r|\n/", $eol, $contents);
    }
}
?>
