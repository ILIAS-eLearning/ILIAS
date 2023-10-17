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

/**
 * @ilCtrl_Calls ilTaxonomySettingsGUI: ilObjTaxonomyGUI
 */
class ilTaxonomySettingsGUI
{
    protected ilTabsGUI $tabs;
    protected ilGlobalTemplateInterface $tpl;
    protected ilCtrl $ctrl;
    protected ilLanguage $lng;
    protected ilToolbarGUI $toolboar;
    protected ?\ILIAS\Taxonomy\Settings\ModifierGUIInterface $modifier;
    protected string $list_info;
    protected bool $multiple;
    protected \ILIAS\Taxonomy\InternalGUIService $gui;
    protected \ILIAS\Taxonomy\InternalDomainService $domain;
    protected int $rep_obj_id;
    protected bool $assigned_item_sorting = false;
    protected ilTaxAssignedItemInfo $assigned_item_info_obj;
    protected string $assigned_item_comp_id = "";
    protected int $assigned_item_obj_id = 0;
    protected string $assigned_item_type = "";


    public function __construct(
        \ILIAS\Taxonomy\InternalDomainService $domain,
        \ILIAS\Taxonomy\InternalGUIService $gui,
        int $rep_obj_id,
        string $list_info = "",
        bool $multiple = true,
        \ILIAS\Taxonomy\Settings\ModifierGUIInterface $modifier = null
    ) {
        $this->domain = $domain;
        $this->gui = $gui;

        $this->toolboar = $gui->toolbar();
        $this->ctrl = $gui->ctrl();
        $this->tpl = $gui->ui()->mainTemplate();
        $this->tabs = $gui->tabs();

        $this->lng = $domain->lng();

        $this->rep_obj_id = $rep_obj_id;
        $this->multiple = $multiple;
        $this->list_info = $list_info;
        $this->modifier = $modifier;

    }

    public function withAssignedItemSorting(
        ilTaxAssignedItemInfo $a_item_info_obj,
        string $a_component_id,
        int $a_obj_id,
        string $a_item_type
    ): self {
        $new = clone $this;
        $new->assigned_item_sorting = true;
        $new->assigned_item_info_obj = $a_item_info_obj;
        $new->assigned_item_comp_id = $a_component_id;
        $new->assigned_item_obj_id = $a_obj_id;
        $new->assigned_item_type = $a_item_type;
        return $new;
    }

    public function executeCommand(): void
    {
        $ctrl = $this->gui->ctrl();

        $next_class = $ctrl->getNextClass($this);
        $cmd = $ctrl->getCmd("listTaxonomies");

        $this->tabs->activateSubTab("tax_settings");

        switch ($next_class) {

            case strtolower(ilObjTaxonomyGUI::class):
                $ctrl->setReturn($this, "");
                $tax_gui = $this->gui->getObjTaxonomyGUI($this->rep_obj_id);
                if ($this->assigned_item_sorting) {
                    $tax_gui->activateAssignedItemSorting(
                        $this->assigned_item_info_obj,
                        $this->assigned_item_comp_id,
                        $this->assigned_item_obj_id,
                        $this->assigned_item_type
                    );
                }
                $this->ctrl->forwardCommand($tax_gui);
                break;

            default:
                if (in_array($cmd, ["listTaxonomies"])) {
                    $this->$cmd();
                }
        }
    }

    protected function listTaxonomies(): void
    {
        $f = $this->gui->ui()->factory();
        $r = $this->gui->ui()->renderer();
        $um = $this->domain->usage();
        $tax_ids = $um->getUsageOfObject($this->rep_obj_id, true);
        if ($this->multiple || count($tax_ids) === 0) {
            $this->toolboar->addButton(
                $this->lng->txt("tax_add_taxonomy"),
                $this->ctrl->getLinkTargetByClass(ilObjTaxonomyGUI::class, "createAssignedTaxonomy")
            );
        } else {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt("tax_max_one_tax"));
        }
        $items = [];
        foreach($tax_ids as $t) {
            $this->ctrl->setParameterByClass(ilObjTaxonomyGUI::class, "tax_id", $t["tax_id"]);
            $action = [];
            $action[] = $f->button()->shy(
                $this->lng->txt("edit"),
                $this->ctrl->getLinkTargetByClass(ilObjTaxonomyGUI::class, "listNodes")
            );
            $action[] = $f->button()->shy(
                $this->lng->txt("delete"),
                $this->ctrl->getLinkTargetByClass(ilObjTaxonomyGUI::class, "confirmDeleteTaxonomy")
            );
            $properties = [];
            if ($this->modifier) {
                $properties = $this->modifier->getProperties((int) $t["tax_id"]);
                foreach ($this->modifier->getActions((int) $t["tax_id"]) as $act) {
                    $action[] = $act;
                }
            }
            $dd = $f->dropdown()->standard($action);
            $item = $f->item()->standard($t["title"])->withActions($dd);
            if (count($properties) > 0) {
                $item = $item->withProperties($properties);
            }
            $items[] = $item;
        }
        $title = ($this->multiple)
            ? $this->lng->txt("obj_taxf")
            : $this->lng->txt("obj_tax");
        $panel = $f->panel()->listing()->standard(
            $title,
            [$f->item()->group("", $items) ]
        );
        $components = [];
        if ($this->list_info !== "") {
            $this->tpl->setOnScreenMessage("info", $this->list_info);
        }
        $components[] = $panel;
        $this->tpl->setContent($r->render($components));
    }

}
