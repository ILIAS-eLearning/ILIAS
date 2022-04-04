<?php

/* Copyright (c) 2021 - Daniel Weise <daniel.weise@concepts-and-training.de> - Extended GPL, see LICENSE */

use ILIAS\Setup;

class ilDBStepReaderExistsObjective implements Setup\Objective
{
    public function getHash() : string
    {
        return hash("sha256", self::class);
    }

    public function getLabel() : string
    {
        return "The step reader for database update steps exists.";
    }

    public function isNotable() : bool
    {
        return true;
    }

    public function getPreconditions(Setup\Environment $environment) : array
    {
        return [
            new ilDatabaseUpdatedObjective()
        ];
    }

    public function achieve(Setup\Environment $environment) : Setup\Environment
    {
        return $environment
            ->withResource(
                ilDBStepReader::class,
                new ilDBStepReader()
            );
    }

    /**
     * @inheritDoc
     */
    public function isApplicable(Setup\Environment $environment) : bool
    {
        $execution_db = $environment->getResource(ilDBStepReader::class);
        return is_null($execution_db);
    }
}
