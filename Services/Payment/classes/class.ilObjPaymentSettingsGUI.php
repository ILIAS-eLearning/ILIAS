<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */
 
/**
* Class ilObjPaymentSettingsGUI
*
* @author Nadia Ahmad <nahmad@databay.de> 
* @author Stefan Meyer <meyer@leifos.com> 
* @author Jens Conze <jc@databay.de> 
* @version $Id$
* 
* @ilCtrl_Calls ilObjPaymentSettingsGUI: ilPermissionGUI, ilShopTopicsGUI, ilShopPageGUI, ilRepositorySearchGUI, ilPaymentObjectSelector
* 
* @extends ilObjectGUI
* @package ilias-core
*
*/

require_once './Services/Object/classes/class.ilObjectGUI.php';
include_once './Services/Payment/classes/class.ilShopVatsList.php';
include_once './Services/Payment/classes/class.ilPaymentPrices.php';
include_once './Services/Payment/classes/class.ilPaymentObject.php';
include_once './Services/Payment/classes/class.ilFileDataShop.php';
include_once './Services/Form/classes/class.ilPropertyFormGUI.php';
include_once './Services/Utilities/classes/class.ilConfirmationGUI.php';
include_once './Services/Payment/classes/class.ilPaymentBookings.php';
include_once './Services/Payment/classes/class.ilPaymentSettings.php';
include_once './Services/Payment/classes/class.ilPaymentCurrency.php';
include_once './Services/Payment/classes/class.ilShopTableGUI.php';
include_once './Services/Payment/classes/class.ilInvoiceNumberPlaceholdersPropertyGUI.php';
include_once './Services/Payment/classes/class.ilUserDefinedInvoiceNumber.php';
include_once './Services/Search/classes/class.ilRepositorySearchGUI.php';
include_once './Services/Payment/classes/class.ilPaymentObjectSelector.php';

class ilObjPaymentSettingsGUI extends ilObjectGUI
{
	const CONDITIONS_EDITOR_PAGE_ID = 99999997;

	/** @var $user_obj \ilObjUser|null
	 * */
	public $user_obj = null;
	
	/** @var $pobject \ilPaymentObject|null */
	public $pobject = null;
	/**
	 * @var $genSetData ilPaymentSettings
	 */
	public $genSetData = null;

	public $active_sub_tab;
	
	public $error = false;
	/** @var $booking_obj ilPaymentBookings */
	public $booking_obj = null;
	public $form = null;
	
	public $ilErr = null;
	/**
	 * @var $vendors_obj ilPaymentVendors
	 */
	public $vendors_obj = null;

	/**
	 * @var $ctrl ilCtrl
	 */
	public $ctrl = null;

	/**
	 * @var $tabs_gui ilTabsGUI
	 */
	public $tabs_gui = null;
	
	/**
	* Constructor
	* @access public
	*/
	public function __construct($a_data, $a_id, $a_call_by_reference, $a_prepare_output = true)
	{
		global $ilErr, $ilUser;

		$this->user_obj = $ilUser;
		$this->ilErr = $ilErr;

		$this->pobject = new ilPaymentObject($this->user_obj);
		
		$this->genSetData = ilPaymentSettings::_getInstance();
		$this->vendors_obj = ilPaymentVendors::getInstance();

		$this->type = 'pays';
		$this->ilObjectGUI($a_data,$a_id,$a_call_by_reference,$a_prepare_output);

		$this->lng->loadLanguageModule('payment');
	}

	public function checkShopActivationObject()
	{
// check general settings
		$check = $this->genSetData->get('currency_unit');
		if($check == null)
		{
			ilUtil::sendInfo($this->lng->txt('please_enter_currency'));
			return $this->generalSettingsObject();
		}

		$check = $this->genSetData->get('address');
		if($check == null)
		{
			ilUtil::sendInfo($this->lng->txt('please_enter_address'));
			return $this->generalSettingsObject();
		}

		$check = $this->genSetData->get('bank_data');
		if($check == null)
		{
			ilUtil::sendInfo($this->lng->txt('please_enter_bank_data'));
			return $this->generalSettingsObject();
		}

		$check = $this->genSetData->get('pdf_path');
		if($check == null)
		{
			ilUtil::sendInfo($this->lng->txt('please_enter_pdf_path'));
			return $this->generalSettingsObject();
		}

// check paymethods
		$pm_array = ilPaymethods::_getActivePaymethods();

		if(count($pm_array) == 0)
		{
			ilUtil::sendInfo($this->lng->txt('please_activate_one_paymethod'));
			$this->payMethodsObject();
			return false;
		}

		foreach($pm_array as $paymethod)
		{
			switch($paymethod['pm_title'])
			{
				case 'bmf':
					$check = unserialize($this->genSetData->get('bmf'));
					if ($check['mandantNr'] == '' ||
						$check['bewirtschafterNr'] == '' ||
						$check['haushaltsstelle'] == '' ||
						$check['objektNr'] == '' ||
						$check['kennzeichenMahnverfahren'] == '' ||
						$check['waehrungskennzeichen'] == '' ||
						$check['ePaymentServer'] == '' ||
						$check['clientCertificate'] == '' ||
						$check['caCertificate'] == '' ||
						$check['timeOut'] == '')
					{
						ilUtil::sendInfo($this->lng->txt('please_enter_bmf_data'));
						$this->bmfSettingsObject();
						return false;

					}
					break;
				case 'paypal':
					$check = unserialize($this->genSetData->get('paypal'));
					if ($check['server_host'] == '' ||
						$check['server_path'] == '' ||
						$check['vendor'] == '' ||
						$check['auth_token'] == '')
					{
						ilUtil::sendInfo($this->lng->txt('please_enter_paypal_data'));
						$this->paypalSettingsObject();
						return false;
					}
					break;
				case 'epay':
				case 'erp':
					break;	
			}
		}
// check vats
		include_once './Services/Payment/classes/class.ilShopVats.php';
		$check= ilShopVats::_readAllVats();
		if(count($check) == 0)
		{
			ilUtil::sendInfo($this->lng->txt('please_enter_vats'));
			$this->vatsObject();
			return false;
		}
// check vendors
		$vendors = $this->vendors_obj->getVendors();

		if(count($vendors)  == 0)
		{
			ilUtil::sendInfo($this->lng->txt('please_create_vendor'));
			$this->vendorsObject();
			return false;
		}

// everything ok
		ilUtil::sendInfo($this->lng->txt('shop_activation_ok'));
		$this->generalSettingsObject();
		return true;

	}

	public function executeCommand()
	{		
		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();
		$this->prepareOutput();

/*
 * shop activation guide
 */
		
		/**
		 * @var  $ilToolbar ilToolbarGUI
		 */

		global $ilToolbar;
		$ilToolbar->addButton($this->lng->txt('check_shop_activation'),$this->ctrl->getLinkTarget($this,'checkShopActivation'));

		$this->getTabs($this->tabs_gui);

		switch($next_class)
		{
			case 'ilrepositorysearchgui':
				include_once('./Services/Search/classes/class.ilRepositorySearchGUI.php');
				$rep_search =& new ilRepositorySearchGUI();

					$rep_search->setCallback($this,
						'addVendorObject', array()
					);
				
				$this->ctrl->setReturn($this,'vendors');
				$ret =& $this->ctrl->forwardCommand($rep_search);
				break;
			
			case 'ilpermissiongui':
				include_once('Services/AccessControl/classes/class.ilPermissionGUI.php');
				$perm_gui = new ilPermissionGUI($this);
				$ret = $this->ctrl->forwardCommand($perm_gui);
				break;
				
			case 'ilshoppagegui':
				if($cmd == 'TermsConditions')
				{	
					$ret = $this->forwardToDocumentsPageObject(self::CONDITIONS_EDITOR_PAGE_ID);
					$this->prepareOutput();
				}
				else
				{
					$ret = $this->forwardToPageObject();
				}
				if($ret != '')
				{
					$this->tpl->setContent($ret);
				}				
				break;
				
			case 'ilshoptopicsgui':
				include_once './Services/Payment/classes/class.ilShopTopicsGUI.php';
				
				$topics_gui = new ilShopTopicsGUI($this);
				$ret = $this->ctrl->forwardCommand($topics_gui);
				$this->getSubTabs('topics', $cmd);
				break;
			
			default:
				if ($cmd == '' || $cmd == 'view')
				{
					$cmd = 'generalSettings';
				}
				switch ($cmd)
				{
					// only needed for subtabs
					case 'saveGeneralSettings' :
					case 'generalSettings' :
												$this->tabs_gui->setTabActive('settings');
												$this->getSubTabs('settings', 'generalSettings');
												break;
					case 'payMethods':
					case 'savePayMethods':
												$this->tabs_gui->setTabActive('pay_methods');
												$this->getSubTabs('payMethods', 'payMethods');
												break;
					case 'saveBmfSettings' :
					case 'bmfSettings' :		$this->tabs_gui->setTabActive('pay_methods');
												$this->getSubTabs('payMethods', 'bmfSettings');
												break;
					case 'savePaypalSettings' :
					case 'paypalSettings' :
												$this->tabs_gui->setTabActive('pay_methods');
												$this->getSubTabs('payMethods', 'paypalSettings');
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
					case 'vats' :				$this->tabs_gui->setTabActive('vats');
												break;
								

#TODO: CURRENCY not finished yet
/**/				case 'addCurrency':
					case 'currencies':
			#		case 'performDeleteCurrency':
					case 'updateCurrency':
			#					if($_POST['action'] == 'editCurrency' || $_POST['action'] == 'deleteCurrency')
			#					$cmd = $_POST['action'];
								$this->tabs_gui->setTabActive('currencies');						
							break;    
/**/
					case 'StatutoryRegulations':
					case 'saveStatutoryRegulations':
						$this->active_sub_tab = 'statutory_regulations';
								$this->tabs_gui->setTabActive('documents');
								$this->getSubTabs('documents', 'statutory_regulations');
								break;
					case 'TermsConditions':
					case 'documents':
						$this->active_sub_tab = 'terms_conditions';
								$this->tabs_gui->setTabActive('documents');
								$this->getSubTabs('documents', 'terms_conditions');

							 $cmd = 'TermsConditions';
							break;
					case 'BillingMail':
					case 'saveBillingMail':
							$this->active_sub_tab = 'billing_mail';
								$this->tabs_gui->setTabActive('documents');
								$this->getSubTabs('documents', 'billing_mail');

							#	$cmd = 'BillingMail';
							break;
					case 'InvoiceNumber':
					case 'saveInvoiceNumber':
							$this->active_sub_tab = 'invoice_number';
								$this->tabs_gui->setTabActive('documents');
								$this->getSubTabs('documents', 'invoice_number');

								#$cmd = 'InvoiceNumber';
							break;
					case 'checkShopActivation':
						break;
				}	
				$cmd .= 'Object';

				$this->$cmd();

				break;
		}
		return true;
	}
	
	public function forwardToPageObject()
	{
		/**
 		 * @var $ilTabs ilTabsGUI
		 */
		global $ilTabs;
		
		if(isset($_GET['pobject_id']) && $_GET['pobject_id'] > 0)
		{
			$this->ctrl->setParameter($this, 'pobject_id', (int)$_GET['pobject_id']);
			$this->__initPaymentObject((int)$_GET['pobject_id']);		
			$this->lng->loadLanguageModule('content');
			
			$ilTabs->clearTargets();
			$ilTabs->clearSubTabs();
			$ilTabs->setBackTarget($this->lng->txt('back'), $this->ctrl->getLinkTarget($this, 'editDetails'));
	
			include_once 'Services/Payment/classes/class.ilShopPage.php';
			include_once 'Services/Payment/classes/class.ilShopPageGUI.php';
			include_once('./Services/Style/classes/class.ilObjStyleSheet.php');
			
			$this->tpl->setVariable('LOCATION_CONTENT_STYLESHEET', ilObjStyleSheet::getContentStylePath(0));
	
			if(!ilShopPage::_exists('shop', (int)$this->pobject->getPobjectId()))
			{
				// doesn't exist -> create new one
				$new_page_object = new ilShopPage();
				$new_page_object->setParentId(0);
				$new_page_object->setId($this->pobject->getPobjectId());
				$new_page_object->createFromXML();
			}
					
			$this->ctrl->setReturnByClass('ilshoppagegui', 'edit');
	
			if(!(int)$_GET['pobject_id'])
			{
				ilUtil::sendInfo($this->lng->txt('paya_no_object_selected'));
				return $this->objectsObject();
			}
			$this->ctrl->setParameter($this, 'pobject_id', (int)$_GET['pobject_id']);
			$this->__initPaymentObject((int)$_GET['pobject_id']);
	
			$page_gui = new ilShopPageGUI((int)$this->pobject->getPobjectId());
			$this->ctrl->setParameter($page_gui, 'pobject_id', (int)$_GET['pobject_id']);
		}
		else 
		{
			/**
			 * @var $lng $lng
			 * @var $ilCtrl ilCtrl
			 */
			global $lng, $ilCtrl;

			$ilTabs->clearTargets();
			$ilTabs->setBackTarget($lng->txt('back'), $this->ctrl->getLinkTarget($this), '_top');

			// page object
			include_once 'Services/Payment/classes/class.ilShopPage.php';
			include_once 'Services/Payment/classes/class.ilShopPageGUI.php';

			$lng->loadLanguageModule('content');

			include_once('./Services/Style/classes/class.ilObjStyleSheet.php');
			$this->tpl->setVariable('LOCATION_CONTENT_STYLESHEET', ilObjStyleSheet::getContentStylePath(0));

			if(!ilShopPage::_exists('shop', self::CONDITIONS_EDITOR_PAGE_ID))
			{
				// doesn't exist -> create new one
				$new_page_object = new ilShopPage();
				$new_page_object->setParentId(0);
				$new_page_object->setId(self::CONDITIONS_EDITOR_PAGE_ID);
				$new_page_object->createFromXML();
			}

			$this->ctrl->setReturnByClass('ilshoppagegui', 'edit');

			$page_gui = new ilShopPageGUI(self::CONDITIONS_EDITOR_PAGE_ID);
		}
		
		return $this->ctrl->forwardCommand($page_gui);
	}
	
	public function saveBmfSettingsObject()
	{
		/**
 		 * @var $rbacsystem ilRbacSystem
		 */
		global $rbacsystem;

		// MINIMUM ACCESS LEVEL = 'read'
		if(!$rbacsystem->checkAccess('read', $this->object->getRefId()))
		{
			$this->ilErr->raiseError($this->lng->txt('msg_no_perm_read'),$this->ilErr->MESSAGE);
		}
		
		include_once './Services/Payment/classes/class.ilBMFSettings.php';
		
		$this->error = '';
		/**
		 * @var $bmfSetObj ilBMFSettings
		 */
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
			ilUtil::sendFailure($this->error);
			$this->bmfSettingsObject();
			return true;
		}
		
		$bmfSetObj->save();
				
		$this->bmfSettingsObject();

		ilUtil::sendSuccess($this->lng->txt('pays_updated_bmf_settings'));

		return true;
	}
	
	public function bmfSettingsObject()
	{
		/** 
		 * @var $rbacsystem ilRbacSystem
		 * */
		global $rbacsystem;

		// MINIMUM ACCESS LEVEL = 'read'
		if(!$rbacsystem->checkAccess('read', $this->object->getRefId()))
		{
			$this->ilErr->raiseError($this->lng->txt('msg_no_perm_read'),$this->ilErr->MESSAGE);
		}
		include_once './Services/Payment/classes/class.ilBMFSettings.php';


		$this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.main_view.html','Services/Payment');

		/**
 		* @var $bmfSetObj ilBMFSettings	
		*/
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
				
		$this->tpl->setVariable('FORM',$form->getHTML());
		return true;
	}
	

	public function updateDetailsObject()
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
					$this->editDetailsObject();

					return false;
				default:
					;
			}
			// check minimum one price
			$prices_obj = new ilPaymentPrices((int) $_GET['pobject_id']);
			$standard_prices = array();
			$standard_prices = $prices_obj->getPrices();

			if(!count($standard_prices))
			{
				ilUtil::sendInfo($this->lng->txt('paya_edit_prices_first'));
				$this->editDetailsObject();
						
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
		$this->editDetailsObject();

		return true;
	}
	
	public function editPricesObject($a_show_delete = false)
	{
		/** 
		 * @var $ilToolbar ilToolbarGUI
		 * */ 
		global $ilToolbar;
		
		if($a_show_delete == false) unset($_SESSION['price_ids']);

		$_SESSION['price_ids'] = $_SESSION['price_ids'] ? $_SESSION['price_ids'] : array();

		if(!$_GET['pobject_id'])
		{
			ilUtil::sendInfo($this->lng->txt('paya_no_object_selected'));

			$this->objectsObject();
			return true;
		}
		$this->ctrl->setParameter($this,'pobject_id',(int) $_GET['pobject_id']);

		$ilToolbar->addButton($this->lng->txt('back'), $this->ctrl->getLinkTarget($this, 'objects'));
		$ilToolbar->addButton($this->lng->txt('paya_edit_details'), $this->ctrl->getLinkTarget($this, 'editDetails'));
		$ilToolbar->addButton($this->lng->txt('paya_edit_prices'), $this->ctrl->getLinkTarget($this, 'editPrices'));
		$ilToolbar->addButton($this->lng->txt('pay_edit_abstract'), $this->ctrl->getLinkTargetByClass(array('ilshoppagegui'), 'edit'));

		$this->__initPaymentObject((int) $_GET['pobject_id']);

		$this->tpl->addBlockfile('ADM_CONTENT','adm_content','tpl.main_view.html','Services/Payment');

		$price_obj = new ilPaymentPrices((int) $_GET['pobject_id']);
		$standard_prices = array();
		$extension_prices = array();
		$standard_prices = $price_obj->getPrices();
		$extension_prices = $price_obj->getExtensionPrices();

		$prices = array_merge($standard_prices, $extension_prices );


		// No prices created
		if(!count($prices))
		{
			ilUtil::sendInfo($this->lng->txt('paya_no_price_available'));
			$ilToolbar->addButton($this->lng->txt('paya_add_price'), $this->ctrl->getLinkTarget($this, 'addPrice'));

			return true;
		}
		// Show confirm delete
		if($a_show_delete)
		{	
			$oConfirmationGUI = new ilConfirmationGUI();
			
			// set confirm/cancel commands
			$oConfirmationGUI->setFormAction($this->ctrl->getFormAction($this,"performDeletePrice"));
			$oConfirmationGUI->setHeaderText($this->lng->txt("paya_sure_delete_selected_prices"));
			$oConfirmationGUI->setCancel($this->lng->txt("cancel"), "editPrices");
			$oConfirmationGUI->setConfirm($this->lng->txt("confirm"), "performDeletePrice");			
			
			foreach($prices as $price)
			{
				$delete_row = '';
				$tmp_price = '';
				
				if(in_array($price['price_id'],$_SESSION['price_ids']))
				{
					switch($price['price_type'])
					{
						case ilPaymentPrices::TYPE_DURATION_DATE:
							include_once './Services/Calendar/classes/class.ilDatePresentation.php';
							$tmp_price = ilDatePresentation::formatDate(new ilDate($price['duration_from'], IL_CAL_DATE))
								.' - '.ilDatePresentation::formatDate(new ilDate($price['duration_until'], IL_CAL_DATE));
							break;
						
						case ilPaymentPrices::TYPE_DURATION_MONTH:
							$tmp_price = $price['duration'].' '.$this->lng->txt('paya_months');
							break;
						
						case ilPaymentPrices::TYPE_UNLIMITED_DURATION:
							$tmp_price = $this->lng->txt('unlimited_duration');
							break;
					}
					
					$delete_row .= ''.$tmp_price.'  ->  '.
									ilFormat::_getLocalMoneyFormat($price['price']).' '.
									$this->genSetData->get('currency_unit');
									
					$oConfirmationGUI->addItem('',$delete_row, $delete_row);
				}
			}
				
			$this->tpl->setVariable('CONFIRMATION', $oConfirmationGUI->getHTML());		
			
			return true;			
		}			

		// Fill table cells
		/** 
		 * @var object $tpl ilTemplate 
		 */
		$tpl = new ilTemplate('tpl.table.html',true,true);

		// set table header
		$tpl->setCurrentBlock('tbl_form_header');
		
		$tpl->setVariable('FORMACTION',$this->ctrl->getFormAction($this));
		$tpl->parseCurrentBlock();

		$counter = 0;
		$data = array();
		foreach($prices as $price)
		{
			$data[$counter]['price_id'] = ilUtil::formCheckBox(in_array($price['price_id'],$_SESSION['price_ids']) ? 1 : 0,
				'price_ids[]', $price['price_id']);	

			switch($price['price_type'])
			{
				case ilPaymentPrices::TYPE_DURATION_MONTH:
					$data[$counter]['duration'] = $price['duration'].' '.$this->lng->txt('paya_months');
					break;
				case ilPaymentPrices::TYPE_DURATION_DATE:

					$data[$counter]['duration'] = ilDatePresentation::formatDate(new ilDate($price['duration_from'], IL_CAL_DATE))
						.' - '.ilDatePresentation::formatDate(new ilDate($price['duration_until'],IL_CAL_DATE));
					break;
				case ilPaymentPrices::TYPE_UNLIMITED_DURATION:
					$data[$counter]['duration'] = $this->lng->txt('unlimited_duration');	
					break;
			}
			$data[$counter]['price'] =  ilFormat::_getLocalMoneyFormat($price['price']);
			$data[$counter]['currency_unit'] = $this->genSetData->get('currency_unit');
			$data[$counter]['extension'] =  ilUtil::formCheckBox($price['extension'] ? 1 : 0,
				'extension_ids[]', (int)$price['price_id'], true);

			$this->ctrl->setParameter($this,"price_id",$price['price_id']);
			$data[$counter]['edit'] = 
			"<div class=\"il_ContainerItemCommands\"><a class=\"il_ContainerItemCommand\" href=\"".$this->ctrl->getLinkTarget($this,"editPrice")."\">".$this->lng->txt("edit")."</a></div>";

			++$counter;
		}
		$this->__editPricesTable($data);	
	
		return true;
	}	
		
	private function __editPricesTable($a_result_set)
	{
		$this->ctrl->setParameter($this, 'cmd', 'editprices');
		$tbl = new ilShopTableGUI($this);

		$tmp_obj = ilObjectFactory::getInstanceByRefId($this->pobject->getRefId(), false);
		if($tmp_obj)
		{
			$tbl->setTitle($tmp_obj->getTitle());
		}
		else
		{
			$tbl->setTitle($this->lng->txt('object_not_found'));
		}

		$tbl->setId('tbl_bookings');
		$tbl->setRowTemplate("tpl.shop_prices_row.html", "Services/Payment");

		$tbl->addColumn(' ', 'price_id', '5%');
		$tbl->addColumn($this->lng->txt('duration'), 'duration', '40%');
		$tbl->addColumn($this->lng->txt('price_a'), 'price', '1%');
		$tbl->addColumn($this->lng->txt('currency'), 'currency_unit', '10%');
		$tbl->addColumn($this->lng->txt('extension_price'), 'extension', '10%');
		$tbl->addColumn('', 'edit', '30%' );

		$tbl->setSelectAllCheckbox('price_id');
		$tbl->addCommandButton('addPrice',$this->lng->txt('paya_add_price'));

		$tbl->addMultiCommand("deletePrice", $this->lng->txt("paya_delete_price"));
		$tbl->fillFooter();
	
		$tbl->setData($a_result_set);

		$this->tpl->setVariable('TABLE', $tbl->getHTML());

		return true;
	}	
			
	public function addPriceObject()
	{
		/**
		 * @var $ilToolbar ilToolbarGUI
		 */
		global $ilToolbar;
		
		if(!$_GET['pobject_id'])
		{
			ilUtil::sendInfo($this->lng->txt('paya_no_object_selected'));

			$this->objectsObject();
			return true;
		}
		/**
		 * @var $genSet ilPaymentSettings
		 * */
		$genSet = ilPaymentSettings::_getInstance();

		$this->ctrl->setParameter($this,'pobject_id',(int) $_GET['pobject_id']);

		$this->__initPaymentObject((int) $_GET['pobject_id']);

		$ilToolbar->addButton($this->lng->txt('paya_edit_details'), $this->ctrl->getLinkTarget($this, 'editDetails'));
		$ilToolbar->addButton($this->lng->txt('paya_edit_prices'), $this->ctrl->getLinkTarget($this, 'editPrices'));
		$ilToolbar->addButton($this->lng->txt('pay_edit_abstract'), $this->ctrl->getLinkTargetByClass(array('ilshoppagegui'), 'edit'));
	
		$tmp_obj = ilObjectFactory::getInstanceByRefId($this->pobject->getRefId(), false);
		if(is_object($tmp_obj))
		{
			$tmp_object['title'] = $tmp_obj->getTitle();
		}
		else
		{
			$tmp_object['title'] = $this->lng->txt('object_not_found');
		}
		$this->tpl->addBlockfile('ADM_CONTENT','adm_content','tpl.main_view.html','Services/Payment');
		
		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this));
		$form->setTitle($this->lng->txt('paya_add_price_title'));
		
		// object_title
		$oTitle = new ilNonEditableValueGUI($this->lng->txt('title'));

		$oTitle->setValue($tmp_object['title']);
		$form->addItem($oTitle);
			
		//price_type

		$radio_group = new ilRadioGroupInputGUI('','price_type');
		$radio_group->setTitle($this->lng->txt('duration'));
		$radio_group->setRequired(true);
		$radio_group->setValue($_POST['price_type']);
		$radio_group->setPostVar('price_type');

		$radio_option_1 = new ilRadioOption($this->lng->txt('duration_month'), 'duration_month');

			// duration month
			$oDuration = new ilNumberInputGUI();
			$oDuration->setTitle($this->lng->txt('paya_months'));
			$oDuration->setSize('30%');
			$oDuration->setValue($_POST['duration_month']);
			$oDuration->setPostVar('duration_month');
			$radio_option_1->addSubItem($oDuration);

		$radio_group->addOption($radio_option_1);

		$radio_option_3 = new ilRadioOption($this->lng->txt('duration_date'), 'duration_date');

			// duration_date from	
			$o_date_from = new ilDateTimeInputGUI();
			$o_date_from->setTitle($this->lng->txt('cal_from'));
			$o_date_from->setPostVar('duration_date_from');			
			$radio_option_3->addSubItem($o_date_from);

			// duration_date until
			$o_date_until = new ilDateTimeInputGUI();
			$o_date_until->setTitle($this->lng->txt('cal_until'));
			$o_date_until->setPostVar('duration_date_until');			
			$radio_option_3->addSubItem($o_date_until);

		$radio_group->addOption($radio_option_3);

		$radio_option_2 = new ilRadioOption($this->lng->txt('unlimited_duration'), 'unlimited_duration');		
		$radio_group->addOption($radio_option_2);

		$form->addItem($radio_group);	
		
		// description
		$oDescription = new ilTextAreaInputGUI($this->lng->txt('description'), 'description');
		$oDescription->setRows(4);
		$oDescription->setCols(35);
		$oDescription->setValue($_POST['description']);
		$form->addItem($oDescription);
		
		// price
		$oPrice = new ilNumberInputGUI();
		$oPrice->setTitle($this->lng->txt('price_a'));
		$oPrice->setValue($_POST['price']);
		$oPrice->setPostVar('price');
		$oPrice->setRequired(true);
		$oPrice->allowDecimals(true);
		$form->addItem($oPrice);
	
		 // currency
		// TODO show curency selector
		 
#TODO: CURRENCY not finished yet
/*		$objCurrency = new ilPaymentCurrency();
		$currencies = $objCurrency->_getAvailableCurrencies();
		
		foreach($currencies as $currency)
		{
			$currency_options[$currency['currency_id']] = $currency['unit'];
		}

		
		$oCurrency = new ilSelectInputGUI($this->lng->txt('currency'), 'currency_id');
		$oCurrency->setOptions($currency_options);
		
		$oCurrency->setValue($_SESSION['pay_objects']['currency_value']);
		$oCurrency->setPostVar('currency_id');
 /**/
		$currency_options = $genSet->get('currency_unit');
		$oCurrency = new ilNonEditableValueGUI($this->lng->txt('currency'));
		$oCurrency->setValue($currency_options);
		$form->addItem($oCurrency);
/**/		

		//extension
		$oExtension = new ilCheckboxInputGUI($this->lng->txt('extension_price'), 'extension');
		$oExtension->setChecked((int)$_POST['extension']);

		$form->addItem($oExtension);

		$form->addCommandButton('performAddPrice',$this->lng->txt('paya_add_price'));
		$form->addCommandButton('editPrices', $this->lng->txt('cancel'));		
		$this->tpl->setVariable('FORM',$form->getHTML());

		return true;
	}

	public function performAddPriceObject()
	{
		$po = new ilPaymentPrices((int)$_GET['pobject_id']);

		switch($_POST['price_type'])
		{
			case 'unlimited_duration':
				$po->setType(ilPaymentPrices::TYPE_UNLIMITED_DURATION);
				$po->setDuration(0);
				$po->setDurationFrom(NULL);
				$po->setDurationUntil(NULL);
				$po->setUnlimitedDuration(1);

				break;

			case 'duration_date':

				$po->setType(ilPaymentPrices::TYPE_DURATION_DATE);
				$po->setDuration(NULL);
				$po->setDurationFrom(ilUtil::stripSlashes(
						$_POST['duration_date_from']['date']['y'].'-'.
						$_POST['duration_date_from']['date']['m'].'-'.
						$_POST['duration_date_from']['date']['d']));
				$po->setDurationUntil(ilUtil::stripSlashes(
						$_POST['duration_date_until']['date']['y'].'-'.
						$_POST['duration_date_until']['date']['m'].'-'.
						$_POST['duration_date_until']['date']['d']));
				break;

			default:
			case 'duration_month':
				$po->setType(ilPaymentPrices::TYPE_DURATION_MONTH);
				$po->setDuration($_POST['duration_month']);
				$po->setDurationFrom(NULL);
				$po->setDurationUntil(NULL);
				break;
		}

		$po->setDescription($_POST['description'] ? ilUtil::stripSlashes($_POST['description']) : NULL);
		$po->setPrice(ilUtil::stripSlashes($_POST['price']));
//		$po->setCurrency($currency[1]['currency_id']);

			if($_POST['extension_price'])
			{
				$po->setExtension(1);
			}
			else
			{
				$po->setExtension(0);
			}

		try
		{
			$po->validate();
			$po->add();
			ilUtil::sendInfo($this->lng->txt('paya_added_new_price'));
			return $this->editPricesObject();
		}
		catch(ilShopException $e)
		{
			ilUtil::sendInfo($e->getMessage());
			return $this->addPriceObject();
		}
	}		

	public function performDeletePriceObject()
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
			
			$this->editPriceObject();
			return true;
		}
		
		$prices = new ilPaymentPrices((int) $_GET['pobject_id']);

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

	public function deletePriceObject()
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

	public function updatePriceObject()
	{
		if(!$_GET['pobject_id'] && !$_POST['pobject_id'])
		{
			ilUtil::sendInfo($this->lng->txt('paya_no_object_selected'));

			$this->objectsObject();
			return true;
		}
		if(isset($_GET['pobject_id']))
		{	
			$pobject_id = (int)$_GET['pobject_id'];
		}
		else
		{
			$pobject_id = (int)$_POST['pobject_id'];
		}

		if(!(int)$_GET['price_id'] && !$_POST['price_id'])
		{
			ilUtil::sendInfo($this->lng->txt('payment_no_price_selected'));
			return $this->editPricesObject();
		}
		if(isset($_GET['price_id']))
		{	
			$price_id = (int)$_GET['price_id'];
		}
		else
		{
			$price_id = (int)$_POST['price_id'];
		}

		$po = new ilPaymentPrices((int)$pobject_id);
		switch($_POST['price_type'])
		{
			case ilPaymentPrices::TYPE_UNLIMITED_DURATION:
				$po->setType(ilPaymentPrices::TYPE_UNLIMITED_DURATION);
				$po->setDuration(NULL);
				$po->setDurationFrom(NULL);
				$po->setDurationUntil(NULL);
				$po->setUnlimitedDuration(1);

				break;

			case ilPaymentPrices::TYPE_DURATION_DATE:
				$po->setType(ilPaymentPrices::TYPE_DURATION_DATE);
				$po->setDuration(NULL);
				$po->setDurationFrom(ilUtil::stripSlashes(
						$_POST['duration_date_from']['date']['y'].'-'.
						$_POST['duration_date_from']['date']['m'].'-'.
						$_POST['duration_date_from']['date']['d']));
				$po->setDurationUntil(ilUtil::stripSlashes(
						$_POST['duration_date_until']['date']['y'].'-'.
						$_POST['duration_date_until']['date']['m'].'-'.
						$_POST['duration_date_until']['date']['d']));
				break;

			default:
			case ilPaymentPrices::TYPE_DURATION_MONTH:
				$po->setType(ilPaymentPrices::TYPE_DURATION_MONTH);
				$po->setDuration($_POST['duration_month']);
				$po->setDurationFrom(NULL);
				$po->setDurationUntil(NULL);
				break;
		}

		$po->setDescription($_POST['description'] ? ilUtil::stripSlashes($_POST['description']) : NULL);
		$po->setPrice(ilUtil::stripSlashes($_POST['price']));
		$po->setCurrency(ilUtil::stripSlashes($_POST['currency']));
		if($_POST['extension_price'])
		{
			$po->setExtension(1);
		}
		else
		{
			$po->setExtension(0);
		}
		try
		{
			$po->validate();
			$po->update($price_id);
			ilUtil::sendInfo($this->lng->txt('paya_updated_price'));
			return $this->editPricesObject();
		}
		catch(ilShopException $e)
		{
			ilUtil::sendInfo($e->getMessage());
			$this->editPriceObject();
			return true;
		}		
	}
	
	public function editDetailsObject($a_show_confirm = false)
	{
		global $ilToolbar;
		
		if(!(int)$_GET['pobject_id'])
		{	
			ilUtil::sendInfo($this->lng->txt('paya_no_object_selected'));
			return $this->objectsObject();
		}
			
		$this->__initPaymentObject((int)$_GET['pobject_id']);

		$this->ctrl->setParameter($this,'pobject_id', (int)$_GET['pobject_id']);

		$ilToolbar->addButton($this->lng->txt('paya_edit_details'), $this->ctrl->getLinkTarget($this, 'editDetails'));
		$ilToolbar->addButton($this->lng->txt('paya_edit_prices'), $this->ctrl->getLinkTarget($this, 'editPrices'));
		$ilToolbar->addButton($this->lng->txt('pay_edit_abstract'), $this->ctrl->getLinkTargetByClass(array('ilshoppagegui'), 'edit'));
		
		$this->tpl->addBlockfile('ADM_CONTENT','adm_content','tpl.main_view.html','Services/Payment');
		
		$tmp_obj = ilObjectFactory::getInstanceByRefId($this->pobject->getRefId(),false);
		if($tmp_obj)
		{
			$tmp_object['title'] = $tmp_obj->getTitle();
			$tmp_object['type'] = $tmp_obj->getType();
		}
		else
		{
			$tmp_object['title'] = $this->lng->txt('object_not_found');
			$tmp_object['type'] = '';
			
		}
		if($a_show_confirm)
		{
			include_once './Services/Utilities/classes/class.ilConfirmationGUI.php';
			$oConfirmationGUI = new ilConfirmationGUI();
			
			// set confirm/cancel commands
			$oConfirmationGUI->setFormAction($this->ctrl->getFormAction($this,"performObjectDelete"));
			$oConfirmationGUI->setHeaderText($this->lng->txt("paya_sure_delete_object"));
			$oConfirmationGUI->setCancel($this->lng->txt("cancel"), "objects");
			$oConfirmationGUI->setConfirm($this->lng->txt("confirm"), "performObjectDelete");			

			$oConfirmationGUI->addItem('', $tmp_object['title'], $tmp_object['title']);
			$this->tpl->setVariable('CONFIRMATION',$oConfirmationGUI->getHTML());
		
			return true;				
		}
		
		$oForm = new ilPropertyFormGUI();
		$oForm->setFormAction($this->ctrl->getFormAction($this, 'updateDetails'));
		$oForm->setTitle($tmp_object['title']);
		
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
		$oPayMethodsGUI->setOptions(ilPayMethods::getPayMethodsOptions('not_specified'));

		$oPayMethodsGUI->setValue($this->pobject->getPayMethod());
		$oForm->addItem($oPayMethodsGUI);		
		
		// topics
		include_once './Services/Payment/classes/class.ilShopTopics.php';
		ilShopTopics::_getInstance()->read();
		if(is_array($topics = ilShopTopics::_getInstance()->getTopics()) && count($topics))
		{
			$oTopicsGUI = new ilSelectInputGUI($this->lng->txt('topic'), 'topic_id');

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

		$this->tpl->setVariable('FORM', $oForm->getHTML());
		return true;
		
	}
	
	public function deleteObjectObject()
	{
		//include_once './Services/Payment/classes/class.ilPaymentBookings.php';

		if(!$_GET['pobject_id'])
		{
			ilUtil::sendInfo($this->lng->txt('paya_no_object_selected'));

			$this->editDetailsObject();
			return true;
		}
		if(ilPaymentBookings::_getCountBookingsByObject((int) $_GET['pobject_id']))
		{
			ilUtil::sendInfo($this->lng->txt('paya_bookings_available'));
			$this->editDetailsObject();

			return false;
		}
		else
		{
			ilUtil::sendQuestion($this->lng->txt('paya_sure_delete_object'));
			$this->editDetailsObject(true);

			return true;
		}
	}
	
	public function performObjectDeleteObject()
	{

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
		$price_obj = new ilPaymentPrices((int) $_GET['pobject_id']);
		$price_obj->deleteAllPrices();
		unset($price_obj);

		ilUtil::sendInfo($this->lng->txt('paya_deleted_object'));

		$this->objectsObject();

		return true;
	}
	
	private function __getHTMLPath($a_ref_id)
	{
		global $tree;

		$path = $tree->getPathFull($a_ref_id);

		unset($path[0]);
		$html = '';
		if(is_array($path))
		{
			foreach($path as $data)
			{
				$html .= $data['title'].' > ';
			}
		}
		return substr($html,0,-2);
	}
		
	private function __getVendors()
	{
		$options = array();		
		$vendors = array();
		
		$all_vendors = $this->vendors_obj->getVendors();
		if (is_array($all_vendors))
		{
			foreach ($all_vendors as $vendor)
			{				
				$vendors[] = $vendor['vendor_id'];
			}
		}		
		
		foreach($vendors as $vendor)
		{
			if(!$tmp_obj = ilObjectFactory::getInstanceByObjId($vendor, false))
			{
				continue;
			}
				
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
	
	public function resetObjectFilterObject()
	{
		unset($_SESSION['pay_statistics']);
		unset($_POST['title_type']);
		unset($_POST['title_value']);
		unset($_POST['vendor']);
		unset($_POST['pay_method']);

		ilUtil::sendInfo($this->lng->txt('paya_filter_reseted'));

		return $this->objectsObject();
	}
	
	public function objectsObject()
	{
		global $rbacsystem;

		// MINIMUM ACCESS LEVEL = 'read'
		if(!$rbacsystem->checkAccess('read', $this->object->getRefId()))
		{
			$this->ilErr->raiseError($this->lng->txt('msg_no_perm_read'),$this->ilErr->MESSAGE);
		}
		
		if ($_POST['updateView'] == 1)
		{
			$_SESSION['pay_objects']['title_type'] = $_POST['title_type'];
			$_SESSION['pay_objects']['title_value'] = $_POST['title_value'];			
			$_SESSION['pay_objects']['pay_method'] = $_POST['pay_method'];			
			$_SESSION['pay_objects']['vendor'] = $_POST['vendor'];
		}	

		$this->tpl->addBlockfile('ADM_CONTENT','adm_content','tpl.main_view.html','Services/Payment');

		$this->__initPaymentObject();
		$this->lng->loadLanguageModule('search');
		
		$filter_form = new ilPropertyFormGUI();
		$filter_form->setFormAction($this->ctrl->getFormAction($this));
		$filter_form->setTitle($this->lng->txt('pay_filter'));
		$filter_form->setId('filter_form');
		$filter_form->setTableWidth('100 %');
	
		//hide_filter
		$o_hide_check = new ilCheckBoxInputGUI($this->lng->txt('show_filter'),'show_filter');
		$o_hide_check->setValue(1);		
		$o_hide_check->setChecked($_POST['show_filter'] ? 1 : 0);		
	
		$o_hidden = new ilHiddenInputGUI('updateView');
		$o_hidden->setValue(1);
		$o_hidden->setPostVar('updateView');
		$o_hide_check->addSubItem($o_hidden);

		//title
		$radio_group = new ilRadioGroupInputGUI($this->lng->txt('search_in_title'), 'title_type');
		$radio_option = new ilRadioOption($this->lng->txt('search_any_word'), 'or');
		$radio_group->addOption($radio_option);
		$radio_option = new ilRadioOption($this->lng->txt('search_all_words'), 'and');
		$radio_group->addOption($radio_option);

		$radio_group->setRequired(false);
		$radio_group->setValue('or');
		$radio_group->setPostVar('title_type');
		
		$o_title = new ilTextInputGUI();
		$o_title->setValue($_SESSION['pay_objects']['title_value']);
		$o_title->setPostVar('title_value');
		$o_title->setTitle($this->lng->txt('title'));
		
		$o_hide_check->addSubItem($radio_group);
		$o_hide_check->addSubItem($o_title);
		
		//vendor
		$o_vendor = new ilTextInputGUI();
		$o_vendor->setTitle($this->lng->txt('paya_vendor'));
		$o_vendor->setValue($_SESSION['pay_objects']['vendor']);				
		$o_vendor->setPostVar('vendor');
		$o_hide_check->addSubItem($o_vendor);
		
		// paymethod	
		$o_paymethod = new ilSelectInputGUI();
		$o_paymethod->setTitle($this->lng->txt('payment_system'));
		$o_paymethod->setOptions(ilPaymethods::getPayMethodsOptions('all'));
		$o_paymethod->setValue($_SESSION['pay_objects']['pay_method']);
		$o_paymethod->setPostVar('pay_method');
		$o_hide_check->addSubItem($o_paymethod);				
		
		$filter_form->addCommandButton('objects', $this->lng->txt('pay_update_view'));
		$filter_form->addCommandButton('resetObjectFilter', $this->lng->txt('pay_reset_filter'));
		
		$filter_form->addItem($o_hide_check);		
		if(!count($objects = ilPaymentObject::_getAllObjectsData()))
		{
			ilUtil::sendInfo($this->lng->txt('paya_no_objects_assigned'));
			
			return true;
		}		
		$this->tpl->setVariable('FORM', $filter_form->getHTML());

		$counter = 0;
		foreach($objects as $data)
		{
			$tmp_obj = ilObjectFactory::getInstanceByRefId($data['ref_id'], false);
			if($tmp_obj)
			{
				$f_result[$counter]['title'] = $tmp_obj->getTitle();
			}
			else
			{
				$f_result[$counter]['title'] = $this->lng->txt('object_not_found');
			}

			switch($data['status'])
			{
				case $this->pobject->STATUS_BUYABLE:
					$f_result[$counter]['status'] = $this->lng->txt('paya_buyable');
					break;

				case $this->pobject->STATUS_NOT_BUYABLE:
					$f_result[$counter]['status'] = $this->lng->txt('paya_not_buyable');
					break;
					
				case $this->pobject->STATUS_EXPIRES:
					$f_result[$counter]['status'] = $this->lng->txt('paya_expires');
					break;
			}

			include_once './Services/Payment/classes/class.ilPayMethods.php';
			$str_paymethod = ilPayMethods::getStringByPaymethod($data['pay_method']);
			$f_result[$counter]['pay_method'] = $str_paymethod;
			
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
		
			$f_result[$counter]['vat_rate'] = $vat_rate;
						
			$tmp_user = ilObjectFactory::getInstanceByObjId($data['vendor_id'], false);
			if($tmp_user )
			{
				$f_result[$counter]['vendor'] = $tmp_user->getFullname().' ['.$tmp_user->getLogin().']';
			}
			else
			{
				$f_result[$counter]['vendor'] = $this->lng->txt('user_not_found');
			}

			// Get number of purchasers
			$f_result[$counter]['purchasers'] = ilPaymentBookings::_getCountBookingsByObject($data['pobject_id']);

			// edit link
			$this->ctrl->setParameter($this,'pobject_id',$data['pobject_id']);
			$link_change = "<div class=\"il_ContainerItemCommands\"><a class=\"il_ContainerItemCommand\" href=\"".$this->ctrl->getLinkTarget($this,"editDetails")."\">".$this->lng->txt("edit")."</a></div>";

			$f_result[$counter]['options'] = $link_change;
			unset($tmp_user);
			unset($tmp_obj);

			++$counter;
		}
		
		return $this->__showObjectsTable($f_result);	

		//return true;
	}
	
	private function __showObjectsTable($a_result_set)
	{
		$this->ctrl->setParameter($this, 'cmd', 'objects');

		$tbl = new ilShopTableGUI($this);
		$tbl->setTitle($this->lng->txt('objects'));

		$tbl->setId('tbl_show_objects');
		$tbl->setRowTemplate("tpl.shop_objects_row.html", "Services/Payment");

		$tbl->addColumn($this->lng->txt('title'), 'title', '10%');
		$tbl->addColumn($this->lng->txt('status'), 'status', '10%');
		$tbl->addColumn($this->lng->txt('paya_pay_method'),'pay_method','10%');
		$tbl->addColumn($this->lng->txt('vat_rate'), 'vat_rate', '15%');
		$tbl->addColumn($this->lng->txt('paya_vendor'), 'vendor', '10%');
		$tbl->addColumn($this->lng->txt('paya_count_purchaser'), 'purchasers', '10%');
		$tbl->addColumn('','options','10%');

		$tbl->setData($a_result_set);

		$this->tpl->setVariable('TABLE', $tbl->getHTML());

		return true;
	}
	
	private function __initPaymentObject($a_pobject_id = 0)
	{
		$this->pobject = new ilPaymentObject($this->user_obj,$a_pobject_id);
		return true;
	}

	public function gatewayObject()
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

	public function resetFilterObject()
	{
		unset($_SESSION['pay_statistics']);
		unset($_POST['transaction_type']);
		unset($_POST['transaction_value']);
		unset($_POST['from']);
		unset($_POST['til']);
		unset($_POST['payed']);
		unset($_POST['access']);
		unset($_POST['customer']);
		unset($_POST['pay_method']);
		unset($_POST['updateView']);
		unset($_POST["adm_filter_title_id"]);
		ilUtil::sendInfo($this->lng->txt('paya_filter_reseted'));

		return $this->statisticObject();
	}

	public function statisticObject()
	{
		global $rbacsystem, $ilToolbar,$ilObjDataCache;

		include_once './Services/Payment/classes/class.ilPayMethods.php';
				
		// MINIMUM ACCESS LEVEL = 'read'
		if(!$rbacsystem->checkAccess('read', $this->object->getRefId()))
		{
			$this->ilErr->raiseError($this->lng->txt('msg_no_perm_read'),$this->ilErr->MESSAGE);
		}

		$ilToolbar->addButton($this->lng->txt('paya_add_customer'), $this->ctrl->getLinkTarget($this, 'showObjectSelector'));

		if ($_POST['updateView'] == 1)
		{
			$_SESSION['pay_statistics']['show_filter']= $_POST['show_filter'];
			$_SESSION['pay_statistics']['updateView'] = true;
			$_SESSION['pay_statistics']['until_check'] = $_POST['until_check'];
			$_SESSION['pay_statistics']['from_check'] = $_POST['from_check'];
			$_SESSION['pay_statistics']['transaction_type'] = isset($_POST['transaction_type']) ? $_POST['transaction_type'] : '' ;
			$_SESSION['pay_statistics']['transaction_value'] = isset($_POST['transaction_value']) ?  $_POST['transaction_value'] : '';
			$_SESSION['pay_statistics']['adm_filter_title_id'] = (int)$_POST['adm_filter_title_id'];

			if($_SESSION['pay_statistics']['from_check'] == '1')
			{
				$_SESSION['pay_statistics']['from']['date']['d'] = $_POST['from']['date']['d'];
				$_SESSION['pay_statistics']['from']['date']['m'] = $_POST['from']['date']['m'];
				$_SESSION['pay_statistics']['from']['date']['y'] = $_POST['from']['date']['y'];
			} 
			else 
			{
				$_SESSION['pay_statistics']['from']['date']['d'] = '';
				$_SESSION['pay_statistics']['from']['date']['m'] = '';
				$_SESSION['pay_statistics']['from']['date']['y'] = '';
			}
			
			if($_SESSION['pay_statistics']['until_check']== '1')
			{
				$_SESSION['pay_statistics']['til']['date']['d'] = $_POST['til']['date']['d'];
				$_SESSION['pay_statistics']['til']['date']['m'] = $_POST['til']['date']['m'];
				$_SESSION['pay_statistics']['til']['date']['y'] = $_POST['til']['date']['y'];
			} 
			else 
			{
				$_SESSION['pay_statistics']['til']['date']['d'] = '';
				$_SESSION['pay_statistics']['til']['date']['m'] = '';
				$_SESSION['pay_statistics']['til']['date']['y'] = '';
			}

			$_SESSION['pay_statistics']['payed'] = $_POST['payed'];
			$_SESSION['pay_statistics']['access'] = $_POST['access'];
			$_SESSION['pay_statistics']['pay_method'] = $_POST['pay_method'];
			$_SESSION['pay_statistics']['customer'] = isset ($_POST['customer']) ? $_POST['customer'] : '';
			$_SESSION['pay_statistics']['vendor'] = isset ($_POST['vendor']) ? $_POST['vendor']: '';

		}

		$this->tpl->addBlockfile('ADM_CONTENT','adm_content','tpl.main_view.html','Services/Payment');

		$filter_form = new ilPropertyFormGUI();
		$filter_form->setFormAction($this->ctrl->getFormAction($this));
		$filter_form->setTitle($this->lng->txt('pay_filter'));
		$filter_form->setId('formular');
		$filter_form->setTableWidth('100 %');
		//filter	
		$o_hide_check = new ilCheckBoxInputGUI($this->lng->txt('show_filter'),'show_filter');
		$o_hide_check->setValue(1);		
		$o_hide_check->setChecked($_POST['show_filter'] ? 1 : 0);		

		$o_hidden = new ilHiddenInputGUI('updateView');
		$o_hidden->setValue(1);
		$o_hidden->setPostVar('updateView');
		$o_hide_check->addSubItem($o_hidden);
			
		$o_transaction_type = new ilSelectInputGUI(); 
		$trans_option = array($this->lng->txt('pay_starting'),$this->lng->txt('pay_ending'));
		$trans_value = array('0','1'); 
		$o_transaction_type->setTitle($this->lng->txt('paya_transaction'));
		$o_transaction_type->setOptions($trans_option);
		$o_transaction_type->setValue($_SESSION['pay_statistics']['transaction_type']);		
		$o_transaction_type->setPostVar('transaction_type');
		$o_hide_check->addSubItem($o_transaction_type);
		
		$o_transaction_val = new ilTextInputGUI();
		$o_transaction_val->setValue($_SESSION['pay_statistics']['transaction_value']);		
		$o_transaction_val->setPostVar('transaction_value');
		$o_hide_check->addSubItem($o_transaction_val);

		$o_customer = new ilTextInputGUI();
		$o_customer->setTitle($this->lng->txt('paya_customer'));
		$o_customer->setValue($_SESSION['pay_statistics']['customer']);		
		$o_customer->setPostVar('customer');
		$o_hide_check->addSubItem($o_customer);
		
		$o_vendor = new ilTextInputGUI();
		$o_vendor->setTitle($this->lng->txt('paya_vendor'));
		$o_vendor->setValue($_SESSION['pay_statistics']['vendor']);				
		$o_vendor->setPostVar('vendor');
		$o_hide_check->addSubItem($o_vendor);
		
		$o_from_check = new ilCheckBoxInputGUI($this->lng->txt('pay_order_date_from'),'from_check');
		$o_from_check->setValue(1);		
		$o_from_check->setChecked($_SESSION['pay_statistics']['from_check'] ? 1 : 0);		
		
		$o_date_from = new ilDateTimeInputGUI();
		$o_date_from->setPostVar('from');			
		
		if($_SESSION['pay_statistics']['from_check'] == '1') 
		{
			$o_date_from->setValueByArray($_SESSION['pay_statistics']['from']);	
			$o_date_from->checkInput();	
		}

		$o_from_check->addSubItem($o_date_from);
		$o_hide_check->addSubItem($o_from_check);
		
		$o_until_check = new ilCheckBoxInputGUI($this->lng->txt('pay_order_date_til'), 'until_check');
		$o_until_check->setValue(1);	
		$o_until_check->setChecked($_SESSION['pay_statistics']['until_check'] ? 1 : 0);				

		$o_date_until = new ilDateTimeInputGUI();
		$o_date_until->setPostVar('til');

		if($_SESSION['pay_statistics']['until_check'] == '1') 
		{
			$o_date_until->setValueByArray($_SESSION['pay_statistics']['til']);		
			$o_date_until->checkInput();	
		}
		
		$o_until_check->addSubItem($o_date_until);
		$o_hide_check->addSubItem($o_until_check);	

		// title filter
		$this->__initBookingObject();
		$title_options['all']=$this->lng->txt('pay_all');
		$unique_titles = $this->booking_obj->getUniqueTitles();

		if(is_array($unique_titles) && count($unique_titles))
		{
			foreach($unique_titles as $ref_id)
			{
				$title_options[$ref_id] = $ilObjDataCache->lookupTitle($ilObjDataCache->lookupObjId($ref_id));
			}
		}

		$o_object_title = new ilSelectInputGUI();
		$o_object_title->setTitle($this->lng->txt('title'));
		$o_object_title->setOptions($title_options);
		$o_object_title->setValue($_SESSION["pay_statistics"]["adm_filter_title_id"]);
		$o_object_title->setPostVar('adm_filter_title_id');
		$o_hide_check->addSubItem($o_object_title);
		
		$o_payed = new ilSelectInputGUI();
		$payed_option = array('all'=>$this->lng->txt('pay_all'),'1'=>$this->lng->txt('yes'),'0'=>$this->lng->txt('no'));

		$o_payed->setTitle($this->lng->txt('paya_payed'));
		$o_payed->setOptions($payed_option);
		$o_payed->setValue($_SESSION['pay_statistics']['payed']);
		$o_payed->setPostVar('payed');		

		$o_hide_check->addSubItem($o_payed);

		$o_access = new ilSelectInputGUI();
		$access_option = array('all'=>$this->lng->txt('pay_all'),'1'=>$this->lng->txt('yes'),'0'=>$this->lng->txt('no'));

		$o_access->setTitle($this->lng->txt('paya_access'));
		$o_access->setOptions($access_option);
		$o_access->setValue($_SESSION['pay_statistics']['access']);
		$o_access->setPostVar('access');
		$o_hide_check->addSubItem($o_access);		
		
		$o_paymethod = new ilSelectInputGUI();
		$o_paymethod->setTitle($this->lng->txt('payment_system'));
		$o_paymethod->setOptions(ilPaymethods::getPayMethodsOptions('all'));
		$o_paymethod->setValue($_SESSION['pay_statistics']['pay_method']);
		$o_paymethod->setPostVar('pay_method');
		$o_hide_check->addSubItem($o_paymethod);				
		
		$filter_form->addCommandButton('statistic', $this->lng->txt('pay_update_view'));
		$filter_form->addCommandButton('resetFilter', $this->lng->txt('pay_reset_filter'));
		
		$filter_form->addItem($o_hide_check);		

		$this->tpl->setVariable('FORM', $filter_form->getHTML());
		//else 	$filter_form->checkInput();
		
		// STATISTICS TABLE
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
				if(ANONYMOUS_USER_ID == $booking['customer_id'])
				{
					$tmp_purchaser = ilObjUser::_lookupLogin($booking['customer_id']);
					$purchaser_name = $booking['name_extern'];
					$tmp_purchaser_email = $booking['email_extern'];
				}
				else
				{
					$tmp_purchaser = ilObjUser::_lookupLogin($booking['customer_id']);
					$tmp_purchaser_name = ilObjUser::_lookupName($booking['customer_id']);
					$purchaser_name = $tmp_purchaser_name['firstname'].' '.$tmp_purchaser_name['lastname'];
					$tmp_purchaser_email = ilObjUser::_lookupEmail($booking['customer_id']);
				}			
				$user_title_cache[$booking['customer_id']] = $tmp_purchaser;
			}
						
			$transaction = $booking['transaction_extern'];
			$str_paymethod = ilPayMethods::getStringByPaymethod($booking['b_pay_method']);
			$transaction .= $booking['transaction']."<br> (" . $str_paymethod . ")";
			
			$f_result[$counter]['transaction'] = $transaction;
			$f_result[$counter]['object_title'] = ($tmp_obj != '' ?  $tmp_obj : $this->lng->txt('object_deleted'));
			$f_result[$counter]['vendor'] = ($tmp_vendor != '' ?  '['.$tmp_vendor.']' : $this->lng->txt('user_deleted'));
			$f_result[$counter]['customer'] = ($tmp_purchaser != '' ?
									$purchaser_name. ' ['.$tmp_purchaser.']<br>'
									.$tmp_purchaser_email 
									: $this->lng->txt('user_deleted'));
			$f_result[$counter]['order_date'] =	ilDatePresentation::formatDate(new ilDate($booking['order_date'], IL_CAL_UNIX));
			
			
			if($booking['duration'] == 0 && $booking['access_enddate'] == NULL)
			{
				$f_result[$counter]['duration'] = $this->lng->txt("unlimited_duration");
			}
			else
			{
//				if($booking['duration'] > 0 )
//				{
//					$f_result[$counter]['duration'] = $booking['duration'].' '.$this->lng->txt('paya_months').' / </br>';
//				}
				$f_result[$counter]['duration'] .= ilDatePresentation::formatDate(new ilDate($booking['access_startdate'], IL_CAL_DATETIME))
						.' - '.ilDatePresentation::formatDate(new ilDate($booking['access_enddate'], IL_CAL_DATETIME));
			}

			$f_result[$counter]['price'] = $booking['price'].' '.$booking['currency_unit'];
			$f_result[$counter]['discount'] = ($booking['discount'] != '' ? ($booking['discount'].' '.$booking['currency_unit']) : '&nbsp;');

			$payed_access = $booking['payed'] ? 
				$this->lng->txt('yes') : 
				$this->lng->txt('no');

			$payed_access .= '/';
			$payed_access .= $booking['access_granted'] ?
				$this->lng->txt('yes') : 
				$this->lng->txt('no');

			$f_result[$counter]['payed_access'] = $payed_access;

			$this->ctrl->setParameter($this,"booking_id",$booking['booking_id']);
			$link_change = "<div class=\"il_ContainerItemCommands\"><a class=\"il_ContainerItemCommand\" href=\"".$this->ctrl->getLinkTarget($this,"editStatistic")."\">".$this->lng->txt("edit")."</a></div>";

			$f_result[$counter]['edit'] = $link_change;

			unset($tmp_obj);
			unset($tmp_vendor);
			unset($tmp_purchaser);

			++$counter;
		}
		return $this->__showStatisticTable($f_result);
	}
	
	public function editStatisticObject($a_show_confirm_delete = false)
	{
		global $ilToolbar;

		include_once './Services/Payment/classes/class.ilPayMethods.php';
				
		if(!isset($_GET['booking_id']))
		{
			ilUtil::sendInfo($this->lng->txt('paya_no_booking_id_given'));
			$this->showStatistics();

			return true;
		}

		$ilToolbar->addButton($this->lng->txt('back'), $this->ctrl->getLinkTarget($this, 'statistic'));

		$this->tpl->addBlockfile('ADM_CONTENT','adm_content','tpl.main_view.html','Services/Payment');	
		$this->ctrl->setParameter($this,'booking_id',(int) $_GET['booking_id']);

		// confirm delete
		if($a_show_confirm_delete)
		{
			$oConfirmationGUI = new ilConfirmationGUI();
			
			// set confirm/cancel commands
			$oConfirmationGUI->setFormAction($this->ctrl->getFormAction($this,"performDelete"));
			$oConfirmationGUI->setHeaderText($this->lng->txt("paya_sure_delete_stat"));
			$oConfirmationGUI->setCancel($this->lng->txt("cancel"), "statistic");
			$oConfirmationGUI->setConfirm($this->lng->txt("confirm"), "performDelete");			
		
			$this->tpl->setVariable('CONFIRMATION', $oConfirmationGUI->getHTML());
			return true;
		}
			

		$this->__initBookingObject();
		$bookings = $this->booking_obj->getBookings();
		$booking = $bookings[(int) $_GET['booking_id']];

		// get customer_obj
		$tmp_user = ilObjectFactory::getInstanceByObjId($booking['customer_id'], false);

		$oForm = new ilPropertyFormGUI();
		$oForm->setFormAction($this->ctrl->getFormAction($this));
		$oForm->setId('stat_form');
		$oForm->setTableWidth('50 %');		
		if(is_object($tmp_user))
		{
			$frm_user = $tmp_user->getFullname().' ['.$tmp_user->getLogin().']';
		}
		else
		{
			$frm_user = $this->lng->txt('user_deleted');
		}
		$oForm->setTitle($frm_user);

		$pObj = new ilPaymentObject($this->user_obj, $booking['pobject_id']);
		$tmp_obj = ilObject::_lookupTitle(ilObject::_lookupObjId($pObj->getRefId()));				

		// object_title
		$oTitleGUI = new ilNonEditableValueGUI($this->lng->txt('title'));
		$oTitleGUI->setValue($tmp_obj != '' ?  $tmp_obj : $this->lng->txt('object_deleted'));
		$oForm->addItem($oTitleGUI);
				
		// transaction
		$oTransactionGUI = new ilNonEditableValueGUI($this->lng->txt('paya_transaction'));
		$oTransactionGUI->setValue($booking['transaction']);
		$oForm->addItem($oTransactionGUI);
		
		//vendor
		$oVendorGUI = new ilNonEditableValueGUI($this->lng->txt('paya_vendor'));
		$tmp_vendor = ilObjectFactory::getInstanceByObjId($booking['b_vendor_id'], false);
		if(is_object($tmp_vendor))
		{
			$frm_vendor = $tmp_vendor->getFullname().' ['.$tmp_vendor->getLogin().']';
		}
		else
		{
			$frm_vendor =  $this->lng->txt('user_deleted');
		}		
		$oVendorGUI->setValue($frm_vendor);
		$oForm->addItem($oVendorGUI);

		// paymethod
		$oPaymethodGUI = new ilNonEditableValueGUI($this->lng->txt('paya_pay_method'));
		$oPaymethodGUI->setValue(ilPayMethods::getStringByPaymethod($booking['b_pay_method']));
		$oForm->addItem($oPaymethodGUI);	

		// order_date
		$oOrderdateGUI = new ilNonEditableValueGUI($this->lng->txt('paya_order_date'));
		$oOrderdateGUI->setValue(ilDatePresentation::formatDate(new ilDate($booking['order_date'], IL_CAL_UNIX)));
		$oForm->addItem($oOrderdateGUI);	
		
		// duration
		$oDurationGUI = new ilNonEditableValueGUI($this->lng->txt('duration'));
		if($booking['duration'] == 0 && $booking['access_enddate'] == NULL)
		{
			$frm_duration = $this->lng->txt("unlimited_duration");
			
		}
		else
		{				
			if($booking['duration'] > 0)
			{
				$frm_duration = $booking['duration'].' '.$this->lng->txt('paya_months').'</br>';
			}
			$frm_duration .= ilDatePresentation::formatDate(new ilDate($booking['access_startdate'], IL_CAL_DATETIME))
					.' - '.ilDatePresentation::formatDate(new ilDate($booking['access_enddate'], IL_CAL_DATETIME));
			
		}		
		$oDurationGUI->setValue($frm_duration);
		$oForm->addItem($oDurationGUI);		
		
		// price
		$oPriceGUI = new ilNonEditableValueGUI($this->lng->txt('price_a'));
		$oPriceGUI->setValue($booking['price'].' '.$booking['currency_unit']);
		$oForm->addItem($oPriceGUI);

		//discount
		$oDiscountGUI = new ilNonEditableValueGUI($this->lng->txt('paya_coupons_coupon'));
		$oDiscountGUI->setValue($booking['discount'].' '.$booking['currency_unit']);
		$oForm->addItem($oDiscountGUI);
		
		// payed
		$oPayedGUI = new ilSelectInputGUI();
		$payed_option = array(0 => $this->lng->txt('no'),1 => $this->lng->txt('yes'));

		$oPayedGUI->setTitle($this->lng->txt('paya_payed'));
		$oPayedGUI->setOptions($payed_option);
		$oPayedGUI->setValue($booking['payed']);
		$oPayedGUI->setPostVar('payed');		
		$oForm->addItem($oPayedGUI);
		
		// access
		$oAccessGUI = new ilSelectInputGUI();
		$access_option = array(0 => $this->lng->txt('no'),1 => $this->lng->txt('yes'));

		$oAccessGUI->setTitle($this->lng->txt('paya_access'));
		$oAccessGUI->setOptions($access_option);
		$oAccessGUI->setValue($booking['access_granted']);
		$oAccessGUI->setPostVar('access');		
		$oForm->addItem($oAccessGUI);
		
		$oForm->addCommandButton('updateStatistic',$this->lng->txt('save'));
		$oForm->addCommandButton('deleteStatistic',$this->lng->txt('delete'));

		// show CUSTOMER_DATA if isset -> setting: save_user_address
		if(ilPayMethods::_PMEnabled($booking['b_pay_method']))
		{
			$oForm2 = new ilPropertyFormGUI();
			$oForm2->setId('cust_form');
			$oForm2->setTableWidth('50%');		
			$oForm2->setTitle($frm_user);		

			// email
			$oEmailGUI = new ilNonEditableValueGUI($this->lng->txt('email'));
			$email = (!$tmp_user) ? $this->lng->txt('user_deleted') : $tmp_user->getEmail();
			$oEmailGUI->setValue($email);
			$oForm2->addItem($oEmailGUI);	

			// street
			$oStreetGUI = new ilNonEditableValueGUI($this->lng->txt('street'));
			$oStreetGUI->setValue($booking['street']);
			$oForm2->addItem($oStreetGUI);
				
			// pobox
			$oPoBoxGUI = new ilNonEditableValueGUI($this->lng->txt('pay_bmf_po_box'));
			$oPoBoxGUI->setValue($booking['po_box']);
			$oForm2->addItem($oPoBoxGUI);	
				
			// zipcode
			$oPoBoxGUI = new ilNonEditableValueGUI($this->lng->txt('zipcode'));
			$oPoBoxGUI->setValue($booking['zipcode']);
			$oForm2->addItem($oPoBoxGUI);
					
			// city
			$oCityGUI = new ilNonEditableValueGUI($this->lng->txt('city'));
			$oCityGUI->setValue($booking['city']);
			$oForm2->addItem($oCityGUI);	
			
			// country
			$oCountryGUI = new ilNonEditableValueGUI($this->lng->txt('country'));
			$oCountryGUI->setValue($booking['country']);
			$oForm2->addItem($oCountryGUI);	
		}
		
		$this->tpl->setVariable('FORM',$oForm->getHTML());
		$this->tpl->setVariable('FORM_2',$oForm2->getHTML());
		return true;
		
	}
	public function updateStatisticObject()
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
			ilUtil::sendSuccess($this->lng->txt('paya_updated_booking'));

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

	public function deleteStatisticObject()
	{
		if(!isset($_GET['booking_id']))
		{
			ilUtil::sendInfo($this->lng->txt('paya_no_booking_id_given'));
			$this->statisticObject();

			return true;
		}
		ilUtil::sendQuestion($this->lng->txt('paya_sure_delete_stat'));

		$this->editStatisticObject(true);

		return true;
	}
	public function performDeleteObject()
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

	/**
	* get tabs
	* @access	public
	* @param	object	ilTabsGUI gui object
	*/
	public function getTabs($tabs_gui)
	{
		global $rbacsystem, $ilHelp;

		$tabs_gui->clearTargets();
		$ilHelp->setScreenIdComponent("pays");
		if ($rbacsystem->checkAccess('visible,read',$this->object->getRefId()))
		{
			// Settings
			$tabs_gui->addTarget('settings', $this->ctrl->getLinkTarget($this, 'generalSettings'),
			array('saveGeneralSettings','generalSettings ','saveBmfSettings','savePaypalSettings','paypalSettings',
//					'saveEPaySettings','epaySettings','saveERPsettings','delERPpreview','','testERPsettings','erpSettings',
				'','view'), '', '');
			
			// Bookings
			$tabs_gui->addTarget('bookings', $this->ctrl->getLinkTarget($this, 'statistic'),
			array(	'statistic','editStatistic','updateStatistic','deleteStatistic','performDelete',
					'resetFilter','exportVendors','addCustomer', 'saveCustomer','showObjectSelector',
					'searchUserSP','performSearchSP'), '', '');
			// Objects
			$tabs_gui->addTarget('objects', $this->ctrl->getLinkTarget($this, 'objects'),
			array('updateObjectDetails','deleteObject','performObjectDelete','objects',
					'editPrices','addPrice','editDetails','resetObjectFilter'), '', '');
			// Vendors
			$tabs_gui->addTarget('vendors', $this->ctrl->getLinkTarget($this, 'vendors'),
			array('vendors','searchUser','search','performSearch','addVendor','addUser','exportVendors','deleteVendors','performDeleteVendors',
					'cancelDeleteVendors','editVendor','performEditVendor'), '', '');
			
#TODO: CURRENCY not finished yet
/*	
			// Currencies
			$tabs_gui->addTarget('currencies',
				$this->ctrl->getLinkTarget($this, 'currencies'),
					array('currencies','editCurrency','deleteCurrency','performDeleteCurrency','updateCurrency','updateDefaultCurrency'), '','');
/**/
			// Paymethods
			$tabs_gui->addTarget('pay_methods', $this->ctrl->getLinkTarget($this, 'payMethods'),
				#array('payMethods','savePayMethods'), '', '');
			array('payMethods','savePayMethods ','saveBmfSettings','savePaypalSettings','paypalSettings',
//					'saveEPaySettings','epaySettings','saveERPsettings','delERPpreview','','testERPsettings','erpSettings',
				'','view'), '', '');

			// Topics
			$tabs_gui->addTarget('topics',
					$this->ctrl->getLinkTargetByClass('ilshoptopicsgui', ''), 'payment_topics', '', '');

			// Vats
			$tabs_gui->addTarget('vats',
					$this->ctrl->getLinkTarget($this, 'vats'), 'vats', '', '');				

			// Documents
			$tabs_gui->addTarget('documents', $this->ctrl->getLinkTarget($this, 'documents'),
				array('documents','TermsConditions','saveTermsConditions','BillingMail',
					'saveBillingMail','InvoiceNumber','saveInvoiceNumber','StatutoryRegulations', 'saveStatutoryRegulations'), '', '');
 		}

		if ($rbacsystem->checkAccess('edit_permission',$this->object->getRefId()))
		{
			// Permissions
			$tabs_gui->addTarget('perm_settings',
				$this->ctrl->getLinkTargetByClass(array(get_class($this),'ilpermissiongui'), 'perm'), array('perm','info','owner'), 'ilpermissiongui');
		}
	}

	private function  getSubTabs($a_tab, $a_sub_tab = null)
	{
		switch($a_tab)
		{
			case 'bookings':
				break;
			case 'objects':
				break;
			case 'vendors':
				break;
			case 'payMethods':

				if(!$a_sub_tab) $a_sub_tab = 'payMethods';
				$this->tabs_gui->addSubTabTarget('settings',
					$this->ctrl->getLinkTargetByClass('ilobjpaymentsettingsgui', 'payMethods'),
					'','', '',$a_sub_tab == 'payMethods' ? true : false);

				$this->tabs_gui->addSubTabTarget('pays_bmf',
					$this->ctrl->getLinkTargetByClass('ilobjpaymentsettingsgui', 'bmfSettings'),
					'','', '',$a_sub_tab == 'bmfSettings' ? true : false);

				$this->tabs_gui->addSubTabTarget('pays_paypal',
					 $this->ctrl->getLinkTargetByClass('ilobjpaymentsettingsgui', 'paypalSettings'),
					 '','', '',$a_sub_tab == 'paypalSettings' ? true : false);

				break;
			case 'currencies':
				break;
			case 'vats':
				break;
			case 'topics':
				if(!$a_sub_tab) $a_sub_tab = 'topics';
				$this->tabs_gui->addSubTabTarget('topics',
					$this->ctrl->getLinkTargetByClass('ilshoptopicsgui', 'showTopicsList'),
					'','', '',$a_sub_tab == 'showTopicsList' ? true : false);

				$this->tabs_gui->addSubTabTarget('topics_settings',
					$this->ctrl->getLinkTargetByClass('ilshoptopicsgui', 'showTopicsSettings'),
					'','', '',$a_sub_tab == 'showTopicsSettings' ? true : false);

				break;
			case 'documents':
				if(!$a_sub_tab) $a_sub_tab = 'terms_conditions';
				$this->tabs_gui->addSubTabTarget('terms_conditions',
					$this->ctrl->getLinkTargetByClass('ilobjpaymentsettingsgui', 'TermsConditions'),
					'','', '',$a_sub_tab == 'terms_conditions' ? true : false);

				$this->tabs_gui->addSubTabTarget('billing_mail',
					$this->ctrl->getLinkTargetByClass('ilobjpaymentsettingsgui', 'BillingMail'),
					'','', '',$a_sub_tab == 'billing_mail' ? true : false);
				$this->tabs_gui->addSubTabTarget('invoice_number',
					$this->ctrl->getLinkTargetByClass('ilobjpaymentsettingsgui', 'InvoiceNumber'),
					'','', '',$a_sub_tab == 'invoice_number' ? true : false);
				$this->tabs_gui->addSubTabTarget('statutory_regulations',
					$this->ctrl->getLinkTargetByClass('ilobjpaymentsettingsgui', 'StatutoryRegulations'),
					'','', '',$a_sub_tab == 'statutory_regulations' ? true : false);
				break;

			default:
			case 'settings':
				if (($_GET['cmd'] == '') || ($_GET['cmd'] == 'view') || ($a_sub_tab == 'generalSettings'))
				$a_sub_tab = 'generalSettings';
				break;
		}
	}

	public function generalSettingsObject()
	{	
		global $rbacsystem, $ilSetting;

		/**
		 * @var $this->object ilObject
		 */
		// MINIMUM ACCESS LEVEL = 'read'
		if(!$rbacsystem->checkAccess('read', $this->object->getRefId()))
		{
			$this->ilErr->raiseError($this->lng->txt('msg_no_perm_read'),$this->ilErr->MESSAGE);
		}
		
		$this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.main_view.html','Services/Payment');		

		$genSet = ilPaymentSettings::_getInstance();
		$genSetData = $genSet->getAll();
						
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
		$formItem->setRequired(true);
		$form->addItem($formItem);

		$formItem = new ilTextAreaInputGUI($this->lng->txt('pays_address'), 'address');
		$formItem->setRows(7);
		$formItem->setCols(35);
		$formItem->setRequired(true);
		$formItem->setValue($this->error != '' && isset($_POST['address'])
							? ilUtil::prepareFormOutput($_POST['address'],true)
							: $genSetData['address']);
		$form->addItem($formItem);
		
		$formItem = new ilTextAreaInputGUI($this->lng->txt('pays_bank_data'), 'bank_data');
		$formItem->setRows(7);
		$formItem->setCols(35);
		$formItem->setRequired(true);
		$formItem->setValue($this->error != '' && isset($_POST['bank_data'])
							? ilUtil::prepareFormOutput($_POST['bank_data'],true)
							: $genSetData['bank_data']);
		$form->addItem($formItem);
		
		$formItem = new ilTextAreaInputGUI($this->lng->txt('pays_add_info'), 'add_info');
		$formItem->setRows(7);
		$formItem->setCols(35);
		$formItem->setValue($this->error != '' && isset($_POST['add_info'])
							? ilUtil::prepareFormOutput($_POST['add_info'],true)
							: $genSetData['add_info']);
		$form->addItem($formItem);
	
		$formItem = new ilTextInputGUI($this->lng->txt('pays_pdf_path'), 'pdf_path');
		$formItem->setValue($this->error != "" && isset($_POST['pdf_path'])
							? ilUtil::prepareFormOutput($_POST['pdf_path'],true)
							: ilUtil::prepareFormOutput($genSetData['pdf_path'],true));
		$formItem->setRequired(true);
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

		// hide advanced search
		$formItem = new ilCheckboxInputGUI($this->lng->txt('pay_hide_advanced_search'), 'hide_advanced_search');
		$formItem->setChecked((int)$genSetData['hide_advanced_search']);
		$formItem->setInfo($this->lng->txt('pay_hide_advanced_search_info'));
		$form->addItem($formItem);

		// hide shop news
		$formItem = new ilCheckboxInputGUI($this->lng->txt('pay_hide_news'), 'hide_news');
		$formItem->setChecked((int)$genSetData['hide_news']);
		$formItem->setInfo($this->lng->txt('pay_hide_news_info'));
		$form->addItem($formItem);

		// Hide coupons
		$formItem = new ilCheckboxInputGUI($this->lng->txt('pay_hide_coupons'), 'hide_coupons');
		$formItem->setChecked((int)$genSetData['hide_coupons']);
		$formItem->setInfo($this->lng->txt('pay_hide_coupons'));
		$form->addItem($formItem);
	
		// hide shop news
		$formItem = new ilCheckboxInputGUI($this->lng->txt('pay_hide_shop_info'), 'hide_shop_info');
		$formItem->setChecked((int)$genSetData['hide_shop_info']);
		$formItem->setInfo($this->lng->txt('pay_hide_shop_info_info'));
		$form->addItem($formItem);

		// use shop specials
		$formItem = new ilCheckboxInputGUI($this->lng->txt('use_shop_specials'), 'use_shop_specials');
		$formItem->setChecked((int)$genSetData['use_shop_specials']);
		$formItem->setInfo($this->lng->txt('use_shop_specials_info'));
		$form->addItem($formItem);

		// show general filter
		$formItem = new ilCheckboxInputGUI($this->lng->txt('show_general_filter'), 'show_general_filter');
		$formItem->setChecked((int)$genSetData['show_general_filter']);
		$formItem->setInfo($this->lng->txt('show_general_filter_info'));
		$form->addItem($formItem);

		// show shop explorer
		$formItem = new ilCheckboxInputGUI($this->lng->txt('show_shop_explorer'), 'show_shop_explorer');
		$formItem->setChecked((int)$genSetData['show_shop_explorer']);
		$formItem->setInfo($this->lng->txt('show_shop_explorer_info'));
		$form->addItem($formItem);

/**/
		// Enable payment notifications
		$payment_noti = new ilCheckboxInputGUI($this->lng->txt("payment_notification"), "payment_notification");
		$payment_noti->setValue(1);
		$payment_noti->setChecked((int)$ilSetting->get('payment_notification', 0) == 1);
		$payment_noti->setInfo($this->lng->txt('payment_notification_desc'));

		$num_days = new ilNumberInputGUI($this->lng->txt('payment_notification_days'),'payment_notification_days');
		$num_days->setSize(3);
		$num_days->setMinValue(0);
		$num_days->setMaxValue(120);
		$num_days->setRequired(true);
		$num_days->setValue($ilSetting->get('payment_notification_days'));
		$num_days->setInfo($this->lng->txt('payment_notification_days_desc'));

		$payment_noti->addSubItem($num_days);
		$form->addItem($payment_noti);

/**/
		$this->tpl->setVariable('FORM',$form->getHTML());
		return true;
	}
	
	public function saveGeneralSettingsObject()
	{
		global $rbacsystem, $ilSetting;

		// MINIMUM ACCESS LEVEL = 'read'
		if(!$rbacsystem->checkAccess('read', $this->object->getRefId()))
		{
			$this->ilErr->raiseError($this->lng->txt('msg_no_perm_read'),$this->ilErr->MESSAGE);
		}
		
		/** 
		 * @var $genSet ilPaymentSettings
		 * */
		$genSet = ilPaymentSettings::_getInstance();

		if ($_POST['currency_unit'] == '' ||
			$_POST['address'] == '' ||
			$_POST['bank_data'] == '' ||
			$_POST['pdf_path'] == '')
		{
			$this->error = $this->lng->txt('pays_general_settings_not_valid');
			ilUtil::sendFailure($this->error);
			return $this->generalSettingsObject();
	
		}

		$genSet->set('currency_unit', $_POST['currency_unit'], 'currencies');
		$genSet->set('address', $_POST['address'], 'invoice');
		$genSet->set('bank_data', $_POST['bank_data'], 'invoice');
		$genSet->set('add_info', $_POST['add_info'], 'invoice');
		$genSet->set('pdf_path', $_POST['pdf_path'], 'invoice');
		
		$genSet->set('max_hits', $_POST['max_hits'], 'gui');

		$genSet->set('hide_advanced_search', $_POST['hide_advanced_search'], 'gui');
		#$genSet->set('hide_filtering', $_POST['hide_filtering'], 'gui');
		$genSet->set('objects_allow_custom_sorting', $_POST['objects_allow_custom_sorting'], 'gui');
		$genSet->set('hide_coupons', $_POST['hide_coupons'], 'gui');
		$genSet->set('hide_news', $_POST['hide_news'], 'gui');
		
		if( (int)$_POST['hide_news']  == 0 )
		{
			if( (int)$ilSetting->get('payment_news_archive_period') == 0)
			{
				$ilSetting->set('payment_news_archive_period', '5');
			}
		}
		
		$genSet->set('hide_shop_info', $_POST['hide_shop_info'], 'gui');
		$genSet->set('use_shop_specials', $_POST['use_shop_specials'], 'gui');
		$genSet->set('show_general_filter', $_POST['show_general_filter'], 'gui');
		$genSet->set('show_shop_explorer', $_POST['show_shop_explorer'], 'gui');

		// payment notification
		$ilSetting->set('payment_notification', $_POST['payment_notification'] ? 1 : 0);
		$ilSetting->set('payment_notification_days', $_POST['payment_notification_days']);

		$check = $this->checkShopActivationObject();
		if($check == true && (int)$_POST['shop_enabled'])
		{
			$genSet->set('shop_enabled', 1, 'common');
		}
		else
		{
			$genSet->set('shop_enabled', 0, 'common');
		}
		ilUtil::sendSuccess($this->lng->txt('pays_updated_general_settings'));

		$this->generalSettingsObject();
		return true;
	}
	
	public function paypalSettingsObject()
	{	
		/**
		 * @var $rbacsystem ilRbacSystem
		 * 
		 */
		global $rbacsystem;
		
		// MINIMUM ACCESS LEVEL = 'read'
		if(!$rbacsystem->checkAccess('read', $this->object->getRefId()))
		{
			$this->ilErr->raiseError($this->lng->txt('msg_no_perm_read'),$this->ilErr->MESSAGE);
		}

		include_once './Services/Payment/classes/class.ilPaypalSettings.php';		

		$this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.main_view.html','Services/Payment');
		
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

//		$formItem = new ilPasswordInputGUI($this->lng->txt('password'), 'vendor_password');
//		$formItem->setRetype(false);
//		$formItem->setValue($ppSet->getVendorPassword());
//		$form->addItem($formItem);
		
		$formItem = new ilTextInputGUI($this->lng->txt('pays_auth_token'), 'auth_token');
		$formItem->setValue($ppSet->getAuthToken());
		$form->addItem($formItem);
		
		$formItem = new ilTextInputGUI($this->lng->txt('pays_page_style'), 'page_style');
		$formItem->setValue($ppSet->getPageStyle());
		$form->addItem($formItem);
				
		$this->tpl->setVariable('FORM',$form->getHTML());		
	}
	
	public function savePaypalSettingsObject()
	{
		include_once './Services/Payment/classes/class.ilPaypalSettings.php';

		global $rbacsystem;

		// MINIMUM ACCESS LEVEL = 'read'
		if(!$rbacsystem->checkAccess('read', $this->object->getRefId()))
		{
			$this->ilErr->raiseError($this->lng->txt('msg_no_perm_read'),$this->ilErr->MESSAGE);
		}
		
		$ppSet = ilPaypalSettings::getInstance();
		
		$ppSet->setServerHost(ilUtil::stripSlashes($_POST['server_host']));
		$ppSet->setServerPath(ilUtil::stripSlashes($_POST['server_path']));
		$ppSet->setVendor(ilUtil::stripSlashes($_POST['vendor']));
//		$ppSet->setVendorPassword(ilUtil::stripSlashes($_POST['vendor_password']));
		$ppSet->setAuthToken(ilUtil::stripSlashes($_POST['auth_token']));
		$ppSet->setPageStyle(ilUtil::stripSlashes($_POST['page_style']));
		$ppSet->setSsl(ilUtil::stripSlashes($_POST['ssl']));		

		if ($_POST['server_host'] == '' ||
			$_POST['server_path'] == '' ||
			$_POST['vendor'] == '' ||
//			$_POST['vendor_password'] == '' ||
			$_POST['auth_token'] == '')
		{
			$this->error = $this->lng->txt('pays_paypal_settings_not_valid');
			ilUtil::sendFailure($this->error);
			$this->paypalSettingsObject();
			return true;
			
		}
		
		$ppSet->save();
				
		$this->paypalSettingsObject();

		ilUtil::sendSuccess($this->lng->txt('pays_updated_paypal_settings'));

		return true;
	}

	//function vendorsObject($a_show_confirm = false)
	public function vendorsObject($a_show_confirm = false)
	{
	//	include_once './Services/Payment/classes/class.ilPaymentBookings.php';
		#include_once './Services/Table/classes/class.ilTable2GUI.php';
	
		global $rbacsystem, $ilToolbar;

		// MINIMUM ACCESS LEVEL = 'read'
		if(!$rbacsystem->checkAccess('read', $this->object->getRefId()))
		{
			$this->ilErr->raiseError($this->lng->txt('msg_no_perm_read'),$this->ilErr->MESSAGE);
		}

		$_SESSION['pays_vendor'] = is_array($_SESSION['pays_vendor']) ?  $_SESSION['pays_vendor'] : array();
	
		$this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.main_view.html','Services/Payment');

		// add vendors
		ilRepositorySearchGUI::fillAutoCompleteToolbar(
			$this,
			$ilToolbar,
			array(
				'auto_complete_name'	=> $this->lng->txt('user'),
				'user_type'				=> null,
				'submit_name'			=> $this->lng->txt('add')
			));

		if(!count($vendors = $this->vendors_obj->getVendors()))
		{
			ilUtil::sendInfo($this->lng->txt('pay_no_vendors_created'));
		}

		if($a_show_confirm)
		{
			$oConfirmationGUI = new ilConfirmationGUI();
			
			// set confirm/cancel commands
			$oConfirmationGUI->setFormAction($this->ctrl->getFormAction($this,"performDeleteVendors"));
			$oConfirmationGUI->setHeaderText($this->lng->txt("pays_sure_delete_selected_vendors"));
			$oConfirmationGUI->setCancel($this->lng->txt("cancel"), "vendors");
			$oConfirmationGUI->setConfirm($this->lng->txt("confirm"), "performDeleteVendors");			
		
			foreach($vendors as $vendor)
			{
				if(in_array($vendor['vendor_id'],$_SESSION['pays_vendor']))
				{
					// GET USER OBJ
					if($tmp_obj = ilObjectFactory::getInstanceByObjId($vendor['vendor_id'],false))
					{
						$delete_row = '';
						$delete_row = $tmp_obj->getLogin().' '.
										$vendor['cost_center'].' '.
										ilPaymentBookings::_getCountBookingsByVendor($vendor['vendor_id']);
						$oConfirmationGUI->addItem('',$delete_row, $delete_row);		
						unset($tmp_obj);
					}
				}
			} // END VENDORS TABLE
			
			if(count($vendors) == count($_SESSION['pays_vendor']))
			{
				ilUtil::sendInfo($this->lng->txt('shop_disabled_no_vendors'));
				$_SESSION['disable_shop'] = true;
			}
			$this->tpl->setVariable('CONFIRMATION', $oConfirmationGUI->getHTML());	#
			return true;		
		}

		$counter = 0;
		$f_result = array();
		foreach($vendors as $vendor)
		{
			// GET USER OBJ
			if($tmp_obj = ilObjectFactory::getInstanceByObjId($vendor['vendor_id'],false))
			{
				$f_result[$counter]['vendor_id']	= ilUtil::formCheckbox(in_array($vendor['vendor_id'],
					$_SESSION['pays_vendor']) ? 1 : 0, "vendor[]", $vendor['vendor_id']);
				$f_result[$counter]['login']	= $tmp_obj->getLogin();
				$f_result[$counter]['cost_center']	= $vendor['cost_center'];
				$f_result[$counter]['number_bookings']	= ilPaymentBookings::_getCountBookingsByVendor($vendor['vendor_id']);
				
				unset($tmp_obj);
				++$counter;
			}
		} // END VENDORS TABLE
		$this->__showVendorsTable($f_result);

		return true;
	}

	public function exportVendorsObject()
	{
		include_once './Services/Payment/classes/class.ilPaymentExcelWriterAdapter.php';

		$pewa = new ilPaymentExcelWriterAdapter('payment_vendors.xls');

		// add/fill worksheet
		$this->addVendorWorksheet($pewa);
		$this->addStatisticWorksheet($pewa);

		// HEADER SENT
		
		$workbook = $pewa->getWorkbook();
		@$workbook->close();
	}

	public function addStatisticWorksheet(&$pewa)
	{
		include_once './Services/Excel/classes/class.ilExcelUtils.php';
		include_once './Services/Payment/classes/class.ilPaymentVendors.php';

		$this->__initBookingObject();

		$workbook = $pewa->getWorkbook();
		$worksheet = $workbook->addWorksheet(utf8_decode($this->lng->txt('bookings')));	
		
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

		$title = $this->lng->txt('bookings');
		$title .= ' '.$this->lng->txt('as_of').' ';
		$title .= strftime('%Y-%m-%d %R',time());

		$worksheet->writeString(0,0,$title,$pewa->getFormatTitle());

		$worksheet->writeString(1,0,$this->lng->txt('payment_system'),$pewa->getFormatHeader());
		$worksheet->writeString(1,1,$this->lng->txt('paya_transaction'),$pewa->getFormatHeader());
		$worksheet->writeString(1,2,$this->lng->txt('title'),$pewa->getFormatHeader());
		$worksheet->writeString(1,3,$this->lng->txt('paya_vendor'),$pewa->getFormatHeader());
		$worksheet->writeString(1,4,$this->lng->txt('pays_cost_center'),$pewa->getFormatHeader());
		$worksheet->writeString(1,5,$this->lng->txt('paya_customer'),$pewa->getFormatHeader());
		$worksheet->writeString(1,6,$this->lng->txt('email'),$pewa->getFormatHeader());
		$worksheet->writeString(1,7,$this->lng->txt('paya_order_date'),$pewa->getFormatHeader());
		$worksheet->writeString(1,8,$this->lng->txt('duration'),$pewa->getFormatHeader());
		$worksheet->writeString(1,9,$this->lng->txt('price_a'),$pewa->getFormatHeader());
		$worksheet->writeString(1,10,$this->lng->txt('paya_payed_access'),$pewa->getFormatHeader());
		
		$worksheet->writeString(1,11,$this->lng->txt('street'),$pewa->getFormatHeader());
		$worksheet->writeString(1,12,$this->lng->txt('pay_bmf_po_box'),$pewa->getFormatHeader());
		$worksheet->writeString(1,13,$this->lng->txt('zipcode'),$pewa->getFormatHeader());
		$worksheet->writeString(1,14,$this->lng->txt('city'),$pewa->getFormatHeader());
		$worksheet->writeString(1,15,$this->lng->txt('country'),$pewa->getFormatHeader());
		
		if(!count($bookings = $this->booking_obj->getBookings()))
		{
			return false;
		}

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
			
			include_once './Services/Payment/classes/class.ilPayMethods.php';
			$str_paymethod = ilPayMethods::getStringByPaymethod($booking['b_pay_method']);	
		
			$worksheet->writeString($counter,0,$str_paymethod);
			$worksheet->writeString($counter,1,$booking['transaction']);
			$worksheet->writeString($counter,2,($tmp_obj != '' ? $tmp_obj : $this->lng->txt('object_deleted')));
			$worksheet->writeString($counter,3,($tmp_vendor != '' ? $tmp_vendor : $this->lng->txt('user_deleted')));
			$worksheet->writeString($counter,4,ilPaymentVendors::_getCostCenter($booking['b_vendor_id']));
			$worksheet->writeString($counter,5,($tmp_purchaser != '' ? $tmp_purchaser : $this->lng->txt('user_deleted')));
			$worksheet->writeString($counter,6,$tmp_purchaser_email);
			$worksheet->writeString($counter,7,strftime('%Y-%m-%d %R',$booking['order_date']));
			$worksheet->writeString($counter,8,$booking['duration']);
			$worksheet->writeString($counter,9,$booking['price']);
			
			$payed_access = $booking['payed'] ? 
				$this->lng->txt('yes') : 
				$this->lng->txt('no');

			$payed_access .= '/';
			$payed_access .= $booking['access_granted'] ?
				$this->lng->txt('yes') : 
				$this->lng->txt('no');

			$worksheet->writeString($counter,10,$payed_access);

			$worksheet->writeString($counter,11,$booking['street']);
			$worksheet->writeString($counter,12,$booking['po_box']);
			$worksheet->writeString($counter,13,$booking['zipcode']);
			$worksheet->writeString($counter,14,$booking['city']);
			$worksheet->writeString($counter,15,$booking['country']);
			
			unset($tmp_obj);
			unset($tmp_vendor);
			unset($tmp_purchaser);

			++$counter;
		}
		return true;
	}		

	public function addVendorWorksheet($pewa)
	{
		include_once './Services/Excel/classes/class.ilExcelUtils.php';

		$workbook = $pewa->getWorkbook();
		$worksheet = $workbook->addWorksheet($this->lng->txt('pays_vendor'));

		// SHOW HEADER
		$worksheet->mergeCells(0,0,0,2);
		$worksheet->setColumn(1,0,32);
		$worksheet->setColumn(1,1,32);
		$worksheet->setColumn(1,2,32);

		$title = $this->lng->txt('pays_vendor');
		$title .= ' '.$this->lng->txt('as_of').' ';
		$title .= strftime('%Y-%m-%d %R',time());

		$worksheet->writeString(0,0,$title,$pewa->getFormatTitle());

		$worksheet->writeString(1,0,$this->lng->txt('login'),$pewa->getFormatHeader());
		$worksheet->writeString(1,1,$this->lng->txt('fullname'),$pewa->getFormatHeader());
		$worksheet->writeString(1,2,$this->lng->txt('pays_cost_center'),$pewa->getFormatHeader());

		if(!count($vendors = $this->vendors_obj->getVendors()))
		{
			return false;
		}

		$counter = 2;
		foreach($vendors as $vendor)
		{
			// GET USER OBJ
			if($tmp_obj = ilObjectFactory::getInstanceByObjId($vendor['vendor_id'],false))
			{
				$worksheet->writeString($counter,0,$tmp_obj->getLogin());
				$worksheet->writeString($counter,1,$tmp_obj->getFullname());
				$worksheet->writeString($counter,2,$vendor['cost_center']);
			}
			unset($tmp_obj);
			++$counter;
		}
		return true;
	}		
	
	public function payMethodsObject($askForDeletingAddresses = array())
	{
		include_once './Services/Payment/classes/class.ilPayMethods.php';

		global $rbacsystem, $ilCtrl;
		
		$this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.main_view.html','Services/Payment');
		
		if(!$rbacsystem->checkAccess('read', $this->object->getRefId()))
		{
			$this->ilErr->raiseError($this->lng->txt('msg_no_perm_read'),$this->ilErr->MESSAGE);
		}
	
		if(count($askForDeletingAddresses))
		{
			$oConfirmationGUI = new ilConfirmationGUI();
			
			// set confirm/cancel commands
			$oConfirmationGUI->setFormAction($ilCtrl->getFormAction($this, "deleteAddressesForPaymethods"));
			$oConfirmationGUI->setHeaderText($this->lng->txt("info_delete_sure"));
			$oConfirmationGUI->setCancel($this->lng->txt("cancel"), "payMethods");
			$oConfirmationGUI->setConfirm($this->lng->txt("confirm"), "deleteAddressesForPaymethods");
		
			foreach($askForDeletingAddresses as $pm_id)
			{
				$pm_obj = new ilPayMethods($pm_id);
				$oConfirmationGUI->additem('paymethod['.$pm_obj->getPmId().']',$pm_obj->getPmId(), $this->lng->txt('delete_addresses_bill').' -> '.ilPayMethods::getStringByPaymethod($pm_obj->getPmTitle()));
			}
			
			$this->tpl->setVariable('CONFIRMATION', $oConfirmationGUI->getHtml());
			return true;	
		}
		
		$obj_paymethods = new ilPayMethods();
		$paymethods = $obj_paymethods->readAll();

		$result = array();
		$counter = 0;
		foreach($paymethods as $paymethod)
		{
			if($paymethod['pm_title'] != 'epay')
			{	
				$result[$counter]['pm_title'] = ilPayMethods::getStringByPaymethod($paymethod['pm_title']);
				$result[$counter]['pm_enabled'] = ilUtil::formCheckbox($paymethod['pm_enabled'] ? 1 : 0,'pm_enabled['.$paymethod['pm_id'].']',1);
				$result[$counter]['save_usr_adr'] = ilUtil::formCheckbox($paymethod['save_usr_adr'] ? 1 : 0,'save_usr_adr['.$paymethod['pm_id'].']',1);
				$this->ctrl->clearParameters($this);
				$counter++;
			}
		}

		$this->ctrl->setParameter($this, 'cmd', 'savePayMethods');
		$tbl = new ilShopTableGUI($this);
		$tbl->setTitle($this->lng->txt('pays_pay_methods'));

		$tbl->setId('tbl_paymethods');
		$tbl->setRowTemplate("tpl.shop_paymethods_row.html", "Services/Payment");

		$tbl->addColumn($this->lng->txt('title'), 'pm_title', '10%');
		$tbl->addColumn($this->lng->txt('enabled'), 'pm_enabled', '10%');
		$tbl->addColumn($this->lng->txt('save_customer_address'),'save_usr_adr','10%');
		$tbl->addCommandButton('savePayMethods', $this->lng->txt('save'));

		$tbl->disable('sort');
		$tbl->setData($result);

		$this->tpl->setVariable('TABLE', $tbl->getHTML());

		return true;
	}

	public function savePayMethodsObject()
	{
		include_once './Services/Payment/classes/class.ilPayMethods.php';

		global $rbacsystem;
		
		if(!$rbacsystem->checkAccess('read', $this->object->getRefId()))
		{
			$this->ilErr->raiseError($this->lng->txt('msg_no_perm_read'),$this->ilErr->MESSAGE);
		}

		$obj_paymethods = new ilPayMethods();
		$paymethods = $obj_paymethods->readAll();
		
		$askForDeletingAddresses = array();

		$pm_enabled  = (array)$_POST['pm_enabled'];
		$pm_addr     = (array)$_POST['save_usr_adr'];

		if(!$pm_enabled)
		{
			ilUtil::sendInfo($this->lng->txt('shop_disabled_no_paymethods'));
			$this->genSetData->set('shop_enabled', 0, 'common');
			$_SESSION['disable_shop'] = false;
		}
		
		$info_msg    = array();
		foreach($paymethods as $paymethod)
		{
			if(
				(!isset($pm_enabled[$paymethod['pm_id']]) || !$pm_enabled[$paymethod['pm_id']]) &&
				$paymethod['pm_enabled']
			)
			{
				if(ilPaymentObject::_getCountObjectsByPayMethod($paymethod['pm_id']))
				{
					$info_msg[] = $this->lng->txt('pays_objects_'.$paymethod['pm_title'].'_exist');
				}
			}
		}
		if($info_msg)
		{
			ilUtil::sendInfo(implode('<br />', $info_msg));
			$this->payMethodsObject();
			return;
		}

		$num_changed = 0;
		foreach($paymethods as $paymethod)
		{
			if(
				(!isset($pm_enabled[$paymethod['pm_id']]) || !$pm_enabled[$paymethod['pm_id']]) &&
				$paymethod['pm_enabled']
			)
			{
				ilPayMethods::_PMdisable($paymethod['pm_id']);
				++$num_changed;
			}
			else if(isset($pm_enabled[$paymethod['pm_id']]) && $pm_enabled[$paymethod['pm_id']] && !$paymethod['pm_enabled'])
			{
				ilPayMethods::_PMenable($paymethod['pm_id']);
				++$num_changed;
			}

			if(
				(!isset($pm_addr[$paymethod['pm_id']]) || !$pm_addr[$paymethod['pm_id']]) &&
				$paymethod['save_usr_adr'])
			{
				$askForDeletingAddresses[] = $paymethod['pm_id'];
			}
			else if(
				(!isset($pm_addr[$paymethod['pm_id']]) || !$pm_addr[$paymethod['pm_id']]) &&
				!$paymethod['save_usr_adr']
			)
			{
				continue;
			}
			else
			{ 
				ilPayMethods::_enableSaveUserAddress($paymethod['pm_id']);
				++$num_changed;
			}
		}
		if($num_changed && !$askForDeletingAddresses)
		{
			ilUtil::sendSuccess($this->lng->txt('pays_updated_pay_method'));
		}
		$this->payMethodsObject($askForDeletingAddresses);

		return true;
	}

	public function cancelDeleteVendorsObject()
	{
		unset($_SESSION['pays_vendor']);
		$_SESSION['disable_shop'] = false;
		$this->vendorsObject();

		return true;
	}

	public function deleteVendorsObject()
	{
		//include_once './Services/Payment/classes/class.ilPaymentBookings.php';

		if(!count($_POST['vendor']))
		{
			ilUtil::sendFailure($this->lng->txt('pays_no_vendor_selected'));
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
		ilUtil::sendQuestion($this->lng->txt('pays_sure_delete_selected_vendors'));
		$this->vendorsObject(true);

		return true;
	}
	
	public function performDeleteVendorsObject()
	{
		include_once './Services/Payment/classes/class.ilPaymentTrustees.php';
		
		global $rbacsystem;

		// MINIMUM ACCESS LEVEL = 'read'
		if(!$rbacsystem->checkAccess('write', $this->object->getRefId()))
		{
			$this->ilErr->raiseError($this->lng->txt('msg_no_perm_write'),$this->ilErr->MESSAGE);
		}

		foreach($_SESSION['pays_vendor'] as $vendor)
		{
			$this->vendors_obj->delete($vendor);
			ilPaymentTrustees::_deleteTrusteesOfVendor($vendor);
		}

		ilUtil::sendInfo($this->lng->txt('pays_deleted_number_vendors').' '.count($_SESSION['pays_vendor']));
		unset($_SESSION['pays_vendor']);
		if($_SESSION['disable_shop'] == true)
		{
			$this->genSetData->set('shop_enabled', 0, 'common');
			$_SESSION['disable_shop'] = false;
		}
		
		$this->vendorsObject();

		return true;
	}

	public function editVendorObject()
	{
		global $rbacsystem;

		// MINIMUM ACCESS LEVEL = 'read'
		if(!$rbacsystem->checkAccess("write", $this->object->getRefId()))
		{
			$this->ilErr->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilErr->MESSAGE);
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

		if (!is_array($this->vendors_obj->vendors[$_SESSION['pays_vendor']]))
		{
			$this->vendorsObject();

			return true;
		}
	
		$this->tpl->addBlockfile('ADM_CONTENT','adm_content','tpl.main_view.html','Services/Payment');	
		
		$form_gui = new ilPropertyFormGUI();
		$form_gui->setFormAction($this->ctrl->getFormAction($this, 'performEditVendor'));
		$form_gui->setTitle($this->lng->txt('pays_vendor'));

		$oVendorGUI = new ilNonEditableValueGUI($this->lng->txt('pays_vendor'));		
		$oVendorGUI->setValue(ilObjUser::getLoginByUserId($this->vendors_obj->vendors[$_SESSION['pays_vendor']]['vendor_id']), true);	
		$form_gui->addItem($oVendorGUI);	
		
		$oCostcenterGUI = new ilTextInputGUI($this->lng->txt('pays_cost_center'),'cost_center');
		$oCostcenterGUI->setValue($this->error != '' && isset($_POST['cost_center'])
								? ilUtil::prepareFormOutput($_POST['cost_center'],true)
								: ilUtil::prepareFormOutput($this->vendors_obj->vendors[$_SESSION['pays_vendor']]['cost_center'],true));
		$form_gui->addItem($oCostcenterGUI);	

		$form_gui->addCommandButton('performEditVendor',$this->lng->txt('save'));
		$this->tpl->setVariable('FORM', $form_gui->getHTML());
		return true;
	}
	
	public function performEditVendorObject()
	{
		global $rbacsystem;

		// MINIMUM ACCESS LEVEL = 'read'
		if(!$rbacsystem->checkAccess('write', $this->object->getRefId()))
		{
			$this->ilErr->raiseError($this->lng->txt('msg_no_perm_write'),$this->ilErr->MESSAGE);
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

		if (!is_array($this->vendors_obj->vendors[$_SESSION['pays_vendor']]))
		{
			$this->vendorsObject();
			return true;
		}

		if ($_POST['cost_center'] == '')
		{
			$this->error = $this->lng->txt('pays_cost_center_not_valid');
			ilUtil::sendFailure($this->error);
			$_POST['vendor'] = array($_SESSION['pays_vendor']);
			return $this->editVendor();
			
		}

		$this->vendors_obj->update($_SESSION['pays_vendor'], $_POST['cost_center']);

		unset($_SESSION['pays_vendor']);

		$this->vendorsObject();

		return true;
	}

	public function showObjectSelectorObject()
	{
		global $rbacsystem, $ilToolbar;

		// MINIMUM ACCESS LEVEL = 'read'
		if(!$rbacsystem->checkAccess('read', $this->object->getRefId()))
		{
			$this->ilErr->raiseError($this->lng->txt('msg_no_perm_read'),$this->ilErr->MESSAGE);
		}
		
		$ilToolbar->addButton($this->lng->txt('back'), $this->ctrl->getLinkTarget($this, 'statistic'));

		ilUtil::sendInfo($this->lng->txt('paya_select_object_to_sell'));

		include_once("./Services/Payment/classes/class.ilPaymentObjectSelector.php");
		$exp = new ilPaymentObjectSelector($this, "showObjectSelector");
		if (!$exp->handleCommand())
		{
			$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.paya_object_selector.html",'Services/Payment');
			$this->tpl->setVariable("EXPLORER",$exp->getHTML());
		}

		return true;
	}

	public function searchObject()
	{
		global $rbacsystem;

		if(!$rbacsystem->checkAccess('read', $this->object->getRefId()))
		{
			$this->ilErr->raiseError($this->lng->txt('msg_no_perm_read'),$this->ilErr->MESSAGE);
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

		$this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.main_view.html','Services/Payment');
		
		$counter = 0;
		$f_result = array();
		foreach($result as $user)
		{
			if(!$tmp_obj = ilObjectFactory::getInstanceByObjId($user['id'],false))
			{
				continue;
			}
			$f_result[$counter]['vendor_id'] = ilUtil::formCheckbox(0,'user[]',$user['id']);
			$f_result[$counter]['login'] = $tmp_obj->getLogin();
			$f_result[$counter]['lastname'] = $tmp_obj->getLastname();
			$f_result[$counter]['firstname'] = $tmp_obj->getFirstname();

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
			$this->ilErr->raiseError($this->lng->txt('msg_no_perm_write'),$this->ilErr->MESSAGE);
		}
		if(!$_POST['user_login'])
		{
			ilUtil::sendFailure($this->lng->txt('pays_no_username_given'));
			$this->vendorsObject();

			return true;
		}
		if(!($usr_id = ilObjUser::getUserIdByLogin(ilUtil::stripSlashes($_POST['user_login']))))
		{
			ilUtil::sendFailure($this->lng->txt('pays_no_valid_username_given'));
			$this->vendorsObject();

			return true;
		}
		
		if($this->vendors_obj->isAssigned($usr_id))
		{
			ilUtil::sendFailure($this->lng->txt('pays_user_already_assigned'));
			$this->vendorsObject();

			return true;
		}
		$this->vendors_obj->add($usr_id);

		ilUtil::sendSuccess($this->lng->txt('pays_added_vendor'));
		$this->vendorsObject();
		
		return true;
	}
		
	public function addUserObject()
	{
		global $rbacsystem;

		// MINIMUM ACCESS LEVEL = 'administrate'
		if(!$rbacsystem->checkAccess('write', $this->object->getRefId()))
		{
			$this->ilErr->raiseError($this->lng->txt('msg_no_perm_write'),$this->ilErr->MESSAGE);
		}

		$this->lng->loadLanguageModule('crs');
		if(!is_array($_POST['user']))
		{
			ilUtil::sendFailure($this->lng->txt('crs_no_users_selected'));
			$this->searchObject();

			return false;
		}
		
		$already_assigned = $assigned = 0;
		foreach($_POST['user'] as $usr_id)
		{
			if($this->vendors_obj->isAssigned($usr_id))
			{
				++$already_assigned;
				continue;
			}
			$this->vendors_obj->add($usr_id);
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

	public function searchUserSPObject()
	{
		global $ilToolbar;
		
		if(!isset($_GET['sell_id']))
		{
			ilUtil::sendFailiure($this->lng->txt('paya_no_booking_id_given'));
			$this->showObjectSelectorObject();

			return false;
		}

		$ilToolbar->addButton($this->lng->txt('back'), $this->ctrl->getLinkTarget($this, 'showObjectSelector'));
		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.main_view.html','Services/Payment');
		
		$this->lng->loadLanguageModule('search');
		$this->ctrl->setParameter($this, 'sell_id', $_GET['sell_id']);

		$form_gui = new ilPropertyFormGUI();
		$form_gui->setFormAction($this->ctrl->getFormAction($this));
		$form_gui->setTitle($this->lng->txt('search_user'));
		$form_gui->setId('search_form');
	
		$oTitle = new ilTextInputGUI($this->lng->txt('search_search_term'), 'search_str');
		$oTitle->setMaxLength(255);
		$oTitle->setSize(40);
		$oTitle->setValue($_POST['search_str']);
		$form_gui->addItem($oTitle);		
		
		// buttons
		$form_gui->addCommandButton('performSearchSP', $this->lng->txt('search'));
		$form_gui->addCommandButton('bookings', $this->lng->txt('cancel'));		
		
		$this->tpl->setVariable('FORM',$form_gui->getHTML());	
		return true;
	}

	public function performSearchSPObject()
	{
		global $ilToolbar;
		// SAVE it to allow sort in tables
		$_SESSION['paya_search_str_user_sp'] = $_POST['search_str'] = $_POST['search_str'] ? $_POST['search_str'] : $_SESSION['paya_search_str_user_sp'];

		if(!trim($_POST['search_str']))
		{
			ilUtil::sendFailure($this->lng->txt('search_no_search_term'));
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
	
		$this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.main_view.html','Services/Payment');
		$this->ctrl->setParameter($this, 'sell_id', $_GET['sell_id']);

		$ilToolbar->addButton($this->lng->txt('back'), $this->ctrl->getLinkTarget($this, 'searchUserSP'));
		
		$counter = 0;
		$f_result = array();
		foreach($result as $user)
		{
			if(!$tmp_obj = ilObjectFactory::getInstanceByObjId($user['id'],false))
			{
				continue;
			}
			$f_result[$counter]['user_id'] = $user['id'];
			$f_result[$counter]['login'] = $tmp_obj->getLogin();
			$f_result[$counter]['firstname'] = $tmp_obj->getFirstname();
			$f_result[$counter]['lastname'] = $tmp_obj->getLastname();
			
			unset($tmp_obj);
			++$counter;
		}
		return $this->__showSearchUserSPTable($f_result);
	}

	public function addCustomerObject()
	{
		global $ilToolbar;
		
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
		$this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.main_view.html','Services/Payment');
		$this->ctrl->setParameter($this, 'sell_id', $_GET['sell_id']);

		$ilToolbar->addButton($this->lng->txt('back'), $this->ctrl->getLinkTarget($this, 'searchUserSP'));

		$this->ctrl->setParameter($this, 'user_id', $_POST['user_id']);

		$pObjectId = ilPaymentObject::_lookupPobjectId($_GET['sell_id']);
		$obj = new ilPaymentObject($this->user_obj, $pObjectId);

		// get obj
		$tmp_obj = ilObjectFactory::getInstanceByRefId($_GET['sell_id'], false);
		if($tmp_obj)
		{
			$tmp_object['title'] = $tmp_obj->getTitle();
		}
		else
		{
			$tmp_object['title'] = $this->lng->txt('object_not_found');
		}
		// get customer_obj
		$tmp_user = ilObjectFactory::getInstanceByObjId($_POST['user_id']);
		// get vendor_obj
		$tmp_vendor = ilObjectFactory::getInstanceByObjId($obj->getVendorId());
/**/
		$oForm = new ilPropertyFormGUI();
		$oForm->setFormAction($this->ctrl->getFormAction($this, 'saveCustomer'));
		$oForm->setTitle($this->lng->txt($tmp_user->getFullname().' ['.$tmp_user->getLogin().']'));
		
		//transaction
		$oTransaction = new ilTextInputGUI();
		$oTransaction->setTitle($this->lng->txt('paya_transaction'));
		//$oTransaction->setValue(ilUtil::prepareFormOutut($_POST['transaction'], true));
		$oTransaction->setValue($_POST['transaction']);
		$oTransaction->setPostVar('transaction');
		$oForm->addItem($oTransaction);
		
		//object
		$oObject = new ilNonEditableValueGUI($this->lng->txt('title'));
		$oObject->setValue($tmp_obj->getTitle());
		$oForm->addItem($oObject);
		
		//vendor
		$oVendor = new ilNonEditableValueGUI($this->lng->txt('paya_vendor'));
		$oVendor->setValue($tmp_vendor->getFullname().' ['.$tmp_vendor->getLogin().']');
		$oForm->addItem($oVendor);
		
		// pay methods
		$oPayMethods = new ilSelectInputGUI($this->lng->txt('paya_pay_method'), 'pay_method');
		$payOptions = ilPaymethods::getPayMethodsOptions(false);
		$oPayMethods->setOptions($payOptions);
		$oPayMethods->setValue($_POST['pay_method']);
		$oPayMethods->setPostVar('pay_method');
		$oForm->addItem($oPayMethods);	
		
		//duration
		$duration_options = array();	
		$price_obj = new ilPaymentPrices($pObjectId);
		
		$standard_prices = array();
		$extension_prices = array();
		$standard_prices = $price_obj->getPrices();
		$extension_prices = $price_obj->getExtensionPrices();

		$prices = array_merge($standard_prices, $extension_prices );

		if (is_array($prices))
		{
			$genSet = ilPaymentSettings::_getInstance();
			$currency_unit = $genSet->get('currency_unit');

			foreach($prices as $price)
			{
				switch($price['price_type'])
				{
					case ilPaymentPrices::TYPE_DURATION_MONTH:
						$txt_duration =
						$price['duration'].' '.$this->lng->txt('paya_months').' -> '.$price['price'].' '. $currency_unit;
						break;
					
					case ilPaymentPrices::TYPE_DURATION_DATE:
						include_once './Services/Calendar/classes/class.ilDatePresentation.php';
						$txt_duration = ilDatePresentation::formatDate(new ilDate($price['duration_from'], IL_CAL_DATE))
							.' - '.ilDatePresentation::formatDate(new ilDate($price['duration_until'], IL_CAL_DATE))
							." -> ".ilPaymentPrices::_getPriceString($price["price_id"]) .' '.$currency_unit;
						break;
					
					case ilPaymentPrices::TYPE_UNLIMITED_DURATION:
						$txt_duration =  $this->lng->txt('unlimited_duration').' -> '.$price['price'].' '. $currency_unit;
						break;
				}
				$txt_extension = '';
				if($price['extension'] == 1)
				{
					$txt_extension = ' ('.$this->lng->txt('extension_price').') ';
				}
				$duration_options[$price['price_id']] .= $txt_duration.''.$txt_extension;
			}
		}

		$oDuration = new ilSelectInputGUI($this->lng->txt('duration'), 'price_id');
		$oDuration->setOptions($duration_options);
		$oDuration->setValue($_POST['price_id']);
		$oForm->addItem($oDuration);	

		//payed		
		$o_payed = new ilSelectInputGUI();
		$payed_option = array('1'=>$this->lng->txt('yes'),'0'=>$this->lng->txt('no'));

		$o_payed->setTitle($this->lng->txt('paya_payed'));
		$o_payed->setOptions($payed_option);
		$o_payed->setValue($_POST['payed']);
		$o_payed->setPostVar('payed');		
		$oForm->addItem($o_payed);	


		$o_access = new ilSelectInputGUI();
		$access_option = array('1'=>$this->lng->txt('yes'),'0'=>$this->lng->txt('no'));

		$o_access->setTitle($this->lng->txt('paya_access'));
		$o_access->setOptions($access_option);
		$o_access->setValue($_POST['access']);
		$o_access->setPostVar('access');
		
		$oForm->addItem($o_access);	

		$oForm->addCommandButton('saveCustomer',$this->lng->txt('save'));
		$oForm->addCommandButton('bookings', $this->lng->txt('cancel'));	
		
		$this->tpl->setVariable('FORM',$oForm->getHTML());
		return true;
	}

	public function saveCustomerObject()
	{
		global $ilUser,$ilObjDataCache;

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
			$_POST['price_id'] == '')
		{
			ilUtil::sendInfo($this->lng->txt('paya_error_mandatory_fields'));
			$this->addCustomerObject();

			return true;
		}

		$pObjectId = ilPaymentObject::_lookupPobjectId($_GET['sell_id']);
		$obj = new ilPaymentObject($this->user_obj, $pObjectId);

		$this->__initBookingObject();

		$transaction = ilInvoiceNumberPlaceholdersPropertyGUI::_generateInvoiceNumber($ilUser->getId());

		$this->booking_obj->setTransaction($transaction);
		$this->booking_obj->setTransactionExtern($_POST['transaction']);
		$this->booking_obj->setPobjectId($pObjectId);
		$this->booking_obj->setCustomerId($_GET['user_id']);
		$this->booking_obj->setVendorId($obj->getVendorId());

		$this->booking_obj->setPayMethod($_POST['pay_method']); 
		$this->booking_obj->setOrderDate(time());

		$price = ilPaymentPrices::_getPrice($_POST['price_id']);
//		$currency = ilPaymentCurrency::_getUnit($price['currency']);
		
#@todo check this.		
		switch($price['price_type'])
		{
			case ilPaymentPrices::TYPE_DURATION_MONTH:
				$this->booking_obj->setDuration($price['duration']);
				break;
			
			case ilPaymentPrices::TYPE_DURATION_DATE:
				$this->booking_obj->setDuration(0);
				$this->booking_obj->setAccessStartdate($price['duration_from']);
				$this->booking_obj->setAccessEnddate($price['duration_until']);
				break;
			case ilPaymentPrices::TYPE_UNLIMITED_DURATION:
				$this->booking_obj->setDuration(0);
				$this->booking_obj->setAccessEnddate(NULL);
				break;
		}

		$this->booking_obj->setPriceType($price['price_type']);
		$this->booking_obj->setPrice($price['price']);
		
		$this->booking_obj->setAccess((int) $_POST['access']);
		$this->booking_obj->setPayed((int) $_POST['payed']);
		$this->booking_obj->setVoucher('');
		
		$obj_id = $ilObjDataCache->lookupObjId($obj->getRefId());
		$obj_type = $ilObjDataCache->lookupType($obj_id);
		$obj_title = $ilObjDataCache->lookupTitle($obj_id);

	//	include_once 'Services/Payment/classes/class.ilShopVatsList.php';
		$oVAT = new ilShopVats((int)$obj->getVatId());
		$obj_vat_rate = $oVAT->getRate();
		$obj_vat_unit = $obj->getVat($this->booking_obj->getPrice());
	
		$this->booking_obj->setObjectTitle($obj_title);
		$this->booking_obj->setVatRate($obj_vat_rate);
		$this->booking_obj->setVatUnit($obj_vat_unit);

		$genSet =ilPaymentSettings::_getInstance();
		$this->booking_obj->setCurrencyUnit( $genSet->get('currency_unit'));

		include_once './Services/Payment/classes/class.ilPayMethods.php';

		$save_user_address_enabled = ilPayMethods::_EnabledSaveUserAddress($this->booking_obj->getPayMethod());
		if($save_user_address_enabled == 1)
		{
			global $ilObjUser;
			$user_id[] = $_GET["user_id"];
		
			$cust_obj = ilObjUser::_readUsersProfileData($user_id);
		
			$this->booking_obj->setStreet($cust_obj[$_GET["user_id"]]['street'],'');
			
			$this->booking_obj->setZipcode($cust_obj[$_GET["user_id"]]['zipcode']);
			$this->booking_obj->setCity($cust_obj[$_GET["user_id"]]['city']);
			$this->booking_obj->setCountry($cust_obj[$_GET["user_id"]]['country']);
		}			
		
		if($this->booking_obj->add())
		{
			// add purchased item to desktop
			ilShopUtils::_addPurchasedObjToDesktop($obj, $this->booking_obj->getCustomerId());

            // autosubscribe user if purchased object is a course
            if($obj_type == 'crs')
            {
                ilShopUtils::_assignPurchasedCourseMemberRole($obj, $this->booking_obj->getCustomerId());
			}

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
	private function __showStatisticTable($a_result_set)
	{
		$this->ctrl->setParameter($this, 'cmd', 'statistic');

		$tbl = new ilShopTableGUI($this);
		$tbl->setTitle($this->lng->txt("bookings"));
		$tbl->setId('tbl_show_statistics');
		$tbl->setRowTemplate("tpl.shop_statistics_row.html", "Services/Payment");

		$tbl->addColumn($this->lng->txt('paya_transaction'), 'transaction', '10%');
		$tbl->addColumn($this->lng->txt('title'), 'object_title', '10%');
		$tbl->addColumn($this->lng->txt('paya_vendor'), 'vendor', '10%');
		$tbl->addColumn($this->lng->txt('paya_customer'), 'customer', '10%');
		$tbl->addColumn($this->lng->txt('paya_order_date'), 'order_date', '10%');
		$tbl->addColumn($this->lng->txt('duration'), 'duration', '20%');
		$tbl->addColumn($this->lng->txt('price_a'), 'price', '5%');
		$tbl->addColumn($this->lng->txt('paya_coupons_coupon'), 'discount', '5%');
		$tbl->addColumn($this->lng->txt('paya_payed_access'), 'payed_access', '1%');
		$tbl->addColumn('','edit', '5%');

		$tbl->addCommandButton('exportVendors',$this->lng->txt('excel_export'));
		$tbl->setData($a_result_set);

		$this->tpl->setVariable('TABLE', $tbl->getHTML());

		return true;
	}

	private function __initBookingObject()
	{
		include_once './Services/Payment/classes/class.ilPaymentBookings.php';

		$this->booking_obj = new ilPaymentBookings($this->user_obj->getId(),true);
	}

	private function __showVendorsTable($a_result_set)
	{
		$this->ctrl->setParameter($this, 'cmd', 'vendors');

		$tbl = new ilShopTableGUI($this);
		$tbl->setTitle($this->lng->txt("vendors"));
		$tbl->setId('tbl_show_vendors');
		$tbl->setRowTemplate("tpl.shop_users_row.html", "Services/Payment");

		$tbl->addColumn('', 'vendor_id', '1%');
		$tbl->addColumn($this->lng->txt('paya_vendor'), 'login', '10%');
		$tbl->addColumn($this->lng->txt('pays_cost_center'), 'cost_center', '10%');
		$tbl->addColumn($this->lng->txt('pays_number_bookings'), 'number_bookings', '10%');

		$tbl->addMultiCommand("editVendor", $this->lng->txt('pays_edit_vendor'));
		$tbl->addMultiCommand("deleteVendors", $this->lng->txt('pays_delete_vendor'));

		$tbl->addCommandButton('exportVendors',$this->lng->txt('excel_export'));

		$tbl->setData($a_result_set);
		$this->tpl->setVariable('TABLE', $tbl->getHTML());

		return true;
	}

	private function __showSearchUserTable($a_result_set)
	{
		$tbl = new ilShopTableGUI($this);

		$tbl->setTitle($this->lng->txt("pays_header_select_vendor"));
		$tbl->setId('tbl_search_user_vendor');
		$tbl->setRowTemplate("tpl.shop_users_row.html", "Services/Payment");

		$tbl->addColumn(' ', 'vendor_id', '3%', true);
		$tbl->addColumn($this->lng->txt('login'), 'login', '32%');
		$tbl->addColumn($this->lng->txt('firstname'),'firstname','32%');
		$tbl->addColumn($this->lng->txt('lastname'), 'lastname', '32%');

		$tbl->setSelectAllCheckbox('vendor_id');
		$tbl->addMultiCommand("addUser", $this->lng->txt("add"));
		$tbl->addCommandButton('vendors',$this->lng->txt('cancel'));

		$tbl->fillFooter();
		$tbl->setData($a_result_set);
		$this->tpl->setVariable('TABLE', $tbl->getHTML());

		return true;
	}

	private function __search($a_search_string)
	{
		include_once('./Services/Search/classes/class.ilSearch.php');

		$this->lng->loadLanguageModule('content');

		$search = new ilSearch($_SESSION['AccountId']);
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
	
	private function __searchSP($a_search_string)
	{
		include_once('./Services/Search/classes/class.ilSearch.php');

		$this->lng->loadLanguageModule('content');

		$search = new ilSearch($this->user_obj->getId());
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
	private function __showSearchUserSPTable($a_result_set)
	{
		$this->ctrl->setParameter($this, 'sell_id', $_GET['sell_id']);
		$tbl = new ilShopTableGUI($this);

		$tbl->setTitle($this->lng->txt('users'));
		$tbl->setId('tbl_search_user_vendor');
		$tbl->setRowTemplate("tpl.shop_users_row.html", "Services/Payment");

		$tbl->addColumn(' ', 'user_id', '3%', true);
		$tbl->addColumn($this->lng->txt('login'), 'login', '32%');
		$tbl->addColumn($this->lng->txt('firstname'),'firstname','32%');
		$tbl->addColumn($this->lng->txt('lastname'), 'lastname', '32%');


		$tbl->addMultiCommand("addCustomer", $this->lng->txt("add"));
		$tbl->addCommandButton('statistic',$this->lng->txt('cancel'));

		$tbl->fillFooter();
		$tbl->setData($a_result_set);
		$this->tpl->setVariable('TABLE', $tbl->getHTML());
		return true;
	}

	public function vatsObject()
	{
		global $ilAccess;

		if(!$ilAccess->checkAccess('read', '', $this->object->getRefId()))
		{
			$this->ilErr->raiseError($this->lng->txt('msg_no_perm_read'), $this->ilErr->MESSAGE);
		}
		
		include_once 'Services/Table/classes/class.ilTable2GUI.php';
		$tbl = new ilTable2GUI($this, 'vats');
		$tbl->setId('pay_vats_tbl');
		$tbl->setFormAction($this->ctrl->getFormAction($this), 'createVat');
		$tbl->setTitle($this->lng->txt('payment_tax_rates'));
		$tbl->setRowTemplate('tpl.shop_vats_list_row.html', 'Services/Payment');				

	 	$tbl->setDefaultOrderField('title');	
		
		$tbl->addColumn('', 'check', '1%');
	 	$tbl->addColumn($this->lng->txt('vat_title'), 'vat_title', '33%');
	 	$tbl->addColumn($this->lng->txt('vat_rate'), 'vat_rate', '33%');
		$tbl->addColumn('', 'commands', '33%');		
		
		$oShopVatsList = new ilShopVatsList();
		$oShopVatsList->read();		
		
		$result = array();
		
		$_SESSION['count_vats'] = $oShopVatsList->getNumItems();
		
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

		if($_SESSION['count_vats'] == count($_POST['vat_id']))
		{
			ilUtil::sendInfo($this->lng->txt('shop_disabled_no_vats'));
			$_SESSION['disable_shop'] = true;
		}
		
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
			$this->tpl->setContent($c_gui->getHTML());	
			return true;
		}
		else
		{
			$this->vatsObject();
			return true;
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
				$_SESSION['disable_shop'] = false;
				return $this->vatsObject();				
			}
		}
		
		if($_SESSION['disable_shop'] == true)
		{
			$this->genSetData->set('shop_enabled', 0, 'common');
			$_SESSION['disable_shop'] = false;
		}
		ilUtil::sendSuccess($this->lng->txt('payment_vat_deleted_successfully'));		
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
			$this->tpl->setContent($this->form->getHtml());
			return true;
		}
		
		if(!ilShopUtils::_checkVATRate($this->form->getInput('vat_rate')))
		{
			$this->form->getItemByPostVar('vat_rate')->setAlert($this->lng->txt('payment_vat_input_invalid'));
			$this->form->setValuesByPost();
			$this->tpl->setContent($this->form->getHtml());
			return true;
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
			$this->tpl->setContent($this->form->getHtml());
			return true;
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
			$this->tpl->setContent($this->form->getHtml());
			return true;
		}
		
		if(!ilShopUtils::_checkVATRate($this->form->getInput('vat_rate')))
		{
			$this->form->getItemByPostVar('vat_rate')->setAlert($this->lng->txt('payment_vat_input_invalid'));
			$this->form->setValuesByPost();
			$this->tpl->setContent($this->form->getHtml());
			return true;
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
			$this->tpl->setContent($this->form->getHtml());
			return true;
			
		}
		
		ilUtil::sendInfo($this->lng->txt('saved'));
		$this->vatsObject();
		return true;
				
	}	

	public function deleteAddressesForPaymethodsObject()
	{
		// delete addresses here
		include_once './Services/Payment/classes/class.ilPayMethods.php';
		$this->__initBookingObject();	
		
		foreach($_POST['paymethod'] as $pay_method)
		{
			ilPayMethods::_disableSaveUserAddress($pay_method);
			$del_bookings = $this->booking_obj->deleteAddressesByPaymethod((int)$pay_method);
		}	
		ilUtil::sendSuccess($this->lng->txt('pays_updated_pay_method'));
		return $this->payMethodsObject();
	}	

	// show currencies
	public function currenciesObject()
	{
		$this->tpl->addBlockfile('ADM_CONTENT','adm_content','tpl.main_view.html','Services/Payment');
		$currency_res = ilPaymentCurrency::_getAvailableCurrencies();
		// currency table
		 $counter = 0;
		 foreach($currency_res as $cur)
		 {
			$f_result[$counter]['currency_id'] = ilUtil::formRadioButton(0,'currency_id',$cur['currency_id']);
			$f_result[$counter]['is_default'] = $cur['is_default'] == 1 ? $this->lng->txt('yes') :  $this->lng->txt('no');

			$f_result[$counter]['currency_unit'] = $cur['unit'];
			$f_result[$counter]['iso_code'] = $cur['iso_code'];
			$f_result[$counter]['currency_symbol'] = $cur['symbol'];
			$f_result[$counter]['conversion_rate'] = $cur['conversion_rate'];

			$counter++;
		 }

		$tbl = new ilShopTableGUI($this);

		$tbl->setTitle($this->lng->txt("currencies"));
		$tbl->setId('tbl_show_currencies');
		$tbl->setRowTemplate("tpl.shop_currencies_row.html", "Services/Payment");

		$tbl->addColumn(' ', 'currency_id', '1%', true);
		$tbl->addColumn($this->lng->txt('is_default'), 'is_default', '5%');
		$tbl->addColumn($this->lng->txt('currency_unit'), 'currency_unit', '10%');
		$tbl->addColumn($this->lng->txt('iso_code'),'iso_code','20%');
		$tbl->addColumn($this->lng->txt('currency_symbol'), 'currency_symbol', '20%');
		$tbl->addColumn($this->lng->txt('conversion_rate'), 'conversion_rate', '15%');
		$tbl->addColumn('', 'options', '5%');

		$this->ctrl->setParameter($this, 'cmd', 'currencies');

		$tbl->addMultiCommand('updateDefaultCurrency', $this->lng->txt('paya_set_default_currency'));
		$tbl->addMultiCommand("editCurrency",$this->lng->txt('edit'));
		$tbl->addMultiCommand("deleteCurrency", $this->lng->txt('delete'));

		$tbl->addCommandButton('addCurrency',$this->lng->txt('add_currency'));
		$tbl->setData($f_result);
		$this->tpl->setVariable('TABLE', $tbl->getHTML());
		return true;
	}
	public function updateDefaultCurrencyObject()
	{
		if(isset($_POST['currency_id'] ))
		{
			ilPaymentCurrency::_updateIsDefault($_POST['currency_id']);
		}
		else ilUtil::sendFailure($this->lng->txt('please_select_currency'));
		
		$this->currenciesObject();
	}
	
	public function addCurrencyObject()
	{
		$this->tpl->addBlockfile('ADM_CONTENT','adm_content','tpl.main_view.html','Services/Payment');	
		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this));
		$form->setTitle($this->lng->txt('paya_add_currency'));
		
		$o_Unit = new ilTextInputGUI($this->lng->txt('paya_currency_unit'),'currency_unit');
		$o_Unit->setValue($_POST['currency_unit']);
		$o_Unit->setPostVar('currency_unit');
		$o_Unit->setRequired(true);

		$o_Isocode = new ilTextInputGUI($this->lng->txt('iso_code'),'iso_code');
		$o_Isocode->setValue($_POST['iso_code']);
		$o_Isocode->setPostVar('iso_code');
		$o_Isocode->setRequired(true);
		
		$o_Symbol = new ilTextInputGUI($this->lng->txt('symbol'), 'symbol');
		$o_Symbol->setValue($_POST['symbol']);
		$o_Symbol->setPostVar('symbol');
		$o_Symbol->setRequired(true);
		
		$o_Conversionrate = new IlTextInputGUI($this->lng->txt('conversion_rate'), 'conversion_rate');
		$o_Conversionrate->setValue($_POST['conversion_rate']);
		$o_Conversionrate->setPostVar('conversion_rate');
		$o_Conversionrate->setRequired(true);
		
		$form->addItem($o_Unit);
		$form->addItem($o_Isocode);
		$form->addItem($o_Symbol);
		$form->addItem($o_Conversionrate);

		$form->addCommandButton('saveCurrency', $this->lng->txt('save'));	
		$form->addCommandButton('currencies', $this->lng->txt('cancel'));	
		
		$this->tpl->setVariable('FORM', $form->getHTML());
	}
	
	public function saveCurrencyObject()
	{
		$obj_currency = new ilPaymentCurrency();
		$obj_currency->setUnit($_POST['currency_unit']);
		$obj_currency->setIsoCode($_POST['iso_code']);
		$obj_currency->setSymbol($_POST['symbol']);
		$obj_currency->setConversionRate($_POST['conversion_rate']);
		$obj_currency->addCurrency();
		$this->currenciesObject();
	}
	
	public function editCurrencyObject()
	{
		$currency_id = $_POST['currency_id'];
		$obj_currency = ilPaymentCurrency::_getCurrency($currency_id);
	
		$this->tpl->addBlockfile('ADM_CONTENT','adm_content','tpl.main_view.html','Services/Payment');	
		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this));
		$form->setTitle($this->lng->txt('paya_edit_currency'));
		
		$o_Unit = new ilTextInputGUI($this->lng->txt('paya_currency_unit'),'currency_unit');
		$o_Unit->setValue($obj_currency[$currency_id]['unit']);
		$o_Unit->setPostVar('currency_unit');
		$o_Unit->setRequired(true);
		
		$o_Isocode = new ilTextInputGUI($this->lng->txt('iso_code'),'iso_code');
		$o_Isocode->setValue($obj_currency[$currency_id]['iso_code']);
		$o_Isocode->setPostVar('iso_code');
		$o_Isocode->setRequired(true);
		
		$o_Symbol = new ilTextInputGUI($this->lng->txt('symbol'), 'symbol');
		$o_Symbol->setValue($obj_currency[$currency_id]['symbol']);
		$o_Symbol->setPostVar('symbol');
		$o_Symbol->setRequired(true);
		
		$o_Conversionrate = new IlTextInputGUI($this->lng->txt('conversion_rate'), 'conversion_rate');
		$o_Conversionrate->setValue($obj_currency[$currency_id]['conversion_rate']);
		$o_Conversionrate->setPostVar('conversion_rate');
		$o_Conversionrate->setRequired(true);
		
		$o_hidden = new ilHiddenInputGUI('currency_id');
		$o_hidden->setValue($obj_currency[$currency_id]['currency_id']);
		$o_hidden->setPostVar('currency_id');
		$form->addItem($o_hidden);
		
		$form->addItem($o_Unit);
		$form->addItem($o_Isocode);
		$form->addItem($o_Symbol);
		$form->addItem($o_Conversionrate);
		
		$form->addCommandButton('updateCurrency', $this->lng->txt('save'));	
		$form->addCommandButton('currencies', $this->lng->txt('cancel'));	
		
		$this->tpl->setVariable('FORM', $form->getHTML());
	}
	
	public function deleteCurrencyObject()
	{
		
		$this->tpl->addBlockfile('ADM_CONTENT','adm_content','tpl.main_view.html','Services/Payment');	
		if(ilPaymentCurrency::_isDefault($_POST['currency_id'])) return false;
		$_SESSION['currency_id'] = $_POST['currency_id'];

		$oConfirmationGUI = new ilConfirmationGUI();
		$this->ctrl->setParameter($this,'currency_id',(int) $_POST['currency_id']);
		// set confirm/cancel commands
		$oConfirmationGUI->setFormAction($this->ctrl->getFormAction($this,"performDeleteCurrency"));
				
		$oConfirmationGUI->setHeaderText($this->lng->txt("paya_sure_delete_selected_currency"));
		$oConfirmationGUI->setCancel($this->lng->txt("cancel"), "currencies");
		$oConfirmationGUI->setConfirm($this->lng->txt("confirm"), "performDeleteCurrency");			
	
		$oConfirmationGUI->addItem('currency_id','', ilPaymentCurrency::_getUnit($_POST['currency_id']),'' );
		
		$this->tpl->setVariable('CONFIRMATION', $oConfirmationGUI->getHtml());
		return true;
	}
	
	public function performDeleteCurrencyObject()
	{
		if(!$_SESSION['currency_id']) return false;
		
		$obj_currency = new ilPaymentCurrency((int)$_SESSION['currency_id']);
		$obj_currency->deleteCurrency();
		
		return $this->currenciesObject();
	}
	public function updateCurrencyObject()
	{
		if(!$_POST['currency_id']) return false;
		
		$obj_currency = new ilPaymentCurrency($_POST['currency_id']);
		$obj_currency->setUnit($_POST['currency_unit']);
		$obj_currency->setIsoCode($_POST['iso_code']);
		$obj_currency->setSymbol($_POST['symbol']);
		$obj_currency->setConversionRate($_POST['conversion_rate']);
		
		$obj_currency->updateCurrency();
			
		$this->currenciesObject();
		
		return true;
	}

	public function TermsConditionsObject()
	{
		global $ilToolbar;

		$ilToolbar->addButton($this->lng->txt('edit_page'), $this->ctrl->getLinkTargetByClass(array('ilshoppagegui'), 'edit'));

		$this->tpl->addBlockfile('ADM_CONTENT','adm_content','tpl.main_view.html','Services/Payment');
		$this->tpl->setVariable('FORM', $this->getDocumentsPageHTML(self::CONDITIONS_EDITOR_PAGE_ID));

		return true;
	}

	public function BillingMailObject()
	{
		include_once('./Services/Form/classes/class.ilPropertyFormGUI.php');

		$this->tpl->addBlockfile('ADM_CONTENT','adm_content','tpl.main_view.html','Services/Payment');
		$this->tpl->addJavaScript('Services/Mail/js/ilMailComposeFunctions.js');

		$form_gui = new ilPropertyFormGUI();
		$form_gui->setFormAction($this->ctrl->getFormAction($this, 'savebillingmail'));
		$form_gui->setTitle($this->lng->txt('billing_mail'));


		// MESSAGE
		$inp = new ilTextAreaInputGUI($this->lng->txt('message_content'), 'm_message');

		$inp->setValue(ilPaymentSettings::getMailBillingText());
		$inp->setRequired(false);
		$inp->setCols(60);
		$inp->setRows(10);

		// PLACEHOLDERS
		$chb = new ilCheckboxInputGUI($this->lng->txt('activate_placeholders'), 'use_placeholders');
		$chb->setOptionTitle($this->lng->txt('activate_placeholders'));
		$chb->setValue(1);
		$chb->setChecked(ilPaymentSettings::getMailUsePlaceholders());
		$form_gui->addItem($inp);

		include_once 'Services/Payment/classes/class.ilBillingMailPlaceholdersPropertyGUI.php';
		$prop = new ilBillingMailPlaceholdersPropertyGUI();

		$chb->addSubItem($prop);
		$chb->setChecked(true);

		$form_gui->addItem($chb);

		$form_gui->addCommandButton('saveBillingMail', $this->lng->txt('save'));
		$this->tpl->setVariable('FORM', $form_gui->getHTML());

		return true;
	}

	public function saveBillingMailObject()
	{
		if($_POST['m_message'])
		{
			ilPaymentSettings::setMailBillingText($_POST['m_message']);
		}

		$_POST['use_placeholders'] ? $placeholders = 1: $placeholders = 0;
		ilPaymentSettings::setMailUsePlaceholders($placeholders);

		ilUtil::sendSuccess($this->lng->txt('saved_successfully'));
		$this->BillingMailObject();
	}

	public function getDocumentsPageHTML($a_editor_page_id)
	{

		// page object

		include_once 'Services/Payment/classes/class.ilShopPage.php';
		include_once 'Services/Payment/classes/class.ilShopPageGUI.php';

		// if page does not exist, return nothing
		if(!ilShopPage::_exists('shop', $a_editor_page_id))
		{
			return '';
		}

		include_once 'Services/Style/classes/class.ilObjStyleSheet.php';
		$this->tpl->setVariable('LOCATION_CONTENT_STYLESHEET', ilObjStyleSheet::getContentStylePath(0));

		// get page object
		$page_gui = new ilShopPageGUI($a_editor_page_id);

		return $page_gui->showPage();
	}

	public function forwardToDocumentsPageObject($a_editor_page_id)
	{
		global $ilTabs;

		$ilTabs->clearTargets();
		$ilTabs->setBackTarget($this->lng->txt('back'), $this->ctrl->getLinkTarget($this,'documents'), '_self');

		include_once 'Services/Payment/classes/class.ilShopPage.php';
		include_once 'Services/Payment/classes/class.ilShopPageGUI.php';
		include_once('./Services/Style/classes/class.ilObjStyleSheet.php');

		$this->tpl->setVariable('LOCATION_CONTENT_STYLESHEET', ilObjStyleSheet::getContentStylePath(0));

		if(!ilShopPage::_exists('shop', $a_editor_page_id))
		{
			// doesn't exist -> create new one
			$new_page_object = new ilShopPage();
			$new_page_object->setParentId(0);
			$new_page_object->setId($a_editor_page_id);
			$new_page_object->createFromXML();
		}

		$this->ctrl->setReturnByClass('ilshoppagegui', 'edit');

		$page_gui = new ilShopPageGUI(self::CONDITIONS_EDITOR_PAGE_ID);

		return $this->ctrl->forwardCommand($page_gui);
	}

	public function InvoiceNumberObject()
	{
		include_once('./Services/Form/classes/class.ilPropertyFormGUI.php');

		$invObj = new ilUserDefinedInvoiceNumber();

		$this->tpl->addBlockfile('ADM_CONTENT','adm_content','tpl.main_view.html','Services/Payment');
		$this->tpl->addJavaScript('Services/Mail/js/ilMailComposeFunctions.js');

		$form_gui = new ilPropertyFormGUI();
		$form_gui->setFormAction($this->ctrl->getFormAction($this, 'saveInvoiceNumber'));
		$form_gui->setTitle($this->lng->txt('invoice_number_setting'));

		// invoice_type
		$radio_group = new ilRadioGroupInputGUI($this->lng->txt('invoice_number'), 'ud_invoice_number');
		$radio_option_1 = new ilRadioOption($this->lng->txt('ilias_invoice_number'), '0');
		$radio_group->addOption($radio_option_1);
		$radio_option_2 = new ilRadioOption($this->lng->txt('userdefined_invoice_number'), '1');
		$radio_group->addOption($radio_option_2);
		$radio_group->setRequired(true);
		$radio_group->setValue($invObj->getUDInvoiceNumberActive(),'0');
		$radio_group->setPostVar('ud_invoice_number');
		$form_gui->addItem($radio_group);

		// incremental current value
		$cur_num = new ilNonEditableValueGUI($this->lng->txt('incremental_current_value'), 'inc_current_value');
		$cur_num->setValue(ilUserDefinedInvoiceNumber::_getIncCurrentValue(), 1);
		$radio_option_2->addSubItem($cur_num);

		// incremental start value
		$inc_num = new ilNumberInputGUI($this->lng->txt('incremental_start_value'), 'inc_start_value');
		$inc_num->setValue($this->error != "" && isset($_POST['incremental_start_value'])
							? ilUtil::prepareFormOutput($_POST['incremental_start_value'],true)
							: ilUtil::prepareFormOutput($invObj->getIncStartValue(),true));
		$inc_num->setInfo($this->lng->txt('incremental_start_value_info'));
		$radio_option_2->addSubItem($inc_num);

		// reset period of current value
		$sel_reset = new ilSelectInputGUI($this->lng->txt('invoice_number_reset_period'), 'inc_reset_period');
		$sel_reset->setValue($this->error != "" && isset($_POST['inc_reset_period'])
			? $_POST['inc_reset_period']
			: $invObj->getIncResetPeriod());

		$reset_options = array(
			1 => $this->lng->txt('yearly'),
			2 => $this->lng->txt('monthly'));
		$sel_reset->setOptions($reset_options);

		$radio_option_2->addSubItem($sel_reset);

		// invoice_number_text
		$inp = new ilTextAreaInputGUI($this->lng->txt('invoice_number_text'), 'invoice_number_text');
		$inp->setValue(	$this->error != "" && isset($_POST['invoice_number_text'])
							? ilUtil::prepareFormOutput($_POST['invoice_number_text'],true)
							: ilUtil::prepareFormOutput($invObj->getInvoiceNumberText(),true));

		
		$inp->setRequired(false);
		$inp->setCols(60);
		$inp->setRows(3);
		$radio_option_2->addSubItem($inp);

		// PLACEHOLDERS

		$prop = new ilInvoiceNumberPlaceholdersPropertyGUI();
		$radio_option_2->addSubItem($prop);

		$form_gui->addCommandButton('saveInvoiceNumber', $this->lng->txt('save'));
		$this->tpl->setVariable('FORM', $form_gui->getHTML());
	}

	public function saveInvoiceNumberObject()
	{
		// check conditions
		if($_POST['ud_invoice_number'] == 1)
		{
			if($_POST['inc_start_value'] <= 0 || $_POST['inc_start_value'] == NULL)
			{
				$this->error = $this->lng->txt('start_value_cannot_be_null');
				ilUtil::sendFailure($this->error);
				/** @noinspection PhpVoidFunctionResultUsedInspection */
				return $this->InvoiceNumberObject();
			}
			
			if($_POST['invoice_number_text'] !== NULL)
			{
				$check_text = $_POST['invoice_number_text'];
				
				if(strpos($check_text, '[INCREMENTAL_NUMBER]') === FALSE)
				{
					$this->error = $this->lng->txt('invoice_number_must_contain_incremental_number');
					ilUtil::sendFailure($this->error);
					$this->InvoiceNumberObject();
					return true;
				}
				else
				{
					if($_POST['inc_reset_period'] == 1) // yearly
					{
						if(strpos($check_text, '[YEAR]') === FALSE && strpos($check_text, '[CURRENT_TIMESTAMP]') === FALSE)
						{
							$this->error = $this->lng->txt('invoice_number_must_contain_year_ct');
							ilUtil::sendFailure($this->error);
							$this->InvoiceNumberObject();
							return true;
						}
					}
					else if($_POST['inc_reset_period'] == 2) // monthly
					{
						if((strpos($check_text, '[YEAR]') === FALSE || strpos($check_text, '[MONTH]') === FALSE )
						&& (strpos($check_text, '[CURRENT_TIMESTAMP]') === FALSE))
						{
							$this->error = $this->lng->txt('invoice_number_must_contain_year_month_ct');
							ilUtil::sendFailure($this->error);
							$this->InvoiceNumberObject();
							return true;
						}
					}
				}
			}
			else
			{
				ilUtil::sendFailure($this->lng->txt('invoice_number_text_cannot_be_null'));
				$this->InvoiceNumberObject();
				return true;
			}
		}
			// everythink ok  .... update settings
			$invObj = new ilUserDefinedInvoiceNumber();
			$invObj->setUDInvoiceNumberActive($_POST['ud_invoice_number']);
			$invObj->setIncStartValue($_POST['inc_start_value']);
			$invObj->setIncResetPeriod($_POST['inc_reset_period']);
			$invObj->setInvoiceNumberText($_POST['invoice_number_text']);
			$invObj->update();

			$this->InvoiceNumberObject();
			ilUtil::sendSuccess($this->lng->txt('pays_updated_general_settings'));

			return true;
	}
	
	public function StatutoryRegulationsObject()
	{ 
		$this->tpl->addBlockfile('ADM_CONTENT','adm_content','tpl.main_view.html','Services/Payment');
		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this, 'saveStatutoryRegulations'));
		$form->setTitle($this->lng->txt('statutory_regulations'));
		$form->setTableWidth('100%');	
		// message
		$post_gui = new ilTextAreaInputGUI($this->lng->txt('content'), 'statutory_regulations');
		$post_gui->setCols(50);
		$post_gui->setRows(15);
		$post_gui->setUseRte(true);
		$post_gui->addPlugin('latex');
		$post_gui->addButton('latex');
		$post_gui->addButton('pastelatex');
		$post_gui->addPlugin('ilimgupload');
		$post_gui->addButton('ilimgupload');
		$post_gui->removePlugin('advlink');
		$post_gui->removePlugin(ilRTE::ILIAS_IMG_MANAGER_PLUGIN);
		$post_gui->removePlugin('image');
		$post_gui->usePurifier(true);	
		$post_gui->setRTERootBlockElement('');
		$post_gui->setRTESupport(ilObject::_lookupObjId($this->ref_id), 'pays~', 'frm_post', 'tpl.tinymce_frm_post.html', false, '3.4.7');
		$post_gui->disableButtons(array(
			'charmap',
			'undo',
			'redo',
			'justifyleft',
			'justifycenter',
			'justifyright',
			'justifyfull',
			'anchor',
			'fullscreen',
			'cut',
			'copy',
			'paste',
			'pastetext',
			'formatselect',
			'image'
		));
		// purifier
		require_once 'Services/Html/classes/class.ilHtmlPurifierFactory.php';
		require_once 'Services/RTE/classes/class.ilRTE.php';
		$post_gui->setPurifier(ilHtmlPurifierFactory::_getInstanceByType('frm_post'));
		$post_gui->setValue(ilRTE::_replaceMediaObjectImageSrc($this->genSetData->get('statutory_regulations'),1));
		$form->addItem($post_gui);

		// show staturaltyio regulations in shoppingcart
		$cb_showShoppingCart = new ilCheckboxInputGUI($this->lng->txt('show_sr_shoppingcart'), 'show_sr_shoppingcart');
		$cb_showShoppingCart->setInfo($this->lng->txt('show_sr_shoppingcart_info'));
		$cb_showShoppingCart->setValue(1);
		$cb_showShoppingCart->setChecked($this->genSetData->get('show_sr_shoppingcart'));
		$form->addItem($cb_showShoppingCart);

		// attach staturaltyio regulations at invoice
		$cb_attachInvoice = new ilCheckboxInputGUI($this->lng->txt('attach_sr_invoice'), 'attach_sr_invoice');
		$cb_attachInvoice->setInfo($this->lng->txt('attach_sr_invoice_info'));
		$cb_attachInvoice->setValue(1);
		$cb_attachInvoice->setChecked($this->genSetData->get('attach_sr_invoice'));
		$form->addItem($cb_attachInvoice);

		$form->addCommandButton('saveStatutoryRegulations', $this->lng->txt('save'));
		$this->tpl->setVariable('FORM', $form->getHTML());

	}

	public function saveStatutoryRegulationsObject()
	{
		require_once 'Services/RTE/classes/class.ilRTE.php';
		
		if(isset($_POST['statutory_regulations']) && $_POST['statutory_regulations'] != NULL)
		{
			$this->genSetData->set('statutory_regulations', ilRTE::_replaceMediaObjectImageSrc($_POST['statutory_regulations'], 0), 'regulations');
			
			// copy temporary media objects (frm~)
			include_once 'Services/MediaObjects/classes/class.ilObjMediaObject.php';
			$mediaObjects = ilRTE::_getMediaObjects($_POST['statutory_regulations'], 0);				
			$myMediaObjects = ilObjMediaObject::_getMobsOfObject('pays~:html', ilObject::_lookupObjId($this->ref_id));
			foreach($mediaObjects as $mob)
			{
				foreach($myMediaObjects as $myMob)
				{
					if($mob == $myMob)
					{
						// change usage
						ilObjMediaObject::_removeUsage($mob, 'pays~:html', ilObject::_lookupObjId($this->ref_id));
						break;													
					}
				}
				ilObjMediaObject::_saveUsage($mob, 'pays~:html',ilObject::_lookupObjId($this->ref_id));
			}
		}
		else
		{
			$this->genSetData->set('statutory_regulations', NULL, 'regulations');
		}
		
		// remove usage of deleted media objects
		include_once 'Services/MediaObjects/classes/class.ilObjMediaObject.php';
		$oldMediaObjects = ilObjMediaObject::_getMobsOfObject('pays~:html', ilObject::_lookupObjId($this->ref_id));
		$curMediaObjects = ilRTE::_getMediaObjects($_POST['statutory_regulations'], 0);
		foreach($oldMediaObjects as $oldMob)
		{
			$found = false;
			foreach($curMediaObjects as $curMob)
			{
				if($oldMob == $curMob)
				{
					$found = true;
					break;																					
				}
			}
			if(!$found)
			{						
				if(ilObjMediaObject::_exists($oldMob))
				{
					ilObjMediaObject::_removeUsage($oldMob, 'pays~:html', ilObject::_lookupObjId($this->ref_id));
					$mob_obj = new ilObjMediaObject($oldMob);
					$mob_obj->delete();
				}
			}
		}
				
		$this->genSetData->set('show_sr_shoppingcart', isset($_POST['show_sr_shoppingcart']) ? 1 : 0, 'regulations');
		$this->genSetData->set('attach_sr_invoice', isset($_POST['attach_sr_invoice']) ? 1 : 0, 'regulations');

		$this->StatutoryRegulationsObject();
		ilUtil::sendSuccess($this->lng->txt('pays_updated_general_settings'));
		return true;
	}
	
	public function editPriceObject()
	{
		$this->tpl->addBlockfile('ADM_CONTENT', 'adm_content', 'tpl.main_view.html', 'Services/Payment');

		$price_id = $_GET['price_id']?$_GET['price_id']: $_POST['price_id'];
		$price= ilPaymentPrices::_getPrice($price_id);
		$this->ctrl->setParameter($this,'pobject_id',(int) $_GET['pobject_id']);
		$tmp_pobject = ilPaymentObject::_getObjectData($_GET['pobject_id']);
		include_once 'Services/Form/classes/class.ilPropertyFormGUI.php';
		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this));
		
		$tmp_obj = ilObjectFactory::getInstanceByRefId($tmp_pobject['ref_id'], false);
			
		if($tmp_obj)
		{
			$form->setTitle($tmp_obj->getTitle());
		}
		else
		{
			$form->setTitle($this->lng->txt('object_not_found'));
		}

		//price_type
		$radio_group = new ilRadioGroupInputGUI('','price_type');
		$radio_group->setTitle($this->lng->txt('duration'));
		$radio_group->setRequired(true);
		$radio_group->setValue($price['price_type']);
		$radio_group->setPostVar('price_type');

		$radio_option_1 = new ilRadioOption($this->lng->txt('duration_month'), ilPaymentPrices::TYPE_DURATION_MONTH);

			// duration month
			$oDuration = new ilNumberInputGUI();
			$oDuration->setTitle($this->lng->txt('paya_months'));
			$oDuration->setSize('20%');
			$oDuration->setValue($price['duration']);
			$oDuration->setPostVar('duration_month');
			$radio_option_1->addSubItem($oDuration);

		$radio_group->addOption($radio_option_1);

		$radio_option_3 = new ilRadioOption($this->lng->txt('duration_date'), ilPaymentPrices::TYPE_DURATION_DATE);

			// duration_date from	
			$o_date_from = new ilDateTimeInputGUI();
			$o_date_from->setTitle($this->lng->txt('cal_from'));
			$o_date_from->setDate(new ilDate($price['duration_from'],IL_CAL_DATE));
			$o_date_from->setPostVar('duration_date_from');	
			$radio_option_3->addSubItem($o_date_from);

			// duration_date until
			$o_date_until = new ilDateTimeInputGUI();
			$o_date_until->setTitle($this->lng->txt('cal_until'));
			$o_date_until->setDate(new ilDate($price['duration_until'],IL_CAL_DATE));
			$o_date_until->setPostVar('duration_date_until');			
			$radio_option_3->addSubItem($o_date_until);

		$radio_group->addOption($radio_option_3);

		$radio_option_2 = new ilRadioOption($this->lng->txt('unlimited_duration'), ilPaymentPrices::TYPE_UNLIMITED_DURATION);		
		$radio_group->addOption($radio_option_2);

		$form->addItem($radio_group);		

		// description
		$oDescription = new ilTextAreaInputGUI($this->lng->txt('description'), 'description');
		$oDescription->setRows(4);
		$oDescription->setCols(35);
		$oDescription->setValue($price['description']);
		$form->addItem($oDescription);
		
		// price
		$oPrice = new ilNumberInputGUI();
		$oPrice->setTitle($this->lng->txt('price_a'));
		$oPrice->setSize('20%');
		$oPrice->setRequired(true);
		$oPrice->setValue($price['price']);
		include_once './Services/Payment/classes/class.ilPaymentSettings.php';
		$genSet = ilPaymentSettings::_getInstance();
		$oPrice->setInfo($genSet->get('currency_unit'));
		$oPrice->setPostVar('price');
		$oPrice->allowDecimals(true);
		$form->addItem($oPrice);

		//extension
		$oExtension = new ilCheckboxInputGUI($this->lng->txt('extension_price'), 'extension');

		$oExtension->setChecked($price['extension']);

		$form->addItem($oExtension);

			$o_hidden_1 = new ilHiddenInputGUI('pobject_id');
			$o_hidden_1->setValue( (int)$_GET['pobject_id']);
			$o_hidden_1->setPostVar('pobject_id');

			$o_hidden_2 = new ilHiddenInputGUI('price_id');
			$o_hidden_2->setValue( (int)$_GET['price_id']);
			$o_hidden_2->setPostVar('price_id');

			$form->addItem($o_hidden_1);
			$form->addItem($o_hidden_2);

			$form->addCommandButton('updatePrice',$this->lng->txt('save'));
			$form->addCommandButton('editPrices', $this->lng->txt('cancel'));	

		$this->tpl->setVariable('FORM',$form->getHTML());
	}	
	
} // END class.ilObjPaymentSettingsGUI
?>
