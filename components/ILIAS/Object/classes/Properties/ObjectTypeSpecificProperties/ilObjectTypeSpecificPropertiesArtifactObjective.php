<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

namespace ILIAS\Object\Properties\ObjectTypeSpecificProperties;

use ILIAS\Object\Properties\ObjectTypeSpecificProperties\ilObjectTypeSpecificProperties;
use ILIAS\Setup\Artifact;
use ILIAS\Setup\Artifact\ArrayArtifact;
use ILIAS\Setup\Artifact\BuildArtifactObjective;
use ILIAS\Setup\ImplementationOfInterfaceFinder;
use ILIAS\Setup\Environment;

class ilObjectTypeSpecificPropertiesArtifactObjective extends BuildArtifactObjective
{
    public const PATH = '../components/ILIAS/Object/artifacts/object_specific_properties.php';

    public function getArtifactPath(): string
    {
        return self::PATH;
    }

    public function build(): Artifact
    {
        $finder = new ImplementationOfInterfaceFinder();

        $object_properties = [];

        foreach ($finder->getMatchingClassNames(ilObjectTypeSpecificProperties::class) as $object_properties_class) {
            /** @var $properties \ILIAS\Object\Properties\ObjectTypeSpecificProperties */
            $properties = new $object_properties_class();
            $object_type = $properties->getObjectTypeString();

            $object_properties[$object_type] = $object_properties_class;
        }
        return new ArrayArtifact($object_properties);
    }
}
