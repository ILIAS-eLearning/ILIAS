<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* @author Jens Conze
* @version $Id$
*
* @ingroup ServicesMail
* @ilCtrl_Calls ilMailFolderGUI: ilMailOptionsGUI, ilMailAttachmentGUI, ilMailSearchGUI
* @ilCtrl_Calls ilMailFolderGUI: ilPublicUserProfileGUI
*/
class ilMailFolderGUI
{
	/** @var bool */
	private $confirmTrashDeletion = false;

	/** @var bool */
	private $errorDelete = false;

	/** @var \ilTemplate */
	private $tpl;

	/** @var \ilCtrl */
	private $ctrl;

	/** @var \ilLanguage */
	private $lng;

	/** @var \ilToolbarGUI */
	private $toolbar;

	/** @var \ilTabsGUI */
	private $tabs;

	/** @var \ilObjUser */
	private $user;

	/** @var \ilMail */
	public $umail;

	/** @var \ilMailBox */
	public $mbox;

	/** @var \Psr\Http\Message\ServerRequestInterface */
	private $httpRequest;
	
	/** @var int */
	private $currentFolderId = 0;

	/**
	 * ilMailFolderGUI constructor.
	 */
	public function __construct()
	{
		global $DIC;

		$this->tpl      = $DIC->ui()->mainTemplate();
		$this->ctrl     = $DIC->ctrl();
		$this->lng      = $DIC->language();
		$this->toolbar  = $DIC->toolbar();
		$this->user     = $DIC->user();
		$this->tabs     = $DIC->tabs();
		$this->httpRequest = $DIC->http()->request();

		$this->umail = new ilMail($this->user->getId());
		$this->mbox  = new ilMailbox($this->user->getId());

		$this->initFolder();
	}

	/**
	 * 
	 */
	protected function initFolder()
	{
		$folderId = $this->httpRequest->getParsedBody()['mobj_id'] ?? 0;
		if (!is_numeric($folderId) || 0 == $folderId) {
			$folderId = $this->httpRequest->getQueryParams()['mobj_id'] ?? 0;
		}

		if (!is_numeric($folderId) || 0 == $folderId || !$this->mbox->isOwnedFolder($folderId)) {
			$folderId = $this->mbox->getInboxFolder();
		}

		$this->currentFolderId = (int)$folderId;
	}

	/**
	 * @param string $originalCommand
	 * @return string
	 */
	protected function parseCommand(string $originalCommand): string 
	{
		$matches = [];
		if (preg_match('/^([a-zA-Z0-9]+?)_(\d+?)$/', $originalCommand, $matches) && 3 === count($matches)) {
			$originalCommand = $matches[1];
		}

		return $originalCommand;
	}

	/**
	 * @param string $command
	 * @return int
	 * @throws \InvalidArgumentException              
	 */
	protected function parseFolderIdFromCommand(string $command): int
	{
		$matches = [];
		if (
			preg_match('/^([a-zA-Z0-9]+?)_(\d+?)$/', $command, $matches) &&
			3 === count($matches) && is_numeric($matches[2])
		) {
			return (int)$matches[2];
		}

		throw new \InvalidArgumentException("Cannot parse a numeric folder id from command string!");
	}

	/**
	 * 
	 */
	public function executeCommand()
	{
		$cmd = $this->parseCommand(
			$this->ctrl->getCmd()
		);

		$nextClass = $this->ctrl->getNextClass($this);
		switch($nextClass) {
			case 'ilcontactgui':
				$this->ctrl->forwardCommand(new \ilContactGUI());
				break;

			case 'ilmailoptionsgui':
				$this->tpl->setTitle($this->lng->txt('mail'));
				$this->ctrl->forwardCommand(new \ilMailOptionsGUI());
				break;

			case 'ilpublicuserprofilegui':
				$this->tpl->setTitle($this->lng->txt('mail'));
				$profileGui = new \ilPublicUserProfileGUI((int)($this->httpRequest->getQueryParams()['user'] ?? 0));

				$this->ctrl->setParameter($this, 'mobj_id', $this->currentFolderId);
				$profileGui->setBackUrl($this->ctrl->getLinkTarget($this, 'showMail'));
				$this->ctrl->clearParameters($this);

				$ret = $this->ctrl->forwardCommand($profileGui);
				if ($ret != '') {
					$this->tpl->setContent($ret);
				}
				$this->tpl->show();
				break;

			default:
				if (!method_exists($this, $cmd)) {
					$cmd = 'showFolder';
				}
				$this->{$cmd}();
				break;
		}
	}

	/**
	 * Called if the deletion of all messages in trash was confirmed by the acting user
	 */
	protected function performEmptyTrash()
	{
		$this->umail->deleteMailsOfFolder($this->currentFolderId); 

		\ilUtil::sendSuccess($this->lng->txt('mail_deleted'), true);
		$this->ctrl->setParameter($this, 'mobj_id', $this->currentFolderId);
		$this->ctrl->redirect($this, 'showFolder');
	}

	/**
	 * Called if the deletion of messages in trash should be confirmed by the acting user
	 */
	protected function confirmEmptyTrash()
	{
		if ($this->umail->countMailsOfFolder($this->currentFolderId)) {
			$this->confirmTrashDeletion = true;
		}

		$this->showFolder();
	}

	/**
	 * @throws ilCtrlException
	 */
	protected function showUser()
	{
		$this->tpl->setVariable('TBL_TITLE', implode(' ', [
			$this->lng->txt('profile_of'),
			\ilObjUser::_lookupLogin((int)($this->httpRequest->getQueryParams()['user'] ?? 0))
		]));
		$this->tpl->setVariable('TBL_TITLE_IMG',ilUtil::getImagePath('icon_usr.svg'));
		$this->tpl->setVariable('TBL_TITLE_IMG_ALT', $this->lng->txt('public_profile'));

		$profile_gui = new \ilPublicUserProfileGUI((int)($this->httpRequest->getQueryParams()['user'] ?? 0));

		$this->ctrl->setParameter($this, 'mail_id', (int)$this->httpRequest->getQueryParams()['mail_id']);
		$this->ctrl->setParameter($this, 'mobj_id', $this->currentFolderId);
		$profile_gui->setBackUrl($this->ctrl->getLinkTarget($this, 'showMail'));
		$this->ctrl->clearParameters($this);

		$this->tpl->setTitle($this->lng->txt('mail'));
		$this->tpl->setContent($this->ctrl->getHTML($profile_gui));
		$this->tpl->show();
	}

	/**
	 * @param bool $isUserSubFolder
	 */
	protected function addSubFolderCommands(bool $isUserSubFolder = false)
	{
		if ('tree' !== ilSession::get(ilMailGUI::VIEWMODE_SESSION_KEY)) {
			$this->toolbar->addSeparator();
		}

		$this->ctrl->setParameter($this, 'mobj_id', $this->currentFolderId);
		$this->toolbar->addButton($this->lng->txt('mail_add_subfolder'), $this->ctrl->getLinkTarget($this, 'addSubFolder'));

		if ($isUserSubFolder) {
			$this->toolbar->addButton($this->lng->txt('rename'), $this->ctrl->getLinkTarget($this, 'renameSubFolder'));
			$this->toolbar->addButton($this->lng->txt('delete'), $this->ctrl->getLinkTarget($this, 'deleteSubFolder'));
		}
		$this->ctrl->clearParameters($this);
	}

	/**
	 * Shows current folder. Current Folder is determined by $_GET["mobj_id"]
	 * @param bool $oneConfirmationDialogueRendered
	 */
	protected function showFolder(bool $oneConfirmationDialogueRendered = false)
	{
		$this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.mail.html', 'Services/Mail');
		$this->tpl->setTitle($this->lng->txt('mail'));

		$sentFolderId = $this->mbox->getSentFolder();
		$draftsFolderId = $this->mbox->getDraftsFolder();

		$isTrashFolder = $this->currentFolderId == $this->mbox->getTrashFolder();
		$isSentFolder = $this->currentFolderId == $sentFolderId;
		$isDraftFolder = $this->currentFolderId == $draftsFolderId;

		if ($isTrashFolder && 'deleteMails' === $this->parseCommand($this->ctrl->getCmd()) && !$this->errorDelete) {
			$confirmationGui = new \ilConfirmationGUI();
			$confirmationGui->setHeaderText($this->lng->txt('mail_sure_delete'));
			foreach ($this->getMailIdsFromRequest() as $mailId) {
				$confirmationGui->addHiddenItem('mail_id[]', $mailId);
			}
			$this->ctrl->setParameter($this, 'mobj_id', $this->currentFolderId);
			$confirmationGui->setFormAction($this->ctrl->getFormAction($this, 'showFolder'));
			$this->ctrl->clearParameters($this);
			$confirmationGui->setConfirm($this->lng->txt('confirm'), 'confirmDeleteMails');
			$confirmationGui->setCancel($this->lng->txt('cancel'), 'showFolder');
			$this->tpl->setVariable('CONFIRMATION', $confirmationGui->getHTML());
			$oneConfirmationDialogueRendered = true;
		}

		$folders = $this->mbox->getSubFolders();
		$mtree = new \ilTree($this->user->getId());
		$mtree->setTableNames('mail_tree', 'mail_obj_data');

		$isUserSubFolder = false;
		$isUserRootFolder = false;

		if ('tree' === \ilSession::get(\ilMailGUI::VIEWMODE_SESSION_KEY)) {
			$folder_d = $mtree->getNodeData($this->currentFolderId);
			if($folder_d['m_type'] === 'user_folder') {
				$isUserSubFolder = true;
			} elseif ($folder_d['m_type'] === 'local') {
				$isUserRootFolder = true;
			}
		}

		$mailtable = new ilMailFolderTableGUI($this, $this->currentFolderId, 'showFolder');
		$mailtable->isSentFolder($isSentFolder)
			->isDraftFolder($isDraftFolder)
			->isTrashFolder($isTrashFolder)
			->setSelectedItems($this->getMailIdsFromRequest(true))
			->initFilter();

		try {
			$mailtable->prepareHTML();
		} catch (\Exception $e) {
			\ilUtil::sendFailure(
				$this->lng->txt($e->getMessage()) != '-' . $e->getMessage() . '-' ?
					$this->lng->txt($e->getMessage()) :
					$e->getMessage()
			);
		}

		$table_html = $mailtable->getHtml();

		$folder_options = array();
		if ('tree' !== \ilSession::get(\ilMailGUI::VIEWMODE_SESSION_KEY)) {
			foreach ($folders as $folder) {
				$folder_d = $mtree->getNodeData($folder['obj_id']);

				if ($folder['obj_id'] == $this->currentFolderId) {
					if ($folder['type'] === 'user_folder') {
						$isUserSubFolder = true;
					} else {
						if ($folder['type'] === 'local') {
							$isUserRootFolder = true;
							$isUserSubFolder  = false;
						}
					}
				}

				$folder_options[$folder['obj_id']] = sprintf(
					$this->lng->txt('mail_change_to_folder'),
					$this->lng->txt('mail_' . $folder['title'])
				);
				if ($folder['type'] === 'user_folder') {
					$pre = '';
					for ($i = 2; $i < $folder_d['depth'] - 1; $i++) {
						$pre .= '&nbsp;';
					}

					if ($folder_d['depth'] > 1) {
						$pre .= '+';
					}

					$folder_options[$folder['obj_id']] = sprintf(
						$this->lng->txt('mail_change_to_folder'),
						$pre . ' ' . $folder['title']
					);
				}
			}
		}

		if ($oneConfirmationDialogueRendered === false && $this->confirmTrashDeletion === false) {
			if('tree' !== \ilSession::get(\ilMailGUI::VIEWMODE_SESSION_KEY)) {
				$si = new \ilSelectInputGUI('', 'mobj_id');
				$si->setOptions($folder_options);
				$si->setValue($this->currentFolderId);
				$this->toolbar->addStickyItem($si);

				$btn = ilSubmitButton::getInstance();
				$btn->setCaption('change');
				$btn->setCommand('showFolder');
				$this->toolbar->addStickyItem($btn);
				$this->toolbar->setFormAction($this->ctrl->getFormAction($this, 'showFolder'));
			}
			if ($isUserRootFolder == true || $isUserSubFolder == true) {
				$this->addSubFolderCommands($isUserSubFolder);
			}
		}

		if ($mailtable->isTrashFolder() && $mailtable->getNumberOfMails() > 0 && $this->confirmTrashDeletion === true) {
			$confirmationGui = new \ilConfirmationGUI();
			$confirmationGui->setHeaderText($this->lng->txt('mail_empty_trash_confirmation'));
			$this->ctrl->setParameter($this, 'mobj_id', $this->currentFolderId);
			$confirmationGui->setFormAction($this->ctrl->getFormAction($this, 'performEmptyTrash'));
			$this->ctrl->clearParameters($this);
			$confirmationGui->setConfirm($this->lng->txt('confirm'), 'performEmptyTrash');
			$confirmationGui->setCancel($this->lng->txt('cancel'), 'showFolder');
			$this->tpl->setVariable('CONFIRMATION', $confirmationGui->getHTML());
		}

		$this->tpl->setVariable('MAIL_TABLE', $table_html);
		$this->tpl->show();
	}

	/**
	 * @param bool $a_show_confirm
	 */
	protected function deleteSubFolder($a_show_confirm = true)
	{
		if ($a_show_confirm) {
			$confirmationGui = new \ilConfirmationGUI();
			$this->ctrl->setParameter($this, 'mobj_id', $this->currentFolderId);
			$confirmationGui->setFormAction($this->ctrl->getFormAction($this, 'showFolder'));
			$this->ctrl->clearParameters($this);
			$confirmationGui->setHeaderText($this->lng->txt('mail_sure_delete_folder'));
			$confirmationGui->setCancel($this->lng->txt('cancel'), 'showFolder');
			$confirmationGui->setConfirm($this->lng->txt('confirm'), 'performDeleteSubFolder');
			$this->tpl->setVariable('CONFIRMATION', $confirmationGui->getHTML());

			$this->showFolder(true);
		} else {
			$this->showFolder(false);
		}
	}

	/**
	 * @throws ilInvalidTreeStructureException
	 */
	protected function performDeleteSubFolder()
	{
		$parentFolderId = $this->mbox->getParentFolderId($this->currentFolderId);
		if ($parentFolderId > 0 && $this->mbox->deleteFolder($this->currentFolderId)) {
			ilUtil::sendInfo($this->lng->txt('mail_folder_deleted'), true);
			$this->ctrl->setParameterByClass('ilMailGUI', 'mobj_id', (int)$parentFolderId);
			$this->ctrl->redirectByClass('ilMailGUI');
		} else {
			\ilUtil::sendFailure($this->lng->txt('mail_error_delete'));
			$this->showFolder();
		}
	}

	/**
	 * @param string $mode
	 * @return ilPropertyFormGUI
	 */
	protected function getSubFolderForm(string $mode = 'create'): \ilPropertyFormGUI
	{
		$form = new \ilPropertyFormGUI();
		$this->ctrl->setParameter($this, 'mobj_id', $this->currentFolderId);
		$form->setFormAction($this->ctrl->getFormAction($this, 'performAddSubFolder'));
		$this->ctrl->clearParameters($this);
		if ('edit' === $mode) {
			$form->addCommandButton('performRenameSubFolder', $this->lng->txt('save'));
			$form->setTitle($this->lng->txt('mail_rename_folder'));
		} else {
			$form->addCommandButton('performAddSubFolder', $this->lng->txt('save'));
			$form->setTitle($this->lng->txt('mail_add_folder'));
		}
		$form->addCommandButton('showFolder', $this->lng->txt('cancel'));

		$title = new \ilTextInputGUI($this->lng->txt('title'), 'subfolder_title');
		$title->setRequired(true);
		$form->addItem($title);

		return $form;
	}

	/**
	 * Called if a folder is created by the action user
	 */
	protected function performAddSubFolder()
	{
		$form = $this->getSubFolderForm();
		$isFormValid = $form->checkInput();
		$form->setValuesByPost();
		if (!$isFormValid) {
			$this->addSubFolder($form);
			return;
		}

		if ($newFolderId = $this->mbox->addFolder($this->currentFolderId, $form->getInput('subfolder_title'))) {
			\ilUtil::sendSuccess($this->lng->txt('mail_folder_created'), true);
			$this->ctrl->setParameterByClass('ilMailGUI', 'mobj_id', $newFolderId);
			$this->ctrl->redirectByClass('ilMailGUI');
		}

		\ilUtil::sendFailure($this->lng->txt('mail_folder_exists'));
		$this->addSubFolder($form);
	}

	/**
	 * Called if the acting user wants to create a folder
	 * @param ilPropertyFormGUI|null $form
	 */
	protected function addSubFolder(\ilPropertyFormGUI $form = null)
	{
		if (null === $form) {
			$form = $this->getSubFolderForm();
		}

		$this->tpl->setTitle($this->lng->txt('mail'));
		$this->tpl->setContent($form->getHTML());
		$this->tpl->show();
	}

	/**
	 * Called if the folder title is renamed by the acting user
	 */
	protected function performRenameSubFolder()
	{
		$form = $this->getSubFolderForm('edit');
		$isFormValid = $form->checkInput();
		$form->setValuesByPost();
		if (!$isFormValid) {
			$this->renameSubFolder($form);
			return;
		}

		$folderData = $this->mbox->getFolderData($this->currentFolderId);
		if ($folderData['title'] === $form->getInput('subfolder_title')) {
			$this->showFolder();
			return;
		}

		if ($this->mbox->renameFolder($this->currentFolderId, $form->getInput('subfolder_title'))){
			\ilUtil::sendSuccess($this->lng->txt('mail_folder_name_changed'), true);
			$this->ctrl->setParameterByClass('ilMailGUI', 'mobj_id', $this->currentFolderId);
			$this->ctrl->redirectByClass('ilMailGUI');
		}

		\ilUtil::sendFailure($this->lng->txt('mail_folder_exists'));
		$this->renameSubFolder($form);
	}

	/**
	 * Called if the acting user wants to rename a folder
	 * @param ilPropertyFormGUI|null $form
	 */
	protected function renameSubFolder(\ilPropertyFormGUI $form = null)
	{
		if (null === $form) {
			$form = $this->getSubFolderForm('edit');
			$form->setValuesByArray(['subfolder_title' => $this->mbox->getFolderData($this->currentFolderId)['title']]);
		}

		$this->tpl->setTitle($this->lng->txt('mail'));
		$this->tpl->setContent($form->getHTML());
		$this->tpl->show();
	}

	/**
	 * @param bool $ignoreHttpGet
	 * @return int[]
	 */
	protected function getMailIdsFromRequest(bool $ignoreHttpGet = false): array
	{
		$mailIds = $this->httpRequest->getParsedBody()['mail_id'] ?? [];
		if (!is_array($mailIds)) {
			return [];
		}

		if (0 === count($mailIds) && !$ignoreHttpGet) {
			$mailId = $this->httpRequest->getQueryParams()['mail_id'] ?? 0;
			if (is_numeric($mailId)) {
				$mailIds = [$mailId];
			}
		}

		return array_filter(array_map('intval', $mailIds));
	}

	/**
	 * Called if multiple messages should be marked as read in the list view
	 */
	protected function markMailsRead()
	{
		$mailIds = $this->getMailIdsFromRequest();
		if (count($mailIds) > 0) {
			$this->umail->markRead($mailIds);
			\ilUtil::sendSuccess($this->lng->txt('saved_successfully'));
		} else {
			\ilUtil::sendInfo($this->lng->txt('mail_select_one'));
		}

		$this->showFolder();
	}

	/**
	 * Called if multiple messages should be marked as un-read in the list view
	 */
	protected function markMailsUnread()
	{
		$mailIds = $this->getMailIdsFromRequest();
		if (count($mailIds) > 0) {
			$this->umail->markUnread($mailIds);
			\ilUtil::sendSuccess($this->lng->txt('saved_successfully'));
		} else {
			\ilUtil::sendInfo($this->lng->txt('mail_select_one'));
		}

		$this->showFolder();
	}

	/**
	 * Called if a single message should be be moved in the detail view
	 */
	protected function moveSingleMail()
	{
		$mailIds = $this->getMailIdsFromRequest();
		if (1 !== count($mailIds)) {
			$this->showMail();
			\ilUtil::sendInfo($this->lng->txt('mail_select_one'));
			return;
		}

		$newFolderId = (int)($this->httpRequest->getParsedBody()['folder_id'] ?? 0);
		$redirectFolderId = $newFolderId;
		foreach ($mailIds as $mailId) {
			$mailData = $this->umail->getMail($mailId);
			if (isset($mailData['folder_id']) && is_numeric($mailData['folder_id']) && (int)$mailData['folder_id'] > 0) {
				$redirectFolderId = $mailData['folder_id'];
				break;
			}
		}

		if ($this->umail->moveMailsToFolder($mailIds, $newFolderId)) {
			\ilUtil::sendSuccess($this->lng->txt('mail_moved'), true);
			$this->ctrl->setParameter($this, 'mobj_id', $redirectFolderId);
			$this->ctrl->redirect($this, 'showFolder');
		} else {
			\ilUtil::sendFailure($this->lng->txt('mail_move_error'));
			$this->showMail();
		}
	}
	
	/**
	 * Called if a single message or multiple messages should be be moved in the list view
	 */
	protected function moveMails()
	{
		$mailIds = $this->getMailIdsFromRequest();
		if (0 === count($mailIds)) {
			$this->showFolder();
			\ilUtil::sendInfo($this->lng->txt('mail_select_one'));
			return;
		}

		$folderId = $this->parseFolderIdFromCommand($this->ctrl->getCmd());
		if ($this->umail->moveMailsToFolder($mailIds, $folderId)) {
			\ilUtil::sendSuccess($this->lng->txt('mail_moved'), true);
			$this->ctrl->setParameter($this, 'mobj_id', $this->currentFolderId);
			$this->ctrl->redirect($this, 'showFolder');
		} else {
			\ilUtil::sendFailure($this->lng->txt('mail_move_error'));
			$this->showFolder();
		}
	}

	/**
	 * Called if a single message or multiple messages should be deleted
	 */
	protected function deleteMails()
	{
		$trashFolderId = (int)$this->mbox->getTrashFolder();
		$mailIds = $this->getMailIdsFromRequest();

		if ($trashFolderId == $this->currentFolderId) {
			if(0 === count($mailIds)) {
				\ilUtil::sendInfo($this->lng->txt('mail_select_one'));
				$this->errorDelete = true;
			}
		} else {
			if (0 === count($mailIds)) {
				\ilUtil::sendInfo($this->lng->txt('mail_select_one'));
			} elseif ($this->umail->moveMailsToFolder($mailIds, $trashFolderId)) {
				\ilUtil::sendSuccess($this->lng->txt('mail_moved_to_trash'), true);
				$this->ctrl->setParameter($this, 'mobj_id', $this->currentFolderId);
				$this->ctrl->redirect($this, 'showFolder');
			} else {
				\ilUtil::sendFailure($this->lng->txt('mail_move_error'));
			}
		}

		$this->showFolder();
	}

	/**
	 * Called if the final deletion of selected messages was confirmed by the acting user
	 */
	protected function confirmDeleteMails()
	{
		$mailIds = $this->getMailIdsFromRequest();
		if (0 === count($mailIds)) {
			$this->showFolder();
			\ilUtil::sendInfo($this->lng->txt('mail_select_one'));
			return;
		}

		if ((int)$this->mbox->getTrashFolder() === (int)$this->currentFolderId) {
			if ($this->umail->deleteMails($mailIds)) {
				\ilUtil::sendSuccess($this->lng->txt('mail_deleted'), true);
				$this->ctrl->setParameter($this, 'mobj_id', $this->currentFolderId);
				$this->ctrl->redirect($this, 'showFolder');
			} else {
				\ilUtil::sendFailure($this->lng->txt('mail_delete_error'));
			}
		}

		$this->showFolder();
	}

	/**
	 * Detail view of a mail
	 */
	protected function showMail()
	{
		if ((int)\ilSession::get('mail_id') > 0) {
			$mailId = (int)\ilSession::get('mail_id');
			\ilSession::set('mail_id', null);
		} else {
			$mailId = $this->httpRequest->getQueryParams()['mail_id'] ?? 0;
		}

		$mailData = $this->umail->getMail($mailId);
		$this->umail->markRead(array($mailId));

		$this->tpl->setTitle($this->lng->txt('mail_mails_of'));

		$this->tabs->clearTargets();
		$this->ctrl->setParameter($this, 'mobj_id', $mailData['folder_id']);
		$this->tabs->setBackTarget($this->lng->txt('back_to_folder'), $this->ctrl->getFormAction($this, 'showFolder'));
		$this->ctrl->clearParameters($this);

		$this->ctrl->setParameter($this, 'mail_id', $mailId);
		$this->ctrl->setParameter($this, 'mobj_id', $mailData['folder_id']);
		$this->toolbar->setFormAction($this->ctrl->getFormAction($this, 'showMail'));
		$this->ctrl->clearParameters($this);

		$form = new \ilPropertyFormGUI();
		$form->setPreventDoubleSubmission(false);
		$form->setTableWidth('100%');
		$this->ctrl->setParameter($this, 'mobj_id', $mailData['folder_id']);
		$this->ctrl->setParameter($this, 'mail_id', $mailId);
		$form->setFormAction($this->ctrl->getFormAction($this, 'showMail'));
		$this->ctrl->clearParameters($this);
		$form->setTitle($this->lng->txt('mail_mails_of'));

		/**
		 * @var $sender ilObjUser
		 */
		$sender   = ilObjectFactory::getInstanceByObjId($mailData['sender_id'], false);
		$replyBtn = null;
		if ($sender && $sender->getId() && !$sender->isAnonymous()) {
			$replyBtn = \ilLinkButton::getInstance();
			$replyBtn->setCaption('reply');
			$this->ctrl->setParameterByClass('ilmailformgui', 'mobj_id', $mailData['folder_id']);
			$this->ctrl->setParameterByClass('ilmailformgui', 'mail_id', $mailId);
			$this->ctrl->setParameterByClass('ilmailformgui', 'type', 'reply');
			$replyBtn->setUrl($this->ctrl->getLinkTargetByClass('ilmailformgui'));
			$this->ctrl->clearParametersByClass('ilmailformgui');
			$replyBtn->setAccessKey(\ilAccessKey::REPLY);
			$replyBtn->setPrimary(true);
			$this->toolbar->addStickyItem($replyBtn);
		}

		$fwdBtn = \ilLinkButton::getInstance();
		$fwdBtn->setCaption('forward');
		$this->ctrl->setParameterByClass('ilmailformgui', 'mobj_id', $mailData['folder_id']);
		$this->ctrl->setParameterByClass('ilmailformgui', 'mail_id', $mailId);
		$this->ctrl->setParameterByClass('ilmailformgui', 'type', 'forward');
		$fwdBtn->setUrl($this->ctrl->getLinkTargetByClass('ilmailformgui'));
		$this->ctrl->clearParametersByClass('ilmailformgui');
		$fwdBtn->setAccessKey(\ilAccessKey::FORWARD_MAIL);
		if (!$replyBtn) {
			$fwdBtn->setPrimary(true);
			$this->toolbar->addStickyItem($fwdBtn);
		} else {
			$this->toolbar->addButtonInstance($fwdBtn);
		}

		$printBtn = \ilLinkButton::getInstance();
		$printBtn->setCaption('print');
		$this->ctrl->setParameter($this, 'mail_id', $mailId);
		$this->ctrl->setParameter($this, 'mobj_id', $mailData['folder_id']);
		$printBtn->setUrl($this->ctrl->getLinkTarget($this, 'printMail'));
		$this->ctrl->clearParameters($this);
		$printBtn->setTarget('_blank');
		$this->toolbar->addButtonInstance($printBtn);

		$deleteBtn = \ilSubmitButton::getInstance();
		$deleteBtn->setCaption('delete');
		$deleteBtn->setCommand('deleteMails');
		$deleteBtn->setAccessKey(\ilAccessKey::DELETE);
		$this->toolbar->addButtonInstance($deleteBtn);

		if ($sender && $sender->getId() && !$sender->isAnonymous()) {
			$linked_fullname    = $sender->getPublicName();
			$picture            = ilUtil::img(
				$sender->getPersonalPicturePath('xsmall'), $sender->getPublicName(),
				'', '', 0, '', 'ilMailAvatar'
			);

			if (in_array(ilObjUser::_lookupPref($sender->getId(), 'public_profile'), array('y', 'g'))) {
				$this->ctrl->setParameter($this, 'mail_id', $mailId);
				$this->ctrl->setParameter($this, 'mobj_id', $mailData['folder_id']);
				$this->ctrl->setParameter($this, 'user', $sender->getId());
				$linked_fullname = '<br /><a href="' . $this->ctrl->getLinkTarget($this, 'showUser') . '" title="'.$linked_fullname.'">' . $linked_fullname . '</a>';
				$this->ctrl->clearParameters($this);
			}

			$from = new ilCustomInputGUI($this->lng->txt('from') . ':');
			$from->setHtml($picture . ' ' . $linked_fullname);
			$form->addItem($from);
		} else if(!$sender || !$sender->getId()) {
			$from = new ilCustomInputGUI($this->lng->txt('from') . ':');
			$from->setHtml($mailData['import_name'] . ' (' . $this->lng->txt('user_deleted') . ')');
			$form->addItem($from);
		} else {
			$from = new ilCustomInputGUI($this->lng->txt('from') . ':');
			$from->setHtml(
				ilUtil::img(ilUtil::getImagePath('HeaderIconAvatar.svg'), ilMail::_getIliasMailerName(), '', '', 0, '', 'ilMailAvatar') .
				'<br />' . ilMail::_getIliasMailerName()
			);
			$form->addItem($from);
		}

		$to = new ilCustomInputGUI($this->lng->txt('mail_to') . ':');
		$to->setHtml(ilUtil::htmlencodePlainString($this->umail->formatNamesForOutput($mailData['rcp_to']), false));
		$form->addItem($to);

		if ($mailData['rcp_cc']) {
			$cc = new ilCustomInputGUI($this->lng->txt('cc') . ':');
			$cc->setHtml(ilUtil::htmlencodePlainString($this->umail->formatNamesForOutput($mailData['rcp_cc']), false));
			$form->addItem($cc);
		}

		if ($mailData['rcp_bcc']) {
			$bcc = new ilCustomInputGUI($this->lng->txt('bc') . ':');
			$bcc->setHtml(ilUtil::htmlencodePlainString($this->umail->formatNamesForOutput($mailData['rcp_bcc']), false));
			$form->addItem($bcc);
		}

		$subject = new ilCustomInputGUI($this->lng->txt('subject') . ':');
		$subject->setHtml(ilUtil::htmlencodePlainString($mailData['m_subject'], true));
		$form->addItem($subject);

		$date = new ilCustomInputGUI($this->lng->txt('date') . ':');
		$date->setHtml(ilDatePresentation::formatDate(new ilDateTime($mailData['send_time'], IL_CAL_DATETIME)));
		$form->addItem($date);

		$message = new ilCustomInputGUI($this->lng->txt('message') . ':');
		$message->setHtml(ilUtil::htmlencodePlainString($mailData['m_message'], true));
		$form->addItem($message);

		if ($mailData['attachments']) {
			$att = new ilCustomInputGUI($this->lng->txt('attachments') . ':');

			$radiog = new ilRadioGroupInputGUI('', 'filename');
			foreach ($mailData['attachments'] as $file) {
				$radiog->addOption(new ilRadioOption($file, md5($file)));
			}

			$att->setHtml($radiog->render());
			$form->addCommandButton('deliverFile', $this->lng->txt('download'));
			$form->addItem($att);
		}

		$isTrashFolder = false;
		if ($this->mbox->getTrashFolder() == $mailData['folder_id']) {
			$isTrashFolder = true;
		}

		$currentFolderData = $this->mbox->getFolderData($mailData['folder_id']);
		$actions = $this->mbox->getActions($mailData['folder_id']);

		$selectOptions = array();
		foreach ($actions as $key => $action) {
			if ($key === 'moveMails') {
				$folders = $this->mbox->getSubFolders();
				foreach ($folders as $folder) {
					if (
						($folder['type'] !== 'trash' || !$isTrashFolder) &&
						$folder['obj_id'] != $mailData['folder_id']
					) {
						$optionText = $action . ' ' . $folder['title'];
						if ($folder['type'] !== 'user_folder') {
							$optionText = $action . ' ' . $this->lng->txt('mail_' . $folder['title']) . ($folder['type'] == 'trash' ? ' (' . $this->lng->txt('delete') . ')' : '');
						}

						$selectOptions[$folder['obj_id']] = $optionText;
					}
				}
			}
		}

		$folderLabel = $this->lng->txt('mail_' . $currentFolderData['title']);
		if ($currentFolderData['type'] === 'user_folder') {
			$folderLabel = $currentFolderData['title'];
		}

		$this->toolbar->addSeparator();
		$this->toolbar->addText(sprintf($this->lng->txt('current_folder'), $folderLabel));

		if (is_array($selectOptions) && count($selectOptions) > 0) {
			$actions = new \ilSelectInputGUI('', 'folder_id');
			$actions->setOptions($selectOptions);
			$this->toolbar->addInputItem($actions);

			$moveBtn = \ilSubmitButton::getInstance();
			$moveBtn->setCaption('execute');
			$moveBtn->setCommand('moveSingleMail');
			$this->toolbar->addButtonInstance($moveBtn);
		}

		$prevMail = $this->umail->getPreviousMail($mailId);
		$nextMail = $this->umail->getNextMail($mailId);
		if (is_array($prevMail) || is_array($nextMail)) {
			$this->toolbar->addSeparator();

			if ($prevMail['mail_id']) {
				$prevBtn = \ilLinkButton::getInstance();
				$prevBtn->setCaption('previous');
				$this->ctrl->setParameter($this, 'mail_id', $prevMail['mail_id']);
				$this->ctrl->setParameter($this, 'mobj_id', $this->currentFolderId);
				$prevBtn->setUrl($this->ctrl->getLinkTarget($this, 'showMail'));
				$this->ctrl->clearParameters($this);
				$this->toolbar->addButtonInstance($prevBtn);
			}

			if ($nextMail['mail_id']) {
				$nextBtn = \ilLinkButton::getInstance();
				$nextBtn->setCaption('next');
				$this->ctrl->setParameter($this, 'mail_id', $nextMail['mail_id']);
				$this->ctrl->setParameter($this, 'mobj_id', $this->currentFolderId);
				$nextBtn->setUrl($this->ctrl->getLinkTarget($this, 'showMail'));
				$this->ctrl->clearParameters($this);
				$this->toolbar->addButtonInstance($nextBtn);
			}
		}

		$this->tpl->setContent($form->getHTML());
		$this->tpl->show();
	}

	/**
	 * Print mail
	 */
	public function printMail()
	{
		$tplprint = new ilTemplate('tpl.mail_print.html', true, true, 'Services/Mail');
		$tplprint->setVariable('JSPATH', $this->tpl->tplPath);

		$mailData = $this->umail->getMail((int)($this->httpRequest->getQueryParams()['mail_id'] ?? 0));

		/**
		 * @var $sender ilObjUser
		 */
		$sender = ilObjectFactory::getInstanceByObjId($mailData['sender_id'], false);

		$tplprint->setVariable('TXT_FROM', $this->lng->txt('from'));
		if ($sender && $sender->getId() && !$sender->isAnonymous()) {
			$tplprint->setVariable('FROM', $sender->getPublicName());
		} elseif (!$sender || !$sender->getId()) {
			$tplprint->setVariable('FROM', $mailData['import_name'] . ' (' . $this->lng->txt('user_deleted') . ')');
		} else {
			$tplprint->setVariable('FROM', ilMail::_getIliasMailerName());
		}

		$tplprint->setVariable('TXT_TO', $this->lng->txt('mail_to'));
		$tplprint->setVariable('TO', $mailData['rcp_to']);

		if ($mailData['rcp_cc']) {
			$tplprint->setCurrentBlock('cc');
			$tplprint->setVariable('TXT_CC', $this->lng->txt('cc'));
			$tplprint->setVariable('CC', $mailData['rcp_cc']);
			$tplprint->parseCurrentBlock();
		}

		if ($mailData['rcp_bcc']) {
			$tplprint->setCurrentBlock('bcc');
			$tplprint->setVariable('TXT_BCC', $this->lng->txt('bc'));
			$tplprint->setVariable('BCC', $mailData['rcp_bcc']);
			$tplprint->parseCurrentBlock();
		}

		$tplprint->setVariable('TXT_SUBJECT', $this->lng->txt('subject'));
		$tplprint->setVariable('SUBJECT', htmlspecialchars($mailData['m_subject']));

		$tplprint->setVariable('TXT_DATE', $this->lng->txt('date'));
		$tplprint->setVariable('DATE', ilDatePresentation::formatDate(new ilDateTime($mailData['send_time'], IL_CAL_DATETIME)));

		$tplprint->setVariable('TXT_MESSAGE', $this->lng->txt('message'));
		$tplprint->setVariable('MAIL_MESSAGE', nl2br(htmlspecialchars($mailData['m_message'])));

		$tplprint->show();
	}

	protected function deliverFile()
	{
		$mailId = $this->httpRequest->getQueryParams()['mail_id'] ?? 0;
		if ((int)\ilSession::get('mail_id') > 0) {
			$mailId = \ilSession::get('mail_id');
			\ilSession::set('mail_id', null);
		}

		$filename = $this->httpRequest->getParsedBody()['filename'] ?? '';
		if (strlen(\ilSession::get('filename')) > 0) {
			$filename = \ilSession::get('filename');
			\ilSession::set('filename', null);
		}

		try {
			if ($mailId > 0 && $filename !== '') {
				while (strpos($filename, '..') !== false) {
					$filename = str_replace('..', '', $filename);
				}

				$mailFileData = new \ilFileDataMail($this->user->getId());
				try {
					$file = $mailFileData->getAttachmentPathAndFilenameByMd5Hash($filename, (int)$mailId);
					\ilUtil::deliverFile($file['path'], $file['filename']);
				} catch (\OutOfBoundsException $e) {
					throw new \ilException('mail_error_reading_attachment');
				}
			} else {
				\ilUtil::sendInfo($this->lng->txt('mail_select_attachment'));
				$this->showMail();
			}
		} catch (\Exception $e) {
			\ilUtil::sendFailure($this->lng->txt($e->getMessage()), true);
			$this->ctrl->setParameter($this, 'mobj_id', $this->currentFolderId);
			$this->ctrl->redirect($this);
		}
	}

	protected function deliverAttachments()
	{
		try {
			$mailId = $this->httpRequest->getQueryParams()['mail_id'] ?? 0;

			$mailData = $this->umail->getMail((int)$mailId);
			if (null === $mailData || 0 === count((array)$mailData['attachments'])) {
				throw new \ilException('mail_error_reading_attachment');
			}

			$type = $this->httpRequest->getQueryParams()['type'] ?? '';

			$mailFileData = new \ilFileDataMail($this->user->getId());
			if (count($mailData['attachments']) === 1) {
				$attachment = current($mailData['attachments']);

				try {
					if ('draft' === $type) {
						if (!$mailFileData->checkFilesExist([$attachment])) {
							throw new \OutOfBoundsException('');
						}
						$pathToFile = $mailFileData->getAbsoluteAttachmentPoolPathByFilename($attachment);
						$fileName = $attachment;
					} else {
						$file = $mailFileData->getAttachmentPathAndFilenameByMd5Hash(md5($attachment), (int)$mailId);
						$pathToFile = $file['path'];
						$fileName = $file['filename'];
					}
					\ilUtil::deliverFile($pathToFile, $fileName);
				} catch (\OutOfBoundsException $e) {
					throw new \ilException('mail_error_reading_attachment');
				}
			} else {
				$mailFileData->deliverAttachmentsAsZip(
					$mailData['m_subject'],
					(int)$mailId,
					$mailData['attachments'],
					'draft' === $type
				);
			}
		} catch (\Exception $e) {
			\ilUtil::sendFailure($this->lng->txt($e->getMessage()), true);
			$this->ctrl->setParameter($this, 'mobj_id', $this->currentFolderId);
			$this->ctrl->redirect($this);
		}
	}

	/**
	 * 
	 */
	protected function applyFilter()
	{
		$sentFolderId   = $this->mbox->getSentFolder();
		$draftsFolderId = $this->mbox->getDraftsFolder();

		$isTrashFolder = $this->currentFolderId  == $this->mbox->getTrashFolder();
		$isSentFolder  = $this->currentFolderId  == $sentFolderId;
		$isDraftFolder = $this->currentFolderId  == $draftsFolderId;

		$table = new ilMailFolderTableGUI($this, $this->currentFolderId, 'showFolder');
		$table->isSentFolder($isSentFolder)
			->isDraftFolder($isDraftFolder)
			->isTrashFolder($isTrashFolder)
			->initFilter();
		$table->resetOffset();
		$table->writeFilterToSession();

		$this->showFolder();
	}

	/**
	 *
	 */
	protected function resetFilter()
	{
		$sentFolderId   = $this->mbox->getSentFolder();
		$draftsFolderId = $this->mbox->getDraftsFolder();

		$isTrashFolder = $this->currentFolderId == $this->mbox->getTrashFolder();
		$isSentFolder  = $this->currentFolderId == $sentFolderId;
		$isDraftFolder = $this->currentFolderId == $draftsFolderId;

		$table = new ilMailFolderTableGUI($this, $this->currentFolderId, 'showFolder');
		$table->isSentFolder($isSentFolder)
			->isDraftFolder($isDraftFolder)
			->isTrashFolder($isTrashFolder)
			->initFilter();
		$table->resetOffset();
		$table->resetFilter();

		$this->showFolder();
	}
}
