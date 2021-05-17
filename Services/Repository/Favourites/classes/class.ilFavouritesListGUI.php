<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Favourites UI
 *
 * @author killing@leifos.de
 */
class ilFavouritesListGUI
{
    /**
     * @var ilPDSelectedItemsBlockViewGUI
     */
    protected $block_view;

    /**
     * @var \ILIAS\DI\UIServices
     */
    protected $ui;

    /**
     * @var ilCtrl
     */
    protected $ctrl;


    /**
     * Constructor
     */
    public function __construct($user = null)
    {
        global $DIC;

        if (is_null($user)) {
            $user = $DIC->user();
        }

        $settings = new ilPDSelectedItemsBlockViewSettings($user);
        $settings->parse();
        $this->block_view = ilPDSelectedItemsBlockViewGUI::bySettings($settings);
        $this->ui = $DIC->ui();
        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->lng->loadLanguageModule("rep");
    }

    /**
     * Render
     *
     * @return string
     */
    public function render()
    {
        $f = $this->ui->factory();
        $item_groups = [];
        $ctrl = $this->ctrl;
        foreach ($this->block_view->getItemGroups() as $group) {
            $items = [];
            foreach ($group->getItems() as $item) {
                $items[] = $f->item()->standard(
                    $f->link()->standard($item["title"], ilLink::_getLink($item["ref_id"]))
                )->withLeadIcon($f->symbol()->icon()->custom(ilObject::_getIcon($item["obj_id"]), $item["title"]));
            }
            if (count($items) > 0) {
                $item_groups[] = $f->item()->group((string) $group->getLabel(), $items);
            }
        }
        if (count($item_groups) > 0) {
            $ctrl->setParameterByClass("ilPDSelectedItemsBlockGUI", "view", "0");
            $ctrl->setParameterByClass("ilPDSelectedItemsBlockGUI", "col_side", "center");
            $ctrl->setParameterByClass("ilPDSelectedItemsBlockGUI", "block_type", "pditems");
            $panel = $f->panel()->secondary()->listing("", $item_groups);
            $panel = $panel->withActions($f->dropdown()->standard([$f->link()->standard(
                $this->lng->txt("rep_configure"),
                $ctrl->getLinkTargetByClass(
                    ["ilDashboardGUI", "ilColumnGUI", "ilPDSelectedItemsBlockGUI"],
                    "manage"
                )
            )
            ]));
            return $this->ui->renderer()->render([$panel]);
        } else {
            $pdblock = new ilPDSelectedItemsBlockGUI();
            return $pdblock->getNoItemFoundContent();
        }
    }
}
