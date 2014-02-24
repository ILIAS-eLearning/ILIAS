<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("./Modules/Portfolio/classes/class.ilPCMyCourses.php");
require_once("./Services/COPage/classes/class.ilPageContentGUI.php");

/**
* Class ilPCMyCoursesGUI
*
* Handles user commands on my courses data
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* @version $I$
*
* @ingroup ModulesPortfolio
*/
class ilPCMyCoursesGUI extends ilPageContentGUI
{
	/**
	* Constructor
	* @access	public
	*/
	function ilPCMyCoursesGUI($a_pg_obj, $a_content_obj, $a_hier_id, $a_pc_id = "")
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
	 * Insert courses form
	 *
	 * @param ilPropertyFormGUI $a_form
	 */
	function insert(ilPropertyFormGUI $a_form = null)
	{
		global $tpl;
		
		// #12816 - no form needed yet
		$this->create();

		/*
		$this->displayValidationError();

		if(!$a_form)
		{
			$a_form = $this->initForm(true);
		}
		$tpl->setContent($a_form->getHTML());		 
		*/
	}

	/**
	 * Edit courses form
	 *
	 * @param ilPropertyFormGUI $a_form
	 */
	function edit(ilPropertyFormGUI $a_form = null)
	{
		global $tpl;

		$this->displayValidationError();

		if(!$a_form)
		{
			$a_form = $this->initForm();
		}
		$tpl->setContent($a_form->getHTML());
	}

	/**
	 * Init courses form
	 *
	 * @param bool $a_insert
	 * @return ilPropertyFormGUI
	 */
	protected function initForm($a_insert = false)
	{
		global $ilCtrl;

		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setFormAction($ilCtrl->getFormAction($this));
		if ($a_insert)
		{
			$form->setTitle($this->lng->txt("cont_insert_my_courses"));
		}
		else
		{
			$form->setTitle($this->lng->txt("cont_update_my_courses"));
		}
		
		$warn = new ilNonEditableValueGUI("");
		$warn->setValue($this->lng->txt("cont_my_courses_no_settings"));
		$form->addItem($warn);
	
		if ($a_insert)
		{		
			$form->addCommandButton("create_my_courses", $this->lng->txt("select"));
			$form->addCommandButton("cancelCreate", $this->lng->txt("cancel"));
		}
		else
		{		
			// $form->addCommandButton("update", $this->lng->txt("select"));
			$form->addCommandButton("cancelUpdate", $this->lng->txt("cancel"));
		}

		return $form;
	}		

	/**
	* Create new courses
	*/
	function create()
	{		
		$form = $this->initForm(true);
		if($form->checkInput())
		{											
			$this->content_obj = new ilPCMyCourses($this->getPage());
			$this->content_obj->create($this->pg_obj, $this->hier_id, $this->pc_id);
			$this->content_obj->setData();
			$this->updated = $this->pg_obj->update();
			if ($this->updated === true)
			{
				$this->ctrl->returnToParent($this, "jump".$this->hier_id);
			}
		}

		$form->setValuesByPost();
		return $this->insert($form);		
	}

	/**
	* Update courses
	*/
	function update()
	{				
		$form = $this->initForm();
		if($form->checkInput())
		{			
			$this->content_obj->setData();
			$this->updated = $this->pg_obj->update();
			if ($this->updated === true)
			{
				$this->ctrl->returnToParent($this, "jump".$this->hier_id);
			}
		}

		$this->pg_obj->addHierIDs();
		$form->setValuesByPost();
		return $this->edit($form);			
	}
}

?>