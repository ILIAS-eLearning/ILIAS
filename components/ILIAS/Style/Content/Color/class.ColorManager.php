<?php

declare(strict_types=1);

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

namespace ILIAS\Style\Content;

use ILIAS\Style\Content\Access;
use ilObjStyleSheet;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class ColorManager
{
    protected CharacteristicDBRepo $characteristic_repo;
    protected ColorDBRepo $color_repo;
    protected \ilObjUser $user;
    protected Access\StyleAccessManager $access_manager;
    protected int $style_id;

    public function __construct(
        int $style_id,
        Access\StyleAccessManager $access_manager,
        CharacteristicDBRepo $char_repo,
        ColorDBRepo $color_repo
    ) {
        global $DIC;

        $this->user = $DIC->user();
        $this->characteristic_repo = $char_repo;
        $this->color_repo = $color_repo;
        $this->access_manager = $access_manager;
        $this->style_id = $style_id;
    }

    public function addColor(
        string $a_name,
        string $a_code
    ): void {
        $this->color_repo->addColor(
            $this->style_id,
            $a_name,
            $a_code
        );
    }

    /**
     * Check whether color exists
     */
    public function colorExists(
        string $name
    ): bool {
        return $this->color_repo->colorExists(
            $this->style_id,
            $name
        );
    }

    /**
     * @throws ContentStyleNoPermissionException
     */
    public function updateColor(
        string $name,
        string $new_name,
        string $code
    ): void {
        if (!$this->access_manager->checkWrite()) {
            throw new ContentStyleNoPermissionException("No write permission for style.");
        }

        $this->color_repo->updateColor(
            $this->style_id,
            $name,
            $new_name,
            $code
        );

        ilObjStyleSheet::_writeUpToDate($this->style_id, false);

        // rename also the name in the style parameter values
        if ($name != $new_name) {
            $this->characteristic_repo->updateColorName(
                $this->style_id,
                $name,
                $new_name
            );
        }
    }
}
