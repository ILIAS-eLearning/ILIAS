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

use ILIAS\Object\Properties\CoreProperties\TileImage\ilObjectTileImageFlavourDefinition;

class FirstPageToTileImageFlavourDefinition extends ilObjectTileImageFlavourDefinition
{
    private const ID = 'b9b2f16325492412304989a9b3e32479e612957582f60ed667af31e7b36e50ed';

    public function getId(): string
    {
        return self::ID;
    }

    public function getFlavourMachineId(): string
    {
        return FirstPageToTileImageMachine::ID;
    }

    public function getInternalName(): string
    {
        return 'first_page_tile_image';
    }
}
