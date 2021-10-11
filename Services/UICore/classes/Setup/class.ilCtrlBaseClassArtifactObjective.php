<?php

use ILIAS\Setup\Artifact\BuildArtifactObjective;
use ILIAS\Setup;

/**
 * Class ilCtrlBaseClassArtifactObjective
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
final class ilCtrlBaseClassArtifactObjective extends BuildArtifactObjective
{
    /**
     * @var string absolute path to the php artifact file.
     */
    public const ARTIFACT_PATH = "Services/UICore/artifacts/ctrl_base_classes.php";

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
        $finder = new Setup\ImplementationOfInterfaceFinder();

        $base_classes = [];
        foreach ($finder->getMatchingClassNames(ilCtrlBaseClassInterface::class) as $base_class) {
            $base_classes[] = strtolower($base_class);
        }

        return new Setup\Artifact\ArrayArtifact($base_classes);
    }
}