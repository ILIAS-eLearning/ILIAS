<?php declare(strict_types = 1);

/* Copyright (c) 2021 Thibeau Fuhrer <thf@studer-raimann.ch> Extended GPL, see docs/LICENSE */

use ILIAS\Setup\Artifact\BuildArtifactObjective;
use ILIAS\Setup\Artifact\ArrayArtifact;
use ILIAS\Setup\Artifact;

/**
 * Class ilCtrlStructureArtifactObjective
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
class ilCtrlStructureArtifactObjective extends BuildArtifactObjective
{
    /**
     * @var string relative path to the php artifact file.
     */
    public const ARTIFACT_PATH = "./Services/UICore/artifacts/ctrl_structure.php";

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
    public function build() : Artifact
    {
        $ilias_path = dirname(__FILE__, 6);
        $class_map = require $ilias_path . "/libs/composer/vendor/composer/autoload_classmap.php";

        return new ArrayArtifact(
            (new ilCtrlStructureReader(
                new ilCtrlArrayIterator($class_map),
                new ilCtrlStructureCidGenerator()
            ))->readStructure()
        );
    }
}
