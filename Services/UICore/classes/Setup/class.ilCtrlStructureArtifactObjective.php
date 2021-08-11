<?php
/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\Setup\Artifact\BuildArtifactObjective;
use ILIAS\Setup;

/**
 *
 */
class ilCtrlStructureArtifactObjective extends BuildArtifactObjective
{
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
