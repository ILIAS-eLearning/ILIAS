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

include_once "./classes/class.ilObjectGUI.php";
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
		
		$this->assignObject();
	}
	
	function assignObject()
	{
		$this->object = new ilObjiLincClassroom($this->id, $this->parent);
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
			'desc' => '',
			'instructoruserid' => 0,
			'alwaysopen' => 1
		);		
		
		$icrs_obj_id = ilObject::_lookupObjectId( $this->parent );
		include_once 'Modules/ILinc/classes/class.ilObjiLincCourse.php';
		$akclassvalues = ilObjiLincCourse::_getAKClassValues( $icrs_obj_id );
		$data['akclassvalue1'] = $akclassvalues[0];
		$data['akclassvalue2'] = $akclassvalues[1];
		
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
			$_POST['Fobject']['title'] = $this->form_gui->getInput('title');
			$_POST['Fobject']['desc'] = $this->form_gui->getInput('desc');	
			$_POST['Fobject']['instructoruserid'] = $this->form_gui->getInput('instructoruserid');
			$_POST['Fobject']['alwaysopen'] = $this->form_gui->getInput('alwaysopen');
			
			// Akclassvalues 
			if($this->ilias->getSetting('ilinc_akclassvalues_active'))
			{
				$icrs_obj_id = ilObject::_lookupObjectId( $this->parent );
				include_once 'Modules/ILinc/classes/class.ilObjiLincCourse.php';
				$akclassvalues = ilObjiLincCourse::_getAKClassValues( $icrs_obj_id );
				
				$_POST['Fobject']['akclassvalue1'] = $akclassvalues[0];
				$_POST['Fobject']['akclassvalue2'] = $akclassvalues[1];
			}
			
			$ilinc_course_id = ilObjiLincClassroom::_lookupiCourseId( $this->parent );
	
			$this->object->ilincAPI->addClass($ilinc_course_id, $_POST['Fobject']);
			$response = $this->object->ilincAPI->sendRequest('addClass');			
			if($response->isError())
			{
				$this->ilErr->raiseError($response->getErrorMsg(), $this->ilErr->MESSAGE);
			}
	
			// Always send a message
			ilUtil::sendInfo($response->getResultMsg(), true);
			
			$this->ctrl->redirectByClass('ilobjilinccoursegui');
		}
		else
		{
			if($this->ilias->getSetting('ilinc_akclassvalues_active'))
			{
				$icrs_obj_id = ilObject::_lookupObjectId( $this->parent );
				include_once 'Modules/ILinc/classes/class.ilObjiLincCourse.php';
				$akclassvalues = ilObjiLincCourse::_getAKClassValues( $icrs_obj_id );
				
				$_POST['akclassvalue1'] = $akclassvalues[0];
				$_POST['akclassvalue2'] = $akclassvalues[1];
			}
			
			$this->form_gui->setValuesByPost();
			return $this->tpl->setVariable('ADM_CONTENT', $this->form_gui->getHtml());
		}
	}
	
	function joinClassroom()
	{
		// join class
		$url = $this->object->joinClass($this->ilias->account,$_GET['class_id']);

		if (!$url)
		{
			$this->ilias->raiseError($this->object->getErrorMsg(),$this->ilias->error_obj->FATAL);
		}

		ilUtil::redirect(trim($url));
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
			'desc' => $this->object->getDescription(),
			'alwaysopen' => $this->object->getStatus(),
			'instructoruserid' => $this->object->getDocentId()
		);	 	
		
		$icrs_obj_id = ilObject::_lookupObjectId( $this->parent );
		include_once 'Modules/ILinc/classes/class.ilObjiLincCourse.php';
		$akclassvalues = ilObjiLincCourse::_getAKClassValues( $icrs_obj_id );
		$data['akclassvalue1'] = $akclassvalues[0];
		$data['akclassvalue2'] = $akclassvalues[1];
		
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
		
		// Docentselection
		$sel = new ilSelectInputGUI($this->lng->txt(ILINC_MEMBER_DOCENT), 'instructoruserid');
		$docentlist = $this->object->getDocentList();
		$docent_options = array();		
		$docent_options[0] = $this->lng->txt('please_choose');
		foreach((array)$docentlist as $id => $data)
		{
			$docent_options[$id] = $data['fullname'];
		}
		$sel->setOptions($docent_options);
		$this->form_gui->addItem($sel);		
		
		// Open
		$rg = new ilRadioGroupInputGUI($this->lng->txt('access'), 'alwaysopen');
		$rg->setValue(0);
			$ro = new ilRadioOption($this->lng->txt('ilinc_classroom_open'), 1);
		$rg->addOption($ro);
			$ro = new ilRadioOption($this->lng->txt('ilinc_classroom_closed'), 0);
		$rg->addOption($ro);				
		$this->form_gui->addItem($rg);	
		
		// Display akclassvalues 
		if($this->ilias->getSetting('ilinc_akclassvalues_active'))
		{			
			$text_input = new ilTextInputGUI($this->lng->txt('akclassvalue1'), 'akclassvalue1');
			$text_input->setDisabled(true);
			$this->form_gui->addItem($text_input);
			
			$text_input = new ilTextInputGUI($this->lng->txt('akclassvalue2'), 'akclassvalue2');
			$text_input->setDisabled(true);
			$this->form_gui->addItem($text_input);			
		}	
		
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
	* display deletion confirmation screen
	* only for referenced objects. For user,role & rolt overwrite this function in the appropriate
	* Object folders classes (ilObjUserFolderGUI,ilObjRoleFolderGUI)
	*
	* @access	public
 	*/
	function removeClassroom($a_error = false)
	{
		unset($this->data);
		$this->data["cols"] = array("type", "title", "last_change");

		$this->data["data"][$_GET['class_id']] = array(
											"type"        => $this->object->getType(),
											"title"       => $this->object->getTitle()."#separator#".$this->object->getDescription()." ",	// workaround for empty desc
											"last_update" => "n/a"
										);

		$this->data["buttons"] = array( "confirmedDeleteClassroom"  => $this->lng->txt("confirm"),
								  "cancelDeleteClassroom"  => $this->lng->txt("cancel"));

		$this->getTemplateFile("confirm");

		if(!$a_error)
		{
			ilUtil::sendInfo($this->lng->txt("info_delete_sure"));
		}

		$obj_str = "&class_id=".$this->object->id;
		$this->tpl->setVariable("FORMACTION", $this->getFormAction("delete",$this->ctrl->getFormAction($this).$obj_str));

		// BEGIN TABLE HEADER
		foreach ($this->data["cols"] as $key)
		{
			$this->tpl->setCurrentBlock("table_header");
			$this->tpl->setVariable("TEXT",$this->lng->txt($key));
			$this->tpl->parseCurrentBlock();
		}
		// END TABLE HEADER

		// BEGIN TABLE DATA
		$counter = 0;

		foreach ($this->data["data"] as $key => $value)
		{
			// BEGIN TABLE CELL
			foreach ($value as $key => $cell_data)
			{
				$this->tpl->setCurrentBlock("table_cell");

				// CREATE TEXT STRING
				if ($key == "type")
				{
					$this->tpl->setVariable("TEXT_CONTENT",ilUtil::getImageTagByType($cell_data,$this->tpl->tplPath));
				}
				elseif ($key == "title")
				{
					$name_field = explode("#separator#",$cell_data);

					$this->tpl->setVariable("TEXT_CONTENT", "<b>".$name_field[0]."</b>");
						
					$this->tpl->setCurrentBlock("subtitle");
					$this->tpl->setVariable("DESC", $name_field[1]);
					$this->tpl->parseCurrentBlock();
					$this->tpl->setCurrentBlock("table_cell");
				}
				else
				{
					$this->tpl->setVariable("TEXT_CONTENT",$cell_data);
				}

				$this->tpl->parseCurrentBlock();
			}

			$this->tpl->setCurrentBlock("table_row");
			$this->tpl->setVariable("CSS_ROW",ilUtil::switchColor(++$counter,"tblrow1","tblrow2"));
			$this->tpl->parseCurrentBlock();
			// END TABLE CELL
		}
		// END TABLE DATA

		// BEGIN OPERATION_BTN
		foreach ($this->data["buttons"] as $name => $value)
		{
			$this->tpl->setCurrentBlock("operation_btn");
			$this->tpl->setVariable("IMG_ARROW",ilUtil::getImagePath("arrow_downright.gif"));
			$this->tpl->setVariable("BTN_NAME",$name);
			$this->tpl->setVariable("BTN_VALUE",$value);
			$this->tpl->parseCurrentBlock();
		}
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
			$this->object->setDocentId( $this->form_gui->getInput('instructoruserid') );
			$this->object->setStatus( $this->form_gui->getInput('alwaysopen') );
			
			if(!$this->object->update())
			{
				$this->ilErr->raiseError($this->object->getErrorMsg(), $this->ilErr->MESSAGE);
			}

			ilUtil::sendInfo($this->getResultMsg(), true);
			
			$this->ctrl->redirectByClass('ilobjilinccoursegui');
		}
		else
		{
			if($this->ilias->getSetting('ilinc_akclassvalues_active'))
			{
				$icrs_obj_id = ilObject::_lookupObjectId( $this->parent );
				include_once 'Modules/ILinc/classes/class.ilObjiLincCourse.php';
				$akclassvalues = ilObjiLincCourse::_getAKClassValues( $icrs_obj_id );
				
				$_POST['akclassvalue1'] = $akclassvalues[0];
				$_POST['akclassvalue2'] = $akclassvalues[1];
			}
			
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
	* cancel deletion of classroom object
	*
	* @access	public
	*/
	function cancelDeleteClassroom()
	{
		session_unregister("saved_post");
		
		ilUtil::sendInfo($this->lng->txt("msg_cancel"),true);

		$this->ctrl->redirectByClass("ilobjilinccoursegui");
	}
	
	/**
	* @access	public
	*/
	function confirmedDeleteClassroom()
	{
		if (!$this->object->delete())
		{
			$msg = $this->object->getErrorMsg();
		}
		else
		{
			$msg = $this->lng->txt('icla_deleted');
		}
		
		// Feedback
		ilUtil::sendInfo($msg,true);
		
		$this->ctrl->redirectByClass("ilobjilinccoursegui");
	}
	
	function getResultMsg()
	{
		return $this->object->result_msg;
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
} // END class.ilObjiLincClassroomGUI
?>
