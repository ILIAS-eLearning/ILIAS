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
	const SEARCH_USER = 1;
	const SEARCH_ROLE = 2;
	
	protected $user_id;
	protected $tpl;
	protected $ctrl;
	protected $lng;
	
	protected $editable = false;
	protected $visible = false;

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
		
		if(isset($_POST['type']) and $_POST['type'] == ilCalendarCategory::TYPE_GLOBAL)
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

		$this->readPermissions();
		$this->checkVisible();

		$this->tpl = new ilTemplate('tpl.edit_category.html',true,true,'Services/Calendar');
		if($this->isEditable())
		{
			include_once('./Services/Calendar/classes/class.ilCalendarShared.php');
			$shared = new ilCalendarShared((int) $_GET['category_id']);
			
			if($shared->getShared() and $this->isEditable())
			{
				include_once('./Services/Calendar/classes/class.ilCalendarSharedListTableGUI.php');
				$table = new ilCalendarSharedListTableGUI($this,'edit');
				$table->setTitle($this->lng->txt('cal_shared_header'));
				$table->setCalendarId((int) $_GET['category_id']);
				$table->parse();
				$this->tpl->setVariable('SHARED_TABLE',$table->getHTML());
			}
			
			$this->initFormCategory('edit');
			$this->tpl->setVariable('EDIT_CAT',$this->form->getHTML());
		}
		
		{
			$category = new ilCalendarCategory((int) $_GET['category_id']);	
			
			
			// Non editable category 
			include_once("./Services/InfoScreen/classes/class.ilInfoScreenGUI.php");
			$info = new ilInfoScreenGUI($this);
			$info->setFormAction($this->ctrl->getFormAction($this));
	
			$info->addSection($this->lng->txt('cal_cal_details'));
	
			// Calendar Name
			$info->addProperty($this->lng->txt('cal_calendar_name'),$category->getTitle());
			switch($category->getType())
			{
				case ilCalendarCategory::TYPE_USR:
					$info->addProperty($this->lng->txt('cal_cal_type'),$this->lng->txt('cal_type_personal'));
					break;
					
				case ilCalendarCategory::TYPE_GLOBAL:
					$info->addProperty($this->lng->txt('cal_cal_type'),$this->lng->txt('cal_type_system'));
					break;
					
				case ilCalendarCategory::TYPE_OBJ:
					$info->addProperty($this->lng->txt('cal_cal_type'),$this->lng->txt('cal_type_'.$category->getObjType()));
					
					$info->addSection($this->lng->txt('additional_info'));
					$info->addProperty($this->lng->txt('perma_link'),$this->addReferenceLinks($category->getObjId()));
					break;
				
			}
			
	
			$this->tpl->setVariable('INFO_CAT',$info->getHTML());	
		}		
		
		
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
		$this->readPermissions();
		if(!$this->isEditable())
		{
			ilUtil::sendInfo($this->lng->txt('permission_denied'));
			$this->edit();
			return false;
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
		$this->readPermissions();
		if(!$this->isEditable())
		{
			ilUtil::sendInfo($this->lng->txt('permission_denied'));
			$this->edit();
			return false;
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
		
		$this->readPermissions();
		if(!$this->isEditable())
		{
			ilUtil::sendInfo($this->lng->txt('permission_denied'));
			$this->edit();
			return false;
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
		include_once('./Services/Calendar/classes/class.ilCalendarCategoryTableGUI.php');
		$table_gui = new ilCalendarCategoryTableGUI($this);
		$nav_parameter = $table_gui->getNavParameter();

		if($_POST[$nav_parameter] != "")
		{
			if($_POST[$nav_parameter."1"] != $_POST[$nav_parameter])
			{
				$nav_value = $_POST[$nav_parameter."1"];
			}
			elseif($_POST[$nav_parameter."2"] != $_POST[$nav_parameter])
			{
				$nav_value = $_POST[$nav_parameter."2"];
			}
		}
		else
		{
			$nav_value = $_GET[$nav_parameter];
		}

		$_SESSION[$nav_parameter] = $nav_value;

		$this->ctrl->returnToParent($this);
	}
	
	/**
	 * share calendar
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function shareSearch()
	{
		global $tpl;
		
		$this->readPermissions();
		if(!$this->isEditable())
		{
			ilUtil::sendInfo($this->lng->txt('permission_denied'));
			$this->edit();
			return false;
		}
		
		$_SESSION['cal_query'] = '';
		
		if(!$_GET['category_id'])
		{
			ilUtil::sendInfo($this->lng->txt('select_one'),true);
			$this->ctrl->returnToParent($this);
		}
		
		$this->ctrl->saveParameter($this,'category_id');
	
		$this->initFormSearch();
		$tpl->setContent($this->form->getHTML());
	}
	
	/**
	 * share perform search
	 *
	 * @access public
	 * @return
	 */
	public function sharePerformSearch()
	{
		$this->lng->loadLanguageModule('search');
		
		if(!$_GET['category_id'])
		{
			ilUtil::sendInfo($this->lng->txt('select_one'),true);
			$this->ctrl->returnToParent($this);
		}
		$this->ctrl->saveParameter($this,'category_id');
		
		
		if(!isset($_POST['query']))
		{
			$query = $_SESSION['cal_query'];
			$type = $_SESSION['cal_type'];
		}
		elseif($_POST['query'])
		{
			$query = $_SESSION['cal_query'] = $_POST['query'];
			$type = $_SESSION['cal_type'] = $_POST['query_type'];
		}
		
		if(!$query)
		{
			ilUtil::sendInfo($this->lng->txt('msg_no_search_string'));
			$this->shareSearch();
			return false;
		}
		

		include_once 'Services/Search/classes/class.ilQueryParser.php';
		include_once 'Services/Search/classes/class.ilObjectSearchFactory.php';
		include_once 'Services/Search/classes/class.ilSearchResult.php';
		
		$res_sum = new ilSearchResult();
		
		$query_parser = new ilQueryParser(ilUtil::stripSlashes($query));
		$query_parser->setCombination(QP_COMBINATION_OR);
		$query_parser->setMinWordLength(3);
		$query_parser->parse();
		

		switch($type)
		{
			case self::SEARCH_USER:
				$search = ilObjectSearchFactory::_getUserSearchInstance($query_parser);
				$search->enableActiveCheck(true);
				
				$search->setFields(array('login'));
				$res = $search->performSearch();
				$res_sum->mergeEntries($res);
				
				$search->setFields(array('firstname'));
				$res = $search->performSearch();
				$res_sum->mergeEntries($res);
		
				$search->setFields(array('lastname'));
				$res = $search->performSearch();
				$res_sum->mergeEntries($res);
				
				$res_sum->filter(ROOT_FOLDER_ID,QP_COMBINATION_OR);
				break;
				
			case self::SEARCH_ROLE:
				 
				include_once 'Services/Search/classes/Like/class.ilLikeObjectSearch.php';
				$search = new ilLikeObjectSearch($query_parser);
				$search->setFilter(array('role'));
				
				$res = $search->performSearch();
				$res_sum->mergeEntries($res);
				
				$res_sum->filter(ROOT_FOLDER_ID,QP_COMBINATION_OR);
				break;
		}				 
		
		if(!count($res_sum->getResults()))
		{
			ilUtil::sendInfo($this->lng->txt('search_no_match'));
			$this->shareSearch();
			return true;
		}
	
		switch($type)
		{
			case self::SEARCH_USER:
				$this->showUserList($res_sum->getResultIds());
				break;
				
			case self::SEARCH_ROLE:
				$this->showRoleList($res_sum->getResultIds());
				break;
		}
	}
	
	/**
	 * share assign
	 *
	 * @access public
	 * @return
	 */
	public function shareAssign()
	{
		global $ilUser;
		
		if(!$_GET['category_id'])
		{
			ilUtil::sendInfo($this->lng->txt('select_one'),true);
			$this->ctrl->returnToParent($this);
		}
		if(!count($_POST['user_ids']))
		{
			ilUtil::sendInfo($this->lng->txt('select_one'));
			$this->sharePerformSearch();
			return false;
		}
		
		$this->readPermissions();
		if(!$this->isEditable())
		{
			ilUtil::sendInfo($this->lng->txt('permission_denied'));
			$this->edit();
			return false;
		}
		
		
		include_once('./Services/Calendar/classes/class.ilCalendarShared.php');
		$shared = new ilCalendarShared((int) $_GET['category_id']);
		
		foreach($_POST['user_ids'] as $user_id)
		{
			if($ilUser->getId() != $user_id)
			{
				$shared->share($user_id,ilCalendarShared::TYPE_USR);	
			}
		}
		ilUtil::sendInfo($this->lng->txt('cal_shared_selected_usr'));
		$this->edit();
	}
	
	/**
	 * share assign roles
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function shareAssignRoles()
	{
		global $ilUser;
		
		if(!$_GET['category_id'])
		{
			ilUtil::sendInfo($this->lng->txt('select_one'),true);
			$this->ctrl->returnToParent($this);
		}
		if(!count($_POST['role_ids']))
		{
			ilUtil::sendInfo($this->lng->txt('select_one'));
			$this->sharePerformSearch();
			return false;
		}
		
		$this->readPermissions();
		if(!$this->isEditable())
		{
			ilUtil::sendInfo($this->lng->txt('permission_denied'));
			$this->edit();
			return false;
		}
		
		include_once('./Services/Calendar/classes/class.ilCalendarShared.php');
		$shared = new ilCalendarShared((int) $_GET['category_id']);
		
		foreach($_POST['role_ids'] as $role_id)
		{
			$shared->share($role_id,ilCalendarShared::TYPE_ROLE);	
		}
		ilUtil::sendInfo($this->lng->txt('cal_shared_selected_usr'));
		$this->edit();
	}
	
	/**
	 * desassign users/roles from calendar
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function shareDeassign()
	{
		if(!$_GET['category_id'])
		{
			ilUtil::sendInfo($this->lng->txt('select_one'),true);
			$this->ctrl->returnToParent($this);
		}
		if(!count($_POST['obj_ids']))
		{
			ilUtil::sendInfo($this->lng->txt('select_one'));
			$this->edit();
			return false;
		}
		
		$this->readPermissions();
		if(!$this->isEditable())
		{
			ilUtil::sendInfo($this->lng->txt('permission_denied'));
			$this->edit();
			return false;
		}
		

		include_once('./Services/Calendar/classes/class.ilCalendarShared.php');
		$shared = new ilCalendarShared((int) $_GET['category_id']);
		
		foreach($_POST['obj_ids'] as $obj_id)
		{
			$shared->stopSharing($obj_id);
		}
		ilUtil::sendInfo($this->lng->txt('cal_unshared_selected_usr'));
		$this->edit();
		return true;
	}
	
	
	/**
	 * show user list
	 *
	 * @access protected
	 * @param array array of user ids
	 * @return
	 */
	protected function showUserList($a_ids = array())
	{
		global $tpl;
		
		include_once('./Services/Calendar/classes/class.ilCalendarSharedUserListTableGUI.php');
		
		$table = new ilCalendarSharedUserListTableGUI($this,'sharePerformSearch');
		$table->setTitle($this->lng->txt('cal_share_search_usr_header'));
		$table->setFormAction($this->ctrl->getFormAction($this));
		$table->setUsers($a_ids);
		$table->parse();
		
		$table->addCommandButton('shareSearch',$this->lng->txt('search_new'));
		$table->addCommandButton('edit',$this->lng->txt('cancel'));
		
		$tpl->setContent($table->getHTML());
	}
	
	/**
	 * show role list
	 *
	 * @access protected
	 * @param array array of role ids
	 * @return
	 */
	protected function showRoleList($a_ids = array())
	{
		global $tpl;
		
		include_once('./Services/Calendar/classes/class.ilCalendarSharedRoleListTableGUI.php');
		
		$table = new ilCalendarSharedRoleListTableGUI($this,'sharePerformSearch');
		$table->setTitle($this->lng->txt('cal_share_search_role_header'));
		$table->setFormAction($this->ctrl->getFormAction($this));
		$table->setRoles($a_ids);
		$table->parse();
		
		$table->addCommandButton('shareSearch',$this->lng->txt('search_new'));
		$table->addCommandButton('edit',$this->lng->txt('cancel'));
		
		$tpl->setContent($table->getHTML());
	}
	
	
	
	/**
	 * init form search
	 *
	 * @access protected
	 * @param
	 * @return
	 */
	protected function initFormSearch()
	{
		global $lng;
		
		$lng->loadLanguageModule('search');
		
		if(!is_object($this->form))
		{
			include_once('Services/Form/classes/class.ilPropertyFormGUI.php');
			$this->form = new ilPropertyFormGUI();
			$this->form->setFormAction($this->ctrl->getFormAction($this));
			$this->form->setTitle($this->lng->txt('cal_share_search_header'));
		}
		
		$type = new ilRadioGroupInputGUI($this->lng->txt('search_type'),'query_type');
		$type->setValue($_POST['query_type'] ? $_POST['query_type'] : self::SEARCH_USER);
		$type->setRequired(true);
		
		$user = new ilRadioOption($this->lng->txt('obj_user'),self::SEARCH_USER);
		$type->addOption($user);
		
		$role = new ilRadioOption($this->lng->txt('obj_role'),self::SEARCH_ROLE);
		$type->addOption($role);
		
		$this->form->addItem($type);
		
		$search = new ilTextInputGUI($this->lng->txt('cal_search'),'query');
		$search->setValue($_POST['query']);
		$search->setSize(16);
		$search->setMaxLength(128);
		$search->setRequired(true);
		$search->setInfo($this->lng->txt('cal_search_info_share'));
		
		$this->form->addItem($search);
		$this->form->addCommandButton('sharePerformSearch',$this->lng->txt('search'));
		$this->form->addCommandButton('edit',$this->lng->txt('cancel'));
	}
	
	
	/**
	 * init edit/create category form 
	 *
	 * @access protected
	 * @return
	 */
	protected function initFormCategory($a_mode)
	{
		global $rbacsystem,$ilUser;
		
		include_once('./Services/Form/classes/class.ilPropertyFormGUI.php');
		include_once('./Services/Calendar/classes/class.ilCalendarCategory.php');
		
		include_once('./Services/Calendar/classes/class.ilCalendarCategories.php');
		$cat_info = ilCalendarCategories::_getInstance()->getCategoryInfo((int) $_GET['category_id']);
		
		
		$this->form = new ilPropertyFormGUI();
		$this->form->setTableWidth('40%');
		switch($a_mode)
		{
			case 'edit':
				$category = new ilCalendarCategory((int) $_GET['category_id']);	
				$this->form->setTitle($this->lng->txt('cal_edit_category'));
				$this->ctrl->saveParameter($this,array('seed','category_id'));
				$this->form->setFormAction($this->ctrl->getFormAction($this));
				if($this->isEditable())
				{
					$this->form->addCommandButton('update',$this->lng->txt('save'));
					
					if($cat_info['type'] == ilCalendarCategory::TYPE_USR)
					{
						$this->form->addCommandButton('shareSearch',$this->lng->txt('cal_share'));
					}
					$this->form->addCommandButton('confirmDelete',$this->lng->txt('delete'));
					$this->form->addCommandButton('cancel',$this->lng->txt('cancel'));
				}
				break;				
			case 'create':
				$this->editable = true;
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
			$title->setDisabled(!$this->isEditable());
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
			$type->setInfo($this->lng->txt('cal_type_info'));
			$this->form->addItem($type);
		}
		
		
		$color = new ilColorPickerInputGUI($this->lng->txt('cal_calendar_color'),'color');
		$color->setValue($category->getColor());
		if(!$this->isEditable())
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
		$table_gui->setAppointments(
			ilCalendarCategoryAssignments::_getAssignedAppointments(
				ilCalendarCategories::_getInstance()->getSubitemCategories((int) $_GET['category_id'])));
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
		
		$title = $this->lng->txt('cal_table_categories');
		$title .= $this->appendCalendarSelection();
		
		$table_gui->setTitle($title);
		$table_gui->addMultiCommand('saveSelection',$this->lng->txt('show'));
		$table_gui->addCommandButton('add',$this->lng->txt('add'));
		$table_gui->parse();
		
		return $table_gui->getHTML();
	}
	
	
	/**
	 * 
	 * @param
	 * @return
	 */
	 protected function appendCalendarSelection()
	 {
	 	global $ilUser;
	 	
	 	$this->lng->loadLanguageModule('pd');
	 	
	 	$tpl = new ilTemplate('tpl.calendar_selection.html',true,true,'Services/Calendar');
		include_once('./Services/Calendar/classes/class.ilCalendarUserSettings.php');
		switch(ilCalendarUserSettings::_getInstance()->getCalendarSelectionType())
		{
			case ilCalendarUserSettings::CAL_SELECTION_MEMBERSHIP:
				$tpl->setVariable('HTEXT',$this->lng->txt('pd_my_memberships'));
				$tpl->touchBlock('head_item');
				$tpl->touchBlock('head_delim');
				$tpl->touchBlock('head_item');
				
				$this->ctrl->setParameter($this,'calendar_mode',ilCalendarUserSettings::CAL_SELECTION_ITEMS);
				$tpl->setVariable('HHREF',$this->ctrl->getLinkTarget($this,'switchCalendarMode'));
				$tpl->setVariable('HLINK',$this->lng->txt('pd_my_offers'));
				$tpl->touchBlock('head_item');
				break;
				
			case ilCalendarUserSettings::CAL_SELECTION_ITEMS:
				$this->ctrl->setParameter($this,'calendar_mode',ilCalendarUserSettings::CAL_SELECTION_MEMBERSHIP);
				$tpl->setVariable('HHREF',$this->ctrl->getLinkTarget($this,'switchCalendarMode'));
				$tpl->setVariable('HLINK',$this->lng->txt('pd_my_memberships'));
				$tpl->touchBlock('head_item');
				$tpl->touchBlock('head_delim');
				$tpl->touchBlock('head_item');
				
				$tpl->setVariable('HTEXT',$this->lng->txt('pd_my_offers'));
				$tpl->touchBlock('head_item');
				break;
			
								
		}
		return $tpl->get();
	 }
	 
	 /**
	 * Switch calendar selection nmode 
	 * @return
	 */
	 protected function switchCalendarMode()
	 {
	 	include_once('./Services/Calendar/classes/class.ilCalendarUserSettings.php');
	 	ilCalendarUserSettings::_getInstance()->setCalendarSelectionType((int) $_GET['calendar_mode']);
	 	ilCalendarUserSettings::_getInstance()->save();
	 	
	 	$this->ctrl->returnToParent($this);
	 	
	 }
	
	
	/**
	 * read permissions
	 *
	 * @access private
	 * @param
	 * @return
	 */
	private function readPermissions()
	{
		global $ilUser,$rbacsystem;
		
		$this->editable = false;
		$this->visible = false;
		
		include_once('./Services/Calendar/classes/class.ilCalendarShared.php');
		
		$shared = ilCalendarShared::getSharedCalendarsForUser($ilUser->getId());
		$cat = new ilCalendarCategory((int) $_GET['category_id']);
		switch($cat->getType())
		{
			case ilCalendarCategory::TYPE_USR:
				if($cat->getObjId() == $ilUser->getId())
				{
					$this->visible = true;
					$this->editable = true;
				}
				elseif(isset($shared[$cat->getCategoryID()]))
				{
					$this->visible = true;
				}
				break;
			
			case ilCalendarCategory::TYPE_GLOBAL:
				$this->editable = $rbacsystem->checkAccess('edit_event',ilCalendarSettings::_getInstance()->getCalendarSettingsId());
				$this->visible = true;
				break;
				
			case ilCalendarCategory::TYPE_OBJ:
				$this->editable = false;
				$this->visible = true;
				break;
		}
		
	}
	
	/**
	 * 
	 * @param
	 * @return
	 */
	 protected function checkVisible()
	 {
		global $ilErr;
		
		if(!$this->visible)
		{
			$ilErr->raiseError($this->lng->txt('permission_denied'),$ilErr->FATAL);
		}
	 }
	
	/**
	 * check if calendar is editable
	 * @access private
	 * @return
	 */
	private function isEditable()
	{
		return $this->editable;
	}
	
	/**
	 * Show links to references
	 * @param int $a_obj_id $obj_id
	 * @return
	 */
	protected function addReferenceLinks($a_obj_id)
	{
		global $tree;
		
		$tpl = new ilTemplate('tpl.cal_reference_links.html',true,true,'Services/Calendar');
		
		foreach(ilObject::_getAllReferences($a_obj_id) as $ref_id => $ref_id)
		{
			include_once('./classes/class.ilLink.php');
			
			$parent_ref_id = $tree->getParentId($ref_id);
			$parent_obj_id = ilObject::_lookupObjId($parent_ref_id);
			$parent_type = ilObject::_lookupType($parent_obj_id);
			$parent_title = ilObject::_lookupTitle($parent_obj_id);
			
			$type = ilObject::_lookupType($a_obj_id);
			$title = ilObject::_lookupTitle($a_obj_id);
			
			$tpl->setCurrentBlock('reference');
			$tpl->setVariable('PIMG_SRC',ilUtil::getTypeIconPath($parent_type,$parent_obj_id,'tiny'));
			$tpl->setVariable('PIMG_ALT',$this->lng->txt('obj_'.$parent_type));
			$tpl->setVariable('PARENT_TITLE',$parent_title);
			$tpl->setVariable('PARENT_HREF',ilLink::_getLink($parent_ref_id));
			 
			$tpl->setVariable('SRC',ilUtil::getTypeIconPath($type,$a_obj_id,'tiny'));
			$tpl->setVariable('ALT',$this->lng->txt('obj_'.$type));
			$tpl->setVariable('TITLE',$title);
			$tpl->setVariable('HREF',ilLink::_getLink($ref_id));
			$tpl->parseCurrentBlock();
		}
		return $tpl->get();
	}
}
?>