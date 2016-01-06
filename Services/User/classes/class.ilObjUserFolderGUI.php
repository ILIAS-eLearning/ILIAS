<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "./Services/Object/classes/class.ilObjectGUI.php";

/**
* Class ilObjUserFolderGUI
*
* @author Stefan Meyer <meyer@leifos.com> 
* @author Sascha Hofmann <saschahofmann@gmx.de>
* @author Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version $Id$
* 
* @ilCtrl_Calls ilObjUserFolderGUI: ilPermissionGUI, ilUserTableGUI
* @ilCtrl_Calls ilObjUserFolderGUI: ilAccountCodesGUI, ilCustomUserFieldsGUI, ilRepositorySearchGUI
*
* @ingroup ServicesUser
*/
class ilObjUserFolderGUI extends ilObjectGUI
{
	var $ctrl;

	/**
	* Constructor
	* @access public
	*/
	function ilObjUserFolderGUI($a_data,$a_id,$a_call_by_reference, $a_prepare_output = true)
	{
		global $ilCtrl;

		// TODO: move this to class.ilias.php
		define('USER_FOLDER_ID',7);
		
		$this->type = "usrf";
		$this->ilObjectGUI($a_data,$a_id,$a_call_by_reference,false);
		
		$this->lng->loadLanguageModule('search');
		$this->lng->loadLanguageModule("user");

		$ilCtrl->saveParameter($this, "letter");
	}

	function setUserOwnerId($a_id)
	{
		$this->user_owner_id = $a_id;
	}
	function getUserOwnerId()
	{
		return $this->user_owner_id ? $this->user_owner_id : USER_FOLDER_ID;
	}

	function &executeCommand()
	{
		global $ilTabs;
		
		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();
		$this->prepareOutput();

		switch($next_class)
		{
			case 'ilusertablegui':
				include_once("./Services/User/classes/class.ilUserTableGUI.php");
				$u_table = new ilUserTableGUI($this, "view");
				$u_table->initFilter();
				$this->ctrl->setReturn($this,'view');
				$this->ctrl->forwardCommand($u_table);
				break;

			case 'ilpermissiongui':
				include_once("Services/AccessControl/classes/class.ilPermissionGUI.php");
				$perm_gui =& new ilPermissionGUI($this);
				$ret =& $this->ctrl->forwardCommand($perm_gui);
				break;
				
			case 'ilrepositorysearchgui':
				include_once('./Services/Search/classes/class.ilRepositorySearchGUI.php');
				$user_search =& new ilRepositorySearchGUI();
				$user_search->setTitle($this->lng->txt("search_user_extended")); // #17502
				$user_search->enableSearchableCheck(false);
				$user_search->setUserLimitations(false);
				$user_search->setCallback(
					$this,
					'searchResultHandler',
					$this->getUserMultiCommands(true)
				);
				$this->tabs_gui->setTabActive('search_user_extended');
				$this->ctrl->setReturn($this,'view');
				$ret =& $this->ctrl->forwardCommand($user_search);
				break;
			
			case 'ilaccountcodesgui':
				$this->tabs_gui->setTabActive('settings');
				$this->setSubTabs("settings");			
				$ilTabs->activateSubTab("account_codes");
				include_once("./Services/User/classes/class.ilAccountCodesGUI.php");
				$acc = new ilAccountCodesGUI($this->ref_id);
				$this->ctrl->forwardCommand($acc);
				break;
			
			case 'ilcustomuserfieldsgui':
				$this->tabs_gui->setTabActive('settings');
				$this->setSubTabs("settings");			
				$ilTabs->activateSubTab("user_defined_fields");
				include_once("./Services/User/classes/class.ilCustomUserFieldsGUI.php");
				$cf = new ilCustomUserFieldsGUI();
				$this->ctrl->forwardCommand($cf);
				break;

			default:
				if(!$cmd)
				{
					$cmd = "view";
				}
				$cmd .= "Object";
				
				$this->$cmd();

				break;
		}
		return true;
	}

	function learningProgressObject()
	{
		global $rbacsystem, $tpl;
		
		// deprecated JF 27 May 2013
		exit();

		if (!$rbacsystem->checkAccess("read",$this->object->getRefId()) ||
			!ilObjUserTracking::_enabledLearningProgress() ||
			!ilObjUserTracking::_enabledUserRelatedData())
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}
		
		include_once "Services/User/classes/class.ilUserLPTableGUI.php";
		$tbl = new ilUserLPTableGUI($this, "learningProgress", $this->object->getRefId());
		
		$tpl->setContent($tbl->getHTML());
	}
	
	/**
	* Reset filter
	* (note: this function existed before data table filter has been introduced
	*/
	function resetFilterObject()
	{
		include_once("./Services/User/classes/class.ilUserTableGUI.php");
		$utab = new ilUserTableGUI($this, "view");
		$utab->resetOffset();
		$utab->resetFilter();

		// from "old" implementation
		$this->viewObject(TRUE);
	}

	/**
	* Add new user;
	*/
	function addUserObject()
	{
		global $ilCtrl;
		
		$ilCtrl->setParameterByClass("ilobjusergui", "new_type", "usr");
		$ilCtrl->redirectByClass(array("iladministrationgui", "ilobjusergui"), "create");
	}
	
	
	/**
	* Apply filter
	*/
	function applyFilterObject()
	{
		global $ilTabs;

		include_once("./Services/User/classes/class.ilUserTableGUI.php");
		$utab = new ilUserTableGUI($this, "view");
		$utab->resetOffset();
		$utab->writeFilterToSession();
		$this->viewObject();
		$ilTabs->activateTab("usrf");
	}

	/**
	* list users
	*
	* @access	public
	*/
	function viewObject($reset_filter = FALSE)
	{
		global $rbacsystem, $ilUser, $ilToolbar, $tpl, $ilSetting, $lng;
		
		include_once "Services/UIComponent/Button/classes/class.ilLinkButton.php";

		if ($rbacsystem->checkAccess('create_usr', $this->object->getRefId()) ||
			$rbacsystem->checkAccess('cat_administrate_users', $this->object->getRefId()))
		{
			$button = ilLinkButton::getInstance();
			$button->setCaption("usr_add");
			$button->setUrl($this->ctrl->getLinkTarget($this, "addUser"));
			$ilToolbar->addButtonInstance($button);

			$button = ilLinkButton::getInstance();
			$button->setCaption("import_users");
			$button->setUrl($this->ctrl->getLinkTarget($this, "importUserForm"));
			$ilToolbar->addButtonInstance($button);
		}

		// alphabetical navigation
		include_once './Services/User/classes/class.ilUserAccountSettings.php';
		$aset = ilUserAccountSettings::getInstance();
		if ((int) $ilSetting->get('user_adm_alpha_nav'))
		{
			$ilToolbar->addSeparator();

			// alphabetical navigation
			include_once("./Services/Form/classes/class.ilAlphabetInputGUI.php");
			$ai = new ilAlphabetInputGUI("", "first");
			include_once("./Services/User/classes/class.ilObjUser.php");
			$ai->setLetters(ilObjUser::getFirstLettersOfLastnames());
			/*$ai->setLetters(array("A","B","C","D","E","F","G","H","I","J",
				"K","L","M","N","O","P","Q","R","S","T",
				"U","V","W","X","Y","Z","1","2","3","4","_",
				"Ä","Ü","Ö",":",";","+","*","#","§","%","&"));*/
			$ai->setParentCommand($this, "chooseLetter");
			$ai->setHighlighted($_GET["letter"]);
			$ilToolbar->addInputItem($ai, true);

		}

		include_once("./Services/User/classes/class.ilUserTableGUI.php");
		$utab = new ilUserTableGUI($this, "view");
		$tpl->setContent($utab->getHTML());
	}

	/**
	 * Show auto complete results
	 */
	protected function addUserAutoCompleteObject()
	{
		include_once './Services/User/classes/class.ilUserAutoComplete.php';
		$auto = new ilUserAutoComplete();
		$auto->setSearchFields(array('login','firstname','lastname','email'));
		$auto->enableFieldSearchableCheck(false);
		$auto->setMoreLinkAvailable(true);

		if(($_REQUEST['fetchall']))
		{
			$auto->setLimit(ilUserAutoComplete::MAX_ENTRIES);
		}

		echo $auto->getList($_REQUEST['term']);
		exit();
	}

	/**
	 * Choose first letter
	 *
	 * @param
	 * @return
	 */
	function chooseLetterObject()
	{
		global $ilCtrl;

		$ilCtrl->redirect($this, "view");
	}

	
	/**
	* show possible action (form buttons)
	*
	* @param	boolean
	* @access	public
 	*/
	function showActions($with_subobjects = false)
	{
		global $rbacsystem;

		$operations = array();
//var_dump($this->actions);
		if ($this->actions == "")
		{
			$d = array(
				"delete" => array("name" => "delete", "lng" => "delete"),
				"activate" => array("name" => "activate", "lng" => "activate"),
				"deactivate" => array("name" => "deactivate", "lng" => "deactivate"),
				"accessRestrict" => array("name" => "accessRestrict", "lng" => "accessRestrict"),
				"accessFree" => array("name" => "accessFree", "lng" => "accessFree"),
				"export" => array("name" => "export", "lng" => "export")
			);
		}
		else
		{
			$d = $this->actions;
		}
		foreach ($d as $row)
		{
			if ($rbacsystem->checkAccess($row["name"],$this->object->getRefId()))
			{
				$operations[] = $row;
			}
		}

		if (count($operations) > 0)
		{
			$select = "<select name=\"selectedAction\">\n";
			foreach ($operations as $val)
			{
				$select .= "<option value=\"" . $val["name"] . "\"";
				if (strcmp($_POST["selectedAction"], $val["name"]) == 0)
				{
					$select .= " selected=\"selected\"";
				}
				$select .= ">";
				$select .= $this->lng->txt($val["lng"]);
				$select .= "</option>";
			}
			$select .= "</select>";
			$this->tpl->setCurrentBlock("tbl_action_select");
			$this->tpl->setVariable("SELECT_ACTION", $select);
			$this->tpl->setVariable("BTN_NAME", "userAction");
			$this->tpl->setVariable("BTN_VALUE", $this->lng->txt("submit"));
			$this->tpl->parseCurrentBlock();
		}

		if ($with_subobjects === true)
		{
			$subobjs = $this->showPossibleSubObjects();
		}

		if ((count($operations) > 0) or $subobjs === true)
		{
			$this->tpl->setCurrentBlock("tbl_action_row");
			$this->tpl->setVariable("COLUMN_COUNTS",count($this->data["cols"]));
			$this->tpl->setVariable("IMG_ARROW", ilUtil::getImagePath("arrow_downright.svg"));
			$this->tpl->setVariable("ALT_ARROW", $this->lng->txt("actions"));
			$this->tpl->parseCurrentBlock();
		}
	}

	/**
	* show possible subobjects (pulldown menu)
	* overwritten to prevent displaying of role templates in local role folders
	*
	* @access	public
 	*/
	function showPossibleSubObjects()
	{
		global $rbacsystem;

		$d = $this->objDefinition->getCreatableSubObjects($this->object->getType());
		
		if (!$rbacsystem->checkAccess('create_usr',$this->object->getRefId()))
		{
			unset($d["usr"]);			
		}

		if (count($d) > 0)
		{
			foreach ($d as $row)
			{
			    $count = 0;
				if ($row["max"] > 0)
				{
					//how many elements are present?
					for ($i=0; $i<count($this->data["ctrl"]); $i++)
					{
						if ($this->data["ctrl"][$i]["type"] == $row["name"])
						{
						    $count++;
						}
					}
				}
				if ($row["max"] == "" || $count < $row["max"])
				{
					$subobj[] = $row["name"];
				}
			}
		}

		if (is_array($subobj))
		{
			//build form
			$opts = ilUtil::formSelect(12,"new_type",$subobj);
			$this->tpl->setCurrentBlock("add_object");
			$this->tpl->setVariable("SELECT_OBJTYPE", $opts);
			$this->tpl->setVariable("BTN_NAME", "create");
			$this->tpl->setVariable("TXT_ADD", $this->lng->txt("add"));
			$this->tpl->parseCurrentBlock();
			
			return true;
		}

		return false;
	}

	function cancelUserFolderActionObject()
	{
		$this->ctrl->redirect($this, 'view');
	}
	
	function cancelSearchActionObject()
	{
		$this->ctrl->redirectByClass('ilrepositorysearchgui', 'showSearchResults');
	}

	/**
	* Set the selected users active
	*
	* @access	public
	*/
	function confirmactivateObject()
	{
		global $rbacsystem, $ilUser;

		// FOR NON_REF_OBJECTS WE CHECK ACCESS ONLY OF PARENT OBJECT ONCE
		if (!$rbacsystem->checkAccess('write',$this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilias->error_obj->WARNING);
		}
		
		// FOR ALL SELECTED OBJECTS
		foreach ($_POST["id"] as $id)
		{
			// instatiate correct object class (usr)
			$obj =& $this->ilias->obj_factory->getInstanceByObjId($id);
			$obj->setActive(TRUE, $ilUser->getId());
			$obj->update();
		}

		ilUtil::sendSuccess($this->lng->txt("user_activated"),true);

		if ($_POST["frsrch"])
		{
			$this->ctrl->redirectByClass('ilRepositorySearchGUI','show');			
		}
		else
		{
			$this->ctrl->redirect($this, "view");
		}
	}

	/**
	* Set the selected users inactive
	*
	* @access	public
	*/
	function confirmdeactivateObject()
	{
		global $rbacsystem, $ilUser;
		
		// FOR NON_REF_OBJECTS WE CHECK ACCESS ONLY OF PARENT OBJECT ONCE
		if (!$rbacsystem->checkAccess('write',$this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilias->error_obj->WARNING);
		}
		
		// FOR ALL SELECTED OBJECTS
		foreach ($_POST["id"] as $id)
		{
			// instatiate correct object class (usr)
			$obj =& $this->ilias->obj_factory->getInstanceByObjId($id);
			$obj->setActive(FALSE, $ilUser->getId());
			$obj->update();
		}

		// Feedback
		ilUtil::sendSuccess($this->lng->txt("user_deactivated"),true);

		if ($_POST["frsrch"])
		{
			$this->ctrl->redirectByClass('ilRepositorySearchGUI','show');			
		}
		else
		{
			$this->ctrl->redirect($this, "view");
		}
	}
	
	function confirmaccessFreeObject()
	{
		global $rbacsystem, $ilUser;

		// FOR NON_REF_OBJECTS WE CHECK ACCESS ONLY OF PARENT OBJECT ONCE
		if (!$rbacsystem->checkAccess('write',$this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilias->error_obj->WARNING);
		}
		
		// FOR ALL SELECTED OBJECTS
		foreach ($_POST["id"] as $id)
		{
			// instatiate correct object class (usr)
			$obj =& $this->ilias->obj_factory->getInstanceByObjId($id);
			$obj->setTimeLimitOwner($ilUser->getId());
			$obj->setTimeLimitUnlimited(1);
			$obj->setTimeLimitFrom("");
			$obj->setTimeLimitUntil("");
			$obj->setTimeLimitMessage(0);
			$obj->update();
		}

		// Feedback
		ilUtil::sendSuccess($this->lng->txt("access_free_granted"),true);

		if ($_POST["frsrch"])
		{
			$this->ctrl->redirectByClass('ilRepositorySearchGUI','show');			
		}
		else
		{
			$this->ctrl->redirect($this, "view");
		}
	}
	
	function setAccessRestrictionObject($a_form = null, $a_from_search = false)
	{
		if(!$a_form)
		{
			$a_form = $this->initAccessRestrictionForm($a_from_search);
		}
		$this->tpl->setContent($a_form->getHTML());
		
		// #10963
		return true;
	}
	
	protected function initAccessRestrictionForm($a_from_search = false)
	{
		$user_ids = $this->getActionUserIds();			
		if(!$user_ids)
		{
			ilUtil::sendFailure($this->lng->txt('select_one'));
			return $this->viewObject();
		}
						
		include_once "Services/Form/classes/class.ilPropertyFormGUI.php";
		$form = new ilPropertyFormGUI();
		$form->setTitle($this->lng->txt("time_limit_add_time_limit_for_selected"));
		$form->setFormAction($this->ctrl->getFormAction($this, "confirmaccessRestrict"));
		
		$from = new ilDateTimeInputGUI($this->lng->txt("access_from"), "from");
		$from->setShowTime(true);
		$from->setRequired(true);
		$form->addItem($from);
		
		$to = new ilDateTimeInputGUI($this->lng->txt("access_until"), "to");
		$to->setRequired(true);
		$to->setShowTime(true);
		$form->addItem($to);
		
		$form->addCommandButton("confirmaccessRestrict", $this->lng->txt("confirm"));
		$form->addCommandButton("view", $this->lng->txt("cancel"));
		
		foreach($user_ids as $user_id)
		{
			$ufield = new ilHiddenInputGUI("id[]");
			$ufield->setValue($user_id);
			$form->addItem($ufield);
		}
		
		// return to search?
		if($a_from_search || $_POST["frsrch"])
		{
			$field = new ilHiddenInputGUI("frsrch");
			$field->setValue(1);
			$form->addItem($field);
		}
		
		return $form;
	}

	function confirmaccessRestrictObject()
	{
		$form = $this->initAccessRestrictionForm();
		if(!$form->checkInput())
		{
			return $this->setAccessRestrictionObject($form);
		}
		
		$timefrom = $form->getItemByPostVar("from")->getDate()->get(IL_CAL_UNIX);
		$timeuntil = $form->getItemByPostVar("to")->getDate()->get(IL_CAL_UNIX);
		if ($timeuntil <= $timefrom)
		{
			ilUtil::sendFailure($this->lng->txt("time_limit_not_valid"));
			return $this->setAccessRestrictionObject($form);
		}

		global $rbacsystem, $ilUser;

		// FOR NON_REF_OBJECTS WE CHECK ACCESS ONLY OF PARENT OBJECT ONCE
		if (!$rbacsystem->checkAccess('write',$this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilias->error_obj->WARNING);
		}		
		
		// FOR ALL SELECTED OBJECTS
		foreach ($_POST["id"] as $id)
		{
			// instatiate correct object class (usr)
			$obj =& $this->ilias->obj_factory->getInstanceByObjId($id);
			$obj->setTimeLimitOwner($ilUser->getId());
			$obj->setTimeLimitUnlimited(0);
			$obj->setTimeLimitFrom($timefrom);
			$obj->setTimeLimitUntil($timeuntil);
			$obj->setTimeLimitMessage(0);
			$obj->update();
		}

		// Feedback
		ilUtil::sendSuccess($this->lng->txt("access_restricted"),true);

		if ($_POST["frsrch"])
		{
			$this->ctrl->redirectByClass('ilRepositorySearchGUI','show');			
		}
		else
		{
			$this->ctrl->redirect($this, "view");
		}
	}

	/**
	* confirm delete Object
	*
	* @access	public
	*/
	function confirmdeleteObject()
	{
		global $rbacsystem, $ilCtrl, $ilUser;

		// FOR NON_REF_OBJECTS WE CHECK ACCESS ONLY OF PARENT OBJECT ONCE
		if (!$rbacsystem->checkAccess('delete',$this->object->getRefId()))
		{
			ilUtil::sendFailure($this->lng->txt("msg_no_perm_delete"), true);
			$ilCtrl->redirect($this, "view");
		}
		
		if (in_array($ilUser->getId(), $_POST["id"]))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_delete_yourself"),$this->ilias->error_obj->WARNING);
		}

		// FOR ALL SELECTED OBJECTS
		foreach ($_POST["id"] as $id)
		{
			// instatiate correct object class (usr)
			$obj =& $this->ilias->obj_factory->getInstanceByObjId($id);
			$obj->delete();
		}

		// Feedback
		ilUtil::sendSuccess($this->lng->txt("user_deleted"),true);
				
		if ($_POST["frsrch"])
		{
			$this->ctrl->redirectByClass('ilRepositorySearchGUI','show');			
		}
		else
		{
			$this->ctrl->redirect($this, "view");
		}
	}
	
	/**
	 * Get selected items for table action
	 * 
	 * @return array
	 */
	protected function getActionUserIds()
	{
		if($_POST["select_cmd_all"])
		{
			include_once("./Services/User/classes/class.ilUserTableGUI.php");
			$utab = new ilUserTableGUI($this, "view", ilUserTableGUI::MODE_USER_FOLDER, false);
			return $utab->getUserIdsForFilter();
		}
		else
		{
			return $_POST["id"];
		}
	}

	/**
	* display activation confirmation screen
	*/
	function showActionConfirmation($action, $a_from_search = false)
	{
		global $ilTabs;
		
		$user_ids = $this->getActionUserIds();	
		if(!$user_ids)
		{
			$this->ilias->raiseError($this->lng->txt("no_checkbox"),$this->ilias->error_obj->MESSAGE);
		}
		
		if(!$a_from_search)
		{
			$ilTabs->activateTab("obj_usrf");
		}
		else
		{
			$ilTabs->activateTab("search_user_extended");
		}
				
		if (strcmp($action, "accessRestrict") == 0) 
		{			
			return $this->setAccessRestrictionObject(null, $a_from_search);
		}		
		if (strcmp($action, "mail") == 0) 
		{
			return $this->mailObject();
		}

		unset($this->data);
		
		if(!$a_from_search)
		{
			$cancel = "cancelUserFolderAction";
		}
		else
		{
			$cancel = "cancelSearchAction";							
		}
		
		// display confirmation message
		include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
		$cgui = new ilConfirmationGUI();
		$cgui->setFormAction($this->ctrl->getFormAction($this));
		$cgui->setHeaderText($this->lng->txt("info_" . $action . "_sure"));
		$cgui->setCancel($this->lng->txt("cancel"), $cancel);
		$cgui->setConfirm($this->lng->txt("confirm"), "confirm" . $action);
		
		if($a_from_search)
		{
			$cgui->addHiddenItem("frsrch", 1);
		}

		foreach($user_ids as $id)
		{
			$user = new ilObjUser($id);

			$login = $user->getLastLogin();
			if(!$login)
			{
				$login = $this->lng->txt("never");
			}
			else
			{
				$login = ilDatePresentation::formatDate(new ilDateTime($login, IL_CAL_DATETIME));
			}

			$caption = $user->getFullname()." (".$user->getLogin().")".", ".
				$user->getEmail()." -  ".$this->lng->txt("last_login").": ".$login;

			$cgui->addItem("id[]", $id, $caption);
		}

		$this->tpl->setContent($cgui->getHTML());

		return true;
	}

	/**
	* Delete users
	*/
	function deleteUsersObject()
	{
		$_POST["selectedAction"] = "delete";
		$this->showActionConfirmation($_POST["selectedAction"]);
	}
	
	/**
	* Activate users
	*/
	function activateUsersObject()
	{
		$_POST["selectedAction"] = "activate";
		$this->showActionConfirmation($_POST["selectedAction"]);
	}
	
	/**
	* Deactivate users
	*/
	function deactivateUsersObject()
	{
		$_POST["selectedAction"] = "deactivate";
		$this->showActionConfirmation($_POST["selectedAction"]);
	}

	/**
	* Restrict access
	*/
	function restrictAccessObject()
	{
		$_POST["selectedAction"] = "accessRestrict";
		$this->showActionConfirmation($_POST["selectedAction"]);
	}

	/**
	* Free access
	*/
	function freeAccessObject()
	{
		$_POST["selectedAction"] = "accessFree";
		$this->showActionConfirmation($_POST["selectedAction"]);
	}

	function userActionObject()
	{
		$this->showActionConfirmation($_POST["selectedAction"]);
	}

	/**
	* display form for user import
	*/
	function importUserFormObject ()
	{
		global $tpl, $rbacsystem;
		
		// Blind out tabs for local user import
		if ($_GET["baseClass"] == 'ilRepositoryGUI')
		{
			$this->tabs_gui->clearTargets();
		}

		if (!$rbacsystem->checkAccess("write", $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}

		$this->initUserImportForm();
		$tpl->setContent($this->form->getHTML());
	}

	/**
	* Init user import form.
	*
	* @param        int        $a_mode        Edit Mode
	*/
	public function initUserImportForm()
	{
		global $lng, $ilCtrl;
	
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$this->form = new ilPropertyFormGUI();

		// Import File
		include_once("./Services/Form/classes/class.ilFileInputGUI.php");
		$fi = new ilFileInputGUI($lng->txt("import_file"), "importFile");
		$fi->setSuffixes(array("xml", "zip"));
		//$fi->enableFileNameSelection();
		//$fi->setInfo($lng->txt(""));
		$this->form->addItem($fi);

		$this->form->addCommandButton("importUserRoleAssignment", $lng->txt("import"));
		$this->form->addCommandButton("importCancelled", $lng->txt("cancel"));
	                
		$this->form->setTitle($lng->txt("import_users"));
		$this->form->setFormAction($ilCtrl->getFormAction($this));
	 
	}

	/**
	* import cancelled
	*
	* @access private
	*/
	function importCancelledObject()
	{
		// purge user import directory
		$import_dir = $this->getImportDir();
		if (@is_dir($import_dir))
		{
			ilUtil::delDir($import_dir);
		}

		if (strtolower($_GET["baseClass"]) == 'iladministrationgui')
		{
			$this->ctrl->redirect($this, "view");
			//ilUtil::redirect($this->ctrl->getLinkTarget($this,$return_location));
		}
		else
		{
			$this->ctrl->redirectByClass('ilobjcategorygui','listUsers');
		}
	}

	/**
	* get user import directory name
	*/
	function getImportDir()
	{
		// For each user session a different directory must be used to prevent
		// that one user session overwrites the import data that another session
		// is currently importing.
		global $ilUser;
		$importDir = ilUtil::getDataDir().'/user_import/usr_'.$ilUser->getId().'_'.session_id(); 
		ilUtil::makeDirParents($importDir);
		return $importDir;
	}

	/**
	* display form for user import
	*/
	function importUserRoleAssignmentObject ()
	{
		global $ilUser,$rbacreview, $tpl, $lng, $ilCtrl;;
	
		// Blind out tabs for local user import
		if ($_GET["baseClass"] == 'ilRepositoryGUI')
		{
			$this->tabs_gui->clearTargets();
		}

		$this->initUserImportForm();
		if ($this->form->checkInput())
		{
			include_once './Services/AccessControl/classes/class.ilObjRole.php';
			include_once './Services/User/classes/class.ilUserImportParser.php';
			
			global $rbacreview, $rbacsystem, $tree, $lng;
			
	
			$this->tpl->addBlockfile("ADM_CONTENT", "adm_content", "tpl.usr_import_roles.html", "Services/User");
	
			$import_dir = $this->getImportDir();
	
			// recreate user import directory
			if (@is_dir($import_dir))
			{
				ilUtil::delDir($import_dir);
			}
			ilUtil::makeDir($import_dir);
	
			// move uploaded file to user import directory
			$file_name = $_FILES["importFile"]["name"];
			$parts = pathinfo($file_name);
			$full_path = $import_dir."/".$file_name;
	
			// check if import file exists
			if (!is_file($_FILES["importFile"]["tmp_name"]))
			{
				ilUtil::delDir($import_dir);
				$this->ilias->raiseError($this->lng->txt("no_import_file_found")
					, $this->ilias->error_obj->MESSAGE);
			}
			ilUtil::moveUploadedFile($_FILES["importFile"]["tmp_name"],
				$_FILES["importFile"]["name"], $full_path);
	
			// handle zip file		
			if (strtolower($parts["extension"]) == "zip")
			{
				// unzip file
				ilUtil::unzip($full_path);
	
				$xml_file = null;
				$file_list = ilUtil::getDir($import_dir);
				foreach ($file_list as $a_file)
				{
					if (substr($a_file['entry'],-4) == '.xml')
					{
						$xml_file = $import_dir."/".$a_file['entry'];
						break;
					}
				}
				if (is_null($xml_file))
				{
					$subdir = basename($parts["basename"],".".$parts["extension"]);
					$xml_file = $import_dir."/".$subdir."/".$subdir.".xml";
				}
			}
			// handle xml file
			else
			{
				$xml_file = $full_path;
			}
	
			// check xml file		
			if (!is_file($xml_file))
			{
				ilUtil::delDir($import_dir);
				$this->ilias->raiseError($this->lng->txt("no_xml_file_found_in_zip")
					." ".$subdir."/".$subdir.".xml", $this->ilias->error_obj->MESSAGE);
			}
	
			require_once("./Services/User/classes/class.ilUserImportParser.php");
	
			// Verify the data
			// ---------------
			$importParser = new ilUserImportParser($xml_file, IL_VERIFY);
			$importParser->startParsing();
			switch ($importParser->getErrorLevel())
			{
				case IL_IMPORT_SUCCESS :
					break;
				case IL_IMPORT_WARNING :
					$this->tpl->setVariable("IMPORT_LOG", $importParser->getProtocolAsHTML($lng->txt("verification_warning_log")));
					break;
				case IL_IMPORT_FAILURE :
					ilUtil::delDir($import_dir);
					$this->ilias->raiseError(
						$lng->txt("verification_failed").$importParser->getProtocolAsHTML($lng->txt("verification_failure_log")),
						$this->ilias->error_obj->MESSAGE
					);
					return;
			}
	
			// Create the role selection form
			// ------------------------------
			$this->tpl->setCurrentBlock("role_selection_form");
			$this->tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
			$this->tpl->setVariable("TXT_IMPORT_USERS", $this->lng->txt("import_users"));
			$this->tpl->setVariable("TXT_IMPORT_FILE", $this->lng->txt("import_file"));
			$this->tpl->setVariable("IMPORT_FILE", $file_name);
			$this->tpl->setVariable("TXT_USER_ELEMENT_COUNT", $this->lng->txt("num_users"));
			$this->tpl->setVariable("USER_ELEMENT_COUNT", $importParser->getUserCount());
			$this->tpl->setVariable("TXT_ROLE_ASSIGNMENT", $this->lng->txt("role_assignment"));
			$this->tpl->setVariable("BTN_IMPORT", $this->lng->txt("import"));
			$this->tpl->setVariable("BTN_CANCEL", $this->lng->txt("cancel"));
			$this->tpl->setVariable("XML_FILE_NAME", $xml_file);
	
			// Extract the roles
			$importParser = new ilUserImportParser($xml_file, IL_EXTRACT_ROLES);
			$importParser->startParsing();
			$roles = $importParser->getCollectedRoles();
	
			// get global roles
			$all_gl_roles = $rbacreview->getRoleListByObject(ROLE_FOLDER_ID);
			$gl_roles = array();
			$roles_of_user = $rbacreview->assignedRoles($ilUser->getId());
			foreach ($all_gl_roles as $obj_data)
			{
				// check assignment permission if called from local admin
				if($this->object->getRefId() != USER_FOLDER_ID)
				{
					if(!in_array(SYSTEM_ROLE_ID,$roles_of_user) && !ilObjRole::_getAssignUsersStatus($obj_data['obj_id']))
					{
						continue;
					}
				}
				// exclude anonymous role from list
				if ($obj_data["obj_id"] != ANONYMOUS_ROLE_ID)
				{
					// do not allow to assign users to administrator role if current user does not has SYSTEM_ROLE_ID
					if ($obj_data["obj_id"] != SYSTEM_ROLE_ID or in_array(SYSTEM_ROLE_ID,$roles_of_user))
					{
						$gl_roles[$obj_data["obj_id"]] = $obj_data["title"];
					}
				}
			}
	
			// global roles
			$got_globals = false;
			foreach($roles as $role_id => $role)
			{
				if ($role["type"] == "Global")
				{
					if (! $got_globals)
					{
						$got_globals = true;
	
						$this->tpl->setCurrentBlock("global_role_section");
						$this->tpl->setVariable("TXT_GLOBAL_ROLES_IMPORT", $this->lng->txt("roles_of_import_global"));
						$this->tpl->setVariable("TXT_GLOBAL_ROLES", $this->lng->txt("assign_global_role"));
					}
	
					// pre selection for role
					$pre_select = array_search($role[name], $gl_roles);
					if (! $pre_select)
					{
						switch($role["name"])
						{
							case "Administrator":	// ILIAS 2/3 Administrator
								$pre_select = array_search("Administrator", $gl_roles);
								break;
	
							case "Autor":			// ILIAS 2 Author
								$pre_select = array_search("User", $gl_roles);
								break;
	
							case "Lerner":			// ILIAS 2 Learner
								$pre_select = array_search("User", $gl_roles);
								break;
	
							case "Gast":			// ILIAS 2 Guest
								$pre_select = array_search("Guest", $gl_roles);
								break;
	
							default:
								$pre_select = array_search("User", $gl_roles);
								break;
						}
					}
					$this->tpl->setCurrentBlock("global_role");
					$role_select = ilUtil::formSelect($pre_select, "role_assign[".$role_id."]", $gl_roles, false, true);
					$this->tpl->setVariable("TXT_IMPORT_GLOBAL_ROLE", $role["name"]." [".$role_id."]");
					$this->tpl->setVariable("SELECT_GLOBAL_ROLE", $role_select);
					$this->tpl->parseCurrentBlock();
				}
			}
	
			// Check if local roles need to be assigned
			$got_locals = false;
			foreach($roles as $role_id => $role)
			{
				if ($role["type"] == "Local")
				{
					$got_locals = true;
					break;
				}
			}
	
			if ($got_locals) 
			{
				$this->tpl->setCurrentBlock("local_role_section");
				$this->tpl->setVariable("TXT_LOCAL_ROLES_IMPORT", $this->lng->txt("roles_of_import_local"));
				$this->tpl->setVariable("TXT_LOCAL_ROLES", $this->lng->txt("assign_local_role"));
	
	
				// get local roles
				if ($this->object->getRefId() == USER_FOLDER_ID)
				{
					// The import function has been invoked from the user folder
					// object. In this case, we show only matching roles,
					// because the user folder object is considered the parent of all
					// local roles and may contains thousands of roles on large ILIAS
					// installations.
					$loc_roles = array();
					foreach($roles as $role_id => $role)
					{
						if ($role["type"] == "Local")
						{
							$searchName = (substr($role['name'],0,1) == '#') ? $role['name'] : '#'.$role['name'];
							$matching_role_ids = $rbacreview->searchRolesByMailboxAddressList($searchName);
							foreach ($matching_role_ids as $mid) {
								if (! in_array($mid, $loc_roles)) {
									$loc_roles[] = $mid;
								}
							}
						}
					}
				} else {
					// The import function has been invoked from a locally
					// administrated category. In this case, we show all roles
					// contained in the subtree of the category.
					$loc_roles = $rbacreview->getAssignableRolesInSubtree($this->object->getRefId());
				}
				$l_roles = array();
				
				// create a search array with  .
				$l_roles_mailbox_searcharray = array();
				foreach ($loc_roles as $key => $loc_role)
				{
					// fetch context path of role
					$rolf = $rbacreview->getFoldersAssignedToRole($loc_role,true);
	
					// only process role folders that are not set to status "deleted" 
					// and for which the user has write permissions.
					// We also don't show the roles which are in the ROLE_FOLDER_ID folder.
					// (The ROLE_FOLDER_ID folder contains the global roles).
					if (
						!$rbacreview->isDeleted($rolf[0]) &&
						$rbacsystem->checkAccess('write',$rolf[0]) &&
						$rolf[0] != ROLE_FOLDER_ID
					)
					{
						// A local role is only displayed, if it is contained in the subtree of 
						// the localy administrated category. If the import function has been 
						// invoked from the user folder object, we show all local roles, because
						// the user folder object is considered the parent of all local roles.
						// Thus, if we start from the user folder object, we initialize the
						// isInSubtree variable with true. In all other cases it is initialized 
						// with false, and only set to true if we find the object id of the
						// locally administrated category in the tree path to the local role.
						$isInSubtree = $this->object->getRefId() == USER_FOLDER_ID;
						
						$path = "";
						if ($this->tree->isInTree($rolf[0]))
						{
							// Create path. Paths which have more than 4 segments
							// are truncated in the middle.
							$tmpPath = $this->tree->getPathFull($rolf[0]);
							for ($i = 1, $n = count($tmpPath) - 1; $i < $n; $i++)
							{
								if ($i > 1)
								{
									$path = $path.' > ';
								}
								if ($i < 3 || $i > $n - 3)
								{
									$path = $path.$tmpPath[$i]['title'];
								} 
								else if ($i == 3 || $i == $n - 3)
								{
									$path = $path.'...';
								}
								
								$isInSubtree |= $tmpPath[$i]['obj_id'] == $this->object->getId();
							}
						}
						else
						{
							$path = "<b>Rolefolder ".$rolf[0]." not found in tree! (Role ".$loc_role.")</b>";
						}
						$roleMailboxAddress = $rbacreview->getRoleMailboxAddress($loc_role);
						$l_roles[$loc_role] = $roleMailboxAddress.', '.$path;
					}
				} //foreach role
	
				$l_roles[""] = ""; 
				natcasesort($l_roles);
				$l_roles[""] = $this->lng->txt("usrimport_ignore_role"); 
				foreach($roles as $role_id => $role)
				{
					if ($role["type"] == "Local")
					{
						$this->tpl->setCurrentBlock("local_role");
						$this->tpl->setVariable("TXT_IMPORT_LOCAL_ROLE", $role["name"]);
						$searchName = (substr($role['name'],0,1) == '#') ? $role['name'] : '#'.$role['name'];
						$matching_role_ids = $rbacreview->searchRolesByMailboxAddressList($searchName);
						$pre_select = count($matching_role_ids) == 1 ? $matching_role_ids[0] : "";
						if ($this->object->getRefId() == USER_FOLDER_ID) {
							// There are too many roles in a large ILIAS installation
							// that's why whe show only a choice with the the option "ignore",
							// and the matching roles.
							$selectable_roles = array();
							$selectable_roles[""] =  $this->lng->txt("usrimport_ignore_role");
							foreach ($matching_role_ids as $id)
							{
								$selectable_roles[$id] =  $l_roles[$id];
							}
							$role_select = ilUtil::formSelect($pre_select, "role_assign[".$role_id."]", $selectable_roles, false, true);
						} else {
							$role_select = ilUtil::formSelect($pre_select, "role_assign[".$role_id."]", $l_roles, false, true);
						}
						$this->tpl->setVariable("SELECT_LOCAL_ROLE", $role_select);
						$this->tpl->parseCurrentBlock();
					}
				}
			}
			// 
	 
			$this->tpl->setVariable("TXT_CONFLICT_HANDLING", $lng->txt("conflict_handling"));
			$handlers = array(
				IL_IGNORE_ON_CONFLICT => "ignore_on_conflict",
				IL_UPDATE_ON_CONFLICT => "update_on_conflict"
			);
			$this->tpl->setVariable("TXT_CONFLICT_HANDLING_INFO", str_replace('\n','<br>',$this->lng->txt("usrimport_conflict_handling_info")));
			$this->tpl->setVariable("TXT_CONFLICT_CHOICE", $lng->txt("conflict_handling"));
			$this->tpl->setVariable("SELECT_CONFLICT", ilUtil::formSelect(IL_IGNORE_ON_CONFLICT, "conflict_handling_choice", $handlers, false, false));
	
			// new account mail
			$this->lng->loadLanguageModule("mail");
			include_once './Services/User/classes/class.ilObjUserFolder.php';
			$amail = ilObjUserFolder::_lookupNewAccountMail($this->lng->getDefaultLanguage());
			if (trim($amail["body"]) != "" && trim($amail["subject"]) != "")
			{
				$this->tpl->setCurrentBlock("inform_user");
				$this->tpl->setVariable("TXT_ACCOUNT_MAIL", $lng->txt("mail_account_mail"));
				if (true)
				{
					$this->tpl->setVariable("SEND_MAIL", " checked=\"checked\"");
				}
				$this->tpl->setVariable("TXT_INFORM_USER_MAIL",
					$this->lng->txt("user_send_new_account_mail"));
				$this->tpl->parseCurrentBlock();
			}
		}
		else
		{
			$this->form->setValuesByPost();
			$tpl->setContent($this->form->getHtml());
		}
	}

	/**
	* import users
	*/
	function importUsersObject()
	{
		global $rbacreview,$ilUser;
		
		// Blind out tabs for local user import
		if ($_GET["baseClass"] == 'ilRepositoryGUI')
		{
			$this->tabs_gui->clearTargets();
		}
		
		include_once './Services/AccessControl/classes/class.ilObjRole.php';
		include_once './Services/User/classes/class.ilUserImportParser.php';

		global $rbacreview, $rbacsystem, $tree, $lng;

		switch ($_POST["conflict_handling_choice"])
		{
			case "update_on_conflict" :
				$rule = IL_UPDATE_ON_CONFLICT;
				break;
			case "ignore_on_conflict" :
			default :
				$rule = IL_IGNORE_ON_CONFLICT;
				break;
		}
		$importParser = new ilUserImportParser($_POST["xml_file"],  IL_USER_IMPORT, $rule);
		$importParser->setFolderId($this->getUserOwnerId());
		$import_dir = $this->getImportDir();

		// Catch hack attempts
		// We check here again, if the role folders are in the tree, and if the
		// user has permission on the roles.
		if ($_POST["role_assign"])
		{
			$global_roles = $rbacreview->getGlobalRoles();
			$roles_of_user = $rbacreview->assignedRoles($ilUser->getId());
			foreach ($_POST["role_assign"] as $role_id)
			{
				if ($role_id != "") 
				{
					if (in_array($role_id, $global_roles))
					{
						if(!in_array(SYSTEM_ROLE_ID,$roles_of_user))
						{
							if ($role_id == SYSTEM_ROLE_ID && ! in_array(SYSTEM_ROLE_ID,$roles_of_user)
							|| ($this->object->getRefId() != USER_FOLDER_ID 
								&& ! ilObjRole::_getAssignUsersStatus($role_id))
							)
							{
								ilUtil::delDir($import_dir);
								$this->ilias->raiseError($this->lng->txt("usrimport_with_specified_role_not_permitted"), 
									$this->ilias->error_obj->MESSAGE);
							}
						}
					}
					else
					{
						$rolf = $rbacreview->getFoldersAssignedToRole($role_id,true);
						if ($rbacreview->isDeleted($rolf[0])
							|| ! $rbacsystem->checkAccess('write',$rolf[0]))
						{
							ilUtil::delDir($import_dir);
							$this->ilias->raiseError($this->lng->txt("usrimport_with_specified_role_not_permitted"), 
								$this->ilias->error_obj->MESSAGE);
							return;
						}
					}
				}
			}
		}

		$importParser->setRoleAssignment($_POST["role_assign"]);
		$importParser->startParsing();

		// purge user import directory
		ilUtil::delDir($import_dir);

		switch ($importParser->getErrorLevel())
		{
			case IL_IMPORT_SUCCESS :
				ilUtil::sendSuccess($this->lng->txt("user_imported"), true);
				break;
			case IL_IMPORT_WARNING :
				ilUtil::sendInfo($this->lng->txt("user_imported_with_warnings").$importParser->getProtocolAsHTML($lng->txt("import_warning_log")), true);
				break;
			case IL_IMPORT_FAILURE :
				$this->ilias->raiseError(
					$this->lng->txt("user_import_failed")
					.$importParser->getProtocolAsHTML($lng->txt("import_failure_log")),
					$this->ilias->error_obj->MESSAGE
				);
				break;
		}

		if (strtolower($_GET["baseClass"]) == "iladministrationgui")
		{
			$this->ctrl->redirect($this, "view");
			//ilUtil::redirect($this->ctrl->getLinkTarget($this));
		}
		else
		{
			$this->ctrl->redirectByClass('ilobjcategorygui','listUsers');
		}
	}


	function appliedUsersObject()
	{
		global $rbacsystem,$ilias;

		unset($_SESSION['applied_users']);

		if (!$rbacsystem->checkAccess("visible,read",$this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}
		
		if(!count($app_users =& $ilias->account->getAppliedUsers()))
		{
			ilUtil::sendFailure($this->lng->txt('no_users_applied'));

			return false;
		}

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.usr_applied_users.html", "Services/User");
		$this->lng->loadLanguageModule('crs');
		
		$counter = 0;
		foreach($app_users as $usr_id)
		{
			$tmp_user =& ilObjectFactory::getInstanceByObjId($usr_id);

			$f_result[$counter][]	= ilUtil::formCheckbox(0,"users[]",$usr_id);
			$f_result[$counter][]   = $tmp_user->getLogin();
			$f_result[$counter][]	= $tmp_user->getFirstname();
			$f_result[$counter][]	= $tmp_user->getLastname();
			
			if($tmp_user->getTimeLimitUnlimited())
			{
				$f_result[$counter][]	= "<b>".$this->lng->txt('crs_unlimited')."</b>";
			}
			else
			{
				$limit = "<b>".$this->lng->txt('crs_from').'</b> '.strftime("%Y-%m-%d %R",$tmp_user->getTimeLimitFrom()).'<br />';
				$limit .= "<b>".$this->lng->txt('crs_to').'</b> '.strftime("%Y-%m-%d %R",$tmp_user->getTimeLimitUntil());

				$f_result[$counter][]	= $limit;
			}
			++$counter;
		}

		$this->__showAppliedUsersTable($f_result);

		return true;
	}

	function editAppliedUsersObject()
	{
		global $rbacsystem;

		if(!$rbacsystem->checkAccess("write", $this->ref_id))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilias->error_obj->MESSAGE);
		}

		$this->lng->loadLanguageModule('crs');

		$_POST['users'] = $_SESSION['applied_users'] = ($_SESSION['applied_users'] ? $_SESSION['applied_users'] : $_POST['users']);

		if(!isset($_SESSION['error_post_vars']))
		{
			ilUtil::sendInfo($this->lng->txt('time_limit_add_time_limit_for_selected'));
		}

		if(!count($_POST["users"]))
		{
			ilUtil::sendFailure($this->lng->txt("time_limit_no_users_selected"));
			$this->appliedUsersObject();

			return false;
		}
		
		$counter = 0;
		foreach($_POST['users'] as $usr_id)
		{
			if($counter)
			{
				$title .= ', ';
			}
			$tmp_user =& ilObjectFactory::getInstanceByObjId($usr_id);
			$title .= $tmp_user->getLogin();
			++$counter;
		}
		if(strlen($title) > 79)
		{
			$title = substr($title,0,80);
			$title .= '...';
		}


		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.usr_edit_applied_users.html", "Services/User");
		$this->tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));

		// LOAD SAVED DATA IN CASE OF ERROR
		$time_limit_unlimited = $_SESSION["error_post_vars"]["au"]["time_limit_unlimited"] ? 
			1 : 0;

		$time_limit_start = $_SESSION["error_post_vars"]["au"]["time_limit_start"] ? 
			$this->__toUnix($_SESSION["error_post_vars"]["au"]["time_limit_start"]) :
			time();
		$time_limit_end = $_SESSION["error_post_vars"]["au"]["time_limit_end"] ? 
			$this->__toUnix($_SESSION["error_post_vars"]["au"]["time_limit_end"]) :
			time();

		
		// SET TEXT VARIABLES
		$this->tpl->setVariable("ALT_IMG",$this->lng->txt("obj_usr"));
		$this->tpl->setVariable("TYPE_IMG",ilObject::_getIcon("", "", "usr"));
		$this->tpl->setVariable("TITLE",$title);
		$this->tpl->setVariable("TXT_TIME_LIMIT",$this->lng->txt("time_limit"));
		$this->tpl->setVariable("TXT_TIME_LIMIT_START",$this->lng->txt("crs_start"));
		$this->tpl->setVariable("TXT_TIME_LIMIT_END",$this->lng->txt("crs_end"));
		$this->tpl->setVariable("CMD_SUBMIT","updateAppliedUsers");
		$this->tpl->setVariable("TXT_CANCEL",$this->lng->txt("cancel"));
		$this->tpl->setVariable("TXT_SUBMIT",$this->lng->txt("submit"));
		


		$this->tpl->setVariable("SELECT_TIME_LIMIT_START_DAY",$this->__getDateSelect("day","au[time_limit_start][day]",
																					 date("d",$time_limit_start)));
		$this->tpl->setVariable("SELECT_TIME_LIMIT_START_MONTH",$this->__getDateSelect("month","au[time_limit_start][month]",
																					   date("m",$time_limit_start)));
		$this->tpl->setVariable("SELECT_TIME_LIMIT_START_YEAR",$this->__getDateSelect("year","au[time_limit_start][year]",
																					  date("Y",$time_limit_start)));
		$this->tpl->setVariable("SELECT_TIME_LIMIT_START_HOUR",$this->__getDateSelect("hour","au[time_limit_start][hour]",
																					  date("G",$time_limit_start)));
		$this->tpl->setVariable("SELECT_TIME_LIMIT_START_MINUTE",$this->__getDateSelect("minute","au[time_limit_start][minute]",
																					  date("i",$time_limit_start)));
		$this->tpl->setVariable("SELECT_TIME_LIMIT_END_DAY",$this->__getDateSelect("day","au[time_limit_end][day]",
																				   date("d",$time_limit_end)));
		$this->tpl->setVariable("SELECT_TIME_LIMIT_END_MONTH",$this->__getDateSelect("month","au[time_limit_end][month]",
																					 date("m",$time_limit_end)));
		$this->tpl->setVariable("SELECT_TIME_LIMIT_END_YEAR",$this->__getDateSelect("year","au[time_limit_end][year]",
																					date("Y",$time_limit_end)));
		$this->tpl->setVariable("SELECT_TIME_LIMIT_END_HOUR",$this->__getDateSelect("hour","au[time_limit_end][hour]",
																					  date("G",$time_limit_end)));
		$this->tpl->setVariable("SELECT_TIME_LIMIT_END_MINUTE",$this->__getDateSelect("minute","au[time_limit_end][minute]",
																					  date("i",$time_limit_end)));
		if($this->ilias->account->getTimeLimitUnlimited())
		{
			$this->tpl->setVariable("ROWSPAN",3);
			$this->tpl->setCurrentBlock("unlimited");
			$this->tpl->setVariable("TXT_TIME_LIMIT_UNLIMITED",$this->lng->txt("crs_unlimited"));
			$this->tpl->setVariable("TIME_LIMIT_UNLIMITED",ilUtil::formCheckbox($time_limit_unlimited,"au[time_limit_unlimited]",1));
			$this->tpl->parseCurrentBlock();
		}
		else
		{
			$this->tpl->setVariable("ROWSPAN",2);
		}
	}

	function updateAppliedUsersObject()
	{
		global $rbacsystem;

		if(!$rbacsystem->checkAccess("write", $this->ref_id))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilias->error_obj->MESSAGE);
		}

		$start	= $this->__toUnix($_POST['au']['time_limit_start']);
		$end	= $this->__toUnix($_POST['au']['time_limit_end']);

		if(!$_POST['au']['time_limit_unlimited'])
		{
			if($start > $end)
			{
				$_SESSION['error_post_vars'] = $_POST;
				ilUtil::sendFailure($this->lng->txt('time_limit_not_valid'));
				$this->editAppliedUsersObject();

				return false;
			}
		}
		#if(!$this->ilias->account->getTimeLimitUnlimited())
		#{
		#	if($start < $this->ilias->account->getTimeLimitFrom() or
		#	   $end > $this->ilias->account->getTimeLimitUntil())
		#	{
		#		$_SESSION['error_post_vars'] = $_POST;
		#		ilUtil::sendInfo($this->lng->txt('time_limit_not_within_owners'));
		#		$this->editAppliedUsersObject();

		#		return false;
		#	}
		#}

		foreach($_SESSION['applied_users'] as $usr_id)
		{
			$tmp_user =& ilObjectFactory::getInstanceByObjId($usr_id);

			$tmp_user->setTimeLimitUnlimited((int) $_POST['au']['time_limit_unlimited']);
			$tmp_user->setTimeLimitFrom($start);
			$tmp_user->setTimeLimitUntil($end);
			$tmp_user->setTimeLimitMessage(0);
			$tmp_user->update();

			unset($tmp_user);
		}

		unset($_SESSION['applied_users']);
		ilUtil::sendSuccess($this->lng->txt('time_limit_users_updated'));
		$this->appliedUsersObject();
		
		return true;
	}

	function __showAppliedUsersTable($a_result_set)
	{
		$tbl =& $this->__initTableGUI();
		$tpl =& $tbl->getTemplateObject();

		// SET FORMAACTION
		$tpl->setCurrentBlock("tbl_form_header");

		$tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("tbl_action_btn");
		$tpl->setVariable("BTN_NAME",'editAppliedUsers');
		$tpl->setVariable("BTN_VALUE",$this->lng->txt('edit'));
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("tbl_action_row");
		$tpl->setVariable("COLUMN_COUNTS",5);
		$tpl->setVariable("IMG_ARROW", ilUtil::getImagePath("arrow_downright.svg"));
		$tpl->setVariable("ALT_ARROW", $this->lng->txt("actions"));
		$tpl->parseCurrentBlock();



		$tbl->setTitle($this->lng->txt("time_limit_applied_users"),"",$this->lng->txt("users"));
		$tbl->setHeaderNames(array('',
								   $this->lng->txt("login"),
								   $this->lng->txt("firstname"),
								   $this->lng->txt("lastname"),
								   $this->lng->txt("time_limits")));
		$header_params = $this->ctrl->getParameterArray($this, "appliedUsers");
		$tbl->setHeaderVars(array("",
								  "login",
								  "firstname",
								  "lastname",
								  "time_limit"),
							array($header_params));
		$tbl->setColumnWidth(array("3%","19%","19%","19%","40%"));


		$this->__setTableGUIBasicData($tbl,$a_result_set);
		$tbl->render();

		$this->tpl->setVariable("APPLIED_USERS",$tbl->tpl->get());

		return true;
	}

	function &__initTableGUI()
	{
		include_once "./Services/Table/classes/class.ilTableGUI.php";

		return new ilTableGUI(0,false);
	}

	function __setTableGUIBasicData(&$tbl,&$result_set,$from = "")
	{
		$offset = $_GET["offset"];
		$order = $_GET["sort_by"];
		$direction = $_GET["sort_order"];

        //$tbl->enable("hits");
		$tbl->setOrderColumn($order);
		$tbl->setOrderDirection($direction);
		$tbl->setOffset($offset);
		$tbl->setLimit($_GET["limit"]);
		$tbl->setMaxCount(count($result_set));
		$tbl->setFooter("tblfooter",$this->lng->txt("previous"),$this->lng->txt("next"));
		$tbl->setData($result_set);
	}

	function __getDateSelect($a_type,$a_varname,$a_selected)
    {
        switch($a_type)
        {
            case "minute":
                for($i=0;$i<=60;$i++)
                {
                    $days[$i] = $i < 10 ? "0".$i : $i;
                }
                return ilUtil::formSelect($a_selected,$a_varname,$days,false,true);

            case "hour":
                for($i=0;$i<24;$i++)
                {
                    $days[$i] = $i < 10 ? "0".$i : $i;
                }
                return ilUtil::formSelect($a_selected,$a_varname,$days,false,true);

            case "day":
                for($i=1;$i<32;$i++)
                {
                    $days[$i] = $i < 10 ? "0".$i : $i;
                }
                return ilUtil::formSelect($a_selected,$a_varname,$days,false,true);

            case "month":
                for($i=1;$i<13;$i++)
                {
                    $month[$i] = $i < 10 ? "0".$i : $i;
                }
                return ilUtil::formSelect($a_selected,$a_varname,$month,false,true);

            case "year":
                for($i = date("Y",time());$i < date("Y",time()) + 3;++$i)
                {
                    $year[$i] = $i;
                }
                return ilUtil::formSelect($a_selected,$a_varname,$year,false,true);
        }
    }
	function __toUnix($a_time_arr)
    {
        return mktime($a_time_arr["hour"],
                      $a_time_arr["minute"],
                      $a_time_arr["second"],
                      $a_time_arr["month"],
                      $a_time_arr["day"],
                      $a_time_arr["year"]);
    }

	function hitsperpageObject()
	{
        parent::hitsperpageObject();
        $this->viewObject();
	}

	/**
	 * Show user account general settings
	 * @return 
	 */
	protected function generalSettingsObject()
	{
		global $ilSetting;
		
		$this->initFormGeneralSettings();
		
		include_once './Services/User/classes/class.ilUserAccountSettings.php';
		$aset = ilUserAccountSettings::getInstance();
		
		$show_blocking_time_in_days = $ilSetting->get('loginname_change_blocking_time') / 86400;
		$show_blocking_time_in_days = (float)$show_blocking_time_in_days;
		
		include_once('./Services/PrivacySecurity/classes/class.ilSecuritySettings.php');
		$security = ilSecuritySettings::_getInstance();
		
		$this->form->setValuesByArray(
			array(
				'lua'	=> $aset->isLocalUserAdministrationEnabled(),
				'lrua'	=> $aset->isUserAccessRestricted(),
				'allow_change_loginname' => (bool)$ilSetting->get('allow_change_loginname'),
				'create_history_loginname' => (bool)$ilSetting->get('create_history_loginname'),
				'reuse_of_loginnames' => (bool)$ilSetting->get('reuse_of_loginnames'),
				'loginname_change_blocking_time' => (float)$show_blocking_time_in_days,
				'user_adm_alpha_nav' => (int)$ilSetting->get('user_adm_alpha_nav'),
				// 'user_ext_profiles' => (int)$ilSetting->get('user_ext_profiles')
				'user_reactivate_code' => (int)$ilSetting->get('user_reactivate_code'),
				'user_own_account' => (int)$ilSetting->get('user_delete_own_account'),
				'user_own_account_email' => $ilSetting->get('user_delete_own_account_email'),
			
				'session_handling_type' => $ilSetting->get('session_handling_type', ilSession::SESSION_HANDLING_FIXED),
				'session_reminder_enabled' => $ilSetting->get('session_reminder_enabled'),
				'session_max_count' => $ilSetting->get('session_max_count', ilSessionControl::DEFAULT_MAX_COUNT),
				'session_min_idle' => $ilSetting->get('session_min_idle', ilSessionControl::DEFAULT_MIN_IDLE),
				'session_max_idle' => $ilSetting->get('session_max_idle', ilSessionControl::DEFAULT_MAX_IDLE),
				'session_max_idle_after_first_request' => $ilSetting->get('session_max_idle_after_first_request', ilSessionControl::DEFAULT_MAX_IDLE_AFTER_FIRST_REQUEST),

				'passwd_auto_generate' => (bool)$ilSetting->get("passwd_auto_generate"),			
				'password_change_on_first_login_enabled' => $security->isPasswordChangeOnFirstLoginEnabled() ? 1 : 0, 													
				'password_must_not_contain_loginame' => $security->getPasswordMustNotContainLoginnameStatus() ? 1 : 0, 													
				'password_chars_and_numbers_enabled' => $security->isPasswordCharsAndNumbersEnabled() ? 1 : 0,
				'password_special_chars_enabled' => $security->isPasswordSpecialCharsEnabled() ? 1 : 0 ,
				'password_min_length' => $security->getPasswordMinLength(),
				'password_max_length' => $security->getPasswordMaxLength(),
				'password_ucase_chars_num' => $security->getPasswordNumberOfUppercaseChars(),
				'password_lowercase_chars_num' => $security->getPasswordNumberOfLowercaseChars(),
				'password_max_age' => $security->getPasswordMaxAge(),
				
				'login_max_attempts' => $security->getLoginMaxAttempts(),				
				'ps_prevent_simultaneous_logins' => (int)$security->isPreventionOfSimultaneousLoginsEnabled(),
				'password_assistance' => (bool)$ilSetting->get("password_assistance")
			)
		);
						
		$this->tpl->setContent($this->form->getHTML());
	}
	
	
	/**
	 * Save user account settings
	 * @return 
	 */
	public function saveGeneralSettingsObject()
	{
		global $ilUser, $ilSetting;
		
		$this->initFormGeneralSettings();
		if($this->form->checkInput())
		{
			$valid = true;
			
			if(!strlen($this->form->getInput('loginname_change_blocking_time')))
			{
				$valid = false;
				$this->form->getItemByPostVar('loginname_change_blocking_time')
										->setAlert($this->lng->txt('loginname_change_blocking_time_invalidity_info'));
			}
											
			include_once('./Services/PrivacySecurity/classes/class.ilSecuritySettings.php');
			$security = ilSecuritySettings::_getInstance();

			// account security settings			
			$security->setPasswordCharsAndNumbersEnabled((bool) $_POST["password_chars_and_numbers_enabled"]);
			$security->setPasswordSpecialCharsEnabled((bool) $_POST["password_special_chars_enabled"]);
			$security->setPasswordMinLength((int) $_POST["password_min_length"]);
			$security->setPasswordMaxLength((int) $_POST["password_max_length"]);
			$security->setPasswordNumberOfUppercaseChars((int) $_POST['password_ucase_chars_num']);
			$security->setPasswordNumberOfLowercaseChars((int) $_POST['password_lowercase_chars_num']);
			$security->setPasswordMaxAge((int) $_POST["password_max_age"]);
			$security->setLoginMaxAttempts((int) $_POST["login_max_attempts"]);
			$security->setPreventionOfSimultaneousLogins((bool)$_POST['ps_prevent_simultaneous_logins']);
			$security->setPasswordChangeOnFirstLoginEnabled((bool) $_POST['password_change_on_first_login_enabled']);
			$security->setPasswordMustNotContainLoginnameStatus((int) $_POST['password_must_not_contain_loginame']);
				
			if(!$security->validate($this->form))
			{
				$valid = false;
			}
			
			if($valid)
			{			
				$security->save();
				
				include_once './Services/User/classes/class.ilUserAccountSettings.php';
				ilUserAccountSettings::getInstance()->enableLocalUserAdministration($this->form->getInput('lua'));
				ilUserAccountSettings::getInstance()->restrictUserAccess($this->form->getInput('lrua'));
				ilUserAccountSettings::getInstance()->update();

				$ilSetting->set('allow_change_loginname', (int)$this->form->getInput('allow_change_loginname'));
				$ilSetting->set('create_history_loginname', (int)$this->form->getInput('create_history_loginname'));
				$ilSetting->set('reuse_of_loginnames', (int)$this->form->getInput('reuse_of_loginnames'));
				$save_blocking_time_in_seconds = (int)($this->form->getInput('loginname_change_blocking_time') * 86400);
				$ilSetting->set('loginname_change_blocking_time', (int)$save_blocking_time_in_seconds);
				$ilSetting->set('user_adm_alpha_nav', (int)$this->form->getInput('user_adm_alpha_nav'));			
				$ilSetting->set('user_reactivate_code', (int)$this->form->getInput('user_reactivate_code'));
				
				$ilSetting->set('user_delete_own_account', (int)$this->form->getInput('user_own_account'));
				$ilSetting->set('user_delete_own_account_email', $this->form->getInput('user_own_account_email'));
				
				$ilSetting->set("passwd_auto_generate", $this->form->getInput("passwd_auto_generate"));	
				$ilSetting->set("password_assistance", $this->form->getInput("password_assistance"));	
				
				// BEGIN SESSION SETTINGS
				$ilSetting->set('session_handling_type',
					(int)$this->form->getInput('session_handling_type'));			

				if( $this->form->getInput('session_handling_type') == ilSession::SESSION_HANDLING_FIXED )
				{
					$ilSetting->set('session_reminder_enabled',
						$this->form->getInput('session_reminder_enabled'));	
				}
				else if( $this->form->getInput('session_handling_type') == ilSession::SESSION_HANDLING_LOAD_DEPENDENT )
				{
					require_once 'Services/Authentication/classes/class.ilSessionControl.php';
					if(
						$ilSetting->get('session_allow_client_maintenance',
							ilSessionControl::DEFAULT_ALLOW_CLIENT_MAINTENANCE) 
					  )
					{				
						// has to be done BEFORE updating the setting!
						include_once "Services/Authentication/classes/class.ilSessionStatistics.php";
						ilSessionStatistics::updateLimitLog((int)$this->form->getInput('session_max_count'));					

						$ilSetting->set('session_max_count',
							(int)$this->form->getInput('session_max_count'));
						$ilSetting->set('session_min_idle',
							(int)$this->form->getInput('session_min_idle'));
						$ilSetting->set('session_max_idle',
							(int)$this->form->getInput('session_max_idle'));
						$ilSetting->set('session_max_idle_after_first_request',
							(int)$this->form->getInput('session_max_idle_after_first_request'));
					}	
				}		
				// END SESSION SETTINGS												
				
				ilUtil::sendSuccess($this->lng->txt('saved_successfully'));
			}
			else
			{
				ilUtil::sendFailure($this->lng->txt('form_input_not_valid'));
			}
		}
		else
		{
			ilUtil::sendFailure($this->lng->txt('form_input_not_valid'));
		}
		$this->form->setValuesByPost();		
		$this->tpl->setContent($this->form->getHTML());
	}
	
	
	/**
	 * init general settings form
	 * @return 
	 */
	protected function initFormGeneralSettings()
	{
		global $ilSetting;
		
		$this->setSubTabs('settings');
		$this->tabs_gui->setTabActive('settings');
		$this->tabs_gui->setSubTabActive('general_settings');
		
		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$this->form = new ilPropertyFormGUI();
		$this->form->setFormAction($this->ctrl->getFormAction($this, 'saveGeneralSettings'));
		
		$this->form->setTitle($this->lng->txt('general_settings'));
		
		$lua = new ilCheckboxInputGUI($this->lng->txt('enable_local_user_administration'),'lua');
		$lua->setInfo($this->lng->txt('enable_local_user_administration_info'));
		$lua->setValue(1);
		$this->form->addItem($lua);
		
		$lrua = new ilCheckboxInputGUI($this->lng->txt('restrict_user_access'),'lrua');
		$lrua->setInfo($this->lng->txt('restrict_user_access_info'));
		$lrua->setValue(1);
		$this->form->addItem($lrua);

		// enable alphabetical navigation in user administration
		$alph = new ilCheckboxInputGUI($this->lng->txt('user_adm_enable_alpha_nav'), 'user_adm_alpha_nav');
		//$alph->setInfo($this->lng->txt('restrict_user_access_info'));
		$alph->setValue(1);
		$this->form->addItem($alph);

		// account codes
		$code = new ilCheckboxInputGUI($this->lng->txt("user_account_code_setting"), "user_reactivate_code");
		$code->setInfo($this->lng->txt('user_account_code_setting_info'));
		$this->form->addItem($code);		
		
		// delete own account
		$own = new ilCheckboxInputGUI($this->lng->txt("user_allow_delete_own_account"), "user_own_account");
		$this->form->addItem($own);		
		$own_email = new ilEMailInputGUI($this->lng->txt("user_delete_own_account_notification_email"), "user_own_account_email");
		$own->addSubItem($own_email);
		
		
		// BEGIN SESSION SETTINGS
		
		// create session handling radio group
		$ssettings = new ilRadioGroupInputGUI($this->lng->txt('sess_mode'), 'session_handling_type');
	
		// first option, fixed session duration
		$fixed = new ilRadioOption($this->lng->txt('sess_fixed_duration'), ilSession::SESSION_HANDLING_FIXED);
		
		// create session reminder subform
		$cb = new ilCheckboxInputGUI($this->lng->txt("session_reminder"), "session_reminder_enabled");
		$expires = ilSession::getSessionExpireValue();
		$time = ilFormat::_secondsToString($expires, true);
		$cb->setInfo($this->lng->txt("session_reminder_info")."<br />".
			sprintf($this->lng->txt('session_reminder_session_duration'), $time));		
		$fixed->addSubItem($cb);
		
		// add session handling to radio group
		$ssettings->addOption($fixed);
		
		// second option, session control
		$ldsh = new ilRadioOption($this->lng->txt('sess_load_dependent_session_handling'), ilSession::SESSION_HANDLING_LOAD_DEPENDENT);

		// add session control subform
		require_once('Services/Authentication/classes/class.ilSessionControl.php');		
        
        // this is the max count of active sessions
		// that are getting started simlutanously
		$sub_ti = new ilTextInputGUI($this->lng->txt('session_max_count'), 'session_max_count');
		$sub_ti->setMaxLength(5);
		$sub_ti->setSize(5);
		$sub_ti->setInfo($this->lng->txt('session_max_count_info'));		
		if( !$ilSetting->get('session_allow_client_maintenance', ilSessionControl::DEFAULT_ALLOW_CLIENT_MAINTENANCE) )
			$sub_ti->setDisabled(true);
		$ldsh->addSubItem($sub_ti);
		
		// after this (min) idle time the session can be deleted,
		// if there are further requests for new sessions,
		// but max session count is reached yet
		$sub_ti = new ilTextInputGUI($this->lng->txt('session_min_idle'), 'session_min_idle');
		$sub_ti->setMaxLength(5);
		$sub_ti->setSize(5);
		$sub_ti->setInfo($this->lng->txt('session_min_idle_info'));		
		if( !$ilSetting->get('session_allow_client_maintenance', ilSessionControl::DEFAULT_ALLOW_CLIENT_MAINTENANCE) )
			$sub_ti->setDisabled(true);
		$ldsh->addSubItem($sub_ti);
		
		// after this (max) idle timeout the session expires
		// and become invalid, so it is not considered anymore
		// when calculating current count of active sessions
		$sub_ti = new ilTextInputGUI($this->lng->txt('session_max_idle'), 'session_max_idle');
		$sub_ti->setMaxLength(5);
		$sub_ti->setSize(5);
		$sub_ti->setInfo($this->lng->txt('session_max_idle_info'));		
		if( !$ilSetting->get('session_allow_client_maintenance', ilSessionControl::DEFAULT_ALLOW_CLIENT_MAINTENANCE) )
			$sub_ti->setDisabled(true);
		$ldsh->addSubItem($sub_ti);

		// this is the max duration that can elapse between the first and the secnd
		// request to the system before the session is immidietly deleted
		$sub_ti = new ilTextInputGUI(
			$this->lng->txt('session_max_idle_after_first_request'),
			'session_max_idle_after_first_request'
		);
		$sub_ti->setMaxLength(5);
		$sub_ti->setSize(5);
		$sub_ti->setInfo($this->lng->txt('session_max_idle_after_first_request_info'));	
		if( !$ilSetting->get('session_allow_client_maintenance', ilSessionControl::DEFAULT_ALLOW_CLIENT_MAINTENANCE) )
			$sub_ti->setDisabled(true);
		$ldsh->addSubItem($sub_ti);
		
		// add session control to radio group
		$ssettings->addOption($ldsh);
		
		// add radio group to form
		if( $ilSetting->get('session_allow_client_maintenance', ilSessionControl::DEFAULT_ALLOW_CLIENT_MAINTENANCE) )
        {
			// just shows the status wether the session
			//setting maintenance is allowed by setup			
			$this->form->addItem($ssettings);
        }
        else
        {
        	// just shows the status wether the session
			//setting maintenance is allowed by setup
			$ti = new ilNonEditableValueGUI($this->lng->txt('session_config'), "session_config");
			$ti->setValue($this->lng->txt('session_config_maintenance_disabled'));
			$ssettings->setDisabled(true);
			$ti->addSubItem($ssettings);
			$this->form->addItem($ti);
        }
		
		// END SESSION SETTINGS
								
		
		$this->lng->loadLanguageModule('ps');		
		
		$pass = new ilFormSectionHeaderGUI();
		$pass->setTitle($this->lng->txt('ps_password_settings'));
		$this->form->addItem($pass);
		 
		// password generation
		$cb = new ilCheckboxInputGUI($this->lng->txt("passwd_generation_pre"), "passwd_auto_generate");
		$cb->setChecked($ilSetting->get("passwd_auto_generate"));		
		$cb->setInfo($this->lng->txt("passwd_generation_info"));
		$this->form->addItem($cb);
		
		$check = new ilCheckboxInputGUI($this->lng->txt('ps_password_change_on_first_login_enabled'),'password_change_on_first_login_enabled');
		$check->setInfo($this->lng->txt('ps_password_change_on_first_login_enabled_info'));
		$this->form->addItem($check);
		
		include_once('./Services/PrivacySecurity/classes/class.ilSecuritySettings.php');

		$check = new ilCheckboxInputGUI($this->lng->txt('ps_password_must_not_contain_loginame'),'password_must_not_contain_loginame');
		$check->setInfo($this->lng->txt('ps_password_must_not_contain_loginame_info'));
		$this->form->addItem($check);
		
		$check = new ilCheckboxInputGUI($this->lng->txt('ps_password_chars_and_numbers_enabled'),'password_chars_and_numbers_enabled');			
		//$check->setOptionTitle($this->lng->txt('ps_password_chars_and_numbers_enabled'));
		$check->setInfo($this->lng->txt('ps_password_chars_and_numbers_enabled_info'));
		$this->form->addItem($check);

		$check = new ilCheckboxInputGUI($this->lng->txt('ps_password_special_chars_enabled'),'password_special_chars_enabled');
		//$check->setOptionTitle($this->lng->txt('ps_password_special_chars_enabled'));
		$check->setInfo($this->lng->txt('ps_password_special_chars_enabled_info'));
		$this->form->addItem($check);

		$text = new ilNumberInputGUI($this->lng->txt('ps_password_min_length'),'password_min_length');
		$text->setInfo($this->lng->txt('ps_password_min_length_info'));
		$text->setSize(1);
		$text->setMaxLength(2);
		$this->form->addItem($text);

		$text = new ilNumberInputGUI($this->lng->txt('ps_password_max_length'),'password_max_length');
		$text->setInfo($this->lng->txt('ps_password_max_length_info'));
		$text->setSize(2);
		$text->setMaxLength(3);
		$this->form->addItem($text);

		$text = new ilNumberInputGUI($this->lng->txt('ps_password_uppercase_chars_num'), 'password_ucase_chars_num');
		$text->setInfo($this->lng->txt('ps_password_uppercase_chars_num_info'));
		$text->setMinValue(0);
		$text->setSize(2);
		$text->setMaxLength(3);
		$this->form->addItem($text);

		$text = new ilNumberInputGUI($this->lng->txt('ps_password_lowercase_chars_num'), 'password_lowercase_chars_num');
		$text->setInfo($this->lng->txt('ps_password_lowercase_chars_num_info'));
		$text->setMinValue(0);
		$text->setSize(2);
		$text->setMaxLength(3);
		$this->form->addItem($text);

		$text = new ilNumberInputGUI($this->lng->txt('ps_password_max_age'),'password_max_age');
		$text->setInfo($this->lng->txt('ps_password_max_age_info'));
		$text->setSize(2);
		$text->setMaxLength(3);
		$this->form->addItem($text);
							
		// password assistance
		$cb = new ilCheckboxInputGUI($this->lng->txt("enable_password_assistance"), "password_assistance");		
		$cb->setInfo($this->lng->txt("password_assistance_info"));
		$this->form->addItem($cb);
				
		$pass = new ilFormSectionHeaderGUI();
		$pass->setTitle($this->lng->txt('ps_security_protection'));
		$this->form->addItem($pass);
		
		$text = new ilNumberInputGUI($this->lng->txt('ps_login_max_attempts'),'login_max_attempts');
		$text->setInfo($this->lng->txt('ps_login_max_attempts_info'));
		$text->setSize(1);
		$text->setMaxLength(2);
		$this->form->addItem($text);		
		
		// prevent login from multiple pcs at the same time
		$objCb = new ilCheckboxInputGUI($this->lng->txt('ps_prevent_simultaneous_logins'), 'ps_prevent_simultaneous_logins');		
		$objCb->setValue(1);
		$objCb->setInfo($this->lng->txt('ps_prevent_simultaneous_logins_info'));
		$this->form->addItem($objCb);
		

		

		$log = new ilFormSectionHeaderGUI();
		$log->setTitle($this->lng->txt('loginname_settings'));
		$this->form->addItem($log);
		
		$chbChangeLogin = new ilCheckboxInputGUI($this->lng->txt('allow_change_loginname'), 'allow_change_loginname');
		$chbChangeLogin->setValue(1);
		$this->form->addItem($chbChangeLogin);		
		$chbCreateHistory = new ilCheckboxInputGUI($this->lng->txt('history_loginname'), 'create_history_loginname');
		$chbCreateHistory->setInfo($this->lng->txt('loginname_history_info'));
		$chbCreateHistory->setValue(1);
		
		$chbChangeLogin->addSubItem($chbCreateHistory);	
		$chbReuseLoginnames = new ilCheckboxInputGUI($this->lng->txt('reuse_of_loginnames_contained_in_history'), 'reuse_of_loginnames');
		$chbReuseLoginnames->setValue(1);
		$chbReuseLoginnames->setInfo($this->lng->txt('reuse_of_loginnames_contained_in_history_info'));
		
		$chbChangeLogin->addSubItem($chbReuseLoginnames);
		$chbChangeBlockingTime = new ilNumberInputGUI($this->lng->txt('loginname_change_blocking_time'), 'loginname_change_blocking_time');
		$chbChangeBlockingTime->allowDecimals(true);
		$chbChangeBlockingTime->setSuffix($this->lng->txt('days'));
		$chbChangeBlockingTime->setInfo($this->lng->txt('loginname_change_blocking_time_info'));
		$chbChangeBlockingTime->setSize(10);
		$chbChangeBlockingTime->setMaxLength(10);
		$chbChangeLogin->addSubItem($chbChangeBlockingTime);		
		
		$this->form->addCommandButton('saveGeneralSettings', $this->lng->txt('save'));
	}




	/**
	* Global user settings
	*
	* Allows to define global settings for user accounts
	*
	* Note: The Global user settings form allows to specify default values
	*       for some user preferences. To avoid redundant implementations, 
	*       specification of default values can be done elsewhere in ILIAS
	*       are not supported by this form. 
	*/
	function settingsObject()
	{
		global $tpl, $lng, $ilias, $ilTabs;

		include_once 'Services/Search/classes/class.ilUserSearchOptions.php';
		$lng->loadLanguageModule("administration");
		$lng->loadLanguageModule("mail");
		$this->setSubTabs('settings');
		$ilTabs->activateTab('settings');
		$ilTabs->activateSubTab('standard_fields');

		include_once("./Services/User/classes/class.ilUserFieldSettingsTableGUI.php");
		$tab = new ilUserFieldSettingsTableGUI($this, "settings");
		if($this->confirm_change) $tab->setConfirmChange();
		$tpl->setContent($tab->getHTML());
	}
	
	function confirmSavedObject()
	{
		$this->saveGlobalUserSettingsObject("save");
	}
	
	function saveGlobalUserSettingsObject($action = "")
	{
		include_once 'Services/Search/classes/class.ilUserSearchOptions.php';
		include_once 'Services/PrivacySecurity/classes/class.ilPrivacySettings.php';

		global $ilias,$ilSetting;
		
		// see ilUserFieldSettingsTableGUI
		include_once("./Services/User/classes/class.ilUserProfile.php");
		$up = new ilUserProfile();
		$up->skipField("username");
		$field_properties = $up->getStandardFields();
		$profile_fields = array_keys($field_properties);
				
		$valid = true;
		foreach ($profile_fields as $field)
		{
			if (	$_POST["chb"]["required_".$field] &&
					!(int)$_POST['chb']['visib_reg_' . $field]
			){
				$valid = false;
				break;
			}
		}
		
		if(!$valid)
		{
			global $lng;
			ilUtil::sendFailure($lng->txt('invalid_visible_required_options_selected'));
			$this->confirm_change = 1;
			$this->settingsObject();
			return;
		}

		// For the following fields, the required state can not be changed
		$fixed_required_fields = array(
			"firstname" => 1,
			"lastname" => 1,
			"upload" => 0,
			"password" => 0,
			"language" => 0,
			"skin_style" => 0,
			"hits_per_page" => 0,
			/*"show_users_online" => 0,*/
			"hide_own_online_status" => 0
		);
		
		// check if a course export state of any field has been added
		$privacy = ilPrivacySettings::_getInstance();
		if ($privacy->enabledCourseExport() == true && 
			$privacy->courseConfirmationRequired() == true && 
			$action != "save")
		{
			foreach ($profile_fields as $field)
			{			
				if (! $ilias->getSetting("usr_settings_course_export_" . $field) && $_POST["chb"]["course_export_" . $field] == "1")
				{
					#ilUtil::sendQuestion($this->lng->txt('confirm_message_course_export'));
					#$this->confirm_change = 1;
					#$this->settingsObject();
					#return;
				}			
			}			
		}
		// Reset user confirmation
		if($action == 'save')
		{
			include_once('Services/Membership/classes/class.ilMemberAgreement.php');
			ilMemberAgreement::_reset();	
		}

		foreach ($profile_fields as $field)
		{
			// Enable disable searchable
			if(ilUserSearchOptions::_isSearchable($field))
			{
				ilUserSearchOptions::_saveStatus($field,(bool) $_POST['chb']['searchable_'.$field]);
			}
		
			if (!$_POST["chb"]["visible_".$field] && !$field_properties[$field]["visible_hide"])
			{
				$ilias->setSetting("usr_settings_hide_".$field, "1");
			}
			else
			{
				$ilias->deleteSetting("usr_settings_hide_".$field);
			}

			if (!$_POST["chb"]["changeable_" . $field] && !$field_properties[$field]["changeable_hide"])
			{
				$ilias->setSetting("usr_settings_disable_".$field, "1");
			}
			else
			{
				$ilias->deleteSetting("usr_settings_disable_".$field);
			}

			// registration visible			
			if ((int)$_POST['chb']['visib_reg_' . $field] && !$field_properties[$field]["visib_reg_hide"])
			{
				$ilSetting->set('usr_settings_visib_reg_'.$field, '1');
			}
			else
			{
				$ilSetting->set('usr_settings_visib_reg_'.$field, '0');
			}

			if ((int)$_POST['chb']['visib_lua_' . $field])
			{
				
				$ilSetting->set('usr_settings_visib_lua_'.$field, '1');
			}
			else
			{
				$ilSetting->set('usr_settings_visib_lua_'.$field, '0');
			}

			if ((int)$_POST['chb']['changeable_lua_' . $field])
			{
				
				$ilSetting->set('usr_settings_changeable_lua_'.$field, '1');
			}
			else
			{
				$ilSetting->set('usr_settings_changeable_lua_'.$field, '0');
			}

			if ($_POST["chb"]["export_" . $field] && !$field_properties[$field]["export_hide"])
			{
				$ilias->setSetting("usr_settings_export_".$field, "1");
			}
			else
			{
				$ilias->deleteSetting("usr_settings_export_".$field);
			}
			
			// Course export/visibility
			if ($_POST["chb"]["course_export_" . $field] && !$field_properties[$field]["course_export_hide"])
			{
				$ilias->setSetting("usr_settings_course_export_".$field, "1");
			}
			else
			{
				$ilias->deleteSetting("usr_settings_course_export_".$field);
			}
			
			// Group export/visibility
			if ($_POST["chb"]["group_export_" . $field] && !$field_properties[$field]["group_export_hide"])
			{
				$ilias->setSetting("usr_settings_group_export_".$field, "1");
			}
			else
			{
				$ilias->deleteSetting("usr_settings_group_export_".$field);
			}

			$is_fixed = array_key_exists($field, $fixed_required_fields);
			if ($is_fixed && $fixed_required_fields[$field] || ! $is_fixed && $_POST["chb"]["required_".$field])
			{
				$ilias->setSetting("require_".$field, "1");
			}
			else
			{
				$ilias->deleteSetting("require_" . $field);
			}
		}

		if ($_POST["select"]["default_hits_per_page"])
		{	
			$ilias->setSetting("hits_per_page",$_POST["select"]["default_hits_per_page"]);
		}

		/*if ($_POST["select"]["default_show_users_online"])
		{
			$ilias->setSetting("show_users_online",$_POST["select"]["default_show_users_online"]);
		}*/
		
		if ($_POST["chb"]["export_preferences"])
		{
			$ilias->setSetting("usr_settings_export_preferences",$_POST["chb"]["export_preferences"]);
		} else {
			$ilias->deleteSetting("usr_settings_export_preferences");
		}
		
		$ilias->setSetting('mail_incoming_mail', (int)$_POST['select']['default_mail_incoming_mail']);

		ilUtil::sendSuccess($this->lng->txt("usr_settings_saved"));
		$this->settingsObject();
	}
	
	
	/**
	*	build select form to distinguish between active and non-active users
	*/
	function __buildUserFilterSelect()
	{
		$action[-1] = $this->lng->txt('all_users');
		$action[1] = $this->lng->txt('usr_active_only');
		$action[0] = $this->lng->txt('usr_inactive_only');
		$action[2] = $this->lng->txt('usr_limited_access_only');
		$action[3] = $this->lng->txt('usr_without_courses');
		$action[4] = $this->lng->txt('usr_filter_lastlogin');
		$action[5] = $this->lng->txt("usr_filter_coursemember");
		$action[6] = $this->lng->txt("usr_filter_groupmember");
		$action[7] = $this->lng->txt("usr_filter_role");

		return  ilUtil::formSelect($_SESSION['user_filter'],"user_filter",$action,false,true);
	}

	/**
	* Download selected export files
	*
	* Sends a selected export file for download
	*
	*/
	function downloadExportFileObject()
	{
		if(!isset($_POST["file"]))
		{
			$this->ilias->raiseError($this->lng->txt("no_checkbox"),$this->ilias->error_obj->MESSAGE);
		}

		if (count($_POST["file"]) > 1)
		{
			$this->ilias->raiseError($this->lng->txt("select_max_one_item"),$this->ilias->error_obj->MESSAGE);
		}

		$file = basename($_POST["file"][0]);

		$export_dir = $this->object->getExportDirectory();
		ilUtil::deliverFile($export_dir."/".$file, $file);
	}
	
	/**
	* confirmation screen for export file deletion
	*/
	function confirmDeleteExportFileObject()
	{
		if(!isset($_POST["file"]))
		{
			$this->ilias->raiseError($this->lng->txt("no_checkbox"),$this->ilias->error_obj->MESSAGE);
		}

		// display confirmation message
		include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
		$cgui = new ilConfirmationGUI();
		$cgui->setFormAction($this->ctrl->getFormAction($this));
		$cgui->setHeaderText($this->lng->txt("info_delete_sure"));
		$cgui->setCancel($this->lng->txt("cancel"), "cancelDeleteExportFile");
		$cgui->setConfirm($this->lng->txt("confirm"), "deleteExportFile");		

		// BEGIN TABLE DATA		
		foreach($_POST["file"] as $file)
		{							
			$cgui->addItem("file[]", $file, $file, ilObject::_getIcon($this->object->getId()), $this->lng->txt("obj_usrf"));
		}

		$this->tpl->setContent($cgui->getHTML());
	}


	/**
	* cancel deletion of export files
	*/
	function cancelDeleteExportFileObject()
	{
		$this->ctrl->redirectByClass("ilobjuserfoldergui", "export");
	}


	/**
	* delete export files
	*/
	function deleteExportFileObject()
	{
		$export_dir = $this->object->getExportDirectory();
		foreach($_POST["file"] as $file)
		{
			$file = basename($file);
			
			$exp_file = $export_dir."/".$file;
			if (@is_file($exp_file))
			{
				unlink($exp_file);
			}
		}
		$this->ctrl->redirectByClass("ilobjuserfoldergui", "export");
	}

	/**
	* Global user settings
	*
	* Allows to define global settings for user accounts
	*
	* Note: The Global user settings form allows to specify default values
	*       for some user preferences. To avoid redundant implementations, 
	*       specification of default values can be done elsewhere in ILIAS
	*       are not supported by this form. 
	*/
	function exportObject()
	{
		global $ilias, $ilCtrl;
		
		if ($_POST["cmd"]["export"])
		{
			$this->object->buildExportFile($_POST["export_type"]);
			$this->ctrl->redirectByClass("ilobjuserfoldergui", "export");
			exit;
		}
		
		$this->tpl->addBlockfile('ADM_CONTENT','adm_content','tpl.usr_export.html','Services/User');
		
		$export_types = array(
			"userfolder_export_excel_x86",
			"userfolder_export_csv",
			"userfolder_export_xml"
		);

		// create table
		include_once("./Services/Table/classes/class.ilTableGUI.php");
		$tbl = new ilTableGUI();

		// load files templates
		$this->tpl->addBlockfile("EXPORT_FILES", "export_files", "tpl.table.html");

		// load template for table content data
		$this->tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.usr_export_file_row.html", "Services/User");

		$num = 0;

		$tbl->setTitle($this->lng->txt("userfolder_export_files"));

		$tbl->setHeaderNames(array("", $this->lng->txt("userfolder_export_file"),
			$this->lng->txt("userfolder_export_file_size"), $this->lng->txt("date") ));
		$tbl->setHeaderVars(array(), $ilCtrl->getParameterArray($this, "export"));

		$tbl->enabled["sort"] = false;
		$tbl->setColumnWidth(array("1%", "49%", "25%", "25%"));

		// control
		$tbl->setOrderColumn($_GET["sort_by"]);
		$tbl->setOrderDirection($_GET["sort_order"]);
		$tbl->setLimit($_GET["limit"]);
		$tbl->setOffset($_GET["offset"]);
		$tbl->setMaxCount($this->maxcount);		// ???


		$this->tpl->setVariable("COLUMN_COUNTS", 4);

		// delete button
		$this->tpl->setVariable("IMG_ARROW", ilUtil::getImagePath("arrow_downright.svg"));
		$this->tpl->setVariable("ALT_ARROW", $this->lng->txt("actions"));
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
		//$tbl->disable("footer");

		$export_files = $this->object->getExportFiles();

		$tbl->setMaxCount(count($export_files));
		$export_files = array_slice($export_files, $_GET["offset"], $_GET["limit"]);

		$tbl->render();

		if(count($export_files) > 0)
		{
			$i=0;
			foreach($export_files as $exp_file)
			{
				$this->tpl->setCurrentBlock("tbl_content");
				$this->tpl->setVariable("TXT_FILENAME", $exp_file["filename"]);

				$css_row = ilUtil::switchColor($i++, "tblrow1", "tblrow2");
				$this->tpl->setVariable("CSS_ROW", $css_row);

				$this->tpl->setVariable("TXT_SIZE", $exp_file["filesize"]);
				$this->tpl->setVariable("CHECKBOX_ID", $exp_file["filename"]);

				$file_arr = explode("__", $exp_file["filename"]);
				$this->tpl->setVariable('TXT_DATE',ilDatePresentation::formatDate(new ilDateTime($file_arr[0],IL_CAL_UNIX)));

				$this->tpl->parseCurrentBlock();
			}
		
			$this->tpl->setCurrentBlock("selectall");
			$this->tpl->setVariable("SELECT_ALL", $this->lng->txt("select_all"));
			$this->tpl->setVariable("CSS_ROW", $css_row);
			$this->tpl->parseCurrentBlock();
		} //if is_array
		/*
		else
		
		{
			$this->tpl->setCurrentBlock("notfound");
			$this->tpl->setVariable("TXT_OBJECT_NOT_FOUND", $this->lng->txt("obj_not_found"));
			$this->tpl->setVariable("NUM_COLS", 3);
			$this->tpl->parseCurrentBlock();
		}
		*/
		
		$this->tpl->parseCurrentBlock();
		
		
		foreach ($export_types as $export_type)
		{		
			$this->tpl->setCurrentBlock("option");
			$this->tpl->setVariable("OPTION_VALUE", $export_type);
			$this->tpl->setVariable("OPTION_TEXT", $this->lng->txt($export_type));
			$this->tpl->parseCurrentBlock();
		}

		$this->tpl->setVariable("EXPORT_BUTTON", $this->lng->txt("create_export_file"));
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
	}
	
	protected function initNewAccountMailForm()
	{
		global $lng, $ilCtrl;
		
		$lng->loadLanguageModule("meta");
		$lng->loadLanguageModule("mail");
		
		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setFormAction($ilCtrl->getFormAction($this));
		
		$form->setTitleIcon(ilUtil::getImagePath("icon_mail.svg"));
		$form->setTitle($lng->txt("user_new_account_mail"));
		$form->setDescription($lng->txt("user_new_account_mail_desc"));
				
		$langs = $lng->getInstalledLanguages();
		foreach($langs as $lang_key)
		{
			$amail = $this->object->_lookupNewAccountMail($lang_key);
			
			$title = $lng->txt("meta_l_".$lang_key);
			if ($lang_key == $lng->getDefaultLanguage())
			{
				$title .= " (".$lng->txt("default").")";
			}
			
			$header = new ilFormSectionHeaderGUI();
			$header->setTitle($title);
			$form->addItem($header);
													
			$subj = new ilTextInputGUI($lng->txt("subject"), "subject_".$lang_key);
			// $subj->setRequired(true);
			$subj->setValue($amail["subject"]);
			$form->addItem($subj);
			
			$salg = new ilTextInputGUI($lng->txt("mail_salutation_general"), "sal_g_".$lang_key);
			// $salg->setRequired(true);
			$salg->setValue($amail["sal_g"]);
			$form->addItem($salg);
			
			$salf = new ilTextInputGUI($lng->txt("mail_salutation_female"), "sal_f_".$lang_key);
			// $salf->setRequired(true);
			$salf->setValue($amail["sal_f"]);
			$form->addItem($salf);
			
			$salm = new ilTextInputGUI($lng->txt("mail_salutation_male"), "sal_m_".$lang_key);
			// $salm->setRequired(true);
			$salm->setValue($amail["sal_m"]);
			$form->addItem($salm);
		
			$body = new ilTextAreaInputGUI($lng->txt("message_content"), "body_".$lang_key);
			// $body->setRequired(true);
			$body->setValue($amail["body"]);
			$body->setRows(10);
			$body->setCols(100);
			$form->addItem($body);
			
			$att = new ilFileInputGUI($lng->txt("attachment"), "att_".$lang_key);
			$att->setAllowDeletion(true);
			if($amail["att_file"])
			{
				$att->setValue($amail["att_file"]);
			}
			$form->addItem($att);
		}		
	
		$form->addCommandButton("saveNewAccountMail", $lng->txt("save"));
		$form->addCommandButton("cancelNewAccountMail", $lng->txt("cancel"));
				
		return $form;		
	}
	
	/**
	* new account mail administration
	*/
	function newAccountMailObject()
	{
		global $lng;
		
		$this->setSubTabs('settings');
		$this->tabs_gui->setTabActive('settings');
		$this->tabs_gui->setSubTabActive('user_new_account_mail');
				
		$form = $this->initNewAccountMailForm();	
		
		$ftpl = new ilTemplate('tpl.usrf_new_account_mail.html', true, true, 'Services/User');
		$ftpl->setVariable("FORM", $form->getHTML());
		unset($form);

		// placeholder help text
		$ftpl->setVariable("TXT_USE_PLACEHOLDERS", $lng->txt("mail_nacc_use_placeholder"));
		$ftpl->setVariable("TXT_MAIL_SALUTATION", $lng->txt("mail_nacc_salutation"));
		$ftpl->setVariable("TXT_FIRST_NAME", $lng->txt("firstname"));
		$ftpl->setVariable("TXT_LAST_NAME", $lng->txt("lastname"));
		$ftpl->setVariable("TXT_EMAIL", $lng->txt("email"));
		$ftpl->setVariable("TXT_LOGIN", $lng->txt("mail_nacc_login"));
		$ftpl->setVariable("TXT_PASSWORD", $lng->txt("password"));
		$ftpl->setVariable("TXT_PASSWORD_BLOCK", $lng->txt("mail_nacc_pw_block"));
		$ftpl->setVariable("TXT_NOPASSWORD_BLOCK", $lng->txt("mail_nacc_no_pw_block"));
		$ftpl->setVariable("TXT_ADMIN_MAIL", $lng->txt("mail_nacc_admin_mail"));
		$ftpl->setVariable("TXT_ILIAS_URL", $lng->txt("mail_nacc_ilias_url"));
		$ftpl->setVariable("TXT_CLIENT_NAME", $lng->txt("mail_nacc_client_name"));
		$ftpl->setVariable("TXT_TARGET", $lng->txt("mail_nacc_target"));
		$ftpl->setVariable("TXT_TARGET_TITLE", $lng->txt("mail_nacc_target_title"));
		$ftpl->setVariable("TXT_TARGET_TYPE", $lng->txt("mail_nacc_target_type"));
		$ftpl->setVariable("TXT_TARGET_BLOCK", $lng->txt("mail_nacc_target_block"));	
		$ftpl->setVariable("TXT_IF_TIMELIMIT", $lng->txt("mail_nacc_if_timelimit"));	
		$ftpl->setVariable("TXT_TIMELIMIT", $lng->txt("mail_nacc_timelimit"));	
		
		$this->tpl->setContent($ftpl->get());
	}

	function cancelNewAccountMailObject()
	{
		$this->ctrl->redirect($this, "settings");
	}

	function saveNewAccountMailObject()
	{
		global $lng;
				
		$langs = $lng->getInstalledLanguages();
		foreach($langs as $lang_key)
		{
			$this->object->_writeNewAccountMail($lang_key,
				ilUtil::stripSlashes($_POST["subject_".$lang_key]),
				ilUtil::stripSlashes($_POST["sal_g_".$lang_key]),
				ilUtil::stripSlashes($_POST["sal_f_".$lang_key]),
				ilUtil::stripSlashes($_POST["sal_m_".$lang_key]),
				ilUtil::stripSlashes($_POST["body_".$lang_key]));
						
			if($_FILES["att_".$lang_key]["tmp_name"])
			{
				$this->object->_updateAccountMailAttachment($lang_key, 
					$_FILES["att_".$lang_key]["tmp_name"],
					$_FILES["att_".$lang_key]["name"]);				
			}

			if ($_POST["att_".$lang_key."_delete"])
			{
				$this->object->_deleteAccountMailAttachment($lang_key);
			}		
		}
		
		ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);
		$this->ctrl->redirect($this, "newAccountMail");
	}

	function getAdminTabs(&$tabs_gui)
	{
		$this->getTabs($tabs_gui);
	}

	/**
	* get tabs
	* @access	public
	* @param	object	tabs gui object
	*/
	function getTabs(&$tabs_gui)
	{
		include_once 'Services/Tracking/classes/class.ilObjUserTracking.php';

		global $rbacsystem;
		
		if ($rbacsystem->checkAccess("visible,read",$this->object->getRefId()))
		{
			$tabs_gui->addTarget("usrf",
				$this->ctrl->getLinkTarget($this, "view"), array("view","delete","resetFilter", "userAction", ""), "", "");

			$tabs_gui->addTarget(
				"search_user_extended",
				$this->ctrl->getLinkTargetByClass('ilRepositorySearchGUI',''),
				array(),
				"ilrepositorysearchgui",
				""
			);
		}
		
		if ($rbacsystem->checkAccess("write",$this->object->getRefId()))
		{
			$tabs_gui->addTarget("settings",
				$this->ctrl->getLinkTarget($this, "generalSettings"),array('settings','generalSettings','listUserDefinedField','newAccountMail'));
				
			$tabs_gui->addTarget("export",
				$this->ctrl->getLinkTarget($this, "export"), "export", "", "");

			/* deprecated, JF 27 May 2013
			if(ilObjUserTracking::_enabledLearningProgress() &&
				ilObjUserTracking::_enabledUserRelatedData())
			{
				$tabs_gui->addTarget("learning_progress",
									 $this->ctrl->getLinkTarget($this, "learningProgress"), "learningProgress", "", "");
			}			  
			*/
		}

		if ($rbacsystem->checkAccess('edit_permission',$this->object->getRefId()))
		{
			$tabs_gui->addTarget("perm_settings",
								 $this->ctrl->getLinkTargetByClass(array(get_class($this),'ilpermissiongui'), "perm"), 
								 array("perm","info","owner"), 'ilpermissiongui');
		}
	}


	/**
	* set sub tabs
	*/
	function setSubTabs($a_tab)
	{
		global $rbacsystem,$ilUser;
		
		switch($a_tab)
		{
			case "settings":
				$this->tabs_gui->addSubTabTarget(
					'general_settings',
					$this->ctrl->getLinkTarget($this, 'generalSettings'), 'generalSettings', get_class($this));												 
				$this->tabs_gui->addSubTabTarget("standard_fields",
												 $this->ctrl->getLinkTarget($this,'settings'),
												 array("settings", "saveGlobalUserSettings"), get_class($this));
				$this->tabs_gui->addSubTabTarget("user_defined_fields",
												 $this->ctrl->getLinkTargetByClass("ilcustomuserfieldsgui", "listUserDefinedFields"),
												 "listUserDefinedFields",get_class($this));
				$this->tabs_gui->addSubTabTarget("user_new_account_mail",
												 $this->ctrl->getLinkTarget($this,'newAccountMail'),
												 "newAccountMail",get_class($this));				
				#$this->tabs_gui->addSubTab("account_codes", $this->lng->txt("user_account_codes"),
				#							 $this->ctrl->getLinkTargetByClass("ilaccountcodesgui"));												 
				break;
		}
	}
	
	public function showLoginnameSettingsObject()
	{
		global $ilSetting;	
		
		$show_blocking_time_in_days = (int)$ilSetting->get('loginname_change_blocking_time') / 86400;
		
		$this->initLoginSettingsForm();
		$this->loginSettingsForm->setValuesByArray(array(
			'allow_change_loginname' => (bool)$ilSetting->get('allow_change_loginname'),
			'create_history_loginname' => (bool)$ilSetting->get('create_history_loginname'),
			'reuse_of_loginnames' => (bool)$ilSetting->get('reuse_of_loginnames'),
			'loginname_change_blocking_time' => (float)$show_blocking_time_in_days
		));
		
		$this->tpl->setVariable('ADM_CONTENT', $this->loginSettingsForm->getHTML());
	}
	
	private function initLoginSettingsForm()
	{
		$this->setSubTabs('settings');
		$this->tabs_gui->setTabActive('settings');
		$this->tabs_gui->setSubTabActive('loginname_settings');
		
		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$this->loginSettingsForm = new ilPropertyFormGUI;
		$this->loginSettingsForm->setFormAction($this->ctrl->getFormAction($this, 'saveLoginnameSettings'));
		$this->loginSettingsForm->setTitle($this->lng->txt('loginname_settings'));
		
		$chbChangeLogin = new ilCheckboxInputGUI($this->lng->txt('allow_change_loginname'), 'allow_change_loginname');
		$chbChangeLogin->setValue(1);
		$this->loginSettingsForm->addItem($chbChangeLogin);		
			$chbCreateHistory = new ilCheckboxInputGUI($this->lng->txt('history_loginname'), 'create_history_loginname');
			$chbCreateHistory->setInfo($this->lng->txt('loginname_history_info'));
			$chbCreateHistory->setValue(1);
		$chbChangeLogin->addSubItem($chbCreateHistory);	
			$chbReuseLoginnames = new ilCheckboxInputGUI($this->lng->txt('reuse_of_loginnames_contained_in_history'), 'reuse_of_loginnames');
			$chbReuseLoginnames->setValue(1);
			$chbReuseLoginnames->setInfo($this->lng->txt('reuse_of_loginnames_contained_in_history_info'));
		$chbChangeLogin->addSubItem($chbReuseLoginnames);
			$chbChangeBlockingTime = new ilNumberInputGUI($this->lng->txt('loginname_change_blocking_time'), 'loginname_change_blocking_time');
			$chbChangeBlockingTime->allowDecimals(true);
			$chbChangeBlockingTime->setSuffix($this->lng->txt('days'));
			$chbChangeBlockingTime->setInfo($this->lng->txt('loginname_change_blocking_time_info'));
			$chbChangeBlockingTime->setSize(10);
			$chbChangeBlockingTime->setMaxLength(10);
		$chbChangeLogin->addSubItem($chbChangeBlockingTime);		
		
		$this->loginSettingsForm->addCommandButton('saveLoginnameSettings', $this->lng->txt('save'));
	}
	
	public function saveLoginnameSettingsObject()
	{
		global $ilUser, $ilSetting;
		
		$this->initLoginSettingsForm();
		if($this->loginSettingsForm->checkInput())
		{
			$valid = true;
			
			if(!strlen($this->loginSettingsForm->getInput('loginname_change_blocking_time')))
			{
				$valid = false;
				$this->loginSettingsForm->getItemByPostVar('loginname_change_blocking_time')
										->setAlert($this->lng->txt('loginname_change_blocking_time_invalidity_info'));
			}
			
			if($valid)
			{	
				$save_blocking_time_in_seconds = (int)$this->loginSettingsForm->getInput('loginname_change_blocking_time') * 86400;
				
				$ilSetting->set('allow_change_loginname', (int)$this->loginSettingsForm->getInput('allow_change_loginname'));
				$ilSetting->set('create_history_loginname', (int)$this->loginSettingsForm->getInput('create_history_loginname'));
				$ilSetting->set('reuse_of_loginnames', (int)$this->loginSettingsForm->getInput('reuse_of_loginnames'));
				$ilSetting->set('loginname_change_blocking_time', (int)$save_blocking_time_in_seconds);
				
				ilUtil::sendSuccess($this->lng->txt('saved_successfully'));
			}
			else
			{
				ilUtil::sendFailure($this->lng->txt('form_input_not_valid'));
			}
		}
		else
		{
			ilUtil::sendFailure($this->lng->txt('form_input_not_valid'));
		}
		$this->loginSettingsForm->setValuesByPost();		
	
		$this->tpl->setVariable('ADM_CONTENT', $this->loginSettingsForm->getHTML());
	}

	/**
	 * goto target group
	 */
	public static function _goto($a_user)
	{
		global $ilAccess, $ilErr, $lng;

		$a_target = USER_FOLDER_ID;

		if ($ilAccess->checkAccess("read", "", $a_target))
		{
			ilUtil::redirect("ilias.php?baseClass=ilAdministrationGUI&ref_id=".$a_target."&jmpToUser=".$a_user);
			exit;
		}
		else
		{
			if ($ilAccess->checkAccess("read", "", ROOT_FOLDER_ID))
			{
				ilUtil::sendFailure(sprintf($lng->txt("msg_no_perm_read_item"),
					ilObject::_lookupTitle(ilObject::_lookupObjId($a_target))), true);
				ilObjectGUI::_gotoRepositoryRoot();
			}
		}
		$ilErr->raiseError($lng->txt("msg_no_perm_read"), $ilErr->FATAL);
	}

	/**
	 * Jump to edit screen for user
	 */
	function jumpToUserObject()
	{
		global $ilCtrl;

		if (((int) $_GET["jmpToUser"]) > 0 && ilObject::_lookupType((int)$_GET["jmpToUser"]) == "usr")
		{
			$ilCtrl->setParameterByClass("ilobjusergui", "obj_id", (int) $_GET["jmpToUser"]);
			$ilCtrl->redirectByClass("ilobjusergui", "view");
		}
	}

	/**
	 * Handles multi command from repository search gui
	 */
	public  function searchResultHandler($a_usr_ids,$a_cmd)
	{
		if(!count((array) $a_usr_ids))
		{
			ilUtil::sendFailure($this->lng->txt('select_one'));
			return false;
		}
		
		$_POST['id'] = $a_usr_ids;
		
		// no real confirmation here
		if(stristr($a_cmd, "export"))
		{
			$cmd = $a_cmd."Object";
			return $this->$cmd();
		}	
		
		$_POST['selectedAction'] = $a_cmd;		
		return $this->showActionConfirmation($a_cmd, true);
	}
	
	public function getUserMultiCommands($a_search_form = false)
	{
		global $rbacsystem, $ilUser;		
		
		// see searchResultHandler()
		if($a_search_form)
		{
			$cmds = array(
				'activate' => $this->lng->txt('activate'),
				'deactivate' => $this->lng->txt('deactivate'),
				'accessRestrict' => $this->lng->txt('accessRestrict'),
				'accessFree' => $this->lng->txt('accessFree')
				);
		
			if ($rbacsystem->checkAccess('delete', $this->object->getRefId()))
			{			
				$cmds["delete"] = $this->lng->txt("delete");
			}						
		}
		// show confirmation
		else
		{
			$cmds = array(
				'activateUsers'	=> $this->lng->txt('activate'),
				'deactivateUsers' => $this->lng->txt('deactivate'),
				'restrictAccess' => $this->lng->txt('accessRestrict'),
				'freeAccess' => $this->lng->txt('accessFree')
				);
			
			if ($rbacsystem->checkAccess('delete', $this->object->getRefId()))
			{
				$cmds["deleteUsers"] = $this->lng->txt("delete");				
			}				
		}
				
		// no confirmation needed
		$export_types = array("userfolder_export_excel_x86", "userfolder_export_csv", "userfolder_export_xml");		
		foreach ($export_types as $type)
		{
			$cmd = explode("_", $type);
			$cmd = array_pop($cmd);
			$cmds['usrExport'.ucfirst($cmd)] = $this->lng->txt('export').' - '.
				$this->lng->txt($type);
		}
		
		// check if current user may send mails
		include_once "Services/Mail/classes/class.ilMail.php";
		$mail = new ilMail($ilUser->getId());
		if($rbacsystem->checkAccess('internal_mail', $mail->getMailObjectReferenceId()))
		{			
			$cmds["mail"] = $this->lng->txt("send_mail");
		}
						
		return $cmds;
	}
	
	function usrExportX86Object()
	{
		$user_ids = $this->getActionUserIds();	
		if(!$user_ids)
		{
			ilUtil::sendFailure($this->lng->txt('select_one'));
			return $this->viewObject();
		}
		$this->object->buildExportFile("userfolder_export_excel_x86", $user_ids);		
		$this->ctrl->redirectByClass("ilobjuserfoldergui", "export");
	}
	
	function usrExportCsvObject()
	{
		$user_ids = $this->getActionUserIds();	
		if(!$user_ids)
		{
			ilUtil::sendFailure($this->lng->txt('select_one'));
			return $this->viewObject();
		}
		$this->object->buildExportFile("userfolder_export_csv", $user_ids);		
		$this->ctrl->redirectByClass("ilobjuserfoldergui", "export");
	}
	
	function usrExportXmlObject()
	{
		$user_ids = $this->getActionUserIds();	
		if(!$user_ids)
		{
			ilUtil::sendFailure($this->lng->txt('select_one'));
			return $this->viewObject();
		}
		$this->object->buildExportFile("userfolder_export_xml", $user_ids);		
		$this->ctrl->redirectByClass("ilobjuserfoldergui", "export");
	}
	
	function mailObject()
	{
		global $ilUser;
		
		$user_ids = $this->getActionUserIds();			
		if(!$user_ids)
		{
			ilUtil::sendFailure($this->lng->txt('select_one'));
			return $this->viewObject();
		}
		
		// remove existing (temporary) lists
		include_once "Services/Contact/classes/class.ilMailingLists.php";
		$list = new ilMailingLists($ilUser);
		$list->deleteTemporaryLists();
		
		// create (temporary) mailing list
		include_once "Services/Contact/classes/class.ilMailingList.php";
		$list = new ilMailingList($ilUser);		
		$list->setMode(ilMailingList::MODE_TEMPORARY);
		$list->setTitle("-TEMPORARY SYSTEM LIST-");
		$list->setDescription("-USER ACCOUNTS MAIL-");
		$list->setCreateDate(date("Y-m-d H:i:s"));		
		$list->insert();
		$list_id = $list->getId();				
		
		// after list has been saved...
		foreach($user_ids as $user_id)
		{		
			$list->assignUser($user_id);
		}
		
		include_once "Services/Mail/classes/class.ilFormatMail.php";
		$umail = new ilFormatMail($ilUser->getId());		
		$mail_data = $umail->getSavedData();		
		
		if(!is_array($mail_data))
		{
			$mail_data = array("user_id" => $ilUser->getId());
		}
				
		// ???
		// $mail_data = $umail->appendSearchResult(array('#il_ml_'.$list_id), 'to');
		
		$umail->savePostData(
			$mail_data['user_id'],
			$mail_data['attachments'],
			'#il_ml_'.$list_id, // $mail_data['rcp_to'],				
			$mail_data['rcp_cc'],
			$mail_data['rcp_bcc'],
			$mail_data['m_type'],
			$mail_data['m_email'],
			$mail_data['m_subject'],
			$mail_data['m_message'],
			$mail_data['use_placeholders'],
			$mail_data['tpl_ctx_id'],
			$mail_data['tpl_ctx_params']
		);		

		require_once 'Services/Mail/classes/class.ilMailFormCall.php';
		ilUtil::redirect(
			ilMailFormCall::getRedirectTarget(
				$this,
				'',
				array(),
				array(
					'type' => 'search_res'
				)
			)
		);
	}
	
	public function addToExternalSettingsForm($a_form_id)
	{
		switch($a_form_id)
		{
			case ilAdministrationSettingsFormHandler::FORM_SECURITY:
				
				include_once('./Services/PrivacySecurity/classes/class.ilSecuritySettings.php');
				$security = ilSecuritySettings::_getInstance();
				
				$fields = array();
				
				$subitems = array(
					'ps_password_change_on_first_login_enabled' => array($security->isPasswordChangeOnFirstLoginEnabled(), ilAdministrationSettingsFormHandler::VALUE_BOOL),
					'ps_password_must_not_contain_loginame' => array((bool)$security->getPasswordMustNotContainLoginnameStatus(), ilAdministrationSettingsFormHandler::VALUE_BOOL),
					'ps_password_chars_and_numbers_enabled' => array($security->isPasswordCharsAndNumbersEnabled(), ilAdministrationSettingsFormHandler::VALUE_BOOL),
					'ps_password_special_chars_enabled' => array($security->isPasswordSpecialCharsEnabled(), ilAdministrationSettingsFormHandler::VALUE_BOOL),
					'ps_password_min_length' => (int)$security->getPasswordMinLength(),
					'ps_password_max_length' => (int)$security->getPasswordMaxLength(),
					'ps_password_uppercase_chars_num' => (int)$security->getPasswordNumberOfUppercaseChars(),
					'ps_password_lowercase_chars_num' => (int)$security->getPasswordNumberOfLowercaseChars(),
					'ps_password_max_age' => (int)$security->getPasswordMaxAge()
				);				
				$fields['ps_password_settings'] = array(null, null, $subitems);
				
				$subitems = array(
					'ps_login_max_attempts' => (int)$security->getLoginMaxAttempts(),
					'ps_prevent_simultaneous_logins' => array($security->isPreventionOfSimultaneousLoginsEnabled(), ilAdministrationSettingsFormHandler::VALUE_BOOL)
				);				
				$fields['ps_security_protection'] = array(null, null, $subitems);
				
				return array(array("generalSettings", $fields));	
		}		
	}
	
} // END class.ilObjUserFolderGUI
?>