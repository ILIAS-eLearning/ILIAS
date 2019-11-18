<?php declare(strict_types=1);

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Dashboard objects table renderer
 */
class ilDashObjectsTableRenderer
{
    protected $parent_gui;

    /**
     * Constructor
     * @param $parent_gui
     */
    public function __construct($parent_gui)
    {
        $this->parent_gui = $parent_gui;
    }


    /**
     * @inheritDoc
     */
    public function render(array $groupedItems): string
    {
        $cnt = 0;
        $html = "";
        foreach ($groupedItems as $group) {

            $items = $group->getItems();
            if (count($items) > 0) {
                $table = new ilDashObjectsTableGUI($this->parent_gui, "render", $cnt++);
                $table->setTitle($group->getLabel());
                $table->setData($group->getItems());
                $html .= $table->getHTML();
            }
        }
        return $html;
    }
}