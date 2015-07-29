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

/**
* Class ilObjiLincClassroomGUI
*
* @author Sascha Hofmann <saschahofmann@gmx.de> 
* @version $Id$
* 
* @ilCtrl_Calls ilObjiLincClassroomGUI:
*
* @extends ilObjectGUI
*/

include_once "./Services/Object/classes/class.ilObjectGUI.php";
include_once "./Modules/ILinc/classes/class.ilObjiLincClassroom.php";

class ilObjiLincClassroomGUI extends ilObjectGUI
{
	private $form_gui = null;
	
	/**
	* Constructor
	* @access public
	* 2 last parameters actually not used
	*/
	function ilObjiLincClassroomGUI($a_icla_id,$a_icrs_id,$a_call_by_reference = false,$a_prepare_output = false)
	{
		global $ilCtrl,$lng,$ilias,$objDefinition,$tpl,$tree,$ilErr;
		
		$this->type = "icla";
		$this->id = $a_icla_id;
		$this->parent = $a_icrs_id;
		$this->ilias =& $ilias;
		$this->objDefinition =& $objDefinition;
		$this->tpl =& $tpl;
		$this->html = "";
		$this->ctrl =& $ilCtrl;
		$this->lng =& $lng;
		$this->tree =& $tree;
		$this->ilErr =& $ilErr;

		//$this->ctrl->saveParameter($this,'parent');
		$this->lng->loadLanguageModule('ilinc');
		
		$this->formaction = array();
		$this->return_location = array();
		$this->target_frame = array();
		//$this->tab_target_script = "adm_object.php";
		$this->actions = "";
		$this->sub_objects = "";

		//prepare output
		if (false)
		{
			$this->prepareOutput();
		}
	}

	public function create()
	{
		$this->prepareOutput();
		
		$this->initSettingsForm('create');
		$this->getDefaultValues();
		return $this->tpl->setVariable('ADM_CONTENT', $this->form_gui->getHtml());
	}
	
	protected function getDefaultValues()
	{
		$data = array(
			'title' => '',
			'desc' => ''
		);
		$this->form_gui->setValuesByArray( $data );
	}

	/**
	* save object
	* @access	public
	*/
	public function save()
	{
		$this->prepareOutput();
		
		$this->initSettingsForm('create');
		if($this->form_gui->checkInput())
		{
			$this->ctrl->redirectByClass('ilobjilinccoursegui');
		}
		else
		{
			$this->form_gui->setValuesByPost();
			return $this->tpl->setVariable('ADM_CONTENT', $this->form_gui->getHtml());
		}
	}
	
	public function editClassroom()
	{
		$this->initSettingsForm('edit');		
		$this->getObjectValues();
		return $this->tpl->setVariable('ADM_CONTENT', $this->form_gui->getHtml());	
	}
	
	protected function getObjectValues()
	{
		$data = array(
			'title' => $this->object->getTitle(),
			'desc' => $this->object->getDescription()
		);	 	
		$this->form_gui->setValuesByArray( $data );
	}
	
	protected function initSettingsForm($a_mode = 'create')
	{
		include_once 'Services/Form/classes/class.ilPropertyFormGUI.php';
		
		$this->form_gui = new ilPropertyFormGUI();
		$this->form_gui->setTableWidth('600');
		if($a_mode == 'create')
		{
			$this->form_gui->setTitle($this->lng->txt('icla_add'));
		}		
		else
		{
			$this->form_gui->setTitle($this->lng->txt('icla_edit'));
		}		
		$this->form_gui->setTitleIcon(ilUtil::getTypeIconPath('icla', 0));
		
		// Title
		$text_input = new ilTextInputGUI($this->lng->txt('title'), 'title');
		$text_input->setRequired(true);
		$this->form_gui->addItem($text_input);
		
		// Description
		$text_area = new ilTextAreaInputGUI($this->lng->txt('desc'), 'desc');
		$this->form_gui->addItem($text_area);
		
		// save and cancel commands
		if($a_mode == 'create')
		{
			$this->ctrl->setParameter($this, 'mode', 'create');
			$this->ctrl->setParameter($this, 'new_type', 'icla');
			
			$this->form_gui->addCommandButton('save', $this->lng->txt('icla_add'));
			$this->form_gui->addCommandButton('cancel', $this->lng->txt('cancel'));
			$this->form_gui->setFormAction($this->ctrl->getFormAction($this, 'save'));
		}
		else
		{
			$this->ctrl->setParameter($this, 'class_id', $this->object->id);
			
			$this->form_gui->addCommandButton('updateClassroom', $this->lng->txt('save'));
			$this->form_gui->addCommandButton('cancel', $this->lng->txt('cancel'));
			$this->form_gui->setFormAction($this->ctrl->getFormAction($this, 'updateClassroom'));
		}
	}
	
	/**
	* get tabs
	* @access	public
	* @param	object	tabs gui object
	*/
	function getTabs(&$tabs_gui)
	{
		// tabs are defined manually here. The autogeneration via objects.xml will be deprecated in future
		// for usage examples see ilObjGroupGUI or ilObjSystemFolderGUI
	}
	
	/**
	* updates class room on ilinc server
	*
	* @access	public
	*/
	public function updateClassroom()
	{
		$this->initSettingsForm('edit');
		if($this->form_gui->checkInput())
		{			
			$this->object->setTitle( $this->form_gui->getInput('title' ));
			$this->object->setDescription( $this->form_gui->getInput('desc') );
			$this->object->update();
			$this->ctrl->redirectByClass('ilobjilinccoursegui');
		}
		else
		{
			$this->form_gui->setValuesByPost();
			return $this->tpl->setVariable('ADM_CONTENT', $this->form_gui->getHtml());
		}		
	}
	
	function &executeCommand()
	{
		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();

		switch($next_class)
		{
			default:
				$this->$cmd();
				break;
		}

		return true;
	}
	
	/**
	* cancel is called when an operation is canceled, method links back
	* @access	public
	*/
	function cancel()
	{
		ilUtil::sendInfo($this->lng->txt("msg_cancel"),true);

		$this->ctrl->redirectByClass("ilobjilinccoursegui");
	}
}