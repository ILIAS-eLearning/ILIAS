<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2008 ILIAS open source, University of Cologne            |
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
* checks if PEAR is installed and includes the auth module 
*
* @author Sascha Hofmann <shofmann@databay.de>
* @version $Id$
*
* @package ilias-core
*/
@include_once("PEAR.php");
@include_once("Auth/Auth.php");
@include_once("MDB2.php");
@include_once("MDB2/Driver/mysql.php");

// wrapper for php 4.3.2 & higher
@include_once "HTML/ITX.php";
$tpl_class_name = "IntegratedTemplate";
$html_templ_it = true;
if (!class_exists("IntegratedTemplateExtension"))
{
	@include_once "HTML/Template/ITX.php";
	$tpl_class_name = "HTML_Template_ITX";
	$html_templ_it = class_exists("HTML_Template_ITX");
}

$include_paths = ini_get("include_path");

// unix & windows use different characters to separate paths
$separator = ";";

if (!strstr(php_uname(), "Windows"))
{
	$separator = ":";
}

$include_paths = explode($separator,$include_paths);

$pear = class_exists("PEAR");
$auth = class_exists("Auth");
$mdb2 = class_exists("MDB2");
$mdb2_mysql = class_exists("MDB2_Driver_mysql");

if (!$pear || !$auth || !$html_templ_it || !$mdb2 || !$mdb2_mysql)
{
	$logo = (is_file("../templates/default/images/HeaderIcon.png"))
		? "../templates/default/images/HeaderIcon.png"
		: "./templates/default/images/HeaderIcon.png";
?>
<div style="border-color:#9EADBA; border-style:solid; border-width:1px; padding: 10px; margin: 150px 25%; font-family:Verdana,Arial,Helvetica,sans-serif; font-size:0.9em;">
<img src=<?php echo '"'.$logo.'"'; ?> border="0" /><br /><br />
<span style="font-size:120%;">Welcome to ILIAS.</span><br/><br/>
To run ILIAS 3 you will need the following missing PEAR components:
<ul>
<?php
	if (!$pear) echo "<li>PEAR</li>";
	if (!$auth) echo "<li>Auth</li>";
	if (!$html_templ_it) echo "<li>HTML_Template_IT</li>";
	if (!$mdb2) echo "<li>MDB2</li>";
	if (!$mdb2_mysql) echo "<li>MDB2#mysql</li>";
?>
</ul>
You can find help on how to install the missing components
at the
<a href="http://www.ilias.de/docu/goto.php?target=lm_367&client_id=docu" target="_blank">
ILIAS website</a>. General help on PEAR is available at
<a href="http://pear.php.net" target="_blank">http://pear.php.net</a>.	
</div>

<?php
	exit();
}

if (!$auth)
{
	$msg = "<p><b>Error: Couldn't find module Auth in your PEAR API!</b><br/>".
		   "ILIAS 3 requires this module for authentification. ".
		   "Please read the manual how to install the auth module before using ILIAS 3.</p>".
		   "<p>More information and a documetation about the PEAR API can be found at ".
		   "<a href=\"http://pear.php.net\" target=\"_blank\">http://pear.php.net</a></p>";	
	echo $msg;
	exit();
}
?>