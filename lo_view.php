<?php
/**
* lo view. Displays LearningObject (db->dom->xsl->ITx)
*
* @author Sascha Hofmann <shofmann@databay.de>
* @version $Id$
*
* @package ilias-core
*/

require_once "include/inc.header.php";
require_once "classes/class.Object.php";
require_once "classes/class.LearningObjectObject.php";
require_once "classes/class.sql2xml.php";
require_once "classes/class.domxml.php";

ob_start(); 

//$T1 = TUtil::StopWatch();

$sql2xml = new sql2xml($_GET["lm_id"],$_GET["lo_id"]);
$lo = $sql2xml->getLearningObject();
$navbar = $sql2xml->setNavigation();

//echo TUtil::StopWatch($T1)." get_XMLdata total<br/>"; 

//echo "<pre>".$lo."</pre>";
//exit;

//echo "<pre>".htmlentities($lo)."</pre>";


//$T1 = TUtil::StopWatch(); 
// load xsl into string
$path = getcwd();
$xsl = file_get_contents($path."/xml/default.xsl");

$args = array( '/_xml' => $lo, '/_xsl' => $xsl );
$xh = xslt_create();
$output = xslt_process($xh,"arg:/_xml","arg:/_xsl",NULL,$args);
echo xslt_error($xh);
xslt_free($xh);
//echo TUtil::StopWatch($T1)." XSLT_parsing total<br/>"; 

//$T1 = TUtil::StopWatch(); 
$tpl->addBlockFile("CONTENT", "content", "tpl.lo_content.html");
//$tpl->addBlockFile("LM_NAVBAR", "navbar", "tpl.lm_navbar.html");
$tpl->addBlockfile("BUTTONS", "buttons", "tpl.buttons.html");
$tpl->touchBlock("buttons");

// this view should switch between a tree view if the learningmodule's structure and full view.
if (!isset($_SESSION["viewmode"]) or $_SESSION["viewmode"] == "flat")
{
	$tpl->setCurrentBlock("btn_cell");
	$tpl->setVariable("BTN_LINK","lo.php?viewmode=tree");
	$tpl->setVariable("BTN_TXT", $lng->txt("treeview"));
	$tpl->parseCurrentBlock();
}
else
{
	$tpl->setCurrentBlock("btn_cell");
	$tpl->setVariable("BTN_LINK","lo.php?viewmode=flat");
	$tpl->setVariable("BTN_TARGET","target=\"_parent\"");
	$tpl->setVariable("BTN_TXT", $lng->txt("flatview"));
	$tpl->parseCurrentBlock();
}

$tpl->setCurrentBlock("content");
$tpl->setVariable("LM_NAVBAR",$navbar);
$tpl->setVariable("LO_CONTENT",$output);
$tpl->parseCurrentBlock();

$tpl->show();
//echo TUtil::StopWatch($T1)." template_output<br/>"; 

$ret_str = ob_get_contents(); 
ob_end_clean(); 

echo $ret_str;

echo "<p><i>server processing time: ".TUtil::StopWatch($t_pagestart)." seconds</i></p>"; 
?>