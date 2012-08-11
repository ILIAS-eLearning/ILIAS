<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("./Services/COPage/classes/class.ilPCResources.php");
require_once("./Services/COPage/classes/class.ilPageContentGUI.php");

/**
* Class ilPCResourcesGUI
*
* User Interface for Resources Component Editing
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ServicesCOPage
*/
class ilPCResourcesGUI extends ilPageContentGUI
{

	/**
	* Constructor
	* @access	public
	*/
	function ilPCResourcesGUI(&$a_pg_obj, &$a_content_obj, $a_hier_id, $a_pc_id = "")
	{
		global $tree;
		
		$this->rep_tree = $tree;
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
	* Insert new resources component form.
	*/
	function insert()
	{
		$this->edit(true);
	}

	/**
	* Edit resources form.
	*/
	function edit($a_insert = false)
	{
		global $ilCtrl, $tpl, $lng, $objDefinition;
		
		$this->displayValidationError();
		
		// edit form
		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setFormAction($ilCtrl->getFormAction($this));
		if ($a_insert)
		{
			$form->setTitle($this->lng->txt("cont_insert_resources"));
		}
		else
		{
			$form->setTitle($this->lng->txt("cont_update_resources"));
		}
		
		// count number of existing objects per type
		$ref_id = (int) $_GET["ref_id"];
		$childs = $this->rep_tree->getChilds($ref_id);
		$type_counts = array();
		foreach ($childs as $c)
		{
			$type_counts[$c["type"]] += 1;
		}
		
		// type selection
		$type_prop = new ilSelectInputGUI($this->lng->txt("cont_type"),
			"type");
		$obj_id = ilObject::_lookupObjId($_GET["ref_id"]);
		$obj_type = ilObject::_lookupType($obj_id);
		$sub_objs = $objDefinition->getGroupedRepositoryObjectTypes($obj_type);
		$types = array();
		foreach($sub_objs as $k => $so)
		{
			if ($k != "itgr")
			{
				$types[$k] = $this->lng->txt("objs_".$k)." (".(int) $type_counts[$k].")";
			}
		}
		$type_prop->setOptions($types);
		$selected = ($a_insert)
			? ""
			: $this->content_obj->getResourceListType();
		$type_prop->setValue($selected);
		$form->addItem($type_prop);
		
		// save/cancel buttons
		if ($a_insert)
		{
			$form->addCommandButton("create_resources", $lng->txt("save"));
			$form->addCommandButton("cancelCreate", $lng->txt("cancel"));
		}
		else
		{
			$form->addCommandButton("update_resources", $lng->txt("save"));
			$form->addCommandButton("cancelUpdate", $lng->txt("cancel"));
		}
		$html = $form->getHTML();
		$tpl->setContent($html);
		return $ret;

	}


	/**
	* Create new Resources Component.
	*/
	function create()
	{
		$this->content_obj = new ilPCResources($this->dom);
		$this->content_obj->create($this->pg_obj, $this->hier_id, $this->pc_id);
		$this->content_obj->setResourceListType($_POST["type"]);
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
	* Update Resources Component.
	*/
	function update()
	{
		$this->content_obj->setResourceListType($_POST["type"]);
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
	
	/**
	 * Insert resources
	 *
	 * @param
	 * @return
	 */
	static function insertResourcesIntoPageContent($a_content)
	{
		global $objDefinition, $tree, $lng;
		
		$ref_id = (int) $_GET["ref_id"];
		$obj_id = (int) ilObject::_lookupObjId($ref_id);
		$obj_type = ilObject::_lookupType($obj_id);
		$childs = $tree->getChilds($ref_id);
		$childs_by_type = array();
		foreach ($childs as $child)
		{
			$childs_by_type[$child["type"]][] = $child;
		}
		$type_grps =
			$objDefinition->getGroupedRepositoryObjectTypes($obj_type);

		foreach ($type_grps as $type => $v)
		{
			if (is_int(strpos($a_content, "[list-".$type."]")))
			{
				if (is_array($childs_by_type[$type]))
				{
					// render block
					$tpl = new ilTemplate("tpl.resource_block.html", true, true, "Services/COPage");
					foreach ($childs_by_type[$type] as $child)
					{
						$tpl->setCurrentBlock("row");
						$tpl->setVariable("IMG", ilUtil::img(ilObject::_getIcon($child["obj_id"], "small")));
						$tpl->setVariable("TITLE", $child["title"]);
						$tpl->parseCurrentBlock();
					}
					$tpl->setVariable("HEADER", $lng->txt("objs_".$type));
					$a_content = str_replace("[list-".$type."]", $tpl->get(), $a_content);
				}
				else
				{
					$a_content = str_replace("[list-".$type."]", "", $a_content);
				}
			}
		}

		return $a_content;
	}
	
}
?>
