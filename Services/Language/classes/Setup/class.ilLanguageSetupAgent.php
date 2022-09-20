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
use ILIAS\Refinery;
use ILIAS\UI;

class ilLanguageSetupAgent implements Setup\Agent
{
    use Setup\Agent\HasNoNamedObjective;

    protected Refinery\Factory $refinery;
    protected \ilSetupLanguage $il_setup_language;

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
    public function hasConfig(): bool
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function getArrayToConfigTransformation(): Refinery\Transformation
    {
        throw new LogicException(self::class . " has no Config.");
    }

    /**
     * @inheritdoc
     */
    public function getInstallObjective(Setup\Config $config = null): Setup\Objective
    {
        return new Setup\ObjectiveCollection(
            "Complete objectives from Services/Language",
            false,
            new ilLanguagesInstalledAndUpdatedObjective($this->il_setup_language),
            new ilDefaultLanguageSetObjective()
        );
    }

    /**
     * @inheritdoc
     */
    public function getUpdateObjective(Setup\Config $config = null): Setup\Objective
    {
        return new Setup\ObjectiveCollection(
            "Complete objectives from Services/Language",
            false,
            new ilLanguagesInstalledAndUpdatedObjective($this->il_setup_language),
        );
    }

    /**
     * @inheritdoc
     */
    public function getBuildArtifactObjective(): Setup\Objective
    {
        return new Setup\Objective\NullObjective();
    }

    /**
     * @inheritdoc
     */
    public function getStatusObjective(Setup\Metrics\Storage $storage): Setup\Objective
    {
        return new ilLanguageMetricsCollectedObjective($storage, $this->il_setup_language);
    }

    /**
     * @inheritDoc
     */
    public function getMigrations(): array
    {
        return [];
    }
}
