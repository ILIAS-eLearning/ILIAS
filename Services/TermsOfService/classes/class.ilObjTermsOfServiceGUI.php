<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Object/classes/class.ilObject2GUI.php';
require_once 'Services/Form/classes/class.ilPropertyFormGUI.php';
require_once 'Services/Utilities/classes/class.ilConfirmationGUI.php';
require_once 'Services/TermsOfService/classes/class.ilTermsOfServiceTableDataProviderFactory.php';
require_once 'Services/TermsOfService/classes/class.ilTermsOfServiceAgreementByLanguageTableGUI.php';
require_once 'Services/TermsOfService/classes/class.ilTermsOfServiceAcceptanceHistoryTableGUI.php';

/**
 * @author            Michael Jansen <mjansen@databay.de>
 * @version           $Id$
 * @ilCtrl_Calls      ilObjTermsOfServiceGUI: ilPermissionGUI
 * @ilCtrl_isCalledBy ilObjTermsOfServiceGUI: ilAdministrationGUI
 */
class ilObjTermsOfServiceGUI extends ilObject2GUI
{
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
	 * @var ilRbacSystem
	 */
	protected $rbacsystem;

	/**
	 * @var ilErrorHandling
	 */
	protected $error;
	
	/**
	 * @var ilToolbarGUI
	 */
	protected $toolbar;

	/**
	 * @var ilObjUser
	 */
	protected $user;

	/**
	 * @var ilLog
	 */
	protected $log;

	/**
	 * @var ILIAS\UI\Factory
	 */
	protected $uiFactory;

	/**
	 * @var ILIAS\UI\Renderer
	 */
	protected $uiRenderer;

	/**
	 * @param int $a_id
	 * @param int $a_id_type
	 * @param int $a_parent_node_id
	 */
	public function __construct($a_id = 0, $a_id_type = self::REPOSITORY_NODE_ID, $a_parent_node_id = 0)
	{
		/**
		 * @var $lng  ilLanguage
		 * @var $ilDB ilDBInterface
		 */
		global $DIC;

		$this->lng        = $DIC['lng'];
		$this->rbacsystem = $DIC['rbacsystem'];
		$this->error      = $DIC['ilErr'];
		$this->log        = $DIC['ilLog'];
		$this->toolbar    = $DIC['ilToolbar'];
		$this->user       = $DIC['ilUser'];

		$this->uiFactory  = $DIC->ui()->factory();
		$this->uiRenderer = $DIC->ui()->renderer();

		parent::__construct($a_id, $a_id_type, $a_parent_node_id);

		$this->lng->loadLanguageModule('tos');
		$this->lng->loadLanguageModule('meta');

		$this->factory = new ilTermsOfServiceTableDataProviderFactory();
		$this->factory->setLanguageAdapter($this->lng);
		$this->factory->setDatabaseAdapter($DIC['ilDB']);
	}

	/**
	 * Functions that must be overwritten
	 */
	public function getType()
	{
		return 'tos';
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
				$perm_gui = new \ilPermissionGUI($this);
				$this->ctrl->forwardCommand($perm_gui);
				break;

			default:
				if ($cmd == '' || $cmd == 'view' || !method_exists($this, $cmd)) {
					$cmd = 'settings';
				}
				$this->$cmd();
				break;
		}
	}

	/**
	 * 
	 */
	public function getAdminTabs()
	{
		if ($this->rbacsystem->checkAccess('read', $this->object->getRefId())) {
			$this->tabs_gui->addTarget('settings', $this->ctrl->getLinkTarget($this, 'settings'), array('saveSettings', 'settings', '', 'view'), '', '');
		}

		if ($this->rbacsystem->checkAccess('read', $this->object->getRefId())) {
			$this->tabs_gui->addTarget('tos_agreement_documents_tab_label', $this->ctrl->getLinkTarget($this, 'showDocuments'), array('reset', 'confirmReset', 'showDocuments'), '', '');
		}

		if($this->rbacsystem->checkAccess('read', $this->object->getRefId()) &&
			$this->rbacsystem->checkAccess('read', USER_FOLDER_ID)
		)
		{
			$this->tabs_gui->addTarget('tos_acceptance_history', $this->ctrl->getLinkTarget($this, 'showAcceptanceHistory'), array('showAcceptanceHistory', 'resetAcceptanceHistoryFilter', 'applyAcceptanceHistoryFilter'), '', '');
		}

		if ($this->rbacsystem->checkAccess('edit_permission', $this->object->getRefId())) {
			$this->tabs_gui->addTarget('perm_settings', $this->ctrl->getLinkTargetByClass(array(get_class($this), 'ilpermissiongui'), 'perm'), array('perm', 'info', 'owner'), 'ilpermissiongui');
		}
	}

	/**
	 * @return ilTermsOfServiceSettingsFormGUI
	 */
	protected function getSettingsForm()
	{
		$form = new ilTermsOfServiceSettingsFormGUI(
			$this->object,
			$this->lng,
			'saveSettings',
			$this->rbacsystem->checkAccess('write', $this->object->getRefId())
		);
		$form->setFormAction($this->ctrl->getLinkTarget($this, 'saveSettings'));

		return $form;
	}

	/**
	 *
	 */
	protected function saveSettings()
	{
		if (!$this->rbacsystem->checkAccess('write', $this->object->getRefId())) {
			$this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
		}

		$form = $this->getSettingsForm();
		if ($form->saveObject()) {
			\ilUtil::sendSuccess($this->lng->txt('saved_successfully'), true);
			$this->ctrl->redirect($this, 'settings');
		} else if ($form->hasTranslatedError()) {
			\ilUtil::sendFailure($form->getTranslatedError());
		}

		$this->tpl->setContent($form->getHTML());
	}

	/**
	 *
	 */
	protected function showMissingDocuments()
	{
		if (!$this->object->getStatus()) {
			return;
		}

		// TODO: Count total documents
		$hasDocuments = true;

		if (!$hasDocuments) {
			\ilUtil::sendInfo($this->lng->txt('tos_no_documents_exist'));
		}
	}

	/**
	 *
	 */
	protected function settings()
	{
		if (!$this->rbacsystem->checkAccess('read', $this->object->getRefId())) {
			$this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
		}

		$this->showMissingDocuments();

		$form = $this->getSettingsForm();
		$this->tpl->setContent($form->getHTML());
	}

	/**
	 *
	 */
	protected function confirmReset()
	{
		if (!$this->rbacsystem->checkAccess('write', $this->object->getRefId())) {
			$this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
		}

		$confirmation = new \ilConfirmationGUI();
		$confirmation->setFormAction($this->ctrl->getFormAction($this, 'confirmReset'));
		$confirmation->setConfirm($this->lng->txt('confirm'), 'reset');
		$confirmation->setCancel($this->lng->txt('cancel'), 'showDocuments');
		$confirmation->setHeaderText($this->lng->txt('tos_sure_reset_tos'));

		$this->tpl->setContent($confirmation->getHTML());
	}

	/**
	 *
	 */
	protected function reset()
	{
		if (!$this->rbacsystem->checkAccess('write', $this->object->getRefId())) {
			$this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
		}

		$this->object->resetAll();

		$this->log->write(__METHOD__ . ': Terms of service reset by ' . $this->user->getId() . ' [' . $this->user->getLogin() . ']');
		\ilUtil::sendSuccess($this->lng->txt('tos_reset_successful'));

		$this->showDocuments();
	}

	/**
	 * 
	 */
	protected function showDocuments()
	{
		if (!$this->rbacsystem->checkAccess('read', $this->object->getRefId())) {
			$this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
		}

		$addDocumentBtn = \ilLinkButton::getInstance();
		$addDocumentBtn->setUrl($this->ctrl->getLinkTarget($this, 'showAddDocumentForm'));
		$addDocumentBtn->setCaption('tos_add_document_btn_label');
		$this->toolbar->addStickyItem($addDocumentBtn);

		$this->tpl->setVariable('MESSAGE', $this->getResetMessageBoxHtml());
		$this->tpl->setContent(implode('', [
			// TODO: Add table HTML 
		]));
	}

	/**
	 *
	 */
	protected function getAgreementTextByFilenameAsynch()
	{
		require_once 'Services/TermsOfService/classes/class.ilTermsOfServiceJsonResponse.php';
		$response = new ilTermsOfServiceJsonResponse();

		if(
			!isset($_GET['agreement_document']) ||
			!strlen($_GET['agreement_document']) ||
			!$this->rbacsystem->checkAccess('read', $this->object->getRefId()))
		{
			$response->setStatus(ilTermsOfServiceJsonResponse::STATUS_FAILURE);
			echo $response;
		}

		$file = realpath(strip_tags(rawurldecode(ilUtil::stripOnlySlashes($_GET['agreement_document']))));
		if(preg_match('/Customizing[\/\\\](global[\/\\\]agreement|clients[\/\\\]' . CLIENT_ID . '[\/\\\]agreement)[\/\\\]agreement_([a-z]{2})\.html$/', $file))
		{
			$content = file_get_contents($file);
			if(strip_tags($content) === $content)
			{
				$content       = '';
				$lines         = file($file);
				foreach($lines as $line)
				{
					$content .= nl2br(trim($line));
				}
			}
			$response->setBody($content);
		}
		else
		{
			$response->setStatus(ilTermsOfServiceJsonResponse::STATUS_FAILURE);
		}

		echo $response;
	}

	/**
	 *
	 */
	protected function showAcceptanceHistory()
	{
		if(
			!$this->rbacsystem->checkAccess('read', '', $this->object->getRefId()) ||
			!$this->rbacsystem->checkAccess('read', '', USER_FOLDER_ID)
		)
		{
			$this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
		}

		$this->lng->loadLanguageModule('meta');

		$table = new ilTermsOfServiceAcceptanceHistoryTableGUI($this, 'showAcceptanceHistory');
		$table->setProvider($this->factory->getByContext(ilTermsOfServiceTableDataProviderFactory::CONTEXT_ACCEPTANCE_HISTORY));
		$table->populate();

		$this->tpl->setContent($table->getHTML());
	}

	/**
	 * 
	 */
	protected function getAcceptedContentAsynch()
	{
		require_once 'Services/TermsOfService/classes/class.ilTermsOfServiceJsonResponse.php';
		$response = new ilTermsOfServiceJsonResponse();

		if(!isset($_GET['tosv_id']))
		{
			$response->setStatus(ilTermsOfServiceJsonResponse::STATUS_FAILURE);
			echo $response;
		}

		$entity = ilTermsOfServiceHelper::getById(ilUtil::stripSlashes($_GET['tosv_id']));
		$response->setBody($entity->getText());

		echo $response;
	}

	/**
	 * Show auto complete results
	 */
	protected function addUserAutoComplete()
	{
		if(
			!$this->rbacsystem->checkAccess('read', '', $this->object->getRefId()) ||
			!$this->rbacsystem->checkAccess('read', '', USER_FOLDER_ID)
		)
		{
			echo json_encode(array());
			exit();
		}
		
		include_once 'Services/User/classes/class.ilUserAutoComplete.php';
		$auto = new ilUserAutoComplete();
		$auto->setSearchFields(array('login', 'firstname', 'lastname', 'email'));
		$auto->enableFieldSearchableCheck(false);
		$auto->setMoreLinkAvailable(true);

		if(($_REQUEST['fetchall']))
		{
			$auto->setLimit(ilUserAutoComplete::MAX_ENTRIES);
		}

		echo $auto->getList($_REQUEST['term']);
		exit();
	}

	/**
	 * 
	 */
	protected function applyAcceptanceHistoryFilter()
	{
		$table = new ilTermsOfServiceAcceptanceHistoryTableGUI($this, 'showAcceptanceHistory');
		$table->resetOffset();
		$table->writeFilterToSession();

		$this->showAcceptanceHistory();
	}

	/**
	 * 
	 */
	protected function resetAcceptanceHistoryFilter()
	{
		$table = new ilTermsOfServiceAcceptanceHistoryTableGUI($this, 'showAcceptanceHistory');
		$table->resetOffset();
		$table->resetFilter();

		$this->showAcceptanceHistory();
	}

	/**
	 * @return string
	 */
	protected function getResetMessageBoxHtml(): string
	{
		if ($this->object->getLastResetDate() && $this->object->getLastResetDate()->get(IL_CAL_UNIX) != 0) {
			$status = ilDatePresentation::useRelativeDates();
			ilDatePresentation::setUseRelativeDates(false);
			$resetText = sprintf(
				$this->lng->txt('tos_last_reset_date'),
				ilDatePresentation::formatDate($this->object->getLastResetDate())
			);
			ilDatePresentation::setUseRelativeDates($status);
		} else {
			$resetText = $this->lng->txt('tos_never_reset');
		}

		$buttons = [];
		if($this->rbacsystem->checkAccess('write', $this->object->getRefId())) {
			$buttons = [
				$this->uiFactory
					->button()
					->standard($this->lng->txt('tos_reset_tos_for_all_users'), $this->ctrl->getLinkTarget($this, 'confirmReset'))
			];
		}

		return $this->uiRenderer->render(
			$this->uiFactory->messageBox()
				->info($resetText)
				->withButtons($buttons)
		);
	}
}