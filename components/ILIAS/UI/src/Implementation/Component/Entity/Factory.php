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

namespace ILIAS\UI\Implementation\Component\Entity;

use ILIAS\UI\Component\Entity as I;
use ILIAS\UI\Component\Symbol\Symbol;
use ILIAS\UI\Component\Image\Image;
use ILIAS\UI\Component\Button\Shy as ShyButton;
use ILIAS\UI\Component\Link\Standard as ShyLink;

class Factory implements I\Factory
{
    public function standard(
        string|int $technical_identifier,
        Symbol|Image|ShyButton|ShyLink|string $primary_visual_identifier,
        Symbol|Image|ShyButton|ShyLink|string $secondary_visual_identifier,
    ): Standard {
        return new Standard($technical_identifier, $primary_visual_identifier, $secondary_visual_identifier);
    }
}
