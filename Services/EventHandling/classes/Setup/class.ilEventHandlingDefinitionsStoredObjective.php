<?php

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
    public function getHash() : string
    {
        return hash("sha256", self::class);
    }

    /**
     * @inheritDoc
     */
    public function getLabel() : string
    {
        return "Events are initialized.";
    }

    /**
     * @inheritDoc
     */
    public function isNotable() : bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function getPreconditions(Setup\Environment $environment) : array
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
    public function achieve(Setup\Environment $environment) : Setup\Environment
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
    public function isApplicable(Setup\Environment $environment) : bool
    {
        return true;
    }
}