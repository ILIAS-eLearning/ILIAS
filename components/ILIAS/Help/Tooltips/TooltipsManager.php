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

namespace ILIAS\Help\Tooltips;

use ILIAS\Help\InternalRepoService;
use ILIAS\Help\InternalDomainService;

class TooltipsManager
{
    protected \ILIAS\Help\GuidedTour\Admin\AdminManager $gd_admin;
    protected \ilLanguage $lng;
    protected \ilObjUser $user;
    protected \ilSetting $settings;
    protected InternalDomainService $domain;
    protected TooltipsDBRepository $repo;

    public function __construct(
        InternalRepoService $repo,
        InternalDomainService $domain
    ) {
        $this->repo = $repo->tooltips();
        $this->domain = $domain;
        $this->settings = $domain->settings();
        $this->user = $domain->user();
        $this->lng = $domain->lng();
        $this->gd_admin = $domain->guidedTour()->admin();
    }

    public function isTooltipIdentifierVisible(): bool
    {
        return $this->gd_admin->areIdentifiersVisible();
    }

    public function areTooltipsVisible(): bool
    {
        return $this->gd_admin->areIdentifiersVisible() ||
            $this->isTooltipMainTextVisible();
    }

    public function areSubMenuTooltipsVisible(): bool
    {
        return $this->isTooltipMainTextVisible();
    }

    protected function isTooltipMainTextVisible(): bool
    {
        $show_main_text = true;
        if ($this->user->getLanguage() !== "de") {
            $show_main_text = false;
        }

        if ($this->settings->get("help_mode") === "1") {
            $show_main_text = false;
        }

        if ($this->user->getPref("hide_help_tt")) {
            $show_main_text = false;
        }
        return $show_main_text;
    }

    public function getTooltipPresentationText(
        string $a_tt_id
    ): string {

        $show_main_text = $this->isTooltipMainTextVisible();

        if (!$show_main_text) {
            if ($this->isTooltipIdentifierVisible()) {
                return $a_tt_id;
            } else {
                return "";
            }
        }
        if ($this->domain->module()->isAuthoringMode()) {
            $module_ids = [0];
        } else {
            $module_ids = $this->domain->module()->getActiveModules();
        }
        $text = $this->repo->getTooltipPresentationText(
            $a_tt_id,
            $module_ids
        );
        if ($this->isTooltipIdentifierVisible()) {
            $text .= "[" . $a_tt_id . "]";
        }
        return $text;
    }

    /**
     * Get object_creation tooltip tab text
     */
    public function getObjCreationTooltipText(
        string $a_type
    ): string {
        return $this->getTooltipPresentationText($a_type . "_create");
    }

    /**
     * @return string tooltip text
     */
    public function getMainMenuTooltip(
        string $a_item_id
    ): string {
        return $a_item_id;
    }

    public function getAllTooltips(
        string $a_comp = "",
        int $a_module_id = 0
    ): array {
        return $this->repo->getAllTooltips($a_comp, $a_module_id);
    }

    public function addTooltip(
        string $a_tt_id,
        string $a_text,
        int $a_module_id = 0
    ): void {
        $this->repo->addTooltip($a_tt_id, $a_text, $a_module_id);
    }

    public function updateTooltip(
        int $a_id,
        string $a_text,
        string $a_tt_id
    ): void {
        $this->repo->updateTooltip($a_id, $a_text, $a_tt_id);
    }


    public function getTooltipComponents(
        int $a_module_id = 0
    ): array {
        $comps[""] = "- " . $this->lng->txt("help_all") . " -";
        foreach ($this->repo->getTooltipComponents($a_module_id) as $c) {
            $comps[$c] = $c;
        }
        return $comps;
    }

    public function deleteTooltip(
        int $a_id
    ): void {
        $this->repo->deleteTooltip($a_id);
    }

    public function deleteTooltipsOfModule(
        int $module_id
    ): void {
        $this->repo->deleteTooltipsOfModule($module_id);
    }

}
