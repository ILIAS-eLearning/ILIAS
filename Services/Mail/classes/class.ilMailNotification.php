<?php

declare(strict_types=1);

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

/**
 * Base class for course/group mail notifications
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @ingroup ServicesMembership
 */
abstract class ilMailNotification
{
    public const SUBJECT_TITLE_LENGTH = 60;
    protected int $type;
    protected int $sender;
    protected ?ilMail $mail = null;
    protected string $subject = '';
    protected string $body = '';
    protected array $attachments = [];
    protected ilLanguage $language;
    protected array $lang_modules = [];
    protected array $recipients = [];
    protected int $ref_id;
    protected int $obj_id;
    protected string $obj_type;
    protected array $additional_info = [];
    protected ilWorkspaceTree $wsp_tree;
    protected ilWorkspaceAccessHandler $wsp_access_handler;

    public function __construct(protected bool $is_in_wsp = false)
    {
        global $DIC;
        $this->setSender(ANONYMOUS_USER_ID);
        $this->language = ilLanguageFactory::_getLanguage($DIC->language()->getDefaultLanguage());

        if ($this->is_in_wsp) {
            $this->wsp_tree = new ilWorkspaceTree($DIC->user()->getId()); // owner of tree is irrelevant
            $this->wsp_access_handler = new ilWorkspaceAccessHandler($this->wsp_tree);
        }
    }

    public function setType(int $a_type): void
    {
        $this->type = $a_type;
    }

    public function getType(): int
    {
        return $this->type;
    }

    public function setSender(int $a_usr_id): void
    {
        $this->sender = $a_usr_id;
    }

    public function getSender(): int
    {
        return $this->sender;
    }

    protected function setSubject(string $a_subject): string
    {
        return $this->subject = $a_subject;
    }

    protected function getSubject(): string
    {
        return $this->subject;
    }

    protected function setBody(string $a_body): void
    {
        $this->body = $a_body;
    }

    protected function appendBody(string $a_body): string
    {
        return $this->body .= $a_body;
    }

    protected function getBody(): string
    {
        return $this->body;
    }

    public function setRecipients(array $a_rcp): void
    {
        $this->recipients = $a_rcp;
    }

    public function getRecipients(): array
    {
        return $this->recipients;
    }

    public function setAttachments(array $a_att): void
    {
        $this->attachments = $a_att;
    }

    public function getAttachments(): array
    {
        return $this->attachments;
    }

    public function setLangModules(array $a_modules): void
    {
        $this->lang_modules = $a_modules;
    }

    protected function initLanguage(int $a_usr_id): void
    {
        $this->language = $this->getUserLanguage($a_usr_id);
    }

    public function getUserLanguage(int $a_usr_id): ilLanguage
    {
        $language = ilLanguageFactory::_getLanguageOfUser($a_usr_id);
        $language->loadLanguageModule('mail');

        foreach ($this->lang_modules as $lmod) {
            $language->loadLanguageModule($lmod);
        }

        return $language;
    }

    protected function initLanguageByIso2Code(string $a_code = ''): void
    {
        $this->language = ilLanguageFactory::_getLanguage($a_code);
        $this->language->loadLanguageModule('mail');

        foreach ($this->lang_modules as $lmod) {
            $this->language->loadLanguageModule($lmod);
        }
    }

    protected function setLanguage(ilLanguage $a_language): void
    {
        $this->language = $a_language;
    }

    protected function getLanguage(): ilLanguage
    {
        return $this->language;
    }

    protected function getLanguageText(string $a_keyword): string
    {
        return str_replace('\n', "\n", $this->getLanguage()->txt($a_keyword));
    }

    public function setRefId(int $a_id): void
    {
        if (!$this->is_in_wsp) {
            $this->ref_id = $a_id;
            $obj_id = ilObject::_lookupObjId($this->ref_id);
        } else {
            $this->ref_id = $a_id;
            $obj_id = $this->wsp_tree->lookupObjectId($this->getRefId());
        }

        $this->setObjId($obj_id);
    }

    public function getRefId(): int
    {
        return $this->ref_id;
    }

    public function getObjId(): int
    {
        return $this->obj_id;
    }

    public function setObjId(int $a_obj_id): void
    {
        $this->obj_id = $a_obj_id;
        $this->obj_type = ilObject::_lookupType($this->obj_id);
    }

    public function getObjType(): string
    {
        return $this->obj_type;
    }

    public function setAdditionalInformation(array $a_info): void
    {
        $this->additional_info = $a_info;
    }

    public function getAdditionalInformation(): array
    {
        return $this->additional_info;
    }

    protected function getObjectTitle(bool $a_shorten = false): string
    {
        if ($this->getObjId() === 0) {
            return '';
        }
        $txt = ilObject::_lookupTitle($this->getObjId());
        if ($a_shorten) {
            $txt = ilStr::shortenTextExtended($txt, self::SUBJECT_TITLE_LENGTH, true);
        }
        return $txt;
    }

    public function sendMail(array $a_rcp, bool $a_parse_recipients = true): void
    {
        $recipients = [];
        foreach ($a_rcp as $rcp) {
            if ($a_parse_recipients) {
                $recipients[] = ilObjUser::_lookupLogin($rcp);
            } else {
                $recipients[] = $rcp;
            }
        }
        $recipients = implode(',', $recipients);
        $errors = $this->getMail()->enqueue(
            $recipients,
            '',
            '',
            $this->getSubject(),
            $this->getBody(),
            $this->getAttachments()
        );
        if ($errors !== []) {
            ilLoggerFactory::getLogger('mail')->dump($errors, ilLogLevel::ERROR);
        }
    }

    protected function initMail(): ilMail
    {
        return $this->mail = new ilMail($this->getSender());
    }

    protected function getMail(): ilMail
    {
        return is_object($this->mail) ? $this->mail : $this->initMail();
    }

    protected function createPermanentLink(array $a_params = [], string $a_append = ''): ?string
    {
        if ($this->getRefId() !== 0) {
            if (!$this->is_in_wsp) {
                return ilLink::_getLink($this->ref_id, $this->getObjType(), $a_params, $a_append);
            }
            return ilWorkspaceAccessHandler::getGotoLink($this->getRefId(), $this->getObjId(), $a_append);
        }
        return ilLink::_getLink(ROOT_FOLDER_ID, 'root');
    }

    protected function userToString(int $a_usr_id): string
    {
        $name = ilObjUser::_lookupName($a_usr_id);
        return ($name['title'] ? $name['title'] . ' ' : '') .
            ($name['firstname'] ? $name['firstname'] . ' ' : '') .
            ($name['lastname'] ? $name['lastname'] . ' ' : '');
    }

    protected function isRefIdAccessible(int $a_user_id, int $a_ref_id, string $a_permission = "read"): bool
    {
        global $DIC;

        // no given permission == accessible

        if (!$this->is_in_wsp) {
            if (trim($a_permission) &&
                !$DIC->access()->checkAccessOfUser(
                    $a_user_id,
                    $a_permission,
                    "",
                    $a_ref_id,
                    $this->getObjType()
                )) {
                return false;
            }
        } elseif (
            trim($a_permission) &&
            !$this->wsp_access_handler->checkAccessOfUser(
                $this->wsp_tree,
                $a_user_id,
                $a_permission,
                "",
                $a_ref_id,
                $this->getObjType()
            )
        ) {
            return false;
        }

        return true;
    }

    public function getBlockBorder(): string
    {
        return "----------------------------------------\n";
    }
}
