<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/


/**
* lo view. Displays LearningObject (db->dom->xsl->ITx)
*
* @author Sascha Hofmann <shofmann@databay.de>
* @version $Id$
*
* @package ilias-core
*/
require_once "include/inc.header.php";
require_once "classes/class.ilSQL2XML.php";
require_once "classes/class.ilDOMXML.php";

ob_start(); 

if (!$rbacsystem->checkAccess("read",$_GET["lm_id"]))
{
	$ilias->raiseError($lng->txt("permission_denied"),$ilias->error_obj->MESSAGE);
}

//$T1 = ilUtil::StopWatch();

$sql2xml = new ilSQL2XML($_GET["lm_id"],$_GET["lo_id"]);
$lo = $sql2xml->getLearningObject();
$navbar = $sql2xml->setNavigation();

//echo ilUtil::StopWatch($T1)." get_XMLdata total<br/>"; 

//echo "<pre>".$lo."</pre>";
//exit;

//echo "<pre>".htmlentities($lo)."</pre>";


//$T1 = ilUtil::StopWatch(); 
// load xsl into string
$path = getcwd();
$xsl = file_get_contents($path."/xml/default.xsl");

$args = array( '/_xml' => $lo, '/_xsl' => $xsl );
$xh = xslt_create();
$output = xslt_process($xh,"arg:/_xml","arg:/_xsl",NULL,$args);
echo xslt_error($xh);
xslt_free($xh);
//echo ilUtil::StopWatch($T1)." XSLT_parsing total<br/>"; 

//$T1 = ilUtil::StopWatch(); 
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
//echo ilUtil::StopWatch($T1)." template_output<br/>"; 

$ret_str = ob_get_contents(); 
ob_end_clean(); 

echo $ret_str;

echo "<p><i>server processing time: ".ilUtil::StopWatch($t_pagestart)." seconds</i></p>"; 
?>
