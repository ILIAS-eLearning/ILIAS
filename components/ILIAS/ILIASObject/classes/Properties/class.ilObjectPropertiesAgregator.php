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

use ILIAS\Object\Properties\ObjectTypeSpecificProperties\Factory as ObjectTypeSpecificPropertiesFactory;

/**
 * Description of class
 *
 * @author Stephan Kergomard
 */
class ilObjectPropertiesAgregator
{
    public function __construct(
        private ilObjectCorePropertiesRepository $core_properties_repository,
        private ilObjectAdditionalPropertiesRepository $additional_properties_repository,
        private ObjectTypeSpecificPropertiesFactory $object_type_specific_properties_factory
    ) {
    }

    public function getFor(int $object_id): ilObjectProperties
    {
        $core_properties = $this->core_properties_repository->getFor($object_id);
        return new ilObjectProperties(
            $core_properties,
            $this->core_properties_repository,
            $this->additional_properties_repository->getFor($object_id),
            $this->additional_properties_repository,
            new ilMD($object_id, 0, $core_properties->getType())
        );
    }

    public function preload(array $object_ids): void
    {
        $this->core_properties_repository->preload($object_ids);
        $objects_by_type = [];
        foreach ($object_ids as $obj_id) {
            $type = ilObject::_lookupType($obj_id);

            if (!array_key_exists($type, $objects_by_type)) {
                $objects_by_type[$type] = [];
            }
            $objects_by_type[$type][] = $obj_id;
        }

        foreach ($objects_by_type as $type => $obj_ids) {
            $this->object_type_specific_properties_factory->getForObjectTypeString($type)?->preload($obj_ids);
        }
    }
}
