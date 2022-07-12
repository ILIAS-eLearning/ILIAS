<?php declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

use ILIAS\HTTP\GlobalHttpState;
use ILIAS\Refinery\Factory as Refinery;

/**
 * Mail User Interface class. (only a start, mail scripts code should go here)
 *
 * @author Peter Gabriel <pgabriel@databay.de>
 */
class ilPDMailGUI
{
    private GlobalHttpState $http;
    private Refinery $refinery;
    protected ILIAS $ilias;
    protected ilRbacSystem $rbacsystem;
    protected ilLanguage $lng;
    protected ilObjUser $user;

    public function __construct()
    {
        global $DIC;
        $this->lng = $DIC->language();
        $this->rbacsystem = $DIC->rbac()->system();
        $this->ilias = $DIC['ilias'];
        $this->user = $DIC->user();
        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();
    }

    public function getPDMailHTML(int $a_mail_id, int $a_mobj_id) : string
    {
        $this->lng->loadLanguageModule('mail');

        //get the mail from user
        $umail = new ilMail($this->user->getId());

        // catch hack attempts
        if (!$this->rbacsystem->checkAccess('internal_mail', $umail->getMailObjectReferenceId())) {
            $this->ilias->raiseError($this->lng->txt('permission_denied'), $this->ilias->error_obj->WARNING);
        }

        $umail->markRead([$a_mail_id]);
        $mail_data = $umail->getMail($a_mail_id);

        $tpl = new ilTemplate('tpl.pd_mail.html', true, true, 'Services/Mail');

        if ($mail_data['attachments']) {
            $mailId = 0;
            if ($this->http->wrapper()->query()->has('mail_id')) {
                $mailId = $this->http->wrapper()->query()->retrieve('mail_id', $this->refinery->kindlyTo()->int());
            }
            foreach ($mail_data['attachments'] as $file) {
                $tpl->setCurrentBlock('a_row');
                $tpl->setVariable(
                    'HREF_DOWNLOAD',
                    'ilias.php?baseClass=ilMailGUI&amp;type=deliverFile&amp;mail_id='
                    . $mailId .
                        '&amp;filename=' . md5($file)
                );
                $tpl->setVariable('FILE_NAME', $file);
                $tpl->setVariable('TXT_DOWNLOAD', $this->lng->txt('download'));
                $tpl->parseCurrentBlock();
            }
            $tpl->setCurrentBlock('attachment');
            $tpl->setVariable('TXT_ATTACHMENT', $this->lng->txt('attachments'));
            $tpl->parseCurrentBlock();
        }

        $tpl->setVariable('TXT_FROM', $this->lng->txt('from'));

        /** @var ilObjUser|null $sender */
        $sender = ilObjectFactory::getInstanceByObjId($mail_data['sender_id'], false);
        if ($sender instanceof ilObjUser && $sender->getId() !== 0 && $sender->getId() !== ANONYMOUS_USER_ID) {
            $tpl->setCurrentBlock('pers_image');
            $tpl->setVariable('IMG_SENDER', $sender->getPersonalPicturePath('xsmall'));
            $tpl->setVariable('ALT_SENDER', htmlspecialchars($sender->getPublicName()));
            $tpl->parseCurrentBlock();

            $tpl->setVariable('PUBLIC_NAME', $sender->getPublicName());
        } elseif (null === $sender) {
            $tpl->setVariable(
                'PUBLIC_NAME',
                $mail_data['import_name'] . ' (' . $this->lng->txt('user_deleted') . ')'
            );
        } else {
            $tpl->setCurrentBlock('pers_image');
            $tpl->setVariable('IMG_SENDER', ilUtil::getImagePath('HeaderIconAvatar.svg'));
            $tpl->setVariable('ALT_SENDER', htmlspecialchars(ilMail::_getIliasMailerName()));
            $tpl->parseCurrentBlock();
            $tpl->setVariable('PUBLIC_NAME', ilMail::_getIliasMailerName());
        }

        $tpl->setVariable('TXT_TO', $this->lng->txt('mail_to'));
        $tpl->setVariable('TO', $umail->formatNamesForOutput((string) $mail_data['rcp_to']));

        if ($mail_data['rcp_cc']) {
            $tpl->setCurrentBlock('cc');
            $tpl->setVariable('TXT_CC', $this->lng->txt('cc'));
            $tpl->setVariable('CC', $umail->formatNamesForOutput((string) $mail_data['rcp_cc']));
            $tpl->parseCurrentBlock();
        }

        $tpl->setVariable('TXT_SUBJECT', $this->lng->txt('subject'));
        $tpl->setVariable('SUBJECT', htmlspecialchars($mail_data['m_subject']));

        $tpl->setVariable('TXT_DATE', $this->lng->txt('date'));
        $tpl->setVariable(
            'DATE',
            ilDatePresentation::formatDate(new ilDateTime($mail_data['send_time'], IL_CAL_DATETIME))
        );

        $tpl->setVariable('TXT_MESSAGE', $this->lng->txt('message'));
        // Note: For security reasons, ILIAS only allows Plain text strings in E-Mails.
        $tpl->setVariable(
            'MAIL_MESSAGE',
            nl2br(ilUtil::makeClickable(htmlspecialchars(ilUtil::securePlainString($mail_data['m_message']))))
        );

        return $tpl->get();
    }
}
