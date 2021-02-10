<?php declare(strict_types=1);

use ILIAS\Setup;
use ILIAS\DI;

class ilComponentPluginAdminInitObjective implements Setup\Objective
{
    /**
     * @inheritdoc
     */
    public function getHash() : string
    {
        return hash("sha256", self::class);
    }

    /**
     * @inheritdoc
     */
    public function getLabel() : string
    {
        return "ilPluginAdmin is initialized and stored into the environment.";
    }

    /**
     * @inheritdoc
     */
    public function isNotable() : bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function getPreconditions(Setup\Environment $environment) : array
    {
        if (!$environment->hasConfigFor('language')) {
            return [];
        }

        $config = $environment->getConfigFor('language');
        return [
            new \ilLanguagesInstalledAndUpdatedObjective($config, new ilSetupLanguage('en'))
        ];
    }

    /**
     * @inheritdoc
     */
    public function achieve(Setup\Environment $environment) : Setup\Environment
    {
        // ATTENTION: This is a total abomination. It only exists to allow various
        // sub components of the various readers to run. This is a memento to the
        // fact, that dependency injection is something we want. Currently, every
        // component could just service locate the whole world via the global $DIC.
        $DIC = $GLOBALS["DIC"];
        $GLOBALS["DIC"] = new DI\Container();
        $GLOBALS["DIC"]["lng"] = new class() {
            public function loadLanguageModule()
            {
            }
        };

        $environment = $environment->withResource(
            Setup\Environment::RESOURCE_PLUGIN_ADMIN,
            new ilPluginAdmin()
        );

        $GLOBALS["DIC"] = $DIC;

        return $environment;
    }

    /**
     * @inheritDoc
     */
    public function isApplicable(Setup\Environment $environment) : bool
    {
        return is_null($environment->getResource(Setup\Environment::RESOURCE_PLUGIN_ADMIN));
    }
}
