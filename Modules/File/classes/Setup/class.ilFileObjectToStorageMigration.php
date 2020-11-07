<?php

use ILIAS\Setup;
use ILIAS\Setup\Environment;

class ilFileObjectToStorageMigration implements Setup\Migration
{
    /**
     * @inheritDoc
     */
    public function getKey() : string
    {
        return 'fileobject_to_storage';
    }

    /**
     * @inheritDoc
     */
    public function getLabel() : string
    {
        return "Migration of File-Objects to Storage service";
    }

    /**
     * @inheritDoc
     */
    public function getDefaultAmountOfStepsPerRun() : int
    {
        return 1000;
    }

    /**
     * @inheritDoc
     */
    public function getPreconditions(Environment $environment) : array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function step(Environment $environment) : void
    {
        usleep(12000);
    }

    /**
     * @inheritDoc
     */
    public function getRemainingAmountOfSteps() : int
    {
        return 86574854;
    }

    /**
     * @inheritDoc
     */
    public function prepare(Environment $environment) : void
    {
        // TODO: Implement prepare() method.
    }

}
