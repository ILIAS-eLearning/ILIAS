<?php
/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("./Services/COPage/classes/class.ilPCQuestionOverview.php");
require_once("./Services/COPage/classes/class.ilPageContentGUI.php");

/**
 * Class ilPCQuestionOverviewGUI
 *
 * User Interface for question overview editing
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup ServicesCOPage
 */
class ilPCQuestionOverviewGUI extends ilPageContentGUI
{

	/**
	 * Constructor
	 */
	function ilPCQuestionOverviewGUI(&$a_pg_obj, &$a_content_obj, $a_hier_id, $a_pc_id = "")
	{
		parent::ilPageContentGUI($a_pg_obj, $a_content_obj, $a_hier_id, $a_pc_id);
	}


	/**
 	 * Execute command
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
	 * Insert new question overview
	 */
	function insert()
	{
		$this->edit(true);
	}

	/**
	 * Edit question overview form.
	 */
	function edit($a_insert = false)
	{
		global $ilCtrl, $tpl, $lng;
		
		$this->displayValidationError();
		
		// edit form
		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setFormAction($ilCtrl->getFormAction($this));
		if ($a_insert)
		{
			$form->setTitle($this->lng->txt("cont_ed_insert_qover"));
		}
		else
		{
			$form->setTitle($this->lng->txt("cont_edit_qover"));
		}
		
		// question overview
		$radg = new ilRadioGroupInputGUI($lng->txt("cont_type"), "type");
			$op1 = new ilRadioOption($lng->txt("cont_qover_short_message"), "Short",$lng->txt("cont_qover_short_message_info"));
			$radg->addOption($op1);
			$op1 = new ilRadioOption($lng->txt("cont_qover_list_wrong_q"), "ListWrongQuestions",$lng->txt("cont_qover_list_wrong_q_info"));
			$radg->addOption($op1);
		$form->addItem($radg);
		
		$selected = ($a_insert)
			? "Short"
			: $this->content_obj->getOverviewType();
		$radg->setValue($selected);
		
		// save/cancel buttons
		if ($a_insert)
		{
			$form->addCommandButton("create_qover", $lng->txt("save"));
			$form->addCommandButton("cancelCreate", $lng->txt("cancel"));
		}
		else
		{
			$form->addCommandButton("update", $lng->txt("save"));
			$form->addCommandButton("cancelUpdate", $lng->txt("cancel"));
		}
		$html = $form->getHTML();
		$tpl->setContent($html);
		return $ret;
	}

	/**
	 * Create new question overview
	 */
	function create()
	{
		$this->content_obj = new ilPCQuestionOverview($this->dom);
		$this->content_obj->create($this->pg_obj, $this->hier_id, $this->pc_id);
		$this->content_obj->setOverviewType($_POST["type"]);
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
	 * Update question overview
	 */
	function update()
	{
		$this->content_obj->setOverviewType($_POST["type"]);
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
