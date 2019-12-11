<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

use PHPMailer\PHPMailer\PHPMailer;

/**
 * Class ilMailMimeTransportBase
 */
abstract class ilMailMimeTransportBase implements \ilMailMimeTransport
{
    /** @var PHPMailer */
    protected $mailer;

    /** @var \ilSetting $settings */
    protected $settings;

    /** @var ilAppEventHandler */
    private $eventHandler;

    /**
     * ilMailMimeTransportBase constructor.
     * @param \ilSetting $settings
     * @param ilAppEventHandler $eventHandler
     */
    public function __construct(\ilSetting $settings, \ilAppEventHandler $eventHandler)
    {
        $this->settings = $settings;
        $this->eventHandler = $eventHandler;

        $mail = new PHPMailer();
        $this->setMailer($mail);
    }

    /**
     * @return PHPMailer
     */
    protected function getMailer()
    {
        return $this->mailer;
    }

    /**
     * @param PHPMailer $mailer
     */
    protected function setMailer($mailer)
    {
        $this->mailer = $mailer;
    }

    protected function resetMailer()
    {
        $this->getMailer()->clearAllRecipients();
        $this->getMailer()->clearAttachments();
        $this->getMailer()->clearReplyTos();
    }

    /**
     *
     */
    protected function onBeforeSend()
    {
    }

    /**
     * @inheritdoc
     */
    final public function send(\ilMimeMail $mail) : bool
    {
        $this->resetMailer();

        $this->getMailer()->XMailer = ' ';

        foreach ($mail->getTo() as $recipients) {
            $recipient_pieces = array_filter(array_map('trim', explode(',', $recipients)));
            foreach ($recipient_pieces as $recipient) {
                $this->getMailer()->AddAddress($recipient, '');
            }
        }

        foreach ($mail->getCc() as $carbon_copies) {
            $cc_pieces = array_filter(array_map('trim', explode(',', $carbon_copies)));
            foreach ($cc_pieces as $carbon_copy) {
                $this->getMailer()->AddCC($carbon_copy, '');
            }
        }

        foreach ($mail->getBcc() as $blind_carbon_copies) {
            $bcc_pieces = array_filter(array_map('trim', explode(',', $blind_carbon_copies)));
            foreach ($bcc_pieces as $blind_carbon_copy) {
                $this->getMailer()->AddBCC($blind_carbon_copy, '');
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
            $this->getMailer()->AddAttachment($attachment['path'], $attachment['name']);
        }

        foreach ($mail->getImages() as $image) {
            $this->getMailer()->AddEmbeddedImage($image['path'], $image['cid'], $image['name']);
        }

        if ($mail->getFinalBodyAlt()) {
            $this->getMailer()->IsHTML(true);
            $this->getMailer()->AltBody = $mail->getFinalBodyAlt();
            $this->getMailer()->Body = $mail->getFinalBody();
        } else {
            $this->getMailer()->IsHTML(false);
            $this->getMailer()->AltBody = '';
            $this->getMailer()->Body = $mail->getFinalBody();
        }

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

        $this->getMailer()->CharSet = 'utf-8';

        $this->mailer->SMTPDebug = 4;
        $this->mailer->Debugoutput = function ($message, $level) {
            ilLoggerFactory::getLogger('mail')->debug($message);
        };

        $this->onBeforeSend();
        $result = $this->getMailer()->Send();
        if ($result) {
            ilLoggerFactory::getLogger('mail')->info(sprintf(
                'Successfully delegated external mail delivery'
            ));
        } else {
            ilLoggerFactory::getLogger('mail')->warning(sprintf(
                'Could not deliver external email: %s',
                $this->getMailer()->ErrorInfo
            ));
        }

        $this->eventHandler->raise('Services/Mail', 'externalEmailDelegated', [
            'mail' => $mail,
            'result' => (bool) $result
        ]);

        return (bool) $result;
    }
}
