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

/**
 * Class ilMailMimeTransportSmtp
 */
class ilMailMimeTransportSmtp extends ilMailMimeTransportBase
{
    protected function onBeforeSend(): void
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
