<?php

declare(strict_types=1);

/* Copyright (c) 2021 Thibeau Fuhrer <thf@studer-raimann.ch> Extended GPL, see docs/LICENSE */

use ILIAS\Setup\Artifact\BuildArtifactObjective;
use ILIAS\Setup\ImplementationOfInterfaceFinder;
use ILIAS\Setup\Artifact\ArrayArtifact;
use ILIAS\Setup\Artifact;

/**
 * Class ilCtrlSecurityArtifactObjective
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
class ilCtrlBaseClassArtifactObjective extends BuildArtifactObjective
{
    /**
     * @var string relative path to the php artifact file.
     */
    public const ARTIFACT_PATH = "./Services/UICore/artifacts/ctrl_base_classes.php";

    /**
     * @inheritDoc
     */
    public function getArtifactPath(): string
    {
        return self::ARTIFACT_PATH;
    }

    /**
     * @inheritDoc
     */
    public function build(): Artifact
    {
        $finder = new ImplementationOfInterfaceFinder();

        $base_classes = [];
        foreach ($finder->getMatchingClassNames(ilCtrlBaseClassInterface::class) as $base_class) {
            $base_classes[] = strtolower($base_class);
        }

        return new ArrayArtifact($base_classes);
    }
}
