<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Mail/classes/class.ilMail.php';

/**
 * Mail User Interface class. (only a start, mail scripts code should go here)
 *
 * @author Peter Gabriel <pgabriel@databay.de>
 * @version $Id$
 */
class ilPDMailGUI
{
	/**
	 * Get Mail HTML for Personal Desktop Mail Display
	 */
	public function getPDMailHTML($a_mail_id, $a_mobj_id)
	{
		global $lng, $rbacsystem, $ilias;

		$lng->loadLanguageModule('mail');

		//get the mail from user
		$umail = new ilMail($_SESSION['AccountId']);

		// catch hack attempts
		if(!$rbacsystem->checkAccess('internal_mail', $umail->getMailObjectReferenceId()))
		{
			$ilias->raiseError($lng->txt('permission_denied'), $ilias->error_obj->WARNING);
		}

		$umail->markRead(array($a_mail_id));
		$mail_data = $umail->getMail($a_mail_id);

		$tpl = new ilTemplate('tpl.pd_mail.html', true, true, 'Services/Mail');

		// attachments
		if($mail_data['attachments'])
		{
			foreach($mail_data['attachments'] as $file)
			{
				$tpl->setCurrentBlock('a_row');
				$tpl->setVariable('HREF_DOWNLOAD',
					'ilias.php?baseClass=ilMailGUI&amp;type=deliverFile&amp;mail_id=' . $_GET['mail_id'] .
						'&amp;filename=' . md5($file));
				$tpl->setVariable('FILE_NAME', $file);
				$tpl->setVariable('TXT_DOWNLOAD', $lng->txt('download'));
				$tpl->parseCurrentBlock();
			}
			$tpl->setCurrentBlock('attachment');
			$tpl->setVariable('TXT_ATTACHMENT', $lng->txt('attachments'));
			$tpl->parseCurrentBlock();
		}

		$tpl->setVariable('TXT_FROM', $lng->txt('from'));

		/**
		 * @var $sender ilObjUser
		 */
		$sender = ilObjectFactory::getInstanceByObjId($mail_data['sender_id'], false);
		if($sender && $sender->getId() != ANONYMOUS_USER_ID)
		{
			$tpl->setCurrentBlock('pers_image');
			$tpl->setVariable('IMG_SENDER', $sender->getPersonalPicturePath('xsmall'));
			$tpl->setVariable('ALT_SENDER', $sender->getPublicName());
			$tpl->parseCurrentBlock();

			$tpl->setVariable('PUBLIC_NAME', $sender->getPublicName());
		}
		else if(!$sender)
		{
			$tpl->setVariable('PUBLIC_NAME', $mail_data['import_name'] . ' (' . $lng->txt('user_deleted') . ')');
		}
		else
		{
			$tpl->setCurrentBlock('pers_image');
			$tpl->setVariable('IMG_SENDER', ilUtil::getImagePath('HeaderIconAvatar.svg'));
			$tpl->setVariable('ALT_SENDER', ilMail::_getIliasMailerName());
			$tpl->parseCurrentBlock();
			$tpl->setVariable('PUBLIC_NAME', ilMail::_getIliasMailerName());
		}

		$tpl->setVariable('TXT_TO', $lng->txt('mail_to'));
		$tpl->setVariable('TO', $umail->formatNamesForOutput($mail_data['rcp_to']));

		if($mail_data['rcp_cc'])
		{
			$tpl->setCurrentBlock('cc');
			$tpl->setVariable('TXT_CC', $lng->txt('cc'));
			$tpl->setVariable('CC', $umail->formatNamesForOutput($mail_data['rcp_cc']));
			$tpl->parseCurrentBlock();
		}

		$tpl->setVariable('TXT_SUBJECT', $lng->txt('subject'));
		$tpl->setVariable('SUBJECT', htmlspecialchars($mail_data['m_subject']));

		$tpl->setVariable('TXT_DATE', $lng->txt('date'));
		$tpl->setVariable('DATE', ilDatePresentation::formatDate(new ilDateTime($mail_data['send_time'], IL_CAL_DATETIME)));

		$tpl->setVariable('TXT_MESSAGE', $lng->txt('message'));
		// Note: For security reasons, ILIAS only allows Plain text strings in E-Mails.
		$tpl->setVariable('MAIL_MESSAGE', nl2br(ilUtil::makeClickable(htmlspecialchars(ilUtil::securePlainString($mail_data['m_message'])))));

		return $tpl->get();
	}
}