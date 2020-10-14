<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\Data\URI;

/**
 * Favourites UI for menu entries
 *
 * @author iszmais@databay.de
 */
class ilFavouritesListMenuGUI extends ilFavouritesListGUI
{
    /**
     * Render
     *
     * @return string
     */
    public function render()
    {
        $this->ctrl->setParameterByClass("ilPDSelectedItemsBlockGUI", "view", "0");
        $this->ctrl->setParameterByClass("ilPDSelectedItemsBlockGUI", "col_side", "center");
        $this->ctrl->setParameterByClass("ilPDSelectedItemsBlockGUI", "block_type", "pditems");
        $items[] = $this->ui->factory()->link()->bulky(
            $this->ui->factory()->symbol()->icon()->standard('adm',$this->lng->txt("rep_configure")),
            $this->lng->txt("rep_configure"),
            new URI(ilUtil::_getHttpPath() . '/' .
                $this->ctrl->getLinkTargetByClass(
                    ["ilDashboardGUI", "ilColumnGUI", "ilPDSelectedItemsBlockGUI"],
                    "manage"
                )
            )
        );
        foreach ($this->block_view->getItemGroups() as $group) {
            foreach ($group->getItems() as $item) {
                $items[] = $this->ui->factory()->link()->bulky(
                    $this->ui->factory()->symbol()->icon()->custom(ilObject::_getIcon($item["obj_id"]),$item['title']),
                    $item['title'],
                    new URI(ilLink::_getLink($item["ref_id"]))
                );
            }
        }
        return $this->ui->renderer()->render($items);
    }
}
