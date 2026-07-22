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

namespace ILIAS\Init\ErrorHandling\Notification;

use ilIniFile;

class Settings implements SettingsInterface
{
    private string $error_recipient;

    public function __construct(
        private readonly ilIniFile $client_ini
    ) {
    }

    public function errorRecipient(): string
    {
        return $this->error_recipient ??=
            $this->client_ini->readVariable('log', 'error_recipient');
    }

    public function saveErrorRecipient(string $recipient): void
    {
        $this->client_ini->addGroup('log');
        $this->client_ini->setVariable('log', 'error_recipient', trim($recipient));
        $this->client_ini->write();
    }
}
