<?php
/*
        +-----------------------------------------------------------------------------+
        | ILIAS open source                                                           |
        +-----------------------------------------------------------------------------+
        | Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
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
* Administration, Side-Block presentation of calendar categories
* 
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
*
* @ilCtrl_Calls ilCalendarCategoryGUI: ilCalendarAppointmentGUI
*
* @ingroup ServicesCalendar
*/

class ilCalendarCategoryGUI
{
	protected $user_id;
	protected $tpl;
	protected $ctrl;
	protected $lng;

	/**
	 * Constructor
	 *
	 * @access public
	 * @param int user id
	 * @return
	 */
	public function __construct($a_user_id,$seed)
	{
		global $lng,$ilCtrl;
		
		$this->user_id = $a_user_id;
		$this->seed = $seed;
		$this->lng = $lng;
		$this->lng->loadLanguageModule('dateplaner');
		$this->ctrl = $ilCtrl;
	}
	
	/**
	 * Execute command
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function executeCommand()
	{
		global $ilUser, $ilSetting,$tpl;

		$next_class = $this->ctrl->getNextClass($this);
		$this->ctrl->saveParameter($this,'category_id');
		switch($next_class)
		{
			case 'ilcalendarappointmentgui':
				$this->ctrl->setReturn($this,'edit');
				
				include_once('./Services/Calendar/classes/class.ilCalendarAppointmentGUI.php');
				$app = new ilCalendarAppointmentGUI($this->seed,(int) $_GET['app_id']);
				$this->ctrl->forwardCommand($app);
				break;
			
			default:
				$cmd = $this->ctrl->getCmd("show");
				$this->$cmd();
				break;
		}
		return true;
	}
	
	/**
	 * cancel
	 *
	 * @access protected
	 * @return
	 */
	protected function cancel()
	{
		$this->ctrl->returnToParent($this);
	}
	
	/**
	 * add new calendar
	 *
	 * @access protected
	 * @return
	 */
	protected function add()
	{
		global $tpl;
		
		$this->tpl = new ilTemplate('tpl.edit_category.html',true,true,'Services/Calendar');
		$this->initFormCategory('create');
		$this->tpl->setVariable('EDIT_CAT',$this->form->getHTML());
		$tpl->setContent($this->tpl->get());
	}
	
	/**
	 * save new calendar
	 *
	 * @access protected
	 */
	protected function save()
	{
		global $ilUser;

		include_once('./Services/Calendar/classes/class.ilCalendarCategory.php');
		$category = new ilCalendarCategory(0);
		$category->setTitle(ilUtil::stripSlashes($_POST['title']));
		$category->setColor('#'.ilUtil::stripSlashes($_POST['color']));
		
		if(isset($_POST['type']))
		{
			$category->setType((int) $_POST['type']);
			$category->setObjId(0);
		}
		else
		{
			$category->setType(ilCalendarCategory::TYPE_USR);
			$category->setObjId($ilUser->getId());
		}
		
		if(!$category->validate())
		{
			ilUtil::sendInfo($this->lng->txt('fill_out_all_required_fields'));
			$this->add();
			return false;
		}
		$category->add();
		
		ilUtil::sendInfo($this->lng->txt('settings_saved'),true);
		$this->ctrl->returnToParent($this);
		
	}
	
	/**
	 * edit category
	 *
	 * @access protected
	 * @return
	 */
	protected function edit()
	{
		global $tpl;

		if(!$_GET['category_id'])
		{
			ilUtil::sendInfo($this->lng->txt('select_one'),true);
			$this->ctrl->returnToParent($this);
		}
		
		$this->tpl = new ilTemplate('tpl.edit_category.html',true,true,'Services/Calendar');
		$this->initFormCategory('edit');
		$this->tpl->setVariable('EDIT_CAT',$this->form->getHTML());
		
		$this->tpl->setVariable('CAT_APPOINTMENTS',$this->showAssignedAppointments());
		
		include_once('./Services/YUI/classes/class.ilYuiUtil.php');
		ilYuiUtil::initButtonControl();
		
		$tpl->setContent($this->tpl->get());
	}
	
	/**
	 * update
	 *
	 * @access protected
	 * @return
	 */
	protected function update()
	{
		if(!$_GET['category_id'])
		{
			ilUtil::sendInfo($this->lng->txt('select_one'),true);
			$this->ctrl->returnToParent($this);
		}
		
		include_once('./Services/Calendar/classes/class.ilCalendarCategory.php');
		$category = new ilCalendarCategory((int) $_GET['category_id']);
		$category->setTitle(ilUtil::stripSlashes($_POST['title']));
		$category->setColor('#'.ilUtil::stripSlashes($_POST['color']));
		$category->update();
		
		ilUtil::sendInfo($this->lng->txt('settings_saved'),true);
		$this->ctrl->returnToParent($this);
	
	}
	
	/**
	 * confirm delete
	 *
	 * @access protected
	 * @return
	 */
	protected function confirmDelete()
	{
		global $tpl;
		
		if(!$_GET['category_id'])
		{
			ilUtil::sendInfo($this->lng->txt('select_one'),true);
			$this->ctrl->returnToParent($this);
		}
		
		include_once('./Services/Calendar/classes/class.ilCalendarCategory.php');
		$category = new ilCalendarCategory((int) $_GET['category_id']);
		
		include_once('./Services/Utilities/classes/class.ilConfirmationGUI.php');
		$confirmation_gui = new ilConfirmationGUI();
		
		$this->ctrl->setParameter($this,'category_id',(int) $_GET['category_id']);
		$confirmation_gui->setFormAction($this->ctrl->getFormAction($this));
		$confirmation_gui->setHeaderText($this->lng->txt('cal_del_cal_sure'));
		$confirmation_gui->setConfirm($this->lng->txt('delete'),'delete');
		$confirmation_gui->setCancel($this->lng->txt('cancel'),'cancel');
		$confirmation_gui->addItem('category_id',(int) $_GET['category_id'],$category->getTitle());
		
		$tpl->setContent($confirmation_gui->getHTML());
	}
	
	/**
	 * Delete
	 *
	 * @access protected
	 * @param
	 * @return
	 */
	protected function delete()
	{
		if(!$_GET['category_id'])
		{
			ilUtil::sendInfo($this->lng->txt('select_one'),true);
			$this->ctrl->returnToParent($this);
		}
		include_once('./Services/Calendar/classes/class.ilCalendarCategory.php');
		$category = new ilCalendarCategory((int) $_GET['category_id']);
		$category->delete();
		
		ilUtil::sendInfo($this->lng->txt('cal_cal_deleted'));
		$this->ctrl->returnToParent($this);
	}
	
	
	
	/**
	 * save selection of categories
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function saveSelection()
	{
		global $ilUser;
		
		include_once('./Services/Calendar/classes/class.ilCalendarCategories.php');
	
		$selection = $_POST['cat_ids'] ? $_POST['cat_ids'] : array();
		$hidden = array();
		
		$cats = ilCalendarCategories::_getInstance($ilUser->getId());
		foreach($cats->getCategories() as $category_id)
		{
			if(!in_array($category_id,$selection))
			{
				$hidden[] = $category_id;
			}
		}
		include_once('./Services/Calendar/classes/class.ilCalendarHidden.php');
		$hidden_categories = ilCalendarHidden::_getInstanceByUserId($this->user_id);
		$hidden_categories->hideSelected($hidden);
		$hidden_categories->save();
		
		ilUtil::sendInfo($this->lng->txt('settings_saved'),true);
		$this->ctrl->returnToParent($this);	
	}
	
	/**
	 * 
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function showCategories()
	{
		$this->ctrl->returnToParent($this);
	}
	
	
	/**
	 * init edit/create category form 
	 *
	 * @access protected
	 * @return
	 */
	protected function initFormCategory($a_mode)
	{
		global $rbacsystem;
		
		include_once('./Services/Form/classes/class.ilPropertyFormGUI.php');
		include_once('./Services/Calendar/classes/class.ilCalendarCategory.php');
		
		include_once('./Services/Calendar/classes/class.ilCalendarCategories.php');
		$cat_info = ilCalendarCategories::_getInstance()->getCategoryInfo((int) $_GET['category_id']);
		
		$editable = false;
		switch($cat_info['type'])
		{
			case ilCalendarCategory::TYPE_USR:
				$editable = true;
				break;
			
			case ilCalendarCategory::TYPE_GLOBAL:
				$editable = $rbacsystem->checkAccess('edit_event',ilCalendarSettings::_getInstance()->getCalendarSettingsId());
				break;
				
			case ilCalendarCategory::TYPE_OBJ:
				$editable = false;
				break;
		}
		
		$this->form = new ilPropertyFormGUI();
		switch($a_mode)
		{
			case 'edit':
				$category = new ilCalendarCategory((int) $_GET['category_id']);	
				$this->form->setTitle($this->lng->txt('cal_edit_category'));
				$this->ctrl->saveParameter($this,array('seed','category_id'));
				$this->form->setFormAction($this->ctrl->getFormAction($this));
				if($editable)
				{
					$this->form->addCommandButton('update',$this->lng->txt('save'));
					$this->form->addCommandButton('confirmDelete',$this->lng->txt('delete'));
					$this->form->addCommandButton('cancel',$this->lng->txt('cancel'));
				}
				break;				
			case 'create':
				$editable = true;
				$category = new ilCalendarCategory(0);	
				$this->ctrl->saveParameter($this,array('category_id'));
				$this->form->setFormAction($this->ctrl->getFormAction($this));
				$this->form->setTitle($this->lng->txt('cal_add_category'));
				$this->form->addCommandButton('save',$this->lng->txt('save'));
				$this->form->addCommandButton('cancel',$this->lng->txt('cancel'));
				break;
		}
		
		// Calendar name
		$title = new ilTextInputGUI($this->lng->txt('cal_calendar_name'),'title');
		if($a_mode == 'edit')
		{
			$title->setDisabled(!$editable);
		}
		$title->setRequired(true);
		$title->setMaxLength(64);
		$title->setSize(32);
		$title->setValue($category->getTitle());
		$this->form->addItem($title);
		
		
		include_once('./Services/Calendar/classes/class.ilCalendarSettings.php');
		if($a_mode == 'create' and $rbacsystem->checkAccess('edit_event',ilCalendarSettings::_getInstance()->getCalendarSettingsId()))
		{
			$type = new ilRadioGroupInputGUI($this->lng->txt('cal_cal_type'),'type');
			$type->setValue($category->getType());
			$type->setRequired(true);
			
				$opt = new ilRadioOption($this->lng->txt('cal_type_personal'),ilCalendarCategory::TYPE_USR);
				$type->addOption($opt);
				
				$opt = new ilRadioOption($this->lng->txt('cal_type_system'),ilCalendarCategory::TYPE_GLOBAL);
				$type->addOption($opt);
				
			$this->form->addItem($type);
		}
		
		
		$color = new ilColorPickerInputGUI($this->lng->txt('cal_calendar_color'),'color');
		$color->setValue($category->getColor());
		if(!$editable)
		{
			$color->setDisabled(true);
		}
		$color->setRequired(true);
		$this->form->addItem($color);
		
		
		
	}

	/**
	 * show assigned aapointments
	 *
	 * @access protected
	 * @return
	 */
	protected function showAssignedAppointments()
	{
		include_once('./Services/Calendar/classes/class.ilCalendarCategoryTableGUI.php');
		include_once('./Services/Calendar/classes/class.ilCalendarCategoryAssignments.php');
		include_once('./Services/Calendar/classes/class.ilCalendarAppointmentsTableGUI.php');
		
		$table_gui = new ilCalendarAppointmentsTableGUI($this,(int) $_GET['category_id']);
		$table_gui->setTitle($this->lng->txt('cal_assigned_appointments'));
		$table_gui->setAppointments(ilCalendarCategoryAssignments::_getAssignedAppointments((int) $_GET['category_id']));
		
		return $table_gui->getHTML();
	}
	
	/**
	 * ask delete appointments
	 *
	 * @access protected
	 * @return
	 */
	protected function askDeleteAppointments()
	{
		global $tpl;
		
		if(!count($_POST['appointments']))
		{
			ilUtil::sendInfo($this->lng->txt('select_one'));
			$this->edit();
			return true;
		}

		include_once('./Services/Utilities/classes/class.ilConfirmationGUI.php');
		$confirmation_gui = new ilConfirmationGUI();
		
		$this->ctrl->setParameter($this,'category_id',(int) $_GET['category_id']);
		$confirmation_gui->setFormAction($this->ctrl->getFormAction($this));
		$confirmation_gui->setHeaderText($this->lng->txt('cal_del_app_sure'));
		$confirmation_gui->setConfirm($this->lng->txt('delete'),'deleteAppointments');
		$confirmation_gui->setCancel($this->lng->txt('cancel'),'edit');
		
		include_once('./Services/Calendar/classes/class.ilCalendarEntry.php');
		foreach($_POST['appointments'] as $app_id)
		{
			$app = new ilCalendarEntry($app_id);			
			$confirmation_gui->addItem('appointments[]',(int) $app_id,$app->getTitle());
		}
		
		$tpl->setContent($confirmation_gui->getHTML());
	}
	
	/**
	 * delete appointments
	 *
	 * @access protected
	 * @return
	 */
	protected function deleteAppointments()
	{
		if(!count($_POST['appointments']))
		{
			ilUtil::sendInfo($this->lng->txt('select_one'));
			$this->edit();
			return true;
		}
		include_once('./Services/Calendar/classes/class.ilCalendarEntry.php');
		foreach($_POST['appointments'] as $app_id)
		{
			$app = new ilCalendarEntry($app_id);
			$app->delete();		

			include_once('./Services/Calendar/classes/class.ilCalendarCategoryAssignments.php');
			ilCalendarCategoryAssignments::_deleteByAppointmentId($app_id);
		}
		
		ilUtil::sendInfo($this->lng->txt('settings_saved'));
		$this->edit();
		return true;
		
	}

	public function getHTML()
	{
		include_once('./Services/Calendar/classes/class.ilCalendarCategoryTableGUI.php');
		
		$table_gui = new ilCalendarCategoryTableGUI($this);
		$table_gui->setTitle($this->lng->txt('cal_table_categories'));
		$table_gui->addMultiCommand('saveSelection',$this->lng->txt('show'));
		$table_gui->addCommandButton('add',$this->lng->txt('add'));
		$table_gui->addHeaderCommand($this->ctrl->getLinkTarget($this,'add'),
			$this->lng->txt('new'));
		$table_gui->parse();
		
		return $table_gui->getHTML();
	}
}
?>