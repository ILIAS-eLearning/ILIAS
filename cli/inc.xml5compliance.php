<?php
/*
    +-----------------------------------------------------------------------------+
    | Copyright (c) by Alexandre Alapetite,                                       |
    | http://alexandre.alapetite.net/cv/alexandre-alapetite.en.html               |
    | http://alexandre.alapetite.net/doc-alex/domxml-php4-php5/                   |
    | Modifications by Alex Killing, alex.killing@gmx.de  (search for ##)         |
    |-----------------------------------------------------------------------------|
    | Allows PHP4/DOMXML scripts to run on PHP5/DOM                               |
    |                                                                             |
    | Typical use:                                                                |
    | {                                                                           |
    | 	if (version_compare(PHP_VERSION,'5','>='))                                |
    | 		require_once('domxml-php4-to-php5.php');                              |
    | }                                                                           |
    |-----------------------------------------------------------------------------|
    | This code is published under Creative Commons                               |
    | Attribution-ShareAlike 2.0 "BY-SA" licence.                                 |
    | See http://creativecommons.org/licenses/by-sa/2.0/ for details.             |
    +-----------------------------------------------------------------------------+
*/

function staticxmlerror(int $errno, string $errstr, ?string $errfile = null, ?int $errline = null, ?array $errcontext = null, bool $ret = false)
{
    static $errs = array();

    $tag = 'DOMDocument::validate(): ';
    $errs[] = str_replace($tag, '', $errstr);

    if ($ret === true) {
        return $errs;
    }
}

define('DOMXML_LOAD_PARSING', 0);

/*
* ##added
*/
function domxml_open_mem($str, $mode = 0, &$error = null)
{
    if (!is_int($mode)) {
        $mode = 0;
    }
    $doc = new php4DOMDocument($str, false, $mode);
    if (!$doc->success) {
        $error = $doc->error;
    }

    return $doc;
}

class php4DOMDocument
{
    public $success = null;
    public string $error = "";
    public DOMDocument $myDOMDocument;

    // ##altered
    public function __construct($source, $file = true, $a_mode = 0)
    {
        $this->myDOMDocument = new DOMDocument();
        // temporary set error handler
        set_error_handler('staticxmlerror');
        $old = ini_set('html_errors', false);

        if (is_object($source)) {
            $this->myDOMDocument = $source;
            $this->success = true;
        } else {
            if ($file) {
                $this->success = @$this->myDOMDocument->load($source, $a_mode);
            } else {
                $this->success = $this->myDOMDocument->loadXML($source, $a_mode);
            }
        }

        // Restore error handling
        ini_set('html_errors', $old);
        restore_error_handler();

        if (!$this->success) {
            $this->error_arr = staticxmlerror(0, "", "", 0, null, true);
            foreach ($this->error_arr as $error) {
                $error = str_replace("DOMDocument::loadXML():", "", $error);
                $this->error .= $error . "<br />";
            }
        }
    }
}
