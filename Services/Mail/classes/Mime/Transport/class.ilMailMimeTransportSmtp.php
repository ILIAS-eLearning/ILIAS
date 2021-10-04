<?php declare(strict_types=1);
/* Copyright (c) 1998-2021 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilMailMimeTransportSmtp
 */
class ilMailMimeTransportSmtp extends ilMailMimeTransportBase
{
    protected function onBeforeSend() : void
    {
        $this->mailer->isSMTP();

        $this->mailer->Host = (string) $this->settings->get('mail_smtp_host', '');
        $this->mailer->Port = (int) $this->settings->get('mail_smtp_port');
        if (((string) $this->settings->get('mail_smtp_user', '')) !== '') {
            $this->mailer->SMTPAuth = true;
            $this->mailer->Username = (string) $this->settings->get('mail_smtp_user', '');
            $this->mailer->Password = (string) $this->settings->get('mail_smtp_password', '');
        }
        $this->mailer->SMTPSecure = (string) $this->settings->get('mail_smtp_encryption', '');
        $this->mailer->SMTPDebug = 4;
    }
}
