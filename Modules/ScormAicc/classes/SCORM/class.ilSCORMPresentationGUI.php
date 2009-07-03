<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
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

require_once("./Modules/ScormAicc/classes/class.ilObjSCORMLearningModule.php");
require_once("./Modules/ScormAicc/classes/SCORM/class.ilSCORMObjectGUI.php");
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
* @ingroup ModulesScormAicc
*/
class ilSCORMPresentationGUI
{
	var $ilias;
	var $slm;
	var $tpl;
	var $lng;

	function ilSCORMPresentationGUI()
	{
		global $ilias, $tpl, $lng, $ilCtrl;

		$this->ilias =& $ilias;
		$this->tpl =& $tpl;
		$this->lng =& $lng;
		$this->ctrl =& $ilCtrl;

		// Todo: check lm id
		$this->slm =& new ilObjSCORMLearningModule($_GET["ref_id"], true);
	}
	
	/**
	* execute command
	*/
	function &executeCommand()
	{
		global $ilAccess, $ilLog, $ilias, $lng;

		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd("frameset");

		if (!$ilAccess->checkAccess("write", "", $_GET["ref_id"]) &&
			(!$ilAccess->checkAccess("read", "", $_GET["ref_id"]) ||
			!$this->slm->getOnline()))
		{
			$ilias->raiseError($lng->txt("permission_denied"), $ilias->error_obj->WARNING);
		}

		switch($next_class)
		{
			default:
				$this->$cmd();
		}
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
	* Output main frameset. If only one SCO/Asset is given, it is displayed
	* without the table of contents explorer frame on the left.
	*/
	function frameset()
	{
		global $lng;
//echo "h".strtolower(get_class($this->slm))."h";
		include_once("./Modules/ScormAicc/classes/SCORM/class.ilSCORMObject.php");
		$items = ilSCORMObject::_lookupPresentableItems($this->slm->getId());
		
		//check for max_attempts and raise error if max_attempts is exceeded
		if ($this->get_max_attempts()!=0) {
			if ($this->get_actual_attempts() >= $this->get_max_attempts()) {
				header('Content-Type: text/html; charset=utf-8');
				echo($lng->txt("cont_sc_max_attempt_exceed"));
				exit;		
			}
		}
		
		//count attempt
		//Cause there is no way to check if the Java-Applet is sucessfully loaded, an attempt equals opening the SCORM player
		
		$this->increase_attempt();
		$this->save_module_version();


		if (count($items) > 1
			|| strtolower(get_class($this->slm)) == "ilobjaicclearningmodule"
			|| strtolower(get_class($this->slm)) == "ilobjhacplearningmodule")
		{
			$this->ctrl->setParameter($this, "expand", "1");
			$exp_link = $this->ctrl->getLinkTarget($this, "explorer");
			$this->tpl = new ilTemplate("tpl.sahs_pres_frameset.html", false, false, "Modules/ScormAicc");
			$this->tpl->setVariable("EXPLORER_LINK", $exp_link);
			$api_link = $this->ctrl->getLinkTarget($this, "api");
			$this->tpl->setVariable("API_LINK", $api_link);
			$pres_link = $this->ctrl->getLinkTarget($this, "view");
			$this->tpl->setVariable("PRESENTATION_LINK", $pres_link);
			$this->tpl->show("DEFAULT", false);
		}
		else if (count($items) == 1)
		{
			//$this->ctrl->setParameter($this, "expand", "1");
			//$exp_link = $this->ctrl->getLinkTarget($this, "explorer");
			$this->tpl = new ilTemplate("tpl.sahs_pres_frameset_one_page.html",
				false, false, "Modules/ScormAicc");
			//$this->tpl->setVariable("EXPLORER_LINK", $exp_link);
			$this->ctrl->setParameter($this, "autolaunch", $items[0]);
			$api_link = $this->ctrl->getLinkTarget($this, "api");
			$this->tpl->setVariable("API_LINK", $api_link);
			$pres_link = $this->ctrl->getLinkTarget($this, "view");
			$this->tpl->setVariable("PRESENTATION_LINK", $pres_link);
			$this->tpl->show("DEFAULT", false);
		}
		
		exit;
		
	}

	/**
	* Get max. number of attempts allowed for this package
	*/
	function get_max_attempts() {
		
		global $ilDB;
		
		$val_set = $ilDB->queryF('SELECT * FROM sahs_lm WHERE id = %s',
		array('integer'), array($this->slm->getId()));
		$val_rec = $ilDB->fetchAssoc($val_set);
		
		return $val_rec["max_attempt"]; 
	}
	
	/**
	* Get number of actual attempts for the user
	*/
	function get_actual_attempts() {
		global $ilDB, $ilUser;

		$val_set = $ilDB->queryF('
			SELECT * FROM scorm_tracking 
			WHERE user_id =  %s
			AND sco_id = %s
			AND lvalue= %s
			AND obj_id = %s',
			array('integer','integer','text','integer'),
			array($ilUser->getId(),0,'package_attempts',$this->slm->getId())
		);
		$val_rec = $ilDB->fetchAssoc($val_set);	
		
		$val_rec["rvalue"] = str_replace("\r\n", "\n", $val_rec["rvalue"]);
		if ($val_rec["rvalue"] == null) {
			$val_rec["rvalue"]=0;
		}

		return $val_rec["rvalue"];
	}
	
	/**
	* Increases attempts by one for this package
	*/
	function increase_attempt() {
		global $ilDB, $ilUser;
		
		//get existing account - sco id is always 0
		$val_set = $ilDB->queryF('
			SELECT * FROM scorm_tracking 
			WHERE user_id =  %s
			AND sco_id = %s
			AND lvalue= %s
			AND obj_id = %s',
			array('integer','integer','text','integer'),
			array($ilUser->getId(),0,'package_attempts',$this->slm->getId())
			);

		$val_rec = $ilDB->fetchAssoc($val_set);		
		
		$val_rec["rvalue"] = str_replace("\r\n", "\n", $val_rec["rvalue"]);
		if ($val_rec["rvalue"] == null) {
			$val_rec["rvalue"]=0;
		}
		$new_rec =  $val_rec["rvalue"]+1;
		//increase attempt by 1
		if($ilDB->numRows($val_set) > 0)
		{
			$result = $ilDB->manipulateF('
				UPDATE scorm_tracking
				SET rvalue = %s
				WHERE user_id =  %s
				AND sco_id = %s
				AND obj_id = %s
				AND lvalue= %s',
				array('text', 'integer', 'integer', 'integer', 'text'),
				array($new_rec,$ilUser->getId(),0,$this->slm->getId(),'package_attempts')
			);
		}
		else
		{
			$result = $ilDB->manipulateF('
				INSERT INTO scorm_tracking (rvalue, user_id, sco_id, obj_id, lvalue) 
				VALUES(%s,%s,%s,%s,%s)',
				array('text', 'integer', 'integer', 'integer', 'text'),
				array($new_rec,$ilUser->getId(),0,$this->slm->getId(),'package_attempts')
			);
		}
		
	}
	
	
	/**
	* save the active module version to scorm_tracking
	*/
	function save_module_version() {
		global $ilDB, $ilUser;
		
		$val_set = $ilDB->queryF('
			SELECT * FROM scorm_tracking 
			WHERE user_id =  %s
			AND sco_id = %s
			AND lvalue= %s
			AND obj_id = %s',
			array('integer','integer','text','integer'),
			array($ilUser->getId(),0,'module_version',$this->slm->getId())

			);
		
		if($ilDB->numRows($val_set) > 0)
		{
			$result = $ilDB->manipulateF('
				UPDATE scorm_tracking
				SET rvalue = %s
				WHERE user_id =  %s
				AND sco_id = %s
				AND obj_id = %s
				AND lvalue= %s',
				array('text', 'integer', 'integer', 'integer', 'text'),
				array($new_rec,$ilUser->getId(),0,$this->slm->getId(),'module_version')
			);
		}
		else
		{
			$result = $ilDB->manipulateF('
				INSERT INTO scorm_tracking (rvalue, user_id, sco_id, obj_id, lvalue) 
				VALUES(%s,%s,%s,%s,%s)',
				array('text', 'integer', 'integer', 'integer', 'text'),
				array($new_rec,$ilUser->getId(),0,$this->slm->getId(),'module_version')
			);
		}
	}
	
	/**
	* output table of content
	*/
	function explorer($a_target = "sahs_content")
	{
		global $ilBench, $ilLog;

		$ilBench->start("SCORMExplorer", "initExplorer");
		
		$this->tpl = new ilTemplate("tpl.sahs_exp_main.html", true, true, "Modules/ScormAicc");
		
		require_once("./Modules/ScormAicc/classes/SCORM/class.ilSCORMExplorer.php");
		$exp = new ilSCORMExplorer($this->ctrl->getLinkTarget($this, "view"), $this->slm);
		$exp->setTargetGet("obj_id");
		$exp->setFrameTarget($a_target);
		
		//$exp->setFiltered(true);

		if ($_GET["scexpand"] == "")
		{
			$mtree = new ilSCORMTree($this->slm->getId());
			$expanded = $mtree->readRootId();
		}
		else
		{
			$expanded = $_GET["scexpand"];
		}
		$exp->setExpand($expanded);
		
		$exp->forceExpandAll(true, false);
		$ilBench->stop("SCORMExplorer", "initExplorer");

		// build html-output
		$ilBench->start("SCORMExplorer", "setOutput");
		$exp->setOutput(0);
		$ilBench->stop("SCORMExplorer", "setOutput");

		$ilBench->start("SCORMExplorer", "getOutput");
		$output = $exp->getOutput();
		$ilBench->stop("SCORMExplorer", "getOutput");

		$this->tpl->setVariable("LOCATION_STYLESHEET", ilUtil::getStyleSheetLocation());
		$this->tpl->addBlockFile("CONTENT", "content", "tpl.sahs_explorer.html", "Modules/ScormAicc");
		//$this->tpl->setVariable("TXT_EXPLORER_HEADER", $this->lng->txt("cont_content"));
		$this->tpl->setVariable("EXP_REFRESH", $this->lng->txt("refresh"));
		$this->tpl->setVariable("EXPLORER",$output);
		$this->tpl->setVariable("ACTION", "ilias.php?baseClass=ilSAHSPresentationGUI&cmd=".$_GET["cmd"]."&frame=".$_GET["frame"].
			"&ref_id=".$this->slm->getRefId()."&scexpand=".$_GET["scexpand"]);
		$this->tpl->parseCurrentBlock();
		$this->tpl->show();
	}


	/**
	* SCORM content screen
	*/
	function view()
	{
		$sc_gui_object =& ilSCORMObjectGUI::getInstance($_GET["obj_id"]);

		if(is_object($sc_gui_object))
		{
			$sc_gui_object->view();
		}

		$this->tpl->setVariable("LOCATION_STYLESHEET", ilUtil::getStyleSheetLocation());
		$this->tpl->show(false);
	}

	function api()
	{
		global $ilias;

		$slm_obj =& new ilObjSCORMLearningModule($_GET["ref_id"]);

		$this->tpl = new ilTemplate("tpl.sahs_api.html", true, true, "Modules/ScormAicc");
		
		// for scorm modules with only one presentable item: launch item
		if ($_GET["autolaunch"] != "")
		{
			$this->tpl->setCurrentBlock("auto_launch");
			include_once("./Modules/ScormAicc/classes/SCORM/class.ilSCORMItem.php");
			include_once("./Modules/ScormAicc/classes/SCORM/class.ilSCORMResource.php");
			$sc_object =& new ilSCORMItem($_GET["autolaunch"]);
			$id_ref = $sc_object->getIdentifierRef();
			$sc_res_id = ilSCORMResource::_lookupIdByIdRef($id_ref, $sc_object->getSLMId());
			$scormtype = strtolower(ilSCORMResource::_lookupScormType($sc_res_id));
			
			if ($scormtype == "asset")
			{
				$item_command = "IliasLaunchAsset";
			}
			else
			{
				$item_command = "IliasLaunchSahs";
			}
			$this->tpl->setVariable("AUTO_LAUNCH_ID", $_GET["autolaunch"]);
			$this->tpl->setVariable("AUTO_LAUNCH_CMD", "this.autoLaunch();");
			$this->tpl->setVariable("AUTO_LAUNCH_ITEM_CMD", $item_command);
			$this->tpl->parseCurrentBlock();
		}

		$this->tpl->setVariable("USER_ID",$ilias->account->getId());
		$this->tpl->setVariable("USER_FIRSTNAME",$ilias->account->getFirstname());
		$this->tpl->setVariable("USER_LASTNAME",$ilias->account->getLastname());
		$this->tpl->setVariable("USER_LOGIN",$ilias->account->getLogin());
		$this->tpl->setVariable("USER_OU",$ilias->account->getDepartment());
		$this->tpl->setVariable("REF_ID",$_GET["ref_id"]);
		$this->tpl->setVariable("SESSION_ID",session_id());
		$this->tpl->setVariable("CODE_BASE", "http://".$_SERVER['SERVER_NAME'].substr($_SERVER['PHP_SELF'], 0, strpos ($_SERVER['PHP_SELF'], "/ilias.php")));
		
		$this->tpl->parseCurrentBlock();
		$this->tpl->show(false);
		exit;
	}

	/**
	* This function is called by the API applet in the content frame
	* when a SCO is started.
	*/
	function launchSahs()
	{
		global $ilUser, $ilDB;

		$sco_id = ($_GET["sahs_id"] == "")
			? $_POST["sahs_id"]
			: $_GET["sahs_id"];
		$ref_id = ($_GET["ref_id"] == "")
			? $_POST["ref_id"]
			: $_GET["ref_id"];

		$this->slm =& new ilObjSCORMLearningModule($ref_id, true);

		include_once("./Modules/ScormAicc/classes/SCORM/class.ilSCORMItem.php");
		include_once("./Modules/ScormAicc/classes/SCORM/class.ilSCORMResource.php");
		$item =& new ilSCORMItem($sco_id);

		$id_ref = $item->getIdentifierRef();
		$resource =& new ilSCORMResource();
		$resource->readByIdRef($id_ref, $item->getSLMId());
		//$slm_obj =& new ilObjSCORMLearningModule($_GET["ref_id"]);
		$href = $resource->getHref();
		$this->tpl = new ilTemplate("tpl.sahs_launch_cbt.html", true, true, "Modules/ScormAicc");
		$this->tpl->setVariable("HREF", $this->slm->getDataDirectory("output")."/".$href);

		// set item data
		$this->tpl->setVariable("LAUNCH_DATA", $item->getDataFromLms());
		$this->tpl->setVariable("MAST_SCORE", $item->getMasteryScore());
		$this->tpl->setVariable("MAX_TIME", $item->getMaxTimeAllowed());
		$this->tpl->setVariable("LIMIT_ACT", $item->getTimeLimitAction());

		// set alternative API name
		if ($this->slm->getAPIAdapterName() != "API")
		{
			$this->tpl->setCurrentBlock("alt_api_ref");
			$this->tpl->setVariable("API_NAME", $this->slm->getAPIAdapterName());
			$this->tpl->parseCurrentBlock();
		}

		$val_set = $ilDB->queryF('
			SELECT * FROM scorm_tracking 
			WHERE user_id = %s
			AND sco_id = %s
			AND obj_id = %s',
			array('integer','integer','integer'),
			array($ilUser->getId(),$sco_id,$this->slm->getId())	
		);
		$re_value = array();
		while($val_rec = $ilDB->fetchAssoc($val_set))		
		{
			$val_rec["rvalue"] = str_replace("\r\n", "\n", $val_rec["rvalue"]);
			$val_rec["rvalue"] = str_replace("\r", "\n", $val_rec["rvalue"]);
			$val_rec["rvalue"] = str_replace("\n", "\\n", $val_rec["rvalue"]);
			$re_value[$val_rec["lvalue"]] = $val_rec["rvalue"];
		}
		
		foreach($re_value as $var => $value)
		{
			switch ($var)
			{
				case "cmi.core.lesson_location":
				case "cmi.core.lesson_status":
				case "cmi.core.entry":
				case "cmi.core.score.raw":
				case "cmi.core.score.max":
				case "cmi.core.score.min":
				case "cmi.core.total_time":
				case "cmi.core.exit":
				case "cmi.suspend_data":
				case "cmi.comments":
				case "cmi.student_preference.audio":
				case "cmi.student_preference.language":
				case "cmi.student_preference.speed":
				case "cmi.student_preference.text":
					$this->setSingleVariable($var, $value);
					break;

				case "cmi.objectives._count":
					$this->setSingleVariable($var, $value);
					$this->setArray("cmi.objectives", $value, "id", $re_value);
					$this->setArray("cmi.objectives", $value, "score.raw", $re_value);
					$this->setArray("cmi.objectives", $value, "score.max", $re_value);
					$this->setArray("cmi.objectives", $value, "score.min", $re_value);
					$this->setArray("cmi.objectives", $value, "status", $re_value);
					break;

				case "cmi.interactions._count":
					$this->setSingleVariable($var, $value);
					$this->setArray("cmi.interactions", $value, "id", $re_value);
					for($i=0; $i<$value; $i++)
					{
						$var2 = "cmi.interactions.".$i.".objectives._count";
						if (isset($v_array[$var2]))
						{
							$cnt = $v_array[$var2];
							$this->setArray("cmi.interactions.".$i.".objectives",
								$cnt, "id", $re_value);
							/*
							$this->setArray("cmi.interactions.".$i.".objectives",
								$cnt, "score.raw", $re_value);
							$this->setArray("cmi.interactions.".$i.".objectives",
								$cnt, "score.max", $re_value);
							$this->setArray("cmi.interactions.".$i.".objectives",
								$cnt, "score.min", $re_value);
							$this->setArray("cmi.interactions.".$i.".objectives",
								$cnt, "status", $re_value);*/
						}
					}
					$this->setArray("cmi.interactions", $value, "time", $re_value);
					$this->setArray("cmi.interactions", $value, "type", $re_value);
					for($i=0; $i<$value; $i++)
					{
						$var2 = "cmi.interactions.".$i.".correct_responses._count";
						if (isset($v_array[$var2]))
						{
							$cnt = $v_array[$var2];
							$this->setArray("cmi.interactions.".$i.".correct_responses",
								$cnt, "pattern", $re_value);
							$this->setArray("cmi.interactions.".$i.".correct_responses",
								$cnt, "weighting", $re_value);
						}
					}
					$this->setArray("cmi.interactions", $value, "student_response", $re_value);
					$this->setArray("cmi.interactions", $value, "result", $re_value);
					$this->setArray("cmi.interactions", $value, "latency", $re_value);
					break;
			}
		}

		global $lng;
		$this->tpl->setCurrentBlock("switch_icon");
		$this->tpl->setVariable("SCO_ID", $_GET["sahs_id"]);
		$this->tpl->setVariable("SCO_ICO", ilUtil::getImagePath("scorm/running.gif"));
		$this->tpl->setVariable("SCO_ALT",
			 $lng->txt("cont_status").": "
			.$lng->txt("cont_sc_stat_running")
		);
		$this->tpl->parseCurrentBlock();
		
		// set icon, if more than one SCO/Asset is presented
		$items = ilSCORMObject::_lookupPresentableItems($this->slm->getId());
		if (count($items) > 1
			|| strtolower(get_class($this->slm)) == "ilobjaicclearningmodule"
			|| strtolower(get_class($this->slm)) == "ilobjhacplearningmodule")
		{
			$this->tpl->setVariable("SWITCH_ICON_CMD", "switch_icon();");
		}


		// lesson mode
		$lesson_mode = $this->slm->getDefaultLessonMode();
		if ($this->slm->getAutoReview())
		{
			if ($re_value["cmi.core.lesson_status"] == "completed" ||
				$re_value["cmi.core.lesson_status"] == "passed" ||
				$re_value["cmi.core.lesson_status"] == "failed")
			{
				$lesson_mode = "review";
			}
		}
		$this->tpl->setVariable("LESSON_MODE", $lesson_mode);

		// credit mode
		if ($lesson_mode == "normal")
		{
			$this->tpl->setVariable("CREDIT_MODE",
				str_replace("_", "-", $this->slm->getCreditMode()));
		}
		else
		{
			$this->tpl->setVariable("CREDIT_MODE", "no-credit");
		}

		// init cmi.core.total_time, cmi.core.lesson_status and cmi.core.entry
		$sahs_obj_id = ilObject::_lookupObjId($_GET["ref_id"]);
		if (!isset($re_value["cmi.core.total_time"]))
		{
			$item->insertTrackData("cmi.core.total_time", "0000:00:00", $sahs_obj_id);
		}
		if (!isset($re_value["cmi.core.lesson_status"]))
		{
			$item->insertTrackData("cmi.core.lesson_status", "not attempted", $sahs_obj_id);
		}
		if (!isset($re_value["cmi.core.entry"]))
		{
			$item->insertTrackData("cmi.core.entry", "", $sahs_obj_id);
		}

		$this->tpl->show();
		//echo htmlentities($this->tpl->get()); exit;
	}

	function finishSahs ()
	{
		global $lng;
		$this->tpl = new ilTemplate("tpl.sahs_finish_cbt.html", true, true, "Modules/ScormAicc");
		$this->tpl->setVariable("LOCATION_STYLESHEET", ilUtil::getStyleSheetLocation());

		$this->tpl->setCurrentBlock("switch_icon");
		$this->tpl->setVariable("SCO_ID", $_GET["sahs_id"]);
		$this->tpl->setVariable("SCO_ICO", ilUtil::getImagePath(
			"scorm/".str_replace(" ", "_", $_GET["status"]).'.gif')
		);
		$this->tpl->setVariable("SCO_ALT",
			 $lng->txt("cont_status").": "
			.$lng->txt("cont_sc_stat_".str_replace(" ", "_", $_GET["status"])).", "
			.$lng->txt("cont_total_time").  ": "
			.$_GET["totime"]
		);
		// BEGIN Partial fix for SCO sequencing:
                //       With this partial fix, ILIAS can now proceed to the next
                //          SCO, if it is a sibling of the current SCO.
                //       This fix doesn't fix the case, if the next SCO has a 
                //          different parent item. 
		//$this->tpl->setVariable("SCO_LAUNCH_ID", $_GET["launch"]);
		
		$launch_id = $_GET['launch'];
		if ($launch_id == 'null' || $launch_id == null) {
			require_once("./Modules/ScormAicc/classes/SCORM/class.ilSCORMTree.php");
			$mtree = new ilSCORMTree($this->slm->getId());
			$node_data = $mtree->fetchSuccessorNode($_GET['sahs_id']);
			if ($node_data && $node_data[type] == 'sit')
			{
				$launch_id = $node_data['child'];
			}
		}
		// END Partial fix for SCO sequencing
		$this->tpl->setVariable("SCO_LAUNCH_ID", $launch_id);
		$this->tpl->parseCurrentBlock();
		$this->tpl->show();
	}

	function unloadSahs ()
	{
		$this->tpl = new ilTemplate("tpl.sahs_unload_cbt.html", true, true, "Modules/ScormAicc");
		$this->tpl->setVariable("LOCATION_STYLESHEET", ilUtil::getStyleSheetLocation());
		$this->tpl->setVariable("SCO_ID", $_GET["sahs_id"]);
		$this->tpl->show();
	}


	function launchAsset()
	{
		global $ilUser, $ilDB;

		$sco_id = ($_GET["asset_id"] == "")
			? $_POST["asset_id"]
			: $_GET["asset_id"];
		$ref_id = ($_GET["ref_id"] == "")
			? $_POST["ref_id"]
			: $_GET["ref_id"];

		$this->slm =& new ilObjSCORMLearningModule($ref_id, true);

		include_once("./Modules/ScormAicc/classes/SCORM/class.ilSCORMItem.php");
		include_once("./Modules/ScormAicc/classes/SCORM/class.ilSCORMResource.php");
		$item =& new ilSCORMItem($sco_id);

		$id_ref = $item->getIdentifierRef();
		$resource =& new ilSCORMResource();
		$resource->readByIdRef($id_ref, $item->getSLMId());
		$href = $resource->getHref();
		$this->tpl->setVariable("HREF", $this->slm->getDataDirectory("output")."/".$href);
		$this->tpl = new ilTemplate("tpl.scorm_launch_asset.html", true, true, "Modules/ScormAicc");
		$this->tpl->setVariable("HREF", $this->slm->getDataDirectory("output")."/".$href);
		$this->tpl->show();
	}


	/**
	* set single value
	*/
	function setSingleVariable($a_var, $a_value)
	{
		$this->tpl->setCurrentBlock("set_value");
		$this->tpl->setVariable("VAR", $a_var);
		$this->tpl->setVariable("VALUE", $a_value);
		$this->tpl->parseCurrentBlock();
	}

	/**
	* set single value
	*/
	function setArray($a_left, $a_value, $a_name, &$v_array)
	{
		for($i=0; $i<$a_value; $i++)
		{
			$var = $a_left.".".$i.".".$a_name;
			if (isset($v_array[$var]))
			{
				$this->tpl->setCurrentBlock("set_value");
				$this->tpl->setVariable("VAR", $var);
				$this->tpl->setVariable("VALUE", $v_array[$var]);
				$this->tpl->parseCurrentBlock();
			}
		}
	}

	/**
	* Download the certificate for the active user
	*/
	public function downloadCertificate()
	{
		global $ilUser, $tree;

		$allowed = false;
		$last_access = 0;
		$obj_id = ilObject::_lookupObjId($_GET["ref_id"]);
		include_once "./Modules/ScormAicc/classes/class.ilObjSAHSLearningModuleAccess.php";
		if (ilObjSAHSLearningModuleAccess::_lookupUserCertificate($obj_id))
		{
			include_once "./Modules/ScormAicc/classes/class.ilObjSAHSLearningModule.php";
			$type = ilObjSAHSLearningModule::_lookupSubType($obj_id);
			switch ($type)
			{
				case "scorm":
					include_once "./Modules/ScormAicc/classes/class.ilObjSCORMLearningModule.php";
					$allowed = true;
					$last_access = ilObjSCORMLearningModule::_lookupLastAccess($obj_id, $ilUser->getId());
					break;
				case "scorm2004":
					include_once "./Modules/Scorm2004/classes/class.ilObjSCORM2004LearningModule.php";
					$allowed = true;
					$last_access = ilObjSCORM2004LearningModule::_lookupLastAccess($obj_id, $ilUser->getId());
					break;
				default:
					break;
			}
		}
		
		if ($allowed)
		{
			include_once "./Services/Certificate/classes/class.ilCertificate.php";
			include_once "./Modules/ScormAicc/classes/class.ilSCORMCertificateAdapter.php";
			$certificate = new ilCertificate(new ilSCORMCertificateAdapter($this->slm));
			$params = array(
				"user_data" => ilObjUser::_lookupFields($ilUser->getId()),
				"last_access" => $last_access
			);
			$certificate->outCertificate($params, true);
			exit;
		}
		// redirect to parent category if certificate is not accessible
		$parent = $tree->getParentId($_GET["ref_id"]);
		$cmd_link = "repository.php?ref_id=".$parent;
		ilUtil::redirect($cmd_link);
	}
}
?>
