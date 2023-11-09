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

class ilLanguageMetricsCollectedObjective extends Setup\Metrics\CollectedObjective
{
    protected \ilSetupLanguage $il_setup_language;

    public function __construct(
        Setup\Metrics\Storage $storage,
        \ilSetupLanguage $il_setup_language
    ) {
        parent::__construct($storage);
        $this->il_setup_language = $il_setup_language;
    }

    /**
     * @inheritDoc
     */
    protected function getTentativePreconditions(Setup\Environment $environment): array
    {
        return [
            new ilIniFilesLoadedObjective(),
            new ilDatabaseInitializedObjective()
        ];
    }

    /**
     * @inheritDoc
     */
    protected function collectFrom(Setup\Environment $environment, Setup\Metrics\Storage $storage): void
    {
        $client_ini = $environment->getResource(Setup\Environment::RESOURCE_CLIENT_INI);
        if ($client_ini) {
            $storage->storeConfigText(
                "default_language",
                $client_ini->readVariable("language", "default"),
                "The language that is used by default."
            );
        }

        $db = $environment->getResource(Setup\Environment::RESOURCE_DATABASE);
        if (!($db instanceof \ilDBInterface)) {
            return;
        }
        $this->il_setup_language->setDbHandler($db);

        // TODO: Remove this once ilSetupLanguage (or a successor) supports proper
        // DI for all methods.
        $GLOBALS["ilDB"] = $db;

        $installed_languages = [];
        $local_languages = $this->il_setup_language->getLocalLanguages();
        foreach ($this->il_setup_language->getInstalledLanguages() as $lang) {
            $local_file = new Setup\Metrics\Metric(
                Setup\Metrics\Metric::STABILITY_STABLE,
                Setup\Metrics\Metric::TYPE_BOOL,
                in_array($lang, $local_languages, true),
                "Is there a local language file for the language?"
            );
            $local_changes = new Setup\Metrics\Metric(
                Setup\Metrics\Metric::STABILITY_STABLE,
                Setup\Metrics\Metric::TYPE_BOOL,
                count($this->il_setup_language->getLocalChanges($lang)) > 0,
                "Are there local changes for the language?"
            );
            $installed_languages[$lang] = new Setup\Metrics\Metric(
                Setup\Metrics\Metric::STABILITY_STABLE,
                Setup\Metrics\Metric::TYPE_COLLECTION,
                [
                    "local_file" => $local_file,
                    "local_changes" => $local_changes
                ]
            );
        }
        $installed_languages = new Setup\Metrics\Metric(
            Setup\Metrics\Metric::STABILITY_STABLE,
            Setup\Metrics\Metric::TYPE_COLLECTION,
            $installed_languages
        );
        $storage->store(
            "installed_languages",
            $installed_languages
        );
    }
}
