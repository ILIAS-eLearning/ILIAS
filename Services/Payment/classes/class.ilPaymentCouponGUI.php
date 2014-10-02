<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once 'Services/Form/classes/class.ilPropertyFormGUI.php';
include_once './Services/Utilities/classes/class.ilConfirmationGUI.php';
include_once './Services/Payment/classes/class.ilShopTableGUI.php';

class ilPaymentCouponGUI extends ilShopBaseGUI
{
	public $ctrl;

	public $lng;
	
	public $user_obj = null;
	public $coupon_obj = null;
	public $pobject = null;

	public function __construct($user_obj)
	{
		parent::__construct();
		
		$this->ctrl->saveParameter($this, 'baseClass');
		$this->user_obj = $user_obj;		
		$this->__initCouponObject();
	}
	
	protected function prepareOutput()
	{
		global $ilTabs;
		
		parent::prepareOutput();

		$ilTabs->setTabActive('paya_header');
		$ilTabs->setSubTabActive('paya_coupons_coupons');		
	}
	
	public function executeCommand()
	{
		$cmd = $this->ctrl->getCmd();
		switch ($this->ctrl->getNextClass($this))
		{
			default:
				if (!$cmd = $this->ctrl->getCmd())
				{
					$cmd = 'showCoupons';
				}
				$this->prepareOutput();
				$this->$cmd();
				break;
		}
	}
	
	public function resetFilter()
	{
		unset($_SESSION["pay_coupons"]);
		unset($_POST["title_type"]);
		unset($_POST["title_value"]);
		unset($_POST["coupon_type"]);
		unset($_POST["updateView"]);
		unset($_POST['from_check']);
		unset($_POST['until_check']);		
		
		$this->showCoupons();
		
		return true;
	}
	
	public function showCoupons()
	{
		global $ilToolbar;
		
		include_once("Services/User/classes/class.ilObjUser.php");
		
		$ilToolbar->addButton($this->lng->txt('paya_coupons_add'), $this->ctrl->getLinkTarget($this,'addCoupon'));
		if(!$_POST['show_filter'] && $_POST['updateView'] == '1')
		{
			$this->resetFilter();
		}
		else
		if ($_POST['updateView'] == 1)
		{ 
			$_SESSION['pay_coupons']['show_filter'] = $_POST['show_filter'];		
			$_SESSION['pay_coupons']['updateView'] = true;
			$_SESSION['pay_coupons']['until_check'] = $_POST['until_check'];
			$_SESSION['pay_coupons']['from_check'] = $_POST['from_check'];
			$_SESSION['pay_coupons']['title_type'] = isset($_POST['title_type']) ? $_POST['title_type'] : '' ;
			$_SESSION['pay_coupons']['title_value'] = isset($_POST['title_value']) ?  $_POST['title_value'] : '';
			
			if($_SESSION['pay_coupons']['from_check'] == '1')
			{
				$_SESSION['pay_coupons']['from']['date']['d'] = $_POST['from']['date']['d'];
				$_SESSION['pay_coupons']['from']['date']['m'] = $_POST['from']['date']['m'];
				$_SESSION['pay_coupons']['from']['date']['y'] = $_POST['from']['date']['y'];
			} 
			else 
			{
				$_SESSION['pay_coupons']['from']['date']['d'] = '';
				$_SESSION['pay_coupons']['from']['date']['m'] = '';
				$_SESSION['pay_coupons']['from']['date']['y'] = '';
			}
			
			if($_SESSION['pay_coupons']['until_check']== '1')
			{
				$_SESSION['pay_coupons']['til']['date']['d'] = $_POST['til']['date']['d'];
				$_SESSION['pay_coupons']['til']['date']['m'] = $_POST['til']['date']['m'];
				$_SESSION['pay_coupons']['til']['date']['y'] = $_POST['til']['date']['y'];
			} 
			else 
			{
				$_SESSION['pay_coupons']['til']['date']['d'] = '';
				$_SESSION['pay_coupons']['til']['date']['m'] = '';
				$_SESSION['pay_coupons']['til']['date']['y'] = '';
			}

			$_SESSION['pay_coupons']['coupon_type'] = $_POST['coupon_type'];

		}
	
		$this->coupon_obj->setSearchTitleType(ilUtil::stripSlashes($_SESSION['pay_coupons']['title_type']));
		$this->coupon_obj->setSearchTitleValue(ilUtil::stripSlashes($_SESSION['pay_coupons']['title_value']));
		$this->coupon_obj->setSearchType(ilUtil::stripSlashes($_SESSION['pay_coupons']['coupon_type']));

		$this->tpl->addBlockfile('ADM_CONTENT', 'adm_content', 'tpl.main_view.html', 'Services/Payment');		

		$filter_form = new ilPropertyFormGUI();
		$filter_form->setFormAction($this->ctrl->getFormAction($this));
		$filter_form->setTitle($this->lng->txt('pay_filter'));
		$filter_form->setId('formular');
		$filter_form->setTableWidth('100 %');
			
		$o_hide_check = new ilCheckBoxInputGUI($this->lng->txt('show_filter'),'show_filter');
		$o_hide_check->setValue(1);		
		$o_hide_check->setChecked($_SESSION['pay_coupons']['show_filter'] ? 1 : 0);	

		$o_hidden = new ilHiddenInputGUI('updateView');
		$o_hidden->setValue(1);
		$o_hidden->setPostVar('updateView');
		$o_hide_check->addSubItem($o_hidden);

		// Title type
		$o_title_type = new ilSelectInputGUI(); 
		$title_option = array($this->lng->txt('pay_starting'),$this->lng->txt('pay_ending'));
		$title_value = array('0','1'); 
		$o_title_type->setTitle($this->lng->txt('title'));
		$o_title_type->setOptions($title_option);
		$o_title_type->setValue($_SESSION['pay_coupons']['title_type']);		
		$o_title_type->setPostVar('title_type');
		$o_hide_check->addSubItem($o_title_type);
		
		// Title value
		$o_title_val = new ilTextInputGUI();
		$o_title_val->setValue($_SESSION['pay_coupons']['title_value']);		
		$o_title_val->setPostVar('title_value');
		$o_hide_check->addSubItem($o_title_val);

		//coupon type
		$o_coupon_type = new ilSelectInputGUI();
		$coupon_option = array(''=>'','fix'=>$this->lng->txt('paya_coupons_fix'),'percent'=>$this->lng->txt('paya_coupons_percentaged'));

		$o_coupon_type->setTitle($this->lng->txt('coupon_type'));
		$o_coupon_type->setOptions($coupon_option);
		$o_coupon_type->setValue($_SESSION['pay_coupons']['coupon_type']);		
		$o_coupon_type->setPostVar('coupon_type');

		$o_hide_check->addSubItem($o_coupon_type);
		
		// date from
		$o_from_check = new ilCheckBoxInputGUI($this->lng->txt('pay_order_date_from'),'from_check');
		$o_from_check->setValue(1);		
		$o_from_check->setChecked($_SESSION['pay_coupons']['from_check'] ? 1 : 0);		
		
		$o_date_from = new ilDateTimeInputGUI();
		$o_date_from->setPostVar('from');			
		$_POST['from'] = $_SESSION['pay_coupons']['from'];
		if($_SESSION['pay_coupons']['from_check'] == '1') 
		{
			$o_date_from->checkInput();	
		}

		$o_from_check->addSubItem($o_date_from);
		$o_hide_check->addSubItem($o_from_check);

		// date until
		$o_until_check = new ilCheckBoxInputGUI($this->lng->txt('pay_order_date_til'), 'until_check');
		$o_until_check->setValue(1);	
		$o_until_check->setChecked($_SESSION['pay_coupons']['until_check'] ? 1 : 0);				

		$o_date_until = new ilDateTimeInputGUI();
		$o_date_until->setPostVar('til');
		$_POST['til'] = $_SESSION['pay_coupons']['til'];
		if($_SESSION['pay_coupons']['until_check'] == '1') 
		{
			$o_date_until->checkInput();	
		}
		
		$o_until_check->addSubItem($o_date_until);
		$o_hide_check->addSubItem($o_until_check);	
	
		$filter_form->addCommandButton('showCoupons', $this->lng->txt('pay_update_view'));
		$filter_form->addCommandButton('resetFilter', $this->lng->txt('pay_reset_filter'));
		
		$filter_form->addItem($o_hide_check);		
	
		$this->tpl->setVariable('FORM', $filter_form->getHTML());
	
		if (!count($coupons = $this->coupon_obj->getCoupons()))
		{
			ilUtil::sendInfo($this->lng->txt('paya_coupons_not_found'));
			//if ($_POST['updateView'] == '1') ilUtil::sendInfo($this->lng->txt('paya_coupons_not_found'));
			//else ilUtil::sendInfo($this->lng->txt('paya_coupons_not_available'));		
			
			return true;
		}		
		
		$counter = 0;
		foreach ($coupons as $coupon)
		{
			$f_result[$counter]['pc_title'] = $coupon['pc_title'];
			$f_result[$counter]['number_of_codes'] = $coupon['number_of_codes'];
			$f_result[$counter]['usage_of_codes'] = $coupon['usage_of_codes'];
			
			if (!empty($coupon['objects']))
			{
				$objects = "";
				for ($i = 0; $i < count($coupon['objects']); $i++)
				{
					$tmp_obj = ilObjectFactory::getInstanceByRefId($coupon['objects'][$i], false);
					if($tmp_obj)
					{
						$objects .= $tmp_obj->getTitle();
					}
					else
					{
						$objects .= $this->lng->txt('object_not_found');
					}
					
					if ($i < count($coupon['objects']) - 1) $objects .= "<br />";
					
					unset($tmp_obj);	
				}				
			}
			else
			{
				$objects = "";
			}
			
			$f_result[$counter]['objects'] = $objects;
			$f_result[$counter]['pc_from'] = ($coupon['pc_from'] != NULL && $coupon['pc_from_enabled'] == '1') ? $coupon['pc_from'] : '';
			$f_result[$counter]['pc_till'] = ($coupon['pc_till'] != NULL && $coupon['pc_till_enabled'] == '1') ? $coupon['pc_till'] : '';
			$f_result[$counter]['pc_last_changed'] = ($coupon['pc_last_changed'] != NULL ? $coupon['pc_last_changed'] : ''); 
			$f_result[$counter]['pc_last_changed_author'] = ($coupon['pc_last_change_usr_id'] > 0 ? ilObjUser::_lookupLogin($coupon['pc_last_change_usr_id']) : ilObjUser::_lookupLogin($coupon['usr_id']));
			$this->ctrl->setParameter($this, 'coupon_id', $coupon['pc_pk']);
			$f_result[$counter]['options'] = "<div class=\"il_ContainerItemCommands\"><a class=\"il_ContainerItemCommand\" href=\"".$this->ctrl->getLinkTarget($this, "addCoupon")."\">".$this->lng->txt("edit")."</a>";
			$f_result[$counter]['options'] .= " <a class=\"il_ContainerItemCommand\" href=\"".$this->ctrl->getLinkTarget($this, "deleteCoupon")."\">".$this->lng->txt("delete")."</a></div>";

			++$counter;
		}
		
		return $this->__showCouponsTable($f_result);
	}
	
	private function __showCouponsTable($f_result)
	{
		require_once 'Services/Payment/classes/class.ilShopCouponsTableGUI.php';
		$tbl = new ilShopCouponsTableGUI($this, 'showCoupons');
		$tbl->setTitle($this->lng->txt("paya_coupons_coupons"));
		$tbl->setId('tbl_show_coupons');
		$tbl->setRowTemplate("tpl.shop_coupons_row.html", "Services/Payment");

		$tbl->addColumn($this->lng->txt('paya_coupons_title'), 'pc_title', '10%');
		$tbl->addColumn($this->lng->txt('paya_coupons_number_of_codes'), 'number_of_codes', '10%');
		$tbl->addColumn($this->lng->txt('paya_coupons_usage_of_codes'), 'usage_of_codes', '10%');
		$tbl->addColumn($this->lng->txt('paya_coupons_objects'), 'objects', '10%');
		$tbl->addColumn($this->lng->txt('paya_coupons_from'), 'pc_from', '10%');
		$tbl->addColumn($this->lng->txt('paya_coupons_till'), 'pc_till', '10%');
		$tbl->addColumn($this->lng->txt('last_change'), 'pc_last_changed', '10%');
		$tbl->addColumn($this->lng->txt('author'), 'pc_last_changed_author', '10%');		
		$tbl->addColumn('', 'options','30%');

		$tbl->setData($f_result);
		$this->tpl->setVariable('TABLE', $tbl->getHTML());	

		return true;
	}

	public function saveCouponForm()
	{
		$this->error = '';

		if ($_POST['title'] == '') $this->error .= 'paya_coupons_title,';
		if ($_POST['coupon_type'] == '') $this->error .= 'paya_coupons_type,';
		if ($_POST['coupon_value'] == '') $this->error .= 'paya_coupons_value,';
		else $_POST['coupon_value'] = ilFormat::checkDecimal($_POST['coupon_value']);		
		
		$this->coupon_obj->setId($_GET['coupon_id']);
		$this->coupon_obj->setCouponUser($this->user_obj->getId());
		$this->coupon_obj->setTitle(ilUtil::stripSlashes($_POST['title']));
		$this->coupon_obj->setDescription(ilUtil::stripSlashes($_POST['description']));			
		$this->coupon_obj->setType(ilUtil::stripSlashes($_POST['coupon_type']));
		$this->coupon_obj->setValue(ilUtil::stripSlashes($_POST['coupon_value']));			
		$this->coupon_obj->setFromDate( date("Y-m-d",mktime(0,0,0,$_POST['from']['date']['m'],$_POST['from']['date']['d'],$_POST['from']['date']['y'])));
		$this->coupon_obj->setTillDate( date("Y-m-d",mktime(0,0,0,$_POST['til']['date']['m'],$_POST['til']['date']['d'],$_POST['til']['date']['y'])));		
	//	$this->coupon_obj->setFromDateEnabled(ilUtil::stripSlashes($_POST['from_check']));
	//$this->coupon_obj->setTillDateEnabled(ilUtil::stripSlashes($_POST['until_check']));

		$this->coupon_obj->setFromDateEnabled($_POST['from_check']);
		$this->coupon_obj->setTillDateEnabled($_POST['until_check']);
		$this->coupon_obj->setUses((int)ilUtil::stripSlashes($_POST['usage']));			
		$this->coupon_obj->setChangeDate(date('Y-m-d H:i:s'));				
		
		if ($this->error == '')
		{		
			if ($_GET['coupon_id'] != "")
			{	
				$this->coupon_obj->update();
			}
			else
			{
				$_GET['coupon_id'] = $this->coupon_obj->add();				 
			}
			
			ilUtil::sendInfo($this->lng->txt('saved_successfully'));
		}
		else
		{			
			if (is_array($e = explode(',', $this->error)))
			{				
				$mandatory = '';
				for ($i = 0; $i < count($e); $i++)
				{
					$e[$i] = trim($e[$i]);
					if ($e[$i] != '')
					{
						$mandatory .= $this->lng->txt($e[$i]);
						if (array_key_exists($i + 1, $e) && $e[$i + 1] != '') $mandatory .= ', ';
					}
				}
				ilUtil::sendInfo($this->lng->txt('fill_out_all_required_fields') . ': ' . $mandatory);
			}			
		}		
		
		$this->addCoupon();
		
		return true;
	}
	
	public function addCoupon()
	{		
		$this->tpl->addBlockfile('ADM_CONTENT','adm_content','tpl.main_view.html','Services/Payment');	
		
		if (isset($_GET['coupon_id']))
		{
			if ($this->error == '') $this->coupon_obj->getCouponById($_GET['coupon_id']);
			
			$this->ctrl->setParameter($this, 'coupon_id', $this->coupon_obj->getId());						
			
			$this->__showButtons();			
		}		

		
		$oForm = new ilPropertyFormGUI();
		$oForm->setId('frm_add_coupon');
		$oForm->setFormAction($this->ctrl->getFormAction($this,'saveCouponForm'));
		$oForm->setTitle($this->coupon_obj->getId() ? $this->lng->txt('paya_coupons_edit') : $this->lng->txt('paya_coupons_add'));

		// Title
		$oTitle = new ilTextInputGUI($this->lng->txt(paya_coupons_title),'title');
		$oTitle->setValue($this->coupon_obj->getTitle());
		$oTitle->setRequired(true);
		$oForm->addItem($oTitle);
		
		// Description
		$oDescription = new ilTextAreaInputGUI($this->lng->txt(paya_coupons_description),'description');
		$oDescription->setValue($this->coupon_obj->getDescription());
		$oForm->addItem($oDescription);
		
		// Type
		$o_coupon_type = new ilSelectInputGUI();
		$coupon_option = array('fix'=>$this->lng->txt('paya_coupons_fix'),'percent'=>$this->lng->txt('paya_coupons_percentaged'));

		$o_coupon_type->setTitle($this->lng->txt('coupon_type'));
		$o_coupon_type->setOptions($coupon_option);
		$o_coupon_type->setValue($this->coupon_obj->getType());		
		$o_coupon_type->setRequired(true);
		$o_coupon_type->setPostVar('coupon_type');
		
		$oForm->addItem($o_coupon_type);
		
		// Value
	
		$o_coupon_value = new ilNumberInputGUI($this->lng->txt('paya_coupons_value'),'coupon_value');
		$o_coupon_value->setSize(5);
		$o_coupon_value->allowDecimals(true);
		$o_coupon_value->setValue($this->coupon_obj->getValue());

		$o_coupon_value->setRequired(true);
		$oForm->addItem($o_coupon_value);
		
		// Date Valid From
		$o_from_check = new ilCheckBoxInputGUI($this->lng->txt('paya_coupons_from'),'from_check');
		$o_from_check->setValue(1);
		$o_from_check->setChecked($this->coupon_obj->getFromDateEnabled() ? 1 : 0);

		$o_date_from = new ilDateTimeInputGUI();
		$o_date_from->setPostVar('from');			
	
		$from_date = explode('-', $this->coupon_obj->getFromDate());
		$date_f['from']['date']['d'] = $from_date[2] != '00' ? $from_date[2] : '';
		$date_f['from']['date']['m'] = $from_date[1] != '00' ? $from_date[1] : '';
		$date_f['from']['date']['y'] = $from_date[0] != '0000' ? $from_date[0] : '';

		$_POST['from'] = $date_f['from'];
		if($this->coupon_obj->getFromDateEnabled() == '1') 
		{
			$o_date_from->checkInput();
		}

		$o_from_check->addSubItem($o_date_from);
		$oForm->addItem($o_from_check);
		
		// Date Valid Until
		$o_until_check = new ilCheckBoxInputGUI($this->lng->txt('paya_coupons_till'), 'until_check');
		$o_until_check->setValue(1);
		$o_until_check->setChecked($this->coupon_obj->getTillDateEnabled() ? 1 : 0);				

		$o_date_until = new ilDateTimeInputGUI();
		$o_date_until->setPostVar('til');
			
		$till_date = explode('-', $this->coupon_obj->getTillDate());
		$date_t['til']['date']['d']= $till_date[2] != '00' ? $till_date[2] : '';
		$date_t['til']['date']['m'] = $till_date[1] != '00' ? $till_date[1] : '';
		$date_t['til']['date']['y'] = $till_date[0] != '0000' ? $till_date[0] : '';
		
		$_POST['til'] = $date_t['til'];
		if($this->coupon_obj->getTillDateEnabled() == '1') 
		{
			$o_date_until->checkInput();	
		}
		
		$o_until_check->addSubItem($o_date_until);
		$oForm->addItem($o_until_check);	
		
		$o_usage = new ilNumberInputGUI($this->lng->txt('paya_coupons_availability'),'usage');
		$o_usage->setSize(5);
		$o_usage->setValue($this->coupon_obj->getUses());
		$oForm->addItem($o_usage);
		
		$oForm->addCommandButton('saveCouponForm', $this->lng->txt('save'));
		$oForm->addCommandButton('showCoupons', $this->lng->txt('cancel'));
		
		$this->tpl->setVariable('FORM',$oForm->getHTML());
	}
	
	public function deleteAllCodes()
	{		
		$this->showCodes("all");
		
		return true;
	}
	
	public function performDeleteAllCodes()
	{
		$this->coupon_obj->deleteAllCodesByCouponId($_GET['coupon_id']);	
		
		$this->showCodes();

		return true;
	}
	
	public function deleteCodes()
	{
		$_SESSION['paya_delete_codes'] = $_POST['codes'];
		
		if (!is_array($_POST['codes']))
		{
			ilUtil::sendInfo($this->lng->txt('paya_coupons_no_codes_selected'));
			
			$this->showCodes();

			return true;
		}
		
		$this->showCodes("selected");
		
		return true;
	}

	public function performDeleteCodes()
	{
		if (is_array($_SESSION['paya_delete_codes']))
		{			
			foreach($_SESSION['paya_delete_codes'] as $id)
			{
				$this->coupon_obj->deleteCode($id);
			}
		}
		unset($_SESSION['paya_delete_codes']);
		ilUtil::sendInfo($this->lng->txt('paya_coupons_code_deleted_successfully'));
		
		$this->showCodes();

		return true;
	}
	
	public function cancelDelete()
	{
		unset($_SESSION['paya_delete_codes']);
		
		$this->showCodes();

		return true;
	}
	
	public function showCodes($a_show_delete = "")
	{		
		$this->coupon_obj->setId($_GET['coupon_id']);
		
		if (!count($codes = $this->coupon_obj->getCodesByCouponId($_GET['coupon_id'])))
		{
			ilUtil::sendInfo($this->lng->txt('paya_coupons_codes_not_available'));			
			$this->generateCodes();			
			
			return true;
		}		
		
		$this->coupon_obj->getCouponById(ilUtil::stripSlashes($_GET['coupon_id']));
		
		$this->ctrl->setParameter($this, 'coupon_id', $_GET['coupon_id']);		
		$this->__showButtons();	

		$this->tpl->addBlockfile('ADM_CONTENT','adm_content','tpl.main_view.html','Services/Payment');	
		
		if($a_show_delete)
		{
			switch($a_show_delete)
			{
				case 'all': $del_cmd = 'performDeleteAllCodes';
							$del_info = $this->lng->txt('paya_coupons_sure_delete_all_codes');
						break;
				case 'selected': $del_cmd = 'performDeleteCodes';
							$del_info = $this->lng->txt('paya_coupons_sure_delete_selected_codes');
						break;
			}
			
			$oConfirmationGUI = new ilConfirmationGUI() ;
			// set confirm/cancel commands
			$oConfirmationGUI->setFormAction($this->ctrl->getFormAction($this, $del_cmd));
			$oConfirmationGUI->setHeaderText($del_info);
			$oConfirmationGUI->setCancel($this->lng->txt("cancel"), "cancelDelete");
			$oConfirmationGUI->setConfirm($this->lng->txt("confirm"), $del_cmd);			
	
			foreach ($codes as $code)
			{
				if(in_array($code['pcc_pk'],$_SESSION['paya_delete_codes']))
				{
					$oConfirmationGUI->addItem('',$code['pcc_code'], $code['pcc_code']);					
				}
			}
			
			$this->tpl->setVariable('CONFIRMATION', $oConfirmationGUI->getHTML());
			return true;
		}	

		$_SESSION['paya_delete_codes'] = $_SESSION['paya_delete_codes'] ? $_SESSION['paya_delete_codes'] : array();
		
		$counter = 0;
		foreach ($codes as $code)
		{
			$f_result[$counter]['coupon_id']	= ilUtil::formCheckbox(in_array($code['pcc_pk'], $_SESSION['paya_delete_codes']) ? 1 : 0,
										"codes[]", $code['pcc_pk']);
			$f_result[$counter]['coupon_code'] = $code['pcc_code'];
			$f_result[$counter]['usage_of_codes'] = $code['pcc_used']." ".strtolower($this->lng->txt('of'))." ".$this->coupon_obj->getUses();
						
			++$counter;
		}

		$tbl = new ilShopTableGUI($this, 'showCodes');
		$tbl->setTitle($this->lng->txt("paya_coupons_coupons"));
		$tbl->setId('tbl_show_codes');
		$tbl->setRowTemplate("tpl.shop_coupons_row.html", "Services/Payment");

		$tbl->addColumn('', 'coupon_id', '1%');
		$tbl->addColumn($this->lng->txt('paya_coupons_code'), 'coupon_code', '30%');
		$tbl->addColumn($this->lng->txt('paya_coupons_usage_of_codes'), 'usage_of_codes', '60%');

		$tbl->setSelectAllCheckbox('codes[]');

		$tbl->addCommandButton('generateCodes',$this->lng->txt('paya_coupons_generate_codes'));
		$tbl->addCommandButton('exportCodes',$this->lng->txt('export'));

		$tbl->addMultiCommand("deleteCodes", $this->lng->txt("delete"));

		$tbl->setData($f_result);
		$this->tpl->setVariable('TABLE', $tbl->getHTML());

		return true;
	}
	
	public function exportCodes()
	{
		$codes = $this->coupon_obj->getCodesByCouponId($_GET["coupon_id"]);
		
		if (is_array($codes))
		{
			include_once('./Services/Utilities/classes/class.ilCSVWriter.php');			
			
			$csv = new ilCSVWriter();
			$csv->setDelimiter("");			
			
			foreach($codes as $data)
			{							
				if ($data["pcc_code"])
				{					
					$csv->addColumn($data["pcc_code"]);
					$csv->addRow();					
				}
			}
			
			ilUtil::deliverData($csv->getCSVString(), "code_export_".date("Ymdhis").".csv");
		}
		
		$this->showCodes();
		
		return true;
	}
	
	public function saveCodeForm()
	{
		if (isset($_POST["generate_length"])) $_SESSION["pay_coupons"]["code_length"] = $_POST["generate_length"];
		else $_POST["generate_length"] = $_SESSION["pay_coupons"]["code_length"];
		
		if (isset($_POST["generate_type"])) $_SESSION["pay_coupons"]["code_type"] = $_POST["generate_type"];
		else $_POST["generate_type"] = $_SESSION["pay_coupons"]["code_type"];
		
		if ($_POST["generate_type"] == "self")
		{
			if ($_GET["coupon_id"] && is_array($_POST["codes"]))
			{				
				$count_inserts = 0;
				
				for ($i = 0; $i < count($_POST["codes"]); $i++)
				{
					$_POST["codes"][$i] = trim($_POST["codes"][$i]);
					
					if ($_POST["codes"][$i] != "")
					{					
						$code = $this->__makeCode($_POST["codes"][$i], $_SESSION["pay_coupons"]["code_length"]);
						
						if ($code != "")
						{
							if ($this->coupon_obj->addCode(ilUtil::stripSlashes($code), $_GET["coupon_id"]))
							{
								++$count_inserts;
							}
						}
					}
				}
				
				if ($count_inserts) 
				{
					ilUtil::sendInfo($this->lng->txt("saved_successfully"));
					$this->showCodes();
				}
				else
				{
					ilUtil::sendInfo($this->lng->txt("paya_coupons_no_codes_generated"));
					$this->generateCodes();
				}				
			}
			else if ((!is_numeric($_POST["generate_number"]) ||  $_POST["generate_number"] <= 0)
					|| (!is_numeric($_POST["generate_length"]) ||  $_POST["generate_length"] <= 0))  
			{
				ilUtil::sendInfo($this->lng->txt("fill_out_all_required_fields"));					
				
				$this->generateCodes();	
			}
			else
			{
				$this->generateCodes("input");
			}
		}
		else if ($_POST["generate_type"] == "auto")
		{
			if ($_GET["coupon_id"] && is_numeric($_POST["generate_number"]) && $_POST["generate_number"] > 0)
			{				
				for ($i = 0; $i < $_POST["generate_number"]; $i++)
				{
					$code = $this->__makeCode("", $_SESSION["pay_coupons"]["code_length"]);
					
					if ($code != "")
					{
						$this->coupon_obj->addCode($code, $_GET["coupon_id"]);
					}
				}
				
				ilUtil::sendInfo($this->lng->txt("saved_successfully"));
				
				$this->showCodes();					
			}
			else
			{	
				ilUtil::sendInfo($this->lng->txt("fill_out_all_required_fields"));					
				
				$this->generateCodes();
			}
		}
	}
	
	private function __makeCode($a_code = "", $a_length = 32)
	{
		if ($a_code == "") $a_code = md5(uniqid(rand()));
	
		if (is_numeric($a_length) && strlen($a_code) > $a_length)
		{
			$a_code = substr($a_code, 0, $a_length);
		}		
				
		return $a_code;
	}
	
	public function generateCodes($view = "choice")
	{		
		$this->coupon_obj->setId($_GET['coupon_id']);
		
		$this->ctrl->setParameter($this, 'coupon_id', $_GET['coupon_id']);
		$this->__showButtons();
		
		$this->coupon_obj->getCouponById(ilUtil::stripSlashes($_GET['coupon_id']));

		$this->tpl->addBlockfile('ADM_CONTENT','adm_content','tpl.main_view.html','Services/Payment');
		$oForm_1 = new ilPropertyFormGUI();
		$oForm_1->setId('save_frm');
		$oForm_1->setFormAction($this->ctrl->getFormAction($this),'saveCodeForm');
		$oForm_1->setTitle($this->lng->txt("paya_coupons_coupon")." ".$this->coupon_obj->getTitle().": ".$this->lng->txt('paya_coupons_code_generation'));
		
		if ($view == "choice")
		{
			$oTypeRadio = new ilRadioGroupInputGUI($this->lng->txt('paya_coupons_generate_codes'), 'generate_type');
			
			$radio_option = new ilRadioOption($this->lng->txt('paya_coupons_type_auto'), 'auto');
			$oTypeRadio->addOption($radio_option);
			$radio_option = new ilRadioOption($this->lng->txt('paya_coupons_type_self'), 'self');
			$oTypeRadio->addOption($radio_option);
			
			$oTypeRadio->setValue(isset($_POST["generate_type"]) ? $_POST["generate_type"] : "auto");
			$oTypeRadio->setPostVar('generate_type'); 
			$oForm_1->addItem($oTypeRadio);

			$oNumCodes = new ilNumberInputGUI($this->lng->txt("paya_coupons_number_of_codes"),'generate_number');
			$oNumCodes->setSize(5);
			$oNumCodes->setValue($_POST['generate_number']);
			$oNumCodes->setRequired(true);
			$oForm_1->addItem($oNumCodes);
					
			$oLength = new ilNumberInputGUI($this->lng->txt("paya_coupons_code_length"),'generate_length');
			$oLength->setSize(5);
			$oLength->setValue($_POST['generate_length']);
			$oLength->setRequired(true);
			$oLength->setInfo($this->lng->txt('paya_coupons_type_self'));
			$oForm_1->addItem($oLength);
			
			$oForm_1->addCommandButton('saveCodeForm',$this->lng->txt('save'));
			
			$this->tpl->setVariable('FORM', $oForm_1->getHTML());
		
			$oForm_2 = new ilPropertyformGUI();
			$oForm_2->setId('import_frm');
			$oForm_2->setFormAction($this->ctrl->getFormAction($this), 'showCodeImport');
			$oForm_2->addCommandButton('showCodeImport',$this->lng->txt('import'));
			$this->tpl->setVariable('FORM_2', $oForm_2->getHTML());
			
		}
		else if ($view == "input")
		{
			if (is_numeric($_POST['generate_number']) && $_POST['generate_number'] > 0)
			{
				for ($i = 0; $i < $_POST['generate_number']; $i++)
				{
					$index = $i +1;
					$oLoopCode = new ilTextInputGUI('#'.$index,'codes['.$i.']');
					$oForm_1->addItem($oLoopCode);
				}
					$oForm_1->addCommandButton('saveCodeForm',$this->lng->txt('save'));
			}
			
			$this->tpl->setVariable('FORM',$oForm_1->getHTML());
			
			$oLoopCode = new ilTextInputGUI();
		}
				
		return true;
	}
	
	public function assignObjects()
	{
		if (is_array($_POST['object_id']))
		{
			$this->coupon_obj->setId($_GET["coupon_id"]);		
			foreach($_POST['object_id'] as $id)
			{							
				if ($id)
				{					
					$this->coupon_obj->assignObjectToCoupon($id);
				}
			}			
		}
		
		$this->showObjects();
		
		return true;
	}
	
	public function unassignObjects()
	{
		if (is_array($_POST['object_id']))
		{			
			$this->coupon_obj->setId($_GET["coupon_id"]);				
			foreach($_POST['object_id'] as $id)
			{							
				if ($id)
				{
					$this->coupon_obj->unassignObjectFromCoupon($id);
				}
			}			
		}
		
		$this->showObjects();
		
		return true;
	}
	
	public function showObjects()
	{
		$this->coupon_obj->setId($_GET['coupon_id']);
		
		$this->__initPaymentObject();		
		
		$this->ctrl->setParameter($this, 'coupon_id', $_GET['coupon_id']);
		$this->__showButtons();
		
		$this->tpl->addBlockfile('ADM_CONTENT','adm_content','tpl.main_view.html','Services/Payment');		

		$objects = $this->pobject->_getObjectsData($this->user_obj->getId());

		$this->coupon_obj->getCouponById(ilUtil::stripSlashes($_GET['coupon_id']));
	
		$counter_assigned = 0;
		$counter_unassigned = 0;
		$f_result_assigned = array();
		$f_result_unassigned = array();
		foreach($objects as $data)
		{					
			if ($this->coupon_obj->isObjectAssignedToCoupon($data['ref_id']))
			{
				$p_counter =& $counter_assigned;
				$p_result =& $f_result_assigned;
			}
			else
			{
				$p_counter =& $counter_unassigned;
				$p_result =& $f_result_unassigned;
			}

			$tmp_obj = ilObjectFactory::getInstanceByRefId($data['ref_id'], false);
			if($tmp_obj)
			{
				$p_result[$p_counter]['object_id']	= ilUtil::formCheckbox(0, 'object_id[]', $data['ref_id']);
				$p_result[$p_counter]['title'] = $tmp_obj->getTitle();

				switch($data['status'])
				{
					case $this->pobject->STATUS_BUYABLE:
						$p_result[$p_counter]['status'] = $this->lng->txt('paya_buyable');
						break;

					case $this->pobject->STATUS_NOT_BUYABLE:
						$p_result[$p_counter]['status'] = $this->lng->txt('paya_not_buyable');
						break;

					case $this->pobject->STATUS_EXPIRES:
						$p_result[$p_counter]['status'] = $this->lng->txt('paya_expires');
						break;
				}
				include_once './Services/Payment/classes/class.ilPayMethods.php';
				$p_result[$p_counter]['pay_method'] = ilPaymethods::getStringByPaymethod($data['pay_method']);
			}
			else
			{
				$p_result[$p_counter]['object_id']	= '';
				$p_result[$p_counter]['title'] = $this->lng->txt('object_not_found');
				$p_result[$p_counter]['status'] = '';
				$p_result[$p_counter]['pay_method'] ='';
			}
			++$p_counter;				
							
			unset($tmp_obj);			
		}
		
		$this->ctrl->setParameter($this, "cmd", "showObjects");
	
		if (count($f_result_assigned) > 0)
		{	
			$tbl = new ilShopTableGUI($this);
			$tbl->setTitle($this->lng->txt("paya_coupons_coupon")." ".$this->coupon_obj->getTitle().": ".$this->lng->txt("paya_coupons_assigned_objects"));
			$tbl->setId('tbl_show_assigned');
			$tbl->setPrefix('assigned');
			$tbl->setRowTemplate("tpl.shop_objects_row.html", "Services/Payment");

			$tbl->addColumn('', 'object_id', '1%');
			$tbl->addColumn($this->lng->txt('title'),'title', '10%');
			$tbl->addColumn($this->lng->txt('status'), 'status', '30%');
			$tbl->addColumn($this->lng->txt('paya_pay_method'), 'paya_pay_method', '60%');

			$tbl->setSelectAllCheckbox('object_id');

			$tbl->addMultiCommand('unassignObjects',$this->lng->txt('remove'));


			$tbl->setData($f_result_assigned);
	
			$this->tpl->setVariable('TABLE', $tbl->getHTML());
		}
		
		if (count($f_result_unassigned) > 0)
		{		
			$tbl_2 = new ilShopTableGUI($this);
			$tbl_2->setTitle($this->lng->txt("paya_coupons_coupon")." ".$this->coupon_obj->getTitle().": ".$this->lng->txt("paya_coupons_unassigned_objects"));
			$tbl_2->setId('tbl_show_unassigned');
			$tbl_2->setPrefix('unassigned');
			$tbl_2->setRowTemplate("tpl.shop_objects_row.html", "Services/Payment");

			$tbl_2->addColumn('', 'object_id', '1%');
			$tbl_2->addColumn($this->lng->txt('title'),'title', '10%');
			$tbl_2->addColumn($this->lng->txt('status'), 'status', '30%');
			$tbl_2->addColumn($this->lng->txt('paya_pay_method'), 'pay_method', '60%');

			$tbl_2->setSelectAllCheckbox('object_id');

			$tbl_2->addMultiCommand('assignObjects',$this->lng->txt('add'));


			$tbl_2->setData($f_result_unassigned);
			
			$this->tpl->setVariable('TABLE_2', $tbl_2->getHTML());
		}
		
		return true;
	}
	
	public function importCodes()
	{	
		include_once('./Services/Utilities/classes/class.ilCSVReader.php');
		
		if ($_FILES["codesfile"]["tmp_name"] != "")
		{
			$csv = new ilCSVReader();
			$csv->setDelimiter("");
			
			if ($csv->open($_FILES["codesfile"]["tmp_name"]))
			{		
				$data = $csv->getDataArrayFromCSVFile();
				
				for ($i = 0; $i < count($data); $i++)
				{
					for ($j = 0; $j < count($data[$i]); $j++)
					{
						$this->coupon_obj->addCode(ilUtil::stripSlashes($data[$i][$j]), $_GET["coupon_id"]);	
					}
				}				
				
				ilUtil::sendInfo($this->lng->txt("paya_coupon_codes_import_successful"));
				
				$this->showCodes();
			}
			else
			{
				ilUtil::sendInfo($this->lng->txt("paya_coupons_import_error_opening_file"));
				
				$this->showCodeImport();
			}
		}
		else
		{
			ilUtil::sendInfo($this->lng->txt("fill_out_all_required_fields"));
			
			$this->showCodeImport();					
		}
		
		return true;
	}
	
	public function showCodeImport()
	{
		$this->ctrl->setParameter($this, 'coupon_id', $_GET['coupon_id']);
		$this->__showButtons();
		$this->tpl->addBlockfile('ADM_CONTENT','adm_content','tpl.main_view.html','Services/Payment');
		
		$this->coupon_obj->getCouponById(ilUtil::stripSlashes($_GET['coupon_id']));
		$oForm = new ilPropertyFormGUI();
		$oForm->setId('coup_form');
		$oForm->setFormAction($this->ctrl->getFormAction($this), 'importCodes');
		$oForm->setTitle( $this->lng->txt("paya_coupons_coupon")." ".$this->coupon_obj->getTitle().": ".$this->lng->txt('paya_coupons_codes_import'));
		
		$oFile = new ilFileInputGUI($this->lng->txt('file'),'codesfile');
		$oFile->setSuffixes(array("txt"));
		$oFile->setRequired(true);
		$oFile->setInfo($this->lng->txt('import_use_only_textfile'));
		$oForm->addItem($oFile);
		
		$oForm->addCommandButton('importCodes',$this->lng->txt('upload'));
		
		$this->tpl->setVariable('FORM', $oForm->getHTML());
		
		return true;
	}
	
	private function __showButtons()
	{		
		global $ilToolbar;

		$ilToolbar->addButton($this->lng->txt('paya_coupons_edit'), $this->ctrl->getLinkTarget($this, 'addCoupon'));
		$ilToolbar->addButton($this->lng->txt('paya_coupons_edit_codes'), $this->ctrl->getLinkTarget($this, 'showCodes'));
		$ilToolbar->addButton($this->lng->txt('paya_coupons_edit_objects'), $this->ctrl->getLinkTarget($this, 'showObjects'));
	
		return true;
	}
	
	private function __initPaymentObject($a_pobject_id = 0)
	{
		include_once './Services/Payment/classes/class.ilPaymentObject.php';

		$this->pobject = new ilPaymentObject($this->user_obj, $a_pobject_id);

		return true;
	}
	
	private function __initCouponObject()
	{
		include_once './Services/Payment/classes/class.ilPaymentCoupons.php';	

		$this->coupon_obj = new ilPaymentCoupons($this->user_obj, true);

		return true;
	}
	public function deleteCoupon()
	{
		$this->tpl->addBlockfile('ADM_CONTENT','adm_content','tpl.main_view.html','Services/Payment');	
		
		if (!isset($_GET['coupon_id']))
		{
			return ilUtil::sendFailure($this->lng->txt('no_coupon_selected'));
		}	

#		$this->ctrl->setParameter($this, 'coupon_id', $this->coupon_obj->getId());						

		$this->__showButtons();			

		ilUtil::sendQuestion($this->lng->txt('paya_coupons_sure_delete_selected_codes'));
		$oConfirmationGUI = new ilConfirmationGUI() ;
		// set confirm/cancel commands
		$oConfirmationGUI->setFormAction($this->ctrl->getFormAction($this, 'performDeleteCoupon'));		
		$oConfirmationGUI->setHeaderText('');
		$oConfirmationGUI->setCancel($this->lng->txt("cancel"), "showCoupons");
		$oConfirmationGUI->setConfirm($this->lng->txt("confirm"), 'performDeleteCoupon');			
		$oConfirmationGUI->addItem('','', ilPaymentCoupons::_lookupTitle($_GET['coupon_id']));					
		$oConfirmationGUI->addHiddenItem('coupon_id', $_GET['coupon_id']);
		$this->tpl->setVariable('CONFIRMATION', $oConfirmationGUI->getHTML());
		return true;	
		
	}
	
	public function performDeleteCoupon()
	{
		if (!isset($_POST['coupon_id']))
		{
			return ilUtil::sendFailure($this->lng->txt('coupon_id_is_missing'));
		}
		$this->coupon_obj->deleteCouponByCouponId((int)$_POST['coupon_id']);
		
		$this->showCoupons();
	}
}
?>
