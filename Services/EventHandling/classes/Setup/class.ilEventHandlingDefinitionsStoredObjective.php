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

use ILIAS\Setup;
use ILIAS\DI;

class ilEventHandlingDefinitionsStoredObjective implements Setup\Objective
{
    /**
     * @var	bool
     */
    protected $populate_before;

    public function __construct(bool $populate_before = true)
    {
        $this->populate_before = $populate_before;
    }

    /**
     * @inheritDoc
     */
    public function getHash(): string
    {
        return hash("sha256", self::class);
    }

    /**
     * @inheritDoc
     */
    public function getLabel(): string
    {
        return "Events are initialized.";
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
        return [
            new \ilDatabaseUpdatedObjective(),
            new \ilSettingsFactoryExistsObjective(),
            new \ilComponentDefinitionsStoredObjective()
        ];
    }

    /**
     * @inheritDoc
     */
    public function achieve(Setup\Environment $environment): Setup\Environment
    {
        $reader = new \ilComponentDefinitionReader(
            new \ilEventHandlingDefinitionProcessor(),
        );
        $reader->purge();
        $reader->readComponentDefinitions();

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
