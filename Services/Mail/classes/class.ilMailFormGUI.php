<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
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

require_once 'Services/User/classes/class.ilObjUser.php';
require_once 'Services/Mail/classes/class.ilMailbox.php';
require_once 'Services/Mail/classes/class.ilFormatMail.php';
require_once 'classes/class.ilFileDataMail.php';

/**
* @author Jens Conze
* @version $Id$
*
* @ingroup ServicesMail
* @ilCtrl_Calls ilMailFormGUI: ilMailFolderGUI, ilMailAttachmentGUI, ilMailSearchGUI, ilMailSearchCoursesGUI, ilMailSearchGroupsGUI
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
		
		$this->ctrl->saveParameter($this, "mobj_id");

		$this->umail = new ilFormatMail($ilUser->getId());
		$this->mfile = new ilFileDataMail($ilUser->getId());
		$this->mbox = new ilMailBox($ilUser->getId());		
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
		
		// Note: For security reasons, ILIAS only allows Plain text strings in E-Mails.
		$f_message = $this->umail->formatLinebreakMessage(ilUtil::securePlainString($_POST['m_message']));
		$this->umail->setSaveInSentbox(true);		

		$m_type = isset($_POST["m_type"]) ? $_POST["m_type"] : array("normal");

		// Note: For security reasons, ILIAS only allows Plain text strings in E-Mails.
		if($errorMessage = $this->umail->sendMail(
				ilUtil::securePlainString($_POST['rcp_to']),
				ilUtil::securePlainString($_POST['rcp_cc']),
				ilUtil::securePlainString($_POST['rcp_bcc']),
				ilUtil::securePlainString($_POST['m_subject']), $f_message,
				$_POST['attachments'],
//				$_POST['m_type'],
				$m_type,
				ilUtil::securePlainString($_POST['use_placeholders'])
				)
			)
		{
			ilUtil::sendInfo($errorMessage);
		}
		else
		{
			$this->umail->savePostData($ilUser->getId(), array(), "", "", "", "", "", "", "", "");			
			
			#$this->ctrl->setParameterByClass("ilmailfoldergui", "mobj_id", $this->mbox->getSentFolder());
			$this->ctrl->setParameterByClass('ilmailgui', 'type', 'message_sent');
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
		
		if(isset($_SESSION["draft"]))
		{
			// Note: For security reasons, ILIAS only allows Plain text strings in E-Mails.
			$this->umail->updateDraft($draftsId,$_POST["attachments"],
				ilUtil::securePlainString($_POST["rcp_to"]),
				ilUtil::securePlainString($_POST["rcp_cc"]),
				ilUtil::securePlainString($_POST["rcp_bcc"]),
				$_POST["m_type"],
				ilUtil::securePlainString($_POST["m_email"]),
				ilUtil::securePlainString($_POST["m_subject"]),
				ilUtil::securePlainString($_POST["m_message"]),
				$_SESSION["draft"],
				ilUtil::securePlainString($_POST['use_placeholders'])
			);
			#session_unregister("draft");
			#ilUtil::sendInfo($this->lng->txt("mail_saved"),true);
			#ilUtil::redirect("ilias.php?baseClass=ilMailGUI&mobj_id=".$mbox->getInboxFolder());
			
			unset($_SESSION["draft"]);
			ilUtil::sendInfo($this->lng->txt("mail_saved"), true);
			$this->ctrl->redirectByClass("ilmailfoldergui");
		}
		else
		{
			if ($this->umail->sendInternalMail($draftsId,$_SESSION["AccountId"],$_POST["attachments"],
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
				$this->ctrl->redirectByClass("ilmailfoldergui");
			}
			else
			{
				ilUtil::sendInfo($this->lng->txt("mail_send_error"));
			}
		}
		
		$this->showForm();
	}

	public function searchRcpTo()
	{
		$_SESSION["mail_search"] = 'to';
		ilUtil::sendInfo($this->lng->txt("mail_insert_query"));

		$this->showSearchForm();
	}

	public function searchUsers($save = true)
	{
		global $ilUser, $ilCtrl;

		if ($save)
		{
			// Note: For security reasons, ILIAS only allows Plain text strings in E-Mails.
			$this->umail->savePostData($ilUser->getId(),
										 $_POST["attachments"],
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
		$form->setFormAction($ilCtrl->getFormAction($this, 'search'));
		
		$inp = new ilTextInputGUI($this->lng->txt("search_for"), 'search');
		$inp->setSize(30);
		if (strlen(trim($_SESSION["mail_search_search"])) > 0)
		{
			$inp->setValue(ilUtil::prepareFormOutput(trim($_SESSION["mail_search_search"]), true));
		}
		$form->addItem($inp);
		
		$chb = new ilCheckboxInputGUI($this->lng->txt("mail_search_addressbook"), 'type_addressbook');
		if ($_SESSION['mail_search_type_addressbook'])
			$chb->setChecked(true);
		$inp->addSubItem($chb);

		$chb = new ilCheckboxInputGUI($this->lng->txt("mail_search_system"), 'type_system');
		if ($_SESSION['mail_search_type_system'])
			$chb->setChecked(true);
		$inp->addSubItem($chb);
		
		$form->addCommandButton('search', $this->lng->txt("search"));
		$form->addCommandButton('cancelSearch', $this->lng->txt("cancel"));
		
		$this->tpl->setContent($form->getHtml());
		$this->tpl->show();
	}

	public function searchCoursesTo()
	{
		global $ilUser;

		// Note: For security reasons, ILIAS only allows Plain text strings in E-Mails.
		$this->umail->savePostData($ilUser->getId(),
									$_POST["attachments"],
									ilUtil::securePlainString($_POST["rcp_to"]),
									ilUtil::securePlainString($_POST["rcp_cc"]),
									ilUtil::securePlainString($_POST["rcp_bcc"]),
									$_POST["m_type"],
									ilUtil::securePlainString($_POST["m_email"]),
									ilUtil::securePlainString($_POST["m_subject"]),
									ilUtil::securePlainString($_POST["m_message"]),
									ilUtil::securePlainString($_POST['use_placeholders'])
									);

		if ($_SESSION["search_crs"])
		{
			$this->ctrl->setParameterByClass("ilmailsearchcoursesgui", "cmd", "showMembers");
		}
		$this->ctrl->setParameterByClass("ilmailsearchcoursesgui", "ref", "mail");
		$this->ctrl->redirectByClass("ilmailsearchcoursesgui");
	}

	public function searchGroupsTo()
	{
		global $ilUser;

		// Note: For security reasons, ILIAS only allows Plain text strings in E-Mails.
		$this->umail->savePostData($ilUser->getId(),
									$_POST["attachments"],
									ilUtil::securePlainString($_POST["rcp_to"]),
									ilUtil::securePlainString($_POST["rcp_cc"]),
									ilUtil::securePlainString($_POST["rcp_bcc"]),
									$_POST["m_type"],
									ilUtil::securePlainString($_POST["m_email"]),
									ilUtil::securePlainString($_POST["m_subject"]),
									ilUtil::securePlainString($_POST["m_message"]),
									ilUtil::securePlainString($_POST['use_placeholders'])
								);

		$this->ctrl->setParameterByClass("ilmailsearchgroupsgui", "ref", "mail");
		$this->ctrl->redirectByClass("ilmailsearchgroupsgui");
	}

	public function searchRcpCc()
	{
		$_SESSION["mail_search"] = 'cc';
		ilUtil::sendInfo($this->lng->txt("mail_insert_query"));

		$this->showSearchForm();
	}

	public function searchRcpBc()
	{
		$_SESSION["mail_search"] = 'bc';
		ilUtil::sendInfo($this->lng->txt("mail_insert_query"));

		$this->showSearchForm();
	}

	public function showSearchForm()
	{
		global $ilUser;

		$this->tpl->setCurrentBlock("search");
		$this->tpl->setVariable("TXT_SEARCH_FOR",$this->lng->txt("search_for"));
		$this->tpl->setVariable("TXT_SEARCH_SYSTEM",$this->lng->txt("mail_search_system"));
		$this->tpl->setVariable("TXT_SEARCH_ADDRESS",$this->lng->txt("mail_search_addressbook"));

		if ($pref = $ilUser->getPref("mail_search"))
		{
				if ($pref == "system" || $pref == "all") $this->tpl->setVariable("SEARCH_SYSTEM_CHECKED", "checked=\"checked\"");
				if ($pref == "addressbook" || $pref == "all") $this->tpl->setVariable("SEARCH_ADDRESS_CHECKED", "checked=\"checked\"");
		}
		else
		{
				$this->tpl->setVariable("SEARCH_SYSTEM_CHECKED", "checked=\"checked\"");
		}

		$this->tpl->setVariable("BUTTON_SEARCH",$this->lng->txt("search"));
		$this->tpl->setVariable("BUTTON_CANCEL",$this->lng->txt("cancel"));
		if (strlen(trim($_POST['search'])) > 0)
		{
			$this->tpl->setVariable("VALUE_SEARCH_FOR", ilUtil::prepareFormOutput(trim($_POST["search"]), true));
		}
		$this->tpl->parseCurrentBlock();

		$this->showForm();
	}

	public function search()
	{
		global $ilUser;
		
		$_SESSION["mail_search_search"] = $_POST["search"];
		$_SESSION["mail_search_type_system"] = $_POST["type_system"];
		$_SESSION["mail_search_type_addressbook"] = $_POST["type_addressbook"];

		// IF NO TYPE IS GIVEN SEARCH IN BOTH 'system' and 'addressbook'
		if(!$_SESSION["mail_search_type_system"] &&
		   !$_SESSION["mail_search_type_addressbook"])
		{
			$_SESSION["mail_search_type_system"] = 1;
			$_SESSION["mail_search_type_addressbook"] = 1;
			$ilUser->writePref("mail_search", "all");
		}
		if (strlen(trim($_SESSION["mail_search_search"])) == 0)
		{
			ilUtil::sendInfo($this->lng->txt("mail_insert_query"));
			#$this->showSearchForm();
			$this->searchUsers(false);
		}
		else if(strlen(trim($_SESSION["mail_search_search"])) < 3)
		{
			$this->lng->loadLanguageModule('search');
			ilUtil::sendInfo($this->lng->txt('search_minimum_three'));
			#$this->showSearchForm();
			$this->searchUsers(false);
		}
		else
		{
			$this->ctrl->setParameterByClass("ilmailsearchgui", "search", urlencode($_SESSION["mail_search_search"]));
			if($_SESSION["mail_search_type_system"])
			{
				$this->ctrl->setParameterByClass("ilmailsearchgui", "system", 1);
			}
			if($_SESSION["mail_search_type_addressbook"])
			{
				$this->ctrl->setParameterByClass("ilmailsearchgui", "addressbook", 1);
			}
			$this->ctrl->redirectByClass("ilmailsearchgui");
		}
	}

	public function cancelSearch()
	{
		unset($_SESSION["mail_search"]);

		#$this->showForm();
		$this->searchResults();
	}

	public function editAttachments()
	{
		// Note: For security reasons, ILIAS only allows Plain text messages.
		$this->umail->savePostData($_SESSION["AccountId"],
									$_POST["attachments"],
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
		global $rbacsystem, $ilUser, $ilCtrl, $lng;

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.mail_new.html", "Services/Mail");
		$this->tpl->setVariable("HEADER", $this->lng->txt("mail"));
		
		$this->lng->loadLanguageModule("crs");

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
				$mailData["m_message"] = $this->umail->appendSignature();
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
				$mailData["m_message"] = $this->umail->appendSignature();
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
				
				
				$mailData["m_message"] = $this->umail->appendSignature();
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
		
				$mailData['m_message'] = $_POST["additional_message_text"].chr(13).chr(10).$this->umail->appendSignature();
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
		$form_gui->setOpenTag(false);
		$this->tpl->setVariable('FORM_ACTION', $this->ctrl->getFormAction($this, 'sendMessage'));

		$this->tpl->setVariable('BUTTON_TO', $lng->txt("search_recipients"));
		$this->tpl->setVariable('BUTTON_COURSES_TO', $lng->txt("mail_my_courses"));
		$this->tpl->setVariable('BUTTON_GROUPS_TO', $lng->txt("mail_my_groups"));
		
		$dsSchema = array('response.results', 'login', 'firstname', 'lastname');
		$dsFormatCallback = 'formatAutoCompleteResults';
		$dsDataLink = $ilCtrl->getLinkTarget($this, 'lookupRecipientAsync');
		$dsDelimiter = array(',');
		
		// RECIPIENT
		$inp = new ilTextInputGUI($this->lng->txt('mail_to'), 'rcp_to');
		$inp->setRequired(true);
		$inp->setSize(50);
		$inp->setValue(ilUtil::htmlencodePlainString($mailData["rcp_to"], false));
		$inp->setDataSource($dsDataLink);
		$inp->setDataSourceSchema($dsSchema);
		$inp->setDataSourceResultFormat($dsFormatCallback);
		$inp->setDataSourceDelimiter($dsDelimiter);
		$form_gui->addItem($inp);

		// CC
		$inp = new ilTextInputGUI($this->lng->txt('cc'), 'rcp_cc');
		$inp->setSize(50);
		$inp->setValue(ilUtil::htmlencodePlainString($mailData["rcp_cc"], false));
		$inp->setDataSource($dsDataLink);
		$inp->setDataSourceSchema($dsSchema);
		$inp->setDataSourceResultFormat($dsFormatCallback);
		$inp->setDataSourceDelimiter($dsDelimiter);
		$form_gui->addItem($inp);

		// BCC
		$inp = new ilTextInputGUI($this->lng->txt('bc'), 'rcp_bcc');
		$inp->setSize(50);
		$inp->setValue(ilUtil::htmlencodePlainString($mailData["rcp_bcc"], false));
		$inp->setDataSource($dsDataLink);
		$inp->setDataSourceSchema($dsSchema);
		$inp->setDataSourceResultFormat($dsFormatCallback);
		$inp->setDataSourceDelimiter($dsDelimiter);
		$form_gui->addItem($inp);

		// SUBJECT
		$inp = new ilTextInputGUI($this->lng->txt('subject'), 'm_subject');
		$inp->setSize(50);
		$inp->setRequired(true);
		$inp->setValue(ilUtil::htmlencodePlainString($mailData["m_subject"], false));
		$form_gui->addItem($inp);

		// Attachments
		include_once 'Services/Mail/classes/class.ilMailFormAttachmentFormPropertyGUI.php';
		$att = new ilMailFormAttachmentPropertyGUI($this->lng->txt( ($mailData["attachments"]) ? 'edit' : 'add' ));
		

		if (is_array($mailData["attachments"]) && count($mailData["attachments"]))
		{
			foreach($mailData["attachments"] as $key => $data)
			{
				$hidden = new ilHiddenInputGUI('attachments[]');
				$hidden->setValue($data);
				$form_gui->addItem($hidden);
				$size = round(filesize($this->mfile->getMailPath() . '/' . $ilUser->getId() . "_" . $data) / 1024);
				if ($size < 1) $size = 1;
				$label = $data . " [" . number_format($size, 0, ".", "") . " KByte]";
				$att->addItem($label);
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
		$inp->setValue(htmlspecialchars($mailData["m_message"], false));
		$inp->setRequired(false);
		$inp->setCols(60);
		$inp->setRows(10);

		// PLACEHOLDERS
		$chb = new ilCheckboxInputGUI($this->lng->txt('placeholder'), 'use_placeholders');
		$chb->setOptionTitle($this->lng->txt('activate_serial_letter_placeholders'));
		$chb->setValue(1);
		$chb->setChecked(false);
		$chb->setAdditionalAttributes('onclick="togglePlaceholdersBox(this.checked);"');
		$form_gui->addItem($inp);

		include_once 'Services/Mail/classes/class.ilMailFormPlaceholdersPropertyGUI.php';
		$prop = new ilMailFormPlaceholdersPropertyGUI();
		
		$chb->addSubItem($prop);

		if ($mailData['use_placeholders'])
		{
			$chb->setChecked(true);
		}
		else
		{
			$this->tpl->touchBlock('hide_placeholders');
		}
		$form_gui->addItem($chb);

		$form_gui->addCommandButton('sendMessage', $this->lng->txt('send'));
		$form_gui->addCommandButton('saveDraft', $this->lng->txt('save_message'));

		$this->tpl->parseCurrentBlock();

		$this->tpl->setVariable('FORM', $form_gui->getHTML());

		$this->tpl->addJavaScript('Services/Mail/js/ilMailComposeFunctions.js');
		$this->tpl->show();
	}

	public function lookupRecipientAsync()
	{
		global $ilUser, $rbacsystem;
		include_once 'Services/JSON/classes/class.ilJsonUtil.php';
		$search = "%" . $_REQUEST["query"] . "%";
		$result = new stdClass();
		$result->response = new stdClass();
		$result->response->results = array();
		if (!$search)
		{
			$result->response->total = 0;
			echo ilJsonUtil::encode($result);
			exit;
		}
		global $ilDB;
		$ilDB->setLimit(0,20);
		
		$allow_smtp = $rbacsystem->checkAccess('smtp_mail', MAIL_SETTINGS_ID);
		
		$query_res = $ilDB->queryF(
			'SELECT DISTINCT
				abook.login as login,
				abook.firstname as firstname,
				abook.lastname as lastname,
				"addressbook" as type
			FROM addressbook as abook
			WHERE abook.user_id = %s
			AND abook.login IS NOT NULL
			AND (
				abook.login LIKE %s OR
				abook.firstname LIKE %s OR
				abook.lastname LIKE %s
			)
			UNION
			SELECT DISTINCT
				abook.email as login,
				abook.firstname as firstname,
				abook.lastname as lastname,
				"addressbook" as type
			FROM addressbook as abook
			WHERE 1='.($allow_smtp ? 1 : 0).'
			AND abook.user_id = %s
			AND abook.login IS NULL
			AND (
				abook.email LIKE %s OR
				abook.firstname LIKE %s OR
				abook.lastname LIKE %s
			)			
			UNION
			SELECT DISTINCT
				mail.rcp_to as login,
				"" as firstname,
				"" as lastname,
				"mail" as type
			FROM mail
			WHERE mail.rcp_to LIKE %s
				AND sender_id = %s',
			array('integer', 'text', 'text', 'text', 'integer', 'text', 'text', 'text', 'text', 'integer'),
			array($ilUser->getId(), $search, $search, $search, $ilUser->getId(), $search, $search, $search, $search, $ilUser->getId())
		);
		
		$setMap = array();
		$i = 0;
		while ($row = $query_res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			if ($i > 20)
				break;
			if (isset($setMap[$row->login]))
				continue;
			$parts = array();
			if (strpos($row->login, ',') || strpos($row->login, ';'))
			{
				$parts = split("[ ]*[;,][ ]*", trim($row->login));
				foreach($parts as $part)
				{
					$tmp = new stdClass();
					$tmp->login = $part;
					$i++;
					$setMap[$part] = 1;
				}
			}
			else
			{
				$tmp = new stdClass();
				$tmp->login = $row->login;
				if ($row->public_profile == 'y' || $row->type = 'addressbook')
				{
					$tmp->firstname = $row->firstname;
					$tmp->lastname = $row->lastname;
				}
				$result->response->results[] = $tmp;
				$i++;
				$setMap[$row->login] = 1;
			}

		}
		$result->response->total = count($result->response->results);
		echo ilJsonUtil::encode($result);
		exit;
	}
}
?>