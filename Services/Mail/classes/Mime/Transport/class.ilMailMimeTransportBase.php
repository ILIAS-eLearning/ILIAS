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

use PHPMailer\PHPMailer\PHPMailer;

/**
 * Class ilMailMimeTransportBase
 */
abstract class ilMailMimeTransportBase implements ilMailMimeTransport
{
    protected PHPMailer $mailer;
    protected ilSetting $settings;
    private ilAppEventHandler $eventHandler;

    public function __construct(ilSetting $settings, ilAppEventHandler $eventHandler)
    {
        $this->settings = $settings;
        $this->eventHandler = $eventHandler;

        $mail = new PHPMailer();
        $this->setMailer($mail);
    }

    protected function getMailer() : PHPMailer
    {
        return $this->mailer;
    }

    protected function setMailer(PHPMailer $mailer) : void
    {
        $this->mailer = $mailer;
    }

    protected function resetMailer() : void
    {
        $this->getMailer()->clearAllRecipients();
        $this->getMailer()->clearAttachments();
        $this->getMailer()->clearReplyTos();
        $this->getMailer()->ErrorInfo = '';
    }

    protected function onBeforeSend() : void
    {
    }

    final public function send(ilMimeMail $mail) : bool
    {
        $this->resetMailer();

        $this->getMailer()->XMailer = ' ';

        foreach ($mail->getTo() as $recipients) {
            $recipient_pieces = array_filter(array_map('trim', explode(',', $recipients)));
            foreach ($recipient_pieces as $recipient) {
                $this->getMailer()->addAddress($recipient);
            }
        }

        foreach ($mail->getCc() as $carbon_copies) {
            $cc_pieces = array_filter(array_map('trim', explode(',', $carbon_copies)));
            foreach ($cc_pieces as $carbon_copy) {
                $this->getMailer()->addCC($carbon_copy);
            }
        }

        foreach ($mail->getBcc() as $blind_carbon_copies) {
            $bcc_pieces = array_filter(array_map('trim', explode(',', $blind_carbon_copies)));
            foreach ($bcc_pieces as $blind_carbon_copy) {
                $this->getMailer()->addBCC($blind_carbon_copy);
            }
        }

        $this->getMailer()->Subject = $mail->getSubject();

        if ($mail->getFrom()->hasReplyToAddress()) {
            $this->getMailer()->addReplyTo($mail->getFrom()->getReplyToAddress(), $mail->getFrom()->getReplyToName());
        }
        if ($mail->getFrom()->hasEnvelopFromAddress()) {
            $this->getMailer()->Sender = $mail->getFrom()->getEnvelopFromAddress();
        }
        $this->getMailer()->setFrom($mail->getFrom()->getFromAddress(), $mail->getFrom()->getFromName(), false);

        foreach ($mail->getAttachments() as $attachment) {
            $this->getMailer()->addAttachment($attachment['path'], $attachment['name']);
        }

        foreach ($mail->getImages() as $image) {
            $this->getMailer()->addEmbeddedImage($image['path'], $image['cid'], $image['name']);
        }

        if ($mail->getFinalBodyAlt()) {
            $this->getMailer()->isHTML(true);
            $this->getMailer()->AltBody = $mail->getFinalBodyAlt();
        } else {
            $this->getMailer()->isHTML(false);
            $this->getMailer()->AltBody = '';
        }
        $this->getMailer()->Body = $mail->getFinalBody();

        ilLoggerFactory::getLogger('mail')->info(sprintf(
            "Trying to delegate external email delivery:" .
            " Initiated by: %s (%s) " .
            "| To: %s | CC: %s | BCC: %s | Subject: %s " .
            "| From: %s / %s " .
            "| ReplyTo: %s / %s " .
            "| EnvelopeFrom: %s",
            $GLOBALS['DIC']->user()->getLogin(),
            $GLOBALS['DIC']->user()->getId(),
            implode(', ', $mail->getTo()),
            implode(', ', $mail->getCc()),
            implode(', ', $mail->getBcc()),
            $mail->getSubject(),
            $mail->getFrom()->getFromAddress(),
            $mail->getFrom()->getFromName(),
            $mail->getFrom()->getReplyToAddress(),
            $mail->getFrom()->getReplyToName(),
            $mail->getFrom()->getEnvelopFromAddress()
        ));

        ilLoggerFactory::getLogger('mail')
                       ->debug(sprintf("Mail Alternative Body: %s", $this->getMailer()->AltBody));
        ilLoggerFactory::getLogger('mail')
                       ->debug(sprintf("Mail Body: %s", $this->getMailer()->Body));

        $this->getMailer()->CharSet = 'utf-8';

        $this->mailer->Debugoutput = static function (string $message, $level) : void {
            if (
                strpos($message, 'Invalid address') ||
                strpos($message, 'Message body empty')
            ) {
                ilLoggerFactory::getLogger('mail')->warning($message);
            } else {
                ilLoggerFactory::getLogger('mail')->debug($message);
            }
        };

        $this->onBeforeSend();
        $result = $this->getMailer()->send();
        if ($result) {
            ilLoggerFactory::getLogger('mail')
                           ->info('Successfully delegated external mail delivery');

            if ($this->getMailer()->ErrorInfo !== '') {
                ilLoggerFactory::getLogger('mail')->warning(sprintf(
                    '... with most recent errors: %s',
                    $this->getMailer()->ErrorInfo
                ));
            }
        } else {
            ilLoggerFactory::getLogger('mail')->warning(sprintf(
                'Could not deliver external email: %s',
                $this->getMailer()->ErrorInfo
            ));
        }

        $this->eventHandler->raise('Services/Mail', 'externalEmailDelegated', [
            'mail' => $mail,
            'result' => $result,
        ]);

        return $result;
    }
}
