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

require_once("classes/class.ilObjectGUI.php");
require_once("classes/class.ilMetaDataGUI.php");
require_once("content/classes/class.ilObjGlossary.php");
require_once("content/classes/class.ilGlossaryTermGUI.php");
require_once("content/classes/class.ilGlossaryDefinition.php");
require_once("content/classes/class.ilTermDefinitionEditorGUI.php");
require_once("content/classes/Pages/class.ilPCParagraph.php");

/**
* Class ilGlossaryGUI
*
* GUI class for ilGlossary
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @package content
*/
class ilObjGlossaryGUI extends ilObjectGUI
{
	var $admin_tabs;

	/**
	* Constructor
	* @access	public
	*/
	function ilObjGlossaryGUI($a_data,$a_id = 0,$a_call_by_reference = true, $a_prepare_output = true)
	{
		$this->type = "glo";
		parent::ilObjectGUI($a_data, $a_id, $a_call_by_reference, false);
		if (defined("ILIAS_MODULE"))
		{
			$this->setTabTargetScript("glossary_edit.php");
		}
		if ($a_prepare_output)
		{
			$this->prepareOutput();
		}
	}

	/**
	* form for new content object creation
	*/
	function createObject()
	{
		parent::createObject();
		return;

		include_once "classes/class.ilMetaDataGUI.php";
		$meta_gui =& new ilMetaDataGUI();
		//$meta_gui->setObject($this->object);

		$meta_gui->setTargetFrame("save", $this->getTargetFrame("save"));

		$new_type = $_POST["new_type"] ? $_POST["new_type"] : $_GET["new_type"];

		$meta_gui->edit("ADM_CONTENT", "adm_content",
			$this->getFormAction("save","adm_object.php?ref_id=".$_GET["ref_id"]."&new_type=".$new_type."&cmd=save"));
	}

	/**
	* save new content object to db
	*/
	function saveObject()
	{
		global $rbacadmin, $rbacsystem;

		// always call parent method first to create an object_data entry & a reference
		//$newObj = parent::saveObject();
		// TODO: fix MetaDataGUI implementation to make it compatible to use parent call
		if (!$rbacsystem->checkAccess("create", $_GET["ref_id"], $_GET["new_type"]))
		{
			$this->ilias->raiseError($this->lng->txt("no_create_permission"), $this->ilias->error_obj->MESSAGE);
		}
		else
		{
			// create and insert object in objecttree
			include_once("content/classes/class.ilObjGlossary.php");
			$newObj = new ilObjGlossary();
			$newObj->setType($this->type);
			$newObj->setTitle($_POST["Fobject"]["title"]);
			$newObj->setDescription($_POST["Fobject"]["desc"]);
			$newObj->create();
			$newObj->createReference();
			$newObj->putInTree($_GET["ref_id"]);
			$newObj->setPermissions($_GET["ref_id"]);
			$newObj->notify("new",$_GET["ref_id"],$_GET["parent_non_rbac_id"],$_GET["ref_id"],$newObj->getRefId());

			//$roles = $newObj->initDefaultRoles();

			// assign author role to creator of forum object
			//$rbacadmin->assignUser($roles[0], $newObj->getOwner(), "n");
			//ilObjUser::updateActiveRoles($newObj->getOwner());

			unset($newObj);

			// always send a message
			sendInfo($this->lng->txt("glo_added"),true);
			header("Location:".$this->getReturnLocation("save","adm_object.php?".$this->link_params));
			exit();
		}
	}

	function editMetaObject()
	{
		include_once "classes/class.ilMetaDataGUI.php";
		$meta_gui =& new ilMetaDataGUI();
		$meta_gui->setObject($this->object);
		$meta_gui->edit("ADM_CONTENT", "adm_content",
			"adm_object.php?ref_id=".$_GET["ref_id"]."&cmd=saveMeta");
	}

	function saveMetaObject()
	{
		include_once "classes/class.ilMetaDataGUI.php";
		$meta_gui =& new ilMetaDataGUI();
		$meta_gui->setObject($this->object);
		$meta_gui->save($_POST["meta_section"]);
		header("Location: adm_object.php?ref_id=".$_GET["ref_id"]);
		exit;
	}



	function viewObject()
	{
		global $rbacsystem;

		if (!$rbacsystem->checkAccess("visible,read",$this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}

		// edit button
		$this->tpl->addBlockfile("BUTTONS", "buttons", "tpl.buttons.html");

		if (!defined("ILIAS_MODULE"))
		{
			$this->tpl->setCurrentBlock("btn_cell");
			$this->tpl->setVariable("BTN_LINK","content/glossary_edit.php?cmd=listTerms&ref_id=".$this->object->getRefID());
			$this->tpl->setVariable("BTN_TARGET"," target=\"bottom\" ");
			$this->tpl->setVariable("BTN_TXT",$this->lng->txt("edit"));
			$this->tpl->parseCurrentBlock();
		}

		parent::viewObject();
	}

	function executeCommand()
	{
echo "1";
		if($_GET["def"] > 0)
		{
echo "2";
			$def_edit =& new ilTermDefinitionEditorGUI();
			$def_edit->executeCommand();
		}
		else
		{
			$cmd = $_GET["cmd"];
			if ($cmd != "listDefinitions" && $cmd != "editTerm")
			{
				$this->prepareOutput();
			}
			if($cmd == "")
			{
				$cmd = "listTerms";
			}

			if ($cmd == "post")
			{
				$cmd = key($_POST["cmd"]);
			}
			$this->$cmd();
		}
		$this->tpl->show();
	}

	function listTerms()
	{
		$this->lng->loadLanguageModule("meta");
		include_once "./classes/class.ilTableGUI.php";

		// load template for table
		$this->tpl->addBlockfile("ADM_CONTENT", "adm_content", "tpl.table.html");

		// load template for table content data
		$this->tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.term_tbl_row.html", true);

		$num = 0;

		$obj_str = ($this->call_by_reference) ? "" : "&obj_id=".$this->obj_id;
		$this->tpl->setVariable("FORMACTION", "glossary_edit.php?ref_id=".$this->ref_id."$obj_str&cmd=post");

		// create table
		$tbl = new ilTableGUI();

		// title & header columns
		$tbl->setTitle($this->lng->txt("cont_terms"));
		//$tbl->setHelp("tbl_help.php","icon_help.gif",$this->lng->txt("help"));

		$tbl->setHeaderNames(array("", $this->lng->txt("cont_term"),
			 $this->lng->txt("language"), $this->lng->txt("cont_definitions")));

		$cols = array("", "term", "language", "definitions", "id");
		$header_params = array("ref_id" => $this->ref_id, "cmd" => "listTerms");
		$tbl->setHeaderVars($cols, $header_params);
		$tbl->setColumnWidth(array("1%","24%","15%","60%"));

		// control
		$tbl->setOrderColumn($_GET["sort_by"]);
		$tbl->setOrderDirection($_GET["sort_order"]);
		$tbl->setLimit($_GET["limit"]);
		$tbl->setOffset($_GET["offset"]);
		$tbl->setMaxCount($this->maxcount);

		$this->tpl->setVariable("COLUMN_COUNTS", 4);
		$this->setActions(array("confirmTermDeletion" => "delete", "addDefinition" => "cont_add_definition"));
		$this->setSubObjects(array("term" => array()));
		$this->showActions(true);

		// footer
		$tbl->setFooter("tblfooter",$this->lng->txt("previous"),$this->lng->txt("next"));
		//$tbl->disable("footer");

		$term_list = $this->object->getTermList();
		$tbl->setMaxCount(count($term_list));

		// sorting array
		include_once "./include/inc.sort.php";
		//$term_list = sortArray($term_list, $_GET["sort_by"], $_GET["sort_order"]);
		$term_list = array_slice($term_list, $_GET["offset"], $_GET["limit"]);

		// render table
		$tbl->render();

		if (count($term_list) > 0)
		{
			$i=1;
			foreach($term_list as $key => $term)
			{
				$css_row = ilUtil::switchColor($i++,"tblrow1","tblrow2");
				$defs = ilGlossaryDefinition::getDefinitionList($term["id"]);
				for($j=0; $j<count($defs); $j++)
				{
					$def = $defs[$j];
					$this->tpl->setCurrentBlock("definition");
					$this->tpl->setVariable("DEF_LINK",
						"glossary_edit.php?ref_id=".$_GET["ref_id"]."&cmd=view&def=".$def["id"]);
					$this->tpl->setVariable("DEF_TEXT", $this->lng->txt("cont_definition")." ".($j + 1));
					$short_str = ilPCParagraph::xml2output($def["short_text"]);
					$short_str = str_replace("<", "&lt;", $short_str);
					$short_str = str_replace(">", "&gt;", $short_str);
					$this->tpl->setVariable("DEF_SHORT", $short_str);
					$this->tpl->parseCurrentBlock();
				}

				$this->tpl->setVariable("CSS_ROW", $css_row);
				$this->tpl->setVariable("TEXT_TERM", $term["term"]);
				$this->tpl->setVariable("CHECKBOX_ID", $term["id"]);
				$this->tpl->setVariable("TARGET_TERM", "glossary_edit.php?ref_id=".
					$_GET["ref_id"]."&cmd=listDefinitions&term_id=".$term["id"]);
				$this->tpl->setVariable("TEXT_LANGUAGE", $this->lng->txt("meta_l_".$term["language"]));
				$this->tpl->setCurrentBlock("tbl_content");
				$this->tpl->parseCurrentBlock();
			}
		} //if is_array
		else
		{
			$this->tpl->setCurrentBlock("notfound");
			$this->tpl->setVariable("TXT_OBJECT_NOT_FOUND", $this->lng->txt("obj_not_found"));
			$this->tpl->setVariable("NUM_COLS", $num);
			$this->tpl->parseCurrentBlock();
		}
	}

	function listDefinitions()
	{
		require_once("content/classes/Pages/class.ilPageObjectGUI.php");

		$this->tpl->setCurrentBlock("ContentStyle");
		$this->tpl->setVariable("LOCATION_CONTENT_STYLESHEET",
			ilObjStyleSheet::getContentStylePath(0));
		$this->tpl->parseCurrentBlock();

		//$this->admin_tabs[] = array("cont_definitions","listDefinitions");
		//$this->admin_tabs[] = array("meta_data","editTerm");
		$term =& new ilGlossaryTerm($_GET["term_id"]);

		// load template for table
		$this->tpl->addBlockfile("CONTENT", "def_list", "tpl.glossary_definition_list.html", true);
		$this->tpl->addBlockfile("STATUSLINE", "statusline", "tpl.statusline.html");
		$this->setLocator();
		$this->setAdminTabs("term_edit");
		$this->tpl->setVariable("TXT_HEADER",
			$this->lng->txt("cont_term").": ".$term->getTerm());

		$this->tpl->setVariable("FORMACTION", "glossary_edit.php?ref_id=".$_GET["ref_id"].
			"&cmd=post&term_id=".$_GET["term_id"]);
		$this->tpl->setVariable("TXT_ADD_DEFINITION",
			$this->lng->txt("cont_add_definition"));
		$this->tpl->setVariable("BTN_ADD", "addDefinition");

		$defs = ilGlossaryDefinition::getDefinitionList($_GET["term_id"]);

		$this->tpl->setVariable("TXT_TERM", $term->getTerm());

		for($j=0; $j<count($defs); $j++)
		{
			$def = $defs[$j];
			$page =& new ilPageObject("gdf", $def["id"]);
			$page_gui =& new ilPageObjectGUI($page);
			//$page_gui->setOutputMode("edit");
			//$page_gui->setPresentationTitle($this->term->getTerm());
			$page_gui->setTemplateOutput(false);
			$output = $page_gui->preview();

			if (count($defs) > 1)
			{
				$this->tpl->setCurrentBlock("definition_header");
						$this->tpl->setVariable("TXT_DEFINITION",
				$this->lng->txt("cont_definition")." ".($j+1));
				$this->tpl->parseCurrentBlock();
			}

			if ($j > 0)
			{
				$this->tpl->setCurrentBlock("up");
				$this->tpl->setVariable("TXT_UP", $this->lng->txt("up"));
				$this->tpl->setVariable("LINK_UP",
					"glossary_edit.php?ref_id=".$_GET["ref_id"]."&cmd=moveUp&def=".$def["id"]);
				$this->tpl->parseCurrentBlock();
			}

			if ($j+1 < count($defs))
			{
				$this->tpl->setCurrentBlock("down");
				$this->tpl->setVariable("TXT_DOWN", $this->lng->txt("down"));
				$this->tpl->setVariable("LINK_DOWN",
					"glossary_edit.php?ref_id=".$_GET["ref_id"]."&cmd=moveDown&def=".$def["id"]);
				$this->tpl->parseCurrentBlock();
			}

			$this->tpl->setCurrentBlock("definition");
			$this->tpl->setVariable("PAGE_CONTENT", $output);
			$this->tpl->setVariable("TXT_EDIT", $this->lng->txt("edit"));
			$this->tpl->setVariable("LINK_EDIT",
				"glossary_edit.php?ref_id=".$_GET["ref_id"]."&cmd=view&def=".$def["id"]);
			$this->tpl->setVariable("TXT_DELETE", $this->lng->txt("delete"));
			$this->tpl->setVariable("LINK_DELETE",
				"glossary_edit.php?ref_id=".$_GET["ref_id"]."&cmd=confirmDefinitionDeletion&def=".$def["id"]);
			$this->tpl->parseCurrentBlock();
		}
		//$this->tpl->setCurrentBlock("def_list");
		//$this->tpl->parseCurrentBlock();

	}


	function confirmTermDeletion()
	{
		if (!isset($_POST["id"]))
		{
			$this->ilias->raiseError($this->lng->txt("no_checkbox"),$this->ilias->error_obj->MESSAGE);
		}

		// save values to
		$_SESSION["term_delete"] = $_POST["id"];

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.obj_confirm.html");

		sendInfo($this->lng->txt("info_delete_sure"));
		$this->tpl->setVariable("FORMACTION", "glossary_edit.php?ref_id=".$this->ref_id."$obj_str&cmd=post");

		// output table header
		$cols = array("cont_term");
		foreach ($cols as $key)
		{
			$this->tpl->setCurrentBlock("table_header");
			$this->tpl->setVariable("TEXT",$this->lng->txt($key));
			$this->tpl->parseCurrentBlock();
		}

		foreach($_POST["id"] as $id)
		{
			$term = new ilGlossaryTerm($id);

			// output title
			$this->tpl->setCurrentBlock("table_cell");
			$this->tpl->setVariable("TEXT_CONTENT", $term->getTerm());
			$this->tpl->parseCurrentBlock();

			// output table row
			$this->tpl->setCurrentBlock("table_row");
			$this->tpl->setVariable("CSS_ROW",ilUtil::switchColor(++$counter,"tblrow1","tblrow2"));
			$this->tpl->parseCurrentBlock();
		}

		// cancel and confirm button
		$buttons = array( "cancelTermDeletion"  => $this->lng->txt("cancel"),
			"deleteTerms"  => $this->lng->txt("confirm"));
		foreach($buttons as $name => $value)
		{
			$this->tpl->setCurrentBlock("operation_btn");
			$this->tpl->setVariable("BTN_NAME",$name);
			$this->tpl->setVariable("BTN_VALUE",$value);
			$this->tpl->parseCurrentBlock();
		}

	}

	/**
	* cancel deletion of object
	*
	* @access	public
	*/
	function cancelTermDeletion()
	{
		session_unregister("term_delete");

		sendInfo($this->lng->txt("msg_cancel"),true);

		header("Location: glossary_edit.php?ref_id=".$this->ref_id."&cmd=listTerms");
		exit();
	}

	function deleteTerms()
	{
		foreach($_SESSION["term_delete"] as $id)
		{
			$term = new ilGlossaryTerm($id);
			$term->delete();
		}
		session_unregister("term_delete");

		header("Location: glossary_edit.php?ref_id=".$this->ref_id."&cmd=listTerms");
		exit();
	}

	/**
	* set Locator
	*
	* @param	object	tree object
	* @param	integer	reference id
	* @access	public
	*/
	function setLocator($a_tree = "", $a_id = "")
	{
		if(!defined("ILIAS_MODULE"))
		{
			parent::setLocator($a_tree, $a_id);
		}
		else
		{
			if(is_object($this->object))
			{

				$this->tpl->addBlockFile("LOCATOR", "locator", "tpl.locator.html");

				if (!empty($_GET["term_id"]))
				{
					$this->tpl->touchBlock("locator_separator");
				}

				$this->tpl->setCurrentBlock("locator_item");
				$this->tpl->setVariable("ITEM", $this->object->getTitle());
				// TODO: SCRIPT NAME HAS TO BE VARIABLE!!!
				$this->tpl->setVariable("LINK_ITEM", "glossary_edit.php?ref_id=".$_GET["ref_id"]."&cmd=listTerms");
				$this->tpl->parseCurrentBlock();

				if (!empty($_GET["term_id"]))
				{
					$term =& new ilGlossaryTerm($_GET["term_id"]);
					$this->tpl->setCurrentBlock("locator_item");
					$this->tpl->setVariable("ITEM", $term->getTerm());
					$this->tpl->setVariable("LINK_ITEM", "glossary_edit.php?ref_id=".$_GET["ref_id"].
						"&cmd=listDefinitions&term_id=".$term->getId());
					$this->tpl->parseCurrentBlock();
				}

				//$this->tpl->touchBlock("locator_separator");

				$this->tpl->setCurrentBlock("locator");
				$this->tpl->setVariable("TXT_LOCATOR",$debug.$this->lng->txt("locator"));
				$this->tpl->parseCurrentBlock();
			}
		}

	}

	function loadAdmTemplate()
	{
		$this->tpl->addBlockFile("CONTENT", "content", "tpl.adm_content.html");

		// catch feedback message
		sendInfo();
	}

	function addDefinition()
	{
		if (empty($_GET["term_id"]))
		{
			if (count($_POST["id"]) < 1)
			{
				$this->ilias->raiseError($this->lng->txt("cont_select_term"),$this->ilias->error_obj->MESSAGE);
			}

			if (count($_POST["id"]) > 1)
			{
				$this->ilias->raiseError($this->lng->txt("cont_select_max_one_term"),$this->ilias->error_obj->MESSAGE);
			}
		}

		$term_id = empty($_GET["term_id"])
			? $_POST["id"][0]
			: $_GET["term_id"];

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.obj_edit.html");
		$this->tpl->setVariable("FORMACTION",
			"glossary_edit.php?ref_id=".$_GET["ref_id"]."&term_id=".$term_id."&cmd=saveDefinition");
		$this->tpl->setVariable("TXT_HEADER", $this->lng->txt("gdf_new"));
		$this->tpl->setVariable("TXT_CANCEL", $this->lng->txt("cancel"));
		$this->tpl->setVariable("TXT_SUBMIT", $this->lng->txt("gdf_add"));
		$this->tpl->setVariable("TXT_TITLE", $this->lng->txt("title"));
		$this->tpl->setVariable("TXT_DESC", $this->lng->txt("description"));
		$this->tpl->setVariable("CMD_SUBMIT", "saveDefinition");
		//$this->tpl->setVariable("TARGET", $this->getTargetFrame("save"));
		$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));
		$this->tpl->parseCurrentBlock();

		/*
		include_once "classes/class.ilMetaDataGUI.php";
		$meta_gui =& new ilMetaDataGUI();
		$meta_gui->setTargetFrame("save",$this->getTargetFrame("save"));
		$meta_gui->edit("ADM_CONTENT", "adm_content",
			"glossary_edit.php?ref_id=".$_GET["ref_id"]."&term_id=".$term_id."&cmd=saveDefinition");
		*/

	}

	function saveDefinition()
	{
		//$meta_gui =& new ilMetaDataGUI();
		//$meta_data =& $meta_gui->create();
		$def =& new ilGlossaryDefinition();
		$def->setTermId($_GET["term_id"]);
		$def->setTitle($_POST["Fobject"]["title"]);#"content object ".$newObj->getId());		// set by meta_gui->save
		$def->setDescription($_POST["Fobject"]["desc"]);	// set by meta_gui->save
		$def->create();
		header("Location: glossary_edit.php?cmd=view&ref_id=".$this->object->getRefId().
			"&def=".$def->getId());
	}

	function editMeta()
	{
		include_once("classes/class.ilMetaDataGUI.php");
		$meta_gui =& new ilMetaDataGUI();
		$meta_gui->setObject($this->object);
		$meta_gui->edit("ADM_CONTENT", "adm_content", "glossary_edit.php?ref_id=".
			$this->object->getRefId()."&cmd=saveMeta");
	}

	function saveMeta()
	{
		include_once("classes/class.ilMetaDataGUI.php");
		$meta_gui =& new ilMetaDataGUI();
		$meta_gui->setObject($this->object);
		$meta_gui->save();
		header("Location: glossary_edit.php?cmd=view&ref_id=".$this->object->getRefId());
	}

	function perm()
	{
		$this->setFormAction("addRole", "glossary_edit.php?ref_id=".$this->object->getRefId()."&cmd=addRole");
		$this->setFormAction("permSave", "glossary_edit.php?ref_id=".$this->object->getRefId()."&cmd=permSave");
		$this->permObject();
	}

	function permSave()
	{
		$this->setReturnLocation("permSave", "glossary_edit.php?ref_id=".$this->object->getRefId()."&cmd=perm");
		$this->permSaveObject();
	}

	function addRole()
	{
		$this->setReturnLocation("addRole", "glossary_edit.php?ref_id=".$this->object->getRefId()."&cmd=perm");
		$this->addRoleObject();
	}

	function owner()
	{
		$this->ownerObject();
	}

	function view()
	{
		$this->viewObject();
	}

	/**
	* create new (subobject) in glossary
	*/
	function create()
	{
		switch($_POST["new_type"])
		{
			case "term":
				$term_gui =& new ilGlossaryTermGUI();
				$term_gui->create();
				break;
		}
	}

	function saveTerm()
	{
		$term_gui =& new ilGlossaryTermGUI();
		$term_gui->setGlossary($this->object);
		$term_gui->save();

		sendinfo($this->lng->txt("cont_added_term"),true);

		header("Location: glossary_edit.php?ref_id=".$_GET["ref_id"]."&cmd=listTerms");
		exit();

	}

	function editTerm()
	{
		$this->loadAdmTemplate();
		$this->setAdminTabs("term_edit");
		$this->setLocator();
		$term_gui =& new ilGlossaryTermGUI($_GET["term_id"]);
		$term_gui->editTerm();
	}

	function updateTerm()
	{
		$term_gui =& new ilGlossaryTermGUI($_GET["term_id"]);
		$term_gui->update();

		sendinfo($this->lng->txt("msg_obj_modified"),true);

		header("Location: glossary_edit.php?ref_id=".$_GET["ref_id"]."&term_id=".
			$_GET["term_id"]."&cmd=listDefinitions");
		exit();

	}

	function setAdminTabs($mode = "std")
	{
		if ($mode == "std")
		{
			parent::setAdminTabs();
		}
		else
		{

			switch($mode)
			{
				case "term_edit":
					$tabs[] = array("cont_definitions","listDefinitions");
					$tabs[] = array("properties","editTerm");
					break;
			}

			$this->tpl->addBlockFile("TABS", "tabs", "tpl.tabs.html");

			foreach ($tabs as $row)
			{
				$i++;

				if ($row[1] == $_GET["cmd"])
				{
					$tabtype = "tabactive";
					$tab = $tabtype;
				}
				else
				{
					$tabtype = "tabinactive";
					$tab = "tab";
				}

				$this->tpl->setCurrentBlock("tab");
				$this->tpl->setVariable("TAB_TYPE", $tabtype);
				$this->tpl->setVariable("TAB_TYPE2", $tab);
				$this->tpl->setVariable("TAB_LINK", "glossary_edit.php?ref_id=".$_GET["ref_id"]."&def=".
					$_GET["def"]."&term_id=".$_GET["term_id"]."&cmd=".$row[1]);
				$this->tpl->setVariable("TAB_TEXT", $this->lng->txt($row[0]));
				$this->tpl->parseCurrentBlock();
			}
		}
	}

}

?>
