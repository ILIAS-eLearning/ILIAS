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

use ILIAS\UI\Implementation\Render\ImagePathResolver;

class ilImagePathResolver implements ImagePathResolver
{
    public function resolveImagePath(string $image_path): string
    {
        global $DIC;

        $styleDefinition = $DIC["styleDefinition"] ?? null;

        // default image
        $default_img = "./assets/images/" . $image_path;

        // use ilStyleDefinition instead of account to get the current skin and style
        $current_skin = ilStyleDefinition::getCurrentSkin();
        $current_style = ilStyleDefinition::getCurrentStyle();

        $skin_img = "";

        if (is_object($styleDefinition) && $current_skin != "default") {
            $image_dir = $styleDefinition->getImageDirectory($current_style);
            $skin_img = "./Customizing/skin/" .
                $current_skin . "/" . $current_style . "/" . $image_dir . "/" . $image_path;
        }

        if (file_exists($skin_img)) {
            return $skin_img;        // found image for skin and style
        }

        return $default_img;            // take image in default
    }
}
