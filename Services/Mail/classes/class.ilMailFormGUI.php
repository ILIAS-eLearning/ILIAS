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
	private $tpl = null;
	private $ctrl = null;
	private $lng = null;
	
	private $umail = null;
	private $mbox = null;
	private $mfile = null;

	public function __construct()
	{
		global $tpl, $ilCtrl, $lng, $ilUser;

		$this->tpl = $tpl;
		$this->ctrl = $ilCtrl;
		$this->lng = $lng;
		
		$this->umail = new ilFormatMail($ilUser->getId());
		$this->mfile = new ilFileDataMail($ilUser->getId());
		$this->mbox = new ilMailBox($ilUser->getId());
		
		if(isset($_POST['mobj_id']) && (int)$_POST['mobj_id'])
		{
			$_GET['mobj_id'] = $_POST['mobj_id'];
		}
		// IF THERE IS NO OBJ_ID GIVEN GET THE ID OF MAIL ROOT NODE
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

	public function sendMessage()
	{
		global $ilUser;
		
		// decode post values
		$files = array();
		if(is_array($_POST['attachments']))
		{
			foreach($_POST['attachments'] as $value)
			{
				if(is_file($this->mfile->getMailPath() . '/' . $ilUser->getId() . "_" . urldecode($value)))
				{
					$files[] = urldecode($value);
				}
			}
		}
		
		// Remove \r
		$f_message = str_replace("\r", '', ilUtil::securePlainString($_POST['m_message']));

		// Note: For security reasons, ILIAS only allows Plain text strings in E-Mails.		
		$f_message = $this->umail->formatLinebreakMessage($f_message);
		
		$this->umail->setSaveInSentbox(true);		

		$m_type = isset($_POST["m_type"]) ? $_POST["m_type"] : array("normal");

		// Note: For security reasons, ILIAS only allows Plain text strings in E-Mails.
		if($errorMessage = $this->umail->sendMail(
				ilUtil::securePlainString($_POST['rcp_to']),
				ilUtil::securePlainString($_POST['rcp_cc']),
				ilUtil::securePlainString($_POST['rcp_bcc']),
				ilUtil::securePlainString($_POST['m_subject']), $f_message,
				$files,
//				$_POST['m_type'],
				$m_type,
				ilUtil::securePlainString($_POST['use_placeholders'])
				)
			)
		{
			if(is_array($_POST['attachments']))
			{
				foreach($_POST['attachments'] as $key => $value)
				{
					if(is_file($this->mfile->getMailPath() . '/' . $ilUser->getId() . "_" . urldecode($value)))
					{
						$_POST['attachments'][$key] = urldecode($value);
					}
					else
					{
						unset($_POST['attachments'][$key]);
					}
				}
			}
			ilUtil::sendInfo($errorMessage);
		}
		else
		{
			$this->umail->savePostData($ilUser->getId(), array(), "", "", "", "", "", "", "", "");			
			
			$this->ctrl->setParameterByClass('ilmailgui', 'type', 'message_sent');

            if(ilMailFormCall::isRefererStored())
            {
                ilUtil::sendInfo($this->lng->txt('mail_message_send'), true);
                ilUtil::redirect(ilMailFormCall::getRefererRedirectUrl());
            }
            else
               $this->ctrl->redirectByClass('ilmailgui');
		}

		$this->showForm();
	}

	public function saveDraft()
	{
		if(!$_POST['m_subject'])
		{
			$_POST['m_subject'] = 'No title';
		}

		$draftsId = $this->mbox->getDraftsFolder();
		
		// decode post values
		$files = array();
		if(is_array($_POST['attachments']))
		{
			foreach($_POST['attachments'] as $value)
			{
				$files[] = urldecode($value);
			}
		}
		
		if(isset($_SESSION["draft"]))
		{
			// Note: For security reasons, ILIAS only allows Plain text strings in E-Mails.
			$this->umail->updateDraft($draftsId, $files,
				ilUtil::securePlainString($_POST["rcp_to"]),
				ilUtil::securePlainString($_POST["rcp_cc"]),
				ilUtil::securePlainString($_POST["rcp_bcc"]),
				$_POST["m_type"],
				ilUtil::securePlainString($_POST["m_email"]),
				ilUtil::securePlainString($_POST["m_subject"]),
				ilUtil::securePlainString($_POST["m_message"]),
				(int)$_SESSION["draft"],
				(int)ilUtil::securePlainString($_POST['use_placeholders'])
			);
			#session_unregister("draft");
			#ilUtil::sendInfo($this->lng->txt("mail_saved"),true);
			#ilUtil::redirect("ilias.php?baseClass=ilMailGUI&mobj_id=".$mbox->getInboxFolder());
			
			unset($_SESSION["draft"]);
			ilUtil::sendInfo($this->lng->txt("mail_saved"), true);
			
            if(ilMailFormCall::isRefererStored())
                ilUtil::redirect(ilMailFormCall::getRefererRedirectUrl());
            else
               $this->ctrl->redirectByClass("ilmailfoldergui");
		}
		else
		{
			if ($this->umail->sendInternalMail($draftsId,$_SESSION["AccountId"],$files,
					// Note: For security reasons, ILIAS only allows Plain text strings in E-Mails.
					ilUtil::securePlainString($_POST["rcp_to"]),
					ilUtil::securePlainString($_POST["rcp_cc"]),
					ilUtil::securePlainString($_POST["rcp_bcc"]),
					'read',
					$_POST["m_type"],
					ilUtil::securePlainString($_POST["m_email"]),
					ilUtil::securePlainString($_POST["m_subject"]),
					ilUtil::securePlainString($_POST["m_message"]),
					$_SESSION["AccountId"],
					ilUtil::securePlainString($_POST['use_placeholders'])
					)
			)
			{
				ilUtil::sendInfo($this->lng->txt("mail_saved"),true);
				#$this->ctrl->setParameterByClass("ilmailfoldergui", "mobj_id", $this->mbox->getDraftsFolder());

                if(ilMailFormCall::isRefererStored())
                    ilUtil::redirect(ilMailFormCall::getRefererRedirectUrl());
                else
                   $this->ctrl->redirectByClass("ilmailfoldergui");
			}
			else
			{
				ilUtil::sendInfo($this->lng->txt("mail_send_error"));
			}
		}
		
		$this->showForm();
	}

	public function searchUsers($save = true)
	{
		global $ilUser, $ilCtrl;
		
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
			$this->umail->savePostData($ilUser->getId(),
										 $files,
										 ilUtil::securePlainString($_POST["rcp_to"]),
										 ilUtil::securePlainString($_POST["rcp_cc"]),
										 ilUtil::securePlainString($_POST["rcp_bcc"]),
										 $_POST["m_type"],
										 ilUtil::securePlainString($_POST["m_email"]),
										 ilUtil::securePlainString($_POST["m_subject"]),
										 ilUtil::securePlainString($_POST["m_message"]),
										 ilUtil::securePlainString($_POST['use_placeholders'])
									);
		}
		include_once 'Services/Form/classes/class.ilPropertyFormGUI.php';
		$form = new ilPropertyFormGUI();
		$form->setId('search_rcp');
		$form->setTitle($this->lng->txt('search_recipients'));
		$form->setFormAction($ilCtrl->getFormAction($this, 'search'));
		
		$inp = new ilTextInputGUI($this->lng->txt("search_for"), 'search');
		$inp->setSize(30);
		$dsDataLink = $ilCtrl->getLinkTarget($this, 'lookupRecipientAsync', '', true, false);
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
		$this->umail->savePostData($_SESSION["AccountId"],
									$files,
									ilUtil::securePlainString($_POST["rcp_to"]),
									ilUtil::securePlainString($_POST["rcp_cc"]),
									ilUtil::securePlainString($_POST["rcp_bcc"]),
									$_POST["m_type"],
									ilUtil::securePlainString($_POST["m_email"]),
							 		ilUtil::securePlainString($_POST["m_subject"]),
									ilUtil::securePlainString($_POST["m_message"]),
									ilUtil::securePlainString($_POST['use_placeholders'])
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

	public function showForm()
	{
		global $rbacsystem, $ilUser, $ilCtrl, $lng, $ilTabs;

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.mail_new.html", "Services/Mail");
		$this->tpl->setTitle($this->lng->txt("mail"));
		
		$this->lng->loadLanguageModule("crs");

        if(ilMailFormCall::isRefererStored())
            $ilTabs->setBackTarget($lng->txt('back'), $ilCtrl->getLinkTarget($this, 'cancelMail'));

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
		$form_gui->setOpenTag(false);
		$this->tpl->setVariable('FORM_ACTION', $this->ctrl->getFormAction($this, 'sendMessage'));

		$this->tpl->setVariable('BUTTON_TO', $lng->txt("search_recipients"));
		$this->tpl->setVariable('BUTTON_COURSES_TO', $lng->txt("mail_my_courses"));
		$this->tpl->setVariable('BUTTON_GROUPS_TO', $lng->txt("mail_my_groups"));
		$this->tpl->setVariable('BUTTON_MAILING_LISTS_TO', $lng->txt("mail_my_mailing_lists"));
		
		$dsDataLink = $ilCtrl->getLinkTarget($this, 'lookupRecipientAsync', '', true);
		
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
				if(is_file($this->mfile->getMailPath() . '/' . $ilUser->getId() . "_" . $data))
				{
					$hidden = new ilHiddenInputGUI('attachments[]');
					$form_gui->addItem($hidden);
					$size = filesize($this->mfile->getMailPath() . '/' . $ilUser->getId() . "_" . $data);
					$label = $data . " [" . ilFormat::formatSize($size) . "]";
					$att->addItem($label);
					$hidden->setValue(urlencode($data));
				}
			}
		}
		$form_gui->addItem($att);

		// ONLY IF SYSTEM MAILS ARE ALLOWED
		if($rbacsystem->checkAccess("system_message",$this->umail->getMailObjectReferenceId()))
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


		// MESSAGE
		$inp = new ilTextAreaInputGUI($this->lng->txt('message_content'), 'm_message');
		//$inp->setValue(htmlspecialchars($mailData["m_message"], false));
		$inp->setValue($mailData["m_message"]);
		$inp->setRequired(false);
		$inp->setCols(60);
		$inp->setRows(10);

		// PLACEHOLDERS
		$chb = new ilCheckboxInputGUI($this->lng->txt('activate_serial_letter_placeholders'), 'use_placeholders');
		$chb->setOptionTitle($this->lng->txt('activate_serial_letter_placeholders'));
		$chb->setValue(1);
		$chb->setChecked(false);
		$form_gui->addItem($inp);

		include_once 'Services/Mail/classes/class.ilMailFormPlaceholdersPropertyGUI.php';
		$prop = new ilMailFormPlaceholdersPropertyGUI();
		
		$chb->addSubItem($prop);

		if ($mailData['use_placeholders'])
		{
			$chb->setChecked(true);
		}
		
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
            ilUtil::redirect(ilMailFormCall::getRefererRedirectUrl());
        else
            return $this->showForm();
    }

	/**
	 *
	 */
	protected function saveMailBeforeSearch()
	{
		/**
		 * @var $ilUser ilObjUser
		 */
		global $ilUser;

		// decode post values
		$files = array();
		if(is_array($_POST['attachments']))
		{
			foreach($_POST['attachments'] as $value)
			{
				$files[] = urldecode($value);
			}
		}

		$this->umail->savePostData($ilUser->getId(),
			$files,
			ilUtil::securePlainString($_POST['rcp_to']),
			ilUtil::securePlainString($_POST['rcp_cc']),
			ilUtil::securePlainString($_POST['rcp_bcc']),
			$_POST['m_type'],
			ilUtil::securePlainString($_POST['m_email']),
			ilUtil::securePlainString($_POST['m_subject']),
			ilUtil::securePlainString($_POST['m_message']),
			ilUtil::securePlainString($_POST['use_placeholders'])
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
}