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

require_once("./classes/class.ilObjSCORMLearningModule.php");
require_once("content/classes/SCORM/class.ilSCORMObjectGUI.php");
//require_once("./classes/class.ilMainMenuGUI.php");
//require_once("./classes/class.ilObjStyleSheet.php");

/**
* Class ilSCORMPresentationGUI
*
* GUI class for scorm learning module presentation
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @package content
*/
class ilSCORMPresentationGUI
{
	var $ilias;
	var $slm;
	var $tpl;
	var $lng;

	function ilSCORMPresentationGUI()
	{
		global $ilias, $tpl, $lng;

		$this->ilias =& $ilias;
		$this->tpl =& $tpl;
		$this->lng =& $lng;

		$cmd = (!empty($_GET["cmd"])) ? $_GET["cmd"] : "frameset";

		// Todo: check lm id
		$this->slm =& new ilObjSCORMLearningModule($_GET["ref_id"], true);

		$this->$cmd();
	}

	function attrib2arr(&$a_attributes)
	{
		$attr = array();
		if(!is_array($a_attributes))
		{
			return $attr;
		}
		foreach ($a_attributes as $attribute)
		{
			$attr[$attribute->name()] = $attribute->value();
		}
		return $attr;
	}


	/**
	* output main menu
	*/
	function frameset()
	{
		//$this->tpl = new ilTemplate("tpl.scorm_pres_frameset.html", false, false, "content");
		$this->tpl = new ilTemplate("tpl.scorm_pres_frameset2.html", false, false, "content");
		$this->tpl->setVariable("REF_ID",$this->slm->getRefId());
		$this->tpl->show();
	}

	/**
	* output table of content
	*/
	function explorer($a_target = "scorm_content")
	{
		$this->tpl = new ilTemplate("tpl.scorm_exp_main.html", true, true, true);
		$this->tpl->setVariable("LOCATION_JAVASCRIPT", "./scorm_functions.js");
		require_once("./content/classes/SCORM/class.ilSCORMExplorer.php");
		$exp = new ilSCORMExplorer("scorm_presentation.php?cmd=view&ref_id=".$this->slm->getRefId(), $this->slm);
		$exp->setTargetGet("obj_id");
		$exp->setFrameTarget($a_target);
		//$exp->setFiltered(true);

		if ($_GET["mexpand"] == "")
		{
			$mtree = new ilSCORMTree($this->slm->getId());
			$expanded = $mtree->readRootId();
		}
		else
		{
			$expanded = $_GET["mexpand"];
		}
		$exp->setExpand($expanded);

		// build html-output
		//666$exp->setOutput(0);
		$exp->setOutput(0);

		$output = $exp->getOutput();

		$this->tpl->setVariable("LOCATION_STYLESHEET", ilUtil::getStyleSheetLocation());

		$this->tpl->addBlockFile("CONTENT", "content", "tpl.explorer.html");
		$this->tpl->setVariable("TXT_EXPLORER_HEADER", $this->lng->txt("cont_content"));
		$this->tpl->setVariable("EXPLORER",$output);
		$this->tpl->setVariable("ACTION", "scorm_presentation.php?cmd=".$_GET["cmd"]."&frame=".$_GET["frame"].
			"&ref_id=".$this->slm->getRefId()."&mexpand=".$_GET["mexpand"]);
		$this->tpl->parseCurrentBlock();
		$this->tpl->show();
	}

	function view()
	{
		$sc_gui_object =& ilSCORMObjectGUI::getInstance($_GET["obj_id"]);

		if(is_object($sc_gui_object))
		{
			$sc_gui_object->view();
		}

		$this->tpl->setVariable("LOCATION_STYLESHEET", ilUtil::getStyleSheetLocation());
		$this->tpl->show();
	}

	function api()
	{
		// should be an item
		$sc_gui_object =& ilSCORMObjectGUI::getInstance($_GET["obj_id"]);

		if(is_object($sc_gui_object))
		{
			$sc_gui_object->api();
		}

	}

	function api2()
	{
		global $ilias;

		$slm_obj =& new ilObjSCORMLearningModule($_GET["ref_id"]);

		$this->tpl = new ilTemplate("tpl.scorm_api2.html", true, true, true);
		//$func_tpl->setVariable("PREFIX", $slm_obj->getAPIFunctionsPrefix());
		//$this->tpl =& new ilTemplate("tpl.scorm_api.html", true, true, true);
		//$this->tpl->setVariable("SCORM_FUNCTIONS", $func_tpl->get());
		//$this->tpl->setVariable("ITEM_ID", $_GET["obj_id"]);
		$this->tpl->setVariable("USER_ID",$ilias->account->getId());
		$this->tpl->setVariable("SESSION_ID",session_id());

		$this->tpl->setVariable("CODE_BASE", "http://".$_SERVER['SERVER_NAME'].substr($_SERVER['PHP_SELF'], 0, strpos ($_SERVER['PHP_SELF'], "/scorm_presentation.php")));
		$this->tpl->parseCurrentBlock();

		$this->tpl->show(false);
		exit;
	}

}
?>
