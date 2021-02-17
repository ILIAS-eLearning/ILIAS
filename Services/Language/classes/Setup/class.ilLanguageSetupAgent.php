<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use ILIAS\Setup;
use ILIAS\Refinery;
use ILIAS\UI;

class ilLanguageSetupAgent implements Setup\Agent
{
    /**
     * @var Refinery\Factory
     */
    protected $refinery;

    /**
     * @var \ilSetupLanguage
     */
    protected $il_setup_language;

    public function __construct(
        Refinery\Factory $refinery,
        $_, // this is Data\Factory, but we do not need it...
        \ilSetupLanguage $il_setup_language
    ) {
        $this->refinery = $refinery;
        $this->il_setup_language = $il_setup_language;
    }

    /**
     * @inheritdoc
     */
    public function hasConfig() : bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function getArrayToConfigTransformation() : Refinery\Transformation
    {
        return $this->refinery->custom()->transformation(function ($data) {
            if (!isset($data["default_language"])) {
                $data["default_language"] = "en";
            }
            return new \ilLanguageSetupConfig(
                $data["default_language"],
                $data["install_languages"] ?? [$data["default_language"]],
                $data["install_local_languages"] ?? []
            );
        });
    }

    /**
     * @inheritdoc
     */
    public function getInstallObjective(Setup\Config $config = null) : Setup\Objective
    {
        return new Setup\ObjectiveCollection(
            "Complete objectives from Services/Language",
            false,
            new ilLanguageConfigStoredObjective($config),
            new ilLanguagesInstalledAndUpdatedObjective($config, $this->il_setup_language),
            new ilDefaultLanguageSetObjective($config)
        );
    }

    /**
     * @inheritdoc
     */
    public function getUpdateObjective(Setup\Config $config = null) : Setup\Objective
    {
        if ($config !== null) {
            return new Setup\ObjectiveCollection(
                "Complete objectives from Services/Language",
                false,
                new ilLanguageConfigStoredObjective($config),
                new ilLanguagesInstalledAndUpdatedObjective($config, $this->il_setup_language),
                new ilDefaultLanguageSetObjective($config)
            );
        }

        return new Setup\ObjectiveCollection(
            "Complete objectives from Services/Language",
            false,
            new ilLanguagesInstalledAndUpdatedObjective(null, $this->il_setup_language),
        );
    }

    /**
     * @inheritdoc
     */
    public function getBuildArtifactObjective() : Setup\Objective
    {
        return new Setup\Objective\NullObjective();
    }

    /**
     * @inheritdoc
     */
    public function getStatusObjective(Setup\Metrics\Storage $storage) : Setup\Objective
    {
        return new ilLanguageMetricsCollectedObjective($storage, $this->il_setup_language);
    }

    /**
     * @inheritDoc
     */
    public function getMigrations() : array
    {
        return [];
    }
}
