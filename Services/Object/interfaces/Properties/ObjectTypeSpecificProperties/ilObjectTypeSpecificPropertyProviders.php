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

namespace ILIAS\Object\Properties\ObjectTypeSpecificProperties;

use ILIAS\UI\Component\Image\Image;
use ILIAS\UI\Component\Image\Factory as ImageFactory;
use ILIAS\UI\Component\Symbol\Icon\Icon;
use ILIAS\UI\Component\Symbol\Icon\Factory as IconFactory;
use ILIAS\ResourceStorage\Services as StorageService;

interface ilObjectTypeSpecificPropertyProviders
{
    /**
     *
     * @param int $card_size Specifies what CardSize this will be displayed as, thus
     * allowing for responsive images.
     */
    public function getObjectTypeSpecificTileImage(
        int $object_id,
        ImageFactory $factory,
        StorageService $irss
    ): ?Image;
    public function getObjectTypeSpecificCustomIcon(
        int $object_id,
        IconFactory $factory,
        StorageService $irss
    ): ?Icon;
}
