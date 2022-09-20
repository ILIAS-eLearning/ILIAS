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

use ilObjectContentStyleSettingsGUI;
use ilGlobalTemplateInterface;
use ilObjStyleSheet;

/**
 * Facade for consumer gui interface
 * @author Alexander Killing <killing@leifos.de>
 */
class GUIService
{
    private InternalService $internal;

    public function __construct(
        InternalService $internal_service
    ) {
        $this->internal = $internal_service;
    }

    public function objectSettingsClass(bool $lower = true): string
    {
        return $this->internal->gui()->objectSettingsClass($lower);
    }

    public function objectSettingsGUIForRefId(
        ?int $selected_style_id,
        int $ref_id
    ): ilObjectContentStyleSettingsGUI {
        return $this->internal->gui()->objectSettingsGUI(
            $selected_style_id,
            $ref_id
        );
    }

    public function objectSettingsGUIForObjId(
        ?int $selected_style_id,
        int $obj_id
    ): ilObjectContentStyleSettingsGUI {
        return $this->internal->gui()->objectSettingsGUI(
            $selected_style_id,
            0,
            $obj_id
        );
    }


    public function redirectToObjectSettings(): void
    {
        $this->internal->gui()->ctrl()->redirectByClass(
            $this->objectSettingsClass(),
            ""
        );
    }

    // add effective style sheet path to global template
    public function addCss(ilGlobalTemplateInterface $tpl, int $ref_id, int $obj_id = 0): void
    {
        $eff_style_id = $this->internal->domain()->object($ref_id, $obj_id)->getEffectiveStyleId();
        $tpl->addCss(ilObjStyleSheet::getContentStylePath($eff_style_id));
    }
}
