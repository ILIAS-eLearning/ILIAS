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

include_once "./Services/Container/classes/class.ilContainerGUI.php";
include_once('./Modules/Group/classes/class.ilObjGroup.php');

/**
* Class ilObjGroupGUI
*
* @author	Stefan Meyer <smeyer.ilias@gmx.de>
* @author	Sascha Hofmann <saschahofmann@gmx.de>
*
* @version	$Id$
*
* @ilCtrl_Calls ilObjGroupGUI: ilGroupRegistrationGUI, ilConditionHandlerInterface, ilPermissionGUI, ilInfoScreenGUI,, ilLearningProgressGUI
* @ilCtrl_Calls ilObjGroupGUI: ilRepositorySearchGUI, ilPublicUserProfileGUI, ilObjCourseGroupingGUI
* @ilCtrl_Calls ilObjGroupGUI: ilCourseContentGUI, ilColumnGUI, ilPageObjectGUI,ilCourseItemAdministrationGUI
*
* @extends ilObjectGUI
*/
class ilObjGroupGUI extends ilContainerGUI
{
	/**
	* Constructor
	* @access	public
	*/
	public function __construct($a_data,$a_id,$a_call_by_reference,$a_prepare_output = false)
	{
		$this->type = "grp";
		$this->ilContainerGUI($a_data,$a_id,$a_call_by_reference,$a_prepare_output);

		$this->lng->loadLanguageModule('grp');
	}

	function &executeCommand()
	{
		global $ilUser,$rbacsystem,$ilAccess, $ilNavigationHistory,$ilErr;

		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();
		$this->prepareOutput();

		// add entry to navigation history
		if (!$this->getCreationMode() &&
			$ilAccess->checkAccess("read", "", $_GET["ref_id"]))
		{
			$ilNavigationHistory->addItem($_GET["ref_id"],
				"repository.php?cmd=frameset&ref_id=".$_GET["ref_id"], "grp");
		}

		switch($next_class)
		{
			case "ilconditionhandlerinterface":
				include_once './classes/class.ilConditionHandlerInterface.php';

				if($_GET['item_id'])
				{
					$this->ctrl->saveParameter($this,'item_id',$_GET['item_id']);
					$this->setSubTabs('activation');
					$this->tabs_gui->setTabActive('view_content');

					$new_gui =& new ilConditionHandlerInterface($this,(int) $_GET['item_id']);
					$this->ctrl->forwardCommand($new_gui);
				}
				else
				{
					$new_gui =& new ilConditionHandlerInterface($this);
					$this->ctrl->forwardCommand($new_gui);
				}
				break;

			case 'ilgroupregistrationgui':
				$this->ctrl->setReturn($this,'');
				include_once('./Modules/Group/classes/class.ilGroupRegistrationGUI.php');
				$registration = new ilGroupRegistrationGUI($this->object);
				$this->ctrl->forwardCommand($registration);
				$this->tabs_gui->setTabActive('join');
				break;

			case 'ilpermissiongui':
				$this->tabs_gui->setTabActive('perm_settings');
				include_once("./classes/class.ilPermissionGUI.php");
				$perm_gui =& new ilPermissionGUI($this);
				$ret =& $this->ctrl->forwardCommand($perm_gui);
				break;

			case 'ilrepositorysearchgui':
				include_once('./Services/Search/classes/class.ilRepositorySearchGUI.php');
				$rep_search =& new ilRepositorySearchGUI();
				$rep_search->setCallback($this,'addUserObject');

				// Set tabs
				$this->tabs_gui->setTabActive('members');
				$this->ctrl->setReturn($this,'members');
				$ret =& $this->ctrl->forwardCommand($rep_search);
				$this->setSubTabs('members');
				$this->tabs_gui->setSubTabActive('members');
				break;

			case 'ilcourseitemadministrationgui':
				include_once 'Modules/Course/classes/class.ilCourseItemAdministrationGUI.php';
				$this->tabs_gui->clearSubTabs();
				$this->ctrl->setReturn($this,'view');
				$item_adm_gui = new ilCourseItemAdministrationGUI($this->object,(int) $_REQUEST['item_id']);
				$this->ctrl->forwardCommand($item_adm_gui);
				break;

			case "ilinfoscreengui":
				$ret =& $this->infoScreen();
				break;

			case "illearningprogressgui":
				include_once './Services/Tracking/classes/class.ilLearningProgressGUI.php';

				$new_gui =& new ilLearningProgressGUI(LP_MODE_REPOSITORY,
													  $this->object->getRefId(),
													  $_GET['user_id'] ? $_GET['user_id'] : $ilUser->getId());
				$this->ctrl->forwardCommand($new_gui);
				$this->tabs_gui->setTabActive('learning_progress');
				break;

			case 'ilobjcoursegroupinggui':
				include_once './Modules/Course/classes/class.ilObjCourseGroupingGUI.php';

				$this->ctrl->setReturn($this,'edit');
				$crs_grp_gui =& new ilObjCourseGroupingGUI($this->object,(int) $_GET['obj_id']);
				$this->ctrl->forwardCommand($crs_grp_gui);
				$this->setSubTabs('settings');
				$this->tabs_gui->setTabActive('settings');
				$this->tabs_gui->setSubTabActive('groupings');
				break;

			case 'ilcoursecontentgui':

				include_once './Modules/Course/classes/class.ilCourseContentGUI.php';
				$course_content_obj = new ilCourseContentGUI($this);
				$this->ctrl->forwardCommand($course_content_obj);
				break;

			case 'ilcourseitemadministrationgui':

				include_once 'Modules/Course/classes/class.ilCourseItemAdministrationGUI.php';

				$this->ctrl->setReturn($this,'');
				$item_adm_gui = new ilCourseItemAdministrationGUI($this->object,(int) $_GET['item_id']);
				$this->ctrl->forwardCommand($item_adm_gui);

				// (Sub)tabs
				$this->setSubTabs('activation');
				$this->tabs_gui->setTabActive('view_content');
				$this->tabs_gui->setSubTabActive('activation');
				break;

			case 'ilpublicuserprofilegui':
				require_once './Services/User/classes/class.ilPublicUserProfileGUI.php';
				$profile_gui = new ilPublicUserProfileGUI($_GET["user"]);
				$html = $this->ctrl->forwardCommand($profile_gui);
				$this->setSubTabs('members');
				$this->tabs_gui->setTabActive('group_members');
				$this->tabs_gui->setSubTabActive('grp_members_gallery');
				$this->tpl->setVariable("ADM_CONTENT", $html);
				break;

			case "ilcolumngui":
				$this->tabs_gui->setTabActive('none');
				$this->checkPermission("read");
				include_once("./Services/Style/classes/class.ilObjStyleSheet.php");
				//$this->getSubItems();
				$this->tpl->setVariable("LOCATION_CONTENT_STYLESHEET",
					ilObjStyleSheet::getContentStylePath(0));
				$this->renderObject();
				break;

			// container page editing
			case "ilpageobjectgui":
				$this->checkPermission("write");
				//$this->prepareOutput(false);
				$ret = $this->forwardToPageObject();
				if ($ret != "")
				{
					$this->tpl->setContent($ret);
				}
				break;

			default:
			
				// check visible permission
				if (!$this->getCreationMode() and !$ilAccess->checkAccess('visible','',$this->object->getRefId(),'grp'))
				{
					$ilErr->raiseError($this->lng->txt("msg_no_perm_read"),$ilErr->MESSAGE);
				}

				// check read permission
				if ((!$this->getCreationMode()
					&& !$rbacsystem->checkAccess('read',$this->object->getRefId()) && $cmd != 'infoScreen')
					|| $cmd == 'join')
				{
					// no join permission -> redirect to info screen
					if (!$rbacsystem->checkAccess('join',$this->object->getRefId()))
					{
						$this->ctrl->redirect($this, "infoScreen");
					}
					else	// no read -> show registration
					{
						include_once('./Modules/Group/classes/class.ilGroupRegistrationGUI.php');
						$this->ctrl->redirectByClass("ilGroupRegistrationGUI", "show");
					}
				}
				if(!$cmd)
				{
					$cmd = 'view';
				}
				$cmd .= 'Object';
				$this->$cmd();
				break;
		}
	}

	function viewObject()
	{
		global $tree,$rbacsystem,$ilUser;

		include_once 'Services/Tracking/classes/class.ilLearningProgress.php';
		ilLearningProgress::_tracProgress($ilUser->getId(),$this->object->getId(),'grp');

		if (strtolower($_GET["baseClass"]) == "iladministrationgui")
		{
			parent::viewObject();
			return true;
		}
		$this->tabs_gui->setTabActive('view_content');
		$this->renderObject();
		
		/*
		else if($tree->checkForParentType($this->ref_id,'crs'))
		{
			$this->renderObject();
			//$this->ctrl->returnToParent($this);
		}
		else
		{
			include_once './Modules/Course/classes/class.ilCourseContentGUI.php';
			$course_content_obj = new ilCourseContentGUI($this);
			
			$this->ctrl->setCmdClass(get_class($course_content_obj));
			$this->ctrl->forwardCommand($course_content_obj);
		}

		$this->tabs_gui->setTabActive('view_content');
		return true;
		*/
	}
	
	/**
	* Modify Item ListGUI for presentation in container
	*/
	function modifyItemGUI($a_item_list_gui, $a_item_data, $a_show_path)
	{
		global $tree;

		// if folder is in a course, modify item list gui according to course requirements
		if ($course_ref_id = $tree->checkForParentType($this->object->getRefId(),'crs'))
		{
			include_once("./Modules/Course/classes/class.ilObjCourse.php");
			include_once("./Modules/Course/classes/class.ilObjCourseGUI.php");
			$course_obj_id = ilObject::_lookupObjId($course_ref_id);
			ilObjCourseGUI::_modifyItemGUI($a_item_list_gui, $a_item_data, $a_show_path,
				ilObjCourse::_lookupAboStatus($course_obj_id), $course_ref_id, $course_obj_id,
				$this->object->getRefId());
		}
	}
	
	
	/**
	 * create object
	 * Show object creation form
	 * Overwritten from base class.
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function createObject()
	{
		if(!is_object($this->object))
		{
			$this->object = new ilObjGroup();
		}
		
		$this->ctrl->setParameter($this,'new_type','grp');
		$this->initForm('create');
		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.grp_create.html','Modules/Group');
		$this->tpl->setVariable('CREATE_FORM',$this->form->getHTML());
		
		// IMPORT
		$this->tpl->setVariable('IMP_FORMACTION',$this->ctrl->getFormAction($this));
		$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));
		$this->tpl->setVariable("TXT_IMPORT_CRS", $this->lng->txt("import_grp"));
		$this->tpl->setVariable("TXT_CRS_FILE", $this->lng->txt("file"));
		$this->tpl->setVariable("TXT_IMPORT", $this->lng->txt("import"));
		$this->tpl->setVariable('TXT_CANCEL',$this->lng->txt('cancel'));

		// get the value for the maximal uploadable filesize from the php.ini (if available)
		$umf=get_cfg_var("upload_max_filesize");
		// get the value for the maximal post data from the php.ini (if available)
		$pms=get_cfg_var("post_max_size");

		// use the smaller one as limit
		$max_filesize=min($umf, $pms);
		if (!$max_filesize) 
			$max_filesize=max($umf, $pms);
	
		// gives out the limit as a littel notice :)
		$this->tpl->setVariable("TXT_FILE_INFO", $this->lng->txt("file_notice").$max_filesize);
		
		
		$this->fillCloneTemplate('DUPLICATE','grp');
	}
	
	/**
	 * save object
	 *
	 * @access public
	 * @return
	 */
	public function saveObject()
	{
		global $ilErr,$ilUser;
		
		$this->object = new ilObjGroup();
		
		$this->load();
		$ilErr->setMessage('');
		
		if(!$this->object->validate())
		{
			$err = $this->lng->txt('err_check_input').'<br />';
			$err .= $this->lng->txt($ilErr->getMessage());
			ilUtil::sendInfo($err);
			$this->createObject();
			return true;
		}

		$this->object->create();
		$this->object->createReference();
		$this->object->putInTree($_GET["ref_id"]);
		$this->object->setPermissions($_GET["ref_id"]);
		$this->object->initDefaultRoles();
		$this->object->initGroupStatus($this->object->getGroupType());
		
		
		// Add user as admin and enable notification
		include_once('./Modules/Group/classes/class.ilGroupParticipants.php');
		$members_obj = ilGroupParticipants::_getInstanceByObjId($this->object->getId());
		$members_obj->add($ilUser->getId(),IL_GRP_ADMIN);
		$members_obj->updateNotification($ilUser->getId(),1);
		
		
		// BEGIN ChangeEvent: Record save object.
		require_once('Services/Tracking/classes/class.ilChangeEvent.php');
		if (ilChangeEvent::_isActive())
		{
			ilChangeEvent::_recordWriteEvent($this->object->getId(), $ilUser->getId(), 'create');
		}
		// END ChangeEvent: Record save object.
		
		
		ilUtil::sendInfo($this->lng->txt("grp_added"),true);
		$this->redirectToRefId($_GET["ref_id"]);
	}
	
	/**
	 * Edit object
	 *
	 * @access public
	 * @param bool show warning, if group type has been changed
	 * @return
	 */
	public function editObject($a_group_type_warning = false)
	{
		$this->setSubTabs('settings');
		$this->tabs_gui->setTabActive('settings');
		$this->tabs_gui->setSubTabActive('grp_settings');
		
		if($a_group_type_warning)
		{
			unset($this->form);
			$this->initForm('update_group_type');
		}
		else
		{
			$this->initForm('edit');
		}
		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.grp_edit.html','Modules/Group');
		$this->tpl->setVariable('EDIT_FORM',$this->form->getHTML());
	}
	
	/**
	 * change group type
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function updateGroupTypeObject()
	{
		$this->updateObject(true);
	}
	
	
	/**
	* update GroupObject
	* @param bool update group type
	* @access public
	*/
	public function updateObject($update_group_type = false)
	{
		global $ilErr;

		$this->checkPermission('write');
		
		$old_type = $this->object->getGroupType();
		
		$this->load();
		$ilErr->setMessage('');
		
		if(!$this->object->validate())
		{
			$err = $this->lng->txt('err_check_input').'<br />';
			$err .= $this->lng->txt($ilErr->getMessage());
			ilUtil::sendInfo($err);
			$this->editObject();
			return true;
		}
		
		if($this->object->isGroupTypeModified($old_type) and !$update_group_type)
		{
			ilUtil::sendInfo($this->lng->txt('grp_warn_grp_type_changed'));
			$this->editObject(true);
			return true;
		}
		if($update_group_type)
		{
			$this->object->updateGroupType();
		}
				
		$this->object->update();

		// BEGIN ChangeEvents: Record update Object.
		require_once('Services/Tracking/classes/class.ilChangeEvent.php');
		if (ilChangeEvent::_isActive())
		{
			global $ilUser;
			ilChangeEvent::_recordWriteEvent($this->object->getId(), $ilUser->getId(), 'update');
			ilChangeEvent::_catchupWriteEvents($this->object->getId(), $ilUser->getId());
		}
		// END PATCH ChangeEvents: Record update Object.

		ilUtil::sendInfo($this->lng->txt("msg_obj_modified"));
		$this->editObject();
		return true;
	}
	
	/**
	* edit container icons
	*/
	public function editGroupIconsObject()
	{
		global $rbacsystem;

		$this->checkPermission('write');
		
		$this->setSubTabs("settings");
		$this->tabs_gui->setTabActive('settings');
		$this->tabs_gui->setSubTabActive('grp_icon_settings');

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.grp_edit_icons.html",'Modules/Group');
		$this->showCustomIconsEditing();
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this,'updateGroupIcons'));
		$this->tpl->setVariable("TXT_CANCEL", $this->lng->txt("cancel"));
		$this->tpl->setVariable("TXT_SUBMIT", $this->lng->txt("save"));
		$this->tpl->setVariable("CMD_CANCEL", "cancel");
		$this->tpl->setVariable("CMD_SUBMIT", "updateGroupIcons");
		$this->tpl->parseCurrentBlock();
	}
	
	/**
	 * update group icons
	 *
	 * @access public
	 * @return
	 */
	public function updateGroupIconsObject()
	{
		$this->checkPermission('write');
		
		//save custom icons
		if ($this->ilias->getSetting("custom_icons"))
		{
			$this->object->saveIcons($_FILES["cont_big_icon"]['tmp_name'],
				$_FILES["cont_small_icon"]['tmp_name'], $_FILES["cont_tiny_icon"]['tmp_name']);
		}

		ilUtil::sendInfo($this->lng->txt("msg_obj_modified"),true);
		$this->ctrl->redirect($this,"editGroupIcons");
	}
	
	/**
	* remove small icon
	*
	* @access	public
	*/
	public function removeSmallIconObject()
	{
		$this->object->removeSmallIcon();
		ilUtil::redirect($this->ctrl->getLinkTarget($this, "editGroupIcons"));
	}

	/**
	* remove big icon
	*
	* @access	public
	*/
	public function removeBigIconObject()
	{
		$this->object->removeBigIcon();
		ilUtil::redirect($this->ctrl->getLinkTarget($this, "editGroupIcons"));
	}
	
	/**
	* remove big icon
	*
	* @access	public
	*/
	public function removeTinyIconObject()
	{
		$this->object->removeTinyIcon();
		ilUtil::redirect($this->ctrl->getLinkTarget($this, "editGroupIcons"));
	}
	
	/**
	* Edit Map Settings
	*/
	public function editMapSettingsObject()
	{
		global $ilUser, $ilCtrl, $ilUser, $ilAccess;

		$this->setSubTabs("settings");
		$this->tabs_gui->setTabActive('settings');
		$this->tabs_gui->setSubTabActive('grp_map_settings');
		
		include_once('./Services/GoogleMaps/classes/class.ilGoogleMapUtil.php');
		if (!ilGoogleMapUtil::isActivated() ||
			!$ilAccess->checkAccess("write", "", $this->object->getRefId()))
		{
			return;
		}

		$latitude = $this->object->getLatitude();
		$longitude = $this->object->getLongitude();
		$zoom = $this->object->getLocationZoom();
		
		// Get Default settings, when nothing is set
		if ($latitude == 0 && $longitude == 0 && $zoom == 0)
		{
			$def = ilGoogleMapUtil::getDefaultSettings();
			$latitude = $def["latitude"];
			$longitude = $def["longitude"];
			$zoom =  $def["zoom"];
		}


		//$this->tpl->setTitleIcon(ilUtil::getImagePath("icon_pd_b.gif"), $this->lng->txt("personal_desktop"));
		//$this->tpl->setVariable("HEADER", $this->lng->txt("personal_desktop"));

		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setFormAction($ilCtrl->getFormAction($this));
		
		$form->setTitle($this->lng->txt("grp_map_settings"));
			
		// enable map
		$public = new ilCheckboxInputGUI($this->lng->txt("grp_enable_map"),
			"enable_map");
		$public->setValue("1");
		$public->setChecked($this->object->getEnableGroupMap());
		$form->addItem($public);

		// map location
		$loc_prop = new ilLocationInputGUI($this->lng->txt("grp_map_location"),
			"location");
		$loc_prop->setLatitude($latitude);
		$loc_prop->setLongitude($longitude);
		$loc_prop->setZoom($zoom);
		$form->addItem($loc_prop);
		
		$form->addCommandButton("saveMapSettings", $this->lng->txt("save"));
		
		$this->tpl->setVariable("ADM_CONTENT", $form->getHTML());
	}

	public function saveMapSettingsObject()
	{
		global $ilCtrl, $ilUser;

		$this->object->setLatitude(ilUtil::stripSlashes($_POST["location"]["latitude"]));
		$this->object->setLongitude(ilUtil::stripSlashes($_POST["location"]["longitude"]));
		$this->object->setLocationZoom(ilUtil::stripSlashes($_POST["location"]["zoom"]));
		$this->object->setEnableGroupMap(ilUtil::stripSlashes($_POST["enable_map"]));
		$this->object->update();
		
		$ilCtrl->redirect($this, "editMapSettings");
	}
	
	/**
	* Members map
	*/
	public function membersMapObject()
	{
		global $tpl;
		
		$this->setSubTabs('members');
		$this->tabs_gui->setTabActive('members');
		
		include_once("./Services/GoogleMaps/classes/class.ilGoogleMapUtil.php");
		if (!ilGoogleMapUtil::isActivated() || !$this->object->getEnableGroupMap())
		{
			return;
		}
		
		include_once("./Services/GoogleMaps/classes/class.ilGoogleMapGUI.php");
		$map = new ilGoogleMapGUI();
		$map->setMapId("group_map");
		$map->setWidth("700px");
		$map->setHeight("500px");
		$map->setLatitude($this->object->getLatitude());
		$map->setLongitude($this->object->getLongitude());
		$map->setZoom($this->object->getLocationZoom());
		$map->setEnableTypeControl(true);
		$map->setEnableNavigationControl(true);
		
		$member_ids = $this->object->getGroupMemberIds();
		$admin_ids = $this->object->getGroupAdminIds();
		
		// fetch all users data in one shot to improve performance
		$members = $this->object->getGroupMemberData($member_ids);
		foreach($member_ids as $user_id)
		{
			$map->addUserMarker($user_id);
		}
		$tpl->setContent($map->getHTML());
		$tpl->setLeftContent($map->getUserListHTML());
	}
	
	
	/**
	 * edit info
	 *
	 * @access public
	 * @return
	 */
	public function editInfoObject()
	{
		global $ilErr,$ilAccess;

		$this->checkPermission('write');
		
		$this->setSubTabs('settings');
		$this->tabs_gui->setTabActive('settings');
		$this->tabs_gui->setSubTabActive('grp_info_settings');
	 	
	 	$this->initInfoEditor();
		$this->tpl->setContent($this->form->getHTML());
	}
	
	/**
	 * init info editor
	 *
	 * @access protected
	 * @return
	 */
	protected function initInfoEditor()
	{
		if(is_object($this->form))
		{
			return true;
		}
	
		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$this->form = new ilPropertyFormGUI();
		$this->form->setFormAction($this->ctrl->getFormAction($this,'updateInfo'));
		$this->form->setTitle($this->lng->txt('grp_general_informations'));
		$this->form->addCommandButton('updateInfo',$this->lng->txt('save'));
		$this->form->addCommandButton('editInfo',$this->lng->txt('cancel'));
		
		$area = new ilTextAreaInputGUI($this->lng->txt('grp_information'),'important');
		$area->setInfo($this->lng->txt('grp_information_info'));
		$area->setValue($this->object->getInformation());
		$area->setRows(8);
		$area->setCols(80);
		$this->form->addItem($area);
	}
	
	/**
	 * update info 
	 *
	 * @access public
	 * @return
	 */
	public function updateInfoObject()
	{
		$this->checkPermission('write');
		
		$this->object->setInformation(ilUtil::stripSlashes($_POST['important']));
		$this->object->update();
		
		ilUtil::sendInfo($this->lng->txt("settings_saved"));
		$this->editInfoObject();
		return true;
	}
	
	/////////////////////////////////////////////////////////// Member section /////////////////////
	/**
	 * Builds a group members gallery as a layer of left-floating images
	 * @author Arturo Gonzalez <arturogf@gmail.com>
	 * @access       public
	 */
	public function membersGalleryObject()
	{
		global $rbacsystem, $ilAccess, $ilUser;
		
		$is_admin = (bool) $rbacsystem->checkAccess("write", $this->object->getRefId());
		
		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.crs_members_gallery.html','Modules/Course');
		
		$this->setSubTabs('members');
		$this->tabs_gui->setTabActive('members');
		
		$member_ids = $this->object->getGroupMemberIds();
		$admin_ids = $this->object->getGroupAdminIds();
		
		// fetch all users data in one shot to improve performance
		$members = $this->object->getGroupMemberData($member_ids);
		
		// MEMBERS
		if(count($members))
		{
			$ordered_members = array();

			foreach($members as $member)
			{
				// get user object
				if(!($usr_obj = ilObjectFactory::getInstanceByObjId($member["id"],false)))
				{
					continue;
				}
				
				// please do not use strtoupper on first/last name for output
				// this messes up with some unicode characters, i guess
				// depending on php verion, alex
				array_push($ordered_members,array("id" => $member["id"], 
								  "login" => $usr_obj->getLogin(),
								  "lastname" => $usr_obj->getLastName(),
								  "firstname" => $usr_obj->getFirstName(),
								  "sortlastname" => strtoupper($usr_obj->getLastName()).strtoupper($usr_obj->getFirstName()),
								  "usr_obj" => $usr_obj));
			}

			$ordered_members=ilUtil::sortArray($ordered_members,"sortlastname","asc");

			foreach($ordered_members as $member) {

				$usr_obj = $member["usr_obj"];

			        $public_profile = $usr_obj->getPref("public_profile");

				// SET LINK TARGET FOR USER PROFILE
				$this->ctrl->setParameterByClass("ilpublicuserprofilegui", "user", $member["id"]);
				$profile_target = $this->ctrl->getLinkTargetByClass("ilpublicuserprofilegui","getHTML");
			
				// GET USER IMAGE
				$file = $usr_obj->getPersonalPicturePath("xsmall");
				
				switch(in_array($member["id"],$admin_ids))
				{
					//admins
					case 1:
						if ($public_profile == "y")
						{
							$this->tpl->setCurrentBlock("tutor_linked");
							$this->tpl->setVariable("LINK_PROFILE", $profile_target);
							$this->tpl->setVariable("SRC_USR_IMAGE", $file);
							$this->tpl->parseCurrentBlock();
						}
						else
						{
							$this->tpl->setCurrentBlock("tutor_not_linked");
							$this->tpl->setVariable("SRC_USR_IMAGE", $file);
							$this->tpl->parseCurrentBlock();
						}
						$this->tpl->setCurrentBlock("tutor");
						break;
				
					case 0:
						if ($public_profile == "y")
						{
							$this->tpl->setCurrentBlock("member_linked");
							$this->tpl->setVariable("LINK_PROFILE", $profile_target);
							$this->tpl->setVariable("SRC_USR_IMAGE", $file);
							$this->tpl->parseCurrentBlock();
						}
						else
						{
							$this->tpl->setCurrentBlock("member_not_linked");
							$this->tpl->setVariable("SRC_USR_IMAGE", $file);
							$this->tpl->parseCurrentBlock();
						}
						$this->tpl->setCurrentBlock("member");
						break;
				}
			
				// do not show name, if public profile is not activated
				if ($public_profile == "y")
				{
					$this->tpl->setVariable("FIRSTNAME", $member["firstname"]);
					$this->tpl->setVariable("LASTNAME", $member["lastname"]);
				}
				$this->tpl->setVariable("LOGIN", $usr_obj->getLogin());
				$this->tpl->parseCurrentBlock();
			}
			$this->tpl->setCurrentBlock("members");	
			//$this->tpl->setVariable("MEMBERS_TABLE_HEADER",$this->lng->txt('crs_members_title'));
			$this->tpl->parseCurrentBlock();
		}
		
		$this->tpl->setVariable("TITLE",$this->lng->txt('crs_members_print_title'));
		$this->tpl->setVariable("CSS_PATH",ilUtil::getStyleSheetLocation());
	}
	
    
	protected function readMemberData($ids,$role = 'admin')
	{
		include_once('./Services/PrivacySecurity/classes/class.ilPrivacySettings.php');
		$privacy = ilPrivacySettings::_getInstance();

		if($this->show_tracking)
		{
			include_once 'Services/Tracking/classes/class.ilLPStatusWrapper.php';
			$completed = ilLPStatusWrapper::_getCompleted($this->object->getId());
			$in_progress = ilLPStatusWrapper::_getInProgress($this->object->getId());
			$not_attempted = ilLPStatusWrapper::_getNotAttempted($this->object->getId());
			$failed = ilLPStatusWrapper::_getFailed($this->object->getId());
		}

		if($privacy->enabledGroupAccessTimes())
		{
			include_once('./Services/Tracking/classes/class.ilLearningProgress.php');
			$progress = ilLearningProgress::_lookupProgressByObjId($this->object->getId());
		}

		foreach($ids as $usr_id)
		{
			$name = ilObjUser::_lookupName($usr_id);
			$tmp_data['firstname'] = $name['firstname'];
			$tmp_data['lastname'] = $name['lastname'];
			$tmp_data['login'] = ilObjUser::_lookupLogin($usr_id);
			$tmp_data['notification'] = $this->object->members_obj->isNotificationEnabled($usr_id) ? 1 : 0;
			$tmp_data['usr_id'] = $usr_id;
			$tmp_data['login'] = ilObjUser::_lookupLogin($usr_id);

			if($this->show_tracking)
			{
				if(in_array($usr_id,$completed))
				{
					$tmp_data['progress'] = LP_STATUS_COMPLETED;
				}
				elseif(in_array($usr_id,$in_progress))
				{
					$tmp_data['progress'] = LP_STATUS_IN_PROGRESS;
				}
				elseif(in_array($usr_id,$failed))
				{
					$tmp_data['progress'] = LP_STATUS_FAILED;
				}
				else
				{
					$tmp_data['progress'] = LP_STATUS_NOT_ATTEMPTED;
				}
			}

			if($privacy->enabledGroupAccessTimes())
			{
				if(isset($progress[$usr_id]['ts']) and $progress[$usr_id]['ts'])
				{
					$tmp_data['access_time'] = ilDatePresentation::formatDate(
						$tmp_date = new ilDateTime($progress[$usr_id]['ts'],IL_CAL_DATETIME));
					$tmp_data['access_time_unix'] = $tmp_date->get('IL_CAL_UNIX');
				}
				else
				{
					$tmp_data['access_time'] = $this->lng->txt('no_date');
					$tmp_data['access_time_unix'] = 0;
				}
			}

			$members[] = $tmp_data;
		}
		return $members ? $members : array();
	}
	
	/**
	 * edit members
	 *
	 * @access public
	 * @return
	 */
	public function membersObject()
	{
		global $ilUser;
		
		include_once('./Modules/Group/classes/class.ilGroupParticipants.php');
		include_once('./Modules/Group/classes/class.ilGroupParticipantsTableGUI.php');
		
		$this->checkPermission('write');
		
		include_once './Services/Tracking/classes/class.ilObjUserTracking.php';
		include_once('./Services/Tracking/classes/class.ilLPObjSettings.php');
		$this->show_tracking = (ilObjUserTracking::_enabledLearningProgress() and 
			ilObjUserTracking::_enabledUserRelatedData() and
			ilLPObjSettings::_lookupMode($this->object->getId()) != LP_MODE_DEACTIVATED);
		
		
		$part = ilGroupParticipants::_getInstanceByObjId($this->object->getId());

		$this->setSubTabs('members');
		$this->tabs_gui->setTabActive('members');
		$this->tabs_gui->setSubTabActive('grp_edit_members');
		
		$this->tpl->addBlockfile('ADM_CONTENT','adm_content','tpl.grp_edit_members.html','Modules/Group');
		$this->tpl->setVariable('FORMACTION',$this->ctrl->getFormAction($this));
		
		// add members
		$this->tpl->addBlockfile("BUTTONS", "buttons", "tpl.buttons.html");
		$this->tpl->setCurrentBlock("btn_cell");
		$this->tpl->setVariable("BTN_LINK",$this->ctrl->getLinkTargetByClass('ilRepositorySearchGUI','start'));
		$this->tpl->setVariable("BTN_TXT",$this->lng->txt("grp_add_member"));
		$this->tpl->parseCurrentBlock();

		$this->setShowHidePrefs();
		
		
		// Waiting list table
		include_once('./Modules/Group/classes/class.ilGroupWaitingList.php');
		$waiting_list = new ilGroupWaitingList($this->object->getId());
		if(count($wait = $waiting_list->getAllUsers()))
		{
			include_once('./Services/Membership/classes/class.ilWaitingListTableGUI.php');
			if($ilUser->getPref('grp_wait_hide'))
			{
				$table_gui = new ilWaitingListTableGUI($this,$waiting_list,false);
				$this->ctrl->setParameter($this,'wait_hide',0);
				$table_gui->addHeaderCommand($this->ctrl->getLinkTarget($this,'members'),
					$this->lng->txt('show'),
					'',
					ilUtil::getImagePath('edit_add.png'));
				$this->ctrl->clearParameters($this);
			}
			else
			{
				$table_gui = new ilWaitingListTableGUI($this,$waiting_list,true);
				$this->ctrl->setParameter($this,'wait_hide',1);
				$table_gui->addHeaderCommand($this->ctrl->getLinkTarget($this,'members'),
					$this->lng->txt('hide'),
					'',
					ilUtil::getImagePath('edit_remove.png'));
				$this->ctrl->clearParameters($this);
			}
			$table_gui->setUsers($wait);
			$table_gui->setTitle($this->lng->txt('grp_header_waiting_list'),'icon_usr.gif',$this->lng->txt('group_new_registrations'));
			$this->tpl->setVariable('TABLE_SUB',$table_gui->getHTML());
		}		

		
		// Subscriber table
		if(count($subscribers = $part->getSubscribers()))
		{
			include_once('./Services/Membership/classes/class.ilSubscriberTableGUI.php');
			if($ilUser->getPref('grp_subscriber_hide'))
			{
				$table_gui = new ilSubscriberTableGUI($this,$part,false);
				$this->ctrl->setParameter($this,'subscriber_hide',0);
				$table_gui->addHeaderCommand($this->ctrl->getLinkTarget($this,'members'),
					$this->lng->txt('show'),
					'',
					ilUtil::getImagePath('edit_add.png'));
				$this->ctrl->clearParameters($this);
			}
			else
			{
				$table_gui = new ilSubscriberTableGUI($this,$part,true);
				$this->ctrl->setParameter($this,'subscriber_hide',1);
				$table_gui->addHeaderCommand($this->ctrl->getLinkTarget($this,'members'),
					$this->lng->txt('hide'),
					'',
					ilUtil::getImagePath('edit_remove.png'));
				$this->ctrl->clearParameters($this);
			}
			$table_gui->setSubscribers($subscribers);
			$table_gui->setTitle($this->lng->txt('group_new_registrations'),'icon_usr.gif',$this->lng->txt('group_new_registrations'));
			$this->tpl->setVariable('TABLE_SUB',$table_gui->getHTML());
		}

		if(count($part->getAdmins()))
		{
			if($ilUser->getPref('grp_admin_hide'))
			{
				$table_gui = new ilGroupParticipantsTableGUI($this,'admin',false,$this->show_tracking);
				$this->ctrl->setParameter($this,'admin_hide',0);
				$table_gui->addHeaderCommand($this->ctrl->getLinkTarget($this,'members'),
					$this->lng->txt('show'),
					'',
					ilUtil::getImagePath('edit_add.png'));
				$this->ctrl->clearParameters($this);
			}
			else
			{
				$table_gui = new ilGroupParticipantsTableGUI($this,'admin',true,$this->show_tracking);
				$this->ctrl->setParameter($this,'admin_hide',1);
				$table_gui->addHeaderCommand($this->ctrl->getLinkTarget($this,'members'),
					$this->lng->txt('hide'),
					'',
					ilUtil::getImagePath('edit_remove.png'));
				$this->ctrl->clearParameters($this);
			}
			$table_gui->setTitle($this->lng->txt('grp_admins'),'icon_usr.gif',$this->lng->txt('grp_admins'));
			$table_gui->setData($this->readMemberData($part->getAdmins()));
			$this->tpl->setVariable('ADMINS',$table_gui->getHTML());	
		}
		
		if(count($part->getMembers()))
		{
			if($ilUser->getPref('grp_member_hide'))
			{
				$table_gui = new ilGroupParticipantsTableGUI($this,'member',false,$this->show_tracking);
				$this->ctrl->setParameter($this,'member_hide',0);
				$table_gui->addHeaderCommand($this->ctrl->getLinkTarget($this,'members'),
					$this->lng->txt('show'),
					'',
					ilUtil::getImagePath('edit_add.png'));
				$this->ctrl->clearParameters($this);
			}
			else
			{
				$table_gui = new ilGroupParticipantsTableGUI($this,'member',true,$this->show_tracking);
				$this->ctrl->setParameter($this,'member_hide',1);
				$table_gui->addHeaderCommand($this->ctrl->getLinkTarget($this,'members'),
					$this->lng->txt('hide'),
					'',
					ilUtil::getImagePath('edit_remove.png'));
				$this->ctrl->clearParameters($this);
			}
				
			$table_gui->setTitle($this->lng->txt('grp_members'),'icon_usr.gif',$this->lng->txt('grp_members'));
			$table_gui->setData($this->readMemberData($part->getMembers()));
			$this->tpl->setVariable('MEMBERS',$table_gui->getHTML());	
			
		}
		
		$this->tpl->setVariable('TXT_SELECTED_USER',$this->lng->txt('grp_selected_users'));
		$this->tpl->setVariable('BTN_FOOTER_EDIT',$this->lng->txt('edit'));
		$this->tpl->setVariable('BTN_FOOTER_VAL',$this->lng->txt('remove'));
		$this->tpl->setVariable('BTN_FOOTER_MAIL',$this->lng->txt('grp_mem_send_mail'));
		$this->tpl->setVariable('ARROW_DOWN',ilUtil::getImagePath('arrow_downright.gif'));
		
	}
	
	/**
	 * assign subscribers
	 *
	 * @access public
	 * @return
	 */
	public function assignSubscribersObject()
	{
		global $lng, $ilIliasIniFile,$ilUser;

		$this->checkPermission('write');
		
		if(!count($_POST['subscribers']))
		{
			ilUtil::sendInfo($this->lng->txt('no_checkbox'));
			$this->membersObject();
			return false;
		}
		
		foreach($_POST['subscribers'] as $usr_id)
		{
			$mail = new ilMail($ilUser->getId());

			// XXX - The message should be sent in the language of the receiver,
			// instead of in the language of the current user
			$mail->sendMail(ilObjUser::_lookupLogin($usr_id),"","",
				sprintf($lng->txt('grp_accept_subscriber'), $this->object->getTitle()),
				sprintf(str_replace('\n',"\n",$lng->txt('grp_accept_subscriber_body')), 
						$this->object->getTitle(), $ilIliasIniFile->readVariable('server','http_path').'/goto.php?client_id='.CLIENT_ID.'&target=grp_'.$this->object->getRefId()),
				array(),array('system'));	
			$this->object->members_obj->add($usr_id,IL_GRP_MEMBER);
			$this->object->members_obj->deleteSubscriber($usr_id);
		}
		ilUtil::sendInfo($this->lng->txt("grp_msg_applicants_assigned"));
		$this->membersObject();
		return true;
	}
	
	/**
	 * refuse subscribers
	 *
	 * @access public
	 * @return
	 */
	public function refuseSubscribersObject()
	{
		global $lng;

		$this->checkPermission('write');
		
		if(!count($_POST['subscribers']))
		{
			ilUtil::sendInfo($this->lng->txt('no_checkbox'));
			$this->membersObject();
			return false;
		}
		
		foreach($_POST['subscribers'] as $usr_id)
		{
			$mail = new ilMail($_SESSION["AccountId"]);
			// XXX - The message should be sent in the language of the receiver,
			// instead of in the language of the current user
			$mail->sendMail(ilObjUser::_lookupLogin($usr_id),"","",
				sprintf($lng->txt('grp_reject_subscriber'), $this->object->getTitle()),
				sprintf(str_replace('\n',"\n",$lng->txt('grp_reject_subscriber_body')), 
						$this->object->getTitle()),
				array(),array('system'));	
			$this->object->members_obj->deleteSubscriber($usr_id);
		}
		ilUtil::sendInfo($this->lng->txt("grp_msg_applicants_removed"));
		$this->membersObject();
		return true;
		
	}
	
	/**
	 * add from waiting list 
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function assignFromWaitingListObject()
	{
		$this->checkPermission('write');
		
		if(!count($_POST["waiting"]))
		{
			ilUtil::sendInfo($this->lng->txt("no_checkbox"));
			$this->membersObject();
			return false;
		}
		
		include_once('./Modules/Group/classes/class.ilGroupWaitingList.php');
		$waiting_list = new ilGroupWaitingList($this->object->getId());

		$added_users = 0;
		foreach($_POST["waiting"] as $user_id)
		{
			if(!$tmp_obj = ilObjectFactory::getInstanceByObjId($user_id,false))
			{
				continue;
			}
			if($this->object->members_obj->isAssigned($user_id))
			{
				continue;
			}
			$this->object->members_obj->add($user_id,IL_GRP_MEMBER);
			#$this->object->members_obj->sendNotification($this->object->members_obj->NOTIFY_ACCEPT_USER,$user_id);
			$waiting_list->removeFromList($user_id);

			++$added_users;
		}
		if($added_users)
		{
			ilUtil::sendInfo($this->lng->txt("grp_users_added"));
			$this->membersObject();

			return true;
		}
		else
		{
			ilUtil::sendInfo($this->lng->txt("grp_users_already_assigned"));
			$this->searchObject();

			return false;
		}
	}
	
	/**
	 * refuse from waiting list
	 *
	 * @access public
	 * @return
	 */
	public function refuseFromListObject()
	{
		$this->checkPermission('write');
		
		if(!count($_POST['waiting']))
		{
			ilUtil::sendInfo($this->lng->txt('no_checkbox'));
			$this->membersObject();
			return false;
		}
		
		include_once('./Modules/Group/classes/class.ilGroupWaitingList.php');
		$waiting_list = new ilGroupWaitingList($this->object->getId());

		foreach($_POST["waiting"] as $user_id)
		{
			$waiting_list->removeFromList($user_id);
		}
		
		ilUtil::sendInfo($this->lng->txt('grp_users_removed_from_list'));
		$this->membersObject();
		return true;
	}
	
	/**
	 * delete selected members
	 *
	 * @access public
	 */
	public function confirmDeleteMembersObject()
	{
		$this->checkPermission('write');
		
		$this->setSubTabs('members');
		$this->tabs_gui->setTabActive('members');
		$this->tabs_gui->setSubTabActive('grp_edit_members');
		
		if(!count($_POST['admins']) and !count($_POST['members']))
		{
			ilUtil::sendInfo($this->lng->txt('no_checkbox'));
			$this->membersObject();
			return true;
		}
		
		include_once('./Services/Utilities/classes/class.ilConfirmationGUI.php');
		$confirm = new ilConfirmationGUI();
		$confirm->setFormAction($this->ctrl->getFormAction($this,'deleteMembers'));
		$confirm->setHeaderText($this->lng->txt('grp_dismiss_member'));
		$confirm->setConfirm($this->lng->txt('confirm'),'deleteMembers');
		$confirm->setCancel($this->lng->txt('cancel'),'members');
		
		foreach($this->readMemberData(array_merge((array) $_POST['admins'],(array) $_POST['members'])) as $participants)
		{
			$confirm->addItem('participants[]',
				$participants['usr_id'],
				$participants['lastname'].', '.$participants['firstname'].' ['.$participants['login'].']',
				ilUtil::getImagePath('icon_usr.gif'));
		}
		
		$this->tpl->setContent($confirm->getHTML());
	}
	
	/**
	 * delete members
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function deleteMembersObject()
	{
		$this->checkPermission('write');
		
		if(!count($_POST['participants']))
		{
			ilUtil::sendInfo($this->lng->txt('no_checkbox'));
			$this->membersObject();
			return true;
		}
	
		$this->object->members_obj->deleteParticipants($_POST['participants']);
		ilUtil::sendInfo($this->lng->txt("grp_msg_membership_annulled"));
		$this->membersObject();
		return true;
	}
	
	/**
	 * show send mail
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function sendMailToSelectedUsersObject()
	{
		if(isset($_GET['member_id']))
		{
			$_POST['participants'] = array($_GET['member_id']);
		}
		else
		{
			$_POST['participants'] = array_unique(array_merge((array) $_POST['admins'],
				(array) $_POST['members'],
				(array) $_POST['waiting'],
				(array) $_POST['subscribers']));
		}
		if (!count($_POST['participants']))
		{
			ilUtil::sendInfo($this->lng->txt("no_checkbox"));
			$this->membersObject();
			return false;
		}
		foreach($_POST['participants'] as $usr_id)
		{
			$rcps[] = ilObjUser::_lookupLogin($usr_id);
		}
		ilUtil::redirect("ilias.php?baseClass=ilmailgui&type=new&rcp_to=".implode(',',$rcps));
		return true;
	}
	
	/**
	 * set preferences (show/hide tabel content)
	 *
	 * @access public
	 * @return
	 */
	public function setShowHidePrefs()
	{
		global $ilUser;
		
		if(isset($_GET['admin_hide']))
		{
			$ilUser->writePref('grp_admin_hide',(int) $_GET['admin_hide']);
		}
		if(isset($_GET['member_hide']))
		{
			$ilUser->writePref('grp_member_hide',(int) $_GET['member_hide']);
		}
		if(isset($_GET['subscriber_hide']))
		{
			$ilUser->writePref('grp_subscriber_hide',(int) $_GET['subscriber_hide']);
		}
		if(isset($_GET['wait_hide']))
		{
			$ilUser->writePref('grp_wait_hide',(int) $_GET['wait_hide']);
		}
	}
	
	/**
	 * edit one member 
	 *
	 * @access public
	 */
	public function editMemberObject()
	{
		$_POST['members'] = array((int) $_GET['member_id']);
		$this->editMembersObject();
	}
	
	/**
	 * edit member(s)
	 *
	 * @access public
	 * @return
	 */
	public function editMembersObject()
	{
		$this->checkPermission('write');
		
		$participants = array_unique(array_merge((array) $_POST['admins'],(array) $_POST['members']));
		
		if(!count($participants))
		{
			ilUtil::sendInfo($this->lng->txt('no_checkbox'));
			$this->membersObject();
			return false;
		}
		
		$this->setSubTabs('members');
		$this->tabs_gui->setTabActive('members');
		$this->tabs_gui->setSubTabActive('grp_edit_members');
		
		include_once('./Modules/Group/classes/class.ilGroupEditParticipantsTableGUI.php');
		$table_gui = new ilGroupEditParticipantsTableGUI($this);
		$table_gui->setTitle($this->lng->txt('grp_mem_change_status'),'icon_usr.gif',$this->lng->txt('grp_mem_change_status'));
		$table_gui->setData($this->readMemberData($participants));

		$this->tpl->setContent($table_gui->getHTML());
		return true;
	}
	
	/**
	 * update members
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function updateMembersObject()
	{
		$this->checkPermission('write');
		
		if(!count($_POST['participants']))
		{
			ilUtil::sendInfo($this->lng->txt('no_checkbox'));
			$this->membersObject();
			return false;
		}
		
		$notifications = $_POST['notification'] ? $_POST['notification'] : array();
		foreach($_POST['participants'] as $usr_id)
		{
			// TODO: check no role, owner, self status changed
			$this->object->members_obj->updateRoleAssignments($usr_id,(array) $_POST['roles'][$usr_id]);
			
			// Disable notification for all of them
			$this->object->members_obj->updateNotification($usr_id,0);
			
			if($this->object->members_obj->isAdmin($usr_id) and in_array($usr_id,$notifications))
			{
				$this->object->members_obj->updateNotification($usr_id,1);
			}
		}
		ilUtil::sendInfo($this->lng->txt("msg_obj_modified"));
		$this->membersObject();
		return true;		
	}
	
	/**
	 * update status 
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function updateStatusObject()
	{
		$this->checkPermission('write');
		
		$notification = $_POST['notification'] ? $_POST['notification'] : array();
		foreach($this->object->members_obj->getAdmins() as $admin_id)
		{
			$this->object->members_obj->updateNotification($admin_id,(int) in_array($admin_id,$notification));
		}
		ilUtil::sendInfo($this->lng->txt('settings_saved'));
		$this->membersObject();
	}
	


	function listExportFilesObject()
	{
		global $rbacsystem;

		$this->tabs_gui->setTabActive('export');

		$this->lng->loadLanguageModule('content');

		if (!$rbacsystem->checkAccess("write",$this->object->getRefId()))
		{
			$this->ilErr->raiseError($this->lng->txt("permission_denied"),$this->ilErr->MESSAGE);
		}

		$this->tpl->addBlockfile("BUTTONS", "buttons", "tpl.buttons.html");
		$this->__exportMenu();

		$this->object->__initFileObject();
		$export_files = $this->object->file_obj->getExportFiles();
		
		require_once("./Services/Table/classes/class.ilTableGUI.php");
		$tbl = new ilTableGUI();

		$this->tpl->addBlockfile("ADM_CONTENT", "adm_content", "tpl.table.html");
		$this->tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.grp_export_file_row.html");

		$num = 0;

		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));

		$tbl->setTitle($this->lng->txt("cont_export_files"));
		$tbl->setHeaderNames(array("", $this->lng->txt("type"),
			$this->lng->txt("cont_file"),
			$this->lng->txt("cont_size"), $this->lng->txt("date") ));

		$cols = array("", "type", "file", "size", "date");
		$header_params = array("ref_id" => $_GET["ref_id"],
							   "cmd" => "listExportFiles", "cmdClass" => strtolower(get_class($this)));
		$tbl->setHeaderVars($cols, $header_params);
		$tbl->setColumnWidth(array("1%", "9%", "40%", "25%", "25%"));
		
		// control
		$tbl->setOrderColumn($_GET["sort_by"]);
		$tbl->setOrderDirection($_GET["sort_order"]);
		$tbl->setLimit($_GET["limit"]);
		$tbl->setOffset($_GET["offset"]);
		$tbl->setMaxCount($this->maxcount);		// ???
		$tbl->disable("sort");

		$this->tpl->setVariable("COLUMN_COUNTS", 5);

		// delete button
		$this->tpl->setVariable("IMG_ARROW", ilUtil::getImagePath("arrow_downright.gif"));
		$this->tpl->setCurrentBlock("tbl_action_btn");
		$this->tpl->setVariable("BTN_NAME", "confirmDeleteExportFile");
		$this->tpl->setVariable("BTN_VALUE", $this->lng->txt("delete"));
		$this->tpl->parseCurrentBlock();

		$this->tpl->setCurrentBlock("tbl_action_btn");
		$this->tpl->setVariable("BTN_NAME", "downloadExportFile");
		$this->tpl->setVariable("BTN_VALUE", $this->lng->txt("download"));
		$this->tpl->parseCurrentBlock();

		// footer
		$tbl->setFooter("tblfooter",$this->lng->txt("previous"),$this->lng->txt("next"));

		$tbl->setMaxCount(count($export_files));
		$export_files = array_slice($export_files, $_GET["offset"], $_GET["limit"]);
		$tbl->render();
		foreach($export_files as $exp_file)
		{
			$this->tpl->setCurrentBlock("tbl_content");
			$this->tpl->setVariable("TXT_FILENAME", $exp_file["file"]);
			
			$css_row = ilUtil::switchColor($i++, "tblrow1", "tblrow2");
			$this->tpl->setVariable("CSS_ROW", $css_row);

			$this->tpl->setVariable("TXT_SIZE", $exp_file["size"]);
			$this->tpl->setVariable("TXT_TYPE", $exp_file["type"]);
			$this->tpl->setVariable("CHECKBOX_ID",$exp_file["file"]);

			$file_arr = explode("__", $exp_file["file"]);
			$this->tpl->setVariable('TXT_DATE',ilDatePresentation::formatDate(new ilDateTime($file_arr[0],IL_CAL_UNIX)));


			$this->tpl->parseCurrentBlock();
		}
		if(!count($export_files))
		{
			$tbl->disable('footer');
			$this->tpl->setCurrentBlock("notfound");
			$this->tpl->setVariable("TXT_OBJECT_NOT_FOUND", $this->lng->txt("obj_not_found"));
			$this->tpl->setVariable("NUM_COLS", 4);
			$this->tpl->parseCurrentBlock();
		}

		$this->tpl->parseCurrentBlock();
	}

	function __exportMenu()
	{
		// create xml export file button
		$this->tpl->setCurrentBlock("btn_cell");
		$this->tpl->setVariable("BTN_LINK", $this->ctrl->getLinkTarget($this, "exportXML"));
		$this->tpl->setVariable("BTN_TXT", $this->lng->txt("cont_create_export_file_xml"));
		$this->tpl->parseCurrentBlock();
	}

	function exportXMLObject()
	{
		global $rbacsystem;

		if (!$rbacsystem->checkAccess("write",$this->object->getRefId()))
		{
			$this->ilErr->raiseError($this->lng->txt("permission_denied"),$this->ilErr->MESSAGE);
		}

		$this->object->exportXML();
		
		$this->listExportFilesObject();

		return true;
	}

	function confirmDeleteExportFileObject()
	{
		global $rbacsystem;

		if (!$rbacsystem->checkAccess("write",$this->object->getRefId()))
		{
			$this->ilErr->raiseError($this->lng->txt("permission_denied"),$this->ilErr->MESSAGE);
		}

		if(!count($_POST['file']))
		{
			ilUtil::sendInfo($this->lng->txt('grp_select_one_file'));
		}
		else
		{
			$this->object->deleteExportFiles($_POST['file']);
			ilUtil::sendInfo($this->lng->txt('grp_deleted_export_files'));
		}

		$this->listExportFilesObject();

		return true;
	}

	function downloadExportFileObject()
	{
		if(!count($_POST['file']))
		{
			ilUtil::sendInfo($this->lng->txt('grp_select_one_file'));
			$this->listExportFilesObject();
			return false;
		}
		if(count($_POST['file']) > 1)
		{
			ilUtil::sendInfo($this->lng->txt('grp_select_one_file_only'));
			$this->listExportFilesObject();
			return false;
		}
		
		$this->object->downloadExportFile(ilUtil::stripSlashes($_POST['file'][0]));
		
		// If file wasn't sent
		ilUtil::sendInfo($this->lng->txt('grp_error_sending_file'));
		
		return true;
	}
			



	/**
	* canceledObject is called when operation is canceled, method links back
	* @access	public
	*/
	function canceledObject()
	{
		$return_location = $_GET["cmd_return_location"];
		if (strcmp($return_location, "") == 0)
		{
			$return_location = "";
		}

		ilUtil::sendInfo($this->lng->txt("action_aborted"),true);
		$this->ctrl->redirect($this, $return_location);
	}



	/**
	* leave Group
	* @access public
	*/
	public function leaveObject()
	{
		$this->checkPermission('leave');
		
		$this->tabs_gui->setTabActive('grp_btn_unsubscribe');
		
		$tpl = new ilTemplate('tpl.unsubscribe.html',true,true,'Modules/Group');
		$tpl->setVariable('UNSUB_FORMACTION',$this->ctrl->getFormAction($this));
		$tpl->setVariable('TXT_SUBMIT',$this->lng->txt('grp_btn_unsubscribe'));
		$tpl->setVariable('TXT_CANCEL',$this->lng->txt('cancel'));
		
		ilUtil::sendInfo($this->lng->txt('grp_dismiss_myself'));
		$this->tpl->setContent($tpl->get());		
	}
	
	/**
	 * unsubscribe from group
	 *
	 * @access public
	 * @return
	 */
	public function unsubscribeObject()
	{
		global $ilUser,$tree;
		
		$this->checkPermission('leave');
		
		$this->object->members_obj->delete($ilUser->getId());
		
		ilUtil::sendInfo($this->lng->txt('grp_msg_membership_annulled'));
		ilUtil::redirect('repository.php?ref_id='.$tree->getParentId($this->object->getRefId()));
	}
	

	/**
	* displays confirmation formular with users that shall be assigned to group
	* @access public
	*/
	function assignMemberObject()
	{
		$user_ids = $_POST["id"];

		if (empty($user_ids[0]))
		{
			// TODO: jumps back to grp content. go back to last search result
			$this->ilErr->raiseError($this->lng->txt("no_checkbox"),$this->ilErr->MESSAGE);
		}

		foreach ($user_ids as $new_member)
		{
			if (!$this->object->addMember($new_member,$this->object->getDefaultMemberRole()))
			{
				$this->ilErr->raiseError("An Error occured while assigning user to group !",$this->ilErr->MESSAGE);
			}
		}

		unset($_SESSION["saved_post"]);

		ilUtil::sendInfo($this->lng->txt("grp_msg_member_assigned"),true);
		ilUtil::redirect($this->ctrl->getLinkTarget($this,"members"));
	}

	/**
	* displays confirmation formular with users that shall be assigned to group
	* @access public
	*/
	function addUserObject()
	{
		$user_ids = $_POST["user"];
		
		$mail = new ilMail($_SESSION["AccountId"]);

		if (empty($user_ids[0]))
		{
			// TODO: jumps back to grp content. go back to last search result
			#$this->ilErr->raiseError($this->lng->txt("no_checkbox"),$this->ilErr->MESSAGE);
			ilUtil::sendInfo($this->lng->txt("no_checkbox"));
		
			return false;
		}

		foreach ($user_ids as $new_member)
		{
			if (!$this->object->addMember($new_member,$this->object->getDefaultMemberRole()))
			{
				$this->ilErr->raiseError("An Error occured while assigning user to group !",$this->ilErr->MESSAGE);
			}
			
			$user_obj = $this->ilias->obj_factory->getInstanceByObjId($new_member);
		
			// SEND A SYSTEM MESSAGE EACH TIME A MEMBER IS ADDED TO THE GROUP
			$user_obj->addDesktopItem($this->object->getRefId(),"grp");
			$mail->sendMail($user_obj->getLogin(),"","",$this->lng->txtlng("common","grp_mail_subj_new_subscription",$user_obj->getLanguage()).": ".$this->object->getTitle(),$this->lng->txtlng("common","grp_mail_body_new_subscription",$user_obj->getLanguage()),array(),array('system'));	

			unset($user_obj);
		}
		
		unset($_SESSION["saved_post"]);
		unset($_SESSION['grp_usr_search_result']);

		ilUtil::sendInfo($this->lng->txt("grp_msg_member_assigned"),true);
		ilUtil::redirect($this->ctrl->getLinkTarget($this,"members"));
	}

	/**
	* Form for mail to group members
	*/
	function mailMembersObject()
	{
		global $rbacreview, $ilObjDataCache;
		include_once('./Services/AccessControl/classes/class.ilObjRole.php');

		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.mail_members.html',"Services/Mail");

		$this->setSubTabs('members');
		$this->tabs_gui->setTabActive('members');

		$this->tpl->setVariable("MAILACTION",'ilias.php?baseClass=ilMailGUI&type=role');
		$this->tpl->setVariable("IMG_ARROW",ilUtil::getImagePath('arrow_downright.gif'));
		$this->tpl->setVariable("TXT_MARKED_ENTRIES",$this->lng->txt('marked_entries'));
		$this->tpl->setVariable("OK",$this->lng->txt('ok'));
		
		// Get role mailbox addresses
		$role_folder = $rbacreview->getRoleFolderOfObject($this->object->getRefId());
		$role_ids = $rbacreview->getRolesOfRoleFolder($role_folder['ref_id'], false);
		$role_addrs = array();
		foreach ($role_ids as $role_id)
		{
			$this->tpl->setCurrentBlock("mailbox_row");
			$role_addr = $rbacreview->getRoleMailboxAddress($role_id);
			$this->tpl->setVariable("CHECK_MAILBOX",ilUtil::formCheckbox(1,'roles[]',
					htmlspecialchars($role_addr)
			));

			if (ilMail::_usePearMail())
			{
				// if pear mail is enabled, mailbox addresses are already localized in the language of the user
				$this->tpl->setVariable("MAILBOX",$role_addr);
			}
			else
			{
				// if pear mail is not enabled, we need to localize mailbox addresses in the language of the user
				$this->tpl->setVariable("MAILBOX",ilObjRole::_getTranslation($ilObjDataCache->lookupTitle($role_id)) . " (".$role_addr.")");
			}
			$this->tpl->parseCurrentBlock();
		}
	}

	/**
	* adds applicant to group as member
	* @access	public
	*/
	function refuseApplicantsObject()
	{
		$user_ids = $_POST["user_id"];

		if (empty($user_ids[0]))
		{
			$this->ilErr->raiseError($this->lng->txt("no_checkbox"),$this->ilErr->MESSAGE);
		}

		$mail = new ilMail($_SESSION["AccountId"]);

		foreach ($user_ids as $new_member)
		{
			$user =& $this->ilias->obj_factory->getInstanceByObjId($new_member);

			$this->object->deleteApplicationListEntry($new_member);
			$mail->sendMail($user->getLogin(),"","","Membership application refused: Group ".$this->object->getTitle(),"Your application has been refused.",array(),array('system'));
		}

		ilUtil::sendInfo($this->lng->txt("grp_msg_applicants_removed"),true);
		ilUtil::redirect($this->ctrl->getLinkTarget($this,"members"));
	}

	// get tabs
	function getTabs(&$tabs_gui)
	{
		global $rbacsystem,$ilUser,$ilAccess;

		if ($rbacsystem->checkAccess('read',$this->ref_id))
		{
			$force_active = (($_GET["cmd"] == "view" || $_GET["cmd"] == "")
				&& $_GET["cmdClass"] == "")
				? true
				: false;
			$tabs_gui->addTarget("view_content",
				$this->ctrl->getLinkTarget($this, ""), array("", "view","addToDesk","removeFromDesk"), get_class($this),
				"", $force_active);
		}
		if ($rbacsystem->checkAccess('visible',$this->ref_id))
		{
			$tabs_gui->addTarget("info_short",
								 $this->ctrl->getLinkTargetByClass(
								 array("ilobjgroupgui", "ilinfoscreengui"), "showSummary"),
								 "infoScreen",
								 "", "",false);
		}


		if ($ilAccess->checkAccess('write','',$this->object->getRefId()))
		{
			$tabs_gui->addTarget("settings",
				$this->ctrl->getLinkTarget($this, "edit"), array("edit", "editMapSettings"), get_class($this),
				"");
		}

		// Members
		if($ilAccess->checkAccess('read','',$this->object->getRefId()))
		{
			$mem_cmd = $ilAccess->checkAccess('write','',$this->ref_id) ? "members" : "membersGallery";
			$tabs_gui->addTarget("members",$this->ctrl->getLinkTarget($this, $mem_cmd), array(),get_class($this));
		}


		// learning progress
		include_once './Services/Tracking/classes/class.ilLearningProgressAccess.php';
		if(ilLearningProgressAccess::checkAccess($this->object->getRefId()))
		{
			$tabs_gui->addTarget('learning_progress',
								 $this->ctrl->getLinkTargetByClass(array('ilobjgroupgui','illearningprogressgui'),''),
								 '',
								 array('illplistofobjectsgui','illplistofsettingsgui','illearningprogressgui','illplistofprogressgui'));
		}
		
		if ($rbacsystem->checkAccess('write',$this->object->getRefId()))
		{
			$tabs_gui->addTarget('export',
								 $this->ctrl->getLinkTarget($this,'listExportFiles'),
								 array('listExportFiles','exportXML','confirmDeleteExportFile','downloadExportFile'),
								 get_class($this));
		}
		
		// parent tabs (all container: edit_permission, clipboard, trash
		parent::getTabs($tabs_gui);

		if($ilAccess->checkAccess('join','',$this->object->getRefId()) and
			!$this->object->members_obj->isAssigned($ilUser->getId()))
		{
			$tabs_gui->addTarget("join",
								 $this->ctrl->getLinkTargetByClass('ilgroupregistrationgui', "show"), 
								 '',
								 "");
		}
		if($ilAccess->checkAccess('leave','',$this->object->getRefId()) and
			$this->object->members_obj->isMember($ilUser->getId()))
		{
			$tabs_gui->addTarget("grp_btn_unsubscribe",
								 $this->ctrl->getLinkTarget($this, "leave"), 
								 '',
								 "");
		}
	}


	// IMPORT FUNCTIONS

	function importFileObject()
	{
		if(!is_array($_FILES['xmldoc']))
		{
			ilUtil::sendInfo($this->lng->txt("import_file_not_valid"));
			$this->createObject();
			return false;
		}
		
		include_once './Modules/Group/classes/class.ilObjGroup.php';

		if($ref_id = ilObjGroup::_importFromFile($_FILES['xmldoc'],(int) $_GET['ref_id']))
		{
			$this->ctrl->setParameter($this, "ref_id", $ref_id);
			ilUtil::sendInfo($this->lng->txt("import_grp_finished"),true);
			ilUtil::redirect($this->ctrl->getLinkTarget($this,'edit'));
		}
		
		ilUtil::sendInfo($this->lng->txt("import_file_not_valid"));
		$this->createObject();
	}	

	// Methods for ConditionHandlerInterface
	function initConditionHandlerGUI($item_id)
	{
		include_once './classes/class.ilConditionHandlerInterface.php';

		if(!is_object($this->chi_obj))
		{
			if($_GET['item_id'])
			{
				$this->chi_obj =& new ilConditionHandlerInterface($this,$item_id);
				$this->ctrl->saveParameter($this,'item_id',$_GET['item_id']);
			}
			else
			{
				$this->chi_obj =& new ilConditionHandlerInterface($this);
			}
		}
		return true;
	}

	
/**
* Creates the output form for group member export
*
* Creates the output form for group member export
*
*/
	function exportObject()
	{
		$this->tpl->addBlockfile("ADM_CONTENT", "adm_content", "tpl.grp_members_export.html");
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("FORMACTION", $this->getFormAction("export",$this->ctrl->getFormAction($this)));
		$this->tpl->setVariable("BUTTON_EXPORT", $this->lng->txt("export_group_members"));
		$this->tpl->parseCurrentBlock();
	}
	
/**
* Exports group members to Microsoft Excel file
*
* Exports group members to Microsoft Excel file
*
*/
	function exportMembersObject()
	{
		$title = preg_replace("/\s/", "_", $this->object->getTitle());
		include_once "./classes/class.ilExcelWriterAdapter.php";
		$adapter = new ilExcelWriterAdapter("export_" . $title . ".xls");
		$workbook = $adapter->getWorkbook();
		// Creating a worksheet
		$format_bold =& $workbook->addFormat();
		$format_bold->setBold();
		$format_percent =& $workbook->addFormat();
		$format_percent->setNumFormat("0.00%");
		$format_datetime =& $workbook->addFormat();
		$format_datetime->setNumFormat("DD/MM/YYYY hh:mm:ss");
		$format_title =& $workbook->addFormat();
		$format_title->setBold();
		$format_title->setColor('black');
		$format_title->setPattern(1);
		$format_title->setFgColor('silver');
		$worksheet =& $workbook->addWorksheet();
		$column = 0;
		$profile_data = array("email", "gender", "firstname", "lastname", "person_title", "institution", 
			"department", "street", "zipcode","city", "country", "phone_office", "phone_home", "phone_mobile",
			"fax", "matriculation");
		foreach ($profile_data as $data)
		{
			$worksheet->writeString(0, $column++, $this->cleanString($this->lng->txt($data)), $format_title);
		}
		$member_ids = $this->object->getGroupMemberIds();
		$row = 1;
		foreach ($member_ids as $member_id)
		{
			$column = 0;
			$member =& $this->ilias->obj_factory->getInstanceByObjId($member_id);
			if ($member->getPref("public_email")=="y")
			{
				$worksheet->writeString($row, $column++, $this->cleanString($member->getEmail()));
			}
			else
			{
				$column++;
			}
			$worksheet->writeString($row, $column++, $this->cleanString($this->lng->txt("gender_" . $member->getGender())));
			$worksheet->writeString($row, $column++, $this->cleanString($member->getFirstname()));
			$worksheet->writeString($row, $column++, $this->cleanString($member->getLastname()));
			$worksheet->writeString($row, $column++, $this->cleanString($member->getUTitle()));
			if ($member->getPref("public_institution")=="y")
			{
				$worksheet->writeString($row, $column++, $this->cleanString($member->getInstitution()));
			}
			else
			{
				$column++;
			}
			if ($member->getPref("public_department")=="y")
			{
				$worksheet->writeString($row, $column++, $this->cleanString($member->getDepartment()));
			}
			else
			{
				$column++;
			}
			if ($member->getPref("public_street")=="y")
			{
				$worksheet->writeString($row, $column++, $this->cleanString($member->getStreet()));
			}
			else
			{
				$column++;
			}
			if ($member->getPref("public_zip")=="y")
			{
				$worksheet->writeString($row, $column++, $this->cleanString($member->getZipcode()));
			}
			else
			{
				$column++;
			}
			if ($member->getPref("public_city")=="y")
			{
				$worksheet->writeString($row, $column++, $this->cleanString($member->getCity()));
			}
			else
			{
				$column++;
			}
			if ($member->getPref("public_country")=="y")
			{
				$worksheet->writeString($row, $column++, $this->cleanString($member->getCountry()));
			}
			else
			{
				$column++;
			}
			if ($member->getPref("public_phone_office")=="y")
			{
				$worksheet->writeString($row, $column++, $this->cleanString($member->getPhoneOffice()));
			}
			else
			{
				$column++;
			}
			if ($member->getPref("public_phone_home")=="y")
			{
				$worksheet->writeString($row, $column++, $this->cleanString($member->getPhoneHome()));
			}
			else
			{
				$column++;
			}
			if ($member->getPref("public_phone_mobile")=="y")
			{
				$worksheet->writeString($row, $column++, $this->cleanString($member->getPhoneMobile()));
			}
			else
			{
				$column++;
			}
			if ($member->getPref("public_fax")=="y")
			{
				$worksheet->writeString($row, $column++, $this->cleanString($member->getFax()));
			}
			else
			{
				$column++;
			}
			if ($member->getPref("public_matriculation")=="y")
			{
				$worksheet->writeString($row, $column++, $this->cleanString($member->getMatriculation()));
			}
			else
			{
				$column++;
			}
			$row++;
		}
		$workbook->close();
	}
	
	/**
	* Clean output string from german umlauts
	*
	* Clean output string from german umlauts. Replaces �� -> ae etc.
	*
	* @param string $str String to clean
	* @return string Cleaned string
	*/
	function cleanString($str)
	{
		return str_replace(array("��","��","��","��","��","��","��"), array("ae","oe","ue","ss","Ae","Oe","Ue"), $str);
	}

	/**
	* set sub tabs
	*/


	/**
	* this one is called from the info button in the repository
	* not very nice to set cmdClass/Cmd manually, if everything
	* works through ilCtrl in the future this may be changed
	*/
	function infoScreenObject()
	{
		$this->ctrl->setCmd("showSummary");
		$this->ctrl->setCmdClass("ilinfoscreengui");
		$this->infoScreen();
	}
	
	/**
	* show information screen
	*/
	function infoScreen()
	{
		global $rbacsystem;
		
		$this->tabs_gui->setTabActive('info_short');

		if(!$rbacsystem->checkAccess("visible", $this->ref_id))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_read"),$this->ilias->error_obj->MESSAGE);
		}

		include_once("./Services/InfoScreen/classes/class.ilInfoScreenGUI.php");
		$info = new ilInfoScreenGUI($this);
		
		if(strlen($this->object->getInformation()))
		{
			$info->addSection($this->lng->txt('grp_general_informations'));
			$info->addProperty($this->lng->txt('grp_information'), nl2br(
								ilUtil::makeClickable ($this->object->getInformation(), true)));
		}

		$info->enablePrivateNotes();
		$info->enableLearningProgress(true);

		$info->addSection($this->lng->txt('group_registration'));
		$info->showLDAPRoleGroupMappingInfo();
		
		if(!$this->object->isRegistrationEnabled())
		{
			$info->addProperty($this->lng->txt('group_registration_mode'),
				$this->lng->txt('grp_reg_deac_info_screen'));
			
		}
		else
		{
			switch($this->object->getRegistrationType())
			{
				case GRP_REGISTRATION_DIRECT:
					$info->addProperty($this->lng->txt('group_registration_mode'),
									   $this->lng->txt('grp_reg_direct_info_screen'));
					break;
													   
				case GRP_REGISTRATION_REQUEST:
					$info->addProperty($this->lng->txt('group_registration_mode'),
									   $this->lng->txt('grp_reg_req_info_screen'));
					break;
	
				case GRP_REGISTRATION_PASSWORD:
					$info->addProperty($this->lng->txt('group_registration_mode'),
									   $this->lng->txt('grp_reg_passwd_info_screen'));
					break;
					
			}
			/*			
			$info->addProperty($this->lng->txt('group_registration_time'),
				ilDatePresentation::formatPeriod(
					$this->object->getRegistrationStart(),
					$this->object->getRegistrationEnd()));
			*/
			if($this->object->isRegistrationUnlimited())
			{
				$info->addProperty($this->lng->txt('group_registration_time'),
					$this->lng->txt('grp_registration_unlimited'));
			}
			elseif($this->object->getRegistrationStart()->getUnixTime() < time())
			{
				$info->addProperty($this->lng->txt("group_registration_time"),
								   $this->lng->txt('cal_until').' '.
								   ilDatePresentation::formatDate($this->object->getRegistrationEnd()));
			}
			elseif($this->object->getRegistrationStart()->getUnixTime() >= time())
			{
				$info->addProperty($this->lng->txt("group_registration_time"),
								   $this->lng->txt('cal_from').' '.
								   ilDatePresentation::formatDate($this->object->getRegistrationStart()));
			}
			if ($this->object->isMembershipLimited()) 
			{
				$info->addProperty($this->lng->txt("mem_free_places"),
								   max(0,$this->object->getMaxMembers() - $this->object->members_obj->getCountMembers()));
				
			}

		}

		// forward the command
		$this->ctrl->forwardCommand($info);
	}

	/**
	* goto target group
	*/
	function _goto($a_target)
	{
		global $ilAccess, $ilErr, $lng;

		if ($ilAccess->checkAccess("read", "", $a_target))
		{
			$_GET["cmd"] = "frameset";
			$_GET["ref_id"] = $a_target;
			include("repository.php");
			exit;
		}
		else
		{
			// to do: force flat view
			if ($ilAccess->checkAccess("visible", "", $a_target))
			{
				$_GET["cmd"] = "infoScreen";
				$_GET["ref_id"] = $a_target;
				include("repository.php");
				exit;
			}
			else
			{
				if ($ilAccess->checkAccess("read", "", ROOT_FOLDER_ID))
				{
					$_GET["cmd"] = "frameset";
					$_GET["target"] = "";
					$_GET["ref_id"] = ROOT_FOLDER_ID;
					ilUtil::sendInfo(sprintf($lng->txt("msg_no_perm_read_item"),
						ilObject::_lookupTitle(ilObject::_lookupObjId($a_target))), true);
					include("repository.php");
					exit;
				}
			}
		}
		$ilErr->raiseError($lng->txt("msg_no_perm_read"), $ilErr->FATAL);
	}

	
	/**
	 * init create/edit form
	 *
	 * @access protected
	 * @param string edit or create
	 * @return
	 */
	protected function initForm($a_mode = 'edit')
	{
		global $ilUser;
		
		if(is_object($this->form))
		{
			return true;
		}
	
		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		
		$this->form = new ilPropertyFormGUI();
		$this->form->setTableWidth('60%');
		$this->form->setFormAction($this->ctrl->getFormAction($this));
		
		// title
		$title = new ilTextInputGUI($this->lng->txt('title'),'title');
		$title->setValue($this->object->getTitle());
		$title->setSize(40);
		$title->setMaxLength(128);
		$title->setRequired(true);
		$this->form->addItem($title);
		
		// desc
		$desc = new ilTextAreaInputGUI($this->lng->txt('description'),'desc');
		$desc->setValue($this->object->getLongDescription());
		$desc->setRows(2);
		$desc->setCols(40);
		$this->form->addItem($desc);
		
		// Group type
		$grp_type = new ilRadioGroupInputGUI($this->lng->txt('grp_typ'),'grp_type');
		
		if($a_mode == 'edit')
		{
			$type = ($this->object->getGroupType() ? $this->object->getGroupType() : $this->object->readGroupStatus());
		}
		else
		{
			$type = ($this->object->getGroupType() ? $this->object->getGroupType() : GRP_TYPE_PUBLIC);
		}
		
		$grp_type->setValue($type);
		$grp_type->setRequired(true);

		// OPEN GROUP
		#$opt_open = new ilRadioOption($this->lng->txt('grp_open'),GRP_TYPE_OPEN,$this->lng->txt('grp_open_info'));
		#$grp_type->addOption($opt_open);
		
		
		// PUBLIC GROUP
		$opt_public = new ilRadioOption($this->lng->txt('grp_public'),GRP_TYPE_PUBLIC,$this->lng->txt('grp_public_info'));
		$grp_type->addOption($opt_public);
		
		// Registration type
		$reg_type = new ilRadioGroupInputGUI('','registration_type');
		$reg_type->setValue($this->object->getRegistrationType());
		
		$opt_dir = new ilRadioOption($this->lng->txt('grp_reg_direct'),GRP_REGISTRATION_DIRECT);#$this->lng->txt('grp_reg_direct_info'));
		$reg_type->addOption($opt_dir);

		$opt_pass = new ilRadioOption($this->lng->txt('grp_pass_request'),GRP_REGISTRATION_PASSWORD);
		$pass = new ilTextInputGUI('','password');
		$pass->setInfo($this->lng->txt('grp_reg_password_info'));
		$pass->setValue($this->object->getPassword());
		$pass->setSize(10);
		$pass->setMaxLength(32);
		$opt_pass->addSubItem($pass);
		$reg_type->addOption($opt_pass);
		$opt_public->addSubItem($reg_type);

		$opt_req = new ilRadioOption($this->lng->txt('grp_reg_request'),GRP_REGISTRATION_REQUEST,$this->lng->txt('grp_reg_request_info'));
		$reg_type->addOption($opt_req);
		
		$opt_deact = new ilRadioOption($this->lng->txt('grp_reg_disabled'),GRP_REGISTRATION_DEACTIVATED,$this->lng->txt('grp_reg_disabled_info'));
		$reg_type->addOption($opt_deact);

		// CLOSED GROUP
		$opt_closed = new ilRadioOption($this->lng->txt('grp_closed'),GRP_TYPE_CLOSED,$this->lng->txt('grp_closed_info'));
		$grp_type->addOption($opt_closed);
		if($a_mode == 'update_group_type')
		{
			$grp_type->setAlert($this->lng->txt('grp_type_changed_info'));
		}
		$this->form->addItem($grp_type);
		
		// time limit
		$time_limit = new ilCheckboxInputGUI($this->lng->txt('grp_reg_limited'),'reg_limit_time');
		$time_limit->setOptionTitle($this->lng->txt('grp_reg_limit_time'));
		$time_limit->setChecked($this->object->isRegistrationUnlimited() ? false : true);
		
			$start = new ilDateTimeInputGUI($this->lng->txt('grp_reg_start'),'registration_start');
			$start->setShowTime(true);
			$start->setDate($this->object->getRegistrationStart());
			$time_limit->addSubItem($start);
			
			$end = new ilDateTimeInputGUI($this->lng->txt('grp_reg_end'),'registration_end');
			$end->setShowTime(true);
			$end->setDate($this->object->getRegistrationEnd());
			
			$time_limit->addSubItem($end);
		
		$this->form->addItem($time_limit);
				
			// max member
		$lim = new ilCheckboxInputGUI($this->lng->txt('reg_grp_max_members_short'),'registration_membership_limited');
		$lim->setValue(1);
		$lim->setOptionTitle($this->lng->txt('reg_grp_max_members'));
		$lim->setChecked($this->object->isMembershipLimited());
			
			
			$max = new ilTextInputGUI('','registration_max_members');
				$max->setValue($this->object->getMaxMembers() ? $this->object->getMaxMembers() : '');
				$max->setTitle($this->lng->txt('members').':');
				$max->setSize(3);
				$max->setMaxLength(4);
				$max->setInfo($this->lng->txt('grp_reg_max_members_info'));
		$lim->addSubItem($max);		
		
			$wait = new ilCheckboxInputGUI('','waiting_list');
			$wait->setValue(1);
			$wait->setOptionTitle($this->lng->txt('grp_waiting_list'));
			$wait->setInfo($this->lng->txt('grp_waiting_list_info'));
			$wait->setChecked($this->object->isWaitingListEnabled() ? true : false);
		$lim->addSubItem($wait);
		$this->form->addItem($lim);
		
		switch($a_mode)
		{
			case 'create':
				$this->form->setTitle($this->lng->txt('grp_new'));
				$this->form->setTitleIcon(ilUtil::getImagePath('icon_grp_s.gif'));
		
				$this->form->addCommandButton('save',$this->lng->txt('grp_new'));
				$this->form->addCommandButton('cancel',$this->lng->txt('cancel'));
				return true;
			
			case 'edit':
				$this->form->setTitle($this->lng->txt('grp_edit'));
				$this->form->setTitleIcon(ilUtil::getImagePath('icon_grp_s.gif'));
			
				$this->form->addCommandButton('update',$this->lng->txt('save'));
				$this->form->addCommandButton('cancel',$this->lng->txt('cancel'));
				return true;

			case 'update_group_type':
				$this->form->setTitle($this->lng->txt('grp_edit'));
				$this->form->setTitleIcon(ilUtil::getImagePath('icon_grp_s.gif'));

				$this->form->addCommandButton('updateGroupType',$this->lng->txt('grp_change_type'));
				$this->form->addCommandButton('cancel',$this->lng->txt('cancel'));
				return true;				
		}
		return true;
	}
	
	/**
	 * load settings
	 *
	 * @access public
	 * @return
	 */
	public function load()
	{
		$this->object->setTitle(ilUtil::stripSlashes($_POST['title']));
		$this->object->setDescription(ilUtil::stripSlashes($_POST['desc']));
		$this->object->setGroupType(ilUtil::stripSlashes($_POST['grp_type']));
		$this->object->setRegistrationType(ilUtil::stripSlashes($_POST['registration_type']));
		$this->object->setPassword(ilUtil::stripSlashes($_POST['password']));
		$this->object->enableUnlimitedRegistration((bool) !$_POST['reg_limit_time']);
		$this->object->setRegistrationStart($this->loadDate('registration_start'));
		$this->object->setRegistrationEnd($this->loadDate('registration_end'));
		$this->object->enableMembershipLimitation((bool) $_POST['registration_membership_limited']);
		$this->object->setMaxMembers((int) $_POST['registration_max_members']);
		$this->object->enableWaitingList((bool) $_POST['waiting_list']);
		
		return true;
	}
	
	/**
	 * load date
	 *
	 * @access protected
	 * @param
	 * @return
	 */
	protected function loadDate($a_field)
	{
		global $ilUser;

		include_once('./Services/Calendar/classes/class.ilDateTime.php');
		
		$dt['year'] = (int) $_POST[$a_field]['date']['y'];
		$dt['mon'] = (int) $_POST[$a_field]['date']['m'];
		$dt['mday'] = (int) $_POST[$a_field]['date']['d'];
		$dt['hours'] = (int) $_POST[$a_field]['time']['h'];
		$dt['minutes'] = (int) $_POST[$a_field]['time']['m'];
		$dt['seconds'] = (int) $_POST[$a_field]['time']['s'];
		
		$date = new ilDateTime($dt,IL_CAL_FKT_GETDATE,$ilUser->getTimeZone());
		return $date;		
	}
	
	/**
	 * set sub tabs
	 *
	 * @access protected
	 * @param
	 * @return
	 */
	protected function setSubTabs($a_tab)
	{
		global $rbacsystem,$ilUser,$ilAccess;
	
		switch($a_tab)
		{
			case 'members':
				// for admins
				if($ilAccess->checkAccess('write','',$this->object->getRefId()))
				{
					$this->tabs_gui->addSubTabTarget("grp_edit_members",
						$this->ctrl->getLinkTarget($this,'members'),
						"members",
						get_class($this));
				}
				// for all
				$this->tabs_gui->addSubTabTarget("grp_members_gallery",
					$this->ctrl->getLinkTarget($this,'membersGallery'),
					"membersGallery", get_class($this));
				
				// members map
				include_once("./Services/GoogleMaps/classes/class.ilGoogleMapUtil.php");
				if (ilGoogleMapUtil::isActivated() &&
					$this->object->getEnableGroupMap())
				{
					$this->tabs_gui->addSubTabTarget("grp_members_map",
						$this->ctrl->getLinkTarget($this,'membersMap'),
						"membersMap", get_class($this));
				}
				
				$this->tabs_gui->addSubTabTarget("mail_members",
				$this->ctrl->getLinkTarget($this,'mailMembers'),
				"mailMembers", get_class($this));

				break;

			case "activation":
				$this->tabs_gui->addSubTabTarget("activation",
												 $this->ctrl->getLinkTargetByClass('ilCourseItemAdministrationGUI','edit'),
												 "edit", get_class($this));
				$this->ctrl->setParameterByClass('ilconditionhandlerinterface','item_id',(int) $_GET['item_id']);
				$this->tabs_gui->addSubTabTarget("preconditions",
												 $this->ctrl->getLinkTargetByClass('ilConditionHandlerInterface','listConditions'),
												 "", "ilConditionHandlerInterface");
				break;

			case 'settings':
				$this->tabs_gui->addSubTabTarget("grp_settings",
												 $this->ctrl->getLinkTarget($this,'edit'),
												 "edit", get_class($this));

				$this->tabs_gui->addSubTabTarget("grp_info_settings",
												 $this->ctrl->getLinkTarget($this,'editInfo'),
												 "editInfo", get_class($this));
												 
				// custom icon
				if ($this->ilias->getSetting("custom_icons"))
				{
					$this->tabs_gui->addSubTabTarget("grp_icon_settings",
													 $this->ctrl->getLinkTarget($this,'editGroupIcons'),
													 "editGroupIcons", get_class($this));
				}
												 

				include_once("./Services/GoogleMaps/classes/class.ilGoogleMapUtil.php");
				if (ilGoogleMapUtil::isActivated())
				{
					$this->tabs_gui->addSubTabTarget("grp_map_settings",
												 $this->ctrl->getLinkTarget($this,'editMapSettings'),
												 "editMapSettings", get_class($this));
				}

				$this->tabs_gui->addSubTabTarget('groupings',
												 $this->ctrl->getLinkTargetByClass('ilobjcoursegroupinggui','listGroupings'),
												 'listGroupings',
												 get_class($this));

				break;
		}
	}
} // END class.ilObjGroupGUI
?>
