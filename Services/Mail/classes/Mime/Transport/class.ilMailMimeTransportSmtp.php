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

        $this->mailer->Host = $this->settings->get('mail_smtp_host', '');
        $this->mailer->Port = (int) $this->settings->get('mail_smtp_port', '25');
        if ($this->settings->get('mail_smtp_user', '') !== '') {
            $this->mailer->SMTPAuth = true;
            $this->mailer->Username = $this->settings->get('mail_smtp_user', '');
            $this->mailer->Password = $this->settings->get('mail_smtp_password', '');
        }
        $this->mailer->SMTPSecure = $this->settings->get('mail_smtp_encryption', '');
        $this->mailer->SMTPDebug = 4;
    }
}
