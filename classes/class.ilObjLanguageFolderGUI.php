<?php
/**
* Class ilObjLanguageFolderGUI
*
* @author	Stefan Meyer <smeyer@databay.de>
* @version	$Id$Id: class.LanguageFolderObjectOut.php,v 1.15 2003/03/24 10:23:01 shofmann Exp $
*
* @extends	ilObject
* @package	ilias-core
*/

require_once "classes/class.ilObjLanguage.php";

class ilObjLanguageFolderGUI extends ilObjectGUI
{
	//var $LangFolderObject;

	/**
	* Constructor
	* @access public
	*/
	function ilObjLanguageFolderGUI($a_data,$a_id,$a_call_by_reference)
	{
		$this->type = "lngf";
		$this->ilObjectGUI($a_data,$a_id,$a_call_by_reference);
		
		// TODO: was soll der quatsch??
		//$this->LangFolderObject =& new ilObjLanguageFolder($_GET["obj_id"]);
	}

	/**
	* Overwritten method from class.Object.php
	* It handles all button commands from Learning Modules
	*
	* @access public
	*/
	function gatewayObject()
	{
		switch(key($_POST["cmd"]))
		{
			case "install":
				$this->installObject();
				break;

			case "uninstall":
				$this->uninstallObject();
				break;

			case "refresh":
				$this->refreshObject();
				break;

			case "set_system_language":
				$this->setsyslangObject();
				break;

			case "change_language":
				$this->setuserlangObject();
				break;

			case "check_language":
				$this->checklangObject();
				break;

		}

		parent::gatewayObject();
	}

	/**
	* show installed languages
	*/
	function viewObject()
	{
		$this->getTemplateFile("view");
		$num = 0;

		$this->tpl->setVariable("FORMACTION", "adm_object.php?ref_id=".$_GET["ref_id"]."&cmd=gateway");

		//table header
		$this->tpl->setCurrentBlock("table_header_cell");

		$cols = array("", "type", "language", "status", "", "last_change");

		foreach ($cols as $key)
		{
			if ($key != "")
			{
			    $out = $this->lng->txt($key);
			}
			else
			{
				$out = "&nbsp;";
			}

			$this->tpl->setVariable("HEADER_TEXT", $out);
			$this->tpl->setVariable("HEADER_LINK", "adm_object.php?ref_id=".$_GET["ref_id"]."&order=type&direction=".$_GET["dir"]);
			$this->tpl->parseCurrentBlock();
		}

		$languages = $this->object->getLanguages();
		//$languages = $this->LangFolderObject->getLanguages();

		foreach ($languages as $lang_key => $lang_data)
		{
			$status = "";

			// set status info (in use oder systemlanguage)
			if ($lang_data["status"])
			{
				$status = "<span class=\"small\"> (".$this->lng->txt($lang_data["status"]).")</span>";
			}
				// set remark color
			switch ($lang_data["info"])
			{
				case "file_not_found":
					$remark = "<span class=\"smallred\"> ".$this->lng->txt($lang_data["info"])."</span>";
					break;
				case "new_language":
					$remark = "<span class=\"smallgreen\"> ".$this->lng->txt($lang_data["info"])."</span>";
					break;
				default:
					$remark = "";
					break;
			}

			$data = $this->data["data"][$i];
			$ctrl = $this->data["ctrl"][$i];
			$num++;
			// color changing
			$css_row = TUtil::switchColor($num,"tblrow1","tblrow2");
			$this->tpl->setCurrentBlock("checkbox");

			$this->tpl->setVariable("CHECKBOX_ID", $lang_data["obj_id"]);
			$this->tpl->setVariable("CSS_ROW", $css_row);
			$this->tpl->parseCurrentBlock();

			$this->tpl->setCurrentBlock("table_cell");
			$this->tpl->parseCurrentBlock();
			//data
			$data = array(
						"type" => "<img src=\"".$this->tpl->tplPath."/images/icon_lng_b.gif\" border=\"0\">",
						"name" => $lang_data["name"].$status,
						"status" => $this->lng->txt($lang_data["desc"]),
						"remark" => $remark,
						"last_change" => Format::formatDate($lang_data["last_update"])
						);
			
			foreach ($data as $key => $val)
			{
				$this->tpl->setCurrentBlock("text");
				$this->tpl->setVariable("TEXT_CONTENT", $val);
				$this->tpl->parseCurrentBlock();
				$this->tpl->setCurrentBlock("table_cell");
				$this->tpl->parseCurrentBlock();
			} //foreach

			$this->tpl->setCurrentBlock("table_row");
			$this->tpl->setVariable("CSS_ROW", $css_row);
			$this->tpl->parseCurrentBlock();
		} //for

		// SHOW VALID ACTIONS
		$this->showActions();
	}


	/**
	* install languages
	*/
	function installObject()
	{
		if (!isset($_POST["id"]))
		{
			$this->ilias->raiseError($this->lng->txt("nothing_checked"),$this->ilias->error_obj->MESSAGE);
		}

		foreach ($_POST["id"] as $obj_id)
		{
			$langObj = new ilObjLanguage($obj_id);
			$key = $langObj->install();
			
			if ($key != "")
			{
				$lang_installed[] = $key;
			}

			unset($langObj);
		}

		if (isset($lang_installed))
		{
			if (count($lang_installed) == 1)
			{
				$this->data = $this->lng->txt("lang_".$lang_installed[0])." ".strtolower($this->lng->txt("installed")).".";
			}
			else
			{
				foreach ($lang_installed as $lang_key)
				{
					$langnames[] = $this->lng->txt("lang_".$lang_key);
				}
				$this->data = implode(", ",$langnames)." ".strtolower($this->lng->txt("installed")).".";
			}
		}
		else
			$this->data = $this->lng->txt("languages_already_installed");

		$this->out();
	}


	/**
	* uninstall language
	*/
	function uninstallObject()
	{
		if (!isset($_POST["id"]))
		{
			$this->ilias->raiseError($this->lng->txt("nothing_checked"),$this->ilias->error_obj->MESSAGE);
		}

		// uninstall all selected languages
		foreach ($_POST["id"] as $obj_id)
		{
			$langObj = new ilObjLanguage($obj_id);
			if (!($sys_lang = $langObj->isSystemLanguage()))
				if (!($usr_lang = $langObj->isUserLanguage()))
				{
					$key = $langObj->uninstall();
					if ($key != "")
						$lang_uninstalled[] = $key;
				}
			unset($langObj);
		}

		// generate output message
		if (isset($lang_uninstalled))
		{
			if (count($lang_uninstalled) == 1)
			{
				$this->data = $this->lng->txt("lang_".$lang_uninstalled[0])." ".$this->lng->txt("uninstalled");
			}
			else
			{
				foreach ($lang_uninstalled as $lang_key)
				{
					$langnames[] = $this->lng->txt("lang_".$lang_key);
				}

				$this->data = implode(", ",$langnames)." ".$this->lng->txt("uninstalled");
			}
		}
		elseif ($sys_lang)
		{
			$this->data = $this->lng->txt("cannot_uninstall_systemlanguage");
		}
		elseif ($usr_lang)
		{
			$this->data = $this->lng->txt("cannot_uninstall_language_in_use");
		}
		else
		{
			$this->data = $this->lng->txt("languages_already_uninstalled");
		}

		$this->out();
	}

	/**
	* update all installed languages
	*/
	function refreshObject()
	{
		$languages = getObjectList("lng");

		foreach ($languages as $lang)
		{
			$langObj = new ilObjLanguage($lang["obj_id"],false);

			if ($langObj->getStatus() == "installed")
			{
				if ($langObj->check())
				{
					$langObj->flush();
					$langObj->insert();
					$langObj->setTitle($langObj->getKey());
					$langObj->setDescription($langObj->getStatus());
					$langObj->update();
					$langObj->optimizeData();
				}
			}

			unset($langObj);
		}

		$this->data = $this->lng->txt("languages_updated");

		$this->out();
	}


	/**
	* set user language
	*/
	function setuserlangObject()
	{
		require_once "classes/class.User.php";

		if (!isset($_POST["id"]))
		{
			$this->ilias->raiseError($this->lng->txt("nothing_checked"),$this->ilias->error_obj->MESSAGE);
		}

		if (count($_POST["id"]) != 1)
		{
			$this->ilias->raiseError($this->lng->txt("choose_only_one_language")."<br/>".$this->lng->txt("action_aborted"),$this->ilias->error_obj->MESSAGE);
		}

		$obj_id = $_POST["id"][0];

		$newUserLangObj = new ilObjLanguage($obj_id);

		if ($newUserLangObj->isUserLanguage())
		{
			$this->ilias->raiseError($this->lng->txt("lang_".$newUserLangObj->getKey())." ".$this->lng->txt("is_already_your")." ".$this->lng->txt("user_language")."<br/>".$this->lng->txt("action_aborted"),$this->ilias->error_obj->MESSAGE);
		}

		if ($newUserLangObj->getStatus() != "installed")
		{
			$this->ilias->raiseError($this->lng->txt("lang_".$newUserLangObj->getKey())." ".$this->lng->txt("language_not_installed")."<br/>".$this->lng->txt("action_aborted"),$this->ilias->error_obj->MESSAGE);
		}

		$curUser = new User($_SESSION["AccountId"]);
		$curUser->setLanguage($newUserLangObj->getKey());
		$curUser->update();
		//$this->setUserLanguage($new_lang_key);

		$this->data = $this->lng->txt("user_language")." ".$this->lng->txt("changed_to")." ".$this->lng->txt("lang_".$newUserLangObj->getKey()).".";

		$this->out();
	}


	/**
	* set the system language
	*/
	function setsyslangObject ()
	{
		if (!isset($_POST["id"]))
		{
			$this->ilias->raiseError($this->lng->txt("nothing_checked"),$this->ilias->error_obj->MESSAGE);
		}

		if (count($_POST["id"]) != 1)
		{
			$this->ilias->raiseError($this->lng->txt("choose_only_one_language")."<br/>".$this->lng->txt("action_aborted"),$this->ilias->error_obj->MESSAGE);
		}

		$obj_id = $_POST["id"][0];

		$newSysLangObj = new ilObjLanguage($obj_id);

		if ($newSysLangObj->isSystemLanguage())
		{
			$this->ilias->raiseError($this->lng->txt("lang_".$newSysLangObj->getKey())." is already the system language!<br>Action aborted!",$this->ilias->error_obj->MESSAGE);
		}

		if ($newSysLangObj->getStatus() != "installed")
		{
			$this->ilias->raiseError($this->lng->txt("lang_".$newSysLangObj->getKey())." is not installed. Please install that language first.<br>Action aborted!",$this->ilias->error_obj->MESSAGE);
		}

		$this->ilias->setSetting("language", $newSysLangObj->getKey());

		// update ini-file
		$this->ilias->ini->setVariable("language","default",$newSysLangObj->getKey());
		$this->ilias->ini->write();

		$this->data = $this->lng->txt("system_language")." ".$this->lng->txt("changed_to")." ".$this->lng->txt("lang_".$newSysLangObj->getKey()).".";

		$this->out();
	}


	/**
	* check all languages
	*/
	function checklangObject ()
	{
		//$langFoldObj = new ilObjLanguageFolder($_GET["obj_id"]);
		//$this->data = $langFoldObj->checkAllLanguages();
		$this->data = $this->object->checkAllLanguages();
		$this->out();
	}


	function out()
	{
		sendInfo($this->data,true);
		header("location: adm_object.php?ref_id=".$_GET["ref_id"]);
		exit();
	}
} // END class.LanguageFolderObjectOut
?>
