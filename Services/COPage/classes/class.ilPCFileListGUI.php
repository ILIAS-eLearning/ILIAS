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

require_once("./Services/COPage/classes/class.ilPCFileList.php");
require_once("./Services/COPage/classes/class.ilPageContentGUI.php");

/**
* Class ilPCListGUI
*
* User Interface for LM List Editing
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ServicesCOPage
*/
class ilPCFileListGUI extends ilPageContentGUI
{

	/**
	* Constructor
	* @access	public
	*/
	function ilPCFileListGUI(&$a_pg_obj, &$a_content_obj, $a_hier_id, $a_pc_id = "")
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
	* insert new file list form
	*/
	function insert()
	{
		global $ilUser;

		// new file list form
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.file_list_new.html", "Services/COPage");
		$this->tpl->setVariable("TXT_ACTION", $this->lng->txt("cont_insert_file_list"));
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));

		$this->displayValidationError();

		if ($_SESSION["il_text_lang_".$_GET["ref_id"]] != "")
		{
			$s_lang = $_SESSION["il_text_lang_".$_GET["ref_id"]];
		}
		else
		{
			$s_lang = $ilUser->getLanguage();
		}


		// select fields for number of columns
		$this->tpl->setVariable("TXT_TITLE", $this->lng->txt("title"));
		$this->tpl->setVariable("INPUT_TITLE", "flst_title");
		$this->tpl->setVariable("TXT_FILE", $this->lng->txt("file"));

		// language
		$this->tpl->setVariable("TXT_LANGUAGE", $this->lng->txt("language"));
		require_once("Services/MetaData/classes/class.ilMDLanguageItem.php");
		$lang = ilMDLanguageItem::_getLanguages();
		$select_lang = ilUtil::formSelect ($s_lang, "flst_language",$lang,false,true);
		$this->tpl->setVariable("SELECT_LANGUAGE", $select_lang);


//		$this->tpl->parseCurrentBlock();

		// operations
		$this->tpl->setCurrentBlock("commands");
		$this->tpl->setVariable("BTN_NAME", "create_flst");
		$this->tpl->setVariable("BTN_TEXT", $this->lng->txt("save"));
		$this->tpl->setVariable("BTN_CANCEL", "cancelCreate");
		$this->tpl->setVariable("TXT_CANCEL", $this->lng->txt("cancel"));
		$this->tpl->parseCurrentBlock();

	}


	/**
	* create new file list in dom and update page in db
	*/
	function create()
	{
		include_once("./Modules/File/classes/class.ilObjFile.php");
		$fileObj = new ilObjFile();
		$fileObj->setType("file");
		$fileObj->setTitle($_FILES["Fobject"]["name"]["file"]);
		$fileObj->setDescription("");
		$fileObj->setFileName($_FILES["Fobject"]["name"]["file"]);
		$fileObj->setFileType($_FILES["Fobject"]["type"]["file"]);
		$fileObj->setFileSize($_FILES["Fobject"]["size"]["file"]);
		$fileObj->setMode("filelist");
		$fileObj->create();
		// upload file to filesystem
		$fileObj->createDirectory();
		$fileObj->raiseUploadError(false);
		$fileObj->getUploadFile($_FILES["Fobject"]["tmp_name"]["file"],
			$_FILES["Fobject"]["name"]["file"]);

		$_SESSION["il_text_lang_".$_GET["ref_id"]] = $_POST["flst_language"];

//echo "::".is_object($this->dom).":";
		$this->content_obj = new ilPCFileList($this->dom);
		$this->content_obj->create($this->pg_obj, $this->hier_id, $this->pc_id);
		$this->content_obj->setListTitle(ilUtil::stripSlashes($_POST["flst_title"]), $_POST["flst_language"]);
		$this->content_obj->appendItem($fileObj->getId(), $fileObj->getFileName(),
			$fileObj->getFileType());
		$this->updated = $this->pg_obj->update();
		if ($this->updated === true)
		{
			$this->ctrl->returnToParent($this, "jump".$this->hier_id);
		}
		else
		{
			$this->insert();
		}
	}

	/**
	* edit properties form
	*/
	function edit()
	{
		// add paragraph edit template
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.file_list_edit.html", "Services/COPage");
		$this->tpl->setVariable("TXT_ACTION", $this->lng->txt("cont_edit_file_list_properties"));
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));

		$this->displayValidationError();

		// select fields for number of columns
		$this->tpl->setVariable("TXT_TITLE", $this->lng->txt("title"));
		$this->tpl->setVariable("INPUT_TITLE", "flst_title");

		// todo: this doesnt work if title contains " quotes
		// ... addslashes doesnt work
		$this->tpl->setVariable("VALUE_TITLE", $this->content_obj->getListTitle());

		// language
		$this->tpl->setVariable("TXT_LANGUAGE", $this->lng->txt("language"));
		require_once("Services/MetaData/classes/class.ilMDLanguageItem.php");
		$lang = ilMDLanguageItem::_getLanguages();
		$select_lang = ilUtil::formSelect ($this->content_obj->getLanguage(),"flst_language",$lang,false,true);
		$this->tpl->setVariable("SELECT_LANGUAGE", $select_lang);


//		$this->tpl->parseCurrentBlock();

		// operations
		$this->tpl->setCurrentBlock("commands");
		$this->tpl->setVariable("BTN_NAME", "saveProperties");
		$this->tpl->setVariable("BTN_TEXT", $this->lng->txt("save"));
		$this->tpl->setVariable("BTN_CANCEL", "cancelUpdate");
		$this->tpl->setVariable("TXT_CANCEL", $this->lng->txt("cancel"));
		$this->tpl->parseCurrentBlock();

	}


	/**
	* save table properties in db and return to page edit screen
	*/
	function saveProperties()
	{
		$this->content_obj->setListTitle(ilUtil::stripSlashes($_POST["flst_title"]),
			$_POST["flst_language"]);
		$this->updated = $this->pg_obj->update();
		if ($this->updated === true)
		{
			$this->ctrl->returnToParent($this, "jump".$this->hier_id);
		}
		else
		{
			$this->pg_obj->addHierIDs();
			$this->edit();
		}
	}
}
?>
