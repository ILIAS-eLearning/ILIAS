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
* registration form for new users
*
* @author Sascha Hofmann <shofmann@databay.de>
* @version $Id$
*
* @package ilias-core
*/

require_once "include/inc.check_pear.php";
require_once "include/inc.header.php";

$_GET["ref_id"] = 7;
$_GET["new_type"] = "usr";
$id = $_GET["ref_id"];

$cmd = ($_GET["cmd"]) ? $_GET["cmd"] : "create";

//add template for content
$tpl->addBlockFile("CONTENT", "content", "tpl.registration_form.html");

$tpl->setCurrentBlock("content");

$tpl->setVariable("TXT_PAGEHEADLINE", $lng->txt("registration"));
$tpl->setVariable("TXT_WELCOME", $lng->txt("welcome_text"));

$obj = $ilias->obj_factory->getInstanceByRefId($_GET["ref_id"]);

$_GET["type"] = $obj->getType();

$obj_type = $_GET["new_type"];
$class_name = $objDefinition->getClassName($obj_type);
$module = $objDefinition->getModule($obj_type);

$module_dir = ($module == "") ? "" : $module."/";
$class_constr = "ilObj".$class_name."GUI";
include_once("./".$module_dir."classes/class.ilObj".$class_name."GUI.php");
$obj = new $class_constr($data, $id, false, false);

$method = $cmd."Object";

$obj->setReturnLocation("save","index.php");
$obj->setFormAction("save","register.php?cmd=save&ref_id=".$_GET["ref_id"]."&new_type=".$obj_type);
$obj->setTargetFrame("save","bottom");

$obj->$method();

// output
$tpl->show();

/*
//instantiate login template
$tpl->addBlockFile("CONTENT", "content", "tpl.registration_form.html");

$data = array();
$data["fields"] = array();
$data["fields"]["login"] = "";
$data["fields"]["passwd"] = "";
$data["fields"]["passwd2"] = "";
$data["fields"]["title"] = "";
$data["fields"]["gender"] = $gender;
$data["fields"]["firstname"] = "";
$data["fields"]["lastname"] = "";
$data["fields"]["institution"] = "";
$data["fields"]["street"] = "";
$data["fields"]["city"] = "";
$data["fields"]["zipcode"] = "";
$data["fields"]["country"] = "";
$data["fields"]["phone"] = "";
$data["fields"]["email"] = "";
$data["fields"]["hobby"] = "";
$data["fields"]["default_role"] = $role;

foreach ($data["fields"] as $key => $val)
{
	$tpl->setVariable("TXT_".strtoupper($key), $lng->txt($key));
	$tpl->setVariable(strtoupper($key), $val);
}

$tpl->setVariable("FORMACTION", "adm_object.php?cmd=save"."&ref_id=".$_GET["ref_id"]."&new_type=".$new_type);
$tpl->setVariable("TXT_SAVE", $lng->txt("save"));
$tpl->setVariable("TXT_REQUIRED_FIELDS", $lng->txt("required_field"));
$tpl->setVariable("TXT_LOGIN_DATA", $lng->txt("login_data"));
$tpl->setVariable("TXT_PERSONAL_DATA", $lng->txt("personal_data"));
$tpl->setVariable("TXT_CONTACT_DATA", $lng->txt("contact_data"));
$tpl->setVariable("TXT_SETTINGS", $lng->txt("settings"));
$tpl->setVariable("TXT_PASSWD2", $lng->txt("retype_password"));

// FILL SAVED VALUES IN CASE OF ERROR
$tpl->setVariable("LOGIN",$_SESSION["error_post_vars"]["Fobject"]["login"]);
$tpl->setVariable("FIRSTNAME",$_SESSION["error_post_vars"]["Fobject"]["firstname"]);
$tpl->setVariable("LASTNAME",$_SESSION["error_post_vars"]["Fobject"]["lastname"]);
$tpl->setVariable("TITLE",$_SESSION["error_post_vars"]["Fobject"]["title"]);
$tpl->setVariable("INSTITUTION",$_SESSION["error_post_vars"]["Fobject"]["institution"]);
$tpl->setVariable("STREET",$_SESSION["error_post_vars"]["Fobject"]["street"]);
$tpl->setVariable("CITY",$_SESSION["error_post_vars"]["Fobject"]["city"]);
$tpl->setVariable("ZIPCODE",$_SESSION["error_post_vars"]["Fobject"]["zipcode"]);
$tpl->setVariable("COUNTRY",$_SESSION["error_post_vars"]["Fobject"]["country"]);
$tpl->setVariable("PHONE",$_SESSION["error_post_vars"]["Fobject"]["phone"]);
$tpl->setVariable("EMAIL",$_SESSION["error_post_vars"]["Fobject"]["email"]);
$tpl->setVariable("HOBBY",$_SESSION["error_post_vars"]["Fobject"]["hobby"]);

$tpl->show();
*/
?>