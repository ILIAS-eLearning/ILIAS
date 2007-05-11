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
* Class ilObjGroupGUI
*
* @author	Stefan Meyer <smeyer@databay.de>
* @author	Sascha Hofmann <saschahofmann@gmx.de>
*
* @version	$Id$
*
* @ilCtrl_Calls ilObjGroupGUI: ilRegisterGUI, ilConditionHandlerInterface, ilPermissionGUI, ilInfoScreenGUI,, ilLearningProgressGUI
* @ilCtrl_Calls ilObjGroupGUI: ilRepositorySearchGUI, ilObjUserGUI, ilObjCourseGroupingGUI
* @ilCtrl_Calls ilObjGroupGUI: ilCourseContentGUI, ilColumnGUI
*
* @extends ilObjectGUI
*/

include_once "class.ilContainerGUI.php";
include_once "class.ilRegisterGUI.php";

class ilObjGroupGUI extends ilContainerGUI
{
	/**
	* Constructor
	* @access	public
	*/
	function ilObjGroupGUI($a_data,$a_id,$a_call_by_reference,$a_prepare_output = false)
	{
		$this->type = "grp";
		$this->ilContainerGUI($a_data,$a_id,$a_call_by_reference,$a_prepare_output);

		$this->lng->loadLanguageModule('grp');
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
		else if(!$tree->checkForParentType($this->ref_id,'crs'))
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
	}


	function &executeCommand()
	{
		global $ilUser,$rbacsystem,$ilAccess, $ilNavigationHistory;

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
					$this->__setSubTabs('activation');
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

			case "ilregistergui":
				$this->ctrl->setReturn($this, "");   // ###
				$reg_gui = new ilRegisterGUI();
				$ret =& $this->ctrl->forwardCommand($reg_gui);
				$this->tabs_gui->setTabActive('join');
				break;

			case 'ilpermissiongui':
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
				$this->__setSubTabs('members');
				$this->tabs_gui->setSubTabActive('members');
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
				$this->__setSubTabs('properties');
				$crs_grp_gui =& new ilObjCourseGroupingGUI($this->object,(int) $_GET['obj_id']);
				$this->ctrl->forwardCommand($crs_grp_gui);
				$this->tabs_gui->setTabActive('edit_properties');
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
				$this->__setSubTabs('activation');
				$this->tabs_gui->setTabActive('view_content');
				$this->tabs_gui->setSubTabActive('activation');
				break;

			case 'ilobjusergui':
				require_once "./classes/class.ilObjUserGUI.php";
				$user_gui = new ilObjUserGUI("",$_GET["user"], false, false);
				$html = $this->ctrl->forwardCommand($user_gui);
				$this->__setSubTabs('members');
				$this->tabs_gui->setTabActive('group_members');
				$this->tabs_gui->setSubTabActive('grp_members_gallery');
				$this->tpl->setVariable("ADM_CONTENT", $html);
				break;

			case "ilcolumngui":
				//$this->prepareOutput();
				include_once("classes/class.ilObjStyleSheet.php");
				$this->getSubItems();
				$this->tpl->setVariable("LOCATION_CONTENT_STYLESHEET",
					ilObjStyleSheet::getContentStylePath(0));
				$this->renderObject();
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
						$this->ctrl->redirectByClass("ilRegisterGUI", "showRegistrationForm");
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

	function listExportFilesObject()
	{
		global $rbacsystem;

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
			$this->tpl->setVariable("TXT_DATE", date("Y-m-d H:i:s",$file_arr[0]));

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
			ilUtil::sendInfo('grp_select_one_file');
		}
		else
		{
			$this->object->deleteExportFiles(ilUtil::stripSlashes($_POST['file']));
			ilUtil::sendInfo('grp_deleted_export_files');
		}

		$this->listExportFilesObject();

		return true;
	}

	function downloadExportFileObject()
	{
		if(!count($_POST['file']))
		{
			ilUtil::sendInfo('grp_select_one_file');
			$this->listExportFilesObject();
			return false;
		}
		if(count($_POST['file']) > 1)
		{
			ilUtil::sendInfo('grp_select_one_file_only');
			$this->listExportFilesObject();
			return false;
		}
		
		$this->object->downloadExportFile(ilUtil::stripSlashes($_POST['file'][0]));
		
		// If file wasn't sent
		ilUtil::sendInfo('grp_error_sending_file');
		
		return true;
	}
			

	/**
	* create new object form
	*/
	function createObject()
	{
		global $rbacsystem;

		$new_type = $_POST["new_type"] ? $_POST["new_type"] : $_GET["new_type"];

		if (!$rbacsystem->checkAccess("create", $_GET["ref_id"], $new_type))
		{
			$this->ilErr->raiseError($this->lng->txt("permission_denied"),$this->ilErr->MESSAGE);
		}

		$data = array();

		if ($_SESSION["error_post_vars"])
		{
			// fill in saved values in case of error
			$data["fields"]["title"] = ilUtil::prepareFormOutput($_SESSION["error_post_vars"]["Fobject"]["title"],true);
			$data["fields"]["desc"] = ilUtil::stripSlashes($_SESSION["error_post_vars"]["Fobject"]["desc"]);
			$data["fields"]["password"] = $_SESSION["error_post_vars"]["password"];
			$data["fields"]["expirationdate"] = $_SESSION["error_post_vars"]["expirationdate"];
			$data["fields"]["expirationtime"] = $_SESSION["error_post_vars"]["expirationtime"];
		}
		else
		{
			$data["fields"]["title"] = "";
			$data["fields"]["desc"] = "";
			$data["fields"]["password"] = "";
			$data["fields"]["expirationdate"] = "";
			$data["fields"]["expirationtime"] = "";
		}

		$this->getTemplateFile("edit", $new_type);
		
		$this->tpl->setCurrentBlock("img1");
		$this->tpl->setVariable("TYPE_IMG1",
			ilUtil::getImagePath("icon_grp.gif"));
		$this->tpl->setVariable("ALT_IMG1",
			$this->lng->txt("obj_grp"));
		$this->tpl->parseCurrentBlock();

		foreach ($data["fields"] as $key => $val)
		{
			$this->tpl->setVariable("TXT_".strtoupper($key), $this->lng->txt($key));
			$this->tpl->setVariable(strtoupper($key), $val);

			if ($this->prepare_output)
			{
				$this->tpl->parseCurrentBlock();
			}
		}

		$stati 	= array(0=>$this->lng->txt("group_status_public"),1=>$this->lng->txt("group_status_closed"));

		$grp_status = $_SESSION["error_post_vars"]["group_status"];

		$checked = array(0=>0,1=>0,2=>0);

		switch ($_SESSION["error_post_vars"]["enable_registration"])
		{
			case 0:
				$checked[0]=1;
				break;

			case 1:
				$checked[1]=1;
				break;

			case 2:
				$checked[2]=1;
				break;

			default:
				$checked[0]=1;
				break;
		}

		//build form
		$cb_registration[0] = ilUtil::formRadioButton($checked[0], "enable_registration", 0);
		$cb_registration[1] = ilUtil::formRadioButton($checked[1], "enable_registration", 1);
		$cb_registration[2] = ilUtil::formRadioButton($checked[2], "enable_registration", 2);

		$opts 	= ilUtil::formSelect(0,"group_status",$stati,false,true);

		$this->tpl->setVariable("FORMACTION", $this->getFormAction("save",$this->ctrl->getFormAction($this)."&new_type=".$new_type));

		$this->tpl->setVariable("TXT_HEADER", $this->lng->txt($new_type."_new"));

		$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));
		$this->tpl->setVariable("TXT_REGISTRATION", $this->lng->txt("group_registration"));
		$this->tpl->setVariable("TXT_REGISTRATION_MODE", $this->lng->txt("group_registration_mode"));
		$this->tpl->setVariable("TXT_REGISTRATION_TIME", $this->lng->txt("group_registration_time"));

		$this->tpl->setVariable("TXT_CANCEL", $this->lng->txt("cancel"));
		$this->tpl->setVariable("TXT_SUBMIT", $this->lng->txt($new_type."_add"));
		$this->tpl->setVariable("CMD_SUBMIT", "save");
		$this->tpl->setVariable("CMD_CANCEL", "cancel");
		$this->tpl->setVariable("TARGET", $this->getTargetFrame("save"));
		$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));

		$this->tpl->setVariable("TXT_DISABLEREGISTRATION", $this->lng->txt("group_req_direct"));
		$this->tpl->setVariable("TXT_REGISTRATION_UNLIMITED", $this->lng->txt("grp_registration_unlimited"));
		$this->tpl->setVariable("RB_NOREGISTRATION", $cb_registration[0]);
		$this->tpl->setVariable("TXT_ENABLEREGISTRATION", $this->lng->txt("group_req_registration"));
		$this->tpl->setVariable("RB_REGISTRATION", $cb_registration[1]);
		$this->tpl->setVariable("TXT_PASSWORDREGISTRATION", $this->lng->txt("group_req_password"));
		$this->tpl->setVariable("RB_PASSWORDREGISTRATION", $cb_registration[2]);

		$this->tpl->setVariable("TXT_EXPIRATIONDATE", $this->lng->txt("group_registration_expiration_date"));
		$this->tpl->setVariable("TXT_EXPIRATIONTIME", $this->lng->txt("group_registration_expiration_time"));
		$this->tpl->setVariable("TXT_DATE", $this->lng->txt("DD.MM.YYYY"));
		$this->tpl->setVariable("TXT_TIME", $this->lng->txt("HH:MM"));

		$this->tpl->setVariable("CB_KEYREGISTRATION", $cb_keyregistration);
		$this->tpl->setVariable("TXT_KEYREGISTRATION", $this->lng->txt("group_keyregistration"));
		$this->tpl->setVariable("TXT_PASSWORD", $this->lng->txt("password"));
		$this->tpl->setVariable("SELECT_GROUPSTATUS", $opts);
		$this->tpl->setVariable("TXT_GROUP_STATUS", $this->lng->txt("group_status"));
		$this->tpl->setVariable("TXT_GROUP_STATUS_DESC", $this->lng->txt("group_status_desc"));

		$this->tpl->setCurrentBlock("img2");
		$this->tpl->setVariable("TYPE_IMG2",
			ilUtil::getImagePath("icon_grp.gif"));
		$this->tpl->setVariable("ALT_IMG2",
			$this->lng->txt("obj_grp"));
		$this->tpl->parseCurrentBlock();

		// IMPORT
		$this->tpl->setCurrentBlock("create");
		$this->tpl->setVariable("TXT_IMPORT_GRP", $this->lng->txt("import_grp"));
		$this->tpl->setVariable("TXT_GRP_FILE", $this->lng->txt("file"));
		$this->tpl->setVariable("TXT_IMPORT", $this->lng->txt("import"));
		
		$this->tpl->setVariable("TXT_CANCEL2", $this->lng->txt("cancel"));
		$this->tpl->setVariable("CMD_CANCEL2", "cancel");

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
		$this->tpl->parseCurrentBlock();

		$this->tpl->setCurrentBlock("fileinfo");
		$this->tpl->setVariable("TXT_FILE_INFO", $this->lng->txt("file_notice").$max_filesize);
		$this->tpl->parseCurrentBlock();
		
		$this->fillCloneTemplate('DUPLICATE','grp');
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
	* canceledObject is called when operation is canceled, method links back
	* @access	public
	*/
	function cancelMemberObject()
	{
		unset($_SESSION['grp_usr_search_result']);
		$return_location = "members";
		
		ilUtil::sendInfo($this->lng->txt("action_aborted"),true);
		ilUtil::redirect($this->ctrl->getLinkTarget($this,$return_location));
	}
	
	/**
	* save group object
	* @access	public
	*/
	function saveObject()
	{
		global $rbacadmin;

		// check required fields
		if (empty($_POST["Fobject"]["title"]))
		{
			$this->ilErr->raiseError($this->lng->txt("fill_out_all_required_fields"),$this->ilErr->MESSAGE);
		}

		// check registration & password
		if ($_POST["enable_registration"] == 2 and empty($_POST["password"]))
		{
			$this->ilErr->raiseError($this->lng->txt("no_password"),$this->ilErr->MESSAGE);
		}

		// create and insert group in objecttree
		$groupObj = parent::saveObject();
		
		// setup rolefolder & default local roles (admin & member)
		$roles = $groupObj->initDefaultRoles();
		$groupObj->initGroupStatus((int) $_POST['group_status']);

		// ...finally assign groupadmin role to creator of group object
		$groupObj->addMember($this->ilias->account->getId(),$groupObj->getDefaultAdminRole());

		$groupObj->setRegistrationFlag(ilUtil::stripSlashes($_POST["enable_registration"]));//0=no registration, 1=registration enabled 2=passwordregistration
		$groupObj->setPassword(ilUtil::stripSlashes($_POST["password"]));
		$groupObj->setExpirationDateTime(ilUtil::stripSlashes($_POST["expirationdate"])." ".
			ilUtil::stripSlashes($_POST["expirationtime"]).":00");

		$this->ilias->account->addDesktopItem($groupObj->getRefId(),"grp");		
		
		// always send a message
		ilUtil::sendInfo($this->lng->txt("grp_added"),true);

		$this->redirectToRefId($_GET["ref_id"]);
	}

	/**
	* update GroupObject
	* @access public
	*/
	function updateObject()
	{
		global $rbacsystem;
		
		if (!$rbacsystem->checkAccess("write",$_GET["ref_id"]) )
		{
			$this->ilErr->raiseError($this->lng->txt("permission_denied"),$this->ilErr->MESSAGE);
		}

		// check required fields
		if (empty($_POST["Fobject"]["title"]))
		{
			$this->ilErr->raiseError($this->lng->txt("fill_out_all_required_fields"),$this->ilErr->MESSAGE);
		}

		if ($_POST["enable_registration"] == 2 && empty($_POST["password"]) || empty($_POST["expirationdate"]) || empty($_POST["expirationtime"]) )//Password-Registration Mode
		{
			$this->ilErr->raiseError($this->lng->txt("grp_err_registration_data"),$this->ilErr->MESSAGE);
		}

		$this->object->setTitle(ilUtil::stripSlashes($_POST["Fobject"]["title"]));
		$this->object->setDescription(ilUtil::stripSlashes($_POST["Fobject"]["desc"]));

		if ($_POST["enable_registration"] == 2 && !ilUtil::isPassword($_POST["password"]))
		{
			$this->ilErr->raiseError($this->lng->txt("passwd_invalid"),$this->ilErr->MESSAGE);
		}

		$this->object->setRegistrationFlag(ilUtil::stripSlashes($_POST["enable_registration"]));
		$this->object->setPassword(ilUtil::stripSlashes($_POST["password"]));
		$this->object->setExpirationDateTime(ilUtil::stripSlashes($_POST["expirationdate"])." ".
			ilUtil::stripSlashes($_POST["expirationtime"]).":00");

		//save custom icons
		if ($this->ilias->getSetting("custom_icons"))
		{
			$this->object->saveIcons($_FILES["cont_big_icon"],
				$_FILES["cont_small_icon"]);
		}

		$this->update = $this->object->update();

		ilUtil::sendInfo($this->lng->txt("msg_obj_modified"),true);
		ilUtil::redirect($this->getReturnLocation("update",$this->ctrl->getLinkTarget($this,"")));
	}

	/**
	* edit Group
	* @access public
	*/
	function editObject()
	{
		global $rbacsystem;

		if (!$rbacsystem->checkAccess("write", $this->ref_id))
		{
			$this->ilErr->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilErr->MESSAGE);
		}

		$data = array();

		if ($_SESSION["error_post_vars"])
		{
			// fill in saved values in case of error
			$data["title"] = ilUtil::prepareFormOutput($_SESSION["error_post_vars"]["Fobject"]["title"],true);
			$data["desc"] = ilUtil::stripSlashes($_SESSION["error_post_vars"]["Fobject"]["desc"]);
			$data["registration"] = $_SESSION["error_post_vars"]["registration"];
			$data["password"] = $_SESSION["error_post_vars"]["password"];
			$data["expirationdate"] = $_SESSION["error_post_vars"]["expirationdate"];//$datetime[0];//$this->grp_object->getExpirationDateTime()[0];
			$data["expirationtime"] = $_SESSION["error_post_vars"]["expirationtime"];//$datetime[1];//$this->grp_object->getExpirationDateTime()[1];

		}
		else
		{
			$data["title"] = ilUtil::prepareFormOutput($this->object->getTitle());
			$data["desc"] = $this->object->getDescription();
			$data["registration"] = $this->object->getRegistrationFlag();
			$data["password"] = $this->object->getPassword();
			$datetime = $this->object->getExpirationDateTime();

			$data["expirationdate"] = $datetime[0];//$this->grp_object->getExpirationDateTime()[0];
			$data["expirationtime"] =  substr($datetime[1],0,5);//$this->grp_object->getExpirationDateTime()[1];

		}

		$this->getTemplateFile("edit");
		$this->__setSubTabs('properties');

		foreach ($data as $key => $val)
		{
			$this->tpl->setVariable("TXT_".strtoupper($key), $this->lng->txt($key));
			$this->tpl->setVariable(strtoupper($key), $val);
			$this->tpl->parseCurrentBlock();
		}

		$checked = array(0=>0,1=>0,2=>0);

		switch ($this->object->getRegistrationFlag())
		{
			case 0:
				$checked[0]=1;
				break;

			case 1:
				$checked[1]=1;
				break;

			case 2:
				$checked[2]=1;
				break;
		}

		$cb_registration[0] = ilUtil::formRadioButton($checked[0], "enable_registration", 0);
		$cb_registration[1] = ilUtil::formRadioButton($checked[1], "enable_registration", 1);
		$cb_registration[2] = ilUtil::formRadioButton($checked[2], "enable_registration", 2);
		
		$this->showCustomIconsEditing(2);
		$this->tpl->setCurrentBlock("adm_content");

		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));//$this->getFormAction("update",$this->ctrl->getFormAction($this)));
		$this->tpl->setVariable("TXT_HEADER", $this->lng->txt("grp_edit"));
		$this->tpl->setVariable("TXT_CANCEL", $this->lng->txt("cancel"));
		$this->tpl->setVariable("TXT_SUBMIT", $this->lng->txt("save"));
		$this->tpl->setVariable("CMD_CANCEL", "canceled");
		$this->tpl->setVariable("CMD_SUBMIT", "update");

		$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));
		$this->tpl->setVariable("TXT_REGISTRATION", $this->lng->txt("group_registration"));
		$this->tpl->setVariable("TXT_REGISTRATION_MODE", $this->lng->txt("group_registration_mode"));
		$this->tpl->setVariable("TXT_REGISTRATION_TIME", $this->lng->txt("group_registration_time"));

		$this->tpl->setVariable("TXT_DISABLEREGISTRATION", $this->lng->txt("group_req_direct"));
		$this->tpl->setVariable("TXT_REGISTRATION_UNLIMITED", $this->lng->txt("grp_registration_unlimited"));
		$this->tpl->setVariable("RB_NOREGISTRATION", $cb_registration[0]);
		$this->tpl->setVariable("TXT_ENABLEREGISTRATION", $this->lng->txt("group_req_registration"));
		$this->tpl->setVariable("RB_REGISTRATION", $cb_registration[1]);
		$this->tpl->setVariable("TXT_PASSWORDREGISTRATION", $this->lng->txt("group_req_password"));
		$this->tpl->setVariable("RB_PASSWORDREGISTRATION", $cb_registration[2]);

		$this->tpl->setVariable("TXT_EXPIRATIONDATE", $this->lng->txt("group_registration_expiration_date"));
		$this->tpl->setVariable("TXT_EXPIRATIONTIME", $this->lng->txt("group_registration_expiration_time"));		
		$this->tpl->setVariable("TXT_DATE", $this->lng->txt("DD.MM.YYYY"));
		$this->tpl->setVariable("TXT_TIME", $this->lng->txt("HH:MM"));

		$this->tpl->setVariable("CB_KEYREGISTRATION", $cb_keyregistration);
		$this->tpl->setVariable("TXT_KEYREGISTRATION", $this->lng->txt("group_keyregistration"));
		$this->tpl->setVariable("TXT_PASSWORD", $this->lng->txt("password"));
	}

	/**
	* displays confirmation form
	* @access public
	*/
	function confirmationObject($user_id="", $confirm, $cancel, $info="", $status="",$a_cmd_return_location = "")
	{
		$this->data["cols"] = array("type", "title", "description", "last_change");

		if (is_array($user_id))
		{
			foreach ($user_id as $id)
			{
				$obj_data =& $this->ilias->obj_factory->getInstanceByObjId($id);

				$this->data["data"]["$id"] = array(
					"type"        => $obj_data->getType(),
					"title"       => $obj_data->getTitle(),
					"desc"        => $obj_data->getDescription(),
					"last_update" => $obj_data->getLastUpdateDate(),

					);
			}
		}
		else
		{
			$obj_data =& $this->ilias->obj_factory->getInstanceByObjId($user_id);

			$this->data["data"]["$id"] = array(
				"type"        => $obj_data->getType(),
				"title"       => $obj_data->getTitle(),
				"desc"        => $obj_data->getDescription(),
				"last_update" => $obj_data->getLastUpdateDate(),
				);
		}

		//write  in sessionvariables
		if(is_array($user_id))
		{
			$_SESSION["saved_post"]["user_id"] = $user_id;
		}
		else
		{
			$_SESSION["saved_post"]["user_id"][0] = $user_id;
		}

		if (isset($status))
		{
			$_SESSION["saved_post"]["status"] = $status;
		}

		$this->data["buttons"] = array( $cancel  => $this->lng->txt("cancel"),
						$confirm  => $this->lng->txt("confirm"));

		$this->getTemplateFile("confirm");

		$this->tpl->setVariable("TPLPATH",$this->tpl->tplPath);

		ilUtil::infoPanel();

		ilUtil::sendInfo($this->lng->txt($info));

		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this)."&cmd_return_location=".$a_cmd_return_location);

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
			$this->tpl->setVariable("IMG_ARROW",ilUtil::getImagePath("spacer.gif"));
			$this->tpl->setVariable("BTN_NAME",$name);
			$this->tpl->setVariable("BTN_VALUE",$value);
			$this->tpl->parseCurrentBlock();
		}
	}

	/**
	* leave Group
	* @access public
	*/
	function leaveGrpObject()
	{
		$member = array($_GET["mem_id"]);
		//set methods that are called after confirmation
		$confirm = "confirmedDeleteMember";
		$cancel  = "canceled";
		$info	 = "info_delete_sure";
		$status  = "";
		$return  = "";
		$this->confirmationObject($member, $confirm, $cancel, $info, $status, $return);
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
	* displays confirmation formular with users that shall be removed from group
	* @access public
	*/
	function removeMemberObject()
	{
		global $rbacreview,$ilUser;
		$user_ids = array();

		if (isset($_POST["user_id"]))
		{
			$user_ids = $_POST["user_id"];
		}
		else if (isset($_GET["mem_id"]))
		{
			$user_ids[] = $_GET["mem_id"];
		}

		if (empty($user_ids[0]))
		{
			$this->ilErr->raiseError($this->lng->txt("no_checkbox"),$this->ilErr->MESSAGE);
		}
		
		if (count($user_ids) == 1 and $this->ilias->account->getId() != $user_ids[0])
		{
			if (!in_array(SYSTEM_ROLE_ID,$rbacreview->assignedRoles($ilUser->getId())) 
				and !in_array($this->ilias->account->getId(),$this->object->getGroupAdminIds()))
			{
				$this->ilErr->raiseError($this->lng->txt("grp_err_no_permission"),$this->ilErr->MESSAGE);
			}
		}
		//bool value: says if $users_ids contains current user id
		$is_dismiss_me = array_search($this->ilias->account->getId(),$user_ids);
		
		$confirm = "confirmedRemoveMember";
		$cancel  = "canceled";
		$info	 = ($is_dismiss_me !== false) ? "grp_dismiss_myself" : "grp_dismiss_member";
		$status  = "";
		$return  = "members";
		$this->confirmationObject($user_ids, $confirm, $cancel, $info, $status, $return);
	}

	/**
	* remove members from group
	* @access public
	*/
	function confirmedRemoveMemberObject()
	{
		$removed_self = false;
		
		$mail = new ilMail($_SESSION["AccountId"]);

		//User needs to have administrative rights to remove members...
		foreach($_SESSION["saved_post"]["user_id"] as $member_id)
		{
			$err_msg = $this->object->removeMember($member_id);

			if (strlen($err_msg) > 0)
			{
				ilUtil::sendInfo($this->lng->txt($err_msg),true);
				ilUtil::redirect($this->ctrl->getLinkTarget($this,"members"));
			}
			
			$user_obj = new ilObjUser($member_id);
			
			$user_obj->dropDesktopItem($this->object->getRefId(), "grp");
			
			if (!$removed_self and $user_obj->getId() == $this->ilias->account->getId())
			{
				$removed_self = true;
			}
			/*
			else
			{
				// SEND A SYSTEM MESSAGE EACH TIME A MEMBER HAS BEEN REMOVED FROM A GROUP
				$mail->sendMail($user_obj->getLogin(),"","",$this->lng->txtlng("common","grp_mail_subj_subscription_cancelled",$user_obj->getLanguage()).": ".$this->object->getTitle(),$this->lng->txtlng("common","grp_mail_body_subscription_cancelled",$user_obj->getLanguage()),array(),array('system'));
			}*/			

		}

		unset($_SESSION["saved_post"]);

		ilUtil::sendInfo($this->lng->txt("grp_msg_membership_annulled"),true);
		
		if ($removed_self)
		{
			ilUtil::redirect("repository.php?ref_id=".$this->tree->getParentId($this->ref_id));
		}

		ilUtil::redirect($this->ctrl->getLinkTarget($this,"members"));
	}


	/**
	* displays form in which the member-status can be changed
	* @access public
	*/
	function changeMemberObject()
	{
		global $rbacreview,$ilUser;
		
		if ($_GET["sort_by"] == "title" or $_GET["sort_by"] == "")
		{
			$_GET["sort_by"] = "login";
		}

		$member_ids = array();

		if (isset($_POST["user_id"]))
		{
			$member_ids = $_POST["user_id"];
		}
		else if (isset($_GET["mem_id"]))
		{
			$member_ids[0] = $_GET["mem_id"];
		}

		if (empty($member_ids[0]))
		{
			$this->ilErr->raiseError($this->lng->txt("no_checkbox"),$this->ilErr->MESSAGE);
		}

		if (!in_array(SYSTEM_ROLE_ID,$rbacreview->assignedRoles($ilUser->getId())) 
			and !in_array($this->ilias->account->getId(),$this->object->getGroupAdminIds()))
		{
			$this->ilErr->raiseError($this->lng->txt("grp_err_no_permission"),$this->ilErr->MESSAGE);
		}

		$stati = array_flip($this->object->getLocalGroupRoles(true));
		//var_dump($stati);exit;

		//build data structure
		foreach ($member_ids as $member_id)
		{
			$member =& $this->ilias->obj_factory->getInstanceByObjId($member_id);
			$mem_status = $this->object->getMemberRoles($member_id);

			$this->data["data"][$member->getId()]= array(
					"login"		=> $member->getLogin(),
					"firstname"	=> $member->getFirstname(),
					"lastname"	=> $member->getLastname(),
					"last_visit"=> ilFormat::formatDate($member->getLastLogin()),
					"grp_role"	=> ilUtil::formSelect($mem_status,"member_status_select[".$member->getId()."][]",$stati,true,true,3)
				);
		}
		
		unset($member);
		
		ilUtil::infoPanel();

		$this->tpl->addBlockfile("ADM_CONTENT", "member_table", "tpl.table.html");

		// load template for table content data
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));

		$this->data["buttons"] = array( "updateMemberStatus"  => $this->lng->txt("confirm"),
										"members"  => $this->lng->txt("back"));

		$this->tpl->setCurrentBlock("tbl_action_row");
		$this->tpl->setVariable("COLUMN_COUNTS",5);
		//$this->tpl->setVariable("TPLPATH",$this->tpl->tplPath);
		$this->tpl->setVariable("IMG_ARROW", ilUtil::getImagePath("arrow_downright.gif"));

		foreach ($this->data["buttons"] as $name => $value)
		{
			$this->tpl->setCurrentBlock("tbl_action_btn");
			$this->tpl->setVariable("BTN_NAME",$name);
			$this->tpl->setVariable("BTN_VALUE",$value);
			$this->tpl->parseCurrentBlock();
		}

		//sort data array
		$this->data["data"] = ilUtil::sortArray($this->data["data"], $_GET["sort_by"], $_GET["sort_order"]);
		$output = array_slice($this->data["data"],$_GET["offset"],$_GET["limit"]);
		
		// create table
		include_once "./Services/Table/classes/class.ilTableGUI.php";

		$tbl = new ilTableGUI($output);

		// title & header columns
		$tbl->setTitle($this->lng->txt("grp_mem_change_status"),"icon_usr_b.gif",$this->lng->txt("grp_mem_change_status"));
		//$tbl->setHelp("tbl_help.php","icon_help.gif",$this->lng->txt("help"));
		$tbl->setHeaderNames(array($this->lng->txt("username"),$this->lng->txt("firstname"),$this->lng->txt("lastname"),$this->lng->txt("last_visit"),$this->lng->txt("role")));
		$tbl->setHeaderVars(array("login","firstname","lastname","last_visit","role"),$this->ctrl->getParameterArray($this,"",false));

		$tbl->setColumnWidth(array("20%","20%","20%","40%"));

		$this->tpl->setCurrentBlock("tbl_action_row");
		$this->tpl->parseCurrentBlock();

		// control
		$tbl->setOrderColumn($_GET["sort_by"]);
		$tbl->setOrderDirection($_GET["sort_order"]);
		$tbl->setLimit($_GET["limit"]);
		$tbl->setOffset($_GET["offset"]);
		$tbl->setMaxCount(count($this->data["data"]));

		$tbl->setFooter("tblfooter",$this->lng->txt("previous"),$this->lng->txt("next"));

		// render table
		$tbl->render();
	}
	
	/**
	* display group members
	*/
	function membersObject()
	{
		global $rbacsystem,$ilBench,$ilDB,$ilUser;

		$this->tpl->addBlockFile("ADM_CONTENT","adm_content","tpl.grp_members.html");
		$this->__setSubTabs('members');

		// display member search button
		$this->lng->loadLanguageModule('crs');
		$is_admin = (bool) $rbacsystem->checkAccess("write", $this->object->getRefId());
		if($is_admin)
		{
			$this->tpl->addBlockfile("BUTTONS", "buttons", "tpl.buttons.html");
			$this->tpl->setCurrentBlock("btn_cell");
			$this->tpl->setVariable("BTN_LINK",$this->ctrl->getLinkTargetByClass('ilRepositorySearchGUI','start'));
			$this->tpl->setVariable("BTN_TXT",$this->lng->txt("crs_add_member"));
			$this->tpl->parseCurrentBlock();
		}

		$ilBench->start("GroupGUI", "membersObject");
		
		//if current user is admin he is able to add new members to group
		$val_contact = "<img src=\"".ilUtil::getImagePath("icon_pencil_b.gif")."\" alt=\"".$this->lng->txt("grp_mem_send_mail")."\" title=\"".$this->lng->txt("grp_mem_send_mail")."\" border=\"0\" vspace=\"0\"/>";
		$val_change = "<img src=\"".ilUtil::getImagePath("icon_change_b.gif")."\" alt=\"".$this->lng->txt("grp_mem_change_status")."\" title=\"".$this->lng->txt("grp_mem_change_status")."\" border=\"0\" vspace=\"0\"/>";
		$val_leave = "<img src=\"".ilUtil::getImagePath("icon_group_out_b.gif")."\" alt=\"".$this->lng->txt("grp_mem_leave")."\" title=\"".$this->lng->txt("grp_mem_leave")."\" border=\"0\" vspace=\"0\"/>";

		// store access checks to improve performance
		$access_leave = $rbacsystem->checkAccess("leave",$this->object->getRefId());
		$access_write = $rbacsystem->checkAccess("write",$this->object->getRefId());

		$member_ids = $this->object->getGroupMemberIds();
		
		// fetch all users data in one shot to improve performance
		$members = $this->object->getGroupMemberData($member_ids);
		
		$account_id = $this->ilias->account->getId();
		$counter = 0;

		foreach ($members as $mem)
		{
			$link_contact = "ilias.php?baseClass=ilMailGUI&type=new&rcp_to=".$mem["login"];
			$link_change = $this->ctrl->getLinkTarget($this,"changeMember")."&mem_id=".$mem["id"];
		
			//build function
			if ($access_write)
			{
				$member_functions = "<a href=\"$link_change\">$val_change</a>";
			}

			if (($mem["id"] == $account_id && $access_leave) || $access_write)
			{
				$link_leave = $this->ctrl->getLinkTarget($this,"RemoveMember")."&mem_id=".$mem["id"];
				$member_functions .="<a href=\"$link_leave\">$val_leave</a>";
			}

			// this is twice as fast than the code above
			$str_member_roles = $this->object->getMemberRolesTitle($mem["id"]);

			if ($access_write)
			{
				$result_set[$counter][] = ilUtil::formCheckBox(0,"user_id[]",$mem["id"]);
			}
			
			$user_ids[$counter] = $mem["id"];
            
            //discarding the checkboxes
			$result_set[$counter][] = $mem["login"];
			$result_set[$counter][] = $mem["firstname"];
			$result_set[$counter][] = $mem["lastname"];
			$result_set[$counter][] = ilFormat::formatDate($mem["last_login"]);
			$result_set[$counter][] = $str_member_roles;
			$result_set[$counter][] = "<a href=\"$link_contact\">".$val_contact."</a>".$member_functions;

			++$counter;

			unset($member_functions);
		}

		$ilBench->stop("GroupGUI", "membersObject");

		return $this->__showMembersTable($result_set,$user_ids);
    }

		
	/**
	 * Builds a group members gallery as a layer of left-floating images
	 * @author Arturo Gonzalez <arturogf@gmail.com>
	 * @access       public
	 */
	function membersGalleryObject()
	{
		global $rbacsystem;
		
		$is_admin = (bool) $rbacsystem->checkAccess("write", $this->object->getRefId());
		
		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.crs_members_gallery.html','Modules/Course');
		
		$this->__setSubTabs('members');
		
		$member_ids = $this->object->getGroupMemberIds();
		$admin_ids = $this->object->getGroupAdminIds();
		
		// fetch all users data in one shot to improve performance
		$members = $this->object->getGroupMemberData($member_ids);
		
		// MEMBERS
		if(count($members))
		{
			foreach($members as $member)
			{
				// get user object
				if(!($usr_obj = ilObjectFactory::getInstanceByObjId($member["id"],false)))
				{
					continue;
				}
				
				$public_profile = $usr_obj->getPref("public_profile");

				// SET LINK TARGET FOR USER PROFILE
				$this->ctrl->setParameterByClass("ilobjusergui", "user", $member["id"]);
				$profile_target = $this->ctrl->getLinkTargetByClass("ilobjusergui", "getPublicProfile");
			
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
	
	
	/**
	* Form for mail to group members
	*/
	function mailMembersObject()
	{
		global $rbacreview;

		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.mail_members.html',"Services/Mail");

		$this->__setSubTabs('members');

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
			$this->tpl->setVariable("MAILBOX",$role_addr);
			$this->tpl->parseCurrentBlock();
		}
	}


	/**
	* Members map
	*/
	function membersMapObject()
	{
		global $tpl;
		
		$this->__setSubTabs('members');
		
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

	function showNewRegistrationsObject()
	{
		global $rbacsystem;

		//get new applicants
		$applications = $this->object->getNewRegistrations();
		
		if (!$applications)
		{
			$this->ilErr->raiseError($this->lng->txt("no_applications"),$this->ilErr->MESSAGE);
		}
		
		if ($_GET["sort_by"] == "title" or $_GET["sort_by"] == "")
		{
			$_GET["sort_by"] = "login";
		}

		$val_contact = "<img src=\"".ilUtil::getImagePath("icon_pencil_b.gif")."\" alt=\"".$this->lng->txt("grp_app_send_mail")."\" title=\"".$this->lng->txt("grp_app_send_mail")."\" border=\"0\" vspace=\"0\"/>";

		foreach ($applications as $applicant)
		{
			$user =& $this->ilias->obj_factory->getInstanceByObjId($applicant->user_id);

			$link_contact = "ilias.php?baseClass=ilMailGUI&mobj_id=3&type=new&rcp_to=".$user->getLogin();
			$link_change = $this->ctrl->getLinkTarget($this,"changeMember")."&mem_id=".$user->getId();
			$member_functions = "<a href=\"$link_change\">$val_change</a>";
			if (strcmp($_GET["check"], "all") == 0)
			{
				$checked = 1;
			}
			else
			{
				$checked = 0;
			}
			$this->data["data"][$user->getId()]= array(
				"check"		=> ilUtil::formCheckBox($checked,"user_id[]",$user->getId()),
				"username"	=> $user->getLogin(),
				"fullname"	=> $user->getFullname(),
				"subject"	=> $applicant->subject,
				"date" 		=> $applicant->application_date,
				"functions"	=> "<a href=\"$link_contact\">".$val_contact."</a>"
				);

				unset($member_functions);
				unset($user);
		}
		// load template for table content data
		//echo $this->ctrl->getFormAction($this,"post");
		//var_dump($this->ctrl->getParameterArray($this,"ShownewRegistrations",false));
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this,"post"));

		$this->data["buttons"] = array( "refuseApplicants"  => $this->lng->txt("refuse"),
										"assignApplicants"  => $this->lng->txt("assign"));

		$this->tpl->addBlockfile("ADM_CONTENT", "member_table", "tpl.table.html");

		//prepare buttons [cancel|assign]
		foreach ($this->data["buttons"] as $name => $value)
		{
			$this->tpl->setCurrentBlock("tbl_action_btn");
			$this->tpl->setVariable("BTN_NAME",$name);
			$this->tpl->setVariable("BTN_VALUE",$value);
			$this->tpl->parseCurrentBlock();
		}
		
		$this->tpl->setCurrentBlock("tbl_action_plain_select");
		$this->tpl->setVariable("SELECT_ACTION", "<a href=\"" . $this->ctrl->getLinkTarget($this,"ShownewRegistrations") . "&check=all\">" . $this->lng->txt("check_all") . "</a>" . " / " . "<a href=\"" . $this->ctrl->getLinkTarget($this,"ShownewRegistrations") . "&check=none\">" . $this->lng->txt("uncheck_all") . "</a>");
		$this->tpl->parseCurrentBlock();

		if (isset($this->data["data"]))
		{
			//sort data array
			$this->data["data"] = ilUtil::sortArray($this->data["data"], $_GET["sort_by"], $_GET["sort_order"]);
			$output = array_slice($this->data["data"],$_GET["offset"],$_GET["limit"]);
		}

		$this->tpl->setCurrentBlock("tbl_action_row");
		$this->tpl->setVariable("IMG_ARROW", ilUtil::getImagePath("arrow_downright.gif"));
		$this->tpl->setVariable("COLUMN_COUNTS",6);
		$this->tpl->setVariable("TPLPATH",$this->tpl->tplPath);

		// create table
		include_once "./Services/Table/classes/class.ilTableGUI.php";
		$tbl = new ilTableGUI($output);
		// title & header columns
		$tbl->setTitle($this->lng->txt("group_new_registrations"),"icon_usr_b.gif",$this->lng->txt("group_applicants"));
		//$tbl->setHelp("tbl_help.php","icon_help.gif",$this->lng->txt("help"));
		$tbl->setHeaderNames(array("",$this->lng->txt("username"),$this->lng->txt("fullname"),$this->lng->txt("subject"),$this->lng->txt("application_date"),$this->lng->txt("grp_options")));
		$tbl->setHeaderVars(array("","username","fullname","subject","date","functions"),$this->ctrl->getParameterArray($this,"ShownewRegistrations",false));
		$tbl->setColumnWidth(array("","20%","20%","35%","20%","5%"));
		
		if ($_GET["sort_by"] == "login")
		{
			$_GET["sort_by"] = "username";
		}
		
		if (!$_GET["sort_order"])
		{
			$_GET["sort_order"] = "asc";
		}
		
		// control
		$tbl->setOrderColumn($_GET["sort_by"]);
		$tbl->setOrderDirection($_GET["sort_order"]);
		$tbl->setLimit($_GET["limit"]);
		$tbl->setOffset($_GET["offset"]);
		$tbl->setMaxCount(count($this->data["data"]));
		$tbl->setFooter("tblfooter",$this->lng->txt("previous"),$this->lng->txt("next"));
		$tbl->render();
	}

	/**
	* adds applicant to group as member
	* @access	public
	*/
	function assignApplicantsObject()
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

			if (!$this->object->addMember($new_member, $this->object->getDefaultMemberRole()))
			{
				$this->ilErr->raiseError("An Error occured while assigning user to group !",$this->ilErr->MESSAGE);
			}

			$this->object->deleteApplicationListEntry($new_member);
			$mail->sendMail($user->getLogin(),"","","New Membership in Group: ".$this->object->getTitle(),"You have been assigned to the group as a member. You can now access all group specific objects like forums, learningmodules,etc..",array(),array('system'));
		}

		ilUtil::sendInfo($this->lng->txt("grp_msg_applicants_assigned"),true);
		ilUtil::redirect($this->ctrl->getLinkTarget($this,"members"));
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

	/**
	* displays form in which the member-status can be changed
	* @access public
	*/
	function updateMemberStatusObject()
	{
		global $rbacsystem;

		if (!$rbacsystem->checkAccess("write",$this->object->getRefId()) )
		{
			$this->ilErr->raiseError("permission_denied",$this->ilErr->MESSAGE);
		}

		if (isset($_POST["member_status_select"]))
		{
			foreach ($_POST["member_status_select"] as $key=>$value)
			{
				$this->object->setMemberStatus($key,$value);
			}
		}

		ilUtil::sendInfo($this->lng->txt("msg_obj_modified"),true);
		ilUtil::redirect($this->ctrl->getLinkTarget($this,"members"));
	}

	function searchUserFormObject()
	{
		global $rbacsystem;

		$this->lng->loadLanguageModule('search');

		// MINIMUM ACCESS LEVEL = 'administrate'
		if(!$rbacsystem->checkAccess("write", $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilias->error_obj->MESSAGE);
		}

		$this->tpl->addBlockFile("ADM_CONTENT","adm_content","tpl.grp_members_search.html");
		
		$this->tpl->setVariable("F_ACTION",$this->ctrl->getFormAction($this));
		$this->tpl->setVariable("SEARCH_ASSIGN_USR",$this->lng->txt("grp_search_members"));
		$this->tpl->setVariable("SEARCH_SEARCH_TERM",$this->lng->txt("search_search_term"));
		$this->tpl->setVariable("SEARCH_VALUE",$_SESSION["grp_search_str"] ? $_SESSION["grp_search_str"] : "");
		$this->tpl->setVariable("SEARCH_FOR",$this->lng->txt("exc_search_for"));
		$this->tpl->setVariable("SEARCH_ROW_TXT_USER",$this->lng->txt("exc_users"));
		$this->tpl->setVariable("SEARCH_ROW_TXT_ROLE",$this->lng->txt("exc_roles"));
		$this->tpl->setVariable("SEARCH_ROW_TXT_GROUP",$this->lng->txt("exc_groups"));
		$this->tpl->setVariable("BTN2_VALUE",$this->lng->txt("cancel"));
		$this->tpl->setVariable("BTN1_VALUE",$this->lng->txt("search"));
		
        $usr = ($_POST["search_for"] == "usr" || $_POST["search_for"] == "") ? 1 : 0;
		$grp = ($_POST["search_for"] == "grp") ? 1 : 0;
		$role = ($_POST["search_for"] == "role") ? 1 : 0;

		$this->tpl->setVariable("SEARCH_ROW_CHECK_USER",ilUtil::formRadioButton($usr,"search_for","usr"));
		$this->tpl->setVariable("SEARCH_ROW_CHECK_ROLE",ilUtil::formRadioButton($role,"search_for","role"));
        $this->tpl->setVariable("SEARCH_ROW_CHECK_GROUP",ilUtil::formRadioButton($grp,"search_for","grp"));

		$this->__unsetSessionVariables();
	}

	function __appendToStoredResults($a_result)
	{
		$tmp_array = array();
		foreach($a_result as $result)
		{
			if(is_array($result))
			{
				$tmp_array[] = $result['id'];
			}
			elseif($result)
			{
				$tmp_array[] = $result;
			}
		}
		// merge results
		
		$_SESSION['grp_usr_search_result'] = array_unique(array_merge((array) $_SESSION['grp_usr_search_result'],$tmp_array));
		return $_SESSION['grp_usr_search_result'];
	}

	function cancelSearchObject()
	{
		$_SESSION['grp_usr_search_result'] = array();
		$_SESSION['grp_search_str'] = '';
		$this->searchUserFormObject();
	}

	function searchObject()
	{
		global $rbacsystem,$tree;

		$_SESSION["grp_search_str"] = $_POST["search_str"] = $_POST["search_str"] ? $_POST["search_str"] : $_SESSION["grp_search_str"];
		$_SESSION["grp_search_for"] = $_POST["search_for"] = $_POST["search_for"] ? $_POST["search_for"] : $_SESSION["grp_search_for"];
		
		// MINIMUM ACCESS LEVEL = 'administrate'
		if(!$rbacsystem->checkAccess("write", $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilias->error_obj->MESSAGE);
		}

		if(!isset($_POST["search_for"]) or !isset($_POST["search_str"]))
		{
			ilUtil::sendInfo($this->lng->txt("grp_search_enter_search_string"));
			$this->searchUserFormObject();
			
			return false;
		}

		if(!count($result = $this->__search(ilUtil::stripSlashes($_POST["search_str"]),$_POST["search_for"])))
		{
			ilUtil::sendInfo($this->lng->txt("grp_no_results_found"));
			$this->searchUserFormObject();

			return false;
		}
		
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.grp_usr_selection.html");
		#$this->__showButton("cancelSearch",$this->lng->txt("grp_new_search"));
		
		$counter = 0;
		$f_result = array();

		switch($_POST["search_for"])
		{
        	case "usr":
				foreach($result as $user)
				{
					if(!$tmp_obj = ilObjectFactory::getInstanceByObjId($user,false))
					{
						continue;
					}
					$user_ids[$counter] = $user;
					
					$f_result[$counter][] = ilUtil::formCheckbox(0,"user[]",$user);
					$f_result[$counter][] = $tmp_obj->getLogin();
					$f_result[$counter][] = $tmp_obj->getFirstname();
					$f_result[$counter][] = $tmp_obj->getLastname();
					$f_result[$counter][] = ilFormat::formatDate($tmp_obj->getLastLogin());

					unset($tmp_obj);
					++$counter;
				}
				$this->__showSearchUserTable($f_result,$user_ids);

				return true;

			case "role":
				foreach($result as $role)
				{
                    // exclude anonymous role
                    if ($role["id"] == ANONYMOUS_ROLE_ID)
                    {
                        continue;
                    }

                    if(!$tmp_obj = ilObjectFactory::getInstanceByObjId($role["id"],false))
					{
						continue;
					}
					
				    // exclude roles with no users assigned to
                    if ($tmp_obj->getCountMembers() == 0)
                    {
                        continue;
                    }
                    
                    $role_ids[$counter] = $role["id"];
                    
					$f_result[$counter][] = ilUtil::formCheckbox(0,"role[]",$role["id"]);
					$f_result[$counter][] = array($tmp_obj->getTitle(),$tmp_obj->getDescription());
					$f_result[$counter][] = $tmp_obj->getCountMembers();
					
					unset($tmp_obj);
					++$counter;
				}
				
				$this->__showSearchRoleTable($f_result,$role_ids);

				return true;
				
			case "grp":
				foreach($result as $group)
				{
					if(!$tree->isInTree($group["id"]))
					{
						continue;
					}
					
					if(!$tmp_obj = ilObjectFactory::getInstanceByRefId($group["id"],false))
					{
						continue;
					}
					
                    // exclude myself :-)
                    if ($tmp_obj->getId() == $this->object->getId())
                    {
                        continue;
                    }
                    
                    $grp_ids[$counter] = $group["id"];
                    
					$f_result[$counter][] = ilUtil::formCheckbox(0,"group[]",$group["id"]);
					$f_result[$counter][] = array($tmp_obj->getTitle(),$tmp_obj->getDescription());
					$f_result[$counter][] = $tmp_obj->getCountMembers();
					
					unset($tmp_obj);
					++$counter;
				}
				
				if(!count($f_result))
				{
					ilUtil::sendInfo($this->lng->txt("grp_no_results_found"));
					$this->searchUserFormObject();

					return false;
				}
				
				$this->__showSearchGroupTable($f_result,$grp_ids);

				return true;
		}
	}

	function searchCancelledObject ()
	{
		ilUtil::sendInfo($this->lng->txt("action_aborted"),true);
		ilUtil::redirect($this->ctrl->getLinkTarget($this,"members"));
	}

	// get tabs
	function getTabs(&$tabs_gui)
	{
		global $rbacsystem,$ilUser;

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


		if ($rbacsystem->checkAccess('write',$this->ref_id))
		{
			$force_active = ($_GET["cmd"] == "edit" && $_GET["cmdClass"] == "")
				? true
				: false;
			$tabs_gui->addTarget("edit_properties",
				$this->ctrl->getLinkTarget($this, "edit"), array("edit", "editMapSettings"), get_class($this),
				"", $force_active);
//  Export tab to export group members to an excel file. Only available for group admins
//  commented out for following reason: clearance needed with developer list
//			$tabs_gui->addTarget("export",
//				$this->ctrl->getLinkTarget($this, "export"), "export", get_class($this));
		}

		if ($rbacsystem->checkAccess('read',$this->ref_id))
		{
			$mem_cmd = ($rbacsystem->checkAccess('write',$this->ref_id))
				? "members"
				: "membersGallery";

			$tabs_gui->addTarget("group_members",
				$this->ctrl->getLinkTarget($this, $mem_cmd), array("members","mailMembers","membersMap","membersGallery","showProfile"), get_class($this));
		}
		
		$applications = $this->object->getNewRegistrations();

		if (is_array($applications) and $this->object->isAdmin($this->ilias->account->getId()))
		{
			$tabs_gui->addTarget("group_new_registrations",
				$this->ctrl->getLinkTarget($this, "ShownewRegistrations"), "ShownewRegistrations", get_class($this));
		}

		// learning progress
		include_once("Services/Tracking/classes/class.ilObjUserTracking.php");
		if($rbacsystem->checkAccess('read',$this->ref_id) and ilObjUserTracking::_enabledLearningProgress())
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
		
		if ($rbacsystem->checkAccess('join',$this->object->getRefId())
		   and !ilObjGroup::_isMember($ilUser->getId(),$this->object->getRefId()))
		{
			$tabs_gui->addTarget("join",
								 $this->ctrl->getLinkTarget($this, "join"), 
								 'join',
								 "");
		}

	// parent tabs (all container: edit_permission, clipboard, trash
		parent::getTabs($tabs_gui);
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
		
		include_once 'classes/class.ilObjGroup.php';

		if($ref_id = ilObjGroup::_importFromFile($_FILES['xmldoc'],(int) $_GET['ref_id']))
		{
			$this->ctrl->setParameter($this, "ref_id", $ref_id);
			ilUtil::sendInfo($this->lng->txt("import_grp_finished"),true);
			ilUtil::redirect($this->ctrl->getLinkTarget($this,'edit'));
		}
		
		ilUtil::sendInfo($this->lng->txt("import_file_not_valid"));
		$this->createObject();
	}	

	function __unsetSessionVariables()
	{
		unset($_SESSION["grp_delete_member_ids"]);
		unset($_SESSION["grp_delete_subscriber_ids"]);
		unset($_SESSION["grp_search_str"]);
		unset($_SESSION["grp_search_for"]);
		unset($_SESSION["grp_role"]);
		unset($_SESSION["grp_group"]);
		unset($_SESSION["grp_archives"]);
	}
	
	function __search($a_search_string,$a_search_for)
	{
		include_once("class.ilSearch.php");

		$this->lng->loadLanguageModule("content");
		$search =& new ilSearch($_SESSION["AccountId"]);
		$search->setPerformUpdate(false);
		$search->setMinWordLength(1);
		$search->setSearchString(ilUtil::stripSlashes($a_search_string));
		$search->setCombination("and");
		$search->setSearchFor(array(0 => $a_search_for));
		$search->setSearchType('new');

		if($search->validate($message))
		{
			$search->performSearch();
		}
		else
		{
			ilUtil::sendInfo($message,true);
			$this->ctrl->redirect($this,"searchUserForm");
		}

		if($a_search_for == 'usr')
		{
			$this->__appendToStoredResults($search->getResultByType($a_search_for));
			return $_SESSION['grp_usr_search_result'];
		}

		return $search->getResultByType($a_search_for);
	}

	function __showSearchUserTable($a_result_set,$a_user_ids = NULL, $a_cmd = "search")
	{
		$this->__showButton('searchUserForm',$this->lng->txt("back"));
	
    	if ($a_cmd == "listUsersRole" or $a_cmd == "listUsersGroup")
    	{
            $return_to = "search";
        }

		$tbl =& $this->__initTableGUI();
		$tpl =& $tbl->getTemplateObject();

		// SET FORMACTION
		$tpl->setCurrentBlock("tbl_form_header");
		$tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$tpl->parseCurrentBlock();


		$tpl->setCurrentBlock("tbl_action_btn");
		$tpl->setVariable("BTN_NAME","addUser");
		$tpl->setVariable("BTN_VALUE",$this->lng->txt("add"));
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("plain_button");
		$tpl->setVariable("PBTN_NAME",'searchUserForm');
		$tpl->setVariable("PBTN_VALUE",$this->lng->txt('append_search'));
		$tpl->parseCurrentBlock();
		
		$tpl->setCurrentBlock("plain_button");
		$tpl->setVariable("PBTN_NAME",'cancelSearch');
		$tpl->setVariable("PBTN_VALUE",$this->lng->txt("grp_new_search"));
		$tpl->parseCurrentBlock();


		if (!empty($a_user_ids))
		{
			// set checkbox toggles
			$tpl->setCurrentBlock("tbl_action_toggle_checkboxes");
			$tpl->setVariable("JS_VARNAME","user");			
			$tpl->setVariable("JS_ONCLICK",ilUtil::array_php2js($a_user_ids));
			$tpl->setVariable("TXT_CHECKALL", $this->lng->txt("check_all"));
			$tpl->setVariable("TXT_UNCHECKALL", $this->lng->txt("uncheck_all"));
			$tpl->parseCurrentBlock();
		}

		$tpl->setCurrentBlock("tbl_action_row");
		$tpl->setVariable("COLUMN_COUNTS",5);
		$tpl->setVariable("IMG_ARROW",ilUtil::getImagePath("arrow_downright.gif"));
		$tpl->parseCurrentBlock();

		$tbl->setTitle($this->lng->txt("grp_header_edit_members"),"icon_usr_b.gif",$this->lng->txt("grp_header_edit_members"));
		$tbl->setHeaderNames(array("",
								   $this->lng->txt("username"),
								   $this->lng->txt("firstname"),
								   $this->lng->txt("lastname"),
								   $this->lng->txt("last_visit")));
		$tbl->setHeaderVars(array("",
								  "login",
								  "firstname",
								  "lastname",
								  "last_visit"),
							array("ref_id" => $this->object->getRefId(),
								  "cmd" => $a_cmd,
								  "cmdClass" => "ilobjgroupgui",
								  "cmdNode" => $_GET["cmdNode"]));

		$tbl->setColumnWidth(array("","33%","33%","33%"));

		$this->__setTableGUIBasicData($tbl,$a_result_set);
		$tbl->render();
		
		$this->tpl->setVariable("SEARCH_RESULT_TABLE",$tbl->tpl->get());

		return true;
	}

	function __showSearchRoleTable($a_result_set,$a_role_ids = NULL)
	{
		$this->__showButton('searchUserForm',$this->lng->txt("back"));

		$tbl =& $this->__initTableGUI();
		$tpl =& $tbl->getTemplateObject();

		$tpl->setCurrentBlock("tbl_form_header");
		$tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("tbl_action_btn");
		$tpl->setVariable("BTN_NAME","searchUserForm");
		$tpl->setVariable("BTN_VALUE",$this->lng->txt("back"));
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("tbl_action_btn");
		$tpl->setVariable("BTN_NAME","listUsersRole");
		$tpl->setVariable("BTN_VALUE",$this->lng->txt("grp_list_users"));
		$tpl->parseCurrentBlock();
		
		if (!empty($a_role_ids))
		{
			// set checkbox toggles
			$tpl->setCurrentBlock("tbl_action_toggle_checkboxes");
			$tpl->setVariable("JS_VARNAME","role");			
			$tpl->setVariable("JS_ONCLICK",ilUtil::array_php2js($a_role_ids));
			$tpl->setVariable("TXT_CHECKALL", $this->lng->txt("check_all"));
			$tpl->setVariable("TXT_UNCHECKALL", $this->lng->txt("uncheck_all"));
			$tpl->parseCurrentBlock();
		}

		$tpl->setCurrentBlock("tbl_action_row");
		$tpl->setVariable("COLUMN_COUNTS",5);
		$tpl->setVariable("IMG_ARROW",ilUtil::getImagePath("arrow_downright.gif"));
		$tpl->parseCurrentBlock();

		$tbl->setTitle($this->lng->txt("grp_header_edit_members"),"icon_usr_b.gif",$this->lng->txt("grp_header_edit_members"));
		$tbl->setHeaderNames(array("",
								   $this->lng->txt("obj_role"),
								   $this->lng->txt("grp_count_members")));
		$tbl->setHeaderVars(array("",
								  "title",
								  "nr_members"),
							array("ref_id" => $this->object->getRefId(),
								  "cmd" => "search",
								  "cmdClass" => "ilobjgroupgui",
								  "cmdNode" => $_GET["cmdNode"]));

		$tbl->setColumnWidth(array("","80%","19%"));


		$this->__setTableGUIBasicData($tbl,$a_result_set,"role");
		$tbl->render();
		
		$this->tpl->setVariable("SEARCH_RESULT_TABLE",$tbl->tpl->get());

		return true;
	}

	function __showSearchGroupTable($a_result_set,$a_grp_ids = NULL)
	{
		$this->__showButton('searchUserForm',$this->lng->txt("back"));

    	$tbl =& $this->__initTableGUI();
		$tpl =& $tbl->getTemplateObject();

		$tpl->setCurrentBlock("tbl_form_header");
		$tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("tbl_action_btn");
		$tpl->setVariable("BTN_NAME","searchUserForm");
		$tpl->setVariable("BTN_VALUE",$this->lng->txt("back"));
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("tbl_action_btn");
		$tpl->setVariable("BTN_NAME","listUsersGroup");
		$tpl->setVariable("BTN_VALUE",$this->lng->txt("grp_list_users"));
		$tpl->parseCurrentBlock();
		
		if (!empty($a_grp_ids))
		{
			// set checkbox toggles
			$tpl->setCurrentBlock("tbl_action_toggle_checkboxes");
			$tpl->setVariable("JS_VARNAME","group");			
			$tpl->setVariable("JS_ONCLICK",ilUtil::array_php2js($a_grp_ids));
			$tpl->setVariable("TXT_CHECKALL", $this->lng->txt("check_all"));
			$tpl->setVariable("TXT_UNCHECKALL", $this->lng->txt("uncheck_all"));
			$tpl->parseCurrentBlock();
		}

		$tpl->setCurrentBlock("tbl_action_row");
		$tpl->setVariable("COLUMN_COUNTS",5);
		$tpl->setVariable("IMG_ARROW",ilUtil::getImagePath("arrow_downright.gif"));
		$tpl->parseCurrentBlock();

		$tbl->setTitle($this->lng->txt("grp_header_edit_members"),"icon_usr_b.gif",$this->lng->txt("grp_header_edit_members"));
		$tbl->setHeaderNames(array("",
								   $this->lng->txt("obj_grp"),
								   $this->lng->txt("grp_count_members")));
		$tbl->setHeaderVars(array("",
								  "title",
								  "nr_members"),
							array("ref_id" => $this->object->getRefId(),
								  "cmd" => "search",
								  "cmdClass" => "ilobjgroupgui",
								  "cmdNode" => $_GET["cmdNode"]));

		$tbl->setColumnWidth(array("","80%","19%"));


		$this->__setTableGUIBasicData($tbl,$a_result_set,"group");
		$tbl->render();
		
		$this->tpl->setVariable("SEARCH_RESULT_TABLE",$tbl->tpl->get());

		return true;
	}
	
	function __showMembersTable($a_result_set,$a_user_ids = NULL)
	{
        global $rbacsystem,$ilBench;
        
		$ilBench->start("GroupGUI", "__showMembersTable");

		$actions = array("RemoveMember"  => $this->lng->txt("remove"),"changeMember"  => $this->lng->txt("change"));

        $tbl =& $this->__initTableGUI();
		$tpl =& $tbl->getTemplateObject();

		$tpl->setCurrentBlock("tbl_form_header");
		$tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("tbl_action_row");
		
		//INTERIMS:quite a circumstantial way to show the list on rolebased accessrights
		if ($rbacsystem->checkAccess("write",$this->object->getRefId()))
		{			//user is administrator
            #$tpl->setCurrentBlock("plain_button");
		    #$tpl->setVariable("PBTN_NAME","searchUserForm");
		    #$tpl->setVariable("PBTN_VALUE",$this->lng->txt("grp_add_member"));
		    #$tpl->parseCurrentBlock();
		    #$tpl->setCurrentBlock("plain_buttons");
		    #$tpl->parseCurrentBlock();
		
			$tpl->setVariable("COLUMN_COUNTS",7);
			$tpl->setVariable("IMG_ARROW", ilUtil::getImagePath("arrow_downright.gif"));

            foreach ($actions as $name => $value)
			{
				$tpl->setCurrentBlock("tbl_action_btn");
				$tpl->setVariable("BTN_NAME",$name);
				$tpl->setVariable("BTN_VALUE",$value);
				$tpl->parseCurrentBlock();
			}
			
			if (!empty($a_user_ids))
			{
				// set checkbox toggles
				$tpl->setCurrentBlock("tbl_action_toggle_checkboxes");
				$tpl->setVariable("JS_VARNAME","user_id");			
				$tpl->setVariable("JS_ONCLICK",ilUtil::array_php2js($a_user_ids));
				$tpl->setVariable("TXT_CHECKALL", $this->lng->txt("check_all"));
				$tpl->setVariable("TXT_UNCHECKALL", $this->lng->txt("uncheck_all"));
				$tpl->parseCurrentBlock();
			}
			
            $tpl->setVariable("TPLPATH",$this->tpl->tplPath);
		}

		$this->ctrl->setParameter($this,"cmd","members");


		// title & header columns
		$tbl->setTitle($this->lng->txt("members"),"icon_usr_b.gif",$this->lng->txt("group_members"));

		//INTERIMS:quite a circumstantial way to show the list on rolebased accessrights
		if ($rbacsystem->checkAccess("write",$this->object->getRefId()))
		{
			//user must be administrator
			$tbl->setHeaderNames(array("",$this->lng->txt("username"),$this->lng->txt("firstname"),$this->lng->txt("lastname"),$this->lng->txt("last_visit"),$this->lng->txt("role"),$this->lng->txt("grp_options")));
			$tbl->setHeaderVars(array("","login","firstname","lastname","date","role","functions"),$this->ctrl->getParameterArray($this,"",false));
			$tbl->setColumnWidth(array("","22%","22%","22%","22%","10%"));
		}
		else
		{
			//user must be member
			$tbl->setHeaderNames(array($this->lng->txt("username"),$this->lng->txt("firstname"),$this->lng->txt("lastname"),$this->lng->txt("last_visit"),$this->lng->txt("role"),$this->lng->txt("grp_options")));
			$tbl->setHeaderVars(array("login","firstname","lastname","date","role","functions"),$this->ctrl->getParameterArray($this,"",false));
			$tbl->setColumnWidth(array("22%","22%","22%","22%","10%"));
		}

		$this->__setTableGUIBasicData($tbl,$a_result_set,"members");
		$tbl->render();
		$this->tpl->setVariable("MEMBER_TABLE",$tbl->tpl->get());
		
		$ilBench->stop("GroupGUI", "__showMembersTable");

		return true;
	}

	function &__initTableGUI()
	{
		include_once "./Services/Table/classes/class.ilTableGUI.php";

		return new ilTableGUI(0,false);
	}

	function __setTableGUIBasicData(&$tbl,&$result_set,$from = "")
	{
        switch($from)
		{
			case "subscribers":
				$offset = $_GET["update_subscribers"] ? $_GET["offset"] : 0;
				$order = $_GET["update_subscribers"] ? $_GET["sort_by"] : 'login';
				$direction = $_GET["update_subscribers"] ? $_GET["sort_order"] : '';
				break;

			case "group":
				$offset = $_GET["offset"];
	           	$order = $_GET["sort_by"] ? $_GET["sort_by"] : "title";
				$direction = $_GET["sort_order"];
				break;
				
			case "role":
				$offset = $_GET["offset"];
	           	$order = $_GET["sort_by"] ? $_GET["sort_by"] : "title";
				$direction = $_GET["sort_order"];
				break;

			default:
				$offset = $_GET["offset"];
				// init sort_by (unfortunatly sort_by is preset with 'title'
	           	if ($_GET["sort_by"] == "title" or empty($_GET["sort_by"]))
                {
                    $_GET["sort_by"] = "login";
                }
                $order = $_GET["sort_by"];
				$direction = $_GET["sort_order"];
				break;
		}

		$tbl->setOrderColumn($order);
		$tbl->setOrderDirection($direction);
		$tbl->setOffset($offset);
		$tbl->setLimit($_GET["limit"]);
		//$tbl->setMaxCount(count($result_set));
		$tbl->setFooter("tblfooter",$this->lng->txt("previous"),$this->lng->txt("next"));
		$tbl->setData($result_set);
	}
	
	function listUsersRoleObject()
	{
		global $rbacsystem,$rbacreview;

		$_SESSION["grp_role"] = $_POST["role"] = $_POST["role"] ? $_POST["role"] : $_SESSION["grp_role"];

		// MINIMUM ACCESS LEVEL = 'administrate'
		if(!$rbacsystem->checkAccess("write", $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilias->error_obj->MESSAGE);
		}

		if(!is_array($_POST["role"]))
		{
			ilUtil::sendInfo($this->lng->txt("grp_no_roles_selected"));
			$this->searchObject();

			return false;
		}

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.grp_usr_selection.html");
		#$this->__showButton("cancelSearch",$this->lng->txt("grp_new_search"));

		// GET ALL MEMBERS
		$members = array();
		foreach($_POST["role"] as $role_id)
		{
			$members = array_merge($rbacreview->assignedUsers($role_id),$members);
		}

		$members = array_unique($members);
		$members = $this->__appendToStoredResults($members);

		// FORMAT USER DATA
		$counter = 0;
		$f_result = array();
		foreach($members as $user)
		{
			if(!$tmp_obj = ilObjectFactory::getInstanceByObjId($user,false))
			{
				continue;
			}
			
			$user_ids[$counter] = $user;

			$f_result[$counter][] = ilUtil::formCheckbox(0,"user[]",$user);
			$f_result[$counter][] = $tmp_obj->getLogin();
			$f_result[$counter][] = $tmp_obj->getLastname();
			$f_result[$counter][] = $tmp_obj->getFirstname();
			$f_result[$counter][] = ilFormat::formatDate($tmp_obj->getLastLogin());

			unset($tmp_obj);
			++$counter;
		}
		$this->__showSearchUserTable($f_result,$user_ids,"listUsersRole");

		return true;
	}
	
	/**
	* remove small icon
	*
	* @access	public
	*/
	function removeSmallIconObject()
	{
		$this->object->removeSmallIcon();
		ilUtil::redirect($this->ctrl->getLinkTarget($this, "edit"));
	}

	/**
	* remove big icon
	*
	* @access	public
	*/
	function removeBigIconObject()
	{
		$this->object->removeBigIcon();
		ilUtil::redirect($this->ctrl->getLinkTarget($this, "edit"));
	}
	
	function listUsersGroupObject()
	{
		global $rbacsystem,$rbacreview,$tree;

		$_SESSION["grp_group"] = $_POST["group"] = $_POST["group"] ? $_POST["group"] : $_SESSION["grp_group"];

		// MINIMUM ACCESS LEVEL = 'administrate'
		if(!$rbacsystem->checkAccess("write", $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilias->error_obj->MESSAGE);
		}

		if(!is_array($_POST["group"]))
		{
			ilUtil::sendInfo($this->lng->txt("grp_no_groups_selected"));
			$this->searchObject();

			return false;
		}

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.grp_usr_selection.html");
		#$this->__showButton("cancelSearch",$this->lng->txt("grp_new_search"));

		// GET ALL MEMBERS
		$members = array();
		foreach($_POST["group"] as $group_id)
		{
			if (!$tree->isInTree($group_id))
			{
				continue;
			}
			if (!$tmp_obj = ilObjectFactory::getInstanceByRefId($group_id))
			{
				continue;
			}

			$members = array_merge($tmp_obj->getGroupMemberIds(),$members);

			unset($tmp_obj);
		}

		$members = array_unique($members);

		// append users
		$members = $this->__appendToStoredResults($members);

		// FORMAT USER DATA
		$counter = 0;
		$f_result = array();
		foreach($members as $user)
		{
			if(!$tmp_obj = ilObjectFactory::getInstanceByObjId($user,false))
			{
				continue;
			}
			
			$user_ids[$counter] = $user;
			
			$f_result[$counter][] = ilUtil::formCheckbox(0,"user[]",$user);
			$f_result[$counter][] = $tmp_obj->getLogin();
			$f_result[$counter][] = $tmp_obj->getLastname();
			$f_result[$counter][] = $tmp_obj->getFirstname();
			$f_result[$counter][] = ilFormat::formatDate($tmp_obj->getLastLogin());

			unset($tmp_obj);
			++$counter;
		}
		$this->__showSearchUserTable($f_result,$user_ids,"listUsersGroup");

		return true;
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
* Clean output string from german umlauts. Replaces ä -> ae etc.
*
* @param string $str String to clean
* @return string Cleaned string
*/
	function cleanString($str)
	{
		return str_replace(array("ä","ö","ü","ß","Ä","Ö","Ü"), array("ae","oe","ue","ss","Ae","Oe","Ue"), $str);
	}

	/**
	* set sub tabs
	*/
	function __setSubTabs($a_tab)
	{
		global $rbacsystem,$ilUser;
	
		switch ($a_tab)
		{
				
			case 'members':
				$this->tabs_gui->addSubTabTarget("members",
				$this->ctrl->getLinkTarget($this,'members'),
				"members", get_class($this));
				
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

			case 'properties':
				$this->tabs_gui->addSubTabTarget("edit_properties",
												 $this->ctrl->getLinkTarget($this,'edit'),
												 "edit", get_class($this));
				
				$this->tabs_gui->addSubTabTarget('groupings',
												 $this->ctrl->getLinkTargetByClass('ilobjcoursegroupinggui','listGroupings'),
												 'listGroupings',
												 get_class($this));

				include_once("./Services/GoogleMaps/classes/class.ilGoogleMapUtil.php");
				if (ilGoogleMapUtil::isActivated())
				{
					$this->tabs_gui->addSubTabTarget("grp_map_settings",
												 $this->ctrl->getLinkTarget($this,'editMapSettings'),
												 "editMapSettings", get_class($this));
				}
				break;
		}
	}


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
		$info->enablePrivateNotes();
		$info->enableLearningProgress(true);

		$info->addSection($this->lng->txt('group_registration'));
		switch($this->object->getRegistrationFlag())
		{
			case GRP_REGISTRATION_DIRECT:
				$info->addProperty($this->lng->txt('group_registration_mode'),
								   $this->lng->txt('group_req_direct'));
				break;
												   
			case GRP_REGISTRATION_REQUEST:
				$info->addProperty($this->lng->txt('group_registration_mode'),
								   $this->lng->txt('group_req_registration'));
				break;

			case GRP_REGISTRATION_PASSWORD:
				$info->addProperty($this->lng->txt('group_registration_mode'),
								   $this->lng->txt('group_req_password'));
				break;
		}
		$date_times = $this->object->getExpirationDateTime();
		$info->addProperty($this->lng->txt('group_registration_time'),
						   $date_times[0].' '.$date_times[1]);
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
	* Edit Map Settings
	*/
	function editMapSettingsObject()
	{
		global $ilUser, $ilCtrl, $ilUser, $ilAccess;

		$this->__setSubTabs("properties");
		
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
		//$this->tpl->show();
	}

	function saveMapSettingsObject()
	{
		global $ilCtrl, $ilUser;

		$this->object->setLatitude(ilUtil::stripSlashes($_POST["location"]["latitude"]));
		$this->object->setLongitude(ilUtil::stripSlashes($_POST["location"]["longitude"]));
		$this->object->setLocationZoom(ilUtil::stripSlashes($_POST["location"]["zoom"]));
		$this->object->setEnableGroupMap(ilUtil::stripSlashes($_POST["enable_map"]));
		$this->object->update();
		
		$ilCtrl->redirect($this, "editMapSettings");
	}

} // END class.ilObjGroupGUI
?>
