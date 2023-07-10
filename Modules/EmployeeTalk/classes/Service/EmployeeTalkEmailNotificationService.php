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

namespace ILIAS\EmployeeTalk\Service;

final class EmployeeTalkEmailNotificationService
{
    private EmployeeTalkEmailNotification $message;
    private string $subject;
    private \ilObjUser $to;
    private \ilObjUser $cc;
    private VCalender $calendar;

    public function __construct(
        EmployeeTalkEmailNotification $message,
        string $subject,
        \ilObjUser $to,
        \ilObjUser $cc,
        VCalender $calendar
    ) {
        $this->message = $message;
        $this->subject = $subject;
        $this->to = $to;
        $this->cc = $cc;
        $this->calendar = $calendar;
    }

    /**
     * Send the notification
     *
     * @return bool
     */
    public function send(): bool
    {
        $notif = new \ilSystemNotification();
        $notif->setLangModules(['etal', 'orgu']);
        $attachment = new \ilFileDataMail(ANONYMOUS_USER_ID);

        $subject = sprintf(
            $notif->getUserLanguage($this->to->getId())->txt($this->message->getSubjectLangKey()),
            $this->subject
        );

        $notif->setRefId($this->message->getTalkRefId());
        $notif->setObjId(0);
        $notif->setIntroductionLangId($this->message->getMessageLangKey());

        $notif->addAdditionalInfo(
            'obj_etal',
            $this->message->getTalkName()
        );

        if ($this->message->getTalkDescription()) {
            $notif->addAdditionalInfo(
                'description',
                $this->message->getTalkDescription()
            );
        }

        if ($this->message->getTalkLocation()) {
            $notif->addAdditionalInfo(
                'location',
                $this->message->getTalkLocation()
            );
        }

        $notif->addAdditionalInfo(
            'superior',
            $this->message->getNameOfSuperior()
        );

        $notif->addAdditionalInfo(
            'notification_talks_date_list_header',
            "- " . implode("\r\n- ", $this->message->getDates()),
            true
        );

        if ($this->message->getAddGoto()) {
            $notif->addAdditionalInfo(
                'url',
                $this->getTalkGoto()
            );
        }

        $attachment_name = 'appointments.ics';
        $attachment->storeAsAttachment(
            $attachment_name,
            $this->calendar->render()
        );

        $mail = new \ilMail(ANONYMOUS_USER_ID);
        $mail->enqueue(
            \ilObjUser::_lookupLogin($this->to->getId()),
            \ilObjUser::_lookupLogin($this->cc->getId()),
            "",
            $subject,
            $notif->composeAndGetMessage($this->to->getId(), null, '', true),
            [$attachment_name]
        );

        $attachment->unlinkFile('appointments.ics');

        return true;
    }



    private function getIcalEvent(string $mime_boundary): string
    {
        $message = "--$mime_boundary\r\n";
        $message .= 'Content-Type: text/calendar;name="appointment.ics";method=' . $this->calendar->getMethod() . "\r\n";
        $message .= "Content-Disposition: attachment;filename=\"appointment.ics\"\r\n";
        $message .= "Content-Transfer-Encoding: UTF8\r\n\r\n";
        $message .= $this->calendar->render() . "\r\n";
        return $message;
    }

    private function getTalkGoto(): string
    {
        return ILIAS_HTTP_PATH . '/goto.php?target=' . \ilObjEmployeeTalk::TYPE . '_' .
            $this->message->getTalkRefId() . '&client_id=' . CLIENT_ID;
    }
}
