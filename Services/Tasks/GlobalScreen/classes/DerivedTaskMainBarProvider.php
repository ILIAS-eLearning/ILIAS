<?php namespace ILIAS\Tasks\DerivedTasks\Provider;

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
