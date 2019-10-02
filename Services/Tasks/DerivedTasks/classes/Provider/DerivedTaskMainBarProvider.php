<?php namespace ILIAS\Tasks\DerivedTasks\Provider;

use ILIAS\GlobalScreen\Scope\MainMenu\Provider\AbstractStaticMainMenuProvider;
use ILIAS\MainMenu\Provider\StandardTopItemsProvider;

/**
 * Main menu entry for derived tasks
 *
 * @author <killing@leifos.de>
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

        $icon = $this->dic->ui()->factory()->symbol()->icon()->custom(\ilUtil::getImagePath("simpleline/check.svg"), "");

        // derived tasks list
        $entries[] = $this->mainmenu->link($this->if->identifier('mm_derived_task_list'))
            ->withTitle($this->dic->language()->txt("mm_task_derived_tasks"))
            ->withPosition(40)
	        ->withSymbol($icon)
            ->withAction($dic->ctrl()->getLinkTargetByClass(["ilDerivedTasksGUI"], ""))
            ->withParent(StandardTopItemsProvider::getInstance()->getPersonalWorkspaceIdentification())
            ->withVisibilityCallable(
                function () use ($dic) {
                    return true;
                }
            );

        return $entries;
    }
}
