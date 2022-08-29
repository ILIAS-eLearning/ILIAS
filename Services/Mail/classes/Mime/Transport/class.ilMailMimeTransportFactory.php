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

class ilMailMimeTransportFactory
{
    public function __construct(protected ilSetting $settings, private ilAppEventHandler $eventHandler)
    {
    }

    public function getTransport(): ilMailMimeTransport
    {
        if (!$this->settings->get('mail_allow_external', '0')) {
            return new ilMailMimeTransportNull();
        }

        if ($this->settings->get('mail_smtp_status', '0')) {
            return new ilMailMimeTransportSmtp($this->settings, $this->eventHandler);
        }

        return new ilMailMimeTransportSendmail($this->settings, $this->eventHandler);
    }
}
