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

use ILIAS\Setup;

/**
 * There seems to already exist an ILIAS installation, an interaction with it
 * should be confirmed.
 */
class ilOverwritesExistingInstallationConfirmed extends ilSetupObjective
{
    /**
     * @inheritdoc
     */
    public function getHash(): string
    {
        return hash(
            "sha256",
            get_class($this)
        );
    }

    /**
     * @inheritdoc
     */
    public function getLabel(): string
    {
        return "Confirm that an existing installation should be overwritten.";
    }

    /**
     * @inheritdoc
     */
    public function isNotable(): bool
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function getPreconditions(Setup\Environment $environment): array
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function achieve(Setup\Environment $environment): Setup\Environment
    {
        if (!$this->iniExists() && !$this->clientIniExists()) {
            return $environment;
        }

        $admin_interaction = $environment->getResource(Setup\Environment::RESOURCE_ADMIN_INTERACTION);

        $message =
            "An installation already seems to exist in this location. Using this command\n" .
            "might change your installation in unexpected ways. Also, the command might not\n" .
            "work as expected. Are you sure that you want to proceed anyway?";

        if (!$admin_interaction->confirmOrDeny($message)) {
            throw new Setup\NoConfirmationException($message);
        }

        return $environment;
    }

    /**
     * @inheritDoc
     */
    public function isApplicable(Setup\Environment $environment): bool
    {
        return $this->iniExists() || $this->clientIniExists();
    }

    public function iniExists(): bool
    {
        return file_exists(dirname(__DIR__, 2) . "/ilias.ini.php");
    }

    public function clientIniExists(): bool
    {
        return file_exists($this->getClientDir() . "/client.ini.php");
    }

    protected function getClientDir(): string
    {
        return dirname(__DIR__, 2) . "/data/" . ((string) $this->config->getClientId());
    }
}
