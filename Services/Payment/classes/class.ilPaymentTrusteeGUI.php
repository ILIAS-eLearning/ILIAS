<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class ilPaymentTrusteeGUI
*
* @author Stefan Meyer
* @version $Id: class.ilPaymentTrusteeGUI.php 20288 2009-06-22 08:15:29Z mjansen $
*
* @package core
*/
include_once './Services/Payment/classes/class.ilPaymentTrustees.php';
include_once './Services/Form/classes/class.ilPropertyFormGUI.php';
include_once './Services/Utilities/classes/class.ilConfirmationGUI.php';
include_once './Services/Payment/classes/class.ilShopTableGUI.php';

class ilPaymentTrusteeGUI extends ilShopBaseGUI
{
	public $trustee_obj = null;
	public $user_obj;
	public $ctrl;

	public function __construct($user_obj)
	{
		parent::__construct();

		$this->user_obj = $user_obj;
		$this->trustee_obj = new ilPaymentTrustees($this->user_obj);
		$this->lng->loadLanguageModule('crs');
		
		$this->ctrl->saveParameter($this, 'baseClass');
	}
	
	protected function prepareOutput()
	{
		global $ilTabs;
		
		parent::prepareOutput();

		$ilTabs->setTabActive('paya_header');
		$ilTabs->setSubTabActive('paya_trustees');
	}
	
	public function executeCommand()
	{
		$cmd = $this->ctrl->getCmd();
		switch ($this->ctrl->getNextClass($this))
		{
			default:
				if(!$cmd = $this->ctrl->getCmd())
				{
					$cmd = 'showTrustees';
				}
				$this->prepareOutput();
				$this->$cmd();
				break;
		}
	}

	public function cancelDelete()
	{
		unset($_SESSION['paya_delete_trustee']);
		$this->showTrustees();

		return true;
	}


	public function showTrustees($a_show_delete = false)
	{
		global $ilToolbar;

		$_SESSION['paya_delete_trustee'] = $_SESSION['paya_delete_trustee'] ? $_SESSION['paya_delete_trustee'] : array();

		$actions = array(0	=> $this->lng->txt("paya_disabled"),
						 1 	=> $this->lng->txt("paya_enabled"));
	
		include_once("./Services/Form/classes/class.ilTextInputGUI.php");
		$ul = new ilTextInputGUI($this->lng->txt("user"), "search_str");
		$ul->setDataSource($this->ctrl->getLinkTarget($this, "performSearch", "", true));
		$ul->setSize(20);
		$ilToolbar->addInputItem($ul, true);
		$ilToolbar->addFormButton($this->lng->txt("add"), "performSearch");
		$ilToolbar->setFormAction($this->ctrl->getFormAction($this));
		
		$this->tpl->addBlockfile('ADM_CONTENT','adm_content','tpl.main_view.html','Services/Payment');
						 
		if($a_show_delete)
		{
			$oConfirmationGUI = new ilConfirmationGUI();
			
			// set confirm/cancel commands
			$oConfirmationGUI->setFormAction($this->ctrl->getFormAction($this,"performDeleteTrustee"));
			$oConfirmationGUI->setHeaderText($this->lng->txt("paya_sure_delete_selected_trustees"));
			$oConfirmationGUI->setCancel($this->lng->txt("cancel"), "cancelDelete");
			$oConfirmationGUI->setConfirm($this->lng->txt("confirm"), "performDeleteTrustee");			
	
			foreach($this->trustee_obj->getTrustees() as $trustee)
			{
				$delete_row = '';
				if(in_array($trustee['trustee_id'],$_POST['trustee']))
				{
					if($tmp_obj = ilObjectFactory::getInstanceByObjId($trustee['trustee_id'],false))
					{
						$delete_row	= $tmp_obj->getLogin().' -> '.$tmp_obj->getFirstname().' '.$tmp_obj->getLastname();
					}	
				}
	
				$oConfirmationGUI->addItem('',$delete_row, $delete_row);
			}
			
			$this->tpl->setVariable("CONFIRMATION",$oConfirmationGUI->getHTML());	
			
		}

		if(!count($this->trustee_obj->getTrustees()))
		{
			ilUtil::sendInfo($this->lng->txt('paya_no_trustees'));
			return true;
		}
		
		$counter = 0;
		$f_result = array();
		
		require_once 'Services/Mail/classes/class.ilMailFormCall.php';
		foreach($this->trustee_obj->getTrustees() as $trustee)
		{
			// GET USER OBJ
			if($tmp_obj = ilObjectFactory::getInstanceByObjId($trustee['trustee_id'],false))
			{
				$f_result[$counter]['trustee_id']	= ilUtil::formCheckbox(in_array($trustee['trustee_id'],$_SESSION['paya_delete_trustee']) ? 1 : 0,
															   "trustee[]",
															   $trustee['trustee_id']);
				$f_result[$counter]['login']	= $tmp_obj->getLogin();
				$f_result[$counter]['firstname']	= $tmp_obj->getFirstname();
				$f_result[$counter]['lastname']	= $tmp_obj->getLastname();

				$f_result[$counter]['perm_stat']	= ilUtil::formSelect((int) $trustee['perm_stat'],
															 'perm_stat['.$trustee['trustee_id'].']',
															 $actions,
															 false,
															 true);
				
				$f_result[$counter]['perm_obj']	= ilUtil::formSelect((int) $trustee['perm_obj'],
															 'perm_obj['.$trustee['trustee_id'].']',
															 $actions,
															 false,
															 true);
															 
				$f_result[$counter]['perm_coupons']	= ilUtil::formSelect((int) $trustee['perm_coupons'],
															 'perm_coupons['.$trustee['trustee_id'].']',
															 $actions,
															 false,
															 true);

#				$link_mail = "<a target=\"_blank\" href=\"./ilias.php?baseClass=ilMailGUI&type=new&rcp_to=".
#					$tmp_obj->getLogin()."\"".$img_mail."</a>";                
                $url_mail = ilMailFormCall::getLinkTarget($this, '', array(), array('type' => 'new', 'rcp_to' => $tmp_obj->getLogin()));
				$link_mail = "<div class=\"il_ContainerItemCommands\"><a class=\"il_ContainerItemCommand\" href=\"".
					$url_mail."\">".$this->lng->txt("mail")."</a></div>";
				
				$f_result[$counter]['options']	= $link_mail;

				unset($tmp_obj);
				++$counter;
			}
		}
		return $this->__showTrusteesTable($f_result);
	}

	public function deleteTrustee()
	{
		if(!is_array($_POST['trustee']))
		{
			ilUtil::sendInfo($this->lng->txt('crs_no_users_selected'));
			$this->showTrustees();

			return true;
		}
		$_SESSION['paya_delete_trustee'] = $_POST['trustee'];
		$this->showTrustees(true);
		
		return true;
	}

	public function performDeleteTrustee()
	{
		if(is_array($_SESSION['paya_delete_trustee']))
		{
			foreach($_SESSION['paya_delete_trustee'] as $id)
			{
				$this->trustee_obj->setTrusteeId($id);
				$this->trustee_obj->delete();
			}
		}
		unset($_SESSION['paya_delete_trustee']);
		ilUtil::sendInfo($this->lng->txt('paya_delete_trustee_msg'));
		$this->showTrustees();

		return true;
	}

	public function update()
	{
		foreach($this->trustee_obj->getTrustees() as $trustee)
		{
			$this->trustee_obj->setTrusteeId($trustee['trustee_id']);
			$this->trustee_obj->toggleStatisticPermission($_POST['perm_stat']["$trustee[trustee_id]"]);
			$this->trustee_obj->toggleObjectPermission($_POST['perm_obj']["$trustee[trustee_id]"]);
			$this->trustee_obj->toggleCouponsPermission($_POST['perm_coupons']["$trustee[trustee_id]"]);			
			$this->trustee_obj->modify();
		}
		ilUtil::sendInfo($this->lng->txt('paya_updated_trustees'));
		$this->showTrustees();

		return true;
	}

	public function performSearch()
	{
		if(!$_POST["search_str"])
		{
			ilUtil::sendInfo($this->lng->txt("crs_search_enter_search_string"));
			$this->showTrustees();

			return false;
		}
		if(!count($result = $this->__search(ilUtil::stripSlashes($_POST["search_str"]))))
		{
			ilUtil::sendInfo($this->lng->txt("crs_no_results_found"));
			$this->showTrustees();

			return false;
		}

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.main_view.html",'Services/Payment');
		$counter = 0;
		$f_result = array();
		foreach($result as $user)
		{
			if(!$tmp_obj = ilObjectFactory::getInstanceByObjId($user["id"],false))
			{
				continue;
			}
			$f_result[$counter]['trustee_id'] = ilUtil::formCheckbox(0,"user[]",$user["id"]);
			$f_result[$counter]['login'] = $tmp_obj->getLogin();
			$f_result[$counter]['firstname'] = $tmp_obj->getFirstname();
			$f_result[$counter]['lastname'] = $tmp_obj->getLastname();
			
			unset($tmp_obj);
			++$counter;
		}
		$this->__showSearchUserTable($f_result);
		return true;
	}

	public function addTrustee()
	{
		if(!is_array($_POST["user"]))
		{
			ilUtil::sendInfo($this->lng->txt("crs_no_users_selected"));
			$this->performSearch();

			return false;
		}
		if(in_array($this->user_obj->getId(),$_POST['user']))
		{
			ilUtil::sendInfo($this->lng->txt('paya_not_assign_yourself'));
			$this->showTrustees();

			return false;
		}

		// add them
		$counter = 0;
		foreach($_POST['user'] as $user_id)
		{
			if($this->trustee_obj->isTrustee($user_id))
			{
				continue;
			}
			$this->trustee_obj->setTrusteeId($user_id);
			$this->trustee_obj->toggleStatisticPermission(false);
			$this->trustee_obj->toggleObjectPermission(true);
			$this->trustee_obj->toggleCouponsPermission(true);
			$this->trustee_obj->add();
			++$counter;
		}

		if($counter)
		{
			ilUtil::sendInfo($this->lng->txt('paya_added_trustee'));
			$this->showTrustees();

			return true;
		}
		else
		{
			ilUtil::sendInfo($this->lng->txt('paya_user_already_assigned'));
			$this->performSearch();

			return false;
		}

	}
	function addUser()
	{
		if(!$_POST['trustee_login'])
		{
			ilUtil::sendInfo($this->lng->txt('paya_enter_login'));
			$this->showTrustees();
			
			return false;
		}
		if(!$user_id = ilObjUser::getUserIdByLogin($_POST['trustee_login']))
		{
			ilUtil::sendInfo($this->lng->txt('paya_no_valid_login'));
			$this->showTrustees();
			
			return false;
		}
		if($this->trustee_obj->isTrustee($user_id))
		{
			ilUtil::sendInfo($this->lng->txt('paya_user_already_assigned'));
			$this->showTrustees();
			
			return false;
		}
		if($user_id == $this->user_obj->getId())
		{
			ilUtil::sendInfo($this->lng->txt('paya_not_assign_yourself'));
			$this->showTrustees();

			return false;
		}
		
		// checks passed => add trustee
		$this->trustee_obj->setTrusteeId($user_id);
		$this->trustee_obj->toggleObjectPermission(true);
		$this->trustee_obj->toggleStatisticPermission(true);
		$this->trustee_obj->toggleCouponsPermission(true);
		$this->trustee_obj->add();

		ilUtil::sendInfo($this->lng->txt('paya_added_trustee'));
		$this->showTrustees();

		return true;
	}
	

	// PRIVATE
	function __search($a_search_string)
	{
		include_once("./Services/Search/classes/class.ilSearch.php");

		$this->lng->loadLanguageModule("content");

		$search = new ilSearch($this->user_obj->getId());
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
			ilUtil::sendInfo($message,true);
			$this->ctrl->redirect($this,"searchUser");
		}
		return $search->getResultByType('usr');
	}
	function __showSearchUserTable($a_result_set)
	{
		$tbl = new ilShopTableGUI($this);

		$tbl->setTitle($this->lng->txt("paya_trustee_table"));
		$tbl->setId('tbl_search_user_trustee');
		$tbl->setRowTemplate("tpl.shop_users_row.html", "Services/Payment");

		$tbl->addColumn(' ', 'trustee_id', '1%', true);
		$tbl->addColumn($this->lng->txt('login'), 'login', '10%');
		$tbl->addColumn($this->lng->txt('firstname'),'firstname','20%');
		$tbl->addColumn($this->lng->txt('lastname'), 'lastname', '20%');

		$tbl->setSelectAllCheckbox('user');
		$tbl->addMultiCommand("addTrustee", $this->lng->txt("add"));
		$tbl->addCommandButton('showTrustees',$this->lng->txt('cancel'));

		$tbl->fillFooter();
		$tbl->setData($a_result_set);
		$this->tpl->setVariable('TABLE', $tbl->getHTML());

		return true;
	}
	
	function __showTrusteesTable($a_result_set)
	{
		$tbl = new ilShopTableGUI($this);

		$tbl->setTitle($this->lng->txt("paya_trustee_table"));
		$tbl->setId('tbl_show_trustee');
		$tbl->setRowTemplate("tpl.shop_users_row.html", "Services/Payment");

		$tbl->addColumn(' ', 'trustee_id', '1%', true);
		$tbl->addColumn($this->lng->txt('login'), 'login', '10%');
		$tbl->addColumn($this->lng->txt('firstname'),'firstname','20%');
		$tbl->addColumn($this->lng->txt('lastname'), 'lastname', '20%');
		$tbl->addColumn($this->lng->txt('paya_perm_stat'), 'perm_stat', '15%');
		$tbl->addColumn($this->lng->txt('paya_perm_obj'), 'perm_obj', '15%');
		$tbl->addColumn($this->lng->txt('paya_perm_coupons'), 'perm_coupons', '15%');
		$tbl->addColumn('', 'options', '5%');

		$tbl->setSelectAllCheckbox('trustee_id');
		$tbl->addMultiCommand("deleteTrustee", $this->lng->txt("delete"));

		$tbl->addCommandButton('update',$this->lng->txt('apply'));
		$tbl->setData($a_result_set);
		$this->tpl->setVariable('TABLE', $tbl->getHTML());

		return true;
	}

}
?>