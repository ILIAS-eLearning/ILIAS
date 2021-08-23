<?php

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\Setup\Artifact\BuildArtifactObjective;
use ILIAS\Setup;

/**
 * ilCtrlStructureArtifactObjective
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
class ilCtrlStructureArtifactObjective extends BuildArtifactObjective
{
    /**
     * @var string absolute path to the php artifact file.
     */
    public const ARTIFACT_PATH = "Services/UICore/artifacts/ctrl_structure.php";

    /**
     * @inheritDoc
     */
    public function getArtifactPath() : string
    {
        return self::ARTIFACT_PATH;
    }

    /**
     * @inheritDoc
     */
    public function build() : Setup\Artifact
    {
        $reader = new ilCtrlStructureReader2();

        return new Setup\Artifact\ArrayArtifact($reader->readStructureOnly());
    }
}
