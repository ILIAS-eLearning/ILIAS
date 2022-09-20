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
 ********************************************************************
 */

use ILIAS\Setup;

class ilLanguagesInstalledAndUpdatedObjective extends ilLanguageObjective
{
    protected \ilSetupLanguage $il_setup_language;

    public function __construct(
        \ilSetupLanguage $il_setup_language
    ) {
        parent::__construct();
        $this->il_setup_language = $il_setup_language;
    }

    /**
     * @inheritDoc
     */
    public function getHash(): string
    {
        return hash("sha256", self::class);
    }

    /**
     * Return installed languages
     */
    protected function getInstallLanguages(): array
    {
        return $this->il_setup_language->getInstalledLanguages() ?: ['en'];
    }

    /**
     * Return installed local languages
     */
    protected function getInstallLocalLanguages(): array
    {
        return $this->il_setup_language->getInstalledLocalLanguages();
    }

    /**
     * @inheritDoc
     */
    public function getLabel(): string
    {
        return "Install/Update languages " . implode(", ", $this->getInstallLanguages());
    }

    /**
     * @inheritDoc
     */
    public function isNotable(): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function getPreconditions(Setup\Environment $environment): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function achieve(Setup\Environment $environment): Setup\Environment
    {
        $db = $environment->getResource(Setup\Environment::RESOURCE_DATABASE);

        // TODO: Remove this once ilSetupLanguage (or a successor) supports proper
        // DI for all methods.
        $db_tmp = $GLOBALS["ilDB"];
        $GLOBALS["ilDB"] = $db;

        $this->il_setup_language->setDbHandler($db);
        $this->il_setup_language->installLanguages(
            $this->getInstallLanguages(),
            $this->getInstallLocalLanguages()
        );

        $GLOBALS["ilDB"] = $db_tmp;

        return $environment;
    }

    /**
     * @inheritDoc
     */
    public function isApplicable(Setup\Environment $environment): bool
    {
        return true;
    }
}
