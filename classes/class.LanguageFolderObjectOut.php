<?php
/**
* Class LanguageFolderObjectOut
*
* @author	Stefan Meyer <smeyer@databay.de> 
* @version	$Id$Id: class.LanguageFolderObjectOut.php,v 1.6 2003/02/25 17:36:49 akill Exp $
* 
* @extends	Object
* @package	ilias-core
*/

class LanguageFolderObjectOut extends ObjectOut
{
	var $LangFolderObject;

	/**
	* Constructor
	* @access public
	*/
	function LanguageFolderObjectOut($a_data)
	{
		$this->ObjectOut($a_data);
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
				return $this->out();
				break;

			case "uninstall":
				return $this->out();
				break;

			case "refresh":
				return $this->refreshObject();
				break;

			case "set_system_language":
				return $this->out();
				break;

			case "change_language":
				return $this->out();
				break;

			case "check_language":
				return $this->out();
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

		$this->tpl->setVariable("FORMACTION", "adm_object.php?obj_id=".$_GET["obj_id"]."&parent=".
								$_GET["parent"]."&cmd=gateway");

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

	function installObject()
	{
		$this->out();
	}

	function uninstallObject()
	{
		$this->out();
	}

	function refreshObject()
	{
		$this->out();
	}
	
	function setuserlangObject()
	{
		$this->out();
	}
	
	function setsyslangObject ()
	{
		$this->out();
	}

	function checklangObject ()
	{
		$this->out();	
	}

	function out()
	{
		$this->ilias->error_obj->sendInfo($this->data);
		header("location: adm_object.php?obj_id=".$_GET["obj_id"]."&parent=".
			   $_GET["parent"]."&parent_parent=".$_GET["parent_parent"]."&cmd=view");
		exit();	
	}
} // END class.LanguageFolderObjectOut
?>