<?php namespace ILIAS\Tasks\DerivedTasks\Provider;

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

use ILIAS\GlobalScreen\Scope\MainMenu\Provider\AbstractStaticMainMenuProvider;
use ILIAS\MainMenu\Provider\StandardTopItemsProvider;
use ILIAS\UI\Component\Symbol\Icon\Standard;
use ilDerivedTasksGUI;

/**
 * Main menu entry for derived tasks
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class DerivedTaskMainBarProvider extends AbstractStaticMainMenuProvider
{

    /**
     * @inheritDoc
     */
    public function getStaticTopItems() : array
    {
        return [];
    }


    /**
     * @inheritDoc
     */
    public function getStaticSubItems() : array
    {
        $dic = $this->dic;

        $title = $this->dic->language()->txt("mm_task_derived_tasks");
        $icon = $this->dic->ui()->factory()->symbol()->icon()->standard(Standard::TASK, $title);

        // derived tasks list
        $entries[] = $this->mainmenu->link($this->if->identifier('mm_derived_task_list'))
            ->withTitle($title)
            ->withPosition(40)
            ->withSymbol($icon)
            ->withAction($dic->ctrl()->getLinkTargetByClass([ilDerivedTasksGUI::class], ""))
            ->withParent(StandardTopItemsProvider::getInstance()->getPersonalWorkspaceIdentification())
            ->withVisibilityCallable(
                fn () : bool => true
            );

        return $entries;
    }
}
