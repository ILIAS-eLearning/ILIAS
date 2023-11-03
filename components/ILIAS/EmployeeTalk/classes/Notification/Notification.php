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

declare(strict_types=1);

namespace ILIAS\EmployeeTalk\Notification;

class Notification
{
    protected \ilObjUser $to;
    protected \ilObjUser $cc;
    protected int $talk_ref_id;
    protected string $talk_name;
    protected string $talk_description;
    protected string $talk_location;
    protected string $subject_key;
    protected string $message_key;

    /**
     * @var string[] $dates
     */
    protected array $dates;
    protected bool $add_goto;
    protected string $attachment;

    public function __construct(
        \ilObjUser $to,
        \ilObjUser $cc,
        int $talk_ref_id,
        string $talk_name,
        string $talk_description,
        string $talk_location,
        string $subject_key,
        string $message_key,
        string $attachment,
        bool $add_goto,
        string ...$dates,
    ) {
        $this->to = $to;
        $this->cc = $cc;
        $this->talk_ref_id = $talk_ref_id;
        $this->talk_name = $talk_name;
        $this->talk_description = $talk_description;
        $this->talk_location = $talk_location;
        $this->subject_key = $subject_key;
        $this->message_key = $message_key;
        $this->dates = $dates;
        $this->add_goto = $add_goto;
        $this->attachment = $attachment;
    }

    public function send(): void
    {
        $notif = new \ilSystemNotification();
        $notif->setLangModules(['etal', 'orgu']);
        $attachment = new \ilFileDataMail(ANONYMOUS_USER_ID);

        $subject = sprintf(
            $notif->getUserLanguage($this->to->getId())->txt($this->subject_key),
            $this->talk_name
        );

        $notif->setRefId($this->talk_ref_id);
        $notif->setObjId(0);
        $notif->setIntroductionLangId($this->message_key);

        $notif->addAdditionalInfo(
            'obj_etal',
            $this->talk_name
        );

        if ($this->talk_description) {
            $notif->addAdditionalInfo(
                'description',
                $this->talk_description
            );
        }

        if ($this->talk_location) {
            $notif->addAdditionalInfo(
                'location',
                $this->talk_location
            );
        }

        $notif->addAdditionalInfo(
            'superior',
            $this->cc->getFullname()
        );

        $notif->addAdditionalInfo(
            'notification_talks_date_list_header',
            "- " . implode("\r\n- ", $this->dates),
            true
        );

        if ($this->add_goto) {
            $notif->addAdditionalInfo(
                'url',
                $this->getTalkGoto()
            );
        }

        $attachment_name = 'appointments.ics';
        $attachment->storeAsAttachment(
            $attachment_name,
            $this->attachment
        );

        $mail = new \ilMail(ANONYMOUS_USER_ID);
        $mail->enqueue(
            $this->to->getLogin(),
            $this->cc->getLogin(),
            '',
            $subject,
            $notif->composeAndGetMessage($this->to->getId(), null, '', true),
            [$attachment_name]
        );

        $attachment->unlinkFile($attachment_name);
    }

    protected function getTalkGoto(): string
    {
        return ILIAS_HTTP_PATH . '/goto.php?target=' . \ilObjEmployeeTalk::TYPE . '_' .
            $this->talk_ref_id . '&client_id=' . CLIENT_ID;
    }
}
