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
* Class ilObjAuthSettingsGUI
*
* @author Stefan Meyer <smeyer@databay.de> 
* @version $Id$
* 
* @extends ilObjectGUI
* @package ilias-core
*/

require_once "./classes/class.ilObjectGUI.php";

class ilObjPaymentSettingsGUI extends ilObjectGUI
{
	/**
	* Constructor
	* @access public
	*/
	function ilObjPaymentSettingsGUI($a_data,$a_id,$a_call_by_reference,$a_prepare_output = true)
	{
		$this->type = "pays";
		$this->ilObjectGUI($a_data,$a_id,$a_call_by_reference,$a_prepare_output);

		$this->lng->loadLanguageModule('payment');
	}

	function gatewayObject()
	{
		switch($_POST["action"])
		{
			case "deleteVendorsObject":
				$this->deleteVendors();
				break;

			default:
				$this->vendorsObject();
				break;
		}
		return true;
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

	function vendorsObject($a_show_confirm = false)
	{
		global $rbacsystem;

		// MINIMUM ACCESS LEVEL = 'read'
		if(!$rbacsystem->checkAccess("read", $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_read"),$this->ilias->error_obj->MESSAGE);
		}

		$_SESSION['pays_vendor'] = is_array($_SESSION['pays_vendor']) ?  $_SESSION['pays_vendor'] : array();
		
		$this->object->initPaymentVendorsObject();
		if(!count($vendors = $this->object->payment_vendors_obj->getVendors()))
		{
			sendInfo($this->lng->txt('pay_no_vendors_created'));
		}
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.pays_vendors.html",true);
		
		$this->__showButton('searchUser',$this->lng->txt('search_user'));

		if($a_show_confirm)
		{
			$this->tpl->setCurrentBlock("confirm_delete");
			$this->tpl->setVariable("CONFIRM_FORMACTION",$this->ctrl->getFormAction($this));
			$this->tpl->setVariable("TXT_CANCEL",$this->lng->txt('cancel'));
			$this->tpl->setVariable("CONFIRM_CMD",'performDeleteVendors');
			$this->tpl->setVariable("TXT_CONFIRM",$this->lng->txt('delete'));
			$this->tpl->parseCurrentBlock();
		}

		$counter = 0;
		$f_result = array();
		foreach($vendors as $vendor)
		{
			// GET USER OBJ
			if($tmp_obj = ilObjectFactory::getInstanceByObjId($vendor['vendor_id'],false))
			{
				$f_result[$counter][]	= ilUtil::formCheckbox(in_array($vendor['vendor_id'],$_SESSION['pays_vendor']) ? 1 : 0,
															   "vendor[]",
															   $vendor['vendor_id']);
				$f_result[$counter][]	= $tmp_obj->getLogin();
				$f_result[$counter][]	= $vendor['cost_center'];
				$f_result[$counter][]	= 2;
				
				unset($tmp_obj);
				++$counter;
			}
			$this->__showVendorsTable($f_result);

		} // END VENDORS TABLE

		return true;
	}
	
	function payMethodsObject()
	{
		include_once './payment/classes/class.ilPayMethods.php';

		global $rbacsystem;

		// MINIMUM ACCESS LEVEL = 'read'
		if(!$rbacsystem->checkAccess("read", $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_read"),$this->ilias->error_obj->MESSAGE);
		}

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.pays_pay_methods.html",true);

		$this->tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$this->tpl->setVariable("TYPE_IMG",ilUtil::getImagePath('icon_pays.gif'));
		$this->tpl->setVariable("ALT_IMG",$this->lng->txt('obj_pays'));
		$this->tpl->setVariable("TITLE",$this->lng->txt('pays_pay_methods'));
		$this->tpl->setVariable("TXT_OFFLINE",$this->lng->txt('pays_offline'));
		$this->tpl->setVariable("TXT_BILL",$this->lng->txt('pays_bill'));
		$this->tpl->setVariable("BILL_CHECK",ilUtil::formCheckbox(
									(int) ilPayMethods::_enabled('pm_bill') ? 1 : 0,'pm_bill',1,true));

		$this->tpl->setVariable("TXT_ENABLED",$this->lng->txt('enabled'));
		$this->tpl->setVariable("TXT_ONLINE",$this->lng->txt('pays_online'));
		$this->tpl->setVariable("TXT_BMF",$this->lng->txt('pays_bmf'));
		$this->tpl->setVariable("ONLINE_CHECK",ilUtil::formCheckbox((int) ilPayMethods::_enabled('pm_bmf'),'pm_bmf',1));
		
		// footer
		$this->tpl->setVariable("COLUMN_COUNT",3);
		$this->tpl->setVariable("PBTN_NAME",'savePayMethods');
		$this->tpl->setVariable("PBTN_VALUE",$this->lng->txt('save'));
		
	}

	function savePayMethodsObject()
	{
		include_once './payment/classes/class.ilPayMethods.php';
		include_once './payment/classes/class.ilPaymentObject.php';


		global $rbacsystem;

		// MINIMUM ACCESS LEVEL = 'read'
		if(!$rbacsystem->checkAccess("read", $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_read"),$this->ilias->error_obj->MESSAGE);
		}
		

		// check current payings
		if(ilPayMethods::_enabled('pm_bill') and !$_POST['pm_bill'])
		{
			if(ilPaymentObject::_getCountObjectsByPayMethod('pm_bill'))
			{
				sendInfo($this->lng->txt('pays_objects_bill_exist'));
				$this->payMethodsObject();

				return false;
			}
		}

		if(ilPayMethods::_enabled('pm_bmf') and !$_POST['pm_bmf'])
		{
			if(ilPaymentObject::_getCountObjectsByPayMethod('pm_bmf'))
			{
				sendInfo($this->lng->txt('pays_objects_bmf_exist'));
				$this->payMethodsObject();

				return false;
			}
		}

		ilPayMethods::_disableAll();
		if(isset($_POST['pm_bill']))
		{
			ilPayMethods::_enable('pm_bill');
		}
		if(isset($_POST['pm_bmf']))
		{
			ilPayMethods::_enable('pm_bmf');
		}
		$this->payMethodsObject();

		sendInfo($this->lng->txt('pays_updated_pay_method'));

		return true;
	}

	function cancelDeleteVendorsObject()
	{
		unset($_SESSION['pays_vendor']);
		$this->vendorsObject();

		return true;
	}

	function deleteVendors()
	{
		if(!count($_POST['vendor']))
		{
			sendInfo($this->lng->txt('pays_no_vendor_selected'));
			$this->vendorsObject();

			return true;
		}
		// CHECK BOOKINGS
		foreach($_POST['vendor'] as $vendor)
		{
			if(0)
			{
				$_SESSION["pays_vendor"] = $_POST["vendor"];
				sendInfo($this->lng->txt('pays_active_bookings'));
				$this->vendorsObject();

				return true;
			}
		}
		
		$_SESSION["pays_vendor"] = $_POST["vendor"];
		sendInfo($this->lng->txt("pays_sure_delete_selected_vendors"));
		$this->vendorsObject(true);

		return true;
	}
	function performDeleteVendorsObject()
	{
		global $rbacsystem;

		// MINIMUM ACCESS LEVEL = 'read'
		if(!$rbacsystem->checkAccess("write", $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilias->error_obj->MESSAGE);
		}

		$this->object->initPaymentVendorsObject();
		foreach($_SESSION['pays_vendor'] as $vendor)
		{
			$this->object->payment_vendors_obj->delete($vendor);
		}

		sendInfo($this->lng->txt('pays_deleted_number_vendors').' '.count($_SESSION['pays_vendor']));
		unset($_SESSION['pays_vendor']);
		
		$this->vendorsObject();

		return true;
	}

	function searchUserObject()
	{
		global $rbacsystem;

		// MINIMUM ACCESS LEVEL = 'read'
		if(!$rbacsystem->checkAccess("read", $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_read"),$this->ilias->error_obj->MESSAGE);
		}

		$this->tpl->addBlockFile("ADM_CONTENT","adm_content","tpl.pays_user_search.html",true);

		$this->lng->loadLanguageModule('search');

		$this->tpl->setVariable("F_ACTION",$this->ctrl->getFormAction($this));
		$this->tpl->setVariable("SEARCH_ASSIGN_USR",$this->lng->txt("crs_search_members"));
		$this->tpl->setVariable("SEARCH_SEARCH_TERM",$this->lng->txt("search_search_term"));
		$this->tpl->setVariable("SEARCH_VALUE",$_SESSION["pays_search_str"] ? $_SESSION["pays_search_str"] : "");
		$this->tpl->setVariable("BTN2_VALUE",$this->lng->txt("cancel"));
		$this->tpl->setVariable("BTN1_VALUE",$this->lng->txt("search"));

		return true;
	}

	function searchObject()
	{
		global $rbacsystem,$tree;

		if(!$rbacsystem->checkAccess("read", $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_read"),$this->ilias->error_obj->MESSAGE);
		}

		$_SESSION["pays_search_str"] = $_POST["search_str"] = $_POST["search_str"] ? $_POST["search_str"] : $_SESSION["pays_search_str"];

		if(!isset($_POST["search_str"]))
		{
			sendInfo($this->lng->txt("crs_search_enter_search_string"));
			$this->searchUserObject();
			
			return false;
		}
		if(!count($result = $this->__search(ilUtil::stripSlashes($_POST["search_str"]))))
		{
			sendInfo($this->lng->txt("crs_no_results_found"));
			$this->searchUserObject();

			return false;
		}

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.pays_usr_selection.html",true);
		$this->__showButton("searchUser",$this->lng->txt("crs_new_search"));
		
		$counter = 0;
		$f_result = array();
		foreach($result as $user)
		{
			if(!$tmp_obj = ilObjectFactory::getInstanceByObjId($user["id"],false))
			{
				continue;
			}
			$f_result[$counter][] = ilUtil::formCheckbox(0,"user[]",$user["id"]);
			$f_result[$counter][] = $tmp_obj->getLogin();
			$f_result[$counter][] = $tmp_obj->getLastname();
			$f_result[$counter][] = $tmp_obj->getFirstname();

			unset($tmp_obj);
			++$counter;
		}
		$this->__showSearchUserTable($f_result);

		return true;
	}
	function addVendorObject()
	{
		global $rbacsystem;

		// MINIMUM ACCESS LEVEL = 'administrate'
		if(!$rbacsystem->checkAccess("write", $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilias->error_obj->MESSAGE);
		}
		if(!$_POST['vendor_login'])
		{
			sendInfo($this->lng->txt('pays_no_username_given'));
			$this->vendorsObject();

			return true;
		}
		if(!($usr_id = ilObjUser::getUserIdByLogin(ilUtil::stripSlashes($_POST['vendor_login']))))
		{
			sendInfo($this->lng->txt('pays_no_valid_username_given'));
			$this->vendorsObject();

			return true;
		}
		
		$this->object->initPaymentVendorsObject();

		if($this->object->payment_vendors_obj->isAssigned($usr_id))
		{
			sendInfo($this->lng->txt('pays_user_already_assigned'));
			$this->vendorsObject();

			return true;
		}
		$this->object->payment_vendors_obj->add($usr_id);

		sendInfo($this->lng->txt('pays_added_vendor'));
		$this->vendorsObject();
		
		return true;
	}
		
	function addUserObject()
	{
		global $rbacsystem;

		// MINIMUM ACCESS LEVEL = 'administrate'
		if(!$rbacsystem->checkAccess("write", $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilias->error_obj->MESSAGE);
		}

		$this->lng->loadLanguageModule('crs');
		if(!is_array($_POST["user"]))
		{
			sendInfo($this->lng->txt("crs_no_users_selected"));
			$this->searchObject();

			return false;
		}
		
		$this->object->initPaymentVendorsObject();

		$already_assigned = $assigned = 0;
		foreach($_POST['user'] as $usr_id)
		{
			if($this->object->payment_vendors_obj->isAssigned($usr_id))
			{
				++$already_assigned;

				continue;
			}
			$this->object->payment_vendors_obj->add($usr_id);
			++$assigned;
			
			// TODO: SEND NOTIFICATION
		}
		$message = '';
		if($assigned)
		{
			$message .= $this->lng->txt('pays_assigned_vendors').' '.$assigned;
		}
		if($already_assigned)
		{
			$message .= '<br />'.$this->lng->txt('pays_already_assigned_vendors').' '.$already_assigned;
		}

		sendInfo($message);
		$this->vendorsObject();

		return true;
	}		


	// PRIVATE
	function __showVendorsTable($a_result_set)
	{
		$actions = array("deleteVendorsObject"	=> $this->lng->txt("pays_delete_vendor"));

		$tbl =& $this->__initTableGUI();
		$tpl =& $tbl->getTemplateObject();

		// SET FORMAACTION
		$tpl->setCurrentBlock("tbl_form_header");

		$tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("tbl_action_row");

		$tpl->setCurrentBlock("input_text");
		$tpl->setVariable("PB_TXT_NAME",'vendor_login');
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("plain_button");
		$tpl->setVariable("PBTN_NAME","addVendor");
		$tpl->setVariable("PBTN_VALUE",$this->lng->txt("pays_add_vendor"));
		$tpl->parseCurrentBlock();
		$tpl->setCurrentBlock("plain_buttons");
		$tpl->parseCurrentBlock();

		$tpl->setVariable("COLUMN_COUNTS",4);

		$tpl->setVariable("IMG_ARROW", ilUtil::getImagePath("arrow_downright.gif"));

		$tpl->setCurrentBlock("tbl_action_select");
		$tpl->setVariable("SELECT_ACTION",ilUtil::formSelect(1,"action",$actions,false,true));
		$tpl->setVariable("BTN_NAME","gateway");
		$tpl->setVariable("BTN_VALUE",$this->lng->txt("execute"));
		$tpl->parseCurrentBlock();
		$tpl->setCurrentBlock("tbl_action_row");
		$tpl->setVariable("TPLPATH",$this->tpl->tplPath);
		$tpl->parseCurrentBlock();

		$tbl->setTitle($this->lng->txt("vendors"),"icon_usr_b.gif",$this->lng->txt("vendors"));
		$tbl->setHeaderNames(array('',
								   $this->lng->txt("pays_vendor"),
								   $this->lng->txt("pays_cost_center"),
								   $this->lng->txt("pays_number_bookings")));
		$tbl->setHeaderVars(array("",
								  "login",
								  "cost_center",
								  "bookings"),
							array("ref_id" => $this->object->getRefId(),
								  "cmd" => "vendors",
								  "update_members" => 1,
								  "cmdClass" => "ilobjpaymentsettingsgui",
								  "cmdNode" => $_GET["cmdNode"]));
		$tbl->setColumnWidth(array("4%","48%","25%","24%"));


		$this->__setTableGUIBasicData($tbl,$a_result_set);
		$tbl->render();

		$this->tpl->setVariable("VENDOR_TABLE",$tbl->tpl->get());

		return true;
	}


	function __showSearchUserTable($a_result_set,$a_cmd = "search")
	{
		$tbl =& $this->__initTableGUI();
		$tpl =& $tbl->getTemplateObject();


		// SET FORMACTION
		$tpl->setCurrentBlock("tbl_form_header");
		$tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("tbl_action_btn");
		$tpl->setVariable("BTN_NAME","vendors");
		$tpl->setVariable("BTN_VALUE",$this->lng->txt("cancel"));
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("tbl_action_btn");
		$tpl->setVariable("BTN_NAME","addUser");
		$tpl->setVariable("BTN_VALUE",$this->lng->txt("add"));
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("tbl_action_row");
		$tpl->setVariable("COLUMN_COUNTS",5);
		$tpl->setVariable("IMG_ARROW",ilUtil::getImagePath("arrow_downright.gif"));
		$tpl->parseCurrentBlock();

		$tbl->setTitle($this->lng->txt("pays_header_select_vendor"),"icon_usr_b.gif",$this->lng->txt("pays_header_select_vendor"));
		$tbl->setHeaderNames(array("",
								   $this->lng->txt("login"),
								   $this->lng->txt("firstname"),
								   $this->lng->txt("lastname")));
		$tbl->setHeaderVars(array("",
								  "login",
								  "firstname",
								  "lastname"),
							array("ref_id" => $this->object->getRefId(),
								  "cmd" => $a_cmd,
								  "cmdClass" => "ilobjpaymentsettingsgui",
								  "cmdNode" => $_GET["cmdNode"]));

		$tbl->setColumnWidth(array("3%","32%","32%","32%"));

		$this->__setTableGUIBasicData($tbl,$a_result_set);
		$tbl->render();
		
		$this->tpl->setVariable("SEARCH_RESULT_TABLE",$tbl->tpl->get());

		return true;
	}


	function &__initTableGUI()
	{
		include_once "./classes/class.ilTableGUI.php";

		return new ilTableGUI(0,false);
	}

	function __setTableGUIBasicData(&$tbl,&$result_set,$from = "")
	{

		$offset = $_GET["offset"];
		$order = $_GET["sort_by"];
		$direction = $_GET["sort_order"];

		$tbl->setOrderColumn($order);
		$tbl->setOrderDirection($direction);
		$tbl->setOffset($offset);
		$tbl->setLimit($_GET["limit"]);
		$tbl->setMaxCount(count($result_set));
		$tbl->setFooter("tblfooter",$this->lng->txt("previous"),$this->lng->txt("next"));
		$tbl->setData($result_set);
	}

	function __search($a_search_string)
	{
		include_once("./classes/class.ilSearch.php");

		$this->lng->loadLanguageModule("content");

		$search =& new ilSearch($_SESSION["AccountId"]);
		$search->setPerformUpdate(false);
		$search->setSearchString(ilUtil::stripSlashes($a_search_string));
		$search->setCombination("and");
		$search->setSearchFor(array(0 => 'usr'));
		$search->setSearchType('new');

		if($search->validate($message))
		{
			$search->performSearch();
		}
		else
		{
			sendInfo($message,true);
			$this->ctrl->redirect($this,"searchUser");
		}
		return $search->getResultByType('usr');
	}		
	
} // END class.ilObjPaymentSettingsGUI
?>
