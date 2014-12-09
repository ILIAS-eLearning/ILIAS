<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("./Modules/Wiki/classes/class.ilPCAMDPageList.php");
require_once("./Services/COPage/classes/class.ilPageContentGUI.php");

/**
* Class ilPCAMDPageListGUI
*
* Handles user commands on advanced md page list
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* @version $I$
*
* @ingroup ModulesWiki
*/
class ilPCAMDPageListGUI extends ilPageContentGUI
{
	/**
	* Constructor
	* @access	public
	*/
	function ilPCAMDPageListGUI($a_pg_obj, $a_content_obj, $a_hier_id, $a_pc_id = "")
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
		
		$this->displayValidationError();

		if(!$a_form)
		{
			$a_form = $this->initForm(true);
		}
		$tpl->setContent($a_form->getHTML());		 		
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
			$form->setTitle($this->lng->txt("cont_insert_amd_page_list"));
		}
		else
		{
			$form->setTitle($this->lng->txt("cont_update_amd_page_list"));
		}
		$form->setDescription($this->lng->txt("wiki_page_list_form_info"));
				
		include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDRecordGUI.php');
		$this->record_gui = new ilAdvancedMDRecordGUI(ilAdvancedMDRecordGUI::MODE_SEARCH,'wiki',$this->getPage()->getWikiId(),'wpg',$this->getPage()->getId());
		$this->record_gui->setPropertyForm($form);
		$this->record_gui->setSelectedOnly(true);
		
		if (!$a_insert)
		{
			$this->record_gui->setSearchFormValues($this->content_obj->getFieldValues());
		}
		
		$this->record_gui->parse();
		
		if ($a_insert)
		{		
			$form->addCommandButton("create_amd_page_list", $this->lng->txt("select"));
			$form->addCommandButton("cancelCreate", $this->lng->txt("cancel"));
		}
		else
		{					
			$form->addCommandButton("update", $this->lng->txt("select"));
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
			$elements = $this->record_gui->importSearchForm();
			if(is_array($elements))
			{				
				$this->content_obj = new ilPCAMDPageList($this->getPage());
				$this->content_obj->create($this->pg_obj, $this->hier_id, $this->pc_id);
				$this->content_obj->setData($elements);
				$this->updated = $this->pg_obj->update();
				if ($this->updated === true)
				{
					$this->ctrl->returnToParent($this, "jump".$this->hier_id);
				}
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
		$valid = $form->checkInput();
		if($valid)
		{
			$elements = $this->record_gui->importSearchForm();
		}													
		if(is_array($elements))
		{	
			$this->content_obj->setData($elements);
			$this->updated = $this->pg_obj->update();
			if ($this->updated === true)
			{
				$this->ctrl->returnToParent($this, "jump".$this->hier_id);
			}
		}		

		$this->pg_obj->addHierIDs();
		// $form->setValuesByPost();
		return $this->edit($form);			
	}
}

?>