<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("./Services/COPage/classes/class.ilPCContentInclude.php");
require_once("./Services/COPage/classes/class.ilPageContentGUI.php");

/**
* Class ilPCContentIncludeGUI
*
* User Interface for Content Includes (Snippets) Editing
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ServicesCOPage
*/
class ilPCContentIncludeGUI extends ilPageContentGUI
{

	/**
	* Constructor
	* @access	public
	*/
	function ilPCContentIncludeGUI(&$a_pg_obj, &$a_content_obj, $a_hier_id, $a_pc_id = "")
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
		
		$html = $form->getHTML();
		$tpl->setContent($html);
		return $ret;

	}


	/**
	* Create new Content Include
	*/
	function create()
	{
		$this->content_obj = new ilPCContentInclude($this->dom);
		$this->content_obj->create($this->pg_obj, $this->hier_id, $this->pc_id);
		$this->content_obj->setContentId("");
		$this->content_obj->setContentType("");
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
	* Update Content Include
	*/
	function update()
	{
		$this->content_obj->setContentId("");
		$this->content_obj->setContentType("");
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
