<?php

declare(strict_types=1);

/* Copyright (c) 2021 Thibeau Fuhrer <thf@studer-raimann.ch> Extended GPL, see docs/LICENSE */

require_once __DIR__ . "/../../../../../libs/composer/vendor/autoload.php";

use ILIAS\Setup\Artifact\BuildArtifactObjective;
use ILIAS\Setup\ImplementationOfInterfaceFinder;
use ILIAS\Setup\Artifact\ArrayArtifact;
use ILIAS\Setup\Artifact;

/**
 * Class ilCtrlSecurityArtifactObjective
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
class ilCtrlSecurityArtifactObjective extends BuildArtifactObjective
{
    /**
     * @var string relative path to the php artifact file.
     */
    public const ARTIFACT_PATH = "./Services/UICore/artifacts/ctrl_security.php";

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

        $security_information = [];
        foreach ($finder->getMatchingClassNames(ilCtrlSecurityInterface::class) as $class) {
            try {
                $reflection = new ReflectionClass($class);

                /** @var $gui_object ilCtrlSecurityInterface */
                $gui_object = $reflection->newInstanceWithoutConstructor();

                $security_information[strtolower($class)] = [
                    ilCtrlStructureInterface::KEY_UNSAFE_COMMANDS => $gui_object->getUnsafeGetCommands(),
                    ilCtrlStructureInterface::KEY_SAFE_COMMANDS => $gui_object->getSafePostCommands(),
                ];
            } catch (ReflectionException $e) {
                continue;
            }
        }

        return new ArrayArtifact($security_information);
    }
}
