<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("./Services/COPage/classes/class.ilPCSourceCode.php");
require_once("./Services/COPage/classes/class.ilPageContentGUI.php");

/**
* Class ilPCSourcecodeGUI
*
* User Interface for Paragraph Editing
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ServicesCOPage
*/
class ilPCSourceCodeGUI extends ilPageContentGUI
{
	
	/**
	* Constructor
	* @access	public
	*/
	function ilPCSourceCodeGUI($a_pg_obj, $a_content_obj, $a_hier_id, $a_pc_id = "")
	{
		parent::ilPageContentGUI($a_pg_obj, $a_content_obj, $a_hier_id, $a_pc_id);
	}


	/**
	* execute command
	*/
	function &executeCommand()
	{
		// get next class that processes or forwards current command
		$next_class = $this->ctrl->getNextClass($this);

		// get current command
		$cmd = $this->ctrl->getCmd();

		switch($next_class)
		{
			default:
				$ret =& $this->$cmd();
				break;
		}

		return $ret;
	}

	/**
	* edit paragraph form
	*/
	function edit()
	{
		$form = $this->initPropertyForm($this->lng->txt("cont_edit_src"), "update", "cancelCreate");

		if ($this->pg_obj->getParentType() == "lm" ||
			$this->pg_obj->getParentType() == "dbk")
		{
			$this->tpl->setVariable("LINK_ILINK",
				$this->ctrl->getLinkTargetByClass("ilInternalLinkGUI", "showLinkHelp"));
			$this->tpl->setVariable("TXT_ILINK", "[".$this->lng->txt("cont_internal_link")."]");
		}

		$this->displayValidationError();

		if (key($_POST["cmd"]) == "update")
		{
			$form->setValuesByPost();

		}
		else{
			$form->getItemByPostVar("par_language")->setValue($this->content_obj->getLanguage());
			$form->getItemByPostVar("par_subcharacteristic")->setValue($this->content_obj->getSubCharacteristic());
			$form->getItemByPostVar("par_downloadtitle")->setValue( $this->content_obj->getDownloadTitle());
			$form->getItemByPostVar("par_showlinenumbers")->setChecked(
				$this->content_obj->getShowLineNumbers()=="y"?true:false);
			$form->getItemByPostVar("par_autoindent")->setChecked(
				$this->content_obj->getAutoIndent()=="y"?true:false);

			$par_content = $this->content_obj->xml2output($this->content_obj->getText());

			//TODO: Find a better way to convert back curly brackets
			$par_content = str_replace("&#123;","{", $par_content);
			$par_content = str_replace("&#125;","}", $par_content);

			$form->getItemByPostVar("par_content")->setValue($par_content);
		}


		$this->tpl->setContent($form->getHTML());
	}
	
	/**
	* insert paragraph form
	*/
	function insert()
	{
		global $ilUser;

		$form = $this->initPropertyForm($this->lng->txt("cont_insert_src"), "create_src", "cancelCreate");

		if ($this->pg_obj->getParentType() == "lm" ||
			$this->pg_obj->getParentType() == "dbk")
		{
			$this->tpl->setVariable("LINK_ILINK",
				$this->ctrl->getLinkTargetByClass("ilInternalLinkGUI", "showLinkHelp"));
			$this->tpl->setVariable("TXT_ILINK", "[".$this->lng->txt("cont_internal_link")."]");
		}

		$this->displayValidationError();

		if (key($_POST["cmd"]) == "create_src")
		{
			$form->setValuesByPost();

		}
		else{
			if ($_SESSION["il_text_lang_".$_GET["ref_id"]] != "")
			{
				$form->getItemByPostVar("par_language")->setValue($_SESSION["il_text_lang_".$_GET["ref_id"]]);
			}
			else
			{
				$form->getItemByPostVar("par_language")->setValue($ilUser->getLanguage());
			}

			$form->getItemByPostVar("par_showlinenumbers")->setChecked(true);
			$form->getItemByPostVar("par_autoindent")->setChecked(true);
			$form->getItemByPostVar("par_subcharacteristic")->setValue("");
			$form->getItemByPostVar("par_content")->setValue("");
		}

		$this->tpl->setContent($form->getHTML());
	}


	/**
	* update paragraph in dom and update page in db
	*/
	function update()
	{
		global $ilBench;

		$this->upload_source();

		$ilBench->start("Editor","Paragraph_update");
		// set language and characteristic
		
		$this->content_obj->setLanguage($_POST["par_language"]);
		$this->content_obj->setCharacteristic($_POST["par_characteristic"]);

		//echo "PARupdate:".htmlentities($this->content_obj->input2xml($_POST["par_content"])).":<br>"; exit;

		 
		// set language and characteristic
		$this->content_obj->setLanguage($_POST["par_language"]);
		$this->content_obj->setSubCharacteristic($_POST["par_subcharacteristic"]);
		$this->content_obj->setDownloadTitle(str_replace('"', '', ilUtil::stripSlashes($_POST["par_downloadtitle"])));
		$this->content_obj->setShowLineNumbers($_POST["par_showlinenumbers"]?"y":"n");
		$this->content_obj->setAutoIndent($_POST["par_autoindent"]?"y":"n");
		$this->content_obj->setSubCharacteristic($_POST["par_subcharacteristic"]);
			$this->content_obj->setCharacteristic("Code");

		$this->updated = $this->content_obj->setText(
			$this->content_obj->input2xml($_POST["par_content"], 0, false));

		if ($this->updated !== true)
		{
			//echo "Did not update!";
			$ilBench->stop("Editor","Paragraph_update");
			$this->edit();
			return;
		}

		$this->updated = $this->pg_obj->update();

		$ilBench->stop("Editor","Paragraph_update");

		if ($this->updated === true && $this->ctrl->getCmd () != "upload" )
		{
			$this->ctrl->returnToParent($this, "jump".$this->hier_id);
		}
		else
		{
			$this->edit();
		}
	}
	
	/**
	* cancel update
	*/
	function cancelUpdate()
	{
		$this->ctrl->returnToParent($this, "jump".$this->hier_id);
	}

	/**
	* create new paragraph in dom and update page in db
	*/
	function create()
	{	
		$this->content_obj = new ilPCSourceCode($this->getPage());
		$this->content_obj->create($this->pg_obj, $this->hier_id, $this->pc_id);
		$this->content_obj->setLanguage($_POST["par_language"]);

		$_SESSION["il_text_lang_".$_GET["ref_id"]] = $_POST["par_language"];

		$uploaded = $this->upload_source();
				
		$this->content_obj->setCharacteristic   ($_POST["par_characteristic"]);
		$this->content_obj->setSubCharacteristic($_POST["par_subcharacteristic"]);
		$this->content_obj->setDownloadTitle    (str_replace('"', '', ilUtil::stripSlashes($_POST["par_downloadtitle"])));
		$this->content_obj->setShowLineNumbers  ($_POST["par_showlinenumbers"]?'y':'n');
		$this->content_obj->setCharacteristic   ('Code');
		$this->content_obj->setAutoIndent   	($_POST["par_autoindent"]?'y':'n');

		if ($uploaded) {
			$this->insert ();
			return;
		}
		
		$this->updated = $this->content_obj->setText(
			$this->content_obj->input2xml($_POST["par_content"], 0, false));
		
		if ($this->updated !== true)
		{
			$this->insert();
			return;
		}
		
		$this->updated = $this->pg_obj->update();

		if ($this->updated === true && !$uploaded)
		{
			$this->ctrl->returnToParent($this, "jump".$this->hier_id);
		}
		else
		{
			$this->insert ();
		}
	}
	
	/**
	* cancel creating paragraph
	*/
	function cancelCreate()
	{
		$this->ctrl->returnToParent($this, "jump".$this->hier_id);
	}
		
	function upload_source () {		
		if (isset($_FILES['userfile']['name']))
		{
			$userfile = $_FILES['userfile']['tmp_name'];
			
			if ($userfile == "" || !is_uploaded_file($userfile))
			{
				$error_str = "<b>Error(s):</b><br>Upload error: file name must not be empty!";
				$this->tpl->setVariable("MESSAGE", $error_str);
				$this->content_obj->setText($this->content_obj->input2xml(stripslashes($_POST["par_content"]), 0, false));
				return false;
			}

			$_POST["par_content"] = file_get_contents($userfile);								
			$_POST["par_downloadtitle"] = $_FILES['userfile']['name'];			
			return true;
		}				
		
		return false;
	} 
	
	
	function readProgLangs () {
		$prog_langs_ini = file ("Services/COPage/syntax_highlight/php/admin/prog_langs.ini");
		$prog_langs = array ("" => $this->lng->txt("cont_src_other"));
		foreach ($prog_langs_ini as $prog_lang) {
			$prog_lang_prop = split (":", $prog_lang);
			if ($prog_lang_prop[2] == 1) {
				$prog_langs[$prog_lang_prop[0]] = $prog_lang_prop[1];
			}
		}
		
		return $prog_langs;
	}

	/**
	 * initiates property form GUI class
	 *
	 * @param string $a_title
	 * @param string $a_cmd
	 * @param string $a_cmd_cancel
	 * @return ilPropertyFormGUI form class
	 */
	function initPropertyForm($a_title, $a_cmd, $a_cmd_cancel)
	{

		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setTitle($a_title);
		$form->setFormAction($this->ctrl->getFormAction($this, $a_cmd));
		$form->addCommandButton($a_cmd_cancel,$this->lng->txt("cancel"));
		$form->addCommandButton($a_cmd,$this->lng->txt("save"));

		require_once("Services/MetaData/classes/class.ilMDLanguageItem.php");
		$lang_var = ilMDLanguageItem::_getLanguages();
		include_once("./Services/Form/classes/class.ilSelectInputGUI.php");
		$lang = new ilSelectInputGUI($this->lng->txt("language"),"par_language");
		$lang->setOptions($lang_var);
		$form->addItem($lang);

		$prog_langs = $this->readProgLangs ();
		$code_style = new ilSelectInputGUI( $this->lng->txt("cont_src"), "par_subcharacteristic");
		$code_style->setOptions($prog_langs);
		$form->addItem($code_style);
		include_once("./Services/Form/classes/class.ilSelectInputGUI.php");
		$line_number = new ilCheckboxInputGUI($this->lng->txt("cont_show_line_numbers"), "par_showlinenumbers");
		$form->addItem($line_number);
		$indent = new ilCheckboxInputGUI($this->lng->txt("cont_autoindent"), "par_autoindent");
		$form->addItem($indent);


		include_once("./Services/Form/classes/class.ilSelectInputGUI.php");
		$code = new ilTextAreaInputGUI("", "par_content");
		$code->setRows(12);
		$form->addItem($code);

		include_once("./Services/Form/classes/class.ilTextInputGUI.php");
		$downlaod_title = new ilTextInputGUI($this->lng->txt("cont_download_title"), "par_downloadtitle");
		$downlaod_title->setSize(40);
		$form->addItem($downlaod_title);

		include_once("./Services/Form/classes/class.ilFileInputGUI.php");
		$file = new ilFileInputGUI($this->lng->txt("import_file"), "userfile");
		$form->addItem($file);

		return $form;
	}
}
