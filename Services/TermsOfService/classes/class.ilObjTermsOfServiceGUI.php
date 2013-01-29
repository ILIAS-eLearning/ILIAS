<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Object/classes/class.ilObjectGUI.php';
require_once 'Services/Form/classes/class.ilPropertyFormGUI.php';
require_once 'Services/Utilities/classes/class.ilConfirmationGUI.php';
require_once 'Services/TermsOfService/classes/class.ilTermsOfServiceTableDataProviderFactory.php';
require_once 'Services/TermsOfService/classes/class.ilTermsOfServiceAgreementByLanguageTableGUI.php';


/**
 * @author            Michael Jansen <mjansen@databay.de>
 * @version           $Id$
 * @ilCtrl_Calls      ilObjTermsOfServiceGUI: ilPermissionGUI
 * @ilCtrl_isCalledBy ilObjTermsOfServiceGUI: ilAdministrationGUI
 */
class ilObjTermsOfServiceGUI extends ilObjectGUI
{
	/**
	 * @var ilPropertyFormGUI
	 */
	protected $form;

	/**
	 * @var ilLanguage
	 */
	public $lng;

	/**
	 * @var ilCtrl
	 */
	public $ctrl;

	/**
	 * @var ilObjTermsOfService
	 */
	public $object;

	/**
	 * @var ilTabsGUI
	 */
	public $tabs_gui;

	/**
	 * @var ilTermsOfServiceTableDataProviderFactory
	 */
	public $factory;

	/**
	 * @param      $a_data
	 * @param      $a_id
	 * @param      $a_call_by_reference
	 * @param bool $a_prepare_output
	 */
	public function __construct($a_data, $a_id, $a_call_by_reference, $a_prepare_output = true)
	{
		/**
		 * @var $lng ilLanguage
		 */
		global $lng;

		$this->type = 'tos';
		parent::__construct($a_data, $a_id, $a_call_by_reference, $a_prepare_output);
		$this->lng->loadLanguageModule('tos');

		$this->factory = new ilTermsOfServiceTableDataProviderFactory();
		$this->factory->setLanguageAdapter($lng);
	}

	/**
	 *
	 */
	public function executeCommand()
	{
		$next_class = $this->ctrl->getNextClass($this);
		$cmd        = $this->ctrl->getCmd();
		$this->prepareOutput();

		switch($next_class)
		{
			case 'ilpermissiongui':
				$this->tabs_gui->setTabActive('perm_settings');
				require_once 'Services/AccessControl/classes/class.ilPermissionGUI.php';
				$perm_gui = new ilPermissionGUI($this);
				$this->ctrl->forwardCommand($perm_gui);
				break;

			default:
				if($cmd == '' || $cmd == 'view')
				{
					$cmd = 'settings';
				}
				$this->$cmd();
				break;
		}
	}

	/**
	 * @param ilTabsGUI $tabs_gui
	 */
	public function getAdminTabs(ilTabsGUI $tabs_gui)
	{
		/**
		 * @var $rbacsystem ilRbacSystem
		 */
		global $rbacsystem;

		if($rbacsystem->checkAccess('read', $this->object->getRefId()))
		{
			$tabs_gui->addTarget('settings', $this->ctrl->getLinkTarget($this, 'settings'), array('saveSettings', 'settings', '', 'view'), '', '');
		}

		if($rbacsystem->checkAccess('read', $this->object->getRefId()))
		{
			$tabs_gui->addTarget('tos_agreement_by_lng', $this->ctrl->getLinkTarget($this, 'showAgreementByLanguage'), array('reset', 'confirmReset', 'showAgreementByLanguage', 'resetAgreementByLanguageFilter', 'applyAgreementByLanguageFilter'), '', '');
		}

		if($rbacsystem->checkAccess('edit_permission', $this->object->getRefId()))
		{
			$tabs_gui->addTarget('perm_settings', $this->ctrl->getLinkTargetByClass(array(get_class($this), 'ilpermissiongui'), 'perm'), array('perm', 'info', 'owner'), 'ilpermissiongui');
		}
	}

	/**
	 *
	 */
	protected function initSettingsForm()
	{
		if(null == $this->form)
		{
			$this->form = new ilPropertyFormGUI();
			$this->form->setTitle($this->lng->txt('tos_tos_settings'));
			$this->form->setFormAction($this->ctrl->getFormAction($this, 'saveSettings'));

			$status = new ilCheckboxInputGUI($this->lng->txt('tos_status_enable'), 'tos_status');
			$status->setInfo($this->lng->txt('tos_status_desc'));
			$this->form->addItem($status);

			$this->form->addCommandButton('saveSettings', $this->lng->txt('save'));
		}
	}

	/**
	 *
	 */
	protected function saveSettings()
	{
		/**
		 * @var $ilAccess ilAccessHandler
		 * @var $ilErr    ilErrorHandling
		 */
		global $ilAccess, $ilErr;

		if(!$ilAccess->checkAccess('write', '', $this->object->getRefId()))
		{
			$ilErr->raiseError($this->lng->txt('permission_denied'), $ilErr->MESSAGE);
		}

		$this->initSettingsForm();
		if($this->form->checkInput())
		{
			$this->object->saveStatus((int)$this->form->getInput('tos_status'));
			ilUtil::sendSuccess($this->lng->txt('saved_successfully'));
		}

		$this->settings(false);
	}

	/**
	 * @param bool $init_from_database
	 */
	protected function settings($init_from_database = true)
	{
		/**
		 * @var $ilAccess  ilAccessHandler
		 * @var $ilErr     ilErrorHandling
		 * @var $tpl       ilTemplate
		 */
		global $ilAccess, $ilErr, $tpl;

		if(!$ilAccess->checkAccess('read', '', $this->object->getRefId()))
		{
			$ilErr->raiseError($this->lng->txt('permission_denied'), $ilErr->MESSAGE);
		}

		$this->initSettingsForm();
		if($init_from_database)
		{
			$this->form->setValuesByArray(array(
				'tos_status' => $this->object->getStatus()
			));
		}
		else
		{
			$this->form->setValuesByPost();
		}

		$tpl->setContent($this->form->getHtml());
	}

	/**
	 *
	 */
	protected function confirmReset()
	{
		/**
		 * @var $ilAccess ilAccessHandler
		 * @var $ilErr    ilErrorHandling
		 * @var $tpl      ilTemplate
		 */
		global $ilAccess, $ilErr, $tpl;

		if(!$ilAccess->checkAccess('write', '', $this->object->getRefId()))
		{
			$ilErr->raiseError($this->lng->txt('permission_denied'), $ilErr->MESSAGE);
		}

		$confirmation = new ilConfirmationGUI();
		$confirmation->setFormAction($this->ctrl->getFormAction($this, 'confirmReset'));
		$confirmation->setConfirm($this->lng->txt('confirm'), 'reset');
		$confirmation->setCancel($this->lng->txt('cancel'), 'showAgreementByLanguage');
		$confirmation->setHeaderText($this->lng->txt('tos_sure_reset_tos'));

		$tpl->setContent($confirmation->getHtml());
	}

	/**
	 *
	 */
	protected function reset()
	{
		/**
		 * @var $ilAccess ilAccessHandler
		 * @var $ilErr    ilErrorHandling
		 * @var $ilLog    ilLog
		 * @var $ilUser   ilObjUser
		 */
		global $ilAccess, $ilErr, $ilLog, $ilUser;

		if(!$ilAccess->checkAccess('write', '', $this->object->getRefId()))
		{
			$ilErr->raiseError($this->lng->txt('permission_denied'), $ilErr->MESSAGE);
		}

		$this->object->resetAll();
		$ilLog->write(__METHOD__ . ': Terms of service reset by ' . $ilUser->getId() . ' [' . $ilUser->getLogin() . ']');
		ilUtil::sendSuccess($this->lng->txt('tos_reset_successfull'));

		$this->showAgreementByLanguage();
	}

	/**
	 *
	 */
	protected function showAgreementByLanguage()
	{
		/**
		 * @var $ilAccess  ilAccessHandler
		 * @var $ilErr     ilErrorHandling
		 * @var $tpl       ilTemplate
		 * @var $ilToolbar ilToolbarGUI
		 */
		global $ilAccess, $ilErr, $tpl, $ilToolbar;

		if(!$ilAccess->checkAccess('read', '', $this->object->getRefId()))
		{
			$ilErr->raiseError($this->lng->txt('permission_denied'), $ilErr->MESSAGE);
		}

		$this->lng->loadLanguageModule('meta');

		if($ilAccess->checkAccess('write', '', $this->object->getRefId()))
		{
			$ilToolbar->setFormAction($this->ctrl->getFormAction($this, 'settings'));
			$ilToolbar->addFormButton($this->lng->txt('tos_reset_tos_for_all_users'), 'confirmReset');
		}

		$this->showLastResetDate();

		$table = new ilTermsOfServiceAgreementByLanguageTableGUI($this, 'showAgreementByLanguage');
		$table->setProvider($this->factory->getByContext(ilTermsOfServiceTableDataProviderFactory::CONTEXT_AGRREMENT_BY_LANGUAGE));
		$table->populate();

		$tpl->setContent($table->getHtml());
	}

	/**
	 *
	 */
	protected function showLastResetDate()
	{
		/**
		 * @var $ilToolbar ilToolbarGUI
		 */
		global $ilToolbar;

		if($this->object->getLastResetDate() && $this->object->getLastResetDate()->get(IL_CAL_UNIX) != 0)
		{
			$status = ilDatePresentation::useRelativeDates();
			ilDatePresentation::setUseRelativeDates(false);
			$ilToolbar->addText(sprintf($this->lng->txt('tos_last_reset_date'), ilDatePresentation::formatDate($this->object->getLastResetDate())));
			ilDatePresentation::setUseRelativeDates($status);
		}
	}

	/**
	 *
	 */
	protected function applyAgreementByLanguageFilter()
	{
		$table = new ilTermsOfServiceAgreementByLanguageTableGUI($this, 'showAgreementByLanguage');
		$table->resetOffset();
		$table->writeFilterToSession();

		$this->showAgreementByLanguage();
	}

	/**
	 *
	 */
	protected function resetAgreementByLanguageFilter()
	{
		$table = new ilTermsOfServiceAgreementByLanguageTableGUI($this, 'showAgreementByLanguage');
		$table->resetOffset();
		$table->resetFilter();

		$this->showAgreementByLanguage();
	}

	/**
	 *
	 */
	protected function showAgreementTextAsynch()
	{
		/**
		 * @var $ilAccess ilAccessHandler
		 */
		global $ilAccess;

		if(!isset($_GET['agreement_file']) || !strlen($_GET['agreement_file']) || !$ilAccess->checkAccess('read', '', $this->object->getRefId()))
		{
			exit();
		}

		$file = realpath(strip_tags(rawurldecode(ilUtil::stripOnlySlashes($_GET['agreement_file']))));
		if(preg_match('/Customizing[\/\\\](global[\/\\\]agreement|clients[\/\\\]' . CLIENT_ID . '[\/\\\]agreement)[\/\\\]agreement_([a-z]{2})\.html$/', $file))
		{
			echo '<div style="overflow:auto;max-width:640px;max-height:480px;padding:5px">' . nl2br(trim(file_get_contents($file))) . '</div>';
		}

		exit();
	}
}