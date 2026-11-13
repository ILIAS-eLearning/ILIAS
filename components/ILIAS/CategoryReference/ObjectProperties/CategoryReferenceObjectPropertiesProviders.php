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

use ILIAS\ILIASObject\Properties\ObjectTypeSpecificProperties\ObjectTypeSpecificPropertyProviders;
use ILIAS\UI\Component\Symbol\Icon\Custom as CustomIcon;
use ILIAS\UI\Component\Symbol\Icon\Factory as IconFactory;
use ILIAS\UI\Component\Image\Image;
use ILIAS\UI\Component\Image\Factory as ImageFactory;
use ILIAS\ResourceStorage\Services as StorageService;

class CategoryReferenceObjectPropertiesProviders implements ObjectTypeSpecificPropertyProviders
{
    public function getObjectTypeSpecificTileImage(
        int $object_id,
        ImageFactory $factory,
        StorageService $irss
    ): ?Image {
        return null;
    }

    public function getObjectTypeSpecificIcon(
        int $object_id,
        IconFactory $factory,
        StorageService $irss
    ): ?CustomIcon {
<<<<<<< HEAD
        $category = new ilObjCategory(
            ilContainerReference::_lookupTargetId($object_id),
            false
        );
=======
        $target_obj_id = ilContainerReference::_lookupTargetId($obj_id);
        if (ilObject::_lookupType($target_obj_id) !== 'cat') {
            return null;
        }

        $category = new ilObjCategory($target_obj_id, false);
>>>>>>> 6bc6fd9dd11 (48743: Course crash on category reference with wrong target type)

        $custom_icon = $category->getObjectProperties()->getPropertyIcon()->getCustomIcon();
        if ($custom_icon?->exists()) {
            return $factory->custom(
                $custom_icon->getFullPath(),
                ''
            );
        }

        return null;
    }
}
