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

namespace ILIAS\Taxonomy\Settings;

use ILIAS\Taxonomy\InternalDomainService;
use ILIAS\Taxonomy\InternalGUIService;

class GUIService
{
    protected InternalGUIService $gui;
    protected InternalDomainService $domain;

    public function __construct(
        InternalDomainService $domain,
        InternalGUIService $gui
    ) {
        $this->domain = $domain;
        $this->gui = $gui;
    }

    public function getSettingsGUI(
        int $rep_obj_id,
        string $list_info = "",
        bool $multiple = true,
        \ILIAS\Taxonomy\Settings\ModifierGUIInterface $modifier = null
    ): \ilTaxonomySettingsGUI {
        return new \ilTaxonomySettingsGUI(
            $this->domain,
            $this->gui,
            $rep_obj_id,
            $list_info,
            $multiple,
            $modifier
        );
    }

    public function addSubTab(int $rep_obj_id): void
    {
        $tabs = $this->gui->tabs();
        $ctrl = $this->gui->ctrl();
        $lng = $this->domain->lng();
        if ($this->domain->settings($rep_obj_id)->isActivated()) {
            $lng->loadLanguageModule("tax");
            $tabs->addSubTab(
                "tax_settings",
                $lng->txt("tax_taxonomy"),
                $ctrl->getLinkTargetByClass(\ilTaxonomySettingsGUI::class, "")
            );
        }
    }
}
