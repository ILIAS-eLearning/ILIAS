<?php
/**
* Class LanguageFolderObjectOut
*
* @author	Stefan Meyer <smeyer@databay.de>
* @version	$Id$Id: class.LanguageFolderObjectOut.php,v 1.11 2003/03/13 17:48:30 akill Exp $
*
* @extends	Object
* @package	ilias-core
*/

require_once "classes/class.LanguageObject.php";

class LanguageFolderObjectOut extends ObjectOut
{
	var $LangFolderObject;

	/**
	* Constructor
	* @access public
	*/
	function LanguageFolderObjectOut($a_data,$a_id,$a_call_by_reference)
	{
		$this->type = "lngf";
		$this->ObjectOut($a_data,$a_id,$a_call_by_reference);
		
		// TODO: was soll der quatsch??
		$this->LangFolderObject =& new LanguageFolderObject($_GET["obj_id"]);
	}

	/**
	* Overwritten method from class.Object.php
	* It handles all button commands from Learning Modules
	*
	* @access public
	*/
	function gatewayObject()
	{
		global $lng;

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
		global $lng;

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
			$this->tpl->setVariable("HEADER_LINK", "adm_object.php?obj_id=".$_GET["obj_id"]."&parent=".
							  $_GET["parent"]."&parent_parent=".$_GET["parent_parent"]."&order=type&direction=".
							  $_GET["dir"]."&cmd=".$_GET["cmd"]);
			$this->tpl->parseCurrentBlock();
		}

		$languages = $this->LangFolderObject->getLanguages();

		foreach ($languages as $lang_key => $lang_data)
		{
			$status = "";

			// set status info (in use oder systemlanguage)
			if ($lang_data["status"])
			{
				$status = "<span class=\"small\"> (".$lng->txt($lang_data["status"]).")</span>";
			}
				// set remark color
			switch ($lang_data["info"])
			{
				case "file_not_found":
					$remark = "<span class=\"smallred\"> ".$lng->txt($lang_data["info"])."</span>";
					break;
				case "new_language":
					$remark = "<span class=\"smallgreen\"> ".$lng->txt($lang_data["info"])."</span>";
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
				"status" => $lng->txt($lang_data["desc"]),
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
		global $lng;

		if (!isset($_POST["id"]))
		{
			$this->ilias->raiseError("No checkbox checked. Nothing happened :-)",$this->ilias->error_obj->MESSAGE);
		}

		foreach ($_POST["id"] as $obj_id)
		{
			$langObj = new LanguageObject($obj_id);
			$key = $langObj->install();
			if ($key != "")
				$lang_installed[] = $key;
			unset($langObj);
		}

		if (isset($lang_installed))
		{
			if (count($lang_installed) == 1)
			{
				$this->data = $lng->txt("lang_".$lang_installed[0])." has been installed.";
			}
			else
			{
				foreach ($lang_installed as $lang_key)
				{
					$langnames[] = $lng->txt("lang_".$lang_key);
				}
				$this->data = implode(", ",$langnames)." have been installed.";
			}
		}
		else
			$this->data = "Funny! Chosen language(s) are already installed.";

		$this->out();
	}


	/**
	* uninstall language
	*/
	function uninstallObject()
	{
		global $lng;

		if (!isset($_POST["id"]))
		{
			$this->ilias->raiseError("No checkbox checked. Nothing happened :-)",$this->ilias->error_obj->MESSAGE);
		}

		// uninstall all selected languages
		foreach ($_POST["id"] as $obj_id)
		{
			$langObj = new LanguageObject($obj_id);
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
				$this->data = $lng->txt("lang_".$lang_uninstalled[0])." has been uninstalled.";
			}
			else
			{
				foreach ($lang_uninstalled as $lang_key)
				{
					$langnames[] = $lng->txt("lang_".$lang_key);
				}

				$this->data = implode(", ",$langnames)." have been uninstalled.";
			}
		}
		elseif ($sys_lang)
		{
			$this->data = "You cannot uninstall the system language!";
		}
		elseif ($usr_lang)
		{
			$this->data = "You cannot uninstall the language currently in use!";
		}
		else
		{
			$this->data = "Funny! Chosen language(s) are already uninstalled.";
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
			$langObj = new LanguageObject($lang["obj_id"],false);

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

		$this->data = "All installed languages have been updated!";

		$this->out();
	}


	/**
	* set user language
	*/
	function setuserlangObject()
	{
		global $lng;
		require_once "classes/class.User.php";

		if (!isset($_POST["id"]))
		{
			$this->ilias->raiseError("No checkbox checked. Nothing happened :-)",$this->ilias->error_obj->MESSAGE);
		}

		if (count($_POST["id"]) != 1)
		{
			$this->ilias->raiseError("Please choose only one language. Action aborted!",$this->ilias->error_obj->MESSAGE);
		}

		$obj_id = $_POST["id"][0];

		$newUserLangObj = new LanguageObject($obj_id);
		//$new_lang = getObject($obj_id);
		//$new_lang_key = $new_lang["title"];
		//$new_lang_status = $new_lang["desc"];

		if ($newUserLangObj->isUserLanguage())
		{
			$this->ilias->raiseError($lng->txt("lang_".$newUserLangObj->getKey())." is already your user language!<br>Action aborted!",$this->ilias->error_obj->MESSAGE);
		}

		/*foreach ($this->languages as $lang_key => $lang_data)
		{
			if ($new_lang_key == $lang_key && $new_lang_status != "installed")
			{
				$this->ilias->raiseError($lng->txt("lang_".$new_lang_key)." is not installed. Please install that language first.<br>Action aborted!",$this->ilias->error_obj->MESSAGE);
			}
		}*/

		if ($newUserLangObj->getStatus() != "installed")
		{
			$this->ilias->raiseError($lng->txt("lang_".$newUserLangObj->getKey())." is not installed. Please install that language first.<br>Action aborted!",$this->ilias->error_obj->MESSAGE);
		}

		$curUser = new User($_SESSION["AccountId"]);
		$curUser->setLanguage($newUserLangObj->getKey());
		$curUser->update();
		//$this->setUserLanguage($new_lang_key);

		$this->data = "Userlanguage changed to ".$lng->txt("lang_".$newUserLangObj->getKey()).".";

		$this->out();
	}


	/**
	* set the system language
	*/
	function setsyslangObject ()
	{
		if (!isset($_POST["id"]))
		{
			$this->ilias->raiseError("No checkbox checked. Nothing happened :-)",$this->ilias->error_obj->MESSAGE);
		}

		if (count($_POST["id"]) != 1)
		{
			$this->ilias->raiseError("Please choose only one language.<br>Action aborted!",$this->ilias->error_obj->MESSAGE);
		}

		$obj_id = $_POST["id"][0];

		$newSysLangObj = new LanguageObject($obj_id);

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

		$this->data = "Systemlanguage changed to ".$this->lng->txt("lang_".$newSysLangObj->getKey()).".";


		$this->out();
	}


	/**
	* check all languages
	*/
	function checklangObject ()
	{
		$langFoldObj = new LanguageFolderObject($_GET["obj_id"]);
		$this->data = $langFoldObj->checkAllLanguages();
		$this->out();
	}


	function out()
	{
		$this->ilias->error_obj->sendInfo($this->data);
		header("location: adm_object.php?ref_id=".$_GET["ref_id"]."&cmd=view");
		exit();
	}
} // END class.LanguageFolderObjectOut
?>
