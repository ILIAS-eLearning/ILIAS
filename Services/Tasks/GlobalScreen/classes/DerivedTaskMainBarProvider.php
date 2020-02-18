<?php namespace ILIAS\Tasks\DerivedTasks\Provider;

use ILIAS\GlobalScreen\Scope\MainMenu\Provider\AbstractStaticMainMenuProvider;
use ILIAS\MainMenu\Provider\StandardTopItemsProvider;
use ILIAS\UI\Component\Symbol\Icon\Standard;

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

        $title = $this->dic->language()->txt("mm_task_derived_tasks");
        $icon = $this->dic->ui()->factory()->symbol()->icon()->standard(Standard::TASK, $title)->withIsOutlined(true);

        // derived tasks list
        $entries[] = $this->mainmenu->link($this->if->identifier('mm_derived_task_list'))
            ->withTitle($title)
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
