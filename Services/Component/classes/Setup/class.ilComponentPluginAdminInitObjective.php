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
 ********************************************************************
 */

declare(strict_types=1);

use ILIAS\Setup;
use ILIAS\DI;

class ilComponentPluginAdminInitObjective implements Setup\Objective
{
    /**
     * @inheritdoc
     */
    public function getHash(): string
    {
        return hash("sha256", self::class);
    }

    /**
     * @inheritdoc
     */
    public function getLabel(): string
    {
        return "ilPluginAdmin is initialized and stored into the environment.";
    }

    /**
     * @inheritdoc
     */
    public function isNotable(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function getPreconditions(Setup\Environment $environment): array
    {
        return [
            new \ilLanguagesInstalledAndUpdatedObjective(new ilSetupLanguage('en')),
            new ilComponentRepositoryExistsObjective()
        ];
    }

    /**
     * @inheritdoc
     */
    public function achieve(Setup\Environment $environment): Setup\Environment
    {
        // ATTENTION: This is a total abomination. It only exists to allow various
        // sub components of the various readers to run. This is a memento to the
        // fact, that dependency injection is something we want. Currently, every
        // component could just service locate the whole world via the global $DIC.
        $DIC = $GLOBALS["DIC"];
        $GLOBALS["DIC"] = new DI\Container();
        $GLOBALS["DIC"]["lng"] = new class () {
            public function loadLanguageModule(): void
            {
            }
        };

        $environment = $environment->withResource(
            Setup\Environment::RESOURCE_PLUGIN_ADMIN,
            new ilPluginAdmin($environment->getResource(Setup\Environment::RESOURCE_COMPONENT_REPOSITORY))
        );

        $GLOBALS["DIC"] = $DIC;

        return $environment;
    }

    /**
     * @inheritDoc
     */
    public function isApplicable(Setup\Environment $environment): bool
    {
        return is_null($environment->getResource(Setup\Environment::RESOURCE_PLUGIN_ADMIN));
    }
}
