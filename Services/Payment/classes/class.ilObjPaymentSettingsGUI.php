<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */
 
/**
* Class ilObjPaymentSettingsGUI
*
* @author Stefan Meyer <smeyer@databay.de> 
* @author Jens Conze <jc@databay.de> 
* @version $Id$
* 
* @ilCtrl_Calls ilObjPaymentSettingsGUI: ilPermissionGUI, ilShopTopicsGUI, ilPageObjectGUI
* 
* @extends ilObjectGUI
* @package ilias-core
*
*/

require_once './classes/class.ilObjectGUI.php';
include_once 'Services/Payment/classes/class.ilShopVatsList.php';
include_once './payment/classes/class.ilPaymentPrices.php';

class ilObjPaymentSettingsGUI extends ilObjectGUI
{
	var $user_obj = null;
	var $pobject = null;

	var $section;
	var $mainSection;

	/**
	* Constructor
	* @access public
	*/
	function ilObjPaymentSettingsGUI($a_data,$a_id,$a_call_by_reference,$a_prepare_output = true)
	{
		global $ilias;

		$this->user_obj =& $ilias->account;

		include_once './payment/classes/class.ilPaymentObject.php';

		$this->pobject =& new ilPaymentObject($this->user_obj);


		$this->type = 'pays';
		$this->ilObjectGUI($a_data,$a_id,$a_call_by_reference,$a_prepare_output);

		$this->SECTION_GENERAL = 1;
		$this->SECTION_PAYPAL = 2;
		$this->SETTINGS = 3;
		$this->OTHERS = 0;
		$this->STATISTIC = 4;
		$this->VENDORS = 5;
		$this->PAY_METHODS = 6;
		$this->OBJECTS = 7;
		$this->SECTION_BMF = 8;
		$this->TOPICS = 9;
		$this->VATS = 10;
		$this->SECTION_VATS = 11;
		
		$this->lng->loadLanguageModule('payment');
	}
	
	function &executeCommand()
	{		
		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();
		$this->prepareOutput();

		switch($next_class)
		{
			case 'ilpermissiongui':
				include_once('./classes/class.ilPermissionGUI.php');
				$perm_gui =& new ilPermissionGUI($this);
				$ret =& $this->ctrl->forwardCommand($perm_gui);
				break;
				
			case 'ilpageobjectgui':
				$this->prepareOutput();
				$ret = $this->forwardToPageObject();
				if($ret != '')
				{
					$this->tpl->setContent($ret);
				}				
				break;
				
			case 'ilshoptopicsgui':
				include_once 'Services/Payment/classes/class.ilShopTopicsGUI.php';
				$topics_gui = new ilShopTopicsGUI($this);
				$ret = $this->ctrl->forwardCommand($topics_gui);
				break;
						
			default:
				if ($cmd == '' || $cmd == 'view')
				{
					$cmd = 'generalSettings';
				}
			
				switch ($cmd)
				{
					case 'vendors' :
					case 'searchUser' :
					case 'search' :
					case 'performSearch' :
					case 'addVendor' :
					case 'exportVendors' :
					case 'performDeleteVendors' :
					case 'cancelDeleteVendors' :
					case 'performEditVendor' :	$this->__setSection($this->OTHERS);
												$this->__setMainSection($this->STATISTIC);
												$this->tabs_gui->setTabActive('vendors');
												break;
					case 'statistic' :
					case 'editStatistic' :
					case 'updateStatistic' :
					case 'deleteStatistic' :
					case 'performDelete' :
					case 'resetFilter' :
					case 'exportVendors' :
					case 'addCustomer' :
					case 'saveCustomer' :
					case 'showObjectSelector' :
					case 'searchUserSP' :
					case 'performSearchSP' :	$this->__setSection($this->OTHERS);
												$this->__setMainSection($this->STATISTIC);
												//$this->tabs_gui->setTabActive('statistic');
												$this->tabs_gui->setTabActive('bookings');
												break;
					case 'updateObjectDetails' :
					case 'deleteObject' :
					case 'performObjectDelete' :
					case 'objects' :
					case 'editPrices' :
					case 'addPrice' :					
					case 'editObject' :
					case 'resetObjectFilter' :
												include_once './payment/classes/class.ilPaymentObject.php';												
												include_once './payment/classes/class.ilPaymentBookings.php';
												$this->__setSection($this->OTHERS);
												$this->__setMainSection($this->OBJECTS);
												$this->tabs_gui->setTabActive('objects');
												break;
					case 'saveGeneralSettings' :
					case 'generalSettings' :	$this->__setSection($this->SECTION_GENERAL);
												$this->__setMainSection($this->SETTINGS);
												$this->tabs_gui->setTabActive('settings');
												break;
					case 'saveBmfSettings' :
					case 'bmfSettings' :		$this->__setSection($this->SECTION_BMF);
												$this->__setMainSection($this->SETTINGS);
												$this->tabs_gui->setTabActive('settings');
												break;
					case 'savePaypalSettings' :
					case 'paypalSettings' :		$this->__setSection($this->SECTION_PAYPAL);
												$this->__setMainSection($this->SETTINGS);
												$this->tabs_gui->setTabActive('settings');
												break;
					case 'savePayMethods' :		$this->__setSection($this->OTHERS);
												$this->__setMainSection($this->PAY_METHODS);
												$this->tabs_gui->setTabActive('pay_methods');
												break;
					case 'gateway' :			if ($_POST['action'] == 'editVendorObject' ||
													$_POST['action'] == 'deleteVendorsObject')
												{
													$this->__setSection($this->OTHERS);
													$this->__setMainSection($this->STATISTIC);
													$this->tabs_gui->setTabActive('vendors');
												}
												break;
												
		
					case 'deleteVat' :
					case 'newVat':
					case 'insertVat':

					case 'updateVat':
					case 'performDeleteVat':
					case 'confirmDeleteVat':
					case 'createVat':
					case 'saveVat':
					case 'editVat':			
					case 'vats' :						
												$this->__setSection($this->OTHERS);
												$this->__setMainSection($this->VATS);
												$this->tabs_gui->setTabActive('vats');					
												break;
												
					default :					$this->__setSection($this->OTHERS);
												$this->__setMainSection($this->OTHERS);
												break;
				}
				$cmd .= 'Object';

				$this->__buildSettingsButtons();

				$this->$cmd();

				break;
		}
		return true;
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
		$ilTabs->setBackTarget($this->lng->txt('back'), $this->ctrl->getLinkTarget($this, 'editObject'));

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
	
	function saveBmfSettingsObject()
	{
		global $rbacsystem;

		// MINIMUM ACCESS LEVEL = 'read'
		if(!$rbacsystem->checkAccess('read', $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt('msg_no_perm_read'),$this->ilias->error_obj->MESSAGE);
		}
		
		include_once './payment/classes/class.ilBMFSettings.php';
		
		$this->error = '';
		
		$bmfSetObj = ilBMFSettings::getInstance();			
		
		$bmfSetObj->setClientId(ilUtil::stripSlashes($_POST['mandantNr']));
		$bmfSetObj->setBewirtschafterNr(ilUtil::stripSlashes($_POST['bewirtschafterNr']));
		$bmfSetObj->setHaushaltsstelle(ilUtil::stripSlashes($_POST['haushaltsstelle']));
		$bmfSetObj->setObjectId(ilUtil::stripSlashes($_POST['objektNr']));
		$bmfSetObj->setKennzeichenMahnverfahren(ilUtil::stripSlashes($_POST['kennzeichenMahnverfahren']));
		$bmfSetObj->setWaehrungsKennzeichen(ilUtil::stripSlashes($_POST['waehrungskennzeichen']));
		$bmfSetObj->setEpaymentServer(ilUtil::stripSlashes($_POST['ePaymentServer']));
		$bmfSetObj->setClientCertificate(ilUtil::stripSlashes($_POST['clientCertificate']));
		$bmfSetObj->setCaCertificate(ilUtil::stripSlashes($_POST['caCertificate']));
		$bmfSetObj->setTimeout(ilUtil::stripSlashes($_POST['timeOut']));
		
		if ($_POST['mandantNr'] == '' ||
			$_POST['bewirtschafterNr'] == '' ||
			$_POST['haushaltsstelle'] == '' ||
			$_POST['objektNr'] == '' ||
			$_POST['kennzeichenMahnverfahren'] == '' ||
			$_POST['waehrungskennzeichen'] == '' ||
			$_POST['ePaymentServer'] == '' ||
			$_POST['clientCertificate'] == '' ||
			$_POST['caCertificate'] == '' ||			
			$_POST['timeOut'] == '')
		{
			$this->error = $this->lng->txt('pays_bmf_settings_not_valid');
			ilUtil::sendInfo($this->error);
			$this->bmfSettingsObject();
			return;
		}
		
		$bmfSetObj->save();
				
		$this->bmfSettingsObject();

		ilUtil::sendInfo($this->lng->txt('pays_updated_bmf_settings'));

		return true;
	}
	
	function bmfSettingsObject()
	{	
		global $rbacsystem;

		// MINIMUM ACCESS LEVEL = 'read'
		if(!$rbacsystem->checkAccess('read', $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt('msg_no_perm_read'),$this->ilias->error_obj->MESSAGE);
		}
		include_once './payment/classes/class.ilBMFSettings.php';
		include_once('./Services/Form/classes/class.ilPropertyFormGUI.php');		

		$this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.pays_bmf_settings.html','payment');
		
		$bmfSetObj = ilBMFSettings::getInstance();		
						
		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this, 'saveBmfSettings'));
		$form->setTitle($this->lng->txt('pays_bmf_settings'));
		
		$form->addCommandButton('saveBmfSettings',$this->lng->txt('save'));
		
		$formItem = new ilTextInputGUI($this->lng->txt('pays_bmf_client_id'), 'mandantNr');
		$formItem->setValue($bmfSetObj->getClientId());
		$form->addItem($formItem);
		
		$formItem = new ilTextInputGUI($this->lng->txt('pays_bmf_bewirtschafter_nr'), 'bewirtschafterNr');
		$formItem->setValue($bmfSetObj->getBewirtschafterNr());
		$form->addItem($formItem);
		
		$formItem = new ilTextInputGUI($this->lng->txt('pays_bmf_haushaltsstelle'), 'haushaltsstelle');
		$formItem->setValue($bmfSetObj->getHaushaltsstelle());
		$form->addItem($formItem);
		
		$formItem = new ilTextInputGUI($this->lng->txt('pays_bmf_object_id'), 'objektNr');
		$formItem->setValue($bmfSetObj->getObjectId());
		$form->addItem($formItem);
		
		$formItem = new ilTextInputGUI($this->lng->txt('pays_bmf_kennzeichen_mahnverfahren'), 'kennzeichenMahnverfahren');
		$formItem->setValue($bmfSetObj->getKennzeichenMahnverfahren());
		$form->addItem($formItem);
		
		$formItem = new ilTextInputGUI($this->lng->txt('pays_bmf_waehrungskennzeichen'), 'waehrungskennzeichen');
		$formItem->setValue($bmfSetObj->getWaehrungsKennzeichen());
		$form->addItem($formItem);
		
		$formItem = new ilTextInputGUI($this->lng->txt('pays_bmf_epayment_server'), 'ePaymentServer');
		$formItem->setValue($bmfSetObj->getEpaymentServer());
		$form->addItem($formItem);
		
		$formItem = new ilTextInputGUI($this->lng->txt('pays_bmf_client_certificate'), 'clientCertificate');
		$formItem->setValue($bmfSetObj->getClientCertificate());
		$form->addItem($formItem);
		
		$formItem = new ilTextInputGUI($this->lng->txt('pays_bmf_ca_certificate'), 'caCertificate');
		$formItem->setValue($bmfSetObj->getCaCertificate());
		$form->addItem($formItem);
		
		$formItem = new ilTextInputGUI($this->lng->txt('pays_bmf_timeout'), 'timeOut');
		$formItem->setValue($bmfSetObj->getTimeOut());
		$form->addItem($formItem);				
				
		$this->tpl->setVariable('BMF_SETTINGS',$form->getHTML());
	}
	
	function updateObjectDetailsObject()
	{ 
		if(!$_GET['pobject_id'])
		{
			ilUtil::sendInfo($this->lng->txt('paya_no_object_selected'));

			$this->objectObjects();
			return true;
		}
		
		$this->__initPaymentObject((int) $_GET['pobject_id']);
		$this->ctrl->setParameter($this,'pobject_id',(int) $_GET['pobject_id']);

		// read old settings
		$old_pay_method = $this->pobject->getPayMethod();
		$old_status = $this->pobject->getStatus();
		$old_vat_id = $this->pobject->getVatId();

		// check status changed from not_buyable
		if($old_status == $this->pobject->STATUS_NOT_BUYABLE and
		   (int) $_POST['status'] != $old_status)
		{
			// check pay_method edited
			switch((int) $_POST['pay_method'])
			{
				case $this->pobject->PAY_METHOD_NOT_SPECIFIED:
					ilUtil::sendInfo($this->lng->txt('paya_select_pay_method_first'));
					$this->editObjectObject();

					return false;
					
				case $this->pobject->PAY_METHOD_BILL:
					include_once './payment/classes/class.ilPaymentBillVendor.php';

					$bill_vendor =& new ilPaymentBillVendor((int) $_GET['pobject_id']);
					if(!$bill_vendor->validate())
					{
						ilUtil::sendInfo($this->lng->txt('paya_select_pay_method_first'));
						$this->editObjectObject();
						
						return false;
					}
					break;

				default:
					;
			}
			// check minimum one price
			include_once './payment/classes/class.ilPaymentPrices.php';

			$prices_obj =& new ilPaymentPrices((int) $_GET['pobject_id']);
			if(!count($prices_obj->getPrices()))
			{
				ilUtil::sendInfo($this->lng->txt('paya_edit_prices_first'));
				$this->editObjectObject();
						
				return false;
			}				
		}
		

		$this->pobject->setStatus((int) $_POST['status']);
		$this->pobject->setVendorId((int) $_POST['vendor']);
		$this->pobject->setPayMethod((int) $_POST['pay_method']);
		$this->pobject->setTopicId((int) $_POST['topic_id']);
		$this->pobject->setVatId((int) $_POST['vat_id']); 
		
		$this->pobject->update();

		ilUtil::sendInfo($this->lng->txt('paya_details_updated'));
		$this->editObjectObject();

		return true;
	}
	
	function editPricesObject($a_show_delete = false)
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


		$this->__showButton('objects',$this->lng->txt('back'));	
		$this->__showButton('editObject',$this->lng->txt('paya_edit_details'));
		$this->__showButton('editPrices',$this->lng->txt('paya_edit_prices'));

		$this->__initPaymentObject((int) $_GET['pobject_id']);

		$this->tpl->addBlockfile('ADM_CONTENT','adm_content','tpl.paya_adm_edit_prices.html','payment');

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
					
					$data[$counter]['price'] = ilFormat::_getLocalMoneyFormat($price['price']);
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
			
	function addPriceObject()
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

		$this->__showButton('editObject',$this->lng->txt('paya_edit_details'));
		$this->__showButton('editPrices',$this->lng->txt('paya_edit_prices'));

		$this->tpl->addBlockfile('ADM_CONTENT','adm_content','tpl.paya_adm_add_price.html','payment');

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
		
		$this->tpl->setVariable('MONTH',$this->lng->txt('paya_months'));
		$this->tpl->setVariable('TXT_DURATION',$this->lng->txt('duration'));
		$this->tpl->setVariable('TXT_UNLIMITED_DURATION',$this->lng->txt('unlimited_duration'));		
		$this->tpl->setVariable('TXT_PRICE',$this->lng->txt('price_a'));
		$this->tpl->setVariable('CANCEL',$this->lng->txt('cancel'));
		$this->tpl->setVariable('ADD',$this->lng->txt('paya_add_price'));

		$this->tpl->setVariable('DURATION',$_POST['duration']);
		$this->tpl->setVariable('UNLIMITED_DURATION',$_POST['unlimited_duration']);
		
		$this->tpl->setVariable('PRICE',$_POST['price']);
		
		return true;
	}

	function performAddPriceObject()
	{
		if(!$_GET['pobject_id'])
		{
			ilUtil::sendInfo($this->lng->txt('paya_no_object_selected'));

			$this->objectsObject();
			return true;
		}

		include_once './payment/classes/class.ilPaymentPrices.php';
		include_once './payment/classes/class.ilPaymentCurrency.php';

		$currency = ilPaymentCurrency::_getAvailableCurrencies();

		$prices =& new ilPaymentPrices((int) $_GET['pobject_id']);

		$prices->setUnlimitedDuration((int)$_POST['unlimited_duration']);	

		if($_POST['unlimited_duration'] == '1')
		{
			$prices->setUnlimitedDuration(1);
		}

		$prices->setDuration($_POST['duration']);
		$prices->setPrice($_POST['price']);
		$prices->setCurrency($currency[1]['currency_id']);

		if(!$prices->validate())
		{
			ilUtil::sendInfo($this->lng->txt('paya_price_not_valid'));
			$this->addPriceObject();

			return true;
		}
		$prices->add();

		ilUtil::sendInfo($this->lng->txt('paya_added_new_price'));
		$this->editPricesObject();

		return true;
	}		

	function performDeletePriceObject()
	{
		if(!$_GET['pobject_id'])
		{
			ilUtil::sendInfo($this->lng->txt('paya_no_object_selected'));

			$this->objectsObject();
			return true;
		}

		if(!count($_SESSION['price_ids']))
		{
			ilUtil::sendInfo($this->lng->txt('paya_no_prices_selected'));
			
			$this->editPricesObject();
			return true;
		}
		include_once './payment/classes/class.ilPaymentPrices.php';
		
		$prices =& new ilPaymentPrices((int) $_GET['pobject_id']);

		foreach($_SESSION['price_ids'] as $price_id)
		{
			if($prices->delete($price_id))
			ilUtil::sendInfo($this->lng->txt('paya_deleted_selected_prices'));
			
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
		
		return $this->editPricesObject();
	}

	function deletePriceObject()
	{
		if(!$_GET['pobject_id'])
		{
			ilUtil::sendInfo($this->lng->txt('paya_no_object_selected'));

			$this->objectsObject();
			return true;
		}

		if(!count($_POST['price_ids']))
		{
			ilUtil::sendInfo($this->lng->txt('paya_no_prices_selected'));
			
			$this->editPricesObject();
			return true;
		}
		$_SESSION['price_ids'] = $_POST['price_ids'];

		$this->editPricesObject(true);
		return true;
	}	

	function updatePriceObject()
	{
		include_once './payment/classes/class.ilPaymentPrices.php';

		if(!$_GET['pobject_id'])
		{
			ilUtil::sendInfo($this->lng->txt('paya_no_object_selected'));

			$this->objectsObject();
			return true;
		}
		$po =& new ilPaymentPrices((int) $_GET['pobject_id']);

		$this->ctrl->setParameter($this,'pobject_id',(int) $_GET['pobject_id']);

		// validate
		foreach($_POST['prices'] as $price_id => $price)
		{
		
			$old_price = $po->getPrice($price_id);

			$po->setDuration($price['duration']);

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

			$this->editPricesObject();
			return false;
		}
		
		
		foreach($_POST['prices'] as $price_id => $price)
		{
			$old_price = $po->getPrice($price_id);

			if(isset($_POST['duration_ids']))
			{
			
	 			$search = in_array((string)$price_id, $_POST['duration_ids']);
	
				//$po->setUnlimitedDuration($price['unlimited_duration']); //$_POST['duration_ids'] != NULL &&
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
		$this->editPricesObject();

		return true;
	}
	
	function editObjectObject($a_show_confirm = false)
	{

		if(!isset($_GET['pobject_id']))
		{
			ilUtil::sendInfo($this->lng->txt('paya_no_booking_id_given'));
			$this->objectsObject();

			return true;
		}	

		$this->ctrl->setParameter($this,'pobject_id',(int) $_GET['pobject_id']);
		
		$this->__showButton('objects',$this->lng->txt('back'));		
		$this->__showButton('editObject',$this->lng->txt('paya_edit_details'));
		$this->__showButton('editPrices',$this->lng->txt('paya_edit_prices'));
		$this->tpl->setCurrentBlock('btn_cell');
		$this->tpl->setVariable('BTN_LINK', $this->ctrl->getLinkTargetByClass(array('ilpageobjectgui'), 'edit'));
		$this->tpl->setVariable('BTN_TXT', $this->lng->txt('pay_edit_abstract'));		
		$this->tpl->parseCurrentBlock();

		$this->tpl->addBlockfile('ADM_CONTENT','adm_content','tpl.paya_adm_edit_objects.html','payment');
				
		$this->__initPaymentObject((int) $_GET['pobject_id']);		
		
		if($a_show_confirm)
		{
			$this->tpl->setCurrentBlock('confirm_delete');
			$this->tpl->setVariable('CONFIRM_FORMACTION',$this->ctrl->getFormAction($this));
			$this->tpl->setVariable('TXT_CANCEL',$this->lng->txt('cancel'));
			$this->tpl->setVariable('CONFIRM_CMD','performObjectDelete');
			$this->tpl->setVariable('TXT_CONFIRM',$this->lng->txt('confirm'));
			$this->tpl->parseCurrentBlock();
		}

		$this->tpl->setVariable("DETAILS_FORMACTION",$this->ctrl->getFormAction($this));

			
		$tmp_obj =& ilObjectFactory::getInstanceByRefId($this->pobject->getRefId());
		
		$this->tpl->setVariable('TYPE_IMG',ilUtil::getImagePath('icon_'.$tmp_obj->getType().'_b.gif'));
		$this->tpl->setVariable('ALT_IMG',$this->lng->txt('obj_'.$tmp_obj->getType()));
		$this->tpl->setVariable('TITLE',$tmp_obj->getTitle());
		$this->tpl->setVariable('DESCRIPTION',$tmp_obj->getDescription());
		$this->tpl->setVariable('TXT_PATH',$this->lng->txt('path'));
		$this->tpl->setVariable('PATH',$this->__getHTMLPath($this->pobject->getRefId()));
		$this->tpl->setVariable('TXT_VENDOR',$this->lng->txt('paya_vendor'));
		$this->tpl->setVariable('VENDOR',$this->__showVendorSelector($this->pobject->getVendorId()));
		$this->tpl->setVariable('TXT_COUNT_PURCHASER',$this->lng->txt('paya_count_purchaser'));
		$this->tpl->setVariable('COUNT_PURCHASER',ilPaymentBookings::_getCountBookingsByObject((int) $_GET['pobject_id']));
		$this->tpl->setVariable('TXT_STATUS',$this->lng->txt('status'));
		$this->tpl->setVariable('STATUS',$this->__showStatusSelector());
		$this->tpl->setVariable('TXT_PAY_METHOD',$this->lng->txt('paya_pay_method'));
		$this->tpl->setVariable('PAY_METHOD',$this->__showPayMethodSelector());
		
		// topics
		include_once 'Services/Payment/classes/class.ilShopTopics.php';
		ilShopTopics::_getInstance()->read();
		if (is_array($topics = ilShopTopics::_getInstance()->getTopics()) && count($topics))
		{
			$selectable_topics = array();
			$selectable_topics[''] = $this->lng->txt('please_choose');
			foreach ($topics as $topic)
			{
				$selectable_topics[$topic->getId()] = $topic->getTitle();
			}
			
			$this->tpl->setVariable('TXT_TOPIC', $this->lng->txt('topic'));
			$this->tpl->setVariable('TOPICS', ilUtil::formSelect(array($this->pobject->getTopicId()), 'topic_id', $selectable_topics, false, true));
		}

		// vats
		$oShopVatsList = new ilShopVatsList();
		$oShopVatsList->read();
		
		$selectable_vats = array();
		$selectable_vats[-1] = '----';
		if($oShopVatsList->hasItems())
		{							
			foreach($oShopVatsList as $oVAT)
			{	
				$selectable_vats[$oVAT->getId()] = ilShopUtils::_formatVAT($oVAT->getRate()).' -> '.$oVAT->getTitle();
			}			
		}
				
		try
		{
			$oVAT = new ilShopVats((int)$this->pobject->getVatId());
			$this->tpl->setVariable('VAT', ilUtil::formSelect($oVAT->getId(), 'vat_id', $selectable_vats, false, true));
		}
		catch(ilShopException $e)
		{
			$this->tpl->setVariable('VAT', ilUtil::formSelect(-1, 'vat_id', $selectable_vats, false, true));
		}	
		$this->tpl->setVariable('TXT_VAT', $this->lng->txt('vats'));
				
		$this->tpl->setVariable('INPUT_CMD','updateObjectDetails');
		$this->tpl->setVariable('INPUT_VALUE',$this->lng->txt('save'));

		$this->tpl->setVariable('DELETE_CMD','deleteObject');
		$this->tpl->setVariable('DELETE_VALUE',$this->lng->txt('delete'));
		
	}
	
	function deleteObjectObject()
	{
		include_once './payment/classes/class.ilPaymentBookings.php';

		if(!$_GET['pobject_id'])
		{
			ilUtil::sendInfo($this->lng->txt('paya_no_object_selected'));

			$this->editObjectObject();
			return true;
		}
		if(ilPaymentBookings::_getCountBookingsByObject((int) $_GET['pobject_id']))
		{
			ilUtil::sendInfo($this->lng->txt('paya_bookings_available'));
			$this->editObjectObject();

			return false;
		}
		else
		{
			ilUtil::sendInfo($this->lng->txt('paya_sure_delete_object'));
			$this->editObjectObject(true);

			return true;
		}
	}
	
	function performObjectDeleteObject()
	{
		include_once './payment/classes/class.ilPaymentPrices.php';
		include_once './payment/classes/class.ilPaymentBillVendor.php';

		if(!$_GET['pobject_id'])
		{
			ilUtil::sendInfo($this->lng->txt('paya_no_object_selected'));

			$this->objectsObject();
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

		$this->objectsObject();

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
	
	function __showVendorSelector($a_selected = 0)
	{		
		include_once './payment/classes/class.ilPaymentVendors.php';
				
		$vendors = array();
		
		$vendor_obj = new ilPaymentVendors();
		$all_vendors = $vendor_obj->getVendors();
		if (is_array($all_vendors))
		{
			foreach ($all_vendors as $vendor)
			{				
				$vendors[] = $vendor['vendor_id'];
			}
		}		
		
		foreach($vendors as $vendor)
		{
			$tmp_obj =& ilObjectFactory::getInstanceByObjId($vendor,false);

			$action[$vendor] = $tmp_obj->getFullname().' ['.$tmp_obj->getLogin().']';
		}
		
		return ilUtil::formSelect($a_selected,'vendor',$action,false,true);
	}
	
	function __showStatusSelector()
	{
		$action = array();
		$action[$this->pobject->STATUS_NOT_BUYABLE] = $this->lng->txt('paya_not_buyable');
		$action[$this->pobject->STATUS_BUYABLE] = $this->lng->txt('paya_buyable');
		$action[$this->pobject->STATUS_EXPIRES] = $this->lng->txt('paya_expires');

		return ilUtil::formSelect($this->pobject->getStatus(),'status',$action,false,true);
	}
	
	function __showPayMethodSelector()
	{
		include_once './payment/classes/class.ilPayMethods.php';

		$action = array();

		$action[$this->pobject->PAY_METHOD_NOT_SPECIFIED] = $this->lng->txt('paya_pay_method_not_specified');
		if(ilPayMethods::_enabled('pm_bill'))
		{
			$action[$this->pobject->PAY_METHOD_BILL] = $this->lng->txt('pays_bill');
		}
		if(ilPayMethods::_enabled('pm_bmf'))
		{
			$action[$this->pobject->PAY_METHOD_BMF] = $this->lng->txt('pays_bmf');
		}
		if(ilPayMethods::_enabled('pm_paypal'))
		{
			$action[$this->pobject->PAY_METHOD_PAYPAL] = $this->lng->txt('pays_paypal');
		}


		return ilUtil::formSelect($this->pobject->getPayMethod(),'pay_method',$action,false,true);
	}
	
	function resetObjectFilterObject()
	{
		unset($_SESSION['pay_statistics']);
		unset($_POST['title_type']);
		unset($_POST['title_value']);
		unset($_POST['vendor']);
		unset($_POST['pay_method']);

		ilUtil::sendInfo($this->lng->txt('paya_filter_reseted'));

		return $this->objectsObject();
	}
	
	function objectsObject()
	{
		global $rbacsystem;

		// MINIMUM ACCESS LEVEL = 'read'
		if(!$rbacsystem->checkAccess('read', $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt('msg_no_perm_read'),$this->ilias->error_obj->MESSAGE);
		}
		
		$this->tpl->addBlockfile('ADM_CONTENT','adm_content','tpl.paya_adm_objects.html','payment');
		
		if ($_POST['updateView'] == 1)
		{
			$_SESSION['pay_objects']['title_type'] = $_POST['title_type'];
			$_SESSION['pay_objects']['title_value'] = $_POST['title_value'];			
			$_SESSION['pay_objects']['pay_method'] = $_POST['pay_method'];			
			$_SESSION['pay_objects']['vendor'] = $_POST['vendor'];
		}			

		$this->__initPaymentObject();
		$this->lng->loadLanguageModule('search');
		$this->tpl->setVariable('TXT_FILTER',$this->lng->txt('pay_filter'));
		$this->tpl->setVariable('FORM_ACTION',$this->ctrl->getFormAction($this));
		$this->tpl->setVariable('TXT_TITLE',$this->lng->txt('title'));
		$this->tpl->setVariable('TXT_AND',$this->lng->txt('search_all_words'));
		$this->tpl->setVariable('TXT_OR',$this->lng->txt('search_any_word'));
		$this->tpl->setVariable('TXT_BILL',$this->lng->txt('pays_bill'));
		$this->tpl->setVariable('TXT_BMF',$this->lng->txt('pays_bmf'));
		$this->tpl->setVariable('TXT_PAYPAL',$this->lng->txt('pays_paypal'));
		$this->tpl->setVariable('TXT_VENDOR',$this->lng->txt('paya_vendor'));		
		$this->tpl->setVariable('TXT_PAYMENT',$this->lng->txt('payment_system'));		
		$this->tpl->setVariable('TXT_UPDATE_VIEW',$this->lng->txt('pay_update_view'));
		$this->tpl->setVariable('TXT_RESET_FILTER',$this->lng->txt('pay_reset_filter'));

		$this->tpl->setVariable(($_SESSION['pay_objects']['title_type'] != '' ? strtoupper($_SESSION['pay_objects']['title_type']) : 'OR') . '_CHECKED', ' checked');
		$this->tpl->setVariable('TITLE_VALUE', ilUtil::prepareFormOutput($_SESSION['pay_objects']['title_value'], true));
		$this->tpl->setVariable('PAYMENT_' . $_SESSION['pay_objects']['pay_method'], ' selected');
		$this->tpl->setVariable('VENDOR', ilUtil::prepareFormOutput($_SESSION['pay_objects']['vendor'], true));
		
		if(!count($objects = ilPaymentObject::_getAllObjectsData()))
		{
			ilUtil::sendInfo($this->lng->txt('paya_no_objects_assigned'));
			
			return true;
		}

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
			$link_change = "<div class=\"il_ContainerItemCommands\"><a class=\"il_ContainerItemCommand\" href=\"".$this->ctrl->getLinkTarget($this,"editObject")."\">".$this->lng->txt("edit")."</a></div>";

			$f_result[$counter][] = $link_change;
			unset($tmp_user);
			unset($tmp_obj);

			++$counter;
		}
		
		$this->__showObjectsTable($f_result);	

		return true;
	}
	
	function __showObjectsTable($a_result_set)
	{
		$tbl =& $this->initTableGUI();
		$tpl =& $tbl->getTemplateObject();

		// SET FORMAACTION
		$tpl->setCurrentBlock('tbl_form_header');
		
		$this->ctrl->setParameter($this, 'cmd', 'objects');

		$tpl->setVariable('FORMACTION', $this->ctrl->getFormAction($this));
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
								   $this->lng->txt("vat_rate"),
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
							array("cmd" => "",
								  "cmdClass" => "ilpaymentobjectgui",
								  "cmdNode" => $_GET["cmdNode"]));
								  */
		$tbl->setColumnWidth(array("15%","15%","15%","20%","20%"));

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
	
	function __initPaymentObject($a_pobject_id = 0)
	{
		include_once './payment/classes/class.ilPaymentObject.php';

		$this->pobject =& new ilPaymentObject($this->user_obj,$a_pobject_id);

		return true;
	}

	function gatewayObject()
	{
		switch($_POST['action'])
		{
			case 'deleteVendorsObject':
				$this->deleteVendors();
				break;

			case 'editVendorObject':
				$this->editVendor();
				break;

			case 'performEditVendorObject':
				$this->performEditVendorObject();
				break;

			default:
				$this->vendorsObject();
				break;
		}
		return true;
	}

	function resetFilterObject()
	{
		unset($_SESSION['pay_statistics']);
		unset($_POST['transaction_type']);
		unset($_POST['transaction_value']);
		unset($_POST['from']['day']);
		unset($_POST['from']['month']);
		unset($_POST['from']['year']);
		unset($_POST['til']['day']);
		unset($_POST['til']['month']);
		unset($_POST['til']['year']);
		unset($_POST['payed']);
		unset($_POST['access']);
		unset($_POST['customer']);
		unset($_POST['pay_method']);
		unset($_POST['updateView']);
		ilUtil::sendInfo($this->lng->txt('paya_filter_reseted'));

		return $this->statisticObject();
	}

	function statisticObject()
	{
		global $rbacsystem;

		// MINIMUM ACCESS LEVEL = 'read'
		if(!$rbacsystem->checkAccess('read', $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt('msg_no_perm_read'),$this->ilias->error_obj->MESSAGE);
		}

		$this->__showButton('showObjectSelector',$this->lng->txt('paya_add_customer'));

		if ($_POST['updateView'] == 1)
		{
			$_SESSION['pay_statistics']['updateView'] = true;
			$_SESSION['pay_statistics']['transaction_type'] = $_POST['transaction_type'];
			$_SESSION['pay_statistics']['transaction_value'] = $_POST['transaction_value'];
			$_SESSION['pay_statistics']['from']['day'] = $_POST['from']['day'];
			$_SESSION['pay_statistics']['from']['month'] = $_POST['from']['month'];
			$_SESSION['pay_statistics']['from']['year'] = $_POST['from']['year'];
			$_SESSION['pay_statistics']['til']['day'] = $_POST['til']['day'];
			$_SESSION['pay_statistics']['til']['month'] = $_POST['til']['month'];
			$_SESSION['pay_statistics']['til']['year'] = $_POST['til']['year'];
			$_SESSION['pay_statistics']['payed'] = $_POST['payed'];
			$_SESSION['pay_statistics']['access'] = $_POST['access'];
			$_SESSION['pay_statistics']['pay_method'] = $_POST['pay_method'];
			$_SESSION['pay_statistics']['customer'] = $_POST['customer'];
			$_SESSION['pay_statistics']['vendor'] = $_POST['vendor'];
		}

		$this->tpl->addBlockfile('ADM_CONTENT','adm_content','tpl.paya_adm_statistic.html','payment');
		
		$this->tpl->setVariable('TXT_FILTER',$this->lng->txt('pay_filter'));
		$this->tpl->setVariable('FORM_ACTION',$this->ctrl->getFormAction($this));
		$this->tpl->setVariable('TXT_TRANSACTION',$this->lng->txt('paya_transaction'));
		$this->tpl->setVariable('TXT_STARTING',$this->lng->txt('pay_starting'));
		$this->tpl->setVariable('TXT_ENDING',$this->lng->txt('pay_ending'));
		$this->tpl->setVariable('TXT_PAYED',$this->lng->txt('paya_payed'));
		$this->tpl->setVariable('TXT_ALL',$this->lng->txt('pay_all'));
		$this->tpl->setVariable('TXT_YES',$this->lng->txt('yes'));
		$this->tpl->setVariable('TXT_NO',$this->lng->txt('no'));
		$this->tpl->setVariable('TXT_BILL',$this->lng->txt('pays_bill'));
		$this->tpl->setVariable('TXT_BMF',$this->lng->txt('pays_bmf'));
		$this->tpl->setVariable('TXT_PAYPAL',$this->lng->txt('pays_paypal'));
		$this->tpl->setVariable('TXT_PAYMENT',$this->lng->txt('payment_system'));
		$this->tpl->setVariable('TXT_CUSTOMER',$this->lng->txt('paya_customer'));
		$this->tpl->setVariable('TXT_VENDOR',$this->lng->txt('paya_vendor'));
		$this->tpl->setVariable('TXT_ACCESS',$this->lng->txt('paya_access'));
		$this->tpl->setVariable('TXT_ORDER_DATE_FROM',$this->lng->txt('pay_order_date_from'));
		$this->tpl->setVariable('TXT_ORDER_DATE_TIL',$this->lng->txt('pay_order_date_til'));
		$this->tpl->setVariable('TXT_UPDATE_VIEW',$this->lng->txt('pay_update_view'));
		$this->tpl->setVariable('TXT_RESET_FILTER',$this->lng->txt('pay_reset_filter'));

		$this->tpl->setVariable('TRANSACTION_TYPE_' . $_SESSION['pay_statistics']['transaction_type'], ' selected');
		$this->tpl->setVariable('TRANSACTION_VALUE', ilUtil::prepareFormOutput($_SESSION['pay_statistics']['transaction_value'], true));
		$this->tpl->setVariable('PAYED_' . $_SESSION['pay_statistics']['payed'], ' selected');
		$this->tpl->setVariable('ACCESS_' . $_SESSION['pay_statistics']['access'], ' selected');
		$this->tpl->setVariable('PAYMENT_' . $_SESSION['pay_statistics']['pay_method'], ' selected');
		$this->tpl->setVariable('CUSTOMER', ilUtil::prepareFormOutput($_SESSION['pay_statistics']['customer'], true));
		$this->tpl->setVariable('VENDOR', ilUtil::prepareFormOutput($_SESSION['pay_statistics']['vendor'], true));

		for ($i = 1; $i <= 31; $i++)
		{
			$this->tpl->setCurrentBlock('loop_from_day');
			$this->tpl->setVariable('LOOP_FROM_DAY', $i < 10 ? '0' . $i : $i);
			if ($_SESSION['pay_statistics']['from']['day'] == $i)
			{
				$this->tpl->setVariable('LOOP_FROM_DAY_SELECTED', ' selected');
			}
			$this->tpl->parseCurrentBlock('loop_from_day');
			$this->tpl->setCurrentBlock('loop_til_day');
			$this->tpl->setVariable('LOOP_TIL_DAY', $i < 10 ? '0' . $i : $i);
			if ($_SESSION['pay_statistics']['til']['day'] == $i)
			{
				$this->tpl->setVariable('LOOP_TIL_DAY_SELECTED', ' selected');
			}
			$this->tpl->parseCurrentBlock('loop_til_day');
		}
		for ($i = 1; $i <= 12; $i++)
		{
			$this->tpl->setCurrentBlock('loop_from_month');
			$this->tpl->setVariable('LOOP_FROM_MONTH', $i < 10 ? '0' . $i : $i);
			if ($_SESSION['pay_statistics']['from']['month'] == $i)
			{
				$this->tpl->setVariable('LOOP_FROM_MONTH_SELECTED', ' selected');
			}
			$this->tpl->parseCurrentBlock('loop_from_month');
			$this->tpl->setCurrentBlock('loop_til_month');
			$this->tpl->setVariable('LOOP_TIL_MONTH', $i < 10 ? '0' . $i : $i);
			if ($_SESSION['pay_statistics']['til']['month'] == $i)
			{
				$this->tpl->setVariable('LOOP_TIL_MONTH_SELECTED', ' selected');
			}
			$this->tpl->parseCurrentBlock('loop_til_month');
		}
		for ($i = 2004; $i <= date('Y'); $i++)
		{
			$this->tpl->setCurrentBlock('loop_from_year');
			$this->tpl->setVariable('LOOP_FROM_YEAR', $i);
			if ($_SESSION['pay_statistics']['from']['year'] == $i)
			{
				$this->tpl->setVariable('LOOP_FROM_YEAR_SELECTED', ' selected');
			}
			$this->tpl->parseCurrentBlock('loop_from_year');
			$this->tpl->setCurrentBlock('loop_til_year');
			$this->tpl->setVariable('LOOP_TIL_YEAR', $i);
			if ($_SESSION['pay_statistics']['til']['year'] == $i)
			{
				$this->tpl->setVariable('LOOP_TIL_YEAR_SELECTED', ' selected');
			}
			$this->tpl->parseCurrentBlock('loop_til_year');
		}
		
		if(!$_SESSION['pay_statistics']['updateView'])
		{
			$this->tpl->setVariable('FILTER_MESSAGE', $this->lng->txt('statistics_filter_advice'));
			return true;
		}

		$this->__initBookingObject();

		if(!count($bookings = $this->booking_obj->getBookings()))
		{
			ilUtil::sendInfo($this->lng->txt('paya_no_bookings'));

			return true;
		}
#		else
#		{
#			$this->__showButton('exportVendors',$this->lng->txt('excel_export'));
#		}
		$img_change = "<img src=\"".ilUtil::getImagePath("edit.gif")."\" alt=\"".
			$this->lng->txt("edit")."\" title=\"".$this->lng->txt("edit").
			"\" border=\"0\" vspace=\"0\"/>";

		include_once 'Services/User/classes/class.ilObjUser.php';
		$object_title_cache = array();
		$user_title_cache = array();
		
		$counter = 0;
		foreach($bookings as $booking)
		{
			if(array_key_exists($booking['ref_id'], $object_title_cache))
			{
				$tmp_obj = $object_title_cache[$booking['ref_id']];
			}
			else
			{
				$tmp_obj = ilObject::_lookupTitle(ilObject::_lookupObjId($booking['ref_id']));				
				$object_title_cache[$booking['ref_id']] = $tmp_obj;
			}
			if(array_key_exists($booking['b_vendor_id'], $user_title_cache))
			{
				$tmp_vendor = $user_title_cache[$booking['b_vendor_id']];
			}
			else
			{
				$tmp_vendor = ilObjUser::_lookupLogin($booking['b_vendor_id']);
				$user_title_cache[$booking['b_vendor_id']] = $tmp_vendor;
			}
			if(array_key_exists($booking['customer_id'], $user_title_cache))
			{
				$tmp_purchaser = $user_title_cache[$booking['customer_id']];
			}
			else
			{
				$tmp_purchaser = ilObjUser::_lookupLogin($booking['customer_id']);
				$tmp_purchaser_name = ilObjUser::_lookupName($booking['customer_id']);
				$tmp_purchaser_email = ilObjUser::_lookupEmail($booking['customer_id']);			
				$user_title_cache[$booking['customer_id']] = $tmp_purchaser;
			}
			
			$transaction = $booking['transaction_extern'];
			switch ($booking['b_pay_method'])
			{
				case $this->pobject->PAY_METHOD_BILL :
					$transaction .= $booking['transaction']."<br> (" . $this->lng->txt("pays_bill") . ")";
					break;
				case $this->pobject->PAY_METHOD_BMF :
					$transaction .= $booking['transaction']." (" . $this->lng->txt("pays_bmf") . ")";
					break;
				case $this->pobject->PAY_METHOD_PAYPAL :
					$transaction .= $booking['transaction']." (" . $this->lng->txt("pays_paypal") . ")";
					break;
			}
			$f_result[$counter][] = $transaction;
			$f_result[$counter][] = ($tmp_obj != '' ?  $tmp_obj : $this->lng->txt('object_deleted'));
			$f_result[$counter][] = ($tmp_vendor != '' ?  '['.$tmp_vendor.']' : $this->lng->txt('user_deleted'));
			$f_result[$counter][] = ($tmp_purchaser != '' ? 
									$tmp_purchaser_name['firstname'].' '.$tmp_purchaser_name['lastname']. ' ['.$tmp_purchaser.']<br>'
									.$tmp_purchaser_email 
									: $this->lng->txt('user_deleted'));
			$f_result[$counter][] = date("Y-m-d H:i:s", $booking['order_date']);
			$f_result[$counter][] = $booking['duration'];
			

			$f_result[$counter][] = $booking['price'];
			$f_result[$counter][] = ($booking['discount'] != '' ? $booking['discount'] : '&nbsp;');

			$payed_access = $booking['payed'] ? 
				$this->lng->txt('yes') : 
				$this->lng->txt('no');

			$payed_access .= '/';
			$payed_access .= $booking['access'] ?
				$this->lng->txt('yes') : 
				$this->lng->txt('no');

			$f_result[$counter][] = $payed_access;

			$this->ctrl->setParameter($this,"booking_id",$booking['booking_id']);
#			$link_change = "<a href=\"".$this->ctrl->getLinkTarget($this,"editStatistic")."\"> ".
#				$img_change."</a>";
			$link_change = "<div class=\"il_ContainerItemCommands\"><a class=\"il_ContainerItemCommand\" href=\"".$this->ctrl->getLinkTarget($this,"editStatistic")."\">".$this->lng->txt("edit")."</a></div>";

			$f_result[$counter][] = $link_change;

			unset($tmp_obj);
			unset($tmp_vendor);
			unset($tmp_purchaser);

			++$counter;
		}
		return $this->__showStatisticTable($f_result);
	}
	
	function editStatisticObject($a_show_confirm_delete = false)
	{
		if(!isset($_GET['booking_id']))
		{
			ilUtil::sendInfo($this->lng->txt('paya_no_booking_id_given'));
			$this->showStatistics();
			

			return true;
		}

		$this->__showButton('statistic',$this->lng->txt('back'));

		$this->tpl->addBlockfile('ADM_CONTENT','adm_content','tpl.paya_adm_edit_statistic.html','payment');
		$this->ctrl->setParameter($this,'booking_id',(int) $_GET['booking_id']);

		// confirm delete
		if($a_show_confirm_delete)
		{
			$this->tpl->setCurrentBlock('confirm_delete');
			$this->tpl->setVariable('CONFIRM_FORMACTION',$this->ctrl->getFormAction($this));
			$this->tpl->setVariable('TXT_CANCEL',$this->lng->txt('cancel'));
			$this->tpl->setVariable('CONFIRM_CMD','performDelete');
			$this->tpl->setVariable('TXT_CONFIRM',$this->lng->txt('confirm'));
			$this->tpl->parseCurrentBlock();
		}
			

		$this->__initBookingObject();
		$bookings = $this->booking_obj->getBookings();
		$booking = $bookings[(int) $_GET['booking_id']];

		// get customer_obj
		$tmp_user = ilObjectFactory::getInstanceByObjId($booking['customer_id'], false);

		$this->tpl->setVariable('STAT_FORMACTION',$this->ctrl->getFormAction($this));
		$this->tpl->setVariable('TYPE_IMG',ilUtil::getImagePath('icon_usr.gif'));
		$this->tpl->setVariable('ALT_IMG',$this->lng->txt('obj_usr'));
		if(is_object($tmp_user))
		{
			$this->tpl->setVariable('TITLE', $tmp_user->getFullname().' ['.$tmp_user->getLogin().']');
		}
		else
		{
			$this->tpl->setVariable('TITLE', $this->lng->txt('user_deleted'));
		}

		// TXT
		$pObj = new ilPaymentObject($this->user_obj, $booking['pobject_id']);
		$tmp_obj = ilObject::_lookupTitle(ilObject::_lookupObjId($pObj->getRefId()));				

		$this->tpl->setVariable('TXT_OBJECT',$this->lng->txt('title'));
		$this->tpl->setVariable('OBJECT', ($tmp_obj != '' ?  $tmp_obj : $this->lng->txt('object_deleted')));

		$this->tpl->setVariable('TXT_TRANSACTION',$this->lng->txt('paya_transaction'));
		$this->tpl->setVariable('TXT_VENDOR',$this->lng->txt('paya_vendor'));
		$this->tpl->setVariable('TXT_PAY_METHOD',$this->lng->txt('paya_pay_method'));
		$this->tpl->setVariable('TXT_ORDER_DATE',$this->lng->txt('paya_order_date'));
		$this->tpl->setVariable('TXT_DURATION',$this->lng->txt('duration'));
		$this->tpl->setVariable('TXT_PRICE',$this->lng->txt('price_a'));
		$this->tpl->setVariable('TXT_PAYED',$this->lng->txt('paya_payed'));
		$this->tpl->setVariable('TXT_ACCESS',$this->lng->txt('paya_access'));

		$this->tpl->setVariable("TRANSACTION",$booking['transaction']);

		$tmp_vendor = ilObjectFactory::getInstanceByObjId($booking['b_vendor_id'], false);
		if(is_object($tmp_vendor))
		{
			$this->tpl->setVariable('VENDOR', $tmp_vendor->getFullname().' ['.$tmp_vendor->getLogin().']');
		}
		else
		{
			$this->tpl->setVariable('VENDOR', $this->lng->txt('user_deleted'));
		}

		switch($booking['b_pay_method'])
		{
			case $this->pobject->PAY_METHOD_BILL:
				$this->tpl->setVariable('PAY_METHOD',$this->lng->txt('pays_bill'));
				break;

			case $this->pobject->PAY_METHOD_BMF:
				$this->tpl->setVariable('PAY_METHOD',$this->lng->txt('pays_bmf'));
				break;

			case $this->pobject->PAY_METHOD_PAYPAL:
				$this->tpl->setVariable('PAY_METHOD',$this->lng->txt('pays_paypal'));
				break;

			default:
				$this->tpl->setVariable('PAY_METHOD',$this->lng->txt('paya_pay_method_not_specified'));
				break;
		}
		$this->tpl->setVariable('ORDER_DATE',date('Y m d H:i:s',$booking['order_date']));
		$this->tpl->setVariable('DURATION',$booking['duration'].' '.$this->lng->txt('paya_months'));
		$this->tpl->setVariable('PRICE',$booking['price']);
		
		$yes_no = array(0 => $this->lng->txt('no'),1 => $this->lng->txt('yes'));

		$this->tpl->setVariable('PAYED',ilUtil::formSelect((int) $booking['payed'],'payed',$yes_no,false,true));
		$this->tpl->setVariable('ACCESS',ilUtil::formSelect((int) $booking['access'],'access',$yes_no,false,true));

		// buttons
		$this->tpl->setVariable('INPUT_CMD','updateStatistic');
		$this->tpl->setVariable('INPUT_VALUE',$this->lng->txt('save'));

		$this->tpl->setVariable('DELETE_CMD','deleteStatistic');
		$this->tpl->setVariable('DELETE_VALUE',$this->lng->txt('delete'));
		$this->tpl->parseCurrentBlock();
		
		// show CUSTOMER_DATA if isset -> setting: save_customer_address
		include_once './payment/classes/class.ilGeneralSettings.php';
		$genSet = new ilGeneralSettings();
		$save_customer_address_enabled = $genSet->get('save_customer_address_enabled');

		if($save_customer_address_enabled == '1')
		{
			$this->showCustomerTable();
		}
	}
	function updateStatisticObject()
	{
		if(!isset($_GET['booking_id']))
		{
			ilUtil::sendInfo($this->lng->txt('paya_no_booking_id_given'));
			$this->statisticObject();

			return true;
		}
		$this->__initBookingObject();

		$this->booking_obj->setBookingId((int) $_GET['booking_id']);
		$this->booking_obj->setAccess((int) $_POST['access']);
		$this->booking_obj->setPayed((int) $_POST['payed']);
		
		if($this->booking_obj->update())
		{
			ilUtil::sendInfo($this->lng->txt('paya_updated_booking'));

			$this->statisticObject();
			return true;
		}
		else
		{
			ilUtil::sendInfo($this->lng->txt('paya_error_update_booking'));

			$this->statisticObject();
			
			return true;
		}
	}

	function deleteStatisticObject()
	{
		if(!isset($_GET['booking_id']))
		{
			ilUtil::sendInfo($this->lng->txt('paya_no_booking_id_given'));
			$this->statisticObject();

			return true;
		}
		ilUtil::sendInfo($this->lng->txt('paya_sure_delete_stat'));

		$this->editStatisticObject(true);

		return true;
	}
	function performDeleteObject()
	{
		if(!isset($_GET['booking_id']))
		{
			ilUtil::sendInfo($this->lng->txt('paya_no_booking_id_given'));
			$this->statisticObject();

			return true;
		}

		$this->__initBookingObject();
		$this->booking_obj->setBookingId((int) $_GET['booking_id']);
		if(!$this->booking_obj->delete())
		{
			die('Error deleting booking');
		}
		ilUtil::sendInfo($this->lng->txt('pay_deleted_booking'));

		$this->statisticObject();

		return true;
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
		global $rbacsystem;

		if ($rbacsystem->checkAccess('visible,read',$this->object->getRefId()))
		{
			$tabs_gui->addTarget('settings',
				$this->ctrl->getLinkTarget($this, 'generalSettings'), array('generalSettings','', 'view'), '', '');
				
			$tabs_gui->addTarget('bookings',
				$this->ctrl->getLinkTarget($this, 'statistic'), 'statistic', '', '');
				
			$tabs_gui->addTarget('objects',
				$this->ctrl->getLinkTarget($this, 'objects'), 'objects', '', '');
				
			$tabs_gui->addTarget('vendors',
				$this->ctrl->getLinkTarget($this, 'vendors'), 'vendors', '', '');
				
			$tabs_gui->addTarget('pay_methods',
				$this->ctrl->getLinkTarget($this, 'payMethods'), 'payMethods', '', '');
			
			$tabs_gui->addTarget('topics',
					$this->ctrl->getLinkTargetByClass('ilshoptopicsgui', ''), 'payment_topics', '', '');

			$tabs_gui->addTarget('vats',
					$this->ctrl->getLinkTarget($this, 'vats'), 'vats', '', '');				
		}

		if ($rbacsystem->checkAccess('edit_permission',$this->object->getRefId()))
		{
			$tabs_gui->addTarget('perm_settings',
				$this->ctrl->getLinkTargetByClass(array(get_class($this),'ilpermissiongui'), 'perm'), array('perm','info','owner'), 'ilpermissiongui');
		}
	}

	function generalSettingsObject($a_show_confirm = false)
	{	
		global $rbacsystem;

		// MINIMUM ACCESS LEVEL = 'read'
		if(!$rbacsystem->checkAccess('read', $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt('msg_no_perm_read'),$this->ilias->error_obj->MESSAGE);
		}
		
		include_once './payment/classes/class.ilGeneralSettings.php';

		$this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.pays_general_settings.html','payment');		

		$genSet = new ilGeneralSettings();
		$genSetData = $genSet->getAll();		
				
		include_once('./Services/Form/classes/class.ilPropertyFormGUI.php');			
						
		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this, 'saveGeneralSettings'));
		$form->setTitle($this->lng->txt('pays_general_settings'));
		
		$form->addCommandButton('saveGeneralSettings',$this->lng->txt('save'));
		
		// enable webshop
		$formItem = new ilCheckboxInputGUI($this->lng->txt('pay_enable_shop'), 'shop_enabled');
		$formItem->setChecked((int)$genSetData['shop_enabled']);
		$formItem->setInfo($this->lng->txt('pay_enable_shop_info'));
		$form->addItem($formItem);
		
		$formItem = new ilTextInputGUI($this->lng->txt('pays_currency_unit'), 'currency_unit');
		$formItem->setSize(5);
		$formItem->setValue($this->error != '' && isset($_POST['currency_unit'])
						? ilUtil::prepareFormOutput($_POST['currency_unit'],true)
						: ilUtil::prepareFormOutput($genSetData['currency_unit'],true));
		$form->addItem($formItem);
		
		$formItem = new ilTextInputGUI($this->lng->txt('pays_currency_subunit'), 'currency_subunit');
		$formItem->setSize(5);
		$formItem->setValue($this->error != '' && isset($_POST['currency_subunit'])
							? ilUtil::prepareFormOutput($_POST['currency_subunit'],true)
							: ilUtil::prepareFormOutput($genSetData['currency_subunit'],true));
		$form->addItem($formItem);
		
		$formItem = new ilTextAreaInputGUI($this->lng->txt('pays_address'), 'address');
		$formItem->setRows(7);
		$formItem->setCols(35);
		$formItem->setValue($this->error != '' && isset($_POST['address'])
							? ilUtil::prepareFormOutput($_POST['address'],true)
							: ilUtil::prepareFormOutput($genSetData['address'],true));
		$form->addItem($formItem);
		
		$formItem = new ilTextAreaInputGUI($this->lng->txt('pays_bank_data'), 'bank_data');
		$formItem->setRows(7);
		$formItem->setCols(35);
		$formItem->setValue($this->error != '' && isset($_POST['bank_data'])
							? ilUtil::prepareFormOutput($_POST['bank_data'],true)
							: ilUtil::prepareFormOutput($genSetData['bank_data'],true));
		$form->addItem($formItem);
		
		$formItem = new ilTextAreaInputGUI($this->lng->txt('pays_add_info'), 'add_info');
		$formItem->setRows(7);
		$formItem->setCols(35);
		$formItem->setValue($this->error != '' && isset($_POST['add_info'])
							? ilUtil::prepareFormOutput($_POST['add_info'],true)
							: ilUtil::prepareFormOutput($genSetData['add_info'],true));
		$form->addItem($formItem);
/*  
		$formItem = new ilTextInputGUI($this->lng->txt('pays_vat_rate'), 'vat_rate');
		$formItem->setSize(5);
		$formItem->setValue($this->error != "" && isset($_POST['vat_rate'])
							? ilUtil::prepareFormOutput($_POST['vat_rate'],true)
							: ilUtil::prepareFormOutput($genSetData['vat_rate'],true));
		$form->addItem($formItem);
*/		
		$formItem = new ilTextInputGUI($this->lng->txt('pays_pdf_path'), 'pdf_path');
		$formItem->setValue($this->error != "" && isset($_POST['pdf_path'])
							? ilUtil::prepareFormOutput($_POST['pdf_path'],true)
							: ilUtil::prepareFormOutput($genSetData['pdf_path'],true));
		$form->addItem($formItem);

		// customer address
		$formItem = new ilCheckboxInputGUI($this->lng->txt('save_customer_address'), 'save_customer_address_enabled');
		$formItem->setChecked((int)$genSetData['save_customer_address_enabled']);
		$formItem->setInfo($this->lng->txt('save_customer_address_info'));
		$form->addItem($formItem);
		
		// default sorting type
		$formItem = new ilSelectInputGUI($this->lng->txt('pay_topics_default_sorting_type'), 'topics_sorting_type');
		$formItem->setValue($genSetData['topics_sorting_type']);
		$options = array(
			1 => $this->lng->txt('pay_topics_sort_by_title'),
			2 => $this->lng->txt('pay_topics_sort_by_date'),
			3 => $this->lng->txt('pay_topics_sort_manually')
		);
		$formItem->setOptions($options);
		$form->addItem($formItem);
		
		// default sorting direction
		$formItem = new ilSelectInputGUI($this->lng->txt('pay_topics_default_sorting_direction'), 'topics_sorting_direction');
		$formItem->setValue($genSetData['topics_sorting_direction']);
		$options = array(
			'asc' => $this->lng->txt('sort_asc'),
			'desc' => $this->lng->txt('sort_desc'),
		);
		$formItem->setOptions($options);
		$form->addItem($formItem);
		
		// custom sorting
		$formItem = new ilCheckboxInputGUI($this->lng->txt('pay_topics_allow_custom_sorting'), 'topics_allow_custom_sorting');
		$formItem->setChecked((int)$genSetData['topics_allow_custom_sorting']);
		$formItem->setInfo($this->lng->txt('pay_topics_allow_custom_sorting_info'));
		$form->addItem($formItem);
		
		// max hits
		$formItem = new ilSelectInputGUI($this->lng->txt('pay_max_hits'), 'max_hits');
		$formItem->setValue($genSetData['max_hits']);
		$options = array();
		for($i = 10; $i <= 100; $i += 10)
		{
			$options[$i] = $i;
		}
		$formItem->setOptions($options);
		$formItem->setInfo($this->lng->txt('pay_max_hits_info'));
		$form->addItem($formItem);
				
		$this->tpl->setVariable('GENERAL_SETTINGS',$form->getHTML());
	}
	
	function saveGeneralSettingsObject()
	{
		global $rbacsystem;

		// MINIMUM ACCESS LEVEL = 'read'
		if(!$rbacsystem->checkAccess('read', $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt('msg_no_perm_read'),$this->ilias->error_obj->MESSAGE);
		}
		
		include_once './payment/classes/class.ilGeneralSettings.php';

		$genSet = new ilGeneralSettings();

		if ($_POST['currency_unit'] == '' ||
			$_POST['currency_subunit'] == '' ||
			$_POST['address'] == '' ||
			$_POST['bank_data'] == '' ||
			$_POST['pdf_path'] == '')
		{
			$this->error = $this->lng->txt('pays_general_settings_not_valid');
			ilUtil::sendInfo($this->error);
			$this->generalSettingsObject();
			return;
		}
		
		$genSet->clearAll();
		$values = array(
			'currency_unit' => ilUtil::stripSlashes($_POST['currency_unit']),
			'currency_subunit' => ilUtil::stripSlashes($_POST['currency_subunit']),
			'address' => ilUtil::stripSlashes($_POST['address']),
			'bank_data' => ilUtil::stripSlashes($_POST['bank_data']),
			'add_info' => ilUtil::stripSlashes($_POST['add_info']),
//			'vat_rate' => (float) str_replace(',', '.', ilUtil::stripSlashes($_POST['vat_rate'])),
			'pdf_path' => ilUtil::stripSlashes($_POST['pdf_path']),
			'topics_allow_custom_sorting' => ilUtil::stripSlashes($_POST['topics_allow_custom_sorting']),
			'topics_sorting_type' => ilUtil::stripSlashes($_POST['topics_sorting_type']),
			'topics_sorting_direction' => ilUtil::stripSlashes($_POST['topics_sorting_direction']),
			'max_hits' => ilUtil::stripSlashes($_POST['max_hits']),
			'shop_enabled' => ilUtil::stripSlashes($_POST['shop_enabled']),	
			'save_customer_address_enabled' => ilUtil::stripSlashes($_POST['save_customer_address_enabled'])		
		);
		$genSet->setAll($values);
		$this->generalSettingsObject();

		ilUtil::sendInfo($this->lng->txt('pays_updated_general_settings'));

		return true;
	}

	function paypalSettingsObject($a_show_confirm = false)
	{	
		global $rbacsystem;

		// MINIMUM ACCESS LEVEL = 'read'
		if(!$rbacsystem->checkAccess('read', $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt('msg_no_perm_read'),$this->ilias->error_obj->MESSAGE);
		}
		
		include_once('./Services/Form/classes/class.ilPropertyFormGUI.php');
		include_once './payment/classes/class.ilPaypalSettings.php';		

		$this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.pays_paypal_settings.html','payment');
		
		$ppSet = ilPaypalSettings::getInstance();
				
		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this, 'savePaypalSettings'));
		$form->setTitle($this->lng->txt('pays_paypal_settings'));
		
		$form->addCommandButton('savePaypalSettings',$this->lng->txt('save'));
		
		$formItem = new ilTextInputGUI($this->lng->txt('pays_server_host'), 'server_host');
		$formItem->setValue($ppSet->getServerHost());
		$form->addItem($formItem);
		
		$formItem = new ilTextInputGUI($this->lng->txt('pays_server_path'), 'server_path');
		$formItem->setValue($ppSet->getServerPath());
		$form->addItem($formItem);
		
		$formItem = new ilTextInputGUI($this->lng->txt('pays_email_vendor'), 'vendor');
		$formItem->setValue($ppSet->getVendor());
		$form->addItem($formItem);
		
		$formItem = new ilTextInputGUI($this->lng->txt('pays_auth_token'), 'auth_token');
		$formItem->setValue($ppSet->getAuthToken());
		$form->addItem($formItem);
		
		$formItem = new ilTextInputGUI($this->lng->txt('pays_page_style'), 'page_style');
		$formItem->setValue($ppSet->getPageStyle());
		$form->addItem($formItem);
				
		$this->tpl->setVariable('PAYPAL_SETTINGS',$form->getHTML());		
	}
	
	function savePaypalSettingsObject()
	{
		include_once './payment/classes/class.ilPaypalSettings.php';

		global $rbacsystem;

		// MINIMUM ACCESS LEVEL = 'read'
		if(!$rbacsystem->checkAccess('read', $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt('msg_no_perm_read'),$this->ilias->error_obj->MESSAGE);
		}
		
		$ppSet = ilPaypalSettings::getInstance();
		
		$ppSet->setServerHost(ilUtil::stripSlashes($_POST['server_host']));
		$ppSet->setServerPath(ilUtil::stripSlashes($_POST['server_path']));
		$ppSet->setVendor(ilUtil::stripSlashes($_POST['vendor']));
		$ppSet->setAuthToken(ilUtil::stripSlashes($_POST['auth_token']));
		$ppSet->setPageStyle(ilUtil::stripSlashes($_POST['page_style']));
		$ppSet->setSsl(ilUtil::stripSlashes($_POST['ssl']));		

		if ($_POST['server_host'] == '' ||
			$_POST['server_path'] == '' ||
			$_POST['vendor'] == '' ||
			$_POST['auth_token'] == '')
		{
			$this->error = $this->lng->txt('pays_paypal_settings_not_valid');
			ilUtil::sendInfo($this->error);
			$this->paypalSettingsObject();
			return;
		}
		
		$ppSet->save();
				
		$this->paypalSettingsObject();

		ilUtil::sendInfo($this->lng->txt('pays_updated_paypal_settings'));

		return true;
	}

	function vendorsObject($a_show_confirm = false)
	{
		include_once './payment/classes/class.ilPaymentBookings.php';
	
		global $rbacsystem;

		// MINIMUM ACCESS LEVEL = 'read'
		if(!$rbacsystem->checkAccess('read', $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt('msg_no_perm_read'),$this->ilias->error_obj->MESSAGE);
		}

		$_SESSION['pays_vendor'] = is_array($_SESSION['pays_vendor']) ?  $_SESSION['pays_vendor'] : array();
		

		$this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.pays_vendors.html','payment');
		
		$this->__showButton('searchUser',$this->lng->txt('search_user'));

		$this->object->initPaymentVendorsObject();
		if(!count($vendors = $this->object->payment_vendors_obj->getVendors()))
		{
			ilUtil::sendInfo($this->lng->txt('pay_no_vendors_created'));
		}
#		else
#		{
#			$this->__showButton('exportVendors',$this->lng->txt('excel_export'));
#		}



		if($a_show_confirm)
		{
			$this->tpl->setCurrentBlock('confirm_delete');
			$this->tpl->setVariable('CONFIRM_FORMACTION',$this->ctrl->getFormAction($this));
			$this->tpl->setVariable('TXT_CANCEL',$this->lng->txt('cancel'));
			$this->tpl->setVariable('CONFIRM_CMD','performDeleteVendors');
			$this->tpl->setVariable('TXT_CONFIRM',$this->lng->txt('delete'));
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
				$f_result[$counter][]	= ilPaymentBookings::_getCountBookingsByVendor($vendor['vendor_id']);
				
				unset($tmp_obj);
				++$counter;
			}
		} // END VENDORS TABLE
		$this->__showVendorsTable($f_result);

		return true;
	}

	function exportVendorsObject()
	{
		include_once './payment/classes/class.ilPaymentExcelWriterAdapter.php';

//$_ENV['TMPDIR'] = '/home/nkrzywon/public_html/ilias310/extern';
		$pewa =& new ilPaymentExcelWriterAdapter('payment_vendors.xls');

		// add/fill worksheet
		$this->addVendorWorksheet($pewa);
		$this->addStatisticWorksheet($pewa);

		// HEADER SENT
		
		$workbook =& $pewa->getWorkbook();
		$workbook->close();
	}

	function addStatisticWorksheet(&$pewa)
	{
		include_once './classes/class.ilExcelUtils.php';
		include_once './payment/classes/class.ilPaymentVendors.php';

		$this->__initBookingObject();

		if(!count($bookings = $this->booking_obj->getBookings()))
		{
			return false;
		}

		$workbook =& $pewa->getWorkbook();
		//$worksheet =& $workbook->addWorksheet(utf8_decode($this->lng->txt('paya_statistic')));
		$worksheet =& $workbook->addWorksheet(utf8_decode($this->lng->txt('bookings')));
		
		$worksheet->mergeCells(0,0,0,3);
		$worksheet->setColumn(0,0,16);
		$worksheet->setColumn(0,1,32);
		$worksheet->setColumn(0,2,32);
		$worksheet->setColumn(0,3,16);
		$worksheet->setColumn(0,4,16);
		$worksheet->setColumn(0,5,16);
		$worksheet->setColumn(0,6,24);
		$worksheet->setColumn(0,7,8);
		$worksheet->setColumn(0,8,12);
		$worksheet->setColumn(0,9,16);

		//$title = $this->lng->txt('paya_statistic');
		$title = $this->lng->txt('bookings');
		$title .= ' '.$this->lng->txt('as_of').' ';
		$title .= strftime('%Y-%m-%d %R',time());

		$worksheet->writeString(0,0,$title,$pewa->getFormatTitle());

		$worksheet->writeString(1,0,ilExcelUtils::_convert_text($this->lng->txt('payment_system')),$pewa->getFormatHeader());
		$worksheet->writeString(1,1,ilExcelUtils::_convert_text($this->lng->txt('paya_transaction')),$pewa->getFormatHeader());
		$worksheet->writeString(1,2,ilExcelUtils::_convert_text($this->lng->txt('title')),$pewa->getFormatHeader());
		$worksheet->writeString(1,3,ilExcelUtils::_convert_text($this->lng->txt('paya_vendor')),$pewa->getFormatHeader());
		$worksheet->writeString(1,4,ilExcelUtils::_convert_text($this->lng->txt('pays_cost_center')),$pewa->getFormatHeader());
		$worksheet->writeString(1,5,ilExcelUtils::_convert_text($this->lng->txt('paya_customer')),$pewa->getFormatHeader());
		$worksheet->writeString(1,6,ilExcelUtils::_convert_text($this->lng->txt('email')),$pewa->getFormatHeader());
		$worksheet->writeString(1,7,ilExcelUtils::_convert_text($this->lng->txt('paya_order_date')),$pewa->getFormatHeader());
		$worksheet->writeString(1,8,ilExcelUtils::_convert_text($this->lng->txt('duration')),$pewa->getFormatHeader());
		$worksheet->writeString(1,9,ilExcelUtils::_convert_text($this->lng->txt('price_a')),$pewa->getFormatHeader());
		$worksheet->writeString(1,10,ilExcelUtils::_convert_text($this->lng->txt('paya_payed_access')),$pewa->getFormatHeader());
		
		$worksheet->writeString(1,11,ilExcelUtils::_convert_text($this->lng->txt('street')),$pewa->getFormatHeader());
		$worksheet->writeString(1,12,ilExcelUtils::_convert_text($this->lng->txt('pay_bmf_po_box')),$pewa->getFormatHeader());
		$worksheet->writeString(1,13,ilExcelUtils::_convert_text($this->lng->txt('zipcode')),$pewa->getFormatHeader());
		$worksheet->writeString(1,14,ilExcelUtils::_convert_text($this->lng->txt('city')),$pewa->getFormatHeader());
		$worksheet->writeString(1,15,ilExcelUtils::_convert_text($this->lng->txt('country')),$pewa->getFormatHeader());
		

		include_once 'Services/User/classes/class.ilObjUser.php';
		$object_title_cache = array();
		$user_title_cache = array();

		$counter = 2;
		foreach($bookings as $booking)
		{
			if(array_key_exists($booking['ref_id'], $object_title_cache))
			{
				$tmp_obj = $object_title_cache[$booking['ref_id']];
			}
			else
			{
				$tmp_obj = ilObject::_lookupTitle(ilObject::_lookupObjId($booking['ref_id']));				
				$object_title_cache[$booking['ref_id']] = $tmp_obj;
			}
			if(array_key_exists($booking['b_vendor_id'], $user_title_cache))
			{
				$tmp_vendor = $user_title_cache[$booking['b_vendor_id']];
			}
			else
			{
				$tmp_vendor = ilObjUser::_lookupLogin($booking['b_vendor_id']);
				$user_title_cache[$booking['b_vendor_id']] = $tmp_vendor;
			}
			if(array_key_exists($booking['customer_id'], $user_title_cache))
			{
				$tmp_purchaser = $user_title_cache[$booking['customer_id']];
			}
			else
			{
				$tmp_purchaser_name = ilObjUser::_lookupName($booking['customer_id']);
				$tmp_purchaser_login = ilObjUser::_lookupLogin($booking['customer_id']);
				$tmp_purchaser_email = ilObjUser::_lookupEmail($booking['customer_id']);
				$tmp_purchaser = ''.$tmp_purchaser_name['firstname'].' '.$tmp_purchaser_name['lastname'].' ['.$tmp_purchaser_login.']';
				$user_title_cache[$booking['customer_id']] = $tmp_purchaser;
			}
			
			switch ($booking['b_pay_method'])
			{
				case $this->pobject->PAY_METHOD_BILL :
					$pay_method = $this->lng->txt('pays_bill');
					break;
				case $this->pobject->PAY_METHOD_BMF :
					$pay_method = $this->lng->txt('pays_bmf');
					break;
				case $this->pobject->PAY_METHOD_PAYPAL :
					$pay_method = $this->lng->txt('pays_paypal');
					break;
			}
			$worksheet->writeString($counter,0,ilExcelUtils::_convert_text($pay_method));
			$worksheet->writeString($counter,1,ilExcelUtils::_convert_text($booking['transaction']));
			$worksheet->writeString($counter,2,ilExcelUtils::_convert_text(($tmp_obj != '' ? $tmp_obj : $this->lng->txt('object_deleted'))));
			$worksheet->writeString($counter,3,ilExcelUtils::_convert_text(($tmp_vendor != '' ? $tmp_vendor : $this->lng->txt('user_deleted'))));
			$worksheet->writeString($counter,4,ilExcelUtils::_convert_text(ilPaymentVendors::_getCostCenter($booking['b_vendor_id'])));
			$worksheet->writeString($counter,5,ilExcelUtils::_convert_text(($tmp_purchaser != '' ? $tmp_purchaser : $this->lng->txt('user_deleted'))));
			$worksheet->writeString($counter,6,ilExcelUtils::_convert_text($tmp_purchaser_email));
			$worksheet->writeString($counter,7,strftime('%Y-%m-%d %R',$booking['order_date']));
			$worksheet->writeString($counter,8,$booking['duration']);
			$worksheet->writeString($counter,9,ilExcelUtils::_convert_text($booking['price']));
			
			$payed_access = $booking['payed'] ? 
				$this->lng->txt('yes') : 
				$this->lng->txt('no');

			$payed_access .= '/';
			$payed_access .= $booking['access'] ?
				$this->lng->txt('yes') : 
				$this->lng->txt('no');

			$worksheet->writeString($counter,10,$payed_access);

			$worksheet->writeString($counter,11,ilExcelUtils::_convert_text($booking['street']));
			$worksheet->writeString($counter,12,ilExcelUtils::_convert_text($booking['po_box']));
			$worksheet->writeString($counter,13,ilExcelUtils::_convert_text($booking['zipcode']));
			$worksheet->writeString($counter,14,ilExcelUtils::_convert_text($booking['city']));
			$worksheet->writeString($counter,15,ilExcelUtils::_convert_text($booking['country']));
			
			unset($tmp_obj);
			unset($tmp_vendor);
			unset($tmp_purchaser);

			++$counter;
		}
	}		

	function addVendorWorksheet(&$pewa)
	{
		include_once './classes/class.ilExcelUtils.php';

		$this->object->initPaymentVendorsObject();
		if(!count($vendors = $this->object->payment_vendors_obj->getVendors()))
		{
			return false;
		}

		$workbook =& $pewa->getWorkbook();
		$worksheet =& $workbook->addWorksheet(ilExcelUtils::_convert_text($this->lng->txt('pays_vendor')));

		// SHOW HEADER
		$worksheet->mergeCells(0,0,0,2);
		$worksheet->setColumn(1,0,32);
		$worksheet->setColumn(1,1,32);
		$worksheet->setColumn(1,2,32);

		$title = $this->lng->txt('bookings');
		$title .= ' '.$this->lng->txt('as_of').' ';
		$title .= strftime('%Y-%m-%d %R',time());

		$worksheet->writeString(0,0,$title,$pewa->getFormatTitle());

		$worksheet->writeString(1,0,ilExcelUtils::_convert_text($this->lng->txt('login')),$pewa->getFormatHeader());
		$worksheet->writeString(1,1,ilExcelUtils::_convert_text($this->lng->txt('fullname')),$pewa->getFormatHeader());
		$worksheet->writeString(1,2,ilExcelUtils::_convert_text($this->lng->txt('pays_cost_center')),$pewa->getFormatHeader());

		$counter = 2;
		foreach($vendors as $vendor)
		{
			// GET USER OBJ
			if($tmp_obj = ilObjectFactory::getInstanceByObjId($vendor['vendor_id'],false))
			{
				$worksheet->writeString($counter,0,ilExcelUtils::_convert_text($tmp_obj->getLogin()));
				$worksheet->writeString($counter,1,ilExcelUtils::_convert_text($tmp_obj->getFullname()));
				$worksheet->writeString($counter,2,ilExcelUtils::_convert_text($vendor['cost_center']));
			}
			unset($tmp_obj);
			++$counter;
		}
	}		
	
	function payMethodsObject()
	{
		include_once './payment/classes/class.ilPayMethods.php';

		global $rbacsystem;

		// MINIMUM ACCESS LEVEL = 'read'
		if(!$rbacsystem->checkAccess('read', $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt('msg_no_perm_read'),$this->ilias->error_obj->MESSAGE);
		}

		$this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.pays_pay_methods.html','payment');

		$this->tpl->setVariable('FORMACTION',$this->ctrl->getFormAction($this));
		$this->tpl->setVariable('TYPE_IMG',ilUtil::getImagePath('icon_pays.gif'));
		$this->tpl->setVariable('ALT_IMG',$this->lng->txt('obj_pays'));
		$this->tpl->setVariable('TITLE',$this->lng->txt('pays_pay_methods'));
		$this->tpl->setVariable('TXT_ONLINE',$this->lng->txt('pays_online'));
		$this->tpl->setVariable('TXT_BILL',$this->lng->txt('pays_bill'));
		$this->tpl->setVariable('BILL_CHECK',ilUtil::formCheckbox(
									(int) ilPayMethods::_enabled('pm_bill') ? 1 : 0,'pm_bill',1));

		$this->tpl->setVariable('TXT_ENABLED',$this->lng->txt('enabled'));
		$this->tpl->setVariable('TXT_ONLINE',$this->lng->txt('pays_online'));
		$this->tpl->setVariable('TXT_BMF',$this->lng->txt('pays_bmf'));
		$this->tpl->setVariable('BMF_ONLINE_CHECK',ilUtil::formCheckbox((int) ilPayMethods::_enabled('pm_bmf'),'pm_bmf',1));
		
		$this->tpl->setVariable('TXT_ENABLED',$this->lng->txt('enabled'));
		$this->tpl->setVariable('TXT_ONLINE',$this->lng->txt('pays_online'));
		$this->tpl->setVariable('TXT_PAYPAL',$this->lng->txt('pays_paypal'));
		$this->tpl->setVariable('PAYPAL_ONLINE_CHECK',ilUtil::formCheckbox((int) ilPayMethods::_enabled('pm_paypal'),'pm_paypal',1));
		
		// footer
		$this->tpl->setVariable('COLUMN_COUNT',3);
		$this->tpl->setVariable('PBTN_NAME','savePayMethods');
		$this->tpl->setVariable('PBTN_VALUE',$this->lng->txt('save'));
		
	}

	function savePayMethodsObject()
	{
		include_once './payment/classes/class.ilPayMethods.php';
		include_once './payment/classes/class.ilPaymentObject.php';


		global $rbacsystem;

		// MINIMUM ACCESS LEVEL = 'read'
		if(!$rbacsystem->checkAccess('read', $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt('msg_no_perm_read'),$this->ilias->error_obj->MESSAGE);
		}
		

		// check current payings
		if(ilPayMethods::_enabled('pm_bill') and !$_POST['pm_bill'])
		{
			if(ilPaymentObject::_getCountObjectsByPayMethod('pm_bill'))
			{
				ilUtil::sendInfo($this->lng->txt('pays_objects_bill_exist'));
				$this->payMethodsObject();

				return false;
			}
		}

		if(ilPayMethods::_enabled('pm_bmf') and !$_POST['pm_bmf'])
		{
			if(ilPaymentObject::_getCountObjectsByPayMethod('pm_bmf'))
			{
				ilUtil::sendInfo($this->lng->txt('pays_objects_bmf_exist'));
				$this->payMethodsObject();

				return false;
			}
		}

		if(ilPayMethods::_enabled('pm_paypal') and !$_POST['pm_paypal'])
		{
			if(ilPaymentObject::_getCountObjectsByPayMethod('pm_paypal'))
			{
				ilUtil::sendInfo($this->lng->txt('pays_objects_paypal_exist'));
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
		if(isset($_POST['pm_paypal']))
		{
			ilPayMethods::_enable('pm_paypal');
		}
		$this->payMethodsObject();

		ilUtil::sendInfo($this->lng->txt('pays_updated_pay_method'));

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
		include_once './payment/classes/class.ilPaymentBookings.php';

		if(!count($_POST['vendor']))
		{
			ilUtil::sendInfo($this->lng->txt('pays_no_vendor_selected'));
			$this->vendorsObject();

			return true;
		}
		// CHECK BOOKINGS
		foreach($_POST['vendor'] as $vendor)
		{
			if(ilPaymentBookings::_getCountBookingsByVendor($vendor))
			{
				ilUtil::sendInfo($this->lng->txt('pays_active_bookings'));
				$this->vendorsObject();

				return true;
			}
		}
		
		$_SESSION['pays_vendor'] = $_POST['vendor'];
		ilUtil::sendInfo($this->lng->txt('pays_sure_delete_selected_vendors'));
		$this->vendorsObject(true);

		return true;
	}
	function performDeleteVendorsObject()
	{
		include_once './payment/classes/class.ilPaymentTrustees.php';
		
		global $rbacsystem;

		// MINIMUM ACCESS LEVEL = 'read'
		if(!$rbacsystem->checkAccess('write', $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt('msg_no_perm_write'),$this->ilias->error_obj->MESSAGE);
		}

		$this->object->initPaymentVendorsObject();

		foreach($_SESSION['pays_vendor'] as $vendor)
		{
			$this->object->payment_vendors_obj->delete($vendor);
			ilPaymentTrustees::_deleteTrusteesOfVendor($vendor);
		}

		ilUtil::sendInfo($this->lng->txt('pays_deleted_number_vendors').' '.count($_SESSION['pays_vendor']));
		unset($_SESSION['pays_vendor']);
		
		$this->vendorsObject();

		return true;
	}

	function editVendor()
	{
		global $rbacsystem;

		// MINIMUM ACCESS LEVEL = 'read'
		if(!$rbacsystem->checkAccess("write", $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilias->error_obj->MESSAGE);
		}

		if(!count($_POST['vendor']))
		{
			ilUtil::sendInfo($this->lng->txt('pays_no_vendor_selected'));
			$this->vendorsObject();

			return true;
		}
		if(count($_POST['vendor']) > 1)
		{
			ilUtil::sendInfo($this->lng->txt('pays_too_many_vendors_selected'));
			$this->vendorsObject();

			return true;
		}

		$_SESSION['pays_vendor'] = $_POST['vendor'][0];

		$this->object->initPaymentVendorsObject();

		if (!is_array($this->object->payment_vendors_obj->vendors[$_SESSION['pays_vendor']]))
		{
			$this->vendorsObject();

			return true;
		}

		$this->tpl->addBlockfile('ADM_CONTENT','adm_content','tpl.pays_vendor.html','payment');

		$this->tpl->setVariable('VENDOR_FORMACTION',$this->ctrl->getFormAction($this));

		// set table header
		$this->tpl->setVariable('TYPE_IMG',ilUtil::getImagePath('icon_usr.gif'));
		$this->tpl->setVariable('ALT_IMG',$this->lng->txt('obj_usr'));
		$this->tpl->setVariable('TITLE',$this->lng->txt('pays_vendor'));

		// set plain text variables
		$this->tpl->setVariable('TXT_VENDOR',$this->lng->txt('pays_vendor'));
		$this->tpl->setVariable('TXT_COST_CENTER',$this->lng->txt('pays_cost_center'));

		$this->tpl->setVariable('INPUT_VALUE',ucfirst($this->lng->txt('save')));

		// fill defaults

		$this->tpl->setVariable('VENDOR',
								ilObjUser::getLoginByUserId($this->object->payment_vendors_obj->vendors[$_SESSION['pays_vendor']]['vendor_id']), true);
		$this->tpl->setVariable('COST_CENTER',
								$this->error != '' && isset($_POST['cost_center'])
								? ilUtil::prepareFormOutput($_POST['cost_center'],true)
								: ilUtil::prepareFormOutput($this->object->payment_vendors_obj->vendors[$_SESSION['pays_vendor']]['cost_center'],true));

		// Button
		$this->tpl->addBlockfile('BUTTONS', 'buttons', 'tpl.buttons.html');
		$this->tpl->setCurrentBlock('btn_cell');
		$this->tpl->setVariable('BTN_LINK', $this->ctrl->getLinkTarget($this, 'vendors'));
		$this->tpl->setVariable('BTN_TXT', $this->lng->txt('pay_bmf_back'));
		$this->tpl->parseCurrentBlock('btn_cell');

	}
	function performEditVendorObject()
	{
		global $rbacsystem;

		// MINIMUM ACCESS LEVEL = 'read'
		if(!$rbacsystem->checkAccess('write', $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt('msg_no_perm_write'),$this->ilias->error_obj->MESSAGE);
		}

		if(!count($_SESSION['pays_vendor']))
		{
			ilUtil::sendInfo($this->lng->txt('pays_no_vendor_selected'));
			$this->vendorsObject();

			return true;
		}
		if(count($_SESSION['pays_vendor']) > 1)
		{
			ilUtil::sendInfo($this->lng->txt('pays_too_many_vendors_selected'));
			$this->vendorsObject();

			return true;
		}

		$this->object->initPaymentVendorsObject();

		if (!is_array($this->object->payment_vendors_obj->vendors[$_SESSION['pays_vendor']]))
		{
			$this->vendorsObject();

			return true;
		}

		if ($_POST['cost_center'] == '')
		{
			$this->error = $this->lng->txt('pays_cost_center_not_valid');
			ilUtil::sendInfo($this->error);
			$_POST['vendor'] = array($_SESSION['pays_vendor']);
			$this->editVendor();
			return;
		}

		$this->object->initPaymentVendorsObject();
		$this->object->payment_vendors_obj->update($_SESSION['pays_vendor'], $_POST['cost_center']);

		unset($_SESSION['pays_vendor']);

		$this->vendorsObject();

		return true;
	}

	function showObjectSelectorObject()
	{
		global $rbacsystem, $tree;

		// MINIMUM ACCESS LEVEL = 'read'
		if(!$rbacsystem->checkAccess('read', $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt('msg_no_perm_read'),$this->ilias->error_obj->MESSAGE);
		}

		include_once './payment/classes/class.ilPaymentObjectSelector.php';

		$this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.paya_object_selector.html','payment');
		//$this->__showButton('statistic',$this->lng->txt('back'));
		$this->__showButton('bookings',$this->lng->txt('back'));


		ilUtil::sendInfo($this->lng->txt('paya_select_object_to_sell'));

		$exp = new ilPaymentObjectSelector($this->ctrl->getLinkTarget($this,'showObjectSelector'), strtolower(get_class($this)));
		$exp->setExpand($_GET['paya_link_expand'] ? $_GET['paya_link_expand'] : $tree->readRootId());
		$exp->setExpandTarget($this->ctrl->getLinkTarget($this,'showObjectSelector'));
		
		$exp->setOutput(0);

		$this->tpl->setVariable('EXPLORER',$exp->getOutput());

		return true;
	}

	function searchUserObject()
	{
		global $rbacsystem;

		// MINIMUM ACCESS LEVEL = 'read'
		if(!$rbacsystem->checkAccess('read', $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt('msg_no_perm_read'),$this->ilias->error_obj->MESSAGE);
		}

		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.pays_user_search.html','payment');
		$this->__showButton('vendors',$this->lng->txt('back'));

		$this->lng->loadLanguageModule('search');

		$this->tpl->setVariable('F_ACTION',$this->ctrl->getFormAction($this));
		$this->tpl->setVariable('SEARCH_ASSIGN_USR',$this->lng->txt('crs_search_members'));
		$this->tpl->setVariable('SEARCH_SEARCH_TERM',$this->lng->txt('search_search_term'));
		$this->tpl->setVariable('SEARCH_VALUE',$_SESSION['pays_search_str'] ? $_SESSION['pays_search_str'] : '');
		$this->tpl->setVariable('BTN2_VALUE',$this->lng->txt('cancel'));
		$this->tpl->setVariable('BTN1_VALUE',$this->lng->txt('search'));

		return true;
	}

	function searchObject()
	{
		global $rbacsystem,$tree;

		if(!$rbacsystem->checkAccess('read', $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt('msg_no_perm_read'),$this->ilias->error_obj->MESSAGE);
		}

		$_SESSION['pays_search_str'] = $_POST['search_str'] = $_POST['search_str'] ? $_POST['search_str'] : $_SESSION['pays_search_str'];

		if(!isset($_POST['search_str']))
		{
			ilUtil::sendInfo($this->lng->txt('crs_search_enter_search_string'));
			$this->searchUserObject();
			
			return false;
		}
		if(!count($result = $this->__search(ilUtil::stripSlashes($_POST['search_str']))))
		{
			ilUtil::sendInfo($this->lng->txt('crs_no_results_found'));
			$this->searchUserObject();

			return false;
		}

		$this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.pays_usr_selection.html','payment');
		$this->__showButton('searchUser',$this->lng->txt('crs_new_search'));
		
		$counter = 0;
		$f_result = array();
		foreach($result as $user)
		{
			if(!$tmp_obj = ilObjectFactory::getInstanceByObjId($user['id'],false))
			{
				continue;
			}
			$f_result[$counter][] = ilUtil::formCheckbox(0,'user[]',$user['id']);
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
		if(!$rbacsystem->checkAccess('write', $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt('msg_no_perm_write'),$this->ilias->error_obj->MESSAGE);
		}
		if(!$_POST['vendor_login'])
		{
			ilUtil::sendInfo($this->lng->txt('pays_no_username_given'));
			$this->vendorsObject();

			return true;
		}
		if(!($usr_id = ilObjUser::getUserIdByLogin(ilUtil::stripSlashes($_POST['vendor_login']))))
		{
			ilUtil::sendInfo($this->lng->txt('pays_no_valid_username_given'));
			$this->vendorsObject();

			return true;
		}
		
		$this->object->initPaymentVendorsObject();

		if($this->object->payment_vendors_obj->isAssigned($usr_id))
		{
			ilUtil::sendInfo($this->lng->txt('pays_user_already_assigned'));
			$this->vendorsObject();

			return true;
		}
		$this->object->payment_vendors_obj->add($usr_id);

		ilUtil::sendInfo($this->lng->txt('pays_added_vendor'));
		$this->vendorsObject();
		
		return true;
	}
		
	function addUserObject()
	{
		global $rbacsystem;

		// MINIMUM ACCESS LEVEL = 'administrate'
		if(!$rbacsystem->checkAccess('write', $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt('msg_no_perm_write'),$this->ilias->error_obj->MESSAGE);
		}

		$this->lng->loadLanguageModule('crs');
		if(!is_array($_POST['user']))
		{
			ilUtil::sendInfo($this->lng->txt('crs_no_users_selected'));
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

		ilUtil::sendInfo($message);
		$this->vendorsObject();

		return true;
	}		


	function searchUserSPObject()
	{
		if(!isset($_GET['sell_id']))
		{
			ilUtil::sendInfo($this->lng->txt('paya_no_booking_id_given'));
			$this->showObjectSelectorObject();

			return false;
		}

		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.paya_user_search.html','payment');
		$this->__showButton('showObjectSelector',$this->lng->txt('back'));

		$this->lng->loadLanguageModule('search');

		$this->ctrl->setParameter($this, 'sell_id', $_GET['sell_id']);
		$this->tpl->setVariable('F_ACTION',$this->ctrl->getFormAction($this));
		$this->tpl->setVariable('SEARCH_ASSIGN_USR',$this->lng->txt('search_user'));
		$this->tpl->setVariable('SEARCH_SEARCH_TERM',$this->lng->txt('search_search_term'));
		$this->tpl->setVariable('SEARCH_VALUE',$_SESSION['paya_search_str_user_sp'] ? $_SESSION['paya_search_str_user_sp'] : '');
		$this->tpl->setVariable('BTN2_VALUE',$this->lng->txt('cancel'));
		$this->tpl->setVariable('BTN1_VALUE',$this->lng->txt('search'));
		$this->tpl->setVariable('SEARCH','performSearchSP');
	//	$this->tpl->setVariable('CANCEL','statistic');
		$this->tpl->setVariable('CANCEL','bookings');

		return true;
	}

	function performSearchSPObject()
	{
		// SAVE it to allow sort in tables
		$_SESSION['paya_search_str_user_sp'] = $_POST['search_str'] = $_POST['search_str'] ? $_POST['search_str'] : $_SESSION['paya_search_str_user_sp'];

		if(!trim($_POST['search_str']))
		{
			ilUtil::sendInfo($this->lng->txt('search_no_search_term'));
			$this->statistics();

			return false;
		}
		if(!count($result = $this->__search(ilUtil::stripSlashes($_POST['search_str']))))
		{
			ilUtil::sendInfo($this->lng->txt('search_no_match'));
			$this->searchUserSPObject();

			return false;
		}

		if(!isset($_GET['sell_id']))
		{
			ilUtil::sendInfo($this->lng->txt('paya_no_booking_id_given'));
			$this->showObjectSelectorObject();

			return false;
		}

		$this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.paya_usr_selection.html','payment');
		$this->ctrl->setParameter($this, 'sell_id', $_GET['sell_id']);
		$this->__showButton('searchUserSP',$this->lng->txt('back'));
		
		$counter = 0;
		$f_result = array();
		foreach($result as $user)
		{
			if(!$tmp_obj = ilObjectFactory::getInstanceByObjId($user['id'],false))
			{
				continue;
			}
			$f_result[$counter][] = ilUtil::formRadiobutton(0,'user_id',$user['id']);
			$f_result[$counter][] = $tmp_obj->getLogin();
			$f_result[$counter][] = $tmp_obj->getFirstname();
			$f_result[$counter][] = $tmp_obj->getLastname();
			
			unset($tmp_obj);
			++$counter;
		}
		$this->__showSearchUserSPTable($f_result);
	}

	function addCustomerObject()
	{
		if ($_POST['sell_id'] != '') $_GET['sell_id'] = $_POST['sell_id'];
		if ($_GET['user_id'] != '') $_POST['user_id'] = $_GET['user_id'];

		if(!isset($_GET['sell_id']))
		{
			ilUtil::sendInfo($this->lng->txt('paya_no_booking_id_given'));
			$this->showObjectSelectorObject();

			return true;
		}

		if(!isset($_POST['user_id']))
		{
			ilUtil::sendInfo($this->lng->txt('paya_no_user_id_given'));
			$this->searchUserSPObject();

			return true;
		}

		$this->ctrl->setParameter($this, 'sell_id', $_GET['sell_id']);
		$this->__showButton('searchUserSP',$this->lng->txt('back'));

		$this->ctrl->setParameter($this, 'user_id', $_POST['user_id']);

		$pObjectId = ilPaymentObject::_lookupPobjectId($_GET['sell_id']);
		$obj =& new ilPaymentObject($this->user_obj, $pObjectId);

		// get obj
		$tmp_obj =& ilObjectFactory::getInstanceByRefId($_GET['sell_id']);
		// get customer_obj
		$tmp_user =& ilObjectFactory::getInstanceByObjId($_POST['user_id']);
		// get vendor_obj
		$tmp_vendor =& ilObjectFactory::getInstanceByObjId($obj->getVendorId());

		$this->tpl->addBlockfile('ADM_CONTENT','adm_content','tpl.paya_add_customer.html','payment');

		$this->tpl->setVariable('FORMACTION',$this->ctrl->getFormAction($this));

		$this->tpl->setVariable('TYPE_IMG',ilUtil::getImagePath('icon_usr.gif'));
		$this->tpl->setVariable('ALT_IMG',$this->lng->txt('obj_usr'));
		$this->tpl->setVariable('TITLE',$tmp_user->getFullname().' ['.$tmp_user->getLogin().']');

		// TXT
		$this->tpl->setVariable('TXT_TRANSACTION',$this->lng->txt('paya_transaction'));
		$this->tpl->setVariable('TRANSACTION',ilUtil::prepareFormOutput($_POST['transaction'], true));

		$this->tpl->setVariable('TXT_OB JECT',$this->lng->txt('title'));
		$this->tpl->setVariable('OBJECT',$tmp_obj->getTitle());

		$this->tpl->setVariable('TXT_VENDOR',$this->lng->txt('paya_vendor'));
		$this->tpl->setVariable('VENDOR',$tmp_vendor->getFullname().' ['.$tmp_vendor->getLogin().']');

		$this->tpl->setVariable('TXT_PAY_METHOD',$this->lng->txt('paya_pay_method'));
		$this->tpl->setVariable('TXT_PAY_METHOD_BILL',$this->lng->txt('pays_bill'));
		$this->tpl->setVariable('TXT_PAY_METHOD_BMF',$this->lng->txt('pays_bmf'));
		$this->tpl->setVariable('TXT_PAY_METHOD_PAYPAL',$this->lng->txt('pays_paypal'));
		$this->tpl->setVariable('PAY_METHOD_'.$_POST['pay_method'], ' selected');

		$this->tpl->setVariable('TXT_ORDER_DATE',$this->lng->txt('paya_order_date'));
		$this->tpl->setVariable('ORDER_DATE',ilDatePresentation::formatDate(new ilDateTime(time(),IL_CAL_UNIX)));

		$this->tpl->setVariable('TXT_DURATION',$this->lng->txt('duration'));
		include_once './payment/classes/class.ilPaymentPrices.php';
		$prices_obj =& new ilPaymentPrices($pObjectId);
		if (is_array($prices = $prices_obj->getPrices()))
		{
			foreach($prices as $price)
			{
				$this->tpl->setCurrentBlock('duration_loop');
				if ($_POST['duration'] == $price['price_id']) $this->tpl->setVariable('DURATION_LOOP_SELECTED', ' selected');
				$this->tpl->setVariable('DURATION_LOOP_ID', $price['price_id']);
				$this->tpl->setVariable('DURATION_LOOP_NAME', $price['duration'].' '.$this->lng->txt('paya_months').', '.ilPaymentPrices::_getPriceString($price['price_id']));
				$this->tpl->parseCurrentBlock();
			}
		}

		$this->tpl->setVariable('TXT_PAYED',$this->lng->txt('paya_payed'));
		if ($_POST['payed'] == 1) $this->tpl->setVariable('PAYED_1', ' selected');
		$this->tpl->setVariable('TXT_ACCESS',$this->lng->txt('paya_access'));
		if ($_POST['access'] == 1) $this->tpl->setVariable('ACCESS_1', ' selected');

		$this->tpl->setVariable('TXT_NO',$this->lng->txt('no'));
		$this->tpl->setVariable('TXT_YES',$this->lng->txt('yes'));
		$this->tpl->setVariable('TXT_SAVE',$this->lng->txt('save'));
		$this->tpl->setVariable('TXT_CANCEL',$this->lng->txt('cancel'));
		//$this->tpl->setVariable('STATISTICS','statistic');
		$this->tpl->setVariable('STATISTICS','bookings');

	}

	function saveCustomerObject()
	{
		global $ilias;

		if(!isset($_GET['sell_id']))
		{
			ilUtil::sendInfo($this->lng->txt('paya_error_no_object_id_given'));
			$this->showObjectSelectorObject();

			return true;
		}

		if(!isset($_GET['user_id']))
		{
			ilUtil::sendInfo($this->lng->txt('paya_error_no_user_id_given'));
			$this->searchUserSPObject();

			return true;
		}

		if ($_POST['pay_method'] == '' ||
			$_POST['duration'] == '')
		{
			ilUtil::sendInfo($this->lng->txt('paya_error_mandatory_fields'));
			$this->addCustomerObject();

			return true;
		}

		$pObjectId = ilPaymentObject::_lookupPobjectId($_GET['sell_id']);
		$obj =& new ilPaymentObject($this->user_obj, $pObjectId);

		$this->__initBookingObject();

		$inst_id_time = $ilias->getSetting('inst_id').'_'.$this->user_obj->getId().'_'.substr((string) time(),-3);
		$transaction = $inst_id_time.substr(md5(uniqid(rand(), true)), 0, 4);
		$this->booking_obj->setTransaction($transaction);
		$this->booking_obj->setTransactionExtern($_POST['transaction']);
		$this->booking_obj->setPobjectId($pObjectId);
		$this->booking_obj->setCustomerId($_GET['user_id']);
		$this->booking_obj->setVendorId($obj->getVendorId());
		$this->booking_obj->setPayMethod((int) $_POST['pay_method']);
		$this->booking_obj->setOrderDate(time());
		include_once './payment/classes/class.ilPaymentPrices.php';
		$price = ilPaymentPrices::_getPrice($_POST['duration']);
		$this->booking_obj->setDuration($price['duration']);
		$this->booking_obj->setPrice(ilPaymentPrices::_getPriceString($_POST['duration']));
		$this->booking_obj->setAccess((int) $_POST['access']);
		$this->booking_obj->setPayed((int) $_POST['payed']);
		$this->booking_obj->setVoucher('');

		if($this->booking_obj->add())
		{
			ilUtil::sendInfo($this->lng->txt('paya_customer_added_successfully'));
			$this->statisticObject();
		}
		else
		{
			ilUtil::sendInfo($this->lng->txt('paya_error_adding_customer'));
			$this->addCustomerObject();
		}

		return true;
	}

	// PRIVATE
	function __setSection($a_section)
	{
		$this->section = $a_section;
	}
	function __getSection()
	{
		return $this->section;
	}
	function __setMainSection($a_section)
	{
		$this->mainSection = $a_section;
	}
	function __getMainSection()
	{
		return $this->mainSection;
	}

	function __buildSettingsButtons()
	{
		if($this->__getMainSection() == $this->SETTINGS)
		{
			$this->tabs_gui->addSubTabTarget('pays_general',
											 $this->ctrl->getLinkTargetByClass('ilobjpaymentsettingsgui', 'generalSettings'),
											 '',
											 '',
											 '',
											 $this->__getSection() == $this->SECTION_GENERAL ? true : false);
			$this->tabs_gui->addSubTabTarget('pays_bmf',
											 $this->ctrl->getLinkTargetByClass('ilobjpaymentsettingsgui', 'bmfSettings'),
											 '',
											 '',
											 '',
											 $this->__getSection() == $this->SECTION_BMF ? true : false);
			$this->tabs_gui->addSubTabTarget('pays_paypal',
											 $this->ctrl->getLinkTargetByClass('ilobjpaymentsettingsgui', 'paypalSettings'),
											 '',
											 '',
											 '',
											 $this->__getSection() == $this->SECTION_PAYPAL ? true : false);
		}
	}

	public function showCustomerTable()
	{
		if(!isset($_GET['booking_id']))
		{
			ilUtil::sendInfo($this->lng->txt('paya_no_booking_id_given'));
			$this->showStatistics();

			return true;
		}

		$this->tpl->addBlockfile('ADM_CONTENT','adm_content','tpl.paya_adm_edit_statistic.html','payment');
		$this->ctrl->setParameter($this,'booking_id',(int) $_GET['booking_id']);
		$this->tpl->setCurrentBlock('CUSTOMER_DATA');

		$this->__initBookingObject();
		$bookings = $this->booking_obj->getBookings();

		$booking = $bookings[(int) $_GET['booking_id']];

		// get customer_obj
		$tmp_user = ilObjectFactory::getInstanceByObjId($booking['customer_id'], false);
		
		$this->tpl->setVariable('TYPE_IMG_2',ilUtil::getImagePath('icon_usr.gif'));
		$this->tpl->setVariable('ALT_IMG_2',$this->lng->txt('obj_usr'));
		if(is_object($tmp_user))
		{
			$this->tpl->setVariable('TITLE_2', $tmp_user->getFullname().' ['.$tmp_user->getLogin().']');
		}
		else
		{
			$this->tpl->setVariable('TITLE_2', $this->lng->txt('user_deleted'));
		}

		// TXT
		$pObj = new ilPaymentObject($this->user_obj, $booking['pobject_id']);
		
		$tmp_obj = ilObject::_lookupTitle(ilObject::_lookupObjId($pObj->getRefId()));				

		$this->tpl->setVariable('TXT_OBJECT',$this->lng->txt('title'));
		$this->tpl->setVariable('OBJECT', ($tmp_obj != '' ?  $tmp_obj : $this->lng->txt('object_deleted')));

		$this->tpl->setVariable('TXT_EMAIL',$this->lng->txt('email'));
		$this->tpl->setVariable('TXT_STREET',$this->lng->txt('street'));
		$this->tpl->setVariable('TXT_PO_BOX',$this->lng->txt('pay_bmf_po_box'));
		$this->tpl->setVariable('TXT_ZIPCODE',$this->lng->txt('zipcode'));
		$this->tpl->setVariable('TXT_CITY',$this->lng->txt('city'));
		$this->tpl->setVariable('TXT_COUNTRY',$this->lng->txt('country'));

		$this->tpl->setVariable('EMAIL',$tmp_user->getEmail());
		$this->tpl->setVariable('STREET',$booking['street']);
		$this->tpl->setVariable('PO_BOX',$booking['po_box']);
		$this->tpl->setVariable('ZIPCODE',$booking['zipcode']);
		$this->tpl->setVariable('CITY',$booking['city']);
		$this->tpl->setVariable('COUNTRY',$booking['country']);
		
		$this->tpl->parseCurrentBlock();
	}
	
	function __showStatisticTable($a_result_set)
	{
		$tbl =& $this->__initTableGUI();
		$tpl =& $tbl->getTemplateObject();

		// SET FORMAACTION
		$tpl->setCurrentBlock('tbl_form_header');

		$tpl->setVariable('FORMACTION',$this->ctrl->getFormAction($this));
		$tpl->parseCurrentBlock();


	//	$tbl->setTitle($this->lng->txt('paya_statistic'),'icon_pays.gif',$this->lng->txt('paya_statistic'));
		$tbl->setTitle($this->lng->txt('bookings'),'icon_pays.gif',$this->lng->txt('bookings'));
		$tbl->setHeaderNames(array($this->lng->txt('paya_transaction'),
								   $this->lng->txt('title'),
								   $this->lng->txt('paya_vendor'),
								   $this->lng->txt('paya_customer'),
								   $this->lng->txt('paya_order_date'),
								   $this->lng->txt('duration'),
								   $this->lng->txt('price_a'),
								   $this->lng->txt('paya_coupons_coupons'),
								   $this->lng->txt('paya_payed_access'),
								   ''));

		$tbl->setHeaderVars(array('transaction',
								  'title',
								  'vendor',
								  'customer',
								  'order_date',
								  'duration',
								  'price',
								  'discount',
								  'payed_access',
								  'options'),
							$this->ctrl->getParameterArray($this,'statistic',false));
					

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

		$tpl->setVariable('COLUMN_COUNTS',10);
		$tpl->setCurrentBlock('plain_buttons');
		$tpl->setVariable('PBTN_NAME','exportVendors');
		$tpl->setVariable('PBTN_VALUE',$this->lng->txt('excel_export'));
		$tpl->parseCurrentBlock();
		$tbl->render();

		$this->tpl->setVariable('STATISTIC_TABLE',$tbl->tpl->get());

		return true;
	}

	function __initBookingObject()
	{
		include_once './payment/classes/class.ilPaymentBookings.php';

		$this->booking_obj =& new ilPaymentBookings($this->user_obj->getId(),true);
	}

	function __showVendorsTable($a_result_set)
	{
		$actions = array(
			'editVendorObject'	=> $this->lng->txt('pays_edit_vendor'),
			'deleteVendorsObject'	=> $this->lng->txt('pays_delete_vendor')
		);

		$tbl =& $this->__initTableGUI();
		$tpl =& $tbl->getTemplateObject();

		// SET FORMAACTION
		$tpl->setCurrentBlock('tbl_form_header');

		$tpl->setVariable('FORMACTION',$this->ctrl->getFormAction($this));
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock('tbl_action_row');

		$tpl->setCurrentBlock('input_text');
		$tpl->setVariable('PB_TXT_NAME','vendor_login');
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock('plain_button');
		$tpl->setVariable('PBTN_NAME','addVendor');
		$tpl->setVariable('PBTN_VALUE',$this->lng->txt('pays_add_vendor'));
		$tpl->parseCurrentBlock();
		$tpl->setCurrentBlock('plain_buttons');
		$tpl->parseCurrentBlock();

		$tpl->setVariable('COLUMN_COUNTS',4);

		$tpl->setVariable('IMG_ARROW', ilUtil::getImagePath('arrow_downright.gif'));

		$tpl->setCurrentBlock('tbl_action_select');
		$tpl->setVariable('SELECT_ACTION',ilUtil::formSelect(1,'action',$actions,false,true));
		$tpl->setVariable('BTN_NAME','gateway');
		$tpl->setVariable('BTN_VALUE',$this->lng->txt('execute'));
		$tpl->parseCurrentBlock();
		$tpl->setCurrentBlock('tbl_action_row');
		$tpl->setVariable('TPLPATH',$this->tpl->tplPath);
		$tpl->parseCurrentBlock();

		$tbl->setTitle($this->lng->txt('vendors'),'icon_usr.gif',$this->lng->txt('vendors'));
		$tbl->setHeaderNames(array('',
								   $this->lng->txt('pays_vendor'),
								   $this->lng->txt('pays_cost_center'),
								   $this->lng->txt('pays_number_bookings')));
		$tbl->setHeaderVars(array('',
								  'login',
								  'cost_center',
								  'bookings'),
							array('ref_id' => $this->object->getRefId(),
								  'cmd' => 'vendors',
								  'update_members' => 1,
								  'baseClass' => 'ilAdministrationGUI',
								  'cmdClass' => 'ilobjpaymentsettingsgui',
								  'cmdNode' => $_GET['cmdNode']));
#		$tbl->setColumnWidth(array('4%','48%','25%','24%'));

		$tpl->setVariable('COLUMN_COUNTS',9);
		$tpl->setCurrentBlock('plain_buttons');
		$tpl->setVariable('PBTN_NAME','exportVendors');
		$tpl->setVariable('PBTN_VALUE',$this->lng->txt('excel_export'));
		$tpl->parseCurrentBlock();

		$this->__setTableGUIBasicData($tbl,$a_result_set);
		$tbl->render();

		$this->tpl->setVariable('VENDOR_TABLE',$tbl->tpl->get());

		return true;
	}


	function __showSearchUserTable($a_result_set,$a_cmd = 'search')
	{
		$tbl =& $this->__initTableGUI();
		$tpl =& $tbl->getTemplateObject();


		// SET FORMACTION
		$tpl->setCurrentBlock('tbl_form_header');
		$tpl->setVariable('FORMACTION',$this->ctrl->getFormAction($this));
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock('tbl_action_btn');
		$tpl->setVariable('BTN_NAME','addUser');
		$tpl->setVariable('BTN_VALUE',$this->lng->txt('add'));
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock('tbl_action_btn');
		$tpl->setVariable('BTN_NAME','vendors');
		$tpl->setVariable('BTN_VALUE',$this->lng->txt('cancel'));
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock('tbl_action_row');
		$tpl->setVariable('COLUMN_COUNTS',5);
		$tpl->setVariable('IMG_ARROW',ilUtil::getImagePath('arrow_downright.gif'));
		$tpl->parseCurrentBlock();

		$tbl->setTitle($this->lng->txt('pays_header_select_vendor'),'icon_usr.gif',$this->lng->txt('pays_header_select_vendor'));
		$tbl->setHeaderNames(array('',
								   $this->lng->txt('login'),
								   $this->lng->txt('firstname'),
								   $this->lng->txt('lastname')));
		$tbl->setHeaderVars(array('',
								  'login',
								  'firstname',
								  'lastname'),
							array('ref_id' => $this->object->getRefId(),
								  'cmd' => $a_cmd,
								  'cmdClass' => 'ilobjpaymentsettingsgui',
								  'cmdNode' => $_GET['cmdNode']));

		$tbl->setColumnWidth(array('3%','32%','32%','32%'));

		$this->__setTableGUIBasicData($tbl,$a_result_set);
		$tbl->render();
		
		$this->tpl->setVariable('SEARCH_RESULT_TABLE',$tbl->tpl->get());

		return true;
	}

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

	function &__initTableGUI()
	{
		include_once './Services/Table/classes/class.ilTableGUI.php';

		return new ilTableGUI(0,false);
	}

	function __setTableGUIBasicData(&$tbl,&$result_set,$from = '')
	{
		$offset = $_GET['offset'];
		$order = $_GET['sort_by'];
		$direction = $_GET['sort_order'];

		$tbl->setOrderColumn($order);
		$tbl->setOrderDirection($direction);
		$tbl->setOffset($offset);
		$tbl->setLimit($_GET['limit']);
		$tbl->setMaxCount(count($result_set));
		$tbl->setFooter('tblfooter',$this->lng->txt('previous'),$this->lng->txt('next'));
		$tbl->setData($result_set);
	}

	function __search($a_search_string)
	{
		include_once('./classes/class.ilSearch.php');

		$this->lng->loadLanguageModule('content');

		$search =& new ilSearch($_SESSION['AccountId']);
		$search->setPerformUpdate(false);
		$search->setSearchString(ilUtil::stripSlashes($a_search_string));
		$search->setCombination('and');
		$search->setSearchFor(array(0 => 'usr'));
		$search->setSearchType('new');

		if($search->validate($message))
		{
			$search->performSearch();
		}
		else
		{
			ilUtil::sendInfo($message,true);
			$this->ctrl->redirect($this,'searchUser');
		}
		return $search->getResultByType('usr');
	}		
	
	function __searchSP($a_search_string)
	{
		include_once('./classes/class.ilSearch.php');

		$this->lng->loadLanguageModule('content');

		$search =& new ilSearch($this->user_obj->getId());
		$search->setPerformUpdate(false);
		$search->setSearchString(ilUtil::stripSlashes($a_search_string));
		$search->setCombination('and');
		$search->setSearchFor(array(0 => 'usr'));
		$search->setSearchType('new');

		if($search->validate($message))
		{
			$search->performSearchSPObject();
		}
		else
		{
			ilUtil::sendInfo($message,true);
			$this->ctrl->redirect($this,'searchUserSP');
		}
		return $search->getResultByType('usr');
	}
	function __showSearchUserSPTable($a_result_set)
	{
		$tbl =& $this->initTableGUI();
		$tpl =& $tbl->getTemplateObject();


		// SET FORMACTION
		$tpl->setCurrentBlock('tbl_form_header');
		$this->ctrl->setParameter($this, 'sell_id', $_GET['sell_id']);
		$tpl->setVariable('FORMACTION',$this->ctrl->getFormAction($this));
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock('tbl_action_btn');
		$tpl->setVariable('BTN_NAME','addCustomer');
		$tpl->setVariable('BTN_VALUE',$this->lng->txt('add'));
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock('tbl_action_btn');
		$tpl->setVariable('BTN_NAME','statistic');
		$tpl->setVariable('BTN_VALUE',$this->lng->txt('cancel'));
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock('tbl_action_row');
		$tpl->setVariable('COLUMN_COUNTS',5);
		$tpl->setVariable('IMG_ARROW',ilUtil::getImagePath('arrow_downright.gif'));
		$tpl->parseCurrentBlock();

		$tbl->setTitle($this->lng->txt('users'),'icon_usr.gif',$this->lng->txt('crs_header_edit_members'));
		$tbl->setHeaderNames(array('',
								   $this->lng->txt('login'),
								   $this->lng->txt('firstname'),
								   $this->lng->txt('lastname')));
		$this->ctrl->setParameter($this, 'cmd', 'addCustomer');
		$header_params = $this->ctrl->getParameterArray($this,'');
		$tbl->setHeaderVars(array('',
								  'login',
								  'firstname',
								  'lastname'), $header_params);
								  /*
							array('cmd' => 'performSearch',
								  'cmdClass' => 'ilpaymentstatisticgui',
								  'cmdNode' => $_GET['cmdNode']));
								  */

		$tbl->setColumnWidth(array('3%','32%','32%','32%'));

		$this->setTableGUIBasicData($tbl,$a_result_set);
		$tbl->render();
		
		$this->tpl->setVariable('SEARCH_RESULT_TABLE',$tbl->tpl->get());

		return true;
	}

	function &initTableGUI()
	{
		include_once './Services/Table/classes/class.ilTableGUI.php';

		return new ilTableGUI(0,false);
	}
	function setTableGUIBasicData(&$tbl,&$result_set,$a_default_order_column = '')
	{
		
		$offset = $_GET['offset'];
		$order = $_GET['sort_by'];
		$direction = $_GET['sort_order'];

		$tbl->setOrderColumn($order,$a_default_order_column);
		$tbl->setOrderDirection($direction);
		$tbl->setOffset($offset);
		$tbl->setLimit($_GET['limit']);
		$tbl->setMaxCount(count($result_set));
		$tbl->setFooter('tblfooter',$this->lng->txt('previous'),$this->lng->txt('next'));
		$tbl->setData($result_set);
	}

	public function vatsObject()
	{
		global $ilAccess;

		if(!$ilAccess->checkAccess('read', '', $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt('msg_no_perm_read'), $this->ilias->error_obj->MESSAGE);
		}
		
		include_once 'Services/Table/classes/class.ilTable2GUI.php';
		$tbl = new ilTable2GUI($this, 'vats');
		$tbl->setFormAction($this->ctrl->getFormAction($this), 'createVat');
		$tbl->setTitle($this->lng->txt('payment_tax_rates'));
		$tbl->setRowTemplate('tpl.shop_vats_list_row.html', 'Services/Payment');				

	 	$tbl->setDefaultOrderField('title');	
		
		$result = array();		
		
		$tbl->addColumn('', 'check', '1%');
	 	$tbl->addColumn($this->lng->txt('vat_title'), 'vat_title', '33%');
	 	$tbl->addColumn($this->lng->txt('vat_rate'), 'vat_rate', '33%');
		$tbl->addColumn('', 'commands', '33%');		
		
		$oShopVatsList = new ilShopVatsList();
		$oShopVatsList->read();		
		
		$result = array();
		
		if($oShopVatsList->hasItems())
		{
			$tbl->enable('select_all');				
			$tbl->setSelectAllCheckbox('vat_id');
			
			$counter = 0;
			foreach($oShopVatsList as $oVAT)
			{
				$result[$counter]['check'] = ilUtil::formCheckbox(0, 'vat_id[]', $oVAT->getId());
				$result[$counter]['vat_title'] = $oVAT->getTitle();
				$result[$counter]['vat_rate'] = ilShopUtils::_formatVAT((float)$oVAT->getRate());								
				$this->ctrl->setParameter($this, 'vat_id',  $oVAT->getId());
				$result[$counter]['edit_text'] = $this->lng->txt('edit');
				$result[$counter]['edit_url'] = $this->ctrl->getLinkTarget($this, 'editVat');
				$result[$counter]['delete_text'] = $this->lng->txt('delete');
				$result[$counter]['delete_url'] = $this->ctrl->getLinkTarget($this, 'confirmDeleteVat');
				$this->ctrl->clearParameters($this);
				++$counter;
			}
			
			$tbl->addMultiCommand('confirmDeleteVat', $this->lng->txt('delete'));	
		}
		else
		{
			$tbl->disable('header');
			$tbl->disable('footer');

			$tbl->setNoEntriesText($this->lng->txt('paya_no_vats_assigned'));
		}
		
		$tbl->setData($result);
		
		$tbl->addCommandButton('createVat', $this->lng->txt('paya_insert_vats'));
		
		$this->tpl->setContent($tbl->getHTML());
		
		return true;
	}	

	public function confirmDeleteVatObject()
	{  
		if((int)$_GET['vat_id'] && !isset($_POST['vat_id']))
		{
			$_POST['vat_id'][] = $_GET['vat_id']; 	
		}		
		
		include_once 'Services/Utilities/classes/class.ilConfirmationGUI.php';
		$c_gui = new ilConfirmationGUI();
		$c_gui->setFormAction($this->ctrl->getFormAction($this, 'performDeleteVat'));
		$c_gui->setHeaderText($this->lng->txt('paya_sure_delete_vats'));
		$c_gui->setCancel($this->lng->txt('cancel'), 'vats');
		$c_gui->setConfirm($this->lng->txt('confirm'), 'performDeleteVat');
		
		$counter = 0;
		foreach((array)$_POST['vat_id'] as $vat_id)
		{
			try
			{
				$oVAT = new ilShopVats((int)$vat_id);
				$c_gui->addItem('vat_id[]', $oVAT->getId(), $oVAT->getTitle());
				++$counter;
			}
			catch(ilShopException $e)
			{
				ilUtil::sendInfo($e->getMessage());
				return $this->vatsObject();				
			}
		}	

		if($counter)
		{
			return $this->tpl->setContent($c_gui->getHTML());	
		}
		else
		{
			return $this->vatsObject();
		}
	}
	
	public function performDeleteVatObject()
	{
		if(!is_array($_POST['vat_id']))
		{
			return $this->vatsObject();
		}		
		
		foreach($_POST['vat_id'] as $vat_id)
		{
			try
			{
				$oVAT = new ilShopVats((int)$vat_id);
				$oVAT->delete();
				
			}
			catch(ilShopException $e)
			{
				ilUtil::sendInfo($e->getMessage());
				return $this->vatsObject();				
			}
		}
		
		ilUtil::sendInfo($this->lng->txt('payment_vat_deleted_successfully'));		
		return $this->vatsObject();
	}
	public function createVatObject()
	{
		$this->initVatForm('create');
		$this->tpl->setContent($this->form->getHtml());
	}
	
	public function editVatObject()
	{
		$this->initVatForm('edit');
		$this->fillVATDataIntoVATForm();
		$this->tpl->setContent($this->form->getHtml());
	}
	
	private function initVatForm($a_type = 'create')
	{
		include_once 'Services/Form/classes/class.ilPropertyFormGUI.php';
		
		$this->form = new ilPropertyFormGUI();		
		if($a_type == 'edit')
		{
			$this->ctrl->setParameter($this, 'vat_id', $_GET['vat_id']);
			$this->form->setFormAction($this->ctrl->getFormAction($this, 'updateVat'));
			$this->form->setTitle($this->lng->txt('payment_edit_vat'));	
		}
		else
		{
			$this->form->setFormAction($this->ctrl->getFormAction($this, 'saveVat'));
			$this->form->setTitle($this->lng->txt('payment_add_vat'));
		}
				
		$oTitle = new ilTextInputGUI($this->lng->txt('title'), 'vat_title');
		$oTitle->setMaxLength(255);
		$oTitle->setSize(40);
		$oTitle->setRequired(true);
		$oTitle->setInfo($this->lng->txt('payment_vat_title_info'));
		$this->form->addItem($oTitle);
		
		$oRate = new ilTextInputGUI($this->lng->txt('vat_rate'), 'vat_rate');
		$oRate->setMaxLength(5);
		$oRate->setSize(5);
		$oRate->setRequired(true);
		$oRate->setInfo($this->lng->txt('payment_vat_rate_info'));
		$this->form->addItem($oRate);
		
		if($a_type == 'edit')
		{			
			$this->form->addCommandButton('updateVat', $this->lng->txt('save'));
		}
		else
		{
			$this->form->addCommandButton('saveVat', $this->lng->txt('save'));	
		}
		
		$this->form->addCommandButton('vats', $this->lng->txt('cancel'));
	}
	
	

	private function fillVATDataIntoVATForm()
	{	
		$oVAT = new ilShopVats((int)$_GET['vat_id']);						
		$this->form->setValuesByArray(array(
			'vat_title' => $oVAT->getTitle(),
			'vat_rate' => $oVAT->getRate()
		));
	}		
	
	public function updateVatObject()
	{
		$this->initVatForm('edit');
		if(!$this->form->checkInput())
		{
			$this->form->setValuesByPost();
			return $this->tpl->setContent($this->form->getHtml());
		}
		
		if(!ilShopUtils::_checkVATRate($this->form->getInput('vat_rate')))
		{
			$this->form->getItemByPostVar('vat_rate')->setAlert($this->lng->txt('payment_vat_input_invalid'));
			$this->form->setValuesByPost();
			return $this->tpl->setContent($this->form->getHtml());
		}
		
		try
		{
			$oVAT = new ilShopVats((int)$_GET['vat_id']);
			$oVAT->setTitle($this->form->getInput('vat_title'));
			$oVAT->setRate((float)str_replace(',', '.', $this->form->getInput('vat_rate')));		
			$oVAT->update();
		}
		catch(ilShopException $e)
		{
			ilUtil::sendInfo($e->getMessage());
			$this->form->setValuesByPost();
			return $this->tpl->setContent($this->form->getHtml());			
		}
		
		ilUtil::sendInfo($this->lng->txt('saved_successfully'));
		return $this->vatsObject();
	}
	
	public function saveVatObject()
	{		
		$this->initVatForm('create');
		if(!$this->form->checkInput())
		{
			$this->form->setValuesByPost();
			return $this->tpl->setContent($this->form->getHtml());
		}
		
		if(!ilShopUtils::_checkVATRate($this->form->getInput('vat_rate')))
		{
			$this->form->getItemByPostVar('vat_rate')->setAlert($this->lng->txt('payment_vat_input_invalid'));
			$this->form->setValuesByPost();
			return $this->tpl->setContent($this->form->getHtml());
		}
		
		try
		{
			$oVAT = new ilShopVats();
			$oVAT->setTitle($this->form->getInput('vat_title'));
			$oVAT->setRate((float)str_replace(',', '.', $this->form->getInput('vat_rate')));		
			$oVAT->save();
		}
		catch(ilShopException $e)
		{
			ilUtil::sendInfo($e->getMessage());
			$this->form->setValuesByPost();
			return $this->tpl->setContent($this->form->getHtml());
			
		}
		
		ilUtil::sendInfo($this->lng->txt('saved'));
		return $this->vatsObject();
				
	}		
} // END class.ilObjPaymentSettingsGUI
?>
