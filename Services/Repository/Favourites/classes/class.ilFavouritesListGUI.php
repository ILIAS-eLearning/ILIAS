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
        $this->block_view = ilPDSelectedItemsBlockViewGUI::bySettings($settings);
        $this->ui = $DIC->ui();
        $this->ctrl = $DIC->ctrl();
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
        foreach ($this->block_view->getItemGroups() as $group)
        {
            $items = [];
            foreach ($group->getItems() as $item) {
                $items[] = $f->item()->standard(
                    $f->button()->shy($item["title"], ilLink::_getLink($item["ref_id"]))
                )->withLeadIcon($f->symbol()->icon()->custom(ilObject::_getIcon($item["obj_id"]), $item["title"]));

            }
            $item_groups[] = $f->item()->group($group->getLabel(), $items);
        }
        $panel = $f->panel()->secondary()->listing("", $item_groups);
        //$panel = $panel->withActions($f->dropdown()->standard([$f->link()->standard("Configure", "#")]));
        $ctrl->setParameterByClass("ilPDSelectedItemsBlockGUI", "view", "0");
        $ctrl->setParameterByClass("ilPDSelectedItemsBlockGUI", "col_side", "center");
        $ctrl->setParameterByClass("ilPDSelectedItemsBlockGUI", "block_type", "pditems");
        $link = $f->link()->standard("Configure",
            $ctrl->getLinkTargetByClass(["ilDashboardGUI", "ilColumnGUI", "ilPDSelectedItemsBlockGUI"],
                "manage"));
        return $this->ui->renderer()->render([$link, $panel]);
    }


}