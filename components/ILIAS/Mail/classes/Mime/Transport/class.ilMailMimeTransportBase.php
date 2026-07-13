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

use PHPMailer\PHPMailer\PHPMailer;

abstract class ilMailMimeTransportBase implements ilMailMimeTransport
{
    protected PHPMailer $mailer;

    public function __construct(protected ilSetting $settings, private readonly ilAppEventHandler $event_handler)
    {
        $mail = new PHPMailer();
        $this->setMailer($mail);
    }

    protected function getMailer(): PHPMailer
    {
        return $this->mailer;
    }

    protected function setMailer(PHPMailer $mailer): void
    {
        $this->mailer = $mailer;
    }

    protected function resetMailer(): void
    {
        $this->getMailer()->clearAllRecipients();
        $this->getMailer()->clearAttachments();
        $this->getMailer()->clearReplyTos();
        $this->getMailer()->ErrorInfo = '';
    }

    protected function onBeforeSend(): void
    {
    }

    final public function send(ilMimeMail $mail): bool
    {
        $this->resetMailer();

        $this->getMailer()->XMailer = ' ';

        foreach ($mail->getTo() as $recipients) {
            $recipient_pieces = array_filter(array_map('trim', explode(',', $recipients)));
            foreach ($recipient_pieces as $recipient) {
                if (!$this->getMailer()->addAddress($recipient)) {
                    ilLoggerFactory::getLogger('mail')->warning('{error}', ['error' => $this->getMailer()->ErrorInfo]);
                }
            }
        }

        foreach ($mail->getCc() as $carbon_copies) {
            $cc_pieces = array_filter(array_map('trim', explode(',', $carbon_copies)));
            foreach ($cc_pieces as $carbon_copy) {
                if (!$this->getMailer()->addCC($carbon_copy)) {
                    ilLoggerFactory::getLogger('mail')->warning('{error}', ['error' => $this->getMailer()->ErrorInfo]);
                }
            }
        }

        foreach ($mail->getBcc() as $blind_carbon_copies) {
            $bcc_pieces = array_filter(array_map('trim', explode(',', $blind_carbon_copies)));
            foreach ($bcc_pieces as $blind_carbon_copy) {
                if (!$this->getMailer()->addBCC($blind_carbon_copy)) {
                    ilLoggerFactory::getLogger('mail')->warning('{error}', ['error' => $this->getMailer()->ErrorInfo]);
                }
            }
        }

        $this->getMailer()->Subject = $mail->getSubject();

        if ($mail->getFrom()->hasReplyToAddress() && !$this->getMailer()->addReplyTo(
            $mail->getFrom()->getReplyToAddress(),
            $mail->getFrom()->getReplyToName()
        )) {
            ilLoggerFactory::getLogger('mail')->warning('{error}', ['error' => $this->getMailer()->ErrorInfo]);
        }
        if ($mail->getFrom()->hasEnvelopFromAddress()) {
            $this->getMailer()->Sender = $mail->getFrom()->getEnvelopFromAddress();
        }

        if (!$this->getMailer()->setFrom($mail->getFrom()->getFromAddress(), $mail->getFrom()->getFromName(), false)) {
            ilLoggerFactory::getLogger('mail')->warning('{error}', ['error' => $this->getMailer()->ErrorInfo]);
        }

        foreach ($mail->getAttachments() as $attachment) {
            if (!$this->getMailer()->addAttachment($attachment['path'], $attachment['name'])) {
                ilLoggerFactory::getLogger('mail')->warning('{error}', ['error' => $this->getMailer()->ErrorInfo]);
            }
        }

        foreach ($mail->getImages() as $image) {
            if (!$this->getMailer()->addEmbeddedImage($image['path'], $image['cid'], $image['name'])) {
                ilLoggerFactory::getLogger('mail')->warning('{error}', ['error' => $this->getMailer()->ErrorInfo]);
            }
        }

        if ($mail->getFinalBodyalt() !== '') {
            $this->getMailer()->isHTML(true);
            $this->getMailer()->AltBody = $mail->getFinalBodyalt();
        } else {
            $this->getMailer()->isHTML(false);
            $this->getMailer()->AltBody = '';
        }
        $this->getMailer()->Body = $mail->getFinalBody();
        $this->getMailer()->AllowEmpty = true;

        ilLoggerFactory::getLogger('mail')->info(
            'Trying to delegate external email delivery:' .
            ' Initiated by: {login} ({id}) ' .
            '| To: {to} | CC: {cc} | BCC: {bcc} | Subject: {subject} ' .
            '| From: {from_address} / {from_name} ' .
            '| ReplyTo: {reply_to_address} / {reply_to_name} ' .
            '| EnvelopeFrom: {envelope_from}',
            [
                'login' => $GLOBALS['DIC']->user()->getLogin(),
                'id' => $GLOBALS['DIC']->user()->getId(),
                'to' => implode(', ', $mail->getTo()),
                'cc' => implode(', ', $mail->getCc()),
                'bcc' => implode(', ', $mail->getBcc()),
                'subject' => $mail->getSubject(),
                'from_address' => $mail->getFrom()->getFromAddress(),
                'from_name' => $mail->getFrom()->getFromName(),
                'reply_to_address' => $mail->getFrom()->getReplyToAddress(),
                'reply_to_name' => $mail->getFrom()->getReplyToName(),
                'envelope_from' => $mail->getFrom()->getEnvelopFromAddress(),
            ]
        );

        ilLoggerFactory::getLogger('mail')
                       ->debug('Mail Alternative Body: {body}', ['body' => $this->getMailer()->AltBody]);
        ilLoggerFactory::getLogger('mail')
                       ->debug('Mail Body: {body}', ['body' => $this->getMailer()->Body]);

        $this->getMailer()->CharSet = 'utf-8';

        $this->mailer->Debugoutput = static function (string $message, $level): void {
            if (str_contains($message, 'Invalid address') ||
                str_contains($message, 'Message body empty')) {
                ilLoggerFactory::getLogger('mail')->warning('{message}', ['message' => $message]);
            } else {
                ilLoggerFactory::getLogger('mail')->debug('{message}', ['message' => $message]);
            }
        };

        $this->onBeforeSend();
        $result = $this->getMailer()->send();
        if ($result) {
            ilLoggerFactory::getLogger('mail')
                           ->info('Successfully delegated external mail delivery');

            if ($this->getMailer()->ErrorInfo !== '') {
                ilLoggerFactory::getLogger('mail')->warning(
                    '... with most recent errors: {error}',
                    ['error' => $this->getMailer()->ErrorInfo]
                );
            }
        } else {
            ilLoggerFactory::getLogger('mail')->warning(
                'Could not deliver external email: {error}',
                ['error' => $this->getMailer()->ErrorInfo]
            );
        }

        $this->event_handler->raise('components/ILIAS/Mail', 'externalEmailDelegated', [
            'mail' => $mail,
            'result' => $result,
        ]);

        return $result;
    }
}
