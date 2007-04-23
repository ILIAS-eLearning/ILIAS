<?php
/**
 * ILIAS Open Source
 * --------------------------------
 * Implementation of ADL SCORM 2004
 * 
 * This program is free software. The use and distribution terms for this software
 * are covered by the GNU General Public License Version 2
 * 	<http://opensource.org/licenses/gpl-license.php>.
 * By using this software in any fashion, you are agreeing to be bound by the terms 
 * of this license.
 * 
 * You must not remove this notice, or any other, from this software.
 *  
 * PRELIMINARY EDITION 
 * This is work in progress and therefore incomplete and buggy ... 
 *  
 * Content-Type: application/x-httpd-php; charset=ISO-8859-1
 * 
 * @author Alfred Kohnert <alfred.kohnert@bigfoot.com>
 * @version $Id$
 * @copyright: (c) 2007 Alfred Kohnert
 *  
 */ 
 
 /**
 * 
 * PURPOSE
 * read all JS files in current directory and return them
 * in js minified and gz compressed form. 
 * Enclose code within a js closure, so that only methods and objects
 * made public via window[publicname] = this.privatename
 * will be visible from other js code in browser window. 
 * 
 * To enforce minimization you may call this script with url param "minify=1".
 *  
 **/
 

	// enable/disabled minimization
define('IL_OP_JSMIN', false); 

 
require_once('../classes/JSMin_lib.php');

function minifyJS($files, $comment = null, $closure=false)
{
	$out = array();
	if (is_string($files)) 
	{
		$files = glob($files);
	}
	elseif (!is_array($files))
	{
		return '';
	}
	foreach ($files as $file) 
	{
		$inp = file_get_contents($file);
		$jsMin = new JSMin($inp, false);
		$jsMin->minify();
		$out[] = $jsMin->out;
	}
	$out = implode("", $out);
	if ($closure) 
	{
		$out = "(new function () {\n" . $out . "\n});";
	}
	if ($comment) 
	{
		if (!is_array($comment)) $comment = array($comment); 
		$out = "/*\n\t" . implode("\n\t", $comment) . "\n*/\n" . $out;
	}
	return $out;
}

$filespec = '*.js';

header('Content-Type: text/javascript');

if (IL_OP_JSMIN || intval($_GET['minify'])) 
{
	$port = $_SERVER['SERVER_PORT'];
	$ssl = !empty($_SERVER['SSL']);
	$selfuri = 'http' . ($ssl ? 's' : '') . '://' . $_SERVER['SERVER_NAME'] . 
		(($ssl && $port===443 || !$ssl && $port===80) ? '' : ':' . $port) . $_SERVER['REQUEST_URI'];
	$js = minifyJS($filespec, array('Compressed and minified script', $selfuri, date('c')), true);
	//file_put_contents('jsmin.gz', gzcompress($js));
	// you add expiry by some header here
	ob_start("ob_gzhandler");
	die($js);
}
else
{
	$ilias=$_GET['ilias'];
	$tpath="scripts/";
	if ($ilias==1) {
		$tpath="./Modules/Scorm2004/scripts/";
	}
	foreach (glob($filespec) as $file) 
	{
		print("\ndocument.writeln('<script src=\"$tpath$file\"></script>');");
	}	
}

?>
