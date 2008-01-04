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

require_once("./Services/COPage/classes/class.ilPCListItem.php");
require_once("./Services/COPage/classes/class.ilPageContentGUI.php");

/**
* Class ilPCFileItemGUI
*
* Handles user commands on items of file lists
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $I$
*
* @ingroup ServicesCOPage
*/
class ilPCFileItemGUI extends ilPageContentGUI
{

	/**
	* Constructor
	* @access	public
	*/
	function ilPCFileItemGUI(&$a_pg_obj, &$a_content_obj, $a_hier_id)
	{
		parent::ilPageContentGUI($a_pg_obj, $a_content_obj, $a_hier_id);
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
	* insert new file item
	*/
	function newFileItem()
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
		$fileObj->raiseUploadError(false);
		// upload file to filesystem
		$fileObj->createDirectory();
		$fileObj->getUploadFile($_FILES["Fobject"]["tmp_name"]["file"],
			$_FILES["Fobject"]["name"]["file"]);

		$this->file_object =& $fileObj;
	}


	/**
	* insert new list item after current one
	*/
	function newItemAfter()
	{
		// new file list form
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.file_item_edit.html", "Services/COPage");
		$this->tpl->setVariable("TXT_ACTION", $this->lng->txt("cont_insert_file_item"));
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));

		$this->displayValidationError();

		// file
		$this->tpl->setVariable("TXT_FILE", $this->lng->txt("file"));

		$this->tpl->parseCurrentBlock();

		// operations
		$this->tpl->setCurrentBlock("commands");
		$this->tpl->setVariable("BTN_NAME", "insertNewItemAfter");
		$this->tpl->setVariable("BTN_TEXT", $this->lng->txt("save"));
		$this->tpl->parseCurrentBlock();

	}

	/**
	* insert new file item after another item
	*/
	function insertNewItemAfter()
	{
		$this->newFileItem();
		$this->content_obj->newItemAfter($this->file_object->getId(),
			$this->file_object->getFileName(), $this->file_object->getFileType());
		$this->updated = $this->pg_obj->update();
		if ($this->updated === true)
		{
			$this->ctrl->returnToParent($this, "jump".$this->hier_id);
		}
		else
		{
			$this->newItemAfter();
		}
	}

	/**
	* insert new list item before current one
	*/
	function newItemBefore()
	{
		// new file list form
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.file_item_edit.html", "Services/COPage");
		$this->tpl->setVariable("TXT_ACTION", $this->lng->txt("cont_insert_file_item"));
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));

		$this->displayValidationError();

		// file
		$this->tpl->setVariable("TXT_FILE", $this->lng->txt("file"));

		$this->tpl->parseCurrentBlock();

		// operations
		$this->tpl->setCurrentBlock("commands");
		$this->tpl->setVariable("BTN_NAME", "insertNewItemBefore");
		$this->tpl->setVariable("BTN_TEXT", $this->lng->txt("save"));
		$this->tpl->parseCurrentBlock();

	}

	/**
	* insert new list item before current one
	*/
	function insertNewItemBefore()
	{
		$this->newFileItem();
		$this->content_obj->newItemBefore($this->file_object->getId(),
			$this->file_object->getFileName(), $this->file_object->getFileType());
		$this->updated = $this->pg_obj->update();
		if ($this->updated === true)
		{
			$this->ctrl->returnToParent($this, "jump".$this->hier_id);
		}
		else
		{
			$this->newItemBefore();
		}
	}

	/**
	* delete a list item
	*/
	function deleteItem()
	{
		$this->content_obj->deleteItem();
		$_SESSION["il_pg_error"] = $this->pg_obj->update();
		$this->ctrl->returnToParent($this, "jump".$this->hier_id);
	}

	/**
	* move list item down
	*/
	function moveItemDown()
	{
		$this->content_obj->moveItemDown();
		$_SESSION["il_pg_error"] = $this->pg_obj->update();
		$this->ctrl->returnToParent($this, "jump".$this->hier_id);
	}

	/**
	* move list item up
	*/
	function moveItemUp()
	{
		$this->content_obj->moveItemUp();
		$_SESSION["il_pg_error"] = $this->pg_obj->update();
		$this->ctrl->returnToParent($this, "jump".$this->hier_id);
	}


}
?>
