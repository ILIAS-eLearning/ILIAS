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

use ILIAS\Logging\Configuration\LoggingConfig;

/**
 * Settings for the error protocol system.
 *
 * @deprecated Read directly from {@see LoggingConfig} (`getErrorDirectory()` /
 * `getErrorRecipient()`) instead.
 */
class ilLoggingErrorSettings
{
    private string $folder;
    private string $mail;

    public function __construct(LoggingConfig $config)
    {
        $this->folder = $config->getErrorDirectory();
        $this->mail = $config->getErrorRecipient();
    }

    public static function getInstance(): self
    {
        global $DIC;
        if ($DIC instanceof \Pimple\Container && isset($DIC['logging.services'])) {
            return new self($DIC['logging.services']->getConfig());
        }
        return new self(new \ILIAS\Logging\Configuration\NullLoggingConfig());
    }

    public function setMail(string $mail): void
    {
        $this->mail = $mail;
    }

    public function folder(): string
    {
        return $this->folder;
    }

    public function mail(): string
    {
        return $this->mail;
    }

    /**
     * Persist the mail recipient back into client.ini.php.
     *
     * Kept for the GUI flow which still calls this; uses the legacy
     * {@see \ilIniFile} bridge in $DIC to write back.
     */
    public function update(): void
    {
        global $DIC;
        if (!isset($DIC['ilClientIniFile']) || !$DIC['ilClientIniFile'] instanceof \ilIniFile) {
            return;
        }

        $client_ini = $DIC['ilClientIniFile'];
        $client_ini->addGroup('log');
        $client_ini->setVariable('log', 'error_recipient', trim($this->mail));
        $client_ini->write();
    }
}
