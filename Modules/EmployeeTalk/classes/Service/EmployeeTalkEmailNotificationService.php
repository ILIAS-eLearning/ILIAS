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

use ilSetting;
use ilTemplate;

final class EmployeeTalkEmailNotificationService
{
    private EmployeeTalkEmailNotification $message;
    private string $subject;
    private string $to;
    private string $cc;
    private VCalender $calendar;
    private ilSetting $settings;

    /**
     * EmployeeTalkEmailNotificationService constructor.
     * @param EmployeeTalkEmailNotification    $message
     * @param string    $subject
     * @param string    $to
     * @param string    $cc
     * @param VCalender $calendar
     */
    public function __construct(EmployeeTalkEmailNotification $message, string $subject, \ilObjUser $to, \ilObjUser $cc, VCalender $calendar)
    {
        global $DIC;

        $this->message = $message;
        $this->subject = $subject;
        $this->to = $to;
        $this->cc = $cc;
        $this->calendar = $calendar;

        $this->settings = $DIC->settings();
    }

    /**
     * Send the notification
     *
     * @return bool
     */
    public function send(): bool
    {
        global $DIC;

        $language = $DIC->language();
        $senderFactory = $DIC->mail()->mime()->senderFactory();
        $sender = $senderFactory->system();

        $mime_boundary = "b1_" . md5(strval(time()));

        $from = $sender->getFromAddress();
        $fromName = strlen($sender->getFromName()) > 0 ? $sender->getFromName() : $from;
        $cc = $this->cc->getEmail();
        $ccName = $this->cc->getFullname();
        $to = $this->to->getEmail();
        $toName = $this->to->getFullname();
        $replayTo = $sender->getReplyToAddress();
        $replayToName = strlen($sender->getReplyToName()) > 0 ? $sender->getReplyToName() : $replayTo;
        //$headers = 'From: ' . $this->encodeWord("$fromName <$from>") . "\n";
        //$headers .= 'Cc: ' . $this->encodeWord("$ccName <$cc>") . "\n";
        //$headers .= 'Reply-To: ' . $this->encodeWord("$replayToName <$replayTo>") . "\n";
        //$headers .= "MIME-Version: 1.0\n";
        //$headers .= "Content-Type: multipart/mixed; boundary=\"$mime_boundary\"\n";
        //$headers .= "Content-class: urn:content-classes:calendarmessage\n";

        $subjectPrefix = strval($this->settings->get('mail_subject_prefix'));
        $subjectDetails = $this->subject;
        $allowExternalMails = boolval(intval($this->settings->get('mail_allow_external')));

        //$mailsent = false;
        $subject = $language->txt('notification_talks_subject');
        //if ($allowExternalMails) {
        //$mailsent = mail($this->encodeWord($toName) . " <$to>", $this->encodeWord("$subjectPrefix $subject: $subjectDetails"), $this->getMessage($mime_boundary), $headers);
        //}

        $mail = new \ilSystemNotification();
        $mail->setSubjectDirect("$subjectPrefix $subject: $subjectDetails");
        $attachment = new \ilFileDataMail(ANONYMOUS_USER_ID);

        $attachmentName = 'appointments.ics';
        $attachment->storeAsAttachment(
            $attachmentName,
            $this->calendar->render()
        );
        $mail->setAttachments([
            $attachment
        ]);
        $mail->sendMail([$this->to]);

        //return $mailsent;
        return true;
    }

    /**
     * Encodes text for email header values to base64, which must be done
     * because some email servers fail to handle non ascii characters in the subject, cc, from and to header fields.
     *
     * @param string $text
     * @return string
     */
    private function encodeWord(string $text): string
    {
        /*
         * The encoding must be either B or Q, these mean base 64 and quoted-printable respectively.
         * See RFC 1342 for more infos.
         *
         * Encoded text should not be longer than 75 chars
         */
        return mb_encode_mimeheader($text, 'utf8', 'B', "\r\n ", 0);
    }

    private function getHtmlMessage(string $mime_boundary): string
    {
        $template = new ilTemplate('tpl.email_appointments.html', true, true, 'Modules/EmployeeTalk');

        $template->setCurrentBlock();
        $template->setVariable('LANGUAGE', 'en');
        $template->setVariable('TITLE', $this->subject);
        $template->setVariable('SALUTATION', $this->message->getSalutation());
        $template->setVariable('TALK_TITLE', $this->message->getTalkTitle());
        $template->setVariable('APPOINTMENT_DETAILS', $this->message->getAppointmentDetails());
        $template->setVariable('DATE_HEADER', $this->message->getDateHeader());
        $template->setVariable('FOOTER', nl2br(\ilMail::_getInstallationSignature()));

        $dates = $this->message->getDates();
        $template->setCurrentBlock('DATE_LIST_ENTRY');
        foreach ($dates as $date) {
            $template->setVariable('DATE', $date);
            $template->parseCurrentBlock();
        }

        $template->parseCurrentBlock();
        $html = $template->get();

        $message = "--$mime_boundary\r\n";
        $message .= "Content-Type: text/html; charset=\"utf-8\"\r\n";
        //$message .= "Content-Transfer-Encoding: QUOTED-PRINTABLE\r\n\r\n";
        $message .= "Content-Transfer-Encoding: UTF8\r\n\r\n";
        $message .= $html;
        $message .= "\r\n";

        return $message;
    }

    private function getTextMessage(string $mime_boundary): string
    {
        $message = "--$mime_boundary\r\n";
        $message .= "Content-Type: text/plain; charset=\"utf-8\"\r\n";
        $message .= "Content-Transfer-Encoding: UTF8\r\n\r\n";
        $message .= $this->message . "\r\n";
        $message .= \ilMail::_getInstallationSignature() . "\r\n";

        return $message;
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
}
