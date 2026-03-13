<?php

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
 * Wrapper classes for system notifications
 *
 * @see FeatureWiki/Guidelines/System Notification Guideline
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 *
 * @phpstan-type SystemNotificationAdditionalInfo array{
 *     caption: string,
 *     content: string,
 *     is_multiline: bool,
 *     is_direct_translation: bool
 * }
 */
class ilSystemNotification extends ilMailNotification
{
    protected string $subject_lang_id = "";
    protected string $subject_direct = "";
    protected string $introduction = "";
    protected string $introduction_direct = "";
    protected string $task = "";
    protected string $reason = "";
    /** @var list<SystemNotificationAdditionalInfo> */
    protected array $additional = [];
    protected string $goto_caption = "";
    protected int $changed_by = 0;
    protected ?array $all_ref_ids = [];

    public function __construct(protected bool $is_in_wsp = false)
    {
        parent::__construct($is_in_wsp);
        $this->ref_id = 0;
    }

    public function setSubjectLangId(string $a_lang_id): void
    {
        $this->subject_lang_id = $a_lang_id;
    }

    public function setSubjectDirect(string $a_text): void
    {
        $this->subject_direct = trim($a_text);
    }

    public function setIntroductionLangId(string $a_lang_id): void
    {
        $this->introduction = $a_lang_id;
    }

    public function setIntroductionDirect(string $a_text): void
    {
        $this->introduction_direct = trim($a_text);
    }

    public function setTaskLangId(string $a_lang_id): void
    {
        $this->task = $a_lang_id;
    }

    public function setReasonLangId(string $a_lang_id): void
    {
        $this->reason = $a_lang_id;
    }

    public function setGotoLangId(string $a_lang_id): void
    {
        $this->goto_caption = $a_lang_id;
    }

    public function setChangedByUserId(int $a_id): void
    {
        $this->changed_by = $a_id;
    }

    /**
     * Add additional information
     */
    public function addAdditionalInfo(
        string $a_lang_id,
        string $a_value,
        bool $a_multiline = false,
        bool $a_lang_direct = false
    ): void {
        $this->additional[] = [
            'caption' => $a_lang_id,
            'content' => trim($a_value),
            'is_direct_translation' => $a_lang_direct,
            'is_multiline' => $a_multiline
        ];
    }

    /**
     * Send notification(s)
     *
     * @param array $a_user_ids
     * @param ?string $a_goto_additional
     * @param string $a_permission
     * @return array recipient ids
     */
    public function sendMailAndReturnRecipients(
        array $a_user_ids,
        ?string $a_goto_additional = null,
        string $a_permission = "read"
    ): array {
        $this->all_ref_ids = null;

        // prepare object related info
        if ($this->getObjId()) {
            if (!$this->getRefId()) {
                // try to find ref_id(s)
                if (!$this->is_in_wsp) {
                    $ref_ids = ilObject::_getAllReferences($this->getObjId());
                    if (count($ref_ids) === 1) {
                        $this->ref_id = array_shift($ref_ids);
                    } else {
                        $this->all_ref_ids = $ref_ids;
                    }
                }
            } elseif ($this->is_in_wsp) { // #11680
                $this->ref_id = $this->wsp_tree->lookupNodeId($this->getObjId());
            }

            // default values
            if (!$this->goto_caption) {
                $this->goto_caption = "url";
            }
        }

        $recipient_ids = array();
        foreach (array_unique($a_user_ids) as $user_id) {
            // author of change should not get notification
            if ($this->changed_by === $user_id) {
                continue;
            }
            if ($this->composeAndSendMail($user_id, $a_goto_additional, $a_permission)) {
                $recipient_ids[] = $user_id;
            }
        }

        return $recipient_ids;
    }

    /**
     * Compose notification to single recipient
     */
    public function compose(
        int $a_user_id,
        ?string $a_goto_additional = null,
        string $a_permission = "read",
        bool $a_append_signature_direct = false
    ): bool {
        $find_ref_id = false;
        $this->initLanguage($a_user_id);
        $this->initMail();
        if ($this->subject_direct) {
            $this->setSubject($this->subject_direct);
        } else {
            $this->setSubject(
                sprintf($this->getLanguageText($this->subject_lang_id), $this->getObjectTitle(true))
            );
        }

        $this->setBody(ilMail::getSalutation($a_user_id, $this->getLanguage()));
        $this->appendBody("\n\n");

        if ($this->introduction) {
            $this->appendBody($this->getLanguageText($this->introduction));
            $this->appendBody("\n\n");
        }

        if ($this->introduction_direct) {
            $this->appendBody($this->introduction_direct);
            $this->appendBody("\n\n");
        }

        if ($this->task) {
            $this->appendBody($this->getLanguageText($this->task));
            $this->appendBody("\n\n");
        }

        // details table
        if ($this->getObjId()) {
            $this->appendBody($this->getLanguageText("obj_" . $this->getObjType()) . ": " .
                $this->getObjectTitle() . "\n");
        }

        if (!empty($this->additional)) {
            $num_addtional_sections = 0;
            $render_opening_block_border = true;

            /** @var SystemNotificationAdditionalInfo $item */
            foreach ($this->additional as $item) {
                if ($num_addtional_sections > 0) {
                    $this->appendBody("\n");
                }

                $caption = $item['caption'];
                if ($caption !== '') {
                    $caption = $item['is_direct_translation']
                        ? $caption
                        : $this->getLanguageText($caption);
                }

                if ($item['is_multiline']) {
                    if ($caption !== '') {
                        $this->appendBody($caption);
                        $this->appendBody("\n");
                        // a) If we render a new caption, we ensure a new opening block border is rendered
                        $render_opening_block_border = true;
                    }

                    if ($render_opening_block_border) {
                        $this->appendBody($this->getBlockBorder());
                    }

                    $this->appendBody("\n");
                    $this->appendBody($item['content']);
                    $this->appendBody("\n");
                    $this->appendBody($this->getBlockBorder());

                    // b) We rendered a closing block border, the next block does not need an opening block border
                    $render_opening_block_border = false;
                } else {
                    if ($caption) {
                        $caption .= ': ';
                    }

                    $this->appendBody($caption . $item['content']);

                    // c) If a single line was rendered, the next block needs an opening border
                    $render_opening_block_border = true;
                }

                ++$num_addtional_sections;
            }
        }
        $this->body = trim($this->body);
        $this->appendBody("\n\n");

        if ($this->changed_by) {
            $this->appendBody($this->getLanguageText("system_notification_installation_changed_by") . ": " .
                ilUserUtil::getNamePresentation($this->changed_by));
            $this->appendBody("\n\n");
        }

        if ($this->getObjId()) {
            // try to find accessible ref_id
            if (!$this->getRefId() && $this->all_ref_ids) {
                $find_ref_id = true;
                foreach ($this->all_ref_ids as $ref_id) {
                    if ($this->isRefIdAccessible($a_user_id, $ref_id, $a_permission)) {
                        $this->ref_id = $ref_id;
                        break;
                    }
                }
            }

            // check if initially given ref_id is accessible for current recipient
            if ($this->getRefId() &&
                !$find_ref_id &&
                !$this->isRefIdAccessible($a_user_id, $this->getRefId(), $a_permission)) {
                return false;
            }

            $goto = $this->createPermanentLink(array(), (string) $a_goto_additional);
            if ($goto) {
                $this->appendBody($this->getLanguageText($this->goto_caption) . ": " .
                    $goto);
                $this->appendBody("\n\n");
            }

            if ($find_ref_id) {
                $this->ref_id = 0;
            }
        }

        if ($this->reason) {
            $this->appendBody($this->getLanguageText($this->reason));
            $this->appendBody("\n\n");
        }

        $this->appendBody(ilMail::_getAutoGeneratedMessageString($this->language));

        // signature will append new lines
        $this->body = trim($this->body);

        if (!$a_append_signature_direct) {
            $this->getMail()->appendInstallationSignature(true);
        } else {
            $this->appendBody(ilMail::_getInstallationSignature());
        }

        return true;
    }

    /**
     * Send notification to single recipient
     */
    protected function composeAndSendMail(
        int $a_user_id,
        ?string $a_goto_additional = null,
        string $a_permission = "read"
    ): bool {
        if ($this->compose($a_user_id, $a_goto_additional, $a_permission)) {
            $this->sendMail(array($a_user_id), is_numeric($a_user_id));
            return true;
        }
        return false;
    }

    /**
     * Compose notification to single recipient
     */
    public function composeAndGetMessage(
        int $a_user_id,
        ?string $a_goto_additional = null,
        string $a_permission = "read",
        bool $a_append_signature_direct = false
    ): string {
        if ($this->compose($a_user_id, $a_goto_additional, $a_permission, $a_append_signature_direct)) {
            return $this->body;
        }
        return "";
    }
}
