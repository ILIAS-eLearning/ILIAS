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

require_once("./content/classes/class.ilLMObjectGUI.php");
require_once("./content/classes/class.ilLMPageObject.php");
require_once("./content/classes/Pages/class.ilPageObjectGUI.php");
require_once("./content/classes/Pages/class.ilPageEditorGUI.php");

/**
* Class ilLMPageObjectGUI
*
* User Interface for Learning Module Page Objects Editing
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @package content
*/
class ilLMPageObjectGUI extends ilLMObjectGUI
{
	var $obj;

	/**
	* Constructor
	* @access	public
	*/
	function ilLMPageObjectGUI(&$a_content_obj)
	{
		global $ilias, $tpl, $lng;

		parent::ilLMObjectGUI($a_content_obj);

	}

	function setLMPageObject(&$a_pg_obj)
	{
		$this->obj =& $a_pg_obj;
		$this->obj->setLMId($this->content_object->getId());
	}

	/*
	* display content of page (edit view)
	*/
	function view()
	{
		$page_object_gui =& new ilPageObjectGUI($this->obj->getPageObject());

		$page_object_gui->setPresentationTitle($this->obj->getPresentationTitle($this->content_object->getPageHeader()));
		$page_object_gui->setTargetScript("lm_edit.php?ref_id=".
			$this->content_object->getRefId()."&obj_id=".$this->obj->getId()."&mode=page_edit");
		$page_object_gui->setTemplateTargetVar("ADM_CONTENT");
		$page_object_gui->view();

	}

	function showPageEditor()
	{
		require_once ("content/classes/Pages/class.ilPageObjectGUI.php");
		$page_gui =& new ilPageObjectGUI($this->obj->getPageObject());
		$page_gui->setTargetScript("lm_edit.php?ref_id=".
			$this->content_object->getRefId()."&obj_id=".$this->obj->getId()."&mode=page_edit");
		$page_gui->setReturnLocation("lm_edit.php?ref_id=".
			$this->content_object->getRefId()."&obj_id=".$this->obj->getId()."&cmd=view");
		$page_gui->showPageEditor();
	}

	function showLinkHelp()
	{
		require_once ("content/classes/Pages/class.ilPageObjectGUI.php");
		$page_gui =& new ilPageObjectGUI($this->obj->getPageObject());
		$page_gui->setTargetScript("lm_edit.php?ref_id=".
			$this->content_object->getRefId()."&obj_id=".$this->obj->getId()."&mode=page_edit");
		$page_gui->setReturnLocation("lm_edit.php?ref_id=".
			$this->content_object->getRefId()."&obj_id=".$this->obj->getId()."&cmd=view");
		$page_gui->showLinkHelp($this->tpl);
	}

	/*
	* preview
	*/
	function preview()
	{
		$page_object_gui =& new ilPageObjectGUI($this->obj->getPageObject());

		$page_object_gui->setPresentationTitle($this->obj->getPresentationTitle($this->content_object->getPageHeader()));
		$page_object_gui->setTargetScript("lm_edit.php?ref_id=".
			$this->content_object->getRefId()."&obj_id=".$this->obj->getId()."&mode=page_edit");
		$page_object_gui->setTemplateTargetVar("ADM_CONTENT");
		$page_object_gui->preview();
	}

	/**
	* output a cell in object list
	*/
	function add_cell($val, $link = "")
	{
		if(!empty($link))
		{
			$this->tpl->setCurrentBlock("begin_link");
			$this->tpl->setVariable("LINK_TARGET", $link);
			$this->tpl->parseCurrentBlock();
			$this->tpl->touchBlock("end_link");
		}

		$this->tpl->setCurrentBlock("text");
		$this->tpl->setVariable("TEXT_CONTENT", $val);
		$this->tpl->parseCurrentBlock();
		$this->tpl->setCurrentBlock("table_cell");
		$this->tpl->parseCurrentBlock();
	}


	function save()
	{
		// create new object
		$meta_gui =& new ilMetaDataGUI();
		$meta_data =& $meta_gui->create();
		$this->obj =& new ilLMPageObject($this->content_object);
		$this->obj->assignMetaData($meta_data);
		$this->obj->setType($_GET["new_type"]);
		$this->obj->setLMId($this->content_object->getId());
		$this->obj->create();

		// obj_id is empty, if page is created from "all pages" screen
		// -> a free page is created (not in the tree)
		if (empty($_GET["obj_id"]))
		{
			header("location: lm_edit.php?cmd=pages&ref_id=".$this->content_object->getRefId());
		}
		else
		{
			$this->putInTree();
			header("location: lm_edit.php?cmd=view&ref_id=".$this->content_object->getRefId()."&obj_id=".
				$_GET["obj_id"]);
		}
	}

	/*
	function displayValidationError($a_error)
	{
		if(is_array($a_error))
		{
			$error_str = "<b>Validation Error(s):</b><br>";
			foreach ($a_error as $error)
			{
				$err_mess = implode($error, " - ");
				if (!is_int(strpos($err_mess, ":0:")))
				{
					$error_str .= htmlentities($err_mess)."<br />";
				}
			}
			$this->tpl->setVariable("MESSAGE", $error_str);
		}
	}*/
}
?>
