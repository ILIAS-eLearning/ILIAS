<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
* Class ilObjUserFolderGUI
*
* @author Stefan Meyer <smeyer@databay.de> 
* @author Sascha Hofmann <saschahofmann@gmx.de>
* @author Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version $Id$
* 
* @ilCtrl_Calls ilObjUserFolderGUI: ilPermissionGUI, ilAdminUserSearchGUI, ilUserTableGUI
*
* @extends ilObjectGUI
*/

require_once "./classes/class.ilObjectGUI.php";

class ilObjUserFolderGUI extends ilObjectGUI
{
	var $ctrl;

	/**
	* Constructor
	* @access public
	*/
	function ilObjUserFolderGUI($a_data,$a_id,$a_call_by_reference, $a_prepare_output = true)
	{
		// TODO: move this to class.ilias.php
		define('USER_FOLDER_ID',7);
		
		$this->type = "usrf";
		$this->ilObjectGUI($a_data,$a_id,$a_call_by_reference,false);
		
		$this->lng->loadLanguageModule('search');
		$this->lng->loadLanguageModule("user");
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
				include_once("./classes/class.ilPermissionGUI.php");
				$perm_gui =& new ilPermissionGUI($this);
				$ret =& $this->ctrl->forwardCommand($perm_gui);
				break;
				
			case 'iladminusersearchgui':
				include_once('./Services/Search/classes/class.ilAdminUserSearchGUI.php');
				$user_search =& new ilAdminUserSearchGUI();
				$user_search->setCallbackClass($this);

				$this->tabs_gui->setTabActive('obj_usrf');
				$this->ctrl->setReturn($this,'view');
				$ret =& $this->ctrl->forwardCommand($user_search);
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

	function learningProgressExcelExportObject()
	{
		global $ilErr,$rbacsystem;
		
		if (!$rbacsystem->checkAccess("read",$this->object->getRefId()))
		{
			$ilErr->raiseError($this->lng->txt("permission_denied"),$ilErr->MESSAGE);
		}

		include_once './classes/class.ilExcelWriterAdapter.php';

		$pewa =& new ilExcelWriterAdapter('learning_progress.xls');

		// add/fill worksheet
		$this->_addLearningProgressWorksheet($pewa);

		// HEADER SENT
		
		$workbook =& $pewa->getWorkbook();
		$workbook->close();
	}
		
	function _addLearningProgressWorksheet(&$pewa)
	{
		include_once 'classes/class.ilExcelUtils.php';

		$workbook =& $pewa->getWorkbook();
		$worksheet =& $workbook->addWorksheet($this->lng->txt('objs_usr'));

		// SHOW HEADER
		$worksheet->mergeCells(0,0,0,4);
		$worksheet->setColumn(1,0,32);
		$worksheet->setColumn(1,1,32);
		$worksheet->setColumn(1,2,32);
		$worksheet->setColumn(1,3,32);
		$worksheet->setColumn(1,4,32);

		$title = $this->lng->txt('learning_progress');
		$title .= ' '.$this->lng->txt('as_of').': ';
		$title .= strftime('%Y-%m-%d %R',time());

		$worksheet->writeString(0,0,ilExcelUtils::_convert_text($title),$pewa->getFormatTitle());

		$worksheet->writeString(1,0,ilExcelUtils::_convert_text($this->lng->txt('login')),$pewa->getFormatHeader());
		$worksheet->writeString(1,1,ilExcelUtils::_convert_text($this->lng->txt('lastname')),$pewa->getFormatHeader());
		$worksheet->writeString(1,2,ilExcelUtils::_convert_text($this->lng->txt('firstname')),$pewa->getFormatHeader());
		$worksheet->writeString(1,3,ilExcelUtils::_convert_text($this->lng->txt('online_time')),$pewa->getFormatHeader());
		$worksheet->writeString(1,4,ilExcelUtils::_convert_text($this->lng->txt('last_login')),$pewa->getFormatHeader());


		$usr_data = ilObjUser::_getAllUserData(array("login","firstname","lastname",'last_login','online_time'),1);
		$usr_data = ilUtil::sortArray($usr_data,$_SESSION["usrf_sort_by"],$_SESSION["usrf_sort_order"]);

		$counter = 2;

		foreach($usr_data as $user)
		{
			$worksheet->writeString($counter,0,$user['login']);
			$worksheet->writeString($counter,1,$user['lastname']);
			$worksheet->writeString($counter,2,$user['firstname']);
			$worksheet->writeString($counter,3,ilFormat::_secondsToShortString($user['online_time']),
									$pewa->getFormatDayTime());
			$worksheet->writeString($counter,4,$user['last_login'],
									$pewa->getFormatDate());

			++$counter;
		}
	}


	function learningProgressObject()
	{
		include_once 'Services/Tracking/classes/class.ilOnlineTracking.php';
		include_once 'Services/Tracking/classes/class.ilObjUserTracking.php';

		$lp_active = ilObjUserTracking::_enabledLearningProgress();

		global $ilUser,$rbacsystem;

		$this->max_count = $ilUser->getPref('hits_per_page');

		if (!$rbacsystem->checkAccess("read",$this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}

		$_SESSION['usrf_sort_by'] = $_GET['sort_by'] ? $_GET['sort_by'] : ($_SESSION['usrf_sort_by'] ? 
																		   $_SESSION['usrf_sort_by'] : 'login');

		$_SESSION['usrf_sort_order'] = $_GET['sort_order'] ? $_GET['sort_order'] : ($_SESSION['usrf_sort_order'] ? 
																		   $_SESSION['usrf_sort_order'] : 'asc');


		// Excel export
		$this->tpl->addBlockfile("BUTTONS", "buttons", "tpl.buttons.html");

		// display button
		$this->lng->loadLanguageModule('payment');
		$this->tpl->setCurrentBlock("btn_cell");
		$this->tpl->setVariable("BTN_LINK",
								$this->ctrl->getLinkTarget($this, "learningProgressExcelExport"));
		$this->tpl->setVariable("BTN_TXT",$this->lng->txt("excel_export"));
		$this->tpl->parseCurrentBlock();

		//prepare objectlist
		$data["cols"] = array("login", "firstname", "lastname", "online_time",'last_login');
		
		$usr_data = ilObjUser::_getAllUserData(array("login","firstname","lastname",'last_login','online_time'),1);
		$num_users = count($usr_data) - 1;


		// sort and slice array
		$usr_data = ilUtil::sortArray($usr_data,$_SESSION["usrf_sort_by"],$_SESSION["usrf_sort_order"]);
		$usr_data = array_slice($usr_data,$_GET["offset"],$this->max_count);

		foreach ($usr_data as $val)
		{
			if ($val["usr_id"] == ANONYMOUS_USER_ID)
			{
                continue;
            }
			
			$data["data"]["$val[usr_id]"] = array(
				"login"			=> $val["login"],
				"firstname"		=> $val["firstname"],
				"lastname"		=> $val["lastname"],
				"online_time"  => ilFormat::_secondsToShortString($val['online_time']),
				"last_login"	=> ilDatePresentation::formatDate(new ilDateTime($val['last_login'],IL_CAL_DATETIME))
				);
		}
		
		$this->tpl->addBlockfile("ADM_CONTENT", "adm_content", "tpl.usr_list.html");

		// Show Table

		include_once "./Services/Table/classes/class.ilTableGUI.php";
		$this->tpl->addBlockfile("USR_TABLE", "user_table", "tpl.table.html");
		$this->tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.usr_tbl_row.html");

		$this->tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));

		// create table
		$tbl = new ilTableGUI();

		// title & header columns
		$tbl->setTitle($this->object->getTitle(),"icon_usr.gif",
					   $this->lng->txt("obj_".$this->object->getType()));

		foreach ($data["cols"] as $val)
		{
			$header_names[] = $this->lng->txt($val);
		}

		$tbl->setHeaderNames($header_names);

		$header_params = $this->ctrl->getParameterArray($this, "learningProgress");
		$tbl->setHeaderVars($data["cols"],$header_params);
		$tbl->setColumnWidth(array("20%","20$%","20%","20%"));
		

		// control
        //$tbl->enable("hits");
		$tbl->setOrderColumn($_GET["sort_by"]);
		$tbl->setOrderDirection($_GET["sort_order"]);
		$tbl->setLimit($this->max_count);
		$tbl->setOffset($_GET["offset"]);
		$tbl->setMaxCount($num_users);

		// footer
		$tbl->setFooter("tblfooter",$this->lng->txt("previous"),$this->lng->txt("next"));

		// render table
		$tbl->render();
		
		//table cell
		$counter = 0;
		foreach($data['data'] as $usr_id => $fields)
		{
			// color changing
			$css_row = ilUtil::switchColor($counter++,"tblrow1","tblrow2");

			$this->tpl->setVariable("CSS_ROW", $css_row);

			foreach ($fields as $key => $val)
			{
				//build link
					
				// dirty workaround to have ids for function showActions (checkbox toggle option)
				$this->ids[] = $ctrl["obj_id"];

				if ($key == "login" and $lp_active)
				{
					$this->ctrl->setParameterByClass("illearningprogressgui", "ref_id",$this->object->getRefId());
					$this->ctrl->setParameterByClass("illearningprogressgui", "obj_id", $usr_id);
					$link = $this->ctrl->getLinkTargetByClass(array("ilobjusergui",'illearningprogressgui'), "");
					
					$this->tpl->setCurrentBlock("begin_link");
					$this->tpl->setVariable("LINK_TARGET", $link);
					$this->tpl->parseCurrentBlock();
					$this->tpl->touchBlock("end_link");
				}

				$this->tpl->setCurrentBlock("text");
				$this->tpl->setVariable("TEXT_CONTENT", $val);
				$this->tpl->parseCurrentBlock();
				$this->tpl->setCurrentBlock("table_cell");
				$this->tpl->parseCurrentBlock();
			} //foreach
			
			$this->tpl->setCurrentBlock("tbl_content");
			$this->tpl->setVariable("CSS_ROW", $css_row);
			$this->tpl->parseCurrentBlock();
		} //for
	}
	
	function resetFilterObject()
	{
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
		include_once("./Services/User/classes/class.ilUserTableGUI.php");
		$utab = new ilUserTableGUI($this, "view");
		$utab->writeFilterToSession();
		$this->viewObject();
	}

	/**
	* list users
	*
	* @access	public
	*/
	function viewObject($reset_filter = FALSE)
	{
		global $rbacsystem, $ilUser, $ilToolbar, $tpl;
		
		// toolbar
		$ilToolbar->addButton($this->lng->txt("search_user_extended"),
			$this->ctrl->getLinkTargetByClass('ilAdminUserSearchGUI','startExtended'));
		if ($_SESSION['rep_search']['usr'])		// last search result
		{
			$ilToolbar->addButton($this->lng->txt("last_search_result"),
				$this->ctrl->getLinkTargetByClass('ilAdminUserSearchGUI','show'));
		}
		$ilToolbar->addButton($this->lng->txt("import_users"),
			$this->ctrl->getLinkTarget($this, "importUserForm"));

if (true)
{
	include_once("./Services/User/classes/class.ilUserTableGUI.php");
	$utab = new ilUserTableGUI($this, "view");
	$tpl->setContent($utab->getHTML());
	return;
}
		
			
		if ($reset_filter)
		{
			$_SESSION["user_filter"] = 1;
		}
		else
		{
			$_SESSION["usergui_cmd_node"] = $this->ctrl->getNodeIdForTargetClass($this->ctrl->getCmdNode(),"ilobjusergui");
			$this->ctrl->saveParameter($this, "user_filter_date_y");
			$this->ctrl->saveParameter($this, "user_filter_date_m");
			$this->ctrl->saveParameter($this, "user_filter_date_d");
			$this->ctrl->saveParameter($this, "selected_course");
			$this->ctrl->saveParameter($this, "selected_group");
			$this->ctrl->saveParameter($this, "selected_role");
			$this->ctrl->saveParameterByClass("ilAdminUserSearchGUI", "user_filter_date_y");
			$this->ctrl->saveParameterByClass("ilAdminUserSearchGUI", "user_filter_date_m");
			$this->ctrl->saveParameterByClass("ilAdminUserSearchGUI", "user_filter_date_d");
			$this->ctrl->saveParameterByClass("ilAdminUserSearchGUI", "selected_course");
			$this->ctrl->saveParameterByClass("ilAdminUserSearchGUI", "selected_group");
			$this->ctrl->saveParameterByClass("ilAdminUserSearchGUI", "selected_role");
			if (strlen($_GET["user_filter_date_y"].$_GET["user_filter_date_m"].$_GET["user_filter_date_d"]))
			{
				$_SESSION["user_filter_data"] = array(
					"y" => $_GET["user_filter_date_y"],
					"m" => $_GET["user_filter_date_m"],
					"d" => $_GET["user_filter_date_d"],
				);
			}
			if (strlen($_GET["selected_course"]))
			{
				$_SESSION["user_filter_data"] = $_GET["selected_course"];
			}
			if (strlen($_GET["selected_group"]))
			{
				$_SESSION["user_filter_data"] = $_GET["selected_group"];
			}
			if (strlen($_GET["selected_role"]))
			{
				$_SESSION["user_filter_data"] = $_GET["selected_role"];
			}
			if (isset($_POST["user_filter"]))
			{
				$_SESSION["user_filter"] = $_POST["user_filter"];
				switch ($_POST["user_filter"])
				{
					case 4:
						$_SESSION["user_filter_data"] = $_POST["date"];
						$this->ctrl->setParameterByClass("ilAdminUserSearchGUI", "user_filter_date_y", $_POST["date"]["y"]);
						$this->ctrl->setParameterByClass("ilAdminUserSearchGUI", "user_filter_date_m", $_POST["date"]["m"]);
						$this->ctrl->setParameterByClass("ilAdminUserSearchGUI", "user_filter_date_d", $_POST["date"]["d"]);
						$this->ctrl->setParameter($this, "user_filter_date_y", $_POST["date"]["y"]);
						$this->ctrl->setParameter($this, "user_filter_date_m", $_POST["date"]["m"]);
						$this->ctrl->setParameter($this, "user_filter_date_d", $_POST["date"]["d"]);
						break;
					case 5:
						$_SESSION["user_filter_data"] = $_POST["courseId"];
						$this->ctrl->setParameterByClass("ilAdminUserSearchGUI", "selected_course", $_POST["courseId"]);
						$this->ctrl->setParameter($this, "selected_course", $_POST["courseId"]);
						break;
					case 6:
						$_SESSION["user_filter_data"] = $_POST["groupId"];
						$this->ctrl->setParameterByClass("ilAdminUserSearchGUI", "selected_group", $_POST["groupId"]);
						$this->ctrl->setParameter($this, "selected_group", $_POST["groupId"]);
						break;
					case 7:
						$_SESSION["user_filter_data"] = $_POST["roleId"];
						$this->ctrl->setParameterByClass("ilAdminUserSearchGUI", "selected_role", $_POST["roleId"]);
						$this->ctrl->setParameter($this, "selected_role", $_POST["roleId"]);
						break;
				}
			}
			if (!isset($_SESSION["user_filter"]))
			{
				$_SESSION["user_filter"] = 1;
			}
		}
		
		// keep offset/sorting
		if (isset($_GET["sort_by"]))
		{
			$_SESSION["user_folder_sort_by"] = $_GET["sort_by"];
		}
		if (isset($_GET["sort_order"]))
		{
			$_SESSION["user_folder_order"] = $_GET["sort_order"];
		}
		if (isset($_GET["offset"]))
		{
			$_SESSION["user_folder_offset"] = $_GET["offset"];
		}
		$_GET["offset"] = $_SESSION["user_folder_offset"]; 
		$_GET["sort_order"] = $_SESSION["user_folder_order"] ? $_SESSION["user_folder_order"] : "asc";
		$_GET["sort_by"] = $_SESSION["user_folder_sort_by"] ? $_SESSION["user_folder_sort_by"] : "login";

		if (!$rbacsystem->checkAccess("visible,read",$this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}

		//prepare objectlist
		$this->data = array();
		$this->data["data"] = array();
		$this->data["ctrl"] = array();

		$this->data["cols"] = array("", "login", "firstname", "lastname","email","access_until", "last_login");
		
		$usr_data = ilObjUser::_getAllUserData(array("login","firstname","lastname","email","time_limit_until","time_limit_unlimited","last_login", "active"), $_SESSION["user_filter"]);

		// fetch current time outside loop
		$current_time = time();
		
		foreach ($usr_data as $val)
		{
			if ($val["usr_id"] == ANONYMOUS_USER_ID)
			{
				continue;
			}
            
			// determine txt_access
			// Note: If you do changes here, you have to change the corresponding
			//       code in ilAdminUserSearchGUI::__fillUserTable as well.
			if ($val['active'])
			{
				if ($val["time_limit_unlimited"])
				{
					$txt_access = $this->lng->txt("access_unlimited");
				}
				elseif ($val["time_limit_until"] < $current_time)
				{
					$txt_access = $this->lng->txt("access_expired");
				}
				else
				{
					$txt_access = ilDatePresentation::formatDate(new ilDateTime($val["time_limit_until"],IL_CAL_UNIX));
				}
			}
			else
			{
				$txt_access = $this->lng->txt("inactive");
			}
			//visible data part
			$this->data["data"][] = array(
							"login"			=> $val["login"],
							"firstname"		=> $val["firstname"],
							"lastname"		=> $val["lastname"],
							"email"			=> $val["email"],
							"access_until"	=> $txt_access,
							"last_login"	=> $val["last_login"],
							"obj_id"		=> $val["usr_id"]
						);
		}
		
		$this->maxcount = count($this->data["data"]);

		if ($_GET["offset"] >= $this->maxcount)
		{
			$_GET["offset"] = $_SESSION["user_folder_offset"] = 0;
		}
		
		// TODO: correct this in objectGUI
		if ($_GET["sort_by"] == "name")
		{
			$_GET["sort_by"] = "login";
		}
		
		//var_dump("<pre>",$this->data["data"],"</pre>");
		// sorting array
		$this->data["data"] = ilUtil::sortArray($this->data["data"],$_GET["sort_by"],$_GET["sort_order"]);
		$this->data["data"] = array_slice($this->data["data"],$_GET["offset"],$_GET["limit"]);
		//var_dump("<pre>",$this->data["data"],"</pre>");
		// now compute control information
		foreach ($this->data["data"] as $key => $val)
		{
			$this->data["ctrl"][$key] = array(
												"ref_id"	=> $this->id,
												"obj_id"	=> $val["obj_id"]
											);
			$tmp[] = $val["obj_id"];
			unset($this->data["data"][$key]["obj_id"]);
		}

		$this->tpl->addBlockfile("ADM_CONTENT", "adm_content", "tpl.usr_list.html");
		
		switch ($_SESSION["user_filter"])
		{
			case 4:
				$this->tpl->setCurrentBlock("filter_date");
				$this->tpl->setVariable("DATE_SELECTION", ilUtil::makeDateSelect("date", $_SESSION["user_filter_data"]["y"], $_SESSION["user_filter_data"]["m"], $_SESSION["user_filter_data"]["d"]));
				$this->tpl->setVariable("TEXT_FILTER_DATE", $this->lng->txt("before"));
				$this->tpl->parseCurrentBlock();
				break;
			case 5:
				$this->tpl->setCurrentBlock("filter_course");
				$courses = ilUtil::_getObjectsByOperations("crs", "read", $ilUser->getId(), -1);
				$options = ilObject::_prepareCloneSelection($courses, "crs");
				$select = ilUtil::formSelect($_SESSION["user_filter_data"], "courseId" ,$options,false,true);
				$this->tpl->setVariable("COURSE_SELECTION", $select);
				$this->tpl->setVariable("TEXT_FILTER_COURSE", $this->lng->txt("course"));
				$this->tpl->parseCurrentBlock();
				break;
			case 6:
				$this->tpl->setCurrentBlock("filter_group");
				$groups = ilUtil::_getObjectsByOperations("grp", "read", $ilUser->getId(), -1);
				$options = ilObject::_prepareCloneSelection($groups, "grp");
				$select = ilUtil::formSelect($_SESSION["user_filter_data"], "groupId" ,$options,false,true);
				$this->tpl->setVariable("GROUP_SELECTION", $select);
				$this->tpl->setVariable("TEXT_FILTER_GROUP", $this->lng->txt("grp"));
				$this->tpl->parseCurrentBlock();
				break;
			case 7:
				global $rbacreview;
				$this->tpl->setCurrentBlock("filter_role");
				$roles = $rbacreview->getRolesByFilter(2, $ilUser->getId());
				$options = array();
				foreach ($roles as $role)
				{
					$options[$role["rol_id"]] = $role["title"];
				}
				$select = ilUtil::formSelect($_SESSION["user_filter_data"], "roleId" ,$options, false, true);
				$this->tpl->setVariable("ROLE_SELECTION", $select);
				$this->tpl->setVariable("TEXT_FILTER_ROLE", $this->lng->txt("role"));
				$this->tpl->parseCurrentBlock();
				break;
		}
		$this->tpl->setCurrentBlock("filter");
		$this->tpl->setVariable("FILTER_TXT_FILTER",$this->lng->txt('filter'));
		$this->tpl->setVariable("SELECT_FILTER",$this->__buildUserFilterSelect());
		$this->tpl->setVariable("FILTER_ACTION",$this->ctrl->getFormAction($this));
		$this->tpl->setVariable("FILTER_NAME",'view');
		$this->tpl->setVariable("FILTER_VALUE",$this->lng->txt('apply_filter'));
		$this->tpl->setVariable("FILTER_VALUE_RESET",$this->lng->txt('reset_filter'));
		$this->tpl->parseCurrentBlock();
		
		$this->tpl->setCurrentBlock("search");
		$this->tpl->setVariable("SEARCH_TXT_SEARCH",$this->lng->txt('search'));
		$this->ctrl->setParameterByClass("ilAdminUserSearchGUI", "user_filter", $_SESSION["user_filter"]);
		$this->tpl->setVariable("SEARCH_ACTION",$this->ctrl->getFormActionByClass('ilAdminUserSearchGUI','startSimple'));
		$this->tpl->setVariable("SEARCH_NAME",'startSimple');
		$this->tpl->setVariable("SEARCH_VALUE",$this->lng->txt('search_users'));
		$this->tpl->parseCurrentBlock();

		$this->displayList();
	} //function


	/**
	* display object list
	*
	* @access	public
 	*/
	function displayList()
	{
		global $lng;
		
		if (!is_array($this->data["data"][0]))
		{
			ilUtil::sendInfo($this->lng->txt("usr_filter_empty_result")); return;
		}

		include_once "./Services/Table/classes/class.ilTableGUI.php";

		// load template for table
		$this->tpl->addBlockfile("USR_TABLE", "user_table", "tpl.table.html");
		// load template for table content data
		$this->tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.obj_tbl_rows.html");

		$num = 0;

		//$obj_str = ($this->call_by_reference) ? "" : "&obj_id=".$this->obj_id;
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));

		// create table
		$tbl = new ilTableGUI();

		// title & header columns
		$tbl->setTitle($this->object->getTitle(),"icon_usr.gif", $this->lng->txt("obj_".$this->object->getType()));

		foreach ($this->data["cols"] as $val)
		{
			$header_names[] = $this->lng->txt($val);
		}

		$tbl->setHeaderNames($header_names);

		$header_params = $this->ctrl->getParameterArray($this, "view");
		$tbl->setHeaderVars($this->data["cols"],$header_params);
		$tbl->setColumnWidth(array("","15%","15%","15%","20%","15%","20%"));
		
		$tbl->enable("select_all");
		$tbl->enable("numinfo_header");
		$tbl->setFormName("cmd");
		$tbl->setSelectAllCheckbox("id");

		// control
		$tbl->setOrderColumn($_GET["sort_by"]);
		$tbl->setOrderDirection($_GET["sort_order"]);
		$tbl->setLimit($_GET["limit"]);
		$tbl->setOffset($_GET["offset"]);
		$tbl->setMaxCount($this->maxcount);
		// footer
		$tbl->setFooter("tblfooter",$this->lng->txt("previous"),$this->lng->txt("next"));
		
		// render table
		$tbl->render();
		
		if (is_array($this->data["data"][0]))
		{
			//table cell
			for ($i=0; $i < count($this->data["data"]); $i++)
			{
				$data = $this->data["data"][$i];
				$ctrl = $this->data["ctrl"][$i];

				// color changing
				$css_row = ilUtil::switchColor($i+1,"tblrow1","tblrow2");

				$this->tpl->setCurrentBlock("checkbox");
				$this->tpl->setVariable("CHECKBOX_ID", $ctrl["obj_id"]);
				$this->tpl->setVariable("CSS_ROW", $css_row);
				$this->tpl->parseCurrentBlock();

				$this->tpl->setCurrentBlock("table_cell");
				$this->tpl->setVariable("CELLSTYLE", "tblrow1");
				$this->tpl->parseCurrentBlock();

				foreach ($data as $key => $val)
				{
					//build link
					$this->ctrl->setParameterByClass("ilobjusergui", "ref_id", "7");
					$this->ctrl->setParameterByClass("ilobjusergui", "obj_id", $ctrl["obj_id"]);
					$link = $this->ctrl->getLinkTargetByClass("ilobjusergui", "view");
					
					// dirty workaround to have ids for function showActions (checkbox toggle option)
					$this->ids[] = $ctrl["obj_id"];

					if ($key == "login")
					{
						$this->tpl->setCurrentBlock("begin_link");
						$this->tpl->setVariable("LINK_TARGET", $link);
						$this->tpl->parseCurrentBlock();
						$this->tpl->touchBlock("end_link");
					}
					
					if ($key == "access_until")
					{
						if ($val == $this->lng->txt("access_unlimited"))
						{
							$val = "<span class=\"smallgreen\">".$val."</span>";
						}
						elseif ($val == $this->lng->txt("access_expired") ||
							$val == $this->lng->txt("inactive"))
						{
							$val = "<span class=\"smallred\">".$val."</span>";
						}
						else
						{
							$val = "<span class=\"small\">".$val."</span>";
						}
					}
					
					if ($key == "last_login")
					{
						$val = ilDatePresentation::formatDate(new ilDateTime($val,IL_CAL_DATETIME));
					}

					if ($key != "access_until")
					{
						$val = "<span class=\"small\">".$val."</span>";
					}

					$this->tpl->setCurrentBlock("text");
					$this->tpl->setVariable("TEXT_CONTENT", $val);
					$this->tpl->parseCurrentBlock();
					$this->tpl->setCurrentBlock("table_cell");
					$this->tpl->parseCurrentBlock();
				} //foreach

				$this->tpl->setCurrentBlock("tbl_content");
				$this->tpl->setVariable("CSS_ROW", $css_row);
				$this->tpl->parseCurrentBlock();
			} //for
		}
		$this->showActions(true);
		
		
		
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
			$this->tpl->setVariable("IMG_ARROW", ilUtil::getImagePath("arrow_downright.gif"));
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
		
		if (!$rbacsystem->checkAccess('create_user',$this->object->getRefId()))
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

	function cancelUserFolderAction()
	{
		session_unregister("saved_post");
		$this->ctrl->returnToParent($this);
	}

	/**
	* cancel activation of object
	*
	* @access	public
	*/
	function cancelactivateObject()
	{
		$this->cancelUserFolderAction();
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
		
		$_SESSION['saved_post'] = $_SESSION['saved_post'] ? $_SESSION['saved_post'] : array();  
		
		// FOR ALL SELECTED OBJECTS
		foreach ($_SESSION["saved_post"] as $id)
		{
			// instatiate correct object class (usr)
			$obj =& $this->ilias->obj_factory->getInstanceByObjId($id);
			$obj->setActive(TRUE, $ilUser->getId());
			$obj->update();
		}

		ilUtil::sendSuccess($this->lng->txt("user_activated"),true);

		if ($_SESSION['user_activate_search'] == true)
		{
			session_unregister("user_activate_search");
			$script = $this->ctrl->getLinkTargetByClass('ilAdminUserSearchGUI','show');
			ilUtil::redirect($script);
		}
		else
		{
			$this->ctrl->redirect($this, "view");
		}
	}

	/**
	* cancel activation of object
	*
	* @access	public
	*/
	function canceldeactivateObject()
	{
		$this->cancelUserFolderAction();
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
		
		$_SESSION['saved_post'] = $_SESSION['saved_post'] ? $_SESSION['saved_post'] : array();  
		
		// FOR ALL SELECTED OBJECTS
		foreach ($_SESSION["saved_post"] as $id)
		{
			// instatiate correct object class (usr)
			$obj =& $this->ilias->obj_factory->getInstanceByObjId($id);
			$obj->setActive(FALSE, $ilUser->getId());
			$obj->update();
		}

		// Feedback
		ilUtil::sendSuccess($this->lng->txt("user_deactivated"),true);

		if ($_SESSION['user_deactivate_search'] == true)
		{
			session_unregister("user_deactivate_search");
			$script = $this->ctrl->getLinkTargetByClass('ilAdminUserSearchGUI','show');
			ilUtil::redirect($script);
		}
		else
		{
			$this->ctrl->redirect($this, "view");
		}
	}
	
	function cancelaccessFreeObject()
	{
		$this->cancelUserFolderAction();
	}
	
	function confirmaccessFreeObject()
	{
		global $rbacsystem, $ilUser;

		// FOR NON_REF_OBJECTS WE CHECK ACCESS ONLY OF PARENT OBJECT ONCE
		if (!$rbacsystem->checkAccess('write',$this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilias->error_obj->WARNING);
		}
		
		$_SESSION['saved_post'] = $_SESSION['saved_post'] ? $_SESSION['saved_post'] : array();  
		
		// FOR ALL SELECTED OBJECTS
		foreach ($_SESSION["saved_post"] as $id)
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

		if ($_SESSION['user_accessFree_search'] == true)
		{
			session_unregister("user_accessFree_search");
			$script = $this->ctrl->getLinkTargetByClass('ilAdminUserSearchGUI','show');
			ilUtil::redirect($script);
		}
		else
		{
			$this->ctrl->redirect($this, "view");
		}
	}
	
	function setAccessRestrictionObject()
	{
		$this->tpl->addBlockfile("ADM_CONTENT", "adm_content", "tpl.confirm.html");
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
		$this->tpl->setVariable("TXT_CONFIRM", $this->lng->txt("time_limit_add_time_limit_for_selected"));
		include_once "./classes/class.ilTemplate.php";
		$tsTemplate = new ilTemplate("tpl.time.limit.html", TRUE, TRUE);
		$from = (array_key_exists("from", $_GET)) ? $_GET["from"] : time();
		$until = (array_key_exists("until", $_GET)) ? $_GET["until"] : time();
		$fromA = getdate($from);
		$untilA = getdate($until);
		$fromDate = ilUtil::makeDateSelect("fromDate", $fromA["year"], $fromA["mon"], $fromA["mday"]);
		$fromTime = ilUtil::makeTimeSelect("fromTime", TRUE, $fromA["hours"], $fromA["minutes"]);
		$untilDate = ilUtil::makeDateSelect("untilDate", $untilA["year"], $untilA["mon"], $untilA["mday"]);
		$untilTime = ilUtil::makeTimeSelect("untilTime", TRUE, $untilA["hours"], $untilA["minutes"]);
		$tsTemplate->setVariable("TEXT_FROM", $this->lng->txt("access_from") . ": ");
		$tsTemplate->setVariable("TEXT_UNTIL", $this->lng->txt("access_until") . ": ");
		$tsTemplate->setVariable("DATE_FROM", $fromDate);
		$tsTemplate->setVariable("TIME_FROM", $fromTime);
		$tsTemplate->setVariable("DATE_UNTIL", $untilDate);
		$tsTemplate->setVariable("TIME_UNTIL", $untilTime);
		$this->tpl->setVariable("TXT_CONTENT", $tsTemplate->get());
		$this->tpl->setVariable("CMD_CANCEL", "cancelaccessRestrict");
		$this->tpl->setVariable("TXT_CANCEL", $this->lng->txt("cancel"));
		$this->tpl->setVariable("CMD_OK", "confirmaccessRestrict");
		$this->tpl->setVariable("TXT_OK", $this->lng->txt("confirm"));
		$this->tpl->parseCurrentBlock();
	}

	function cancelaccessRestrictObject()
	{
		$this->cancelUserFolderAction();
	}
	
	function confirmaccessRestrictObject()
	{
		$timefrom = mktime($_POST["fromTime"]["h"], $_POST["fromTime"]["m"], 0, $_POST["fromDate"]["m"], $_POST["fromDate"]["d"], $_POST["fromDate"]["y"]);
		$timeuntil = mktime($_POST["untilTime"]["h"], $_POST["untilTime"]["m"], 0, $_POST["untilDate"]["m"], $_POST["untilDate"]["d"], $_POST["untilDate"]["y"]);
		if ($timeuntil <= $timefrom)
		{
			ilUtil::sendFailure($this->lng->txt("time_limit_not_valid"), TRUE);
			$this->ctrl->setParameter($this, "from", $timefrom);
			$this->ctrl->setParameter($this, "until", $timeuntil);
			$this->ctrl->redirect($this, "setAccessRestriction");
		}

		global $rbacsystem, $ilUser;

		// FOR NON_REF_OBJECTS WE CHECK ACCESS ONLY OF PARENT OBJECT ONCE
		if (!$rbacsystem->checkAccess('write',$this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilias->error_obj->WARNING);
		}
		
		$_SESSION['saved_post'] = $_SESSION['saved_post'] ? $_SESSION['saved_post'] : array();  
		
		// FOR ALL SELECTED OBJECTS
		foreach ($_SESSION["saved_post"] as $id)
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

		if ($_SESSION['user_accessRestrict_search'] == true)
		{
			session_unregister("user_accessRestrict_search");
			$script = $this->ctrl->getLinkTargetByClass('ilAdminUserSearchGUI','show');
			ilUtil::redirect($script);
		}
		else
		{
			$this->ctrl->redirect($this, "view");
		}
	}

	/**
	* cancel deletion of object
	*
	* @access	public
	*/
	function canceldeleteObject()
	{
		$this->cancelUserFolderAction();
	}

	/**
	* confirm delete Object
	*
	* @access	public
	*/
	function confirmdeleteObject()
	{
		global $rbacsystem;

		// FOR NON_REF_OBJECTS WE CHECK ACCESS ONLY OF PARENT OBJECT ONCE
		if (!$rbacsystem->checkAccess('delete',$this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_delete"),$this->ilias->error_obj->WARNING);
		}
		
		$_SESSION['saved_post'] = $_SESSION['saved_post'] ? $_SESSION['saved_post'] : array();
		
		if (in_array($_SESSION["AccountId"],$_SESSION["saved_post"]))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_delete_yourself"),$this->ilias->error_obj->WARNING);
		}

		// FOR ALL SELECTED OBJECTS
		foreach ($_SESSION["saved_post"] as $id)
		{
			// instatiate correct object class (usr)
			$obj =& $this->ilias->obj_factory->getInstanceByObjId($id);
			$obj->delete();
		}

		// Feedback
		ilUtil::sendSuccess($this->lng->txt("user_deleted"),true);
		
		// now unset the delete users in session
		#unset($_SESSION['saved_post']);

		if ($_SESSION['user_delete_search'] == true)
		{
			session_unregister("user_delete_search");
			$script = $this->ctrl->getLinkTargetByClass('ilAdminUserSearchGUI','show');
			ilUtil::redirect($script);
		}
		else
		{
			$this->ctrl->redirect($this, "view");
		}
	}

	/**
	* cancel export of object
	*
	* @access	public
	*/
	function cancelexportObject()
	{
		$this->cancelUserFolderAction();
	}

	/**
	* confirm export of object
	*
	* @access	public
	*/
	function confirmexportObject()
	{
		$user_data_filter = $_SESSION['saved_post'] ? $_SESSION['saved_post'] : array();  
		session_unregister("saved_post");
		$this->object->buildExportFile($_POST["export_type"], $user_data_filter);
		ilUtil::redirect($this->ctrl->getLinkTargetByClass("ilobjuserfoldergui", "export"));
	}

	/**
	* display deletion confirmation screen
	*/
	function deleteObject()
	{
		if(!isset($_POST["id"]))
		{
			$this->ilias->raiseError($this->lng->txt("no_checkbox"),$this->ilias->error_obj->MESSAGE);
		}
		// SAVE POST VALUES
		$_SESSION["saved_post"] = $_POST["id"];

		unset($this->data);
		$this->data["cols"] = array("type", "title", "description", "last_change");

		foreach($_POST["id"] as $id)
		{
			$obj_data =& $this->ilias->obj_factory->getInstanceByObjId($id);

			$this->data["data"]["$id"] = array(
				"type"        => $obj_data->getType(),
				"title"       => $obj_data->getTitle(),
				"desc"        => $obj_data->getDescription(),
				"last_update" => $obj_data->getLastUpdateDate());
		}

		$this->data["buttons"] = array( "cancelDelete"  => $this->lng->txt("cancel"),
								  "confirmedDelete"  => $this->lng->txt("confirm"));

		$this->getTemplateFile("confirm");

		ilUtil::sendQuestion($this->lng->txt("info_delete_sure"));

		$this->tpl->setVariable("FORMACTION",
			$this->ctrl->getFormAction($this));

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

		foreach($this->data["data"] as $key => $value)
		{
			// BEGIN TABLE CELL
			foreach($value as $key => $cell_data)
			{
				$this->tpl->setCurrentBlock("table_cell");

				// CREATE TEXT STRING
				if($key == "type")
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
		foreach($this->data["buttons"] as $name => $value)
		{
			$this->tpl->setCurrentBlock("operation_btn");
			$this->tpl->setVariable("BTN_NAME",$name);
			$this->tpl->setVariable("BTN_VALUE",$value);
			$this->tpl->parseCurrentBlock();
		}
	}

	function selectExportFormat()
	{
		$this->tpl->addBlockfile("ADM_CONTENT", "adm_content", "tpl.confirm.html");
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
		$this->tpl->setVariable("TXT_CONFIRM", $this->lng->txt("export_format_selection"));
		$export_types = array("userfolder_export_excel_x86", "userfolder_export_csv", "userfolder_export_xml");
		$options = array();
		foreach ($export_types as $type)
		{
			$options[$type] = $this->lng->txt($type);
		}
		$select = ilUtil::formSelect("userfolder_export_xml", "export_type" ,$options, false, true);
		$this->tpl->setVariable("TXT_CONTENT", $this->lng->txt("export_format") . ": " . $select);
		$this->tpl->setVariable("CMD_CANCEL", "cancelexport");
		$this->tpl->setVariable("TXT_CANCEL", $this->lng->txt("cancel"));
		$this->tpl->setVariable("CMD_OK", "confirmexport");
		$this->tpl->setVariable("TXT_OK", $this->lng->txt("confirm"));
		$this->tpl->parseCurrentBlock();
	}

	/**
	* display activation confirmation screen
	*/
	function showActionConfirmation($action)
	{
		if(!isset($_POST["id"]))
		{
			$this->ilias->raiseError($this->lng->txt("no_checkbox"),$this->ilias->error_obj->MESSAGE);
		}
		// SAVE POST VALUES
		$_SESSION["saved_post"] = $_POST["id"];

		if (strcmp($action, "export") == 0) return $this->selectExportFormat();
		if (strcmp($action, "accessRestrict") == 0) return $this->setAccessRestrictionObject();

		unset($this->data);
		$this->data["cols"] = array("type", "title", "description", "last_change");

		foreach($_POST["id"] as $id)
		{
			$obj_data =& $this->ilias->obj_factory->getInstanceByObjId($id);

			$this->data["data"]["$id"] = array(
				"type"        => $obj_data->getType(),
				"title"       => $obj_data->getTitle(),
				"desc"        => $obj_data->getDescription(),
				"last_update" => $obj_data->getLastUpdateDate());
		}

		$this->data["buttons"] = array( "cancel" . $action  => $this->lng->txt("cancel"),
								  "confirm" . $action  => $this->lng->txt("confirm"));

		$this->getTemplateFile("confirm");

		ilUtil::sendQuestion($this->lng->txt("info_" . $action . "_sure"));

		$this->tpl->setVariable("FORMACTION",
			$this->ctrl->getFormAction($this));

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

		foreach($this->data["data"] as $key => $value)
		{
			// BEGIN TABLE CELL
			foreach($value as $key => $cell_data)
			{
				$this->tpl->setCurrentBlock("table_cell");

				// CREATE TEXT STRING
				if($key == "type")
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
		foreach($this->data["buttons"] as $name => $value)
		{
			$this->tpl->setCurrentBlock("operation_btn");
			$this->tpl->setVariable("BTN_NAME",$name);
			$this->tpl->setVariable("BTN_VALUE",$value);
			$this->tpl->parseCurrentBlock();
		}
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

	/**
	* Export users
	*/
	function exportUsersObject()
	{
		$_POST["selectedAction"] = "export";
		$this->showActionConfirmation($_POST["selectedAction"]);
	}

	function userActionObject()
	{
		$this->showActionConfirmation($_POST["selectedAction"]);
	}
	
	/**
     * displays user search form
     *
     *
     */
   	// presumably deprecated
	// functionality moved to search/classes/iladminusersearch
	// dont't if this method is used elsewhere too	- saschahofmann@gmx.de 6.6.07
	function searchUserFormObject ()
	{
		$this->tabs_gui->setTabActive('obj_usrf');

		$this->tpl->addBlockfile("ADM_CONTENT", "adm_content", "tpl.usr_search_form.html");

		$this->tpl->setVariable("FORMACTION",
			$this->ctrl->getFormAction($this));
		$this->tpl->setVariable("USERNAME_CHECKED", " checked=\"checked\"");
		$this->tpl->setVariable("FIRSTNAME_CHECKED", " checked=\"checked\"");
		$this->tpl->setVariable("LASTNAME_CHECKED", " checked=\"checked\"");
		$this->tpl->setVariable("EMAIL_CHECKED", " checked=\"checked\"");
		$this->tpl->setVariable("ACTIVE_CHECKED", " checked=\"checked\"");
		$this->tpl->setVariable("TXT_SEARCH_USER",$this->lng->txt("search_user"));
		$this->tpl->setVariable("TXT_SEARCH_IN",$this->lng->txt("search_in"));
		$this->tpl->setVariable("TXT_SEARCH_USERNAME",$this->lng->txt("username"));
		$this->tpl->setVariable("TXT_SEARCH_FIRSTNAME",$this->lng->txt("firstname"));
		$this->tpl->setVariable("TXT_SEARCH_LASTNAME",$this->lng->txt("lastname"));
		$this->tpl->setVariable("TXT_SEARCH_EMAIL",$this->lng->txt("email"));
        $this->tpl->setVariable("TXT_SEARCH_ACTIVE",$this->lng->txt("search_active"));
        $this->tpl->setVariable("TXT_SEARCH_INACTIVE",$this->lng->txt("search_inactive"));
		$this->tpl->setVariable("BUTTON_SEARCH",$this->lng->txt("search"));
		$this->tpl->setVariable("BUTTON_CANCEL",$this->lng->txt("cancel"));
        $this->tpl->setVariable("TXT_SEARCH_NOTE",$this->lng->txt("search_note"));
		$this->tpl->setVariable("ACTIVE_CHECKED","checked=\"checked\"");
	}

	// presumably deprecated
	// functionality moved to search/classes/iladminusersearch
	// dont't if this method is used elsewhere too	- saschahofmann@gmx.de 6.6.07
	function searchCancelledObject()
	{
		$this->ctrl->redirect($this, "view");
	}

	// presumably deprecated
	// functionality moved to search/classes/iladminusersearch
	// dont't if this method is used elsewhere too	- saschahofmann@gmx.de 6.6.07
	function searchUserObject()
	{
		global $rbacreview;

		$obj_str = "&obj_id=".$this->obj_id;

		$_POST["search_string"] = trim($_POST["search_string"]) ? trim($_POST["search_string"]) : trim(urldecode($_GET["search_string"]));
        //$_POST["search_fields"] = $_POST["search_fields"] ? $_POST["search_fields"] : explode(",",urldecode($_GET["search_fields"]));
		$_SESSION['us_active'] = isset($_POST['active']) ? $_POST['active'] : $_SESSION['us_active'];

		$_POST["search_fields"] = array ("username","firstname","lastname","email");

        if (empty($_POST["search_string"]))
        {
            $_POST["search_string"] = "%";
        }

        if (empty($_POST["search_fields"]))
        {
            $_POST["search_fields"] = array();
        }
		if (count($search_result = ilObjUser::searchUsers($_POST["search_string"],$_SESSION['us_active'])) == 0)
		{
	        if ($_POST["search_string"] == "%")
    	    {
        	    $_POST["search_string"] = "";
	        }
			$msg = $this->lng->txt("msg_no_search_result");
			if ($_POST["search_string"] != "")
			{
				$msg .= " ".$this->lng->txt("with")." '".htmlspecialchars($_POST["search_string"])."'";
			}
			ilUtil::sendInfo($msg, true);
			$this->ctrl->redirect($this, "searchUserForm");
		}
		//add template for buttons
		$this->tpl->addBlockfile("BUTTONS", "buttons", "tpl.buttons.html");
		
		// display button
		$this->tpl->setCurrentBlock("btn_cell");
		$this->tpl->setVariable("BTN_LINK",
			$this->ctrl->getLinkTarget($this, "searchUserForm"));
		$this->tpl->setVariable("BTN_TXT",$this->lng->txt("search_new"));
		$this->tpl->parseCurrentBlock();

        $this->data["cols"] = array("", "login", "firstname", "lastname", "email", "active");

		if($_SESSION['us_active'] == 1)
		{
            $searchActive = true;
		}
        else
        {
            $searchInactive = true;
        }

		foreach ($search_result as $key => $val)
		{
            $val["active_text"] = $this->lng->txt("inactive");
            if ($val["active"])
            {
                $val["active_text"] = $this->lng->txt("active");
            }

			// check if the fields are set
			$searchStringToLower = strtolower($_POST["search_string"]);
			$displaySearchResult = false;
			if (in_array("username", $_POST["search_fields"]))
				if (strpos(strtolower($val["login"]), strtolower($_POST["search_string"])) !== false)
					$displaySearchResult = true;
			if (in_array("firstname", $_POST["search_fields"]))
				if (strpos(strtolower($val["firstname"]), strtolower($_POST["search_string"])) !== false)
					$displaySearchResult = true;
			if (in_array("lastname", $_POST["search_fields"]))
				if (strpos(strtolower($val["lastname"]), strtolower($_POST["search_string"])) !== false)
					$displaySearchResult = true;
			if (in_array("email", $_POST["search_fields"]))
				if (strpos(strtolower($val["email"]), strtolower($_POST["search_string"])) !== false)
					$displaySearchResult = true;
			if (($val["active"] == 1) && ($searchActive == true) ||
				($val["active"] == 0) && ($searchInactive == true))
            {
				if ((strcmp($_POST["search_string"], "%") == 0) || $displaySearchResult)
				{
					//visible data part
					$this->data["data"][] = array(
						"login"         => $val["login"],
						"firstname"     => $val["firstname"],
						"lastname"      => $val["lastname"],
						"email"         => $val["email"],
						"active"        => $val["active_text"],
						"obj_id"        => $val["usr_id"]
						);
				}
            }
		}
		if (count($this->data["data"]) == 0)
		{
			ilUtil::sendInfo($this->lng->txt("msg_no_search_result")." ".$this->lng->txt("with")." '".htmlspecialchars($_POST["search_string"])."'",true);

			$this->ctrl->redirect($this, "searchUserForm");
		}
		
		$this->maxcount = count($this->data["data"]);

		// TODO: correct this in objectGUI
		if ($_GET["sort_by"] == "name")
		{
			$_GET["sort_by"] = "login";
		}

		// sorting array
		$this->data["data"] = ilUtil::sortArray($this->data["data"],$_GET["sort_by"],$_GET["sort_order"]);
		$this->data["data"] = array_slice($this->data["data"],$_GET["offset"],$_GET["limit"]);

		// now compute control information
		foreach ($this->data["data"] as $key => $val)
		{
			$this->data["ctrl"][$key] = array(
												"ref_id"	=> $this->id,
												"obj_id"	=> $val["obj_id"]
											);
			$tmp[] = $val["obj_id"];
			unset($this->data["data"][$key]["obj_id"]);
		}

		// remember filtered users
		$_SESSION["user_list"] = $tmp;		
	
		// load template for table
		$this->tpl->addBlockfile("ADM_CONTENT", "adm_content", "tpl.table.html");
		// load template for table content data
		$this->tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.obj_tbl_rows.html");

		$num = 0;

		$this->ctrl->setParameter($this, "sort_by", "name");
		$this->ctrl->setParameter($this, "sort_order", $_GET["sort_order"]);
		$this->ctrl->setParameter($this, "offset", $_GET["offset"]);
		$this->tpl->setVariable("FORMACTION",
			$this->ctrl->getFormAction($this));

		// create table
		include_once "./Services/Table/classes/class.ilTableGUI.php";
		$tbl = new ilTableGUI();

		// title & header columns
		$tbl->setTitle($this->lng->txt("search_result"),"icon_".$this->object->getType().".gif",$this->lng->txt("obj_".$this->object->getType()));
		$tbl->setHelp("tbl_help.php","icon_help.gif",$this->lng->txt("help"));
		
		foreach ($this->data["cols"] as $val)
		{
			$header_names[] = $this->lng->txt($val);
		}
		
		$tbl->setHeaderNames($header_names);

		$header_params = $this->ctrl->getParameterArray($this, "searchUser");
		$header_params["search_string"] = urlencode($_POST["search_string"]);
		$header_params["search_fields"] = urlencode(implode(",",$_POST["search_fields"]));

		$tbl->setHeaderVars($this->data["cols"],$header_params);
		$tbl->setColumnWidth(array("","25%","25$%","25%","25%"));

		// control
        $tbl->enable("hits");
		$tbl->setOrderColumn($_GET["sort_by"]);
		$tbl->setOrderDirection($_GET["sort_order"]);
		$tbl->setLimit($_GET["limit"]);
		$tbl->setOffset($_GET["offset"]);
		$tbl->setMaxCount($this->maxcount);

		$this->tpl->setVariable("COLUMN_COUNTS",count($this->data["cols"]));	

		// footer
		$tbl->setFooter("tblfooter",$this->lng->txt("previous"),$this->lng->txt("next"));

		// render table
		$tbl->render();

		if (is_array($this->data["data"][0]))
		{
			//table cell
			for ($i=0; $i < count($this->data["data"]); $i++)
			{
				$data = $this->data["data"][$i];
				$ctrl = $this->data["ctrl"][$i];

				// dirty workaround to have ids for function showActions (checkbox toggle option)
				$this->ids[] = $ctrl["obj_id"];
					
				// color changing
				$css_row = ilUtil::switchColor($i+1,"tblrow1","tblrow2");

				$this->tpl->setCurrentBlock("checkbox");
				$this->tpl->setVariable("CHECKBOX_ID", $ctrl["obj_id"]);
				//$this->tpl->setVariable("CHECKED", $checked);
				$this->tpl->setVariable("CSS_ROW", $css_row);
				$this->tpl->parseCurrentBlock();

				$this->tpl->setCurrentBlock("table_cell");
				$this->tpl->setVariable("CELLSTYLE", "tblrow1");
				$this->tpl->parseCurrentBlock();

				foreach ($data as $key => $val)
				{
					//build link
					$this->ctrl->setParameterByClass("ilobjusergui", "ref_id", "7");
					$this->ctrl->setParameterByClass("ilobjusergui", "obj_id", $ctrl["obj_id"]);
					$link = $this->ctrl->getLinkTargetByClass("ilobjusergui", "view");

					if ($key == "login")
					{
						$this->tpl->setCurrentBlock("begin_link");
						$this->tpl->setVariable("LINK_TARGET", $link);
						$this->tpl->parseCurrentBlock();
						$this->tpl->touchBlock("end_link");
					}

					$this->tpl->setCurrentBlock("text");
					$this->tpl->setVariable("TEXT_CONTENT", $val);
					$this->tpl->parseCurrentBlock();
					$this->tpl->setCurrentBlock("table_cell");
					$this->tpl->parseCurrentBlock();
				} //foreach

				$this->tpl->setCurrentBlock("tbl_content");
				$this->tpl->setVariable("CSS_ROW", $css_row);
				$this->tpl->parseCurrentBlock();
			} //for
			
			$this->showActions(true);
		}
	}


	/**
	* display form for user import
	*/
	function importUserFormObject ()
	{
		global $tpl;
		
		// Blind out tabs for local user import
		if($this->ctrl->getTargetScript() == 'repository.php')
		{
			$this->tabs_gui->clearTargets();
		}

		$this->initUserImportForm();
		$tpl->setContent($this->form->getHTML());

		//$this->tpl->addBlockfile("ADM_CONTENT", "adm_content", "tpl.usr_import_form.html");
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
		if($this->ctrl->getTargetScript() == 'repository.php')
		{
			$this->tabs_gui->clearTargets();
		}

		$this->initUserImportForm();
		if ($this->form->checkInput())
		{
			include_once './Services/AccessControl/classes/class.ilObjRole.php';
			include_once './Services/User/classes/class.ilUserImportParser.php';
			
			global $rbacreview, $rbacsystem, $tree, $lng;
			
	
			$this->tpl->addBlockfile("ADM_CONTENT", "adm_content", "tpl.usr_import_roles.html");
	
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
									$loc_roles[] = array('obj_id'=>$mid);
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
					$rolf = $rbacreview->getFoldersAssignedToRole($loc_role["obj_id"],true);
	
					// only process role folders that are not set to status "deleted" 
					// and for which the user has write permissions.
					// We also don't show the roles which are in the ROLE_FOLDER_ID folder.
					// (The ROLE_FOLDER_ID folder contains the global roles).
					if (!$rbacreview->isDeleted($rolf[0])
					&& $rbacsystem->checkAccess('write',$tree->getParentId($rolf[0]))
					&& $rolf[0] != ROLE_FOLDER_ID
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
							$path = "<b>Rolefolder ".$rolf[0]." not found in tree! (Role ".$loc_role["obj_id"].")</b>";
						}
						$roleMailboxAddress = $rbacreview->getRoleMailboxAddress($loc_role['obj_id']);
						$l_roles[$loc_role['obj_id']] = $roleMailboxAddress.', '.$path;
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
		if($this->ctrl->getTargetScript() == 'repository.php')
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
							|| ! $rbacsystem->checkAccess('write',$tree->getParentId($rolf[0])))
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

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.usr_applied_users.html");
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


		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.usr_edit_applied_users.html");
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
		$this->tpl->setVariable("TYPE_IMG",ilUtil::getImagePath("icon_usr_b.gif"));
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
		$tpl->setVariable("IMG_ARROW", ilUtil::getImagePath("arrow_downright.gif"));
		$tpl->setVariable("ALT_ARROW", $this->lng->txt("actions"));
		$tpl->parseCurrentBlock();



		$tbl->setTitle($this->lng->txt("time_limit_applied_users"),"icon_usr_b.gif",$this->lng->txt("users"));
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


	// Functions for user defined fields
	function listUserDefinedFieldsObject()
	{
		global $lng;
		
		$lng->loadLanguageModule("administration");
		
		unset($_SESSION['select_num_values']);
		unset($_SESSION['num_values']);

		include_once './Services/User/classes/class.ilUserDefinedFields.php';	

		$this->setSubTabs('settings');
		$this->tabs_gui->setTabActive('global_settings');
		$this->tabs_gui->setSubTabActive('user_defined_fields');

		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.usrf_list_user_defined.html');
		$this->tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		
		if ($this->confirm_change == 1)
		{
			$this->tpl->setVariable("CONFIRM_MESSAGE", $this->lng->txt("confirm_message_course_export"));
			$this->tpl->setVariable("BUTTON_CONFIRM", $this->lng->txt("confirm"));	
			$this->tpl->setVariable("BUTTON_CANCEL", $this->lng->txt("cancel"));						
		}		
		
		$this->tpl->setVariable("TXT_OVERVIEW",$this->lng->txt('user_defined_list'));		

		$this->tpl->setVariable("BTN_ADD",$this->lng->txt('add_user_defined_field'));

		$user_field_definitions =& ilUserDefinedFields::_getInstance();

		if(!count($definitions = $user_field_definitions->getDefinitions()))
		{
			$this->tpl->setCurrentBlock("no_content");
			$this->tpl->setVariable("NO_FIELDS",$this->lng->txt('no_user_defined_fields_defined'));
			$this->tpl->parseCurrentBlock();
		}
		else
		{
			$this->tpl->setVariable("BTN_SAVE",$this->lng->txt('save'));
		}
		
		foreach($definitions as $field_id => $definition)
		{
			$this->tpl->setCurrentBlock("user_defined");			
			$this->tpl->setVariable("TXT_VISIBLE", $this->lng->txt("visible"));
			$this->tpl->setVariable("TXT_CHANGE", $this->lng->txt("changeable"));
			$this->tpl->setVariable("TXT_REQUIRED", $this->lng->txt("required_field"));
			$this->tpl->setVariable("TXT_SEARCHABLE", $this->lng->txt("header_searchable"));			
			$this->tpl->setVariable("TXT_EXPORT", $this->lng->txt("export"));
			$this->tpl->setVariable("TXT_COURSE_EXPORT", $lng->txt("course_export"));
			
			$this->tpl->setVariable("NAME",$definition['field_name']);
			
			if ($_POST["cmd"]["updateFields"] != "")
			{
				$definition['visible'] = isset($_POST['def'][$field_id]['visible']) ? 1 : 0;
				$definition['changeable'] = isset($_POST['def'][$field_id]['changeable']) ? 1 : 0;
				$definition['required'] = isset($_POST['def'][$field_id]['required']) ? 1 : 0;
				$definition['searchable'] = isset($_POST['def'][$field_id]['searchable']) ? 1 : 0;
				$definition['export'] = isset($_POST['def'][$field_id]['export']) ? 1 : 0;
				$definition['course_export'] = isset($_POST['def'][$field_id]['course_export']) ? 1 : 0;
			}			
			
			$this->tpl->setVariable("VISIBLE",ilUtil::formCheckbox($definition['visible'],"def[$field_id][visible]",1));
			$this->tpl->setVariable("CHANGE",ilUtil::formCheckbox($definition['changeable'],"def[$field_id][changeable]",1));
			$this->tpl->setVariable("REQUIRED",ilUtil::formCheckbox($definition['required'],"def[$field_id][required]",1));
			$this->tpl->setVariable("SEARCHABLE",ilUtil::formCheckbox($definition['searchable'],"def[$field_id][searchable]",1));
			$this->tpl->setVariable("EXPORT",ilUtil::formCheckbox($definition['export'],"def[$field_id][export]",1));
			$this->tpl->setVariable("COURSE_EXPORT",ilUtil::formCheckbox($definition['course_export'],"def[$field_id][course_export]",1));

			$this->ctrl->setParameter($this,'field_id',$field_id);

			// Show edit link (depends on type)
			switch($definition['field_type'])
			{
				case UDF_TYPE_TEXT:
					$this->tpl->setVariable("EDIT_LINK",$this->ctrl->getLinkTarget($this,'editTextField'));
					break;

				case UDF_TYPE_SELECT:
					$this->tpl->setVariable("EDIT_LINK",$this->ctrl->getLinkTarget($this,'editSelectField'));
					break;
			}
			$this->tpl->setVariable("EDIT",$this->lng->txt('edit'));		

			$this->tpl->setVariable("DELETE_LINK",$this->ctrl->getLinkTarget($this,'askDeleteField'));
			$this->tpl->setVariable("DELETE",$this->lng->txt('delete'));
			$this->tpl->parseCurrentBlock();			
		}
	}
	
	function editTextFieldObject()
	{
		include_once './Services/User/classes/class.ilUserDefinedFields.php';
		$udf =& ilUserDefinedFields::_getInstance();
		$definition = $udf->getDefinition((int) $_GET['field_id']);

		// Save paremeter
		$this->ctrl->setParameter($this,'field_id',(int) $_GET['field_id']);

		$this->setSubTabs('settings');
		$this->tabs_gui->setTabActive('global_settings');
		$this->tabs_gui->setSubTabActive('user_defined_fields');
		
		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.usrf_update_text_field.html');
		
		$this->tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$this->tpl->setVariable("TXT_SELECT_TYPE",$this->lng->txt('udf_update_text_field'));
		$this->tpl->setVariable("TXT_FIELD_NAME",$this->lng->txt('field_name'));
		
		$this->tpl->setVariable("FIELD_NAME",$definition['field_name']);
		
		$this->tpl->setVariable("BTN_PREVIOUS",$this->lng->txt('cancel'));
		$this->tpl->setVariable("BTN_ADD",$this->lng->txt('update'));

		return true;
	}

	function updateTextFieldObject()
	{
		include_once './Services/User/classes/class.ilUserDefinedFields.php';
		
		$udf =& ilUserDefinedFields::_getInstance();
		$definition = $udf->getDefinition((int) $_GET['field_id']);

		$udf->setFieldName(ilUtil::stripslashes($_POST['field_name']));
		$udf->setFieldType($definition['field_type']);
		$udf->setFieldValues($definition['field_values']);
		$udf->enableVisible($definition['visible']);
		$udf->enableChangeable($definition['changeable']);
		$udf->enableRequired($definition['required']);
		$udf->enableSearchable($definition['searchable']);
		$udf->enableExport($definition['export']);
		$udf->enableCourseExport($definition['course_export']);

		$udf->update($definition['field_id']);

		ilUtil::sendSuccess($this->lng->txt('udf_update_success'));
		
		$this->listUserDefinedFieldsObject();
	}

	function editSelectFieldObject()
	{
		include_once './Services/User/classes/class.ilUserDefinedFields.php';

		$_SESSION['select_num_values'] = $_SESSION['select_num_values'] ? $_SESSION['select_num_values'] : 0;

		$udf =& ilUserDefinedFields::_getInstance();
		$definition = $udf->getDefinition((int) $_GET['field_id']);

		// Save paremeter
		$this->ctrl->setParameter($this,'field_id',(int) $_GET['field_id']);

		$this->setSubTabs('settings');
		$this->tabs_gui->setTabActive('global_settings');
		$this->tabs_gui->setSubTabActive('user_defined_fields');
		
		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.usrf_update_select_field.html');

		//$this->tpl->setVariable("TXT_WARNING",$this->lng->txt('udf_warn_delete'));
		$this->tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$this->tpl->setVariable("TXT_SELECT_TYPE",$this->lng->txt('udf_update_select_field'));
		$this->tpl->setVariable("TXT_FIELD_NAME",$this->lng->txt('field_name'));
		
		$this->tpl->setVariable("FIELD_NAME_VALUE",$definition['field_name']);

		// OLD VALUES
		$counter = 0;
		foreach($definition['field_values'] as $value)
		{
			$this->tpl->setCurrentBlock("values");
			$this->tpl->setVariable("COUNTER",$counter++);
			$this->tpl->setVariable("TXT_VALUES",$this->lng->txt('udf_value').' '.($counter));
			$this->tpl->setVariable("FIELD_NAME",$value);

			$this->ctrl->setParameter($this,'value_id',$counter-1);
			$this->tpl->setVariable("DELETE_LINK",$this->ctrl->getLinkTarget($this,'deleteValue'));
			$this->tpl->setVariable("DELETE",$this->lng->txt('delete'));
			$this->tpl->parseCurrentBlock();
		}

		// new values
		for($i = 0; $i < $_SESSION['select_num_values'];$i++)
		{
			$this->tpl->setCurrentBlock("new_values");
			$this->tpl->setVariable("NEW_COUNTER",$counter++);
			$this->tpl->setVariable("TXT_NEW_VALUES",$this->lng->txt('udf_value').' '.($counter));
			$this->tpl->setVariable("NEW_FIELD_NAME",$_POST['field_values'][$counter-1]);
			$this->tpl->parseCurrentBlock();
		}
		$this->tpl->setVariable("BTN_NEW_VALUE",$this->lng->txt('btn_new_value'));
		$this->tpl->setVariable("BTN_PREVIOUS",$this->lng->txt('cancel'));
		$this->tpl->setVariable("BTN_ADD",$this->lng->txt('save'));

		return true;
	}

	function updateSelectFieldObject()
	{
		include_once './Services/User/classes/class.ilUserDefinedFields.php';

		
		$_POST['field_values'] = is_array($_POST['field_values']) ? $_POST['field_values'] : array();

		$udf =& ilUserDefinedFields::_getInstance();
		$definition = $udf->getDefinition((int) $_GET['field_id']);


		$udf->setFieldName(ilUtil::stripslashes($_POST['field_name']));
		$udf->setFieldValues(array_merge($definition['field_values'],$_POST['field_values']));
		$udf->setFieldType($definition['field_type']);
		$udf->enableVisible($definition['visible']);
		$udf->enableChangeable($definition['changeable']);
		$udf->enableRequired($definition['required']);
		$udf->enableSearchable($definition['searchable']);
		$udf->enableExport($definition['export']);
		$udf->enableCourseExport($definition['course_export']);

		if($error = $udf->validateValues())
		{
			switch($error)
			{
				case UDF_DUPLICATE_VALUES:
					ilUtil::sendFailure($this->lng->txt('udf_duplicate_entries'));
					$this->editSelectFieldObject();
					return false;

				case UDF_NO_VALUES:
					ilUtil::sendFailure($this->lng->txt('udf_no_entries'));
					$this->editSelectFieldObject();
					return false;
			}
		}

		$udf->update($definition['field_id']);
		ilUtil::sendSuccess($this->lng->txt('udf_update_success'));
		
		unset($_SESSION['select_num_values']);
		$this->editSelectFieldObject();
	}


	function deleteValueObject()
	{
		include_once './Services/User/classes/class.ilUserDefinedFields.php';
		
		$udf =& ilUserDefinedFields::_getInstance();
		$udf->deleteValue((int) $_GET['field_id'],(int) $_GET['value_id']);

		ilUtil::sendSuccess($this->lng->txt('udf_value_deleted'));
		$this->editSelectFieldObject();

		return true;
	}

	function addSelectValueObject()
	{
		$_SESSION['select_num_values'] += 1;
		$this->editSelectFieldObject();
		return true;
	}		
		
	function askDeleteFieldObject()
	{
		include_once './Services/User/classes/class.ilUserDefinedFields.php';

		ilUtil::sendQuestion($this->lng->txt('udf_delete_sure'));

		$this->setSubTabs('settings');
		$this->tabs_gui->setTabActive('global_settings');
		$this->tabs_gui->setSubTabActive('user_defined_fields');

		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.usrf_ask_delete.html');
		
		$this->ctrl->setParameter($this,'field_id',(int) $_GET['field_id']);
		$this->tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$this->tpl->setVariable("TXT_DELETE_FIELD",$this->lng->txt('udf_delete_header'));
		$this->tpl->setVariable("TXT_FIELD_NAME",$this->lng->txt('field_name'));

		$user_field_definitions =& ilUserDefinedFields::_getInstance();
		$definition = $user_field_definitions->getDefinition((int) $_GET['field_id']);

		$this->tpl->setVariable("FIELD_NAME",$definition['field_name']);
		$this->tpl->setVariable("BTN_DELETE",$this->lng->txt('delete'));
		$this->tpl->setVariable("BTN_CANCEL",$this->lng->txt('cancel'));

		return true;
	}
		
	function deleteFieldObject()
	{
		include_once './Services/User/classes/class.ilUserDefinedFields.php';
		$user_field_definitions =& ilUserDefinedFields::_getInstance();

		$user_field_definitions->delete((int) $_GET['field_id']);

		ilUtil::sendSuccess($this->lng->txt('udf_field_deleted'));
		$this->listUserDefinedFieldsObject();

		return true;
	}
	
	function confirmSavedUDFObject()
	{
		$this->updateFieldsObject("save");
	}

	function updateFieldsObject($action = "")
	{
		include_once './Services/User/classes/class.ilUserDefinedFields.php';		
		
		$user_field_definitions =& ilUserDefinedFields::_getInstance();
		$a_fields = $user_field_definitions->getDefinitions();
			
		// check if a course export state of any user defined field has been added
		if ($action != 'save')
		{
			include_once 'Services/PrivacySecurity/classes/class.ilPrivacySettings.php';
			$privacy = ilPrivacySettings::_getInstance();
			if ($privacy->enabledExport() == true && $privacy->confirmationRequired() == true)
			{ 
				foreach($a_fields as $field_id => $definition)
				{
					if ($definition['course_export'] == 0 && (int) $_POST['def'][$field_id]['course_export'] == 1)
					{	
						$this->confirm_change = 1;
						$this->listUserDefinedFieldsObject();
						return true;
					}
				}
			}
		}
		
		// Reset user confirmation
		if ($action == 'save')
		{
			include_once('Modules/Course/classes/class.ilCourseAgreement.php');
			ilCourseAgreement::_reset();	
		}

		foreach($a_fields as $field_id => $definition)
		{			
			$user_field_definitions->setFieldName($definition['field_name']);
			$user_field_definitions->setFieldType($definition['field_type']);
			$user_field_definitions->setFieldValues($definition['field_values']);
			$user_field_definitions->enableVisible((int) $_POST['def'][$field_id]['visible']);
			$user_field_definitions->enableChangeable((int) $_POST['def'][$field_id]['changeable']);
			$user_field_definitions->enableRequired((int) $_POST['def'][$field_id]['required']);
			$user_field_definitions->enableSearchable((int) $_POST['def'][$field_id]['searchable']);
			$user_field_definitions->enableExport((int) $_POST['def'][$field_id]['export']);
			$user_field_definitions->enableCourseExport((int) $_POST['def'][$field_id]['course_export']);
			$user_field_definitions->update($field_id);
		}

		ilUtil::sendSuccess($this->lng->txt('settings_saved'));
		$this->listUserDefinedFieldsObject();
		
		return true;
	}

	function chooseFieldTypeObject()
	{
		include_once './Services/User/classes/class.ilUserDefinedFields.php';

		// number of values defaults to 3
		$_SESSION['num_values'] = 3;

		$this->setSubTabs('settings');
		$this->tabs_gui->setTabActive('global_settings');
		$this->tabs_gui->setSubTabActive('user_defined_fields');

		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.usrf_select_field_type.html');
		$this->tpl->setVariable('FORMACTION',$this->ctrl->getFormAction($this,'chooseDefinitions'));
		$this->tpl->setVariable("TXT_SELECT_TYPE",$this->lng->txt('add_new_user_defined_field'));
		$this->tpl->setVariable("FIELD_TYPE",$this->lng->txt('field_type'));
		$this->tpl->setVariable("TYPE_TEXT",ilUtil::formRadioButton(1,'field_type',UDF_TYPE_TEXT));
		$this->tpl->setVariable("TYPE_SELECT",ilUtil::formRadioButton(0,'field_type',UDF_TYPE_SELECT));
		$this->tpl->setVariable("TXT_TEXT",$this->lng->txt('udf_type_text'));
		$this->tpl->setVariable("TXT_SELECT",$this->lng->txt('udf_type_select'));
		$this->tpl->setVariable("BTN_MORE",$this->lng->txt('btn_next'));
		$this->tpl->setVariable("BTN_CANCEL",$this->lng->txt('cancel'));

		return true;
	}

	function chooseDefinitionsObject()
	{
		include_once './Services/User/classes/class.ilUserDefinedFields.php';

		$this->setSubTabs('settings');
		$this->tabs_gui->setTabActive('global_settings');
		$this->tabs_gui->setSubTabActive('user_defined_fields');

		switch($field_type = (int) $_REQUEST['field_type'])
		{
			case UDF_TYPE_TEXT:
				$this->__showTextTable();
				break;

			case UDF_TYPE_SELECT:
				$this->__showSelectTable();
				break;
		}
	}

	function __showTextTable()
	{		
		global $lng;
		
		$lng->loadLanguageModule("administration");
		$lng->loadLanguageModule("ps");
				
		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.usrf_edit_text_field.html');
				
		include_once("Modules/Course/classes/class.ilCourseAgreement.php");
	 	if (ilCourseAgreement::_hasAgreements())
	 	{
			$this->tpl->setCurrentBlock('warning_modify');
			$this->tpl->setVariable('TXT_WARNING', $lng->txt('ps_warning_modify'));
			$this->tpl->parseCurrentBlock();
	 	}

		// Save field_type
		$this->ctrl->setParameter($this,'field_type',(int) $_REQUEST['field_type']);
		$this->tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		
		$this->tpl->setVariable("BTN_ADD",$this->lng->txt('udf_add_field'));
		$this->tpl->setVariable("BTN_PREVIOUS",$this->lng->txt('btn_previous'));
		$this->tpl->setVariable("TXT_SELECT_TYPE",$this->lng->txt('add_new_user_defined_text_field'));
		$this->tpl->setVariable("TXT_FIELD_NAME",$this->lng->txt('field_name'));
		$this->tpl->setVariable("FIELD_NAME",$_POST['field_name']);

		$this->tpl->setVariable("TXT_VISIBLE", $this->lng->txt("visible"));
		$this->tpl->setVariable("TXT_CHANGE", $this->lng->txt("changeable"));
		$this->tpl->setVariable("TXT_REQUIRED", $this->lng->txt("required_field"));
		$this->tpl->setVariable("TXT_SEARCHABLE", $this->lng->txt("header_searchable"));
		$this->tpl->setVariable("TXT_EXPORT", $this->lng->txt("export"));
		$this->tpl->setVariable("TXT_COURSE_EXPORT", $lng->txt("course_export"));

		$this->tpl->setVariable("VISIBLE",ilUtil::formCheckbox($_POST['def']['visible'],"def[visible]",1));
		$this->tpl->setVariable("CHANGE",ilUtil::formCheckbox($_POST['def']['changeable'],"def[changeable]",1));
		$this->tpl->setVariable("REQUIRED",ilUtil::formCheckbox($_POST['def']['required'],"def[required]",1));
		$this->tpl->setVariable("SEARCHABLE",ilUtil::formCheckbox($_POST['def']['searchable'],"def[searchable]",1));
		$this->tpl->setVariable("EXPORT",ilUtil::formCheckbox($_POST['def']['export'],"def[export]",1));
		$this->tpl->setVariable("COURSE_EXPORT",ilUtil::formCheckbox($_POST['def']['course_export'],"def[course_export]",1));

		return true;
	}

	function __showSelectTable()
	{
		global $lng;
		
		$lng->loadLanguageModule("administration");
		$lng->loadLanguageModule("ps");		
				
		include_once("Modules/Course/classes/class.ilCourseAgreement.php");
	 	if (ilCourseAgreement::_hasAgreements())
	 	{
			$this->tpl->setCurrentBlock('warning_modify');
			$this->tpl->setVariable('TXT_WARNING', $lng->txt('ps_warning_modify'));
			$this->tpl->parseCurrentBlock();
	 	}
		
		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.usrf_edit_select_field.html');
		
		// Save field_type
		$this->ctrl->setParameter($this,'field_type',(int) $_REQUEST['field_type']);
		$this->tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$this->tpl->setVariable("BTN_ADD",$this->lng->txt('udf_add_field'));
		$this->tpl->setVariable("BTN_PREVIOUS",$this->lng->txt('btn_previous'));
		$this->tpl->setVariable("TXT_SELECT_TYPE",$this->lng->txt('add_new_user_defined_select_field'));
		$this->tpl->setVariable("TXT_FIELD_NAME",$this->lng->txt('field_name'));
		$this->tpl->setVariable("FIELD_NAME_VALUE",$_POST['field_name']);
		$this->tpl->setVariable("TXT_EXPORT", $this->lng->txt("export"));
		$this->tpl->setVariable("TXT_COURSE_EXPORT", $lng->txt("course_export"));

		$this->tpl->setVariable("TXT_VISIBLE", $this->lng->txt("visible"));
		$this->tpl->setVariable("TXT_CHANGE", $this->lng->txt("changeable"));
		$this->tpl->setVariable("TXT_REQUIRED", $this->lng->txt("required_field"));
		$this->tpl->setVariable("TXT_SEARCHABLE", $this->lng->txt("header_searchable"));

		$this->tpl->setVariable("VISIBLE",ilUtil::formCheckbox($_POST['def']['visible'],"def[visible]",1));
		$this->tpl->setVariable("CHANGE",ilUtil::formCheckbox($_POST['def']['changeable'],"def[changeable]",1));
		$this->tpl->setVariable("REQUIRED",ilUtil::formCheckbox($_POST['def']['required'],"def[required]",1));
		$this->tpl->setVariable("SEARCHABLE",ilUtil::formCheckbox($_POST['def']['searchable'],"def[searchable]",1));
		$this->tpl->setVariable("EXPORT",ilUtil::formCheckbox($_POST['def']['export'],"def[export]",1));
		$this->tpl->setVariable("COURSE_EXPORT",ilUtil::formCheckbox($_POST['def']['course_export'],"def[course_export]",1));

		$this->tpl->setVariable("BTN_NEW_VALUE",$this->lng->txt('btn_new_value'));

		for($i = 0; $i < $_SESSION['num_values']; $i++)
		{
			$this->tpl->setCurrentBlock("values");
			$this->tpl->setVariable("TXT_VALUES",$this->lng->txt('udf_value').' '.($i+1));
			$this->tpl->setVariable("COUNTER",$i);
			$this->tpl->setVariable("FIELD_NAME",$_POST['field_values'][$i]);
			$this->tpl->parseCurrentBlock();
		}
			

	}

	function addValueObject()
	{
		$_SESSION['num_values'] += 1;
		$this->chooseDefinitionsObject();
		return true;
	}

	function saveFieldObject()
	{
		include_once './Services/User/classes/class.ilUserDefinedFields.php';
		$user_field_definitions =& ilUserDefinedFields::_getInstance();

		if (!strlen($_POST['field_name']))
		{
			ilUtil::sendFailure($this->lng->txt('udf_no_name_given'));
			$this->chooseDefinitionsObject();
			return false;
		}

		if($user_field_definitions->nameExists($_POST['field_name']))
		{
			ilUtil::sendFailure($this->lng->txt('udf_name_already_exists'));
			$this->chooseDefinitionsObject();
			return false;
		}

		// Text fields
		if($_REQUEST['field_type'] == UDF_TYPE_TEXT)
		{
			$user_field_definitions->setFieldType(UDF_TYPE_TEXT);
		}
		if($_REQUEST['field_type'] == UDF_TYPE_SELECT)
		{
			$user_field_definitions->setFieldType(UDF_TYPE_SELECT);
			$user_field_definitions->setFieldValues($_POST['field_values']);
			if($error = $user_field_definitions->validateValues())
			{
				switch($error)
				{
					case UDF_DUPLICATE_VALUES:
						ilUtil::sendFailure($this->lng->txt('udf_duplicate_entries'));
						$this->chooseDefinitionsObject();
						return false;

					case UDF_NO_VALUES:
						ilUtil::sendFailure($this->lng->txt('udf_no_entries'));
						$this->chooseDefinitionsObject();
						return false;
				}
			}
		}
				
		$user_field_definitions->setFieldName($_POST['field_name']);
		$user_field_definitions->enableVisible($_POST['def']['visible']);
		$user_field_definitions->enableChangeable($_POST['def']['changeable']);
		$user_field_definitions->enableRequired($_POST['def']['required']);
		$user_field_definitions->enableSearchable($_POST['def']['searchable']);		
		$user_field_definitions->enableExport($_POST['def']['export']);
		$user_field_definitions->enableCourseExport($_POST['def']['course_export']);
		$user_field_definitions->add();
		
		if ((int) $_POST['def']['course_export'] == 1)
		{
			include_once('Modules/Course/classes/class.ilCourseAgreement.php');
			ilCourseAgreement::_reset();			
		}

		ilUtil::sendSuccess($this->lng->txt('udf_added_field'));
		$this->listUserDefinedFieldsObject();

		return true;
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
		include_once 'Services/Search/classes/class.ilUserSearchOptions.php';

		global $ilias;		
		global $lng;	
		
		$lng->loadLanguageModule("administration");
		
		$this->getTemplateFile("settings","usr");
		$this->setSubTabs('settings');
		
		$profile_fields =& $this->object->getProfileFields();
		
		$profile_fields [] = "preferences";
		// For the following fields, the required state can not be changed.
		// key = field, value = 1 (field is required), 0 (field is not required)
		$fixed_required_fields = array(
			"firstname" => 1,
			"lastname" => 1,
			"upload" => 0,
			"password" => 0,
			"language" => 0,
			"skin_style" => 0,
			"hits_per_page" => 0,
			"show_users_online" => 0,
			"instant_messengers" => 0,
			"hide_own_online_status" => 0,
			"preferences" => 0,
		);
		
		$no_export_fields = array(
			"hits_per_page",
			"show_users_online",
			"hide_own_online_status"
		);
		
		// fields which won't be changeable
		$no_changeable_fields = array(
			#"preferences",
		);
		
		// fields which won't be visible
		$no_visible_fields = array(
			#"preferences",
		);
		
		// disable visible checkbox
		$disabled_visible_fields= array(
			"preferences",
		);
		
		//disable changeable checkbox
		$disabled_changeable_fields= array(
			"preferences",
		);
		
		// Settings for the course export state
		// key = field, value = 2 (field is disabled but checked), value = 1 (field is changeable), 0 (checkbox is not shown)
		$course_export_fields = array(
			"gender" => 1, "firstname" => 2,
			"lastname" => 2, "title" => 1,
			"upload" => 0, "password" => 0,
			"institution" => 1, "department" => 1,
			"street" => 1, "zipcode" => 1,
			"city" => 1, "country" => 1,
			"phone_office" => 1, "phone_home" => 1,
			"phone_mobile" => 1, "fax" => 1,
			"email" => 1, "hobby" => 1,
			"referral_comment" => 0, "matriculation" => 1,
			"language" => 0, "skin_style" => 0,
			"hits_per_page" => 0, "show_users_online" => 0,
			"instant_messengers" => 0,
			"preferences" => 0,
		);
		foreach ($profile_fields as $field)
		{			
			$this->tpl->setCurrentBlock("profile_settings");			
			
			$txt = strcasecmp($field,"upload") == 0 ? "personal_picture" : $field;
			$this->tpl->setVariable("TXT_PROFILE_DATA", $this->lng->txt($txt));
			
			$this->tpl->setVariable("PROFILE_OPTION_ENABLED", "enabled_" . $field);
			$this->tpl->setVariable("PROFILE_OPTION_VISIBLE", "visible_" . $field);
			$this->tpl->setVariable("PROFILE_OPTION_REQUIRED", "required_" . $field);
			
			// Headers which are always displayed in a row
			$this->tpl->setVariable("HEADER_PROFILE_DATA", $this->lng->txt("usr_settings_header_profile_profile"));
			$this->tpl->setVariable("HEADER_ENABLED", $this->lng->txt("changeable"));
			$this->tpl->setVariable("HEADER_VISIBLE", $this->lng->txt("visible"));			
			$this->tpl->setVariable("HEADER_REQUIRED", $this->lng->txt("required_field"));
			$this->tpl->setVariable("HEADER_EXPORT", $this->lng->txt("export"));						

			// Check searchable
			if(ilUserSearchOptions::_isSearchable($field))
			{
				$this->tpl->setVariable("HEADER_SEARCH",$this->lng->txt('header_searchable'));
				$checked = ($_POST["cmd"]["saveGlobalUserSettings"] && $this->confirm_change == 1) ? 
					$_POST["cbh"][$field]["searchable"] : ilUserSearchOptions::_isEnabled($field);				
				$this->tpl->setVariable("CHECK_SEARCH",ilUtil::formCheckbox($checked,
																			"cbh[$field][searchable]",
																			1));
			}

			// BEGIN Enable field in Personal Profile
			if (!in_array($field, $no_changeable_fields) &&
				($ilias->getSetting("usr_settings_disable_".$field) != "1" || 
			   ($this->confirm_change == 1 && ! $_POST["chb"]["enabled_" . $field])))
			{
				$this->tpl->setVariable("CHECKED_ENABLED", " checked=\"checked\"");
			}
			
			if (in_array($field, $disabled_changeable_fields))
			{
				$this->tpl->setVariable("DISABLE_ENABLED", " disabled=\"disabled\"");
			}
			
			// END Enable field in Personal Profile
			// BEGIN Show field in Personal Profile
			if (!in_array($field, $no_visible_fields) && 			
			 	($ilias->getSetting("usr_settings_hide_".$field) != "1" || 
			    ($this->confirm_change == 1 && ! $_POST["chb"]["visible_" . $field])))
			{
				$this->tpl->setVariable("CHECKED_VISIBLE", " checked=\"checked\"");
			}

			if (in_array($field, $disabled_visible_fields))
			{
				$this->tpl->setVariable("DISABLE_VISIBLE", " disabled=\"disabled\"");
			}
			
			// END Show field in Personal Profile
			if (!in_array($field, $no_export_fields))
			{
				$this->tpl->setVariable("PROFILE_OPTION_EXPORT", "export_" . $field);
				// BEGIN Export field of Personal Profile
				if ($ilias->getSetting("usr_settings_export_".$field) == "1" || 
				   ($this->confirm_change == 1 && $_POST["chb"]["export_" . $field] == "1"))
				{
					$this->tpl->setVariable("CHECKED_EXPORT", " checked=\"checked\"");
				}
				// END Export field of Personal Profile
			}
			// BEGIN Require field in Personal Profile
			$is_fixed = array_key_exists($field, $fixed_required_fields);
			if ($is_fixed && $fixed_required_fields[$field] || ! $is_fixed && 
			   ($ilias->getSetting("require_".$field) == "1" || 
			   ($this->confirm_change == 1 && $_POST["chb"]["required_" . $field] == "1")))
			{
				$this->tpl->setVariable("CHECKED_REQUIRED", " checked=\"checked\"");
			}
			if ($is_fixed)
			{
				$this->tpl->setVariable("DISABLE_REQUIRED", " disabled=\"disabled\"");
			}
			// END Require field in Personal Profile
			
			// BEGIN Course Export field in Personal Profile
			if (is_array($course_export_fields))
			{
				if (in_array($field, $course_export_fields) && $course_export_fields[$field] != 0)
				{					
					$this->tpl->setVariable("HEADER_COURSE_EXPORT", $lng->txt("course_export"));
					$this->tpl->setVariable("PROFILE_OPTION_COURSE_EXPORT", "course_export_" . $field);
					
					if ($course_export_fields[$field] == 2)
					{
						$this->tpl->setVariable("CHECKED_COURSE_EXPORT", " checked=\"checked\"");
						$this->tpl->setVariable("DISABLE_COURSE_EXPORT", " disabled=\"disabled\"");					
					}
					else
					{						
						if ($ilias->getSetting("usr_settings_course_export_".$field) == "1"  || 
						   ($this->confirm_change == 1 && $_POST["chb"]["course_export_" . $field] == "1"))
						{
							$this->tpl->setVariable("CHECKED_COURSE_EXPORT", " checked=\"checked\"");
						}				
					}
				}	
			}
			// END Course Export field in Personal Profile			

			// BEGIN Default value for hits per pages field in Personal Profile
			if ($field == "hits_per_page")
			{								
				$this->tpl->setVariable("PROFILE_OPTION_DEFAULT_VALUE", "default_" . $field);
				$options = array(2,10,15,20,30,40,50,100,9999);
				$selected_option = $ilias->getSetting("hits_per_page");
				if ($selected_option == null) $selected_option = 10;
				$this->tpl->setCurrentBlock("default_value_option");
				foreach ($options as $option)
				{
					if ($option == $selected_option)
					{
						$this->tpl->setVariable("OPTION_SELECTED", " selected=\"selected\"");
					}

					$this->tpl->setVariable("OPTION_VALUE", $option);
					$this->tpl->setVariable("OPTION_TEXT", ($option == 9999) ? $this->lng->txt("no_limit") : $option);
					$this->tpl->parseCurrentBlock();
				}
				$this->tpl->setCurrentBlock("profile_settings");
				$this->tpl->setVariable("HEADER_DEFAULT_VALUE", $this->lng->txt("default_value"));
			}
			// END Default value for language field in Personal Profile

			// BEGIN Show Users Online
			else if ($field == "show_users_online")
			{
				$this->tpl->setVariable("PROFILE_OPTION_DEFAULT_VALUE", "default_" . $field);
				$options = array('y','associated','n');
				$selected_option = $ilias->getSetting("show_users_online");
				if ($selected_option == "") $selected_option = "y";
				$this->tpl->setCurrentBlock("default_value_option");
				foreach ($options as $option)
				{
					if ($option == $selected_option)
					{
						$this->tpl->setVariable("OPTION_SELECTED", " selected=\"selected\"");
					}

					$this->tpl->setVariable("OPTION_VALUE", $option);
					$this->tpl->setVariable("OPTION_TEXT", $this->lng->txt("users_online_show_short_".$option));
					$this->tpl->parseCurrentBlock();
				}
				$this->tpl->setCurrentBlock("profile_settings");
				$this->tpl->setVariable("HEADER_DEFAULT_VALUE", $this->lng->txt("default_value"));
			}
			// END Show Users Online

			$this->tpl->parseCurrentBlock();
		}
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
		$this->tpl->setVariable("TXT_HEADER_PROFILE", $this->lng->txt("usr_settings_header_profile"));
		$this->tpl->setVariable("TXT_EXPLANATION_PROFILE", $this->lng->txt("usr_settings_explanation_profile"));	
		$this->tpl->setVariable("TXT_SAVE", $this->lng->txt("save"));

		if ($this->confirm_change == 1)
		{
			$this->tpl->setVariable("CONFIRM_MESSAGE", $this->lng->txt("confirm_message_course_export"));
			$this->tpl->setVariable("BUTTON_CONFIRM", $this->lng->txt("confirm"));	
			$this->tpl->setVariable("BUTTON_CANCEL", $this->lng->txt("cancel"));						
		}		
	}
	
	function confirmSavedObject()
	{
		$this->saveGlobalUserSettingsObject("save");
	}
	
	function saveGlobalUserSettingsObject($action = "")
	{
		include_once 'Services/Search/classes/class.ilUserSearchOptions.php';
		include_once 'Services/PrivacySecurity/classes/class.ilPrivacySettings.php';

		global $ilias;
		
		$profile_fields =& $this->object->getProfileFields();
		// For the following fields, the required state can not be changed
		$fixed_required_fields = array(
			"firstname" => 1,
			"lastname" => 1,
			"upload" => 0,
			"password" => 0,
			"language" => 0,
			"skin_style" => 0,
			"hits_per_page" => 0,
			"show_users_online" => 0,
			"hide_own_online_status" => 0
		);
		
		// check if a course export state of any field has been added
		$privacy = ilPrivacySettings::_getInstance();
		if ($privacy->enabledExport() == true && 
			$privacy->confirmationRequired() == true && 
			$action != "save")
		{
			foreach ($profile_fields as $field)
			{			
				if (! $ilias->getSetting("usr_settings_course_export_" . $field) && $_POST["chb"]["course_export_" . $field] == "1")
				{
					$this->confirm_change = 1;
					$this->settingsObject();
					return;
				}			
			}			
		}
		// Reset user confirmation
		if($action == 'save')
		{
			include_once('Modules/Course/classes/class.ilCourseAgreement.php');
			ilCourseAgreement::_reset();	
		}

		foreach ($profile_fields as $field)
		{
			// Enable disable searchable
			if(ilUserSearchOptions::_isSearchable($field))
			{
				ilUserSearchOptions::_saveStatus($field,(bool) $_POST['cbh'][$field]['searchable']);
			}

			if (! $_POST["chb"]["visible_".$field])
			{
				$ilias->setSetting("usr_settings_hide_".$field, "1");
			}
			else
			{
				$ilias->deleteSetting("usr_settings_hide_".$field);
			}

			if (! $_POST["chb"]["enabled_" . $field])
			{
				$ilias->setSetting("usr_settings_disable_".$field, "1");
			}
			else
			{
				$ilias->deleteSetting("usr_settings_disable_".$field);
			}

			if ($_POST["chb"]["export_" . $field])
			{
				$ilias->setSetting("usr_settings_export_".$field, "1");
			}
			else
			{
				$ilias->deleteSetting("usr_settings_export_".$field);
			}
			
			if ($_POST["chb"]["course_export_" . $field])
			{
				$ilias->setSetting("usr_settings_course_export_".$field, "1");
			}
			else
			{
				$ilias->deleteSetting("usr_settings_course_export_".$field);
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

		if ($_POST["select"]["default_show_users_online"])
		{
			$ilias->setSetting("show_users_online",$_POST["select"]["default_show_users_online"]);
		}
		
		if ($_POST["chb"]["export_preferences"])
		{
			$ilias->setSetting("usr_settings_export_preferences",$_POST["chb"]["export_preferences"]);
		} else {
			$ilias->deleteSetting("usr_settings_export_preferences");
		}

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


		$export_dir = $this->object->getExportDirectory();
		ilUtil::deliverFile($export_dir."/".$_POST["file"][0],
			$_POST["file"][0]);
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

		// SAVE POST VALUES
		$_SESSION["ilExportFiles"] = $_POST["file"];

		$this->getTemplateFile("confirm_delete_export","usr");		

		ilUtil::sendQuestion($this->lng->txt("info_delete_sure"));

		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));

		// BEGIN TABLE HEADER
		$this->tpl->setCurrentBlock("table_header");
		$this->tpl->setVariable("TEXT",$this->lng->txt("objects"));
		$this->tpl->parseCurrentBlock();

		// BEGIN TABLE DATA
		$counter = 0;
		foreach($_POST["file"] as $file)
		{
				$this->tpl->setCurrentBlock("table_row");
				$this->tpl->setVariable("CSS_ROW",ilUtil::switchColor(++$counter,"tblrow1","tblrow2"));
				$this->tpl->setVariable("IMG_OBJ",ilUtil::getImagePath("icon_usrf.gif"));
				$this->tpl->setVariable("TEXT_CONTENT", $file);
				$this->tpl->parseCurrentBlock();
		}

		// cancel/confirm button
		$this->tpl->setVariable("IMG_ARROW",ilUtil::getImagePath("arrow_downright.gif"));
		$this->tpl->setVariable("ALT_ARROW", $this->lng->txt("actions"));
		$buttons = array( "cancelDeleteExportFile"  => $this->lng->txt("cancel"),
			"deleteExportFile"  => $this->lng->txt("confirm"));
		foreach ($buttons as $name => $value)
		{
			$this->tpl->setCurrentBlock("operation_btn");
			$this->tpl->setVariable("BTN_NAME",$name);
			$this->tpl->setVariable("BTN_VALUE",$value);
			$this->tpl->parseCurrentBlock();
		}
	}


	/**
	* cancel deletion of export files
	*/
	function cancelDeleteExportFileObject()
	{
		session_unregister("ilExportFiles");
		ilUtil::redirect($this->ctrl->getLinkTargetByClass("ilobjuserfoldergui", "export"));
	}


	/**
	* delete export files
	*/
	function deleteExportFileObject()
	{
		$export_dir = $this->object->getExportDirectory();
		foreach($_SESSION["ilExportFiles"] as $file)
		{
			$exp_file = $export_dir."/".$file;
			if (@is_file($exp_file))
			{
				unlink($exp_file);
			}
		}
		ilUtil::redirect($this->ctrl->getLinkTargetByClass("ilobjuserfoldergui", "export"));
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
			ilUtil::redirect($this->ctrl->getLinkTargetByClass("ilobjuserfoldergui", "export"));
			exit;
		}
		
		$this->getTemplateFile("export","usr");
		
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
		$this->tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.usr_export_file_row.html");

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
		$this->tpl->setVariable("IMG_ARROW", ilUtil::getImagePath("arrow_downright.gif"));
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
		else
		{
			$this->tpl->setCurrentBlock("notfound");
			$this->tpl->setVariable("TXT_OBJECT_NOT_FOUND", $this->lng->txt("obj_not_found"));
			$this->tpl->setVariable("NUM_COLS", 3);
			$this->tpl->parseCurrentBlock();
		}

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
	
	/**
	* new account mail administration
	*/
	function newAccountMailObject()
	{
		global $lng;
		
		$this->setSubTabs('settings');
		$this->tabs_gui->setTabActive('global_settings');
		$this->tabs_gui->setSubTabActive('user_new_account_mail');

		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.usrf_new_account_mail.html');
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
		$this->tpl->setVariable("IMG_MAIL", ilUtil::getImagePath("icon_mail.gif"));
		
		$lng->loadLanguageModule("meta");
		$lng->loadLanguageModule("mail");
		$this->tpl->setVariable("TXT_NEW_USER_ACCOUNT_MAIL", $lng->txt("user_new_account_mail"));
		$this->tpl->setVariable("TXT_NEW_USER_ACCOUNT_MAIL_DESC", $lng->txt("user_new_account_mail_desc"));
		
		// placeholder help text
		$this->tpl->setVariable("TXT_USE_PLACEHOLDERS", $lng->txt("mail_nacc_use_placeholder"));
		$this->tpl->setVariable("TXT_MAIL_SALUTATION", $lng->txt("mail_nacc_salutation"));
		$this->tpl->setVariable("TXT_FIRST_NAME", $lng->txt("firstname"));
		$this->tpl->setVariable("TXT_LAST_NAME", $lng->txt("lastname"));
		$this->tpl->setVariable("TXT_EMAIL", $lng->txt("email"));
		$this->tpl->setVariable("TXT_LOGIN", $lng->txt("mail_nacc_login"));
		$this->tpl->setVariable("TXT_PASSWORD", $lng->txt("password"));
		$this->tpl->setVariable("TXT_PASSWORD_BLOCK", $lng->txt("mail_nacc_pw_block"));
		$this->tpl->setVariable("TXT_NOPASSWORD_BLOCK", $lng->txt("mail_nacc_no_pw_block"));
		$this->tpl->setVariable("TXT_ADMIN_MAIL", $lng->txt("mail_nacc_admin_mail"));
		$this->tpl->setVariable("TXT_ILIAS_URL", $lng->txt("mail_nacc_ilias_url"));
		$this->tpl->setVariable("TXT_CLIENT_NAME", $lng->txt("mail_nacc_client_name"));
		$this->tpl->setVariable("TXT_TARGET", $lng->txt("mail_nacc_target"));
		$this->tpl->setVariable("TXT_TARGET_TITLE", $lng->txt("mail_nacc_target_title"));
		$this->tpl->setVariable("TXT_TARGET_TYPE", $lng->txt("mail_nacc_target_type"));
		$this->tpl->setVariable("TXT_TARGET_BLOCK", $lng->txt("mail_nacc_target_block"));
		
		$langs = $lng->getInstalledLanguages();
		foreach($langs as $lang_key)
		{
			$amail = $this->object->_lookupNewAccountMail($lang_key);
			$this->tpl->setCurrentBlock("mail_block");
			$add = "";
			if ($lang_key == $lng->getDefaultLanguage())
			{
				$add = " (".$lng->txt("default").")";
			}
			$this->tpl->setVariable("TXT_LANGUAGE",
				$lng->txt("meta_l_".$lang_key).$add);
			$this->tpl->setVariable("TXT_BODY", $lng->txt("message_content"));
			$this->tpl->setVariable("TA_BODY", "body_".$lang_key);
			$this->tpl->setVariable("VAL_BODY", 
				ilUtil::prepareFormOutput($amail["body"]));
			$this->tpl->setVariable("TXT_SUBJECT", $lng->txt("subject"));
			$this->tpl->setVariable("INPUT_SUBJECT", "subject_".$lang_key);
			$this->tpl->setVariable("VAL_SUBJECT", 
				ilUtil::prepareFormOutput($amail["subject"]));
			$this->tpl->setVariable("TXT_SAL_G", $lng->txt("mail_salutation_general"));
			$this->tpl->setVariable("INPUT_SAL_G", "sal_g_".$lang_key);
			$this->tpl->setVariable("VAL_SAL_G", 
				ilUtil::prepareFormOutput($amail["sal_g"]));
			$this->tpl->setVariable("TXT_SAL_M", $lng->txt("mail_salutation_male"));
			$this->tpl->setVariable("INPUT_SAL_M", "sal_m_".$lang_key);
			$this->tpl->setVariable("VAL_SAL_M", 
				ilUtil::prepareFormOutput($amail["sal_m"]));
			$this->tpl->setVariable("TXT_SAL_F", $lng->txt("mail_salutation_female"));
			$this->tpl->setVariable("INPUT_SAL_F", "sal_f_".$lang_key);
			$this->tpl->setVariable("VAL_SAL_F", 
				ilUtil::prepareFormOutput($amail["sal_f"]));
			$this->tpl->parseCurrentBlock();
		}
		$this->tpl->setVariable("TXT_CANCEL", $lng->txt("cancel"));
		$this->tpl->setVariable("TXT_SAVE", $lng->txt("save"));
	}

	function cancelNewAccountMailObject()
	{
		$this->ctrl->redirect($this, "settings");
	}

	function saveNewAccountMailObject()
	{
		global $lng;
		
		ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);
		$langs = $lng->getInstalledLanguages();
		foreach($langs as $lang_key)
		{
			$this->object->_writeNewAccountMail($lang_key,
				ilUtil::stripSlashes($_POST["subject_".$lang_key]),
				ilUtil::stripSlashes($_POST["sal_g_".$lang_key]),
				ilUtil::stripSlashes($_POST["sal_f_".$lang_key]),
				ilUtil::stripSlashes($_POST["sal_m_".$lang_key]),
				ilUtil::stripSlashes($_POST["body_".$lang_key]));
		}
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
			$tabs_gui->addTarget("obj_usrf",
				$this->ctrl->getLinkTarget($this, "view"), array("view","delete","resetFilter", "userAction", ""), "", "");
		}
		
		if ($rbacsystem->checkAccess("write",$this->object->getRefId()))
		{
			$tabs_gui->addTarget("global_settings",
				$this->ctrl->getLinkTarget($this, "settings"), array("settings", "saveGlobalUserSettings"), "", "");
				
			$tabs_gui->addTarget("export",
				$this->ctrl->getLinkTarget($this, "export"), "export", "", "");

			if((ilObjUserTracking::_enabledLearningProgress() or
				ilObjUserTracking::_enabledTracking())
			   and ilObjUserTracking::_enabledUserRelatedData())
			{
				$tabs_gui->addTarget("learning_progress",
									 $this->ctrl->getLinkTarget($this, "learningProgress"), "learningProgress", "", "");
			}
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
				$this->tabs_gui->addSubTabTarget("standard_fields",
												 $this->ctrl->getLinkTarget($this,'settings'),
												 array("settings", "saveGlobalUserSettings"), get_class($this));
				$this->tabs_gui->addSubTabTarget("user_defined_fields",
												 $this->ctrl->getLinkTarget($this,'listUserDefinedFields'),
												 "listUserDefinedFields",get_class($this));
				$this->tabs_gui->addSubTabTarget("user_new_account_mail",
												 $this->ctrl->getLinkTarget($this,'newAccountMail'),
												 "newAccountMail",get_class($this));
				$this->tabs_gui->addSubTabTarget('loginname_settings',
					$this->ctrl->getLinkTarget($this, 'showLoginnameSettings'), 'showLoginnameSettings', get_class($this));												 
												 
				break;
		}
	}
	
	public function showLoginnameSettingsObject()
	{
		global $ilSetting;
		
		$this->setSubTabs('settings');
		$this->tabs_gui->setTabActive('global_settings');
		$this->tabs_gui->setSubTabActive('loginname_settings');
		
		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI;
		$form->setFormAction($this->ctrl->getFormAction($this, 'saveLoginnameSettings'));
		$form->setTitle($this->lng->txt('loginname_settings'));
		$check = new ilCheckboxInputGUI($this->lng->txt('allow_change_loginname'), 'allow_change_loginname');
		$check->setValue('1');
		$check->setChecked((bool)$ilSetting->get('allow_change_loginname'));
		$form->addItem($check);
		$check = new ilCheckboxInputGUI($this->lng->txt('create_history_loginname'), 'create_history_loginname');
		$check->setValue('1');
		$check->setChecked((bool)$ilSetting->get('create_history_loginname'));
		$form->addItem($check);	
		$info = new ilCustomInputGUI($this->lng->txt('create_history_loginname_must_be_enabled'),'create_history_loginname_must_be_enabled');
		$form->addItem($info);
		$check = new ilCheckboxInputGUI($this->lng->txt('allow_history_loginname_again'), 'allow_history_loginname_again');
		$check->setValue('1');
		$check->setChecked((bool)$ilSetting->get('allow_history_loginname_again'));
		$form->addItem($check);	
		$form->addCommandButton('saveLoginnameSettings', $this->lng->txt('save'));
		
		$this->tpl->setVariable('ADM_CONTENT', $form->getHTML());
	}
	
	public function saveLoginnameSettingsObject()
	{
		global $ilUser, $ilSetting;
		
		$allow_change_loginname = (int)$_POST['allow_change_loginname'];
		$create_history_loginname = (int)$_POST['create_history_loginname'];
		$allow_history_loginname_again = (int)$_POST['allow_history_loginname_again'];		

		$ilSetting->set('allow_change_loginname', (string)$allow_change_loginname);
		$ilSetting->set('create_history_loginname', (string)$create_history_loginname);
		$ilSetting->set('allow_history_loginname_again', (string)$allow_history_loginname_again);
		
		ilUtil::sendSuccess($this->lng->txt('saved_successfully'));
	
		return $this->showLoginnameSettingsObject();		
	}
	
	/*
	function showUpperIcon()
	{
		var_dump($this->ctrl->getCmd());
	}*/
} // END class.ilObjUserFolderGUI
?>
