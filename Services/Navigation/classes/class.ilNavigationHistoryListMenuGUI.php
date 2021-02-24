<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\Data\URI;
use ILIAS\DI\Container;

/**
 * Favourites UI
 *
 * @author killing@leifos.de
 */
class ilNavigationHistoryListMenuGUI
{
    /**
     * @var ilPDSelectedItemsBlockViewGUI
     */
    protected $block_view;

    /**
     * @var Container
     */
    protected $dic;


    /**
     * Constructor
     */
    public function __construct()
    {
        global $DIC;
        $this->dic = $DIC;
    }

    /**
     * Render
     *
     * @return string
     */
    public function render()
    {
        $nav_items = [];
        if (isset($this->dic['ilNavigationHistory'])) {
            $nav_items = $this->dic['ilNavigationHistory']->getItems();
        }

        $items = [];
        foreach (array_slice($nav_items, 0, 10) as $nav_item) {
            if (!isset($nav_item["ref_id"]) || !isset($_GET["ref_id"])
                || ($nav_item["ref_id"] != $_GET["ref_id"])
            ) {
                $obj_id = ilObject::_lookupObjectId($nav_item["ref_id"]);
                $url = $nav_item["link"];
                if(strpos($nav_item["link"], ilUtil::_getHttpPath()) !== 0) {
                    $url = ilUtil::_getHttpPath() . '/' . $nav_item["link"];
                }
                $items[] = $this->dic->ui()->factory()->link()->bulky(
                    $this->dic->ui()->factory()->symbol()->icon()->custom(ilObject::_getIcon($obj_id), $nav_item["title"]),
                    $nav_item["title"],
                    new URI($url)
                );
            }
        }


        return $this->dic->ui()->renderer()->render($items);
    }
}
