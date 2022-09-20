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

/**
 * Favourites UI
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilFavouritesListGUI
{
    protected ilPDSelectedItemsBlockViewGUI $block_view;
    protected \ILIAS\DI\UIServices $ui;
    protected ilCtrl $ctrl;
    protected ilLanguage $lng;

    public function __construct(?ilObjUser $user = null)
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

    public function render(): string
    {
        $f = $this->ui->factory();
        $item_groups = [];
        $ctrl = $this->ctrl;
        foreach ($this->block_view->getItemGroups() as $group) {
            $items = [];
            foreach ($group->getItems() as $item) {
                $items[] = $f->item()->standard(
                    $f->link()->standard($item["title"], ilLink::_getLink($item["ref_id"]))
                )->withLeadIcon($f->symbol()->icon()->custom(ilObject::_getIcon((int) $item["obj_id"]), $item["title"]));
            }
            if (count($items) > 0) {
                $item_groups[] = $f->item()->group($group->getLabel(), $items);
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
        }

        $pdblock = new ilPDSelectedItemsBlockGUI();
        return $pdblock->getNoItemFoundContent();
    }
}
