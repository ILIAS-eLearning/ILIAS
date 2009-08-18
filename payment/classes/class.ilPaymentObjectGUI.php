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

include_once 'payment/classes/class.ilPaymentObject.php';
include_once 'payment/classes/class.ilPaymentBookings.php';
include_once 'Services/Payment/classes/class.ilFileDataShop.php';
include_once 'Services/Payment/classes/class.ilShopVatsList.php';

 
/**
* Class ilPaymentObjectGUI
*
* @author Stefan Meyer
* @version $Id$
* @ilCtrl_Calls ilPaymentObjectGUI: ilPageObjectGUI
*
* @package core
*/
class ilPaymentObjectGUI extends ilShopBaseGUI
{
	var $ctrl;
	var $lng;
	var $user_obj;	
	var $pobject = null;

	public function ilPaymentObjectGUI($user_obj)
	{	
		parent::__construct();

		$this->user_obj = $user_obj;
		$this->lng->loadLanguageModule('crs');
	}
	
	protected function prepareOutput()
	{
		global $ilTabs;
		
		$this->setSection(6);
		
		parent::prepareOutput();

		$ilTabs->setTabActive('paya_header');
		$ilTabs->setSubTabActive('paya_object');
	}
	
	public function executeCommand()
	{
		$cmd = $this->ctrl->getCmd();
		switch($this->ctrl->getNextClass($this))
		{
			case 'ilpageobjectgui':
				$this->prepareOutput();
				$ret = $this->forwardToPageObject();
				if($ret != '')
				{
					$this->tpl->setContent($ret);
				}				
				break;
			
			default:
				if(!$cmd = $this->ctrl->getCmd())
				{
					$cmd = 'showObjects';
				}
				$this->prepareOutput();
				$this->$cmd();
				break;
		}
	}
	
	public function forwardToPageObject()
	{	
		global $ilTabs;
		
		if(!(int)$_GET['pobject_id'])
		{
			ilUtil::sendInfo($this->lng->txt('paya_no_object_selected'));
			return $this->showObjects();
		}
		$this->ctrl->setParameter($this, 'pobject_id', (int)$_GET['pobject_id']);
		$this->__initPaymentObject((int)$_GET['pobject_id']);		
		
		$this->lng->loadLanguageModule('content');
		
		$ilTabs->clearTargets();
		$ilTabs->clearSubTabs();
		$ilTabs->setBackTarget($this->lng->txt('back'), $this->ctrl->getLinkTarget($this, 'editDetails'), '_top');

		// page objec
		include_once 'Services/COPage/classes/class.ilPageObject.php';
		include_once 'Services/COPage/classes/class.ilPageObjectGUI.php';
		include_once('./Services/Style/classes/class.ilObjStyleSheet.php');
		
		$this->tpl->setVariable('LOCATION_CONTENT_STYLESHEET', ilObjStyleSheet::getContentStylePath(0));

		if(!ilPageObject::_exists('shop', $this->pobject->getPobjectId()))
		{
			// doesn't exist -> create new one
			$new_page_object = new ilPageObject('shop');
			$new_page_object->setParentId(0);
			$new_page_object->setId($this->pobject->getPobjectId());
			$new_page_object->createFromXML();
		}
				
		$this->ctrl->setReturnByClass('ilpageobjectgui', 'edit');

		$page_gui = new ilPageObjectGUI('shop', $this->pobject->getPobjectId());
		$this->ctrl->setParameter($page_gui, 'pobject_id', (int)$_GET['pobject_id']);
		$page_gui->setIntLinkHelpDefault('StructureObject', $this->pobject->getPobjectId());
		$page_gui->setTemplateTargetVar('ADM_CONTENT');
		$page_gui->setLinkXML('');
		$page_gui->setFileDownloadLink($this->ctrl->getLinkTargetByClass(array('ilpageobjectgui'), 'downloadFile'));
		$page_gui->setFullscreenLink($this->ctrl->getLinkTargetByClass(array('ilpageobjectgui'), 'displayMediaFullscreen'));
		$page_gui->setSourcecodeDownloadScript($this->ctrl->getLinkTargetByClass(array('ilpageobjectgui'), 'download_paragraph'));
		$page_gui->setPresentationTitle('');
		$page_gui->setTemplateOutput(false);
		$page_gui->setHeader('');
		$page_gui->setEnabledRepositoryObjects(false);
		$page_gui->setEnabledFileLists(true);
		$page_gui->setEnabledMaps(true);
		$page_gui->setEnabledPCTabs(true);

		return $this->ctrl->forwardCommand($page_gui);
	}

	function showObjects()
	{
		$this->showButton('showObjectSelector', $this->lng->txt('paya_sell_object'));

		$this->tpl->addBlockfile('ADM_CONTENT', 'adm_content', 'tpl.paya_objects.html', 'payment');

		if(!count($objects = ilPaymentObject::_getObjectsData($this->user_obj->getId())))
		{
			ilUtil::sendInfo($this->lng->txt('paya_no_objects_assigned'));
			
			return true;
		}

		$this->__initPaymentObject();


		$img_change = "<img src=\"".ilUtil::getImagePath("edit.gif")."\" alt=\"".
			$this->lng->txt("edit")."\" title=\"".$this->lng->txt("edit").
			"\" border=\"0\" vspace=\"0\"/>";

		$counter = 0;
		foreach($objects as $data)
		{

			$tmp_obj =& ilObjectFactory::getInstanceByRefId($data['ref_id']);
			$f_result[$counter][] = $tmp_obj->getTitle();


			switch($data['status'])
			{
				case $this->pobject->STATUS_BUYABLE:
					$f_result[$counter][] = $this->lng->txt('paya_buyable');
					break;

				case $this->pobject->STATUS_NOT_BUYABLE:
					$f_result[$counter][] = $this->lng->txt('paya_not_buyable');
					break;
					
				case $this->pobject->STATUS_EXPIRES:
					$f_result[$counter][] = $this->lng->txt('paya_expires');
					break;
			}
			switch($data['pay_method'])
			{
				case $this->pobject->PAY_METHOD_NOT_SPECIFIED:
					$f_result[$counter][] = $this->lng->txt('paya_pay_method_not_specified');
					break;

				case $this->pobject->PAY_METHOD_BILL:
					$f_result[$counter][] = $this->lng->txt('pays_bill');
					break;

				case $this->pobject->PAY_METHOD_BMF:
					$f_result[$counter][] = $this->lng->txt('pays_bmf');
					break;

				case $this->pobject->PAY_METHOD_PAYPAL:
					$f_result[$counter][] = $this->lng->txt('pays_paypal');
					break;
			}

			if($data['vat_id'] <= 0)
			{
			
			 	$vat_rate = $this->lng->txt('payment_vat_has_to_be_defined_by_administration_short');
			}
			else 
			{
				
				try
				{
					$oVAT = new ilShopVats((int)$data['vat_id']);
					$vat_rate = ilShopUtils::_formatVAT((float)$oVAT->getRate()); 
				}
				catch(ilShopException $e)
				{
					$vat_rate = $this->lng->txt('payment_vat_has_to_be_defined_by_administration_short');		
				}
				
			}


			$f_result[$counter][] = $vat_rate;

			
			$tmp_user =& ilObjectFactory::getInstanceByObjId($data['vendor_id']);
			$f_result[$counter][] = $tmp_user->getFullname().' ['.$tmp_user->getLogin().']';

			// Get number of purchasers
			
			$f_result[$counter][] = ilPaymentBookings::_getCountBookingsByObject($data['pobject_id']);


			// edit link
			$this->ctrl->setParameter($this,'pobject_id',$data['pobject_id']);
#			$link_change = "<a href=\"".$this->ctrl->getLinkTarget($this,"editDetails")."\"> ".
#				$img_change."</a>";
			$link_change = "<div class=\"il_ContainerItemCommands\"><a class=\"il_ContainerItemCommand\" href=\"".$this->ctrl->getLinkTarget($this,"editDetails")."\">".$this->lng->txt("edit")."</a></div>";

			$f_result[$counter][] = $link_change;
			unset($tmp_user);
			unset($tmp_obj);

			++$counter;
		}

		return $this->__showObjectsTable($f_result);
	}

	function editDetails($a_show_confirm = false)
	{
		global $ilToolbar;
		
		if(!(int)$_GET['pobject_id'])
		{	
			ilUtil::sendInfo($this->lng->txt('paya_no_object_selected'));
			return $this->showObjects();
		}
			
		$this->__initPaymentObject((int)$_GET['pobject_id']);

		$this->ctrl->setParameter($this,'pobject_id', (int)$_GET['pobject_id']);
	
		$this->showButton('editDetails', $this->lng->txt('paya_edit_details'));

		$this->showButton('editPrices', $this->lng->txt('paya_edit_prices'));	

		$ilToolbar->addButton($this->lng->txt('pay_edit_abstract'), $this->ctrl->getLinkTargetByClass(array('ilpageobjectgui'), 'edit'));
		
		$this->tpl->addBlockfile('ADM_CONTENT','adm_content','tpl.paya_edit.html','payment');	
		$this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.shop_content.html', 'Services/Payment');	

		if($a_show_confirm)
		{
			$this->tpl->setCurrentBlock('confirm_delete');
			$this->tpl->setVariable('CONFIRM_FORMACTION',$this->ctrl->getFormAction($this));
			$this->tpl->setVariable('TXT_CANCEL',$this->lng->txt('cancel'));
			$this->tpl->setVariable('CONFIRM_CMD','performDelete');
			$this->tpl->setVariable('TXT_CONFIRM',$this->lng->txt('confirm'));
			$this->tpl->parseCurrentBlock();
		}
		
		$tmp_obj = ilObjectFactory::getInstanceByRefId($this->pobject->getRefId());

		include_once 'Services/Form/classes/class.ilPropertyFormGUI.php';
		$oForm = new ilPropertyFormGUI();
		$oForm->setFormAction($this->ctrl->getFormAction($this, 'updateDetails'));
		$oForm->setTitle($tmp_obj->getTitle());
		$oForm->setTitleIcon(ilUtil::getImagePath('icon_'.$tmp_obj->getType().'_b.gif'));
		
		// repository path
		$oPathGUI = new ilNonEditableValueGUI($this->lng->txt('path'));
		$oPathGUI->setValue($this->__getHTMLPath($this->pobject->getRefId()));
		$oForm->addItem($oPathGUI);
		
		// number of purchasers
		$oPurchasersGUI = new ilNonEditableValueGUI($this->lng->txt('paya_count_purchaser'));
		$oPurchasersGUI->setValue(ilPaymentBookings::_getCountBookingsByObject((int)$_GET['pobject_id']));
		$oForm->addItem($oPurchasersGUI);
		
		// vendors
		$oVendorsGUI = new ilSelectInputGUI($this->lng->txt('paya_vendor'), 'vendor');		
		$oVendorsGUI->setOptions($this->__getVendors());
		$oVendorsGUI->setValue($this->pobject->getVendorId());
		$oForm->addItem($oVendorsGUI);
		
		// status
		$oStatusGUI = new ilSelectInputGUI($this->lng->txt('status'), 'status');
		$oStatusGUI->setOptions($this->__getStatus());
		$oStatusGUI->setValue($this->pobject->getStatus());
		$oForm->addItem($oStatusGUI);
		
		// pay methods
		$oPayMethodsGUI = new ilSelectInputGUI($this->lng->txt('paya_pay_method'), 'pay_method');
		$oPayMethodsGUI->setOptions($this->__getPayMethods());
		$oPayMethodsGUI->setValue($this->pobject->getPayMethod());
		$oForm->addItem($oPayMethodsGUI);		
		
		// topics
		ilShopTopics::_getInstance()->read();
		if(is_array($topics = ilShopTopics::_getInstance()->getTopics()) && count($topics))
		{
			$oTopicsGUI = new ilSelectInputGUI($this->lng->txt('topic'), 'topic_id');
			include_once 'Services/Payment/classes/class.ilShopTopics.php';
			ilShopTopics::_getInstance()->read();
			$topic_options = array();
			$topic_options[''] = $this->lng->txt('please_choose');
			
			foreach($topics as $oTopic)
			{			
				$topic_options[$oTopic->getId()] = $oTopic->getTitle();
			}
			
			$oTopicsGUI->setOptions($topic_options);
			$oTopicsGUI->setValue($this->pobject->getTopicId());
			$oForm->addItem($oTopicsGUI);
		}
		
		// vats
		$oShopVatsList = new ilShopVatsList();
		$oShopVatsList->read();			
		if($oShopVatsList->hasItems())
		{
			$oVatsGUI = new ilSelectInputGUI($this->lng->txt('vat_rate'), 'vat_id');

			$vats_options = array();				
			foreach($oShopVatsList as $oVAT)
			{	
				$vats_options[$oVAT->getId()] = ilShopUtils::_formatVAT($oVAT->getRate()).' -> '.$oVAT->getTitle();
			}
		
			$oVatsGUI->setOptions($vats_options);
			$oVatsGUI->setValue($this->pobject->getVatId());
			$oForm->addItem($oVatsGUI);
		}
		else
		{
			$oVatsGUI = new ilNonEditableValueGUI($this->lng->txt('vat_rate'));		
			$oVatsGUI->setValue($this->lng->txt('paya_no_vats_assigned'));	
			$oForm->addItem($oVatsGUI);	
		}						
		
		$oThumbnail = new ilImageFileInputGUI($this->lng->txt('pay_thumbnail'), 'thumbnail');
		$oFile = new ilFileDataShop($this->pobject->getPobjectId());
		if(($webpath_file = $oFile->getCurrentImageWebPath()) !== false)
		{
			$oThumbnail->setImage($webpath_file);
		}
		$oForm->addItem($oThumbnail);
		
		// buttons
		$oForm->addCommandButton('updateDetails', $this->lng->txt('save'));
		$oForm->addCommandButton('deleteObject', $this->lng->txt('delete'));		

		$this->tpl->setVariable('PAYMENT_OBJECT_FORM', $oForm->getHTML());
	}

	function deleteObject()
	{
		include_once './payment/classes/class.ilPaymentBookings.php';

		if(!$_GET['pobject_id'])
		{
			ilUtil::sendInfo($this->lng->txt('paya_no_object_selected'));

			$this->showObjects();
			return true;
		}
		if(ilPaymentBookings::_getCountBookingsByObject((int) $_GET['pobject_id']))
		{
			ilUtil::sendInfo($this->lng->txt('paya_bookings_available'));
			$this->editDetails();

			return false;
		}
		else
		{
			ilUtil::sendInfo($this->lng->txt('paya_sure_delete_object'));
			$this->editDetails(true);

			return true;
		}
	}

	function performDelete()
	{
		include_once './payment/classes/class.ilPaymentPrices.php';
		include_once './payment/classes/class.ilPaymentBillVendor.php';

		if(!$_GET['pobject_id'])
		{
			ilUtil::sendInfo($this->lng->txt('paya_no_object_selected'));

			$this->showObjects();
			return true;
		}
		$this->__initPaymentObject((int) $_GET['pobject_id']);

		// delete object data
		$this->pobject->delete();
		
		// delete payment prices
		$price_obj =& new ilPaymentPrices((int) $_GET['pobject_id']);
		$price_obj->deleteAllPrices();
		unset($price_obj);

		$bv =& new ilPaymentBillVendor((int) $_GET['pobject_id']);
		$bv->delete();
		unset($bv);

		// delete bill vendor data if exists
		ilUtil::sendInfo($this->lng->txt('paya_deleted_object'));

		$this->showObjects();

		return true;
	}

	function editPayMethod()
	{
		$this->__initPaymentObject((int) $_GET['pobject_id']);

		switch($this->pobject->getPayMethod())
		{
			case $this->pobject->PAY_METHOD_NOT_SPECIFIED:
				ilUtil::sendInfo($this->lng->txt('paya_select_pay_method_first'));
				$this->editDetails();
				
				return true;
			case $this->pobject->PAY_METHOD_BILL:
				ilUtil::sendInfo($this->lng->txt('paya_no_settings_necessary'));
				$this->editDetails();
				
				return true;				
			case $this->pobject->PAY_METHOD_BMF:
				ilUtil::sendInfo($this->lng->txt('paya_no_settings_necessary'));
				$this->editDetails();
				
				return true;
			case $this->pobject->PAY_METHOD_PAYPAL:
				ilUtil::sendInfo($this->lng->txt('paya_no_settings_necessary'));
				$this->editDetails();
				
				return true;
		}
		
		$this->editDetails();
		
		return true;
	}	
	
	function editPrices($a_show_delete = false)
	{
		if($a_show_delete == false) unset($_SESSION['price_ids']);

		include_once './payment/classes/class.ilPaymentPrices.php';
		include_once './payment/classes/class.ilPaymentCurrency.php';
		include_once './Services/Table/classes/class.ilTableGUI.php';
		include_once './payment/classes/class.ilGeneralSettings.php';

		$genSet = new ilGeneralSettings();

		$_SESSION['price_ids'] = $_SESSION['price_ids'] ? $_SESSION['price_ids'] : array();

		if(!$_GET['pobject_id'])
		{
			ilUtil::sendInfo($this->lng->txt('paya_no_object_selected'));

			$this->objectsObject();
			return true;
		}
		$this->ctrl->setParameter($this,'pobject_id',(int) $_GET['pobject_id']);


		$this->__showButton('showObjects',$this->lng->txt('back'));	
		$this->__showButton('editDetails',$this->lng->txt('paya_edit_details'));
		$this->__showButton('editPrices',$this->lng->txt('paya_edit_prices'));

		// edit abstract
		$this->tpl->setCurrentBlock('btn_cell');
		$this->tpl->setVariable('BTN_LINK', $this->ctrl->getLinkTargetByClass(array('ilpageobjectgui'), 'edit'));
		$this->tpl->setVariable('BTN_TXT', $this->lng->txt('pay_edit_abstract'));		
		$this->tpl->parseCurrentBlock();		
		
		$this->__initPaymentObject((int) $_GET['pobject_id']);

		$this->tpl->addBlockfile("ADM_CONTENT", "adm_content",'tpl.paya_edit_prices.html','payment');
		$price_obj =& new ilPaymentPrices((int) $_GET['pobject_id']);
		$prices = $price_obj->getPrices();

		// No prices created
		if(!count($prices))
		{
			ilUtil::sendInfo($this->lng->txt('paya_no_price_available'));

			$this->tpl->setCurrentBlock('price_info');
			$this->tpl->setVariable('CONFIRM_FORMACTION',$this->ctrl->getFormAction($this));
			$this->tpl->setVariable('CONFIRM_CMD','addPrice');
			$this->tpl->setVariable('TXT_CONFIRM',$this->lng->txt('paya_add_price'));
			$this->tpl->parseCurrentBlock();
			
			return true;
		}
		// Show confirm delete
		if($a_show_delete)
		{	
			ilUtil::sendInfo($this->lng->txt('paya_sure_delete_selected_prices'));

			$this->tpl->setCurrentBlock('cancel');
			$this->tpl->setVariable('CANCEL_CMD','editPrices');
			$this->tpl->setVariable('TXT_CANCEL',$this->lng->txt('cancel'));
			$this->tpl->parseCurrentBlock();

			$this->tpl->setCurrentBlock('price_info');
			$this->tpl->setVariable('CONFIRM_FORMACTION',$this->ctrl->getFormAction($this));
			$this->tpl->setVariable('CONFIRM_CMD','performDeletePrice');
			$this->tpl->setVariable('TXT_CONFIRM',$this->lng->txt('paya_delete_price'));
			$this->tpl->parseCurrentBlock();
			
		}			

		// Fill table cells
		$tpl =& new ilTemplate('tpl.table.html',true,true);

		// set table header
		$tpl->setCurrentBlock('tbl_form_header');
		
		$tpl->setVariable('FORMACTION',$this->ctrl->getFormAction($this));
		$tpl->parseCurrentBlock();

		$counter = 0;
		foreach($prices as $price)
		{
			$currency = ilPaymentCurrency::_getCurrency($price['currency']);
			if($a_show_delete == true ) 
			{	
				$this->ctrl->setParameter($this, 'show_delete', 'true');
				
				if(in_array($price['price_id'],$_SESSION['price_ids']))
				{
			
					$data[$counter]['price_id'] = '';
					$data[$counter]['duration'] =$price['duration']  ;
					$data[$counter]['month'] = $this->lng->txt('paya_months');
					
					$data[$counter]['unlimited_duration'] = ilUtil::formCheckBox($price['unlimited_duration'] ? 1 : 0,
						'duration_ids[]', (int)$price['price_id']);	
					
					$data[$counter]['price'] =  ilFormat::_getLocalMoneyFormat($price['price']);
					$data[$counter]['currency_unit'] = $genSet->get('currency_unit');
				}
			}
			else
			{
				$data[$counter]['price_id'] = ilUtil::formCheckBox(in_array($price['price_id'],$_SESSION['price_ids']) ? 1 : 0,
					'price_ids[]', $price['price_id']);	
				
				$data[$counter]['duration'] = ilUtil::formInput('prices['.$price['price_id'].'][duration]',$price['duration']);
				$data[$counter]['month'] = $this->lng->txt('paya_months');
				
				$data[$counter]['unlimited_duration'] = ilUtil::formCheckBox($price['unlimited_duration'] ? 1 : 0,
					'duration_ids[]', (int)$price['price_id']);	
				
				$data[$counter]['price'] = ilUtil::formInput('prices['.$price['price_id'].'][price]', ilFormat::_getLocalMoneyFormat($price['price']));
				$data[$counter]['currency_unit'] = $genSet->get('currency_unit');
			}
			++$counter;
		}
		$this->__editPricesTable($data);	
	
		return true;
	}	
		
	function __editPricesTable($a_result_set)
	{
		$tpl =& new ilTemplate('tpl.table.html',true,true);
		
		$parmeter = $this->ctrl->getParameterArray($this, 'show_delete');
		
		if(!$parmeter['show_delete'])
		{
			// SET FOOTER
			$tpl->setCurrentBlock("tbl_action_btn");
			$tpl->setVariable("BTN_NAME","deletePrice");
			$tpl->setVariable("BTN_VALUE",$this->lng->txt("paya_delete_price"));
			$tpl->parseCurrentBlock();
	
			$tpl->setCurrentBlock("plain_buttons");
			$tpl->setVariable("PBTN_NAME","addPrice");
			$tpl->setVariable("PBTN_VALUE",$this->lng->txt("paya_add_price"));
			$tpl->parseCurrentBlock();
			
			$tpl->setCurrentBlock("plain_buttons");
			$tpl->setVariable("PBTN_NAME","updatePrice");
			$tpl->setVariable("PBTN_VALUE",$this->lng->txt("paya_update_price"));
			$tpl->parseCurrentBlock();
			
			$tpl->setCurrentBlock("tbl_action_row");
			$tpl->setVariable("TPLPATH",$this->tpl->tplPath);
			$tpl->setVariable("COLUMN_COUNTS",6);
			$tpl->setVariable("IMG_ARROW", ilUtil::getImagePath("arrow_downright.gif"));
			$tpl->parseCurrentBlock();
		}
		$tbl = new ilTableGUI();
		$tbl->setTemplate($tpl);

		// SET FORMAACTION
		$tpl->setCurrentBlock("tbl_form_header");
		$this->ctrl->setParameter($this, 'cmd', 'editprices');
		
		$tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$tpl->parseCurrentBlock();

		$tmp_obj =& ilObjectFactory::getInstanceByRefId($this->pobject->getRefId());		
		$tbl->setTitle($tmp_obj->getTitle(),
					   'icon_'.$tmp_obj->getType().'_b.gif',
					   $this->lng->txt('objs_'.$tmp_obj->getType()));
					   		
		$tbl->setHeaderNames(array( '',
									$this->lng->txt("duration"),
								  	'',
								 	$this->lng->txt("unlimited_duration"),
								   	$this->lng->txt("price_a"),
								  	''),
								   	'');
		$header_params = $this->ctrl->getParameterArray($this,'');
		$tbl->setHeaderVars(array(	"price_id",
									"duration",
									"month",
									"unlimited_duration",
									"price",
									"currency_unit",
									"options"),$header_params);
		$tbl->setColumnWidth(array('5%', "10%","10%","15%","15%","50%"));

		$offset = $_GET["offset"];
		if($_GET["sort_by"] == NULL) $order = 'duration'; 
		else $order = $_GET["sort_by"]; 
		$direction = $_GET["sort_order"]; 

		$tbl->setOrderColumn($order);
		$tbl->setOrderDirection($direction);
		$tbl->setOffset($offset);
		$tbl->setLimit($_GET["limit"]);
		$tbl->setMaxCount(count($a_result_set));
		$tbl->setFooter("tblfooter",$this->lng->txt("previous"),$this->lng->txt("next"));
		$tbl->setData($a_result_set);

		$tbl->render();

		$this->tpl->setVariable("PRICES_TABLE",$tbl->tpl->get());

		return true;
		
	}	
	
	function addPrice()
	{
		if(!$_GET['pobject_id'])
		{
			ilUtil::sendInfo($this->lng->txt('paya_no_object_selected'));

			$this->showObjects();
			return true;
		}

		include_once './payment/classes/class.ilGeneralSettings.php';

		$genSet = new ilGeneralSettings();

		$this->ctrl->setParameter($this,'pobject_id',(int) $_GET['pobject_id']);

		$this->__initPaymentObject((int) $_GET['pobject_id']);

		$this->showButton('editDetails',$this->lng->txt('paya_edit_details'));
		$this->showButton('editPrices',$this->lng->txt('paya_edit_prices'));
#		$this->__showPayMethodLink();

		$this->tpl->addBlockfile('ADM_CONTENT','adm_content','tpl.paya_add_price.html','payment');

		$this->tpl->setVariable('ADD_FORMACTION',$this->ctrl->getFormAction($this));

		$tmp_obj =& ilObjectFactory::getInstanceByRefId($this->pobject->getRefId());
		$this->tpl->setVariable('TYPE_IMG',ilUtil::getImagePath('icon_'.$tmp_obj->getType().'_b.gif'));
		$this->tpl->setVariable('ALT_IMG',$this->lng->txt('obj_'.$tmp_obj->getType()));
		$this->tpl->setVariable('TITLE',$tmp_obj->getTitle());
		$this->tpl->setVariable('DESCRIPTION',$this->lng->txt('paya_add_price_title'));
		
		// TODO show curency selector
#		$this->tpl->setVariable('TXT_PRICE_A',$this->lng->txt('currency_euro'));
#		$this->tpl->setVariable('TXT_PRICE_B',$this->lng->txt('currency_cent'));
		$this->tpl->setVariable('TXT_PRICE_A',$genSet->get('currency_unit'));
		//$this->tpl->setVariable('TXT_PRICE_B',$genSet->get('currency_subunit'));
		
		$this->tpl->setVariable('MONTH',$this->lng->txt('paya_months'));
		$this->tpl->setVariable('TXT_UNLIMITED_DURATION', $this->lng->txt('unlimited_duration'));

		$this->tpl->setVariable('TXT_DURATION',$this->lng->txt('duration'));
		$this->tpl->setVariable('TXT_PRICE',$this->lng->txt('price_a'));
		$this->tpl->setVariable('CANCEL',$this->lng->txt('cancel'));
		$this->tpl->setVariable('ADD',$this->lng->txt('paya_add_price'));
		
		$this->tpl->setVariable('DURATION',$_POST['duration']);
		$this->tpl->setVariable('UNLIMITED_DURATION', $_POST['unlimited_duration']);

		$this->tpl->setVariable('PRICE',$_POST['price']);
		return true;
	}

	function performAddPrice()
	{

		if(!$_GET['pobject_id'])
		{
			ilUtil::sendInfo($this->lng->txt('paya_no_object_selected'));

			$this->showObjects();
			return true;
		}

		include_once './payment/classes/class.ilPaymentPrices.php';
		include_once './payment/classes/class.ilPaymentCurrency.php';

		$currency = ilPaymentCurrency::_getAvailableCurrencies();

		$prices =& new ilPaymentPrices((int) $_GET['pobject_id']);

		$prices->setDuration($_POST['duration']);
		$prices->setUnlimitedDuration($_POST['unlimited_duration']);
		if($_POST['unlimited_duration'] == '1')
		{
			$prices->setUnlimitedDuration(1);
		}

		$prices->setPrice($_POST['price']);
		$prices->setCurrency($currency[1]['currency_id']);
		

		if(!$prices->validate())
		{
			ilUtil::sendInfo($this->lng->txt('paya_price_not_valid'));
			$this->addPrice();

			return true;
		}
		$prices->add();

		ilUtil::sendInfo($this->lng->txt('paya_added_new_price'));
		$this->editPrices();

		return true;
	}

	function performDeletePrice()
	{
		if(!$_GET['pobject_id'])
		{
			ilUtil::sendInfo($this->lng->txt('paya_no_object_selected'));

			$this->showObjects();
			return true;
		}

		if(!count($_SESSION['price_ids']))
		{
			ilUtil::sendInfo($this->lng->txt('paya_no_prices_selected'));
			
			$this->editPrices();
			return true;
		}
		include_once './payment/classes/class.ilPaymentPrices.php';
		
		$prices =& new ilPaymentPrices((int) $_GET['pobject_id']);

		foreach($_SESSION['price_ids'] as $price_id)
		{
			$prices->delete($price_id);
		}

		// check if it was last price otherwise set status to 'not_buyable'
		if(!count($prices->getPrices()))
		{
			$this->__initPaymentObject((int) $_GET['pobject_id']);

			$this->pobject->setStatus($this->pobject->STATUS_NOT_BUYABLE);
			$this->pobject->update();
			
			ilUtil::sendInfo($this->lng->txt('paya_deleted_last_price'));
		}
		unset($prices);
		unset($_SESSION['price_ids']);
		
		return $this->editPrices();
	}


	function deletePrice()
	{
		if(!$_GET['pobject_id'])
		{
			ilUtil::sendInfo($this->lng->txt('paya_no_object_selected'));

			$this->showObjects();
			return true;
		}

		if(!count($_POST['price_ids']))
		{
			ilUtil::sendInfo($this->lng->txt('paya_no_prices_selected'));
			
			$this->editPrices();
			return true;
		}
		$_SESSION['price_ids'] = $_POST['price_ids'];

		$this->editPrices(true);
		return true;
	}		

	function updatePrice()
	{
		include_once './payment/classes/class.ilPaymentPrices.php';

		if(!$_GET['pobject_id'])
		{
			ilUtil::sendInfo($this->lng->txt('paya_no_object_selected'));

			$this->showObjects();
			return true;
		}
		$po =& new ilPaymentPrices((int) $_GET['pobject_id']);

		$this->ctrl->setParameter($this,'pobject_id',(int) $_GET['pobject_id']);

		// validate
		foreach($_POST['prices'] as $price_id => $price)
		{
			$old_price = $po->getPrice($price_id);

			$po->setDuration($price['duration']);
			$po->setUnlimitedDuration($price['unlimited_duration']);
			$po->setPrice($price['price']);			
			$po->setCurrency($old_price['currency']);

			if(!$po->validate())
			{
				$error = true;
			}
		}
		if($error)
		{
			ilUtil::sendInfo($this->lng->txt('paya_insert_only_numbers'));

			$this->editPrices();
			return false;
		}
		foreach($_POST['prices'] as $price_id => $price)
		{
			$old_price = $po->getPrice($price_id);
			if(isset($_POST['duration_ids']))
			{
			
	 			$search = in_array((string)$price_id, $_POST['duration_ids']);
	

				 if($_POST['duration_ids'] == NULL)
				{
					$po->setUnlimitedDuration(0);		
					$po->setDuration($price['duration']);	
							
				}

				else if( $search = in_array((string)$price_id, $_POST['duration_ids']))
				{
				
					$po->setUnlimitedDuration(1);		
					$po->setDuration(0);	
				}
				else 
				{
					$po->setUnlimitedDuration(0);	
				}	
			}
			
			$po->setDuration($price['duration']);

			$po->setPrice($price['price']);
			$po->setCurrency($old_price['currency']);

			$po->update($price_id);
		}
		ilUtil::sendInfo($this->lng->txt('paya_updated_prices'));
		$this->editPrices();

		return true;
	}
		

	function updateDetails()
	{
		
		
		if(!$_GET['pobject_id'])
		{
			ilUtil::sendInfo($this->lng->txt('paya_no_object_selected'));

			$this->showObjects();
			return true;
		}
		$this->__initPaymentObject((int)$_GET['pobject_id']);
		$this->ctrl->setParameter($this, 'pobject_id', (int)$_GET['pobject_id']);		

		// read old settings
		$old_pay_method = $this->pobject->getPayMethod();
		$old_status = $this->pobject->getStatus();
		

		// check status changed from not_buyable
		if($old_status == $this->pobject->STATUS_NOT_BUYABLE and
		   (int) $_POST['status'] != $old_status)
		{
			// check pay_method edited
			switch((int) $_POST['pay_method'])
			{
				case $this->pobject->PAY_METHOD_NOT_SPECIFIED:
					ilUtil::sendInfo($this->lng->txt('paya_select_pay_method_first'));
					$this->editDetails();

					return false;

				default:
					;
			}
			// check minimum one price
			include_once './payment/classes/class.ilPaymentPrices.php';

			$prices_obj =& new ilPaymentPrices((int) $_GET['pobject_id']);
			if(!count($prices_obj->getPrices()))
			{
				ilUtil::sendInfo($this->lng->txt('paya_edit_prices_first'));
				$this->editDetails();
						
				return false;
			}
			
		}
		
		$this->pobject->setStatus((int)$_POST['status']);
		$this->pobject->setVendorId((int)$_POST['vendor']);
		$this->pobject->setPayMethod((int)$_POST['pay_method']);
		$this->pobject->setTopicId((int)$_POST['topic_id']);
		$this->pobject->setVatId((int)$_POST['vat_id']);
		//$this->pobject->setVatRate((float) $_POST['vat_rate']);

		if((int)$_POST['thumbnail_delete'])
		{
			$oFile = new ilFileDataShop($this->pobject->getPobjectId());
			$oFile->deassignFileFromPaymentObject();
		}
		else if($_FILES['thumbnail']['tmp_name'] != '')
		{
			$this->lng->loadLanguageModule('form');
			include_once 'Services/Form/classes/class.ilPropertyFormGUI.php';
			$oThumbnail = new ilImageFileInputGUI($this->lng->txt('pay_thumbnail'), 'thumbnail');
			if($oThumbnail->checkInput())
			{
				$oFile = new ilFileDataShop($this->pobject->getPobjectId());
				if(($oFile->storeUploadedFile($_FILES['thumbnail'])) !== false)
				{
					$oFile->assignFileToPaymentObject();
				}
			}
			else
			{
				ilUtil::sendInfo($oThumbnail->getAlert());	
				return $this->editDetails();
			}	
		}
		
		$this->pobject->update();

		ilUtil::sendInfo($this->lng->txt('paya_details_updated'));
		//$this->editDetails();
		$this->showObjects();

		return true;
	}

	function showObjectSelector()
	{
		global $tree;

		include_once './payment/classes/class.ilPaymentObjectSelector.php';

		$this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.paya_object_selector.html','payment');
		$this->showButton('showObjects',$this->lng->txt('back'));


		ilUtil::sendInfo($this->lng->txt('paya_select_object_to_sell'));

		$exp = new ilPaymentObjectSelector($this->ctrl->getLinkTarget($this,'showObjectSelector'), strtolower(get_class($this)));
		$exp->setExpand($_GET['paya_link_expand'] ? $_GET['paya_link_expand'] : $tree->readRootId());
		$exp->setExpandTarget($this->ctrl->getLinkTarget($this,'showObjectSelector'));
		
		$exp->setOutput(0);

		$this->tpl->setVariable("EXPLORER",$exp->getOutput());

		return true;
	}

	function showSelectedObject()
	{
		if(!(int)$_GET['sell_id'])
		{
			ilUtil::sendInfo($this->lng->txt('paya_no_object_selected'));			
			return $this->showObjectSelector();
		}
		
		$this->showButton('showObjectSelector',$this->lng->txt('back'));
		
		// save ref_id of selected object
		$this->ctrl->setParameter($this, 'sell_id', (int)$_GET['sell_id']);
		
		include_once 'Services/Form/classes/class.ilPropertyFormGUI.php';
		$oForm = new ilPropertyFormGUI();
		$oForm->setFormAction($this->ctrl->getFormAction($this, 'updateDetails'));
		$oForm->setTitle($this->lng->txt('details'));
		$oForm->setTitleIcon(ilUtil::getImagePath('icon_pays.gif', false));
		
		$tmp_obj = ilObjectFactory::getInstanceByRefId($_GET['sell_id']);		
		
		// title
		$oTitleGUI = new ilNonEditableValueGUI($this->lng->txt('title'));
		$oTitleGUI->setValue($tmp_obj->getTitle());
		$oForm->addItem($oTitleGUI);
		
		// description
		$oDescriptionGUI = new ilNonEditableValueGUI($this->lng->txt('description'));
		$oDescriptionGUI->setValue($tmp_obj->getDescription());
		$oForm->addItem($oDescriptionGUI);
		
		// owner
		$oOwnerGUI = new ilNonEditableValueGUI($this->lng->txt('owner'));
		$oOwnerGUI->setValue($tmp_obj->getOwnerName());
		$oForm->addItem($oOwnerGUI);
		
		// path
		$oPathGUI = new ilNonEditableValueGUI($this->lng->txt('paya_count_purchaser'));
		$oPathGUI->setValue($this->__getHTMLPath((int)$_GET['sell_id']));
		$oForm->addItem($oPathGUI);
		
		// vendors
		$oVendorsGUI = new ilSelectInputGUI($this->lng->txt('paya_vendor'), 'vendor');		
		$oVendorsGUI->setOptions($this->__getVendors());
		$oForm->addItem($oVendorsGUI);
		
		// buttons
		$oForm->addCommandButton('addObject', $this->lng->txt('next'));
		$oForm->addCommandButton('showObjects', $this->lng->txt('cancel'));		
		
		$this->tpl->setVariable('ADM_CONTENT', $oForm->getHTML());
	}

	function addObject()
	{
		if(!$_GET['sell_id'])
		{
			ilUtil::sendInfo($this->lng->txt('paya_no_object_selected'));
			
			return $this->showObjectSelector();
		}
		if(!(int)$_POST['vendor'])
		{
			ilUtil::sendInfo($this->lng->txt('paya_no_vendor_selected'));
			
			return $this->showSelectedObject();
		}
		if(!ilPaymentObject::_isPurchasable($_GET['sell_id']))
		{
			ilUtil::sendInfo($this->lng->txt('paya_object_not_purchasable'));

			return $this->showObjectSelector();
		}
		
		include_once 'payment/classes/class.ilPaymentObject.php';

		$p_obj = new ilPaymentObject($this->user_obj);
		
		$p_obj->setRefId((int)$_GET['sell_id']);
		$p_obj->setStatus($p_obj->STATUS_NOT_BUYABLE);
		$p_obj->setPayMethod($p_obj->PAY_METHOD_NOT_SPECIFIED);
		$p_obj->setVendorId((int)$_POST['vendor']);
		$p_obj->setTopicId((int)$_POST['topic_id']);
		//$p_obj->setVatRate((float)$_POST['vat_rate']);
		$p_obj->setVatId((int)$_POST['vat_id']);


		if($new_id = $p_obj->add())
		{
			ilUtil::sendInfo($this->lng->txt('paya_added_new_object'));			
			$_GET['pobject_id'] = $new_id;
			return $this->editDetails();
			//return $this->addPrice();
		}
		else
		{
			ilUtil::sendInfo($this->lng->txt('paya_err_adding_object'));
			return $this->showObjects();
		}
	}
	
	private function __getVendors()
	{
		include_once 'payment/classes/class.ilPaymentVendors.php';
		
		$options = array();
		$vendors = array();
		if(ilPaymentVendors::_isVendor($this->user_obj->getId()))
		{
			$vendors[] = $this->user_obj->getId();
		}
		if($vend = ilPaymentTrustees::_getVendorsForObjects($this->user_obj->getId()))
		{
			$vendors = array_merge($vendors, $vend);
		}
		foreach($vendors as $vendor)
		{
			$tmp_obj = ilObjectFactory::getInstanceByObjId($vendor, false);
			$options[$vendor] = $tmp_obj->getFullname().' ['.$tmp_obj->getLogin().']';
		}
		
		return $options;
	}

	
	private function __getStatus()
	{
			
		$option = array();
		$option[$this->pobject->STATUS_NOT_BUYABLE] = $this->lng->txt('paya_not_buyable');
		$option[$this->pobject->STATUS_BUYABLE] = $this->lng->txt('paya_buyable');
		$option[$this->pobject->STATUS_EXPIRES] = $this->lng->txt('paya_expires');
		
		return $option;
	}
	
	private function __getPayMethods()
	{
		include_once 'payment/classes/class.ilPayMethods.php';

		$options = array();

		$options[$this->pobject->PAY_METHOD_NOT_SPECIFIED] = $this->lng->txt('paya_pay_method_not_specified');
		if(ilPayMethods::_enabled('pm_bill'))
		{
			$options[$this->pobject->PAY_METHOD_BILL] = $this->lng->txt('pays_bill');
		}
		if(ilPayMethods::_enabled('pm_bmf'))
		{
			$options[$this->pobject->PAY_METHOD_BMF] = $this->lng->txt('pays_bmf');
		}
		if(ilPayMethods::_enabled('pm_paypal'))
		{
			$options[$this->pobject->PAY_METHOD_PAYPAL] = $this->lng->txt('pays_paypal');
		}
		
		return $options;
	}
	

/*unused*/
/*	function __showPayMethodLink()
	{ 
		switch($this->pobject->getPayMethod())
		{
			case $this->pobject->PAY_METHOD_NOT_SPECIFIED:
				$this->showButton('editPayMethod',$this->lng->txt('paya_edit_pay_method'));
				break;

			case $this->pobject->PAY_METHOD_BILL:
			/*
				$this->ctrl->setParameterByClass('ilpaymentbilladmingui','pobject_id',(int) $_GET['pobject_id']);
				$this->tpl->addBlockfile("BUTTONS", "buttons", "tpl.buttons.html");
				$this->tpl->setCurrentBlock("btn_cell");
				$this->tpl->setVariable("BTN_LINK",$this->ctrl->getLinkTargetByClass('ilpaymentbilladmingui'));
				$this->tpl->setVariable("BTN_TXT",$this->lng->txt('paya_edit_pay_method'));
				$this->tpl->parseCurrentBlock();
			*/
/*				$this->showButton('editPayMethod',$this->lng->txt('paya_edit_pay_method'));
				break;

			case $this->pobject->PAY_METHOD_BMF:
				$this->showButton('editPayMethod',$this->lng->txt('paya_edit_pay_method'));
				break;

			case $this->pobject->PAY_METHOD_PAYPAL:
				$this->showButton('editPayMethod',$this->lng->txt('paya_edit_pay_method'));
				break;
		}
	}
*/	
		function __showButton($a_cmd,$a_text,$a_target = '')
	{
		$this->tpl->addBlockfile('BUTTONS', 'buttons', 'tpl.buttons.html');
		
		// display button
		$this->tpl->setCurrentBlock('btn_cell');
		$this->tpl->setVariable('BTN_LINK',$this->ctrl->getLinkTarget($this,$a_cmd));
		$this->tpl->setVariable('BTN_TXT',$a_text);
		if($a_target)
		{
			$this->tpl->setVariable('BTN_TARGET',$a_target);
		}

		$this->tpl->parseCurrentBlock();
	}		
	
	function __showObjectsTable($a_result_set)
	{
		$tbl =& $this->initTableGUI();
		$tpl =& $tbl->getTemplateObject();

		// SET FORMAACTION
		$tpl->setCurrentBlock('tbl_form_header');

		$tpl->setVariable('FORMACTION',$this->ctrl->getFormAction($this));
		$tpl->parseCurrentBlock();

		/*
		$tpl->setCurrentBlock("tbl_action_row");
		$tpl->setCurrentBlock("plain_buttons");
		$tpl->parseCurrentBlock();

		$tpl->setVariable("COLUMN_COUNTS",6);
		$tpl->setVariable("IMG_ARROW", ilUtil::getImagePath("arrow_downright.gif"));

		$tpl->setCurrentBlock("tbl_action_button");
		$tpl->setVariable("BTN_NAME","deleteTrustee");
		$tpl->setVariable("BTN_VALUE",$this->lng->txt("delete"));
		$tpl->parseCurrentBlock();
		$tpl->setCurrentBlock("tbl_action_row");
		$tpl->setVariable("TPLPATH",$this->tpl->tplPath);
		$tpl->parseCurrentBlock();
		*/

		$tbl->setTitle($this->lng->txt('objects'),'icon_pays.gif',$this->lng->txt('objects'));
		$tbl->setHeaderNames(array($this->lng->txt('title'),
								   $this->lng->txt('status'),
								   $this->lng->txt('paya_pay_method'),
								   $this->lng->txt('vat_rate'),
								   $this->lng->txt('paya_vendor'),
								   $this->lng->txt('paya_count_purchaser'),
								   ''));
		$header_params = $this->ctrl->getParameterArray($this,'');
		$tbl->setHeaderVars(array('title',
								  'status',
								  'pay_method',
								  'vat_rate',
								  'vendor',
								  'purchasers',
								  'options'),$header_params);
								  /*
							array('cmd' => '',
								  'cmdClass' => 'ilpaymentobjectgui',
								  'cmdNode' => $_GET['cmdNode']));
								  */
		$tbl->setColumnWidth(array('15%','15%','15%','15%','20%','20%'));

#		$this->setTableGUIBasicData($tbl,$a_result_set);

		$offset = $_GET['offset'];
		$order = $_GET['sort_by'];
		$direction = $_GET['sort_order'] ? $_GET['sort_order'] : 'desc';

		$tbl->setOrderColumn($order,'order_date');
		$tbl->setOrderDirection($direction);
		$tbl->setOffset($offset);
		$tbl->setLimit($_GET['limit']);
		$tbl->setMaxCount(count($a_result_set));
		$tbl->setFooter('tblfooter',$this->lng->txt('previous'),$this->lng->txt('next'));
		$tbl->setData($a_result_set);

		$tbl->render();

		$this->tpl->setVariable('OBJECTS_TABLE',$tbl->tpl->get());

		return true;
	}


	function __getHTMLPath($a_ref_id)
	{
		global $tree;

		$path = $tree->getPathFull($a_ref_id);
		unset($path[0]);

		foreach($path as $data)
		{
			$html .= $data['title'].' > ';
		}
		return substr($html,0,-2);
	}

	function __initPaymentObject($a_pobject_id = 0)
	{
		include_once './payment/classes/class.ilPaymentObject.php';

		$this->pobject =& new ilPaymentObject($this->user_obj,$a_pobject_id);

		return true;
	}
}
?>
