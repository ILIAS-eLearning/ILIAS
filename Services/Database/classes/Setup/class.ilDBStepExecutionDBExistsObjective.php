<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use ILIAS\Setup;
use ILIAS\DI;

class ilDBStepExecutionDBExistsObjective implements Setup\Objective
{
    public function getHash() : string
    {
        return hash("sha256", self::class);
    }

    public function getLabel() : string
    {
        return "The execution log for database update steps exists.";
    }

    public function isNotable() : bool
    {
        return true;
    }

    public function getPreconditions(Setup\Environment $environment) : array
    {
        return [
            new \ilDatabaseUpdatedObjective()
        ];
    }

    public function achieve(Setup\Environment $environment) : Setup\Environment
    {
        $db = $environment->getResource(Setup\Environment::RESOURCE_DATABASE);
        return $environment
            ->withResource(
                \ilDatabaseUpdateStepExecutionLog::class,
                new \ilDBStepExecutionDB(
                    $db,
                    fn () => new \DateTime()
                )
            );
    }

    /**
     * @inheritDoc
     */
    public function isApplicable(Setup\Environment $environment) : bool
    {
        $execution_db = $environment->getResource(\ilDBStepExecutionDB::class);
        return is_null($execution_db);
    }
}
