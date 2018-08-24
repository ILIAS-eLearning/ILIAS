<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/User/classes/class.ilObjUser.php';
require_once 'Services/Mail/classes/class.ilMailbox.php';
require_once 'Services/Mail/classes/class.ilFormatMail.php';
require_once './Services/Mail/classes/class.ilFileDataMail.php';
require_once 'Services/Mail/classes/class.ilMailFormCall.php';

/**
* @author Jens Conze
* @version $Id$
*
* @ingroup ServicesMail
* @ilCtrl_Calls ilMailFormGUI: ilMailFolderGUI, ilMailAttachmentGUI, ilMailSearchGUI, ilMailSearchCoursesGUI, ilMailSearchGroupsGUI, ilMailingListsGUI
*/
class ilMailFormGUI
{
	/**
	 * @var \ilTemplate
	 */
	private $tpl;

	/**
	 * @var \ilCtrl
	 */
	private $ctrl;

	/**
	 * @var \ilLanguage
	 */
	private $lng;

	/**
	 * @var \ilObjUser
	 */
	private $user;

	/**
	 * @var \ilTabsGUI
	 */
	private $tabs;

	/**
	 * @var \ilToolbarGUI
	 */
	private $toolbar;

	/**
	 * @var \ilRbacSystem
	 */
	private $rbacsystem;

	/**
	 * @var \ilFormatMail
	 */
	private $umail;

	/**
	 * @var \ilMailBox
	 */
	private $mbox;

	/**
	 * @var \ilFileDataMail
	 */
	private $mfile;

	public function __construct()
	{
		global $DIC;

		$this->tpl        = $DIC->ui()->mainTemplate();
		$this->ctrl       = $DIC->ctrl();
		$this->lng        = $DIC->language();
		$this->user       = $DIC->user();
		$this->tabs       = $DIC->tabs();
		$this->toolbar    = $DIC->toolbar();
		$this->rbacsystem = $DIC->rbac()->system();

		$this->umail = new ilFormatMail($this->user->getId());
		$this->mfile = new ilFileDataMail($this->user->getId());
		$this->mbox  = new ilMailBox($this->user->getId());

		if(isset($_POST['mobj_id']) && (int)$_POST['mobj_id'])
		{
			$_GET['mobj_id'] = $_POST['mobj_id'];
		}

		if(!(int)$_GET['mobj_id'])
		{
			$_GET['mobj_id'] = $this->mbox->getInboxFolder();
		}
		$_GET['mobj_id'] = (int)$_GET['mobj_id'];

		$this->ctrl->saveParameter($this, 'mobj_id');
	}

	public function executeCommand()
	{
		$forward_class = $this->ctrl->getNextClass($this);
		switch($forward_class)
		{
			case 'ilmailfoldergui':
				include_once 'Services/Mail/classes/class.ilMailFolderGUI.php';

				$this->ctrl->forwardCommand(new ilMailFolderGUI());
				break;

			case 'ilmailattachmentgui':
				include_once 'Services/Mail/classes/class.ilMailAttachmentGUI.php';

				$this->ctrl->setReturn($this, "returnFromAttachments");
				$this->ctrl->forwardCommand(new ilMailAttachmentGUI());
				break;

			case 'ilmailsearchgui':
				include_once 'Services/Contact/classes/class.ilMailSearchGUI.php';

				$this->ctrl->setReturn($this, "searchResults");
				$this->ctrl->forwardCommand(new ilMailSearchGUI());
				break;

			case 'ilmailsearchcoursesgui':
				include_once 'Services/Contact/classes/class.ilMailSearchCoursesGUI.php';

				$this->ctrl->setReturn($this, "searchResults");
				$this->ctrl->forwardCommand(new ilMailSearchCoursesGUI());
				break;
			
			case 'ilmailinglistsgui':
				include_once 'Services/Contact/classes/class.ilMailingListsGUI.php';

				$this->ctrl->setReturn($this, 'searchResults');
				$this->ctrl->forwardCommand(new ilMailingListsGUI());
				break;

			case 'ilmailsearchgroupsgui':
				include_once 'Services/Contact/classes/class.ilMailSearchGroupsGUI.php';

				$this->ctrl->setReturn($this, "searchResults");
				$this->ctrl->forwardCommand(new ilMailSearchGroupsGUI());
				break;

			default:
				if (!($cmd = $this->ctrl->getCmd()))
				{
					$cmd = "showForm";
				}

				$this->$cmd();
				break;
		}
		return true;
	}

	/**
	 * @param array $files
	 * @return array
	 */
	protected function decodeAttachmentFiles(array $files)
	{
		$decodedFiles = array();

		foreach($files as $value)
		{
			if(is_file($this->mfile->getMailPath() . '/' . $this->user->getId() . '_' . urldecode($value)))
			{
				$decodedFiles[] = urldecode($value);
			}
		}

		return $decodedFiles;
	}

	public function sendMessage()
	{
		$m_type = isset($_POST["m_type"]) ? $_POST["m_type"] : array("normal");

		$message = strip_tags(ilUtil::stripSlashes($_POST['m_message'], false));
		$message = str_replace("\r", '', $message);

		$files = $this->decodeAttachmentFiles(isset($_POST['attachments']) ? (array)$_POST['attachments'] : array());

		$mailer = $this->umail
			->withContextId(\ilMailFormCall::getContextId() ? \ilMailFormCall::getContextId() : '')
			->withContextParameters(is_array(ilMailFormCall::getContextParameters()) ? ilMailFormCall::getContextParameters() : []);

		$mailer->setSaveInSentbox(true);

		if ($errors = $mailer->sendMail(
			ilUtil::securePlainString($_POST['rcp_to']),
			ilUtil::securePlainString($_POST['rcp_cc']),
			ilUtil::securePlainString($_POST['rcp_bcc']),
			ilUtil::securePlainString($_POST['m_subject']), $message,
			$files,
			$m_type,
			(int)$_POST['use_placeholders']
		)
		) {
			$_POST['attachments'] = $files;
			$this->showSubmissionErrors($errors);
		} else {
			$mailer->savePostData($this->user->getId(), array(), "", "", "", "", "", "", "", "");

			$this->ctrl->setParameterByClass('ilmailgui', 'type', 'message_sent');

			if (ilMailFormCall::isRefererStored()) {
				ilUtil::sendInfo($this->lng->txt('mail_message_send'), true);
				$this->ctrl->redirectToURL(ilMailFormCall::getRefererRedirectUrl());
			} else {
				$this->ctrl->redirectByClass('ilmailgui');
			}
		}

		$this->showForm();
	}

	public function saveDraft()
	{
		if(!$_POST['m_subject'])
		{
			$_POST['m_subject'] = 'No title';
		}

		$draftFolderId = $this->mbox->getDraftsFolder();
		$files         = $this->decodeAttachmentFiles(isset($_POST['attachments']) ? (array)$_POST['attachments'] : array());

		if($errors = $this->umail->validateRecipients(
			ilUtil::securePlainString($_POST['rcp_to']),
			ilUtil::securePlainString($_POST['rcp_cc']),
			ilUtil::securePlainString($_POST['rcp_bcc'])
		))
		{
			$_POST['attachments'] = $files;
			$this->showSubmissionErrors($errors);
			$this->showForm();
			return;
		}

		if(isset($_SESSION["draft"]))
		{
			$draftId = (int)$_SESSION['draft'];
			unset($_SESSION['draft']);
		}
		else
		{
			$draftId = $this->umail->getNewDraftId($this->user->getId(), $draftFolderId);
		}

		$this->umail->updateDraft($draftFolderId, $files,
			ilUtil::securePlainString($_POST['rcp_to']),
			ilUtil::securePlainString($_POST['rcp_cc']),
			ilUtil::securePlainString($_POST['rcp_bcc']),
			$_POST['m_type'],
			ilUtil::securePlainString($_POST['m_email']),
			ilUtil::securePlainString($_POST['m_subject']),
			ilUtil::securePlainString($_POST['m_message']),
			$draftId,
			(int)$_POST['use_placeholders'],
			ilMailFormCall::getContextId(),
			ilMailFormCall::getContextParameters()
		);

		ilUtil::sendInfo($this->lng->txt('mail_saved'), true);

		if(ilMailFormCall::isRefererStored())
			ilUtil::redirect(ilMailFormCall::getRefererRedirectUrl());
		else
			$this->ctrl->redirectByClass(['ilmailgui', 'ilmailfoldergui']);

		$this->showForm();
	}

	public function searchUsers($save = true)
	{
		$this->tpl->setTitle($this->lng->txt("mail"));

		if ($save)
		{
			// decode post values
			$files = array();
			if(is_array($_POST['attachments']))
			{
				foreach($_POST['attachments'] as $value)
				{
					$files[] = urldecode($value);
				}
			}
			
			// Note: For security reasons, ILIAS only allows Plain text strings in E-Mails.
			$this->umail->savePostData($this->user->getId(),
										 $files,
										 ilUtil::securePlainString($_POST["rcp_to"]),
										 ilUtil::securePlainString($_POST["rcp_cc"]),
										 ilUtil::securePlainString($_POST["rcp_bcc"]),
										 $_POST["m_type"],
										 ilUtil::securePlainString($_POST["m_email"]),
										 ilUtil::securePlainString($_POST["m_subject"]),
										 ilUtil::securePlainString($_POST["m_message"]),
										 ilUtil::securePlainString($_POST['use_placeholders']),
										 ilMailFormCall::getContextId(),
										 ilMailFormCall::getContextParameters()
									);
		}
		include_once 'Services/Form/classes/class.ilPropertyFormGUI.php';
		$form = new ilPropertyFormGUI();
		$form->setId('search_rcp');
		$form->setTitle($this->lng->txt('search_recipients'));
		$form->setFormAction($this->ctrl->getFormAction($this, 'search'));
		
		$inp = new ilTextInputGUI($this->lng->txt("search_for"), 'search');
		$inp->setSize(30);
		$dsDataLink = $this->ctrl->getLinkTarget($this, 'lookupRecipientAsync', '', true, false);
		$inp->setDataSource($dsDataLink);
		
		if (strlen(trim($_SESSION["mail_search_search"])) > 0)
		{
			$inp->setValue(ilUtil::prepareFormOutput(trim($_SESSION["mail_search_search"]), true));
		}
		$form->addItem($inp);

		$form->addCommandButton('search', $this->lng->txt("search"));
		$form->addCommandButton('cancelSearch', $this->lng->txt("cancel"));

		$this->tpl->setContent($form->getHtml());
		$this->tpl->show();
	}

	/**
	 *
	 */
	public function searchCoursesTo()
	{
		$this->saveMailBeforeSearch();

		if($_SESSION['search_crs'])
		{
			$this->ctrl->setParameterByClass('ilmailsearchcoursesgui', 'cmd', 'showMembers');
		}
		
		$this->ctrl->setParameterByClass('ilmailsearchcoursesgui', 'ref', 'mail');
		$this->ctrl->redirectByClass('ilmailsearchcoursesgui');
	}

	/**
	 *
	 */
	public function searchGroupsTo()
	{
		$this->saveMailBeforeSearch();

		$this->ctrl->setParameterByClass('ilmailsearchgroupsgui', 'ref', 'mail');
		$this->ctrl->redirectByClass('ilmailsearchgroupsgui');
	}

	public function search()
	{
		$_SESSION["mail_search_search"] = $_POST["search"];
		if(strlen(trim($_SESSION["mail_search_search"])) == 0)
		{
			ilUtil::sendInfo($this->lng->txt("mail_insert_query"));
			$this->searchUsers(false);
		}
		else
		{
			if(strlen(trim($_SESSION["mail_search_search"])) < 3)
			{
				$this->lng->loadLanguageModule('search');
				ilUtil::sendInfo($this->lng->txt('search_minimum_three'));
				$this->searchUsers(false);
			}
			else
			{
				$this->ctrl->setParameterByClass("ilmailsearchgui", "search", urlencode($_SESSION["mail_search_search"]));
				$this->ctrl->redirectByClass("ilmailsearchgui");
			}
		}
	}

	public function cancelSearch()
	{
		unset($_SESSION["mail_search"]);
		$this->searchResults();
	}

	public function editAttachments()
	{
		// decode post values
		$files = array();
		if(is_array($_POST['attachments']))
		{
			foreach($_POST['attachments'] as $value)
			{
				$files[] = urldecode($value);
			}
		}
		
		// Note: For security reasons, ILIAS only allows Plain text messages.
		$this->umail->savePostData($this->user->getId(),
									$files,
									ilUtil::securePlainString($_POST["rcp_to"]),
									ilUtil::securePlainString($_POST["rcp_cc"]),
									ilUtil::securePlainString($_POST["rcp_bcc"]),
									$_POST["m_type"],
									ilUtil::securePlainString($_POST["m_email"]),
							 		ilUtil::securePlainString($_POST["m_subject"]),
									ilUtil::securePlainString($_POST["m_message"]),
									ilUtil::securePlainString($_POST['use_placeholders']),
									ilMailFormCall::getContextId(),
									ilMailFormCall::getContextParameters()
								);
			
		$this->ctrl->redirectByClass("ilmailattachmentgui");
	}

	public function returnFromAttachments()
	{
		$_GET["type"] = "attach";
		$this->showForm();
	} 
	
	public function searchResults()
	{
		$_GET["type"] = "search_res";
		$this->showForm();
	}

	public function mailUser()
	{
		$_GET["type"] = "new";
		$this->showForm();		
	}

	public function mailRole()
	{
		$_GET["type"] = "role";
		$this->showForm();		
	}

	public function replyMail()
	{
		$_GET["type"] = "reply";
		$this->showForm();		
	}

	public function mailAttachment()
	{
		$_GET["type"] = "attach";
		$this->showForm();		
	}

	/**
	 * Called asynchronously when changing the template
	 */
	protected function getTemplateDataById()
	{
		require_once 'Services/JSON/classes/class.ilJsonUtil.php';

		if(!isset($_GET['template_id']))
		{
			exit();
		}

		try
		{
			require_once 'Services/Mail/classes/class.ilMailTemplateService.php';
			require_once 'Services/Mail/classes/class.ilMailTemplateDataProvider.php';
			$template_id = (int)$_GET['template_id'];
			$template_provider = new ilMailTemplateDataProvider();
			$template = $template_provider->getTemplateById($template_id);
			$context = ilMailTemplateService::getTemplateContextById($template->getContext());
			echo json_encode(array(
				'm_subject' => $template->getSubject(),
				'm_message' => $template->getMessage()
			));
		}
		catch(Exception $e)
		{
		}
		exit();
	}

	public function showForm()
	{
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.mail_new.html", "Services/Mail");
		$this->tpl->setTitle($this->lng->txt("mail"));
		
		$this->lng->loadLanguageModule("crs");

		if(ilMailFormCall::isRefererStored())
		{
			$this->tabs->setBackTarget($this->lng->txt('back'), $this->ctrl->getLinkTarget($this, 'cancelMail'));
		}

		switch($_GET["type"])
		{
			case 'reply':
				if($_SESSION['mail_id'])
				{
					$_GET['mail_id'] = $_SESSION['mail_id'];
				}
				$mailData = $this->umail->getMail($_GET["mail_id"]);
				$mailData["m_subject"] = $this->umail->formatReplySubject();
				$mailData["m_message"] = $this->umail->formatReplyMessage(); 
				$mailData["m_message"] = $this->umail->prependSignature();
				// NO ATTACHMENTS FOR REPLIES
				$mailData["attachments"] = array();
				//$mailData["rcp_cc"] = $this->umail->formatReplyRecipientsForCC();
				$mailData["rcp_cc"] = '';
				$mailData["rcp_to"] = $this->umail->formatReplyRecipient();	
				$_SESSION["mail_id"] = "";
				break;
		
			case 'search_res':
				$mailData = $this->umail->getSavedData();

				/*if($_SESSION["mail_search_results"])
				{
					$mailData = $this->umail->appendSearchResult($_SESSION["mail_search_results"],$_SESSION["mail_search"]);
				}
				unset($_SESSION["mail_search"]);
				unset($_SESSION["mail_search_results"]);*/

				if($_SESSION["mail_search_results_to"])
				{
					$mailData = $this->umail->appendSearchResult($_SESSION["mail_search_results_to"], 'to');
				}
				if($_SESSION["mail_search_results_cc"])
				{
					$mailData = $this->umail->appendSearchResult($_SESSION["mail_search_results_cc"], 'cc');
				}
				if($_SESSION["mail_search_results_bcc"])
				{
					$mailData = $this->umail->appendSearchResult($_SESSION["mail_search_results_bcc"], 'bc');
				}
				
				unset($_SESSION["mail_search_results_to"]);
				unset($_SESSION["mail_search_results_cc"]);
				unset($_SESSION["mail_search_results_bcc"]);
								
				break;
		
			case 'attach':
				$mailData = $this->umail->getSavedData();
				break;
		
			case 'draft':
				$_SESSION["draft"] = $_GET["mail_id"];
				$mailData = $this->umail->getMail($_GET["mail_id"]);
				ilMailFormCall::setContextId($mailData['tpl_ctx_id']);
				ilMailFormCall::setContextParameters($mailData['tpl_ctx_params']);
				break;
		
			case 'forward':
				$mailData = $this->umail->getMail($_GET["mail_id"]);
				$mailData["rcp_to"] = $mailData["rcp_cc"] = $mailData["rcp_bcc"] = '';
				$mailData["m_subject"] = $this->umail->formatForwardSubject();
				$mailData["m_message"] = $this->umail->prependSignature();
				if(count($mailData["attachments"]))
				{
					if($error = $this->mfile->adoptAttachments($mailData["attachments"],$_GET["mail_id"]))
					{
						ilUtil::sendInfo($error);
					}
				}
				break;
		
			case 'new':
				if($_GET['rcp_to'])
				{
					// Note: For security reasons, ILIAS only allows Plain text strings in E-Mails.
					$mailData["rcp_to"] = ilUtil::securePlainString($_GET['rcp_to']);
				}
				else if($_SESSION['rcp_to'])
				{
					$mailData["rcp_to"] = $_SESSION['rcp_to'];
				}
				if($_GET['rcp_cc'])
				{
					// Note: For security reasons, ILIAS only allows Plain text strings in E-Mails.
					$mailData["rcp_cc"] = ilUtil::securePlainString($_GET['rcp_cc']);
				}
				else if($_SESSION['rcp_cc'])
				{
					$mailData["rcp_cc"] = $_SESSION['rcp_cc'];
				}
				if($_GET['rcp_bcc'])
				{
					// Note: For security reasons, ILIAS only allows Plain text strings in E-Mails.
					$mailData["rcp_bcc"] = ilUtil::securePlainString($_GET['rcp_bcc']);
				}
				else if($_SESSION['rcp_bcc'])
				{
					$mailData["rcp_bcc"] = $_SESSION['rcp_bcc'];
				}
				$mailData['m_message'] = '';
				if(strlen($sig = ilMailFormCall::getSignature()))
				{
					$mailData['m_message'] = $sig;
					$mailData['m_message'] .= chr(13).chr(10).chr(13).chr(10);
				}
				$mailData['m_message'] .= $this->umail->appendSignature();

				$_SESSION['rcp_to'] = '';
				$_SESSION['rcp_cc'] = '';
				$_SESSION['rcp_bcc'] = '';
				break;
		
			case 'role':
		
				if(is_array($_POST['roles']))
				{
					// Note: For security reasons, ILIAS only allows Plain text strings in E-Mails.
					$mailData['rcp_to'] = ilUtil::securePlainString(implode(',',$_POST['roles']));
				}
				elseif(is_array($_SESSION['mail_roles']))
				{
					$mailData['rcp_to'] = ilUtil::securePlainString(implode(',', $_SESSION['mail_roles']));
				}

				$mailData['m_message'] = '';
				if(strlen($sig = ilMailFormCall::getSignature()))
				{
					$mailData['m_message'] = $sig;
					$mailData['m_message'] .= chr(13).chr(10).chr(13).chr(10);
				}
		
				$mailData['m_message'] .= $_POST["additional_message_text"].chr(13).chr(10).$this->umail->appendSignature();
				$_POST["additional_message_text"] = "";
				$_SESSION['mail_roles'] = "";
				break;
		
			case 'address':
				$mailData["rcp_to"] = urldecode($_GET["rcp"]);
				break;
		
			default:
				// GET DATA FROM POST
				$mailData = $_POST;

				// strip slashes
				foreach ($mailData as $key => $value)
				{
					if (is_string($value))
					{
						// Note: For security reasons, ILIAS only allows Plain text strings in E-Mails.
						$mailData[$key] = ilUtil::securePlainString($value);
					}
				}
				break;
		}

		include_once('./Services/Form/classes/class.ilPropertyFormGUI.php');

		$form_gui = new ilPropertyFormGUI();
		$form_gui->setTitle($this->lng->txt('compose'));
		$form_gui->setId('mail_compose_form');
		$form_gui->setName('mail_compose_form');
		$form_gui->setFormAction($this->ctrl->getFormAction($this, 'sendMessage'));

		$this->tpl->setVariable('FORM_ID', $form_gui->getId());

		require_once 'Services/UIComponent/Button/classes/class.ilButton.php';
		$btn = ilButton::getInstance();
		$btn->setButtonType(ilButton::BUTTON_TYPE_SUBMIT);
		$btn->setForm('form_' . $form_gui->getName())
			->setName('searchUsers')
			->setCaption('search_recipients');
		$this->toolbar->addStickyItem($btn);

		$btn = ilButton::getInstance();
		$btn->setButtonType(ilButton::BUTTON_TYPE_SUBMIT)
			->setName('searchCoursesTo')
			->setForm('form_' . $form_gui->getName())
			->setCaption('mail_my_courses');
		$this->toolbar->addButtonInstance($btn);

		$btn = ilButton::getInstance();
		$btn->setButtonType(ilButton::BUTTON_TYPE_SUBMIT)
			->setName('searchGroupsTo')
			->setForm('form_' . $form_gui->getName())
			->setCaption('mail_my_groups');
		$this->toolbar->addButtonInstance($btn);
		
		$btn = ilButton::getInstance();
		$btn->setButtonType(ilButton::BUTTON_TYPE_SUBMIT)
			->setName('searchMailingListsTo')
			->setForm('form_' . $form_gui->getName())
			->setCaption('mail_my_mailing_lists');
		$this->toolbar->addButtonInstance($btn);

		$dsDataLink = $this->ctrl->getLinkTarget($this, 'lookupRecipientAsync', '', true);
		
		// RECIPIENT
		$inp = new ilTextInputGUI($this->lng->txt('mail_to'), 'rcp_to');
		$inp->setRequired(true);
		$inp->setSize(50);
		$inp->setValue($mailData["rcp_to"]);
		$inp->setDataSource($dsDataLink, ",");
		$inp->setMaxLength(null);
		$form_gui->addItem($inp);

		// CC
		$inp = new ilTextInputGUI($this->lng->txt('cc'), 'rcp_cc');
		$inp->setSize(50);
		$inp->setValue($mailData["rcp_cc"]);
		$inp->setDataSource($dsDataLink, ",");
		$inp->setMaxLength(null);
		$form_gui->addItem($inp);

		// BCC
		$inp = new ilTextInputGUI($this->lng->txt('bc'), 'rcp_bcc');
		$inp->setSize(50);
		$inp->setValue($mailData["rcp_bcc"]);
		$inp->setDataSource($dsDataLink, ",");
		$inp->setMaxLength(null);
		$form_gui->addItem($inp);

		// SUBJECT
		$inp = new ilTextInputGUI($this->lng->txt('subject'), 'm_subject');
		$inp->setSize(50);
		$inp->setRequired(true);
		$inp->setValue($mailData["m_subject"]);
		$form_gui->addItem($inp);

		// Attachments
		include_once 'Services/Mail/classes/class.ilMailFormAttachmentFormPropertyGUI.php';
		$att = new ilMailFormAttachmentPropertyGUI($this->lng->txt( ($mailData["attachments"]) ? 'edit' : 'add' ));
		
		if (is_array($mailData["attachments"]) && count($mailData["attachments"]))
		{
			foreach($mailData["attachments"] as $data)
			{
				if(is_file($this->mfile->getMailPath() . '/' . $this->user->getId() . "_" . $data))
				{
					$hidden = new ilHiddenInputGUI('attachments[]');
					$form_gui->addItem($hidden);
					$size = filesize($this->mfile->getMailPath() . '/' . $this->user->getId() . "_" . $data);
					$label = $data . " [" . ilUtil::formatSize($size) . "]";
					$att->addItem($label);
					$hidden->setValue(urlencode($data));
				}
			}
		}
		$form_gui->addItem($att);

		// ONLY IF SYSTEM MAILS ARE ALLOWED
		if($this->rbacsystem->checkAccess("system_message",$this->umail->getMailObjectReferenceId()))
		{
			$chb = new ilCheckboxInputGUI($this->lng->txt('type'), 'm_type[]');
			$chb->setOptionTitle($this->lng->txt('system_message'));
			$chb->setValue('system');
			$chb->setChecked(false);
			if(is_array($mailData["m_type"]) and in_array('system',$mailData["m_type"]))
			{
				$chb->setChecked(true);
			}
			$form_gui->addItem($chb);
		}

		if(ilMailFormCall::getContextId())
		{
			$context_id = ilMailFormCall::getContextId();

			// Activate placeholders
			$mailData['use_placeholders'] = true;

			try {
				require_once 'Services/Mail/classes/class.ilMailTemplateService.php';
				$context = ilMailTemplateService::getTemplateContextById($context_id);

				require_once 'Services/Mail/classes/class.ilMailTemplateDataProvider.php';
				$template_provider = new ilMailTemplateDataProvider();
				$templates = $template_provider->getTemplateByContextId($context->getId());

				if(count($templates))
				{
					$options = array();
					foreach($templates as $template)
					{
						$options[$template->getTplId()] = $template->getTitle();
					}
					asort($options);

					require_once 'Services/Mail/classes/Form/class.ilMailTemplateSelectInputGUI.php';
					$template_chb = new ilMailTemplateSelectInputGUI(
						$this->lng->txt('mail_template_client'),
						'template_id',
						$this->ctrl->getLinkTarget($this, 'getTemplateDataById', '', true, false),
						array('m_subject', 'm_message')
					);
					$template_chb->setInfo($this->lng->txt('mail_template_client_info'));
					$template_chb->setOptions(array('' => $this->lng->txt('please_choose')) + $options);
					$form_gui->addItem($template_chb);
				}
			}
			catch(Exception $e)
			{
				require_once './Services/Logging/classes/public/class.ilLoggerFactory.php';
				ilLoggerFactory::getLogger('mail')->error(sprintf('%s has been called with invalid context id: %s.', __METHOD__, $context_id));
			}
		}
		else
		{
			require_once 'Services/Mail/classes/class.ilMailTemplateGenericContext.php';
			$context = new ilMailTemplateGenericContext();
		}

		// MESSAGE
		$inp = new ilTextAreaInputGUI($this->lng->txt('message_content'), 'm_message');
		//$inp->setValue(htmlspecialchars($mailData["m_message"], false));
		$inp->setValue($mailData["m_message"]);
		$inp->setRequired(false);
		$inp->setCols(60);
		$inp->setRows(10);
		$form_gui->addItem($inp);

		// PLACEHOLDERS
		$chb = new ilCheckboxInputGUI($this->lng->txt('mail_serial_letter_placeholders'), 'use_placeholders');
		$chb->setOptionTitle($this->lng->txt('activate_serial_letter_placeholders'));
		$chb->setValue(1);
		if(isset($mailData['use_placeholders']) && $mailData['use_placeholders'])
		{
			$chb->setChecked(true);
		}
		
		require_once 'Services/Mail/classes/Form/class.ilManualPlaceholderInputGUI.php';
		$placeholders = new ilManualPlaceholderInputGUI('m_message');
		$placeholders->setInstructionText($this->lng->txt('mail_nacc_use_placeholder'));
		$placeholders->setAdviseText(sprintf($this->lng->txt('placeholders_advise'), '<br />'));
		foreach($context->getPlaceholders() as $key => $value)
		{
			$placeholders->addPlaceholder($value['placeholder'], $value['label'] );
		}
		$chb->addSubItem($placeholders);
		$form_gui->addItem($chb);

		$form_gui->addCommandButton('sendMessage', $this->lng->txt('send_mail'));
		$form_gui->addCommandButton('saveDraft', $this->lng->txt('save_message'));       
        if(ilMailFormCall::isRefererStored())
            $form_gui->addCommandButton('cancelMail', $this->lng->txt('cancel'));

		$this->tpl->parseCurrentBlock();

		$this->tpl->setVariable('FORM', $form_gui->getHTML());

		$this->tpl->addJavaScript('Services/Mail/js/ilMailComposeFunctions.js');
		$this->tpl->show();
	}

	public function lookupRecipientAsync()
	{
		include_once 'Services/JSON/classes/class.ilJsonUtil.php';
		include_once 'Services/Mail/classes/class.ilMailForm.php';
		
		$search = $_REQUEST["term"];
		$result = array();
		if (!$search)
		{			
			echo ilJsonUtil::encode($result);
			exit;
		}
		
		// #14768
		$quoted = ilUtil::stripSlashes($search);
		$quoted = str_replace('%', '\%', $quoted);
		$quoted = str_replace('_', '\_', $quoted);
		
		$mailFormObj = new ilMailForm;
		$result      = $mailFormObj->getRecipientAsync("%" . $quoted . "%", ilUtil::stripSlashes($search));
		
		echo ilJsonUtil::encode($result);
		exit;
	}

	public function cancelMail()
	{
		if(ilMailFormCall::isRefererStored())
		{
			ilUtil::redirect(ilMailFormCall::getRefererRedirectUrl());
		}

		$this->showForm();
	}

	/**
	 *
	 */
	protected function saveMailBeforeSearch()
	{
		$files = array();
		if(is_array($_POST['attachments']))
		{
			foreach($_POST['attachments'] as $value)
			{
				$files[] = urldecode($value);
			}
		}

		$this->umail->savePostData($this->user->getId(),
			$files,
			ilUtil::securePlainString($_POST['rcp_to']),
			ilUtil::securePlainString($_POST['rcp_cc']),
			ilUtil::securePlainString($_POST['rcp_bcc']),
			$_POST['m_type'],
			ilUtil::securePlainString($_POST['m_email']),
			ilUtil::securePlainString($_POST['m_subject']),
			ilUtil::securePlainString($_POST['m_message']),
			ilUtil::securePlainString($_POST['use_placeholders']),
			ilMailFormCall::getContextId(),
			ilMailFormCall::getContextParameters()
		);
	}

	/**
	 *
	 */
	public function searchMailingListsTo()
	{
		$this->saveMailBeforeSearch();

		$this->ctrl->setParameterByClass('ilmailinglistsgui', 'ref', 'mail');
		$this->ctrl->redirectByClass('ilmailinglistsgui');
	}

	/**
	 * @param array $errors
	 */
	protected function showSubmissionErrors(array $errors)
	{
		$errors_to_display = array();

		foreach($errors as $error)
		{
			$error       = array_values($error);
			$first_error = array_shift($error);

			$translation = $this->lng->txt($first_error);
			if($translation == '-' . $first_error . '-')
			{
				$translation = $first_error;
			}

			if(count($error) == 0 || $translation == $first_error)
			{
				$errors_to_display[] = $translation;
			}
			else
			{
				// We expect all other parts of this error array are recipient addresses = input parameters
				$error = array_map(function($address) {
					return ilUtil::prepareFormOutput($address);
				}, $error);

				array_unshift($error, $translation);
				$errors_to_display[] = call_user_func_array('sprintf', $error);
			}
		}

		if(count($errors_to_display) > 0)
		{
			$tpl = new ilTemplate('tpl.mail_new_submission_errors.html', true, true, 'Services/Mail');
			if(count($errors_to_display) == 1)
			{
				$tpl->setCurrentBlock('single_error');
				$tpl->setVariable('SINGLE_ERROR', current($errors_to_display));
				$tpl->parseCurrentBlock();
			}
			else
			{
				$first_error = array_shift($errors_to_display);

				foreach($errors_to_display as $error)
				{
					$tpl->setCurrentBlock('error_loop');
					$tpl->setVariable('ERROR', $error);
					$tpl->parseCurrentBlock();
				}

				$tpl->setCurrentBlock('multiple_errors');
				$tpl->setVariable('FIRST_ERROR', $first_error);
				$tpl->parseCurrentBlock();
			}

			ilUtil::sendInfo($tpl->get());
		}
	}
}