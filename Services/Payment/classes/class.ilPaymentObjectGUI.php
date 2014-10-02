<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once 'Services/Payment/classes/class.ilPaymentObject.php';
include_once 'Services/Payment/classes/class.ilPaymentBookings.php';
include_once 'Services/Payment/classes/class.ilFileDataShop.php';
include_once 'Services/Payment/classes/class.ilShopVatsList.php';
include_once './Services/Payment/classes/class.ilShopTableGUI.php';


/**
 * Class ilPaymentObjectGUI
 * @author       Stefan Meyer
 * @version      $Id: class.ilPaymentObjectGUI.php 21617 2009-09-10 13:49:10Z jgoedvad $
 * @ilCtrl_Calls ilPaymentObjectGUI: ilShopPageGUI
 * @package      core
 * 
 * 
 */
class ilPaymentObjectGUI extends ilShopBaseGUI
{
	/** @var $ctrl ilCtrl */
	public $ctrl;
	/** @var $lng ilLanguage */
	public $lng;
	/** @var $user_obj ilObjUser */
	public $user_obj;
	/** @var $pobject ilPaymentObject */
	public $pobject = null;

	public function __construct($user_obj)
	{
		parent::__construct();

		$this->user_obj = $user_obj;
		$this->lng->loadLanguageModule('crs');
		ilDatePresentation::setUseRelativeDates(false);
	}

	protected function prepareOutput()
	{
		/** 
		 * @var $ilTabs ilTabsGUI 
		 */
		global $ilTabs;
		
		parent::prepareOutput();

		$ilTabs->setTabActive('paya_header');
		$ilTabs->setSubTabActive('paya_object');
	}

	public function executeCommand()
	{
		$cmd = $this->ctrl->getCmd();
		switch($this->ctrl->getNextClass($this))
		{
			case 'ilshoppagegui':
				$this->prepareOutput();
				$ret = $this->forwardToPageObject();
				if($ret != '')
				{
					$this->tpl->setContent($ret);
				}
				break;

			default:
				if(!$cmd)
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
		/**
	 	* @var $ilTabs ilTabsGUI
	 	*/
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
		include_once 'Services/Payment/classes/class.ilShopPage.php';
		include_once 'Services/Payment/classes/class.ilShopPageGUI.php';
		include_once('./Services/Style/classes/class.ilObjStyleSheet.php');

		$this->tpl->setVariable('LOCATION_CONTENT_STYLESHEET', ilObjStyleSheet::getContentStylePath(0));

		if(!ilShopPage::_exists('shop', $this->pobject->getPobjectId()))
		{
			// doesn't exist -> create new one
			$new_page_object = new ilShopPage();
			$new_page_object->setParentId(0);
			$new_page_object->setId($this->pobject->getPobjectId());
			$new_page_object->createFromXML();
		}

		$this->ctrl->setReturnByClass('ilshoppagegui', 'edit');

		$page_gui = new ilShopPageGUI($this->pobject->getPobjectId());
		$this->ctrl->setParameter($page_gui, 'pobject_id', (int)$_GET['pobject_id']);

		return $this->ctrl->forwardCommand($page_gui);
	}

	public function resetObjectFilter()
	{
		unset($_SESSION['pay_objects']);
		unset($_POST['title_type']);
		unset($_POST['title_value']);
		unset($_POST['vendor']);
		unset($_POST['pay_method']);
		unset($_POST['updateView']);
		unset($_POST['show_filter']);

		ilUtil::sendInfo($this->lng->txt('paya_filter_reseted'));

		return $this->showObjects();
	}

	public function showObjects()
	{
		/** 
		 * @var $ilToolbar ilToolbarGUI */
		global $ilToolbar;

		include_once './Services/Payment/classes/class.ilPayMethods.php';

		$ilToolbar->addButton($this->lng->txt('paya_sell_object'), $this->ctrl->getLinkTarget($this, 'showObjectSelector'));

		if(!$_POST['show_filter'] && $_POST['updateView'] == '1')
		{
			$this->resetObjectFilter();
		}
		else
			if($_POST['updateView'] == 1)
			{
				$_SESSION['pay_objects']['updateView']  = $_POST['updateView'];
				$_SESSION['pay_objects']['show_filter'] = $_POST['show_filter'];
				$_SESSION['pay_objects']['title_type']  = $_POST['title_type'];
				$_SESSION['pay_objects']['title_value'] = $_POST['title_value'];
				$_SESSION['pay_objects']['pay_method']  = $_POST['pay_method'];
				$_SESSION['pay_objects']['vendor']      = $_POST['vendor'];
			}

		$this->tpl->addBlockfile('ADM_CONTENT', 'adm_content', 'tpl.main_view.html', 'Services/Payment');

		$this->__initPaymentObject();
		$this->lng->loadLanguageModule('search');

		$filter_form = new ilPropertyFormGUI();
		$filter_form->setFormAction($this->ctrl->getFormAction($this));
		$filter_form->setTitle($this->lng->txt('pay_filter'));
		$filter_form->setId('filter_form');
		$filter_form->setTableWidth('100 %');

		//hide_filter
		$o_hide_check = new ilCheckBoxInputGUI($this->lng->txt('show_filter'), 'show_filter');
		$o_hide_check->setValue(1);
		$o_hide_check->setChecked($_SESSION['pay_objects']['show_filter'] ? 1 : 0);

		$o_hidden = new ilHiddenInputGUI('updateView');
		$o_hidden->setValue(1);
		$o_hidden->setPostVar('updateView');
		$o_hide_check->addSubItem($o_hidden);

		//title
		$radio_group  = new ilRadioGroupInputGUI($this->lng->txt('search_in_title'), 'title_type');
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

		$filter_form->addCommandButton('showObjects', $this->lng->txt('pay_update_view'));
		$filter_form->addCommandButton('resetObjectFilter', $this->lng->txt('pay_reset_filter'));

		$filter_form->addItem($o_hide_check);
		if(!count($objects = ilPaymentObject::_getObjectsData($this->user_obj->getId())))
		{
			ilUtil::sendInfo($this->lng->txt('paya_no_objects_assigned'));

			return true;
		}
		$this->tpl->setVariable('FORM', $filter_form->getHTML());

		$counter = 0;
		foreach($objects as $data)
		{
			/** @var $tmp_obj ilObject */
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
			$str_paymethod                    = ilPayMethods::getStringByPaymethod($data['pay_method']);
			$f_result[$counter]['pay_method'] = $str_paymethod;

			if($data['vat_id'] <= 0)
			{
				$vat_rate = $this->lng->txt('payment_vat_has_to_be_defined_by_administration_short');
			}
			else
			{
				try
				{
					$oVAT     = new ilShopVats((int)$data['vat_id']);
					$vat_rate = ilShopUtils::_formatVAT((float)$oVAT->getRate());
				}
				catch(ilShopException $e)
				{
					$vat_rate = $this->lng->txt('payment_vat_has_to_be_defined_by_administration_short');
				}
			}
			$f_result[$counter]['vat_rate'] = $vat_rate;

			/** @var $tmp_user ilObjUser */
			$tmp_user                     = ilObjectFactory::getInstanceByObjId($data['vendor_id']);
			$f_result[$counter]['vendor'] = $tmp_user->getFullname() . ' [' . $tmp_user->getLogin() . ']';

			// Get number of purchasers
			$f_result[$counter]['purchasers'] = ilPaymentBookings::_getCountBookingsByObject($data['pobject_id']);

			// edit link
			$this->ctrl->setParameter($this, 'pobject_id', $data['pobject_id']);
			$link_change = "<div class=\"il_ContainerItemCommands\"><a class=\"il_ContainerItemCommand\" href=\"" . $this->ctrl->getLinkTarget($this, "editDetails") . "\">" . $this->lng->txt("edit") . "</a></div>";

			$f_result[$counter]['options'] = $link_change;
			unset($tmp_user);
			unset($tmp_obj);

			++$counter;
		}

		return $this->__showObjectsTable($f_result);
	}

	public function editDetails($a_show_confirm = false)
	{
		/** 
		 * @var $ilToolbar ilToolbarGUI 
		 */ 
		global $ilToolbar;

		include_once './Services/Payment/classes/class.ilPaymentSettings.php';
		/** @var $genSet ilPaymentSettings */
		$genSet = ilPaymentSettings::_getInstance();

		if(!(int)$_GET['pobject_id'])
		{
			ilUtil::sendInfo($this->lng->txt('paya_no_object_selected'));
			return $this->showObjects();
		}

		$this->__initPaymentObject((int)$_GET['pobject_id']);

		$this->ctrl->setParameter($this, 'pobject_id', (int)$_GET['pobject_id']);

		$this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.main_view.html', 'Services/Payment');
		/** @var $tmp_obj ilObject */
		$tmp_obj = ilObjectFactory::getInstanceByRefId($this->pobject->getRefId(), false);
		if(is_object($tmp_obj))
		{
			$trash = '';
			if(ilObject::_isInTrash($this->pobject->getRefId()))
			{
				$trash = ' (' . $this->lng->txt('object_deleted') . ')';
			}
			$tmp_object['title'] = $tmp_obj->getTitle() . '' . $trash;
			$tmp_object['type']  = $tmp_obj->getType();
		}
		else
		{
			$tmp_object['title'] = $this->lng->txt('object_not_found');
			$tmp_object['type']  = false;
		}

		if($a_show_confirm)
		{
			include_once './Services/Utilities/classes/class.ilConfirmationGUI.php';
			$oConfirmationGUI = new ilConfirmationGUI();

			// set confirm/cancel commands
			$oConfirmationGUI->setFormAction($this->ctrl->getFormAction($this, "performDelete"));
			$oConfirmationGUI->setHeaderText($this->lng->txt("paya_sure_delete_object"));
			$oConfirmationGUI->setCancel($this->lng->txt("cancel"), "editDetails");
			$oConfirmationGUI->setConfirm($this->lng->txt("confirm"), "performDelete");

			$oConfirmationGUI->addItem('', $tmp_object['title'], $tmp_object['title']);
			$this->tpl->setVariable('CONFIRMATION', $oConfirmationGUI->getHTML());

			return true;
		}

		$ilToolbar->addButton($this->lng->txt('paya_edit_details'), $this->ctrl->getLinkTarget($this, 'editDetails'));
		$ilToolbar->addButton($this->lng->txt('paya_edit_prices'), $this->ctrl->getLinkTarget($this, 'editPrices'));
		$ilToolbar->addButton($this->lng->txt('pay_edit_abstract'), $this->ctrl->getLinkTargetByClass(array('ilshoppagegui'), 'edit'));

		include_once 'Services/Form/classes/class.ilPropertyFormGUI.php';
		$oForm = new ilPropertyFormGUI();
		$oForm->setFormAction($this->ctrl->getFormAction($this, 'updateDetails'));
		$oForm->setTitle($tmp_object['title']);
		if($tmp_object['type'])
		{
			$oForm->setTitleIcon(ilObject::_getIcon($tmp_obj->getId()));
		}
		// repository path
		$oPathGUI = new ilNonEditableValueGUI($this->lng->txt('path'));
		$oPathGUI->setValue($this->__getHTMLPath($this->pobject->getRefId()));
		$oForm->addItem($oPathGUI);

		switch($tmp_object['type'])
		{
			case 'exc':
				$exc_subtype_option = array();
				$check_subtypes     = ilPaymentObject::_checkExcSubtype($this->pobject->getRefId());

				if(!in_array('download', $check_subtypes) || $this->pobject->getSubtype() == 'download')
					$exc_subtype_option['download'] = $this->lng->txt('download');
				if(!in_array('upload', $check_subtypes) || $this->pobject->getSubtype() == 'upload')
					$exc_subtype_option['upload'] = $this->lng->txt('upload');

				$oExcSubtype = new ilSelectInputGUI($this->lng->txt('select_subtype'), 'exc_subtype');
				$oExcSubtype->setOptions($exc_subtype_option);
				$oExcSubtype->setValue($this->pobject->getSubtype());
				$oForm->addItem($oExcSubtype);

				break;
			default:
				break;
		}
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
		$PMoptions      = ilPaymethods::getPayMethodsOptions('not_specified');
		$oPayMethodsGUI->setOptions($PMoptions);
		$oPayMethodsGUI->setValue($this->pobject->getPayMethod());
		$oForm->addItem($oPayMethodsGUI);

		// topics
		/** @var $shopTopicsObj ilShopTopics */
		$shopTopicsObj = ilShopTopics::_getInstance();
		$shopTopicsObj->read();
		if(is_array($topics = $shopTopicsObj->getTopics()) && count($topics))
		{
			$oTopicsGUI = new ilSelectInputGUI($this->lng->txt('topic'), 'topic_id');
			include_once 'Services/Payment/classes/class.ilShopTopics.php';
			/** @var $shopTopicsObj ilShopTopics */
			$shopTopicsObj = ilShopTopics::_getInstance();
			$shopTopicsObj->read();
			$topic_options     = array();
			$topic_options[''] = $this->lng->txt('please_choose');

			/** @var $oTopic ilShopTopic */
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
			/** @var $oVAT ilShopVats */
			foreach($oShopVatsList as $oVAT)
			{
				$vats_options[$oVAT->getId()] = ilShopUtils::_formatVAT($oVAT->getRate()) . ' -> ' . $oVAT->getTitle();
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
		$oFile      = new ilFileDataShop($this->pobject->getPobjectId());
		if(($webpath_file = $oFile->getCurrentImageWebPath()) !== false)
		{
			$oThumbnail->setImage($webpath_file);
		}
		$oForm->addItem($oThumbnail);

		if($genSet->get('use_shop_specials'))
		{
			// special object
			$oSpecial = new ilCheckboxInputGUI($this->lng->txt('special'), 'is_special');
			$oSpecial->setChecked((int)$this->pobject->getSpecial());
			$oSpecial->setInfo($this->lng->txt('special_info'));
			$oForm->addItem($oSpecial);
		}
		// buttons
		$oForm->addCommandButton('updateDetails', $this->lng->txt('save'));
		$oForm->addCommandButton('deleteObject', $this->lng->txt('delete'));

		$this->tpl->setVariable('FORM', $oForm->getHTML());
		return true;
	}

	public function deleteObject()
	{
		include_once './Services/Payment/classes/class.ilPaymentBookings.php';

		if(!$_GET['pobject_id'])
		{
			ilUtil::sendFailure($this->lng->txt('paya_no_object_selected'));

			$this->showObjects();
			return true;
		}
		if(ilPaymentBookings::_getCountBookingsByObject((int)$_GET['pobject_id']))
		{
			ilUtil::sendInfo($this->lng->txt('paya_bookings_available'));
			$this->editDetails();

			return false;
		}
		else
		{

			$this->editDetails(true);

			return true;
		}
	}

	public function performDelete()
	{
		include_once './Services/Payment/classes/class.ilPaymentPrices.php';

		if(!$_GET['pobject_id'])
		{
			ilUtil::sendInfo($this->lng->txt('paya_no_object_selected'));

			$this->showObjects();
			return true;
		}
		$this->__initPaymentObject((int)$_GET['pobject_id']);

		// delete object data
		$this->pobject->delete();

		// delete payment prices
		$price_obj = new ilPaymentPrices((int)$_GET['pobject_id']);
		$price_obj->deleteAllPrices();
		unset($price_obj);

		ilUtil::sendInfo($this->lng->txt('paya_deleted_object'));

		$this->showObjects();

		return true;
	}

	public function editPayMethod()
	{
		$this->__initPaymentObject((int)$_GET['pobject_id']);

		switch($this->pobject->getPayMethod())
		{
			case $this->pobject->PAY_METHOD_NOT_SPECIFIED:
				ilUtil::sendFailure($this->lng->txt('paya_select_pay_method_first'));
				$this->editDetails();
				return true;

			default:
				ilUtil::sendInfo($this->lng->txt('paya_no_settings_necessary'));
				$this->editDetails();
				return true;
		}
	}

	public function editPrices($a_show_delete = false)
	{
		/** 
		 * @var $ilToolbar ilToolbarGUI
		 */
		global $ilToolbar;
		
		include_once './Services/Payment/classes/class.ilPaymentPrices.php';
		include_once './Services/Payment/classes/class.ilPaymentCurrency.php';
		include_once './Services/Utilities/classes/class.ilConfirmationGUI.php';
		include_once './Services/Payment/classes/class.ilPaymentSettings.php';
		/** @var $genSet ilPaymentSettings */
		$genSet = ilPaymentSettings::_getInstance();

		if($a_show_delete == false) unset($_SESSION['price_ids']);

		$_SESSION['price_ids'] = $_SESSION['price_ids'] ? $_SESSION['price_ids'] : array();

		if(!$_GET['pobject_id'] && !$_POST['pobject_id'])
		{
			ilUtil::sendInfo($this->lng->txt('paya_no_object_selected'));

			$this->showObjects();
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

		$this->ctrl->setParameter($this, 'pobject_id', $pobject_id);
		$this->__initPaymentObject((int)$_GET['pobject_id']);

		$this->tpl->addBlockfile("ADM_CONTENT", "adm_content", 'tpl.main_view.html', 'Services/Payment');

		$price_obj = new ilPaymentPrices($pobject_id);

		$standard_prices  = array();
		$extension_prices = array();
		$standard_prices  = $price_obj->getPrices();
		$extension_prices = $price_obj->getExtensionPrices();

		$prices = array_merge($standard_prices, $extension_prices);

		// No prices created
		if(!count($prices))
		{
			ilUtil::sendInfo($this->lng->txt('paya_no_price_available'));
			$ilToolbar->addButton($this->lng->txt('paya_add_price'), $this->ctrl->getLinkTarget($this, 'addPrice'));

			return true;
		}
		else if(!count($standard_prices))
		{
			//set pobject status to "not buyable" if there is no standard_price defined
			$this->pobject->setStatus(0);
			$this->pobject->update();
			ilUtil::sendInfo($this->lng->txt('paya_no_price_available'));
		}

		// Show confirm delete
		if($a_show_delete)
		{
			$oConfirmationGUI = new ilConfirmationGUI();

			// set confirm/cancel commands
			$oConfirmationGUI->setFormAction($this->ctrl->getFormAction($this, "performDeletePrice"));
			$oConfirmationGUI->setHeaderText($this->lng->txt("paya_sure_delete_selected_prices"));
			$oConfirmationGUI->setCancel($this->lng->txt("cancel"), "editPrices");
			$oConfirmationGUI->setConfirm($this->lng->txt("confirm"), "performDeletePrice");

			$counter = 0;

			foreach($prices as $price)
			{
				$currency = $genSet->get('currency_unit');
				if(in_array($price['price_id'], $_SESSION['price_ids']))
				{

					if($price['unlimited_duration'] == '1')
					{
						$tmp_price = $this->lng->txt('unlimited_duration');
					}
					else
					{
						$tmp_price = $price['duration'] . ' ' . $this->lng->txt('paya_months');
					}
					$delete_row = '' . $tmp_price . '  :  ' .
						ilFormat::_getLocalMoneyFormat($price['price']) . ' ' .
						$currency;

					$oConfirmationGUI->addItem('', $delete_row, $delete_row);
					$delete_row = '';
				}
				$counter++;
			}

			$this->tpl->setVariable('CONFIRMATION', $oConfirmationGUI->getHTML());

			return true;
		}

		$ilToolbar->addButton($this->lng->txt('paya_edit_details'), $this->ctrl->getLinkTarget($this, 'editDetails'));
		$ilToolbar->addButton($this->lng->txt('paya_edit_prices'), $this->ctrl->getLinkTarget($this, 'editPrices'));
		$ilToolbar->addButton($this->lng->txt('pay_edit_abstract'), $this->ctrl->getLinkTargetByClass(array('ilshoppagegui'), 'edit'));

		// Fill table cells
		/** @var $tpl ilTemplate */
		$tpl = new ilTemplate('tpl.table.html', true, true);

		// set table header
		$tpl->setCurrentBlock('tbl_form_header');

		$tpl->setVariable('FORMACTION', $this->ctrl->getFormAction($this));
		$tpl->parseCurrentBlock();

		$counter = 0;
		include_once './Services/Calendar/classes/class.ilDatePresentation.php';
		foreach($prices as $price)
		{
			$data[$counter]['price_id'] = ilUtil::formCheckBox(in_array($price['price_id'], $_SESSION['price_ids']) ? 1 : 0,
				'price_ids[]', $price['price_id']);

			switch($price['price_type'])
			{
				case ilPaymentPrices::TYPE_DURATION_MONTH:
					$data[$counter]['duration'] = $price['duration'] . ' ' . $this->lng->txt('paya_months');
					break;
				case ilPaymentPrices::TYPE_DURATION_DATE:

					$data[$counter]['duration'] = ilDatePresentation::formatDate(new ilDate($price['duration_from'], IL_CAL_DATE))
						. ' - ' . ilDatePresentation::formatDate(new ilDate($price['duration_until'], IL_CAL_DATE));
					break;
				case ilPaymentPrices::TYPE_UNLIMITED_DURATION:
					$data[$counter]['duration'] = $this->lng->txt('unlimited_duration');
					break;
			}
			$data[$counter]['price']         = ilFormat::_getLocalMoneyFormat($price['price']);
			$data[$counter]['currency_unit'] = $genSet->get('currency_unit');
			$data[$counter]['extension']     = ilUtil::formCheckBox($price['extension'] ? 1 : 0,
				'extension_ids[]', (int)$price['price_id'], true);

			$this->ctrl->setParameter($this, "price_id", $price['price_id']);
			$data[$counter]['edit'] =
				"<div class=\"il_ContainerItemCommands\"><a class=\"il_ContainerItemCommand\" href=\"" . $this->ctrl->getLinkTarget($this, "editPrice") . "\">" . $this->lng->txt("edit") . "</a></div>";

			++$counter;
		}
		$this->__editPricesTable($data);

		return true;
	}

	private function __editPricesTable($a_result_set)
	{
		$tbl = new ilShopTableGUI($this);

		/** @var $tmp_obj ilObject */
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
		$tbl->addColumn('', 'edit', '30%');

		$tbl->setSelectAllCheckbox('price_id');
		$tbl->addCommandButton('addPrice', $this->lng->txt('paya_add_price'));

		$tbl->addMultiCommand("deletePrice", $this->lng->txt("paya_delete_price"));
		$tbl->fillFooter();

		$tbl->setData($a_result_set);

		$this->tpl->setVariable('TABLE', $tbl->getHTML());

		return true;
	}

	public function addPrice()
	{
		/** 
		 * @var $ilToolbar ilToolbarGUI 
		 * */
		global $ilToolbar;

		if(!$_GET['pobject_id'])
		{
			ilUtil::sendInfo($this->lng->txt('paya_no_object_selected'));

			$this->showObjects();
			return true;
		}

		include_once './Services/Payment/classes/class.ilPaymentSettings.php';
		/** @var $genSet ilPaymentSettings */
		$genSet = ilPaymentSettings::_getInstance();

		$this->ctrl->setParameter($this, 'pobject_id', (int)$_GET['pobject_id']);

		$this->__initPaymentObject((int)$_GET['pobject_id']);

		$ilToolbar->addButton($this->lng->txt('paya_edit_details'), $this->ctrl->getLinkTarget($this, 'editDetails'));
		$ilToolbar->addButton($this->lng->txt('paya_edit_prices'), $this->ctrl->getLinkTarget($this, 'editPrices'));

		$this->tpl->addBlockfile('ADM_CONTENT', 'adm_content', 'tpl.main_view.html', 'Services/Payment');

		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this));
		$form->setTitle($this->lng->txt('paya_add_price_title'));

		// object_title
		$oTitle  = new ilNonEditableValueGUI($this->lng->txt('title'));
		/** @var $tmp_obj ilObject */
		$tmp_obj = ilObjectFactory::getInstanceByRefId($this->pobject->getRefId(), false);
		if(is_object($tmp_obj))
		{
			$oTitle->setValue($tmp_obj->getTitle());
		}
		else
		{
			$oTitle->setValue($this->lng->txt('object_not_found'));
		}
		$form->addItem($oTitle);

		//price_type

		$radio_group = new ilRadioGroupInputGUI('', 'price_type');
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
		$o_date_from->setTitle($this->lng->txt('from'));
		$o_date_from->setPostVar('duration_date_from');
		$radio_option_3->addSubItem($o_date_from);

		// duration_date until
		$o_date_until = new ilDateTimeInputGUI();
		$o_date_until->setTitle($this->lng->txt('until'));
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
		$this->tpl->setVariable('TXT_PRICE_A', $genSet->get('currency_unit'));

		//extension
		$oExtension = new ilCheckboxInputGUI($this->lng->txt('extension_price'), 'extension');
		isset($_POST['extension']) ? $ext_value = 1 : $ext_value = 0;
		$oExtension->setChecked($ext_value);

		$form->addItem($oExtension);

		$form->addCommandButton('performAddPrice', $this->lng->txt('paya_add_price'));
		$form->addCommandButton('editPrices', $this->lng->txt('cancel'));
		$this->tpl->setVariable('FORM', $form->getHTML());

		return true;
	}

	public function performAddPrice()
	{
		if(!(int)$_GET['pobject_id'])
		{
			ilUtil::sendInfo($this->lng->txt('paya_no_object_selected'));

			$this->showObjects();
			return true;
		}

		include_once './Services/Payment/classes/class.ilPaymentPrices.php';
		include_once './Services/Payment/classes/class.ilPaymentCurrency.php';

		$currency = ilPaymentCurrency::_getAvailableCurrencies();

		$po = new ilPaymentPrices((int)$_GET['pobject_id']);

		switch($_POST['price_type'])
		{
			case 'unlimited_duration':
				$po->setPriceType(ilPaymentPrices::TYPE_UNLIMITED_DURATION);
				$po->setDuration(0);
				$po->setDurationFrom(NULL);
				$po->setDurationUntil(NULL);
				$po->setUnlimitedDuration(1);

				break;

			case 'duration_date':

				$po->setPriceType(ilPaymentPrices::TYPE_DURATION_DATE);
				$po->setDuration(NULL);
				$po->setDurationFrom(ilUtil::stripSlashes(
					$_POST['duration_date_from']['date']['y'] . '-' .
						$_POST['duration_date_from']['date']['m'] . '-' .
						$_POST['duration_date_from']['date']['d']));
				$po->setDurationUntil(ilUtil::stripSlashes(
					$_POST['duration_date_until']['date']['y'] . '-' .
						$_POST['duration_date_until']['date']['m'] . '-' .
						$_POST['duration_date_until']['date']['d']));
				break;

			default:
			case 'duration_month':
				$po->setPriceType(ilPaymentPrices::TYPE_DURATION_MONTH);
				$po->setDuration($_POST['duration_month']);
				$po->setDurationFrom(NULL);
				$po->setDurationUntil(NULL);
				break;
		}

		$po->setDescription($_POST['description'] ? ilUtil::stripSlashes($_POST['description']) : NULL);
		$po->setPrice(ilUtil::stripSlashes($_POST['price']));
		$po->setCurrency($currency[1]['currency_id']);

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

			return $this->editPrices();

		}
		catch(ilShopException $e)
		{
			ilUtil::sendInfo($e->getMessage());
			return $this->addPrice();
		}

	}

	public function performDeletePrice()
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
		include_once './Services/Payment/classes/class.ilPaymentPrices.php';

		$prices = new ilPaymentPrices((int)$_GET['pobject_id']);

		foreach($_SESSION['price_ids'] as $price_id)
		{
			$prices->delete($price_id);
		}

		// check if it was last price otherwise set status to 'not_buyable'
		if(!count($prices->getPrices()))
		{
			$this->__initPaymentObject((int)$_GET['pobject_id']);

			$this->pobject->setStatus($this->pobject->STATUS_NOT_BUYABLE);
			$this->pobject->update();

			ilUtil::sendInfo($this->lng->txt('paya_deleted_last_price'));
		}
		unset($prices);
		unset($_SESSION['price_ids']);

		return $this->editPrices();
	}


	public function deletePrice()
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

	public function ORg_updatePrice()
	{
		include_once './Services/Payment/classes/class.ilPaymentPrices.php';

		if(!$_GET['pobject_id'] && !$_POST['pobject_id'])
		{
			ilUtil::sendInfo($this->lng->txt('paya_no_object_selected'));

			$this->showObjects();
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
		$po = new ilPaymentPrices($pobject_id);

		$this->ctrl->setParameter($this, 'pobject_id', $pobject_id);

		$price_id = (int)$_POST['price_id'];

		// validate
		$old_price = $po->getPrice($price_id);


		$po->setDuration((int)$_POST['duration']);
		$po->setUnlimitedDuration($_POST['unlimited_duration']);
		$po->setPrice($_POST['price']);
		$po->setPriceType($_POST['price_type']);
		$po->setCurrency($old_price['currency']);
		$po->setExtension((int)$_POST['extension']);

		if(!$po->validate())
		{
			$error = true;
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
//	 			$search = in_array((string)$price_id, $_POST['duration_ids']);
				if($_POST['duration_ids'] == NULL)
				{
					$po->setUnlimitedDuration(0);
					$po->setDuration($price['duration']);
				}

				else if($search = in_array((string)$price_id, $_POST['duration_ids']))
				{
					$po->setUnlimitedDuration(1);
					$po->setDuration(0);
				}
				else
				{
					$po->setUnlimitedDuration(0);
				}
			}

			if(isset($_POST['extension_ids']))
			{
//	 			$search = in_array((string)$price_id, $_POST['extension_ids']);
				if($search = in_array((string)$price_id, $_POST['extension_ids']))
				{
					$po->setExtension(1);
				}
				else
				{
					$po->setExtension(0);
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

	public function updateDetails()
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
		$old_status = $this->pobject->getStatus();

		// check status changed from not_buyable
		if($old_status == $this->pobject->STATUS_NOT_BUYABLE and
			(int)$_POST['status'] != $old_status
		)
		{
			// check pay_method edited
			switch((int)$_POST['pay_method'])
			{
				case $this->pobject->PAY_METHOD_NOT_SPECIFIED:
					ilUtil::sendInfo($this->lng->txt('paya_select_pay_method_first'));
					$this->editDetails();

					return false;

				default:
					;
			}
			// check minimum one price
			include_once './Services/Payment/classes/class.ilPaymentPrices.php';

			$prices_obj = new ilPaymentPrices((int)$_GET['pobject_id']);
			if(!count($prices_obj->getPrices()))
			{
				ilUtil::sendInfo($this->lng->txt('paya_edit_prices_first'));
				$this->editDetails();

				return false;
			}
		}

		if((int)$_POST['status'] == 0)
		{
			// Status: not buyable -> delete depending shoppingcart entries
			include_once './Services/Payment/classes/class.ilPaymentShoppingCart.php';
			ilPaymentShoppingCart::_deleteShoppingCartEntries($this->pobject->getPobjectId());
		}

		$this->pobject->setStatus((int)$_POST['status']);
		$this->pobject->setVendorId((int)$_POST['vendor']);
		$this->pobject->setPayMethod((int)$_POST['pay_method']);
		$this->pobject->setTopicId((int)$_POST['topic_id']);
		$this->pobject->setVatId((int)$_POST['vat_id']);
		$this->pobject->setSubtype((string)$_POST['exc_subtype']);
		$this->pobject->setSpecial((int)$_POST['is_special']);

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
		$this->showObjects();

		return true;
	}

	public function showObjectSelector()
	{
		/** 
		 * @var $ilToolbar ilToolbarGUI */
		global $ilToolbar;

		$this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.paya_object_selector.html', 'Services/Payment');
		$ilToolbar->addButton($this->lng->txt('back'), $this->ctrl->getLinkTarget($this, 'showObjects'));

		ilUtil::sendInfo($this->lng->txt('paya_select_object_to_sell'));

		include_once("./Services/Payment/classes/class.ilPaymentObjectSelector.php");
		$exp = new ilPaymentObjectSelector($this, "showObjectSelector");
		if (!$exp->handleCommand())
		{
			$this->tpl->setLeftNavContent($exp->getHTML());
		}

		return true;
	}

	public function showSelectedObject()
	{
		global $ilToolbar;

		if(!(int)$_GET['sell_id'])
		{
			ilUtil::sendInfo($this->lng->txt('paya_no_object_selected'));
			return $this->showObjectSelector();
		}

		$ilToolbar->addButton($this->lng->txt('back'), $this->ctrl->getLinkTarget($this, 'showObjectSelector'));

		// save ref_id of selected object
		$this->ctrl->setParameter($this, 'sell_id', (int)$_GET['sell_id']);

		include_once 'Services/Form/classes/class.ilPropertyFormGUI.php';
		$oForm = new ilPropertyFormGUI();
		$oForm->setFormAction($this->ctrl->getFormAction($this, 'updateDetails'));
		$oForm->setTitle($this->lng->txt('details'));
		$tmp_obj = ilObjectFactory::getInstanceByRefId($_GET['sell_id'], false);
		$oForm->setTitleIcon(ilObject::_getIcon($tmp_obj->getId()));
	
		if(is_object($tmp_obj))
		{
			$tmp_object['title']       = $tmp_obj->getTitle();
			$tmp_object['description'] = $tmp_obj->getDescription();
			$tmp_object['owner']       = $tmp_obj->getOwnerName();
			$tmp_object['path']        = $this->__getHTMLPath((int)$_GET['sell_id']);
		}
		else
		{
			$tmp_object['title']       = $this->lng->txt('object_not_found');
			$tmp_object['description'] = '';
			$tmp_object['owner']       = '';
			$tmp_object['path']        = '';
		}

		// title
		$oTitleGUI = new ilNonEditableValueGUI($this->lng->txt('title'));
		$oTitleGUI->setValue($tmp_object['title']);
		$oForm->addItem($oTitleGUI);

		// description
		$oDescriptionGUI = new ilNonEditableValueGUI($this->lng->txt('description'));
		$oDescriptionGUI->setValue($tmp_object['description']);
		$oForm->addItem($oDescriptionGUI);

		// owner
		$oOwnerGUI = new ilNonEditableValueGUI($this->lng->txt('owner'));
		$oOwnerGUI->setValue($tmp_object['owner']);
		$oForm->addItem($oOwnerGUI);

		// repository path
		$oPathGUI = new ilNonEditableValueGUI($this->lng->txt('path'));
		$oPathGUI->setValue($tmp_object['path']);
		$oForm->addItem($oPathGUI);

		// vendors
		$oVendorsGUI = new ilSelectInputGUI($this->lng->txt('paya_vendor'), 'vendor');
		$oVendorsGUI->setOptions($this->__getVendors());
		$oForm->addItem($oVendorsGUI);

		// buttons
		$oForm->addCommandButton('addObject', $this->lng->txt('next'));
		$oForm->addCommandButton('showObjects', $this->lng->txt('cancel'));

		$this->tpl->setVariable('ADM_CONTENT', $oForm->getHTML());
		return true;
	}

	public function addObject()
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

		include_once 'Services/Payment/classes/class.ilPaymentObject.php';
		$p_obj = new ilPaymentObject($this->user_obj);

		if($check_subtypes = ilPaymentObject::_checkExcSubtype($_GET['sell_id']))
		{
			if(!in_array('download', $check_subtypes))
				$p_obj->setSubtype('download');
			else
				if(!in_array('upload', $check_subtypes))
					$p_obj->setSubtype('upload');


		}
		else
			if(ilPaymentObject::_isPurchasable($_GET['sell_id']))
			{
				// means that current object already exits in payment_objects _table ...
				ilUtil::sendInfo($this->lng->txt('paya_object_not_purchasable'));

				return $this->showObjectSelector();
			}

		$p_obj->setRefId((int)$_GET['sell_id']);
		$p_obj->setStatus($p_obj->STATUS_NOT_BUYABLE);
		$p_obj->setPayMethod($p_obj->PAY_METHOD_NOT_SPECIFIED);
		$p_obj->setVendorId((int)$_POST['vendor']);
		$p_obj->setTopicId((int)$_POST['topic_id']);
		$p_obj->setVatId((int)$_POST['vat_id']);

		$new_id = $p_obj->add();
		if($new_id)
		{
			ilUtil::sendInfo($this->lng->txt('paya_added_new_object'));
			$_GET['pobject_id'] = $new_id;
			$this->editPrices();
			return true;
		}
		else
		{
			ilUtil::sendInfo($this->lng->txt('paya_err_adding_object'));
			return $this->showObjects();
		}
	}

	private function __getVendors()
	{
		include_once 'Services/Payment/classes/class.ilPaymentVendors.php';

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
			/** @var $tmp_obj ilObjUser */
			$tmp_obj          = ilObjectFactory::getInstanceByObjId($vendor, false);
			$options[$vendor] = $tmp_obj->getFullname() . ' [' . $tmp_obj->getLogin() . ']';
		}

		return $options;
	}


	private function __getStatus()
	{
		$option                                     = array();
		$option[$this->pobject->STATUS_NOT_BUYABLE] = $this->lng->txt('paya_not_buyable');
		$option[$this->pobject->STATUS_BUYABLE]     = $this->lng->txt('paya_buyable');
		$option[$this->pobject->STATUS_EXPIRES]     = $this->lng->txt('paya_expires');

		return $option;
	}

	private function __showObjectsTable($a_result_set)
	{
		$tbl = new ilShopTableGUI($this);
		$tbl->setTitle($this->lng->txt('objects'));

		$tbl->setId('tbl_objects');
		$tbl->setRowTemplate("tpl.shop_objects_row.html", "Services/Payment");

		$tbl->addColumn($this->lng->txt('title'), 'title', '10%');
		$tbl->addColumn($this->lng->txt('status'), 'status', '10%');
		$tbl->addColumn($this->lng->txt('paya_pay_method'), 'pay_method', '10%');
		$tbl->addColumn($this->lng->txt('vat_rate'), 'vat_rate', '15%');
		$tbl->addColumn($this->lng->txt('paya_vendor'), 'vendor', '10%');
		$tbl->addColumn($this->lng->txt('paya_count_purchaser'), 'purchasers', '10%');
		$tbl->addColumn('', 'options', '10%');

		$tbl->setData($a_result_set);

		$this->tpl->setVariable('TABLE', $tbl->getHTML());

		return true;
	}


	private function __getHTMLPath($a_ref_id)
	{
		/** 
		 * @var $tree ilTree */
		global $tree;

		$path = $tree->getPathFull($a_ref_id);
		unset($path[0]);

		$html = '';
		foreach($path as $data)
		{
			$html .= $data['title'] . ' > ';
		}
		return substr($html, 0, -2);
	}

	private function __initPaymentObject($a_pobject_id = 0)
	{
		include_once './Services/Payment/classes/class.ilPaymentObject.php';

		$this->pobject = new ilPaymentObject($this->user_obj, $a_pobject_id);

		return true;
	}

	public function editPrice()
	{
		$this->tpl->addBlockfile('ADM_CONTENT', 'adm_content', 'tpl.main_view.html', 'Services/Payment');

		$price_id = $_GET['price_id'] ? $_GET['price_id'] : $_POST['price_id'];
		$price    = ilPaymentPrices::_getPrice($price_id);


		include_once 'Services/Form/classes/class.ilPropertyFormGUI.php';
		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this));

		//price_type
		$radio_group = new ilRadioGroupInputGUI('', 'price_type');
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

		$now_date = date('Y-m-d');

		// duration_date from	
		$o_date_from = new ilDateTimeInputGUI();
		$o_date_from->setTitle($this->lng->txt('from'));

		$o_date_from->setDate(new ilDate($price['duration_from'] == NULL ? $now_date : $price['duration_from'], IL_CAL_DATE));
		$o_date_from->setPostVar('duration_date_from');
		$radio_option_3->addSubItem($o_date_from);

		// duration_date until
		$o_date_until = new ilDateTimeInputGUI();
		$o_date_until->setTitle($this->lng->txt('until'));
		$o_date_until->setDate(new ilDate($price['duration_until'] == NULL ? $now_date : $price['duration_until'], IL_CAL_DATE));
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
		$oPrice->allowDecimals(true);
		$oPrice->setRequired(true);
		$oPrice->setSize('20%');
		$oPrice->setValue($price['price']);
		include_once './Services/Payment/classes/class.ilPaymentSettings.php';
		/** @var $genSet ilPaymentSettings */
		$genSet = ilPaymentSettings::_getInstance();
		$oPrice->setInfo($genSet->get('currency_unit'));
		$oPrice->setPostVar('price');
		$oPrice->allowDecimals(true);
		$form->addItem($oPrice);

		//extension
		$oExtension = new ilCheckboxInputGUI($this->lng->txt('extension_price'), 'extension');
		$oExtension->setChecked((int)$price['extension']);
		$form->addItem($oExtension);

		$o_hidden_1 = new ilHiddenInputGUI('pobject_id');
		$o_hidden_1->setValue((int)$_GET['pobject_id']);
		$o_hidden_1->setPostVar('pobject_id');

		$o_hidden_2 = new ilHiddenInputGUI('price_id');
		$o_hidden_2->setValue((int)$_GET['price_id']);
		$o_hidden_2->setPostVar('price_id');

		$form->addItem($o_hidden_1);
		$form->addItem($o_hidden_2);

		$form->addCommandButton('updatePrice', $this->lng->txt('save'));
		$form->addCommandButton('editPrices', $this->lng->txt('cancel'));

		$this->tpl->setVariable('FORM', $form->getHTML());
	}

	public function updatePrice()
	{
		include_once './Services/Payment/classes/class.ilPaymentPrices.php';

		if(!$_GET['pobject_id'] && !$_POST['pobject_id'])
		{
			ilUtil::sendInfo($this->lng->txt('paya_no_object_selected'));

			$this->showObjects();
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
			return $this->editPrices();
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
				$po->setPriceType(ilPaymentPrices::TYPE_UNLIMITED_DURATION);
				$po->setDuration(NULL);
				$po->setDurationFrom(NULL);
				$po->setDurationUntil(NULL);
				$po->setUnlimitedDuration(1);

				break;

			case ilPaymentPrices::TYPE_DURATION_DATE:

				$po->setPriceType(ilPaymentPrices::TYPE_DURATION_DATE);
				$po->setDuration(NULL);
				$po->setDurationFrom(ilUtil::stripSlashes(
					$_POST['duration_date_from']['date']['y'] . '-' .
						$_POST['duration_date_from']['date']['m'] . '-' .
						$_POST['duration_date_from']['date']['d']));
				$po->setDurationUntil(ilUtil::stripSlashes(
					$_POST['duration_date_until']['date']['y'] . '-' .
						$_POST['duration_date_until']['date']['m'] . '-' .
						$_POST['duration_date_until']['date']['d']));
				break;

			default:
			case ilPaymentPrices::TYPE_DURATION_MONTH:
				$po->setPriceType(ilPaymentPrices::TYPE_DURATION_MONTH);
				$po->setDuration($_POST['duration_month']);
				$po->setDurationFrom(NULL);
				$po->setDurationUntil(NULL);
				break;
		}

		$po->setDescription($_POST['description'] ? ilUtil::stripSlashes($_POST['description']) : NULL);
		$po->setPrice(ilUtil::stripSlashes($_POST['price']));
		$po->setCurrency(ilUtil::stripSlashes($_POST['currency']));
		if($_POST['extension'])
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
			return $this->editPrices();
		}
		catch(ilShopException $e)
		{
			ilUtil::sendInfo($e->getMessage());
			return $this->editPrices();
		}
	}
}
