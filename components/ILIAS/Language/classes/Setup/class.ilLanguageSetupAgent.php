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

use ILIAS\Setup;
use ILIAS\Refinery;
use ILIAS\UI;
use ILIAS\Language\Activities\InstallLanguage;
use ILIAS\Language\Activities\UpdateLanguage;
use ILIAS\Language\Setup\InstalledLanguageRepository;

class ilLanguageSetupAgent implements Setup\Agent
{
    use Setup\Agent\HasNoNamedObjective;

    protected Refinery\Factory $refinery;
    protected \ilSetupLanguage $il_setup_language;
    protected InstallLanguage $install_language;
    protected UpdateLanguage $update_language;
    protected InstalledLanguageRepository $repository;

    public function __construct(
        Refinery\Factory $refinery,
        \ilSetupLanguage $il_setup_language,
        InstallLanguage $install_language,
        UpdateLanguage $update_language,
        InstalledLanguageRepository $repository
    ) {
        $this->refinery = $refinery;
        $this->il_setup_language = $il_setup_language;
        $this->install_language = $install_language;
        $this->update_language = $update_language;
        $this->repository = $repository;
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
    public function getInstallObjective(?Setup\Config $config = null): Setup\Objective
    {
        return new Setup\ObjectiveCollection(
            "Complete objectives from components/ILIAS/Language",
            false,
            new ilLanguagesInstalledAndUpdatedObjective(
                $this->il_setup_language,
                $this->install_language,
                $this->update_language
            ),
            new ilDefaultLanguageSetObjective()
        );
    }

    /**
     * @inheritdoc
     */
    public function getUpdateObjective(?Setup\Config $config = null): Setup\Objective
    {
        return new Setup\ObjectiveCollection(
            "Complete objectives from components/ILIAS/Language",
            false,
            new ilLanguagesInstalledAndUpdatedObjective(
                $this->il_setup_language,
                $this->install_language,
                $this->update_language
            ),
        );
    }

    /**
     * @inheritdoc
     */
    public function getBuildObjective(): Setup\Objective
    {
        return new Setup\Objective\NullObjective();
    }

    /**
     * @inheritdoc
     */
    public function getStatusObjective(Setup\Metrics\Storage $storage): Setup\Objective
    {
        return new ilLanguageMetricsCollectedObjective($storage, $this->repository);
    }

    /**
     * @inheritDoc
     */
    public function getMigrations(): array
    {
        return [];
    }
}
