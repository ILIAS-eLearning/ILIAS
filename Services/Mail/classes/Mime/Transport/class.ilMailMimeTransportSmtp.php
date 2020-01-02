<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilMailMimeTransportSmtp
 */
class ilMailMimeTransportSmtp extends \ilMailMimeTransportBase
{
    /**
     *
     */
    protected function onBeforeSend()
    {
        $this->mailer->isSMTP();

        $this->mailer->Host = $this->settings->get('mail_smtp_host');
        $this->mailer->Port = (int) $this->settings->get('mail_smtp_port');
        if (strlen($this->settings->get('mail_smtp_user')) > 0) {
            $this->mailer->SMTPAuth = true;
            $this->mailer->Username = $this->settings->get('mail_smtp_user');
            $this->mailer->Password = $this->settings->get('mail_smtp_password');
        }
        $this->mailer->SMTPSecure = $this->settings->get('mail_smtp_encryption');
    }
}
