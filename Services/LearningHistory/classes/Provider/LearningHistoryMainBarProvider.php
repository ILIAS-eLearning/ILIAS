<?php namespace ILIAS\LearningHistory;

use ilAchievementsGUI;
use ILIAS\GlobalScreen\Scope\MainMenu\Provider\AbstractStaticMainMenuProvider;
use ILIAS\MainMenu\Provider\StandardTopItemsProvider;
use ilLearningHistoryGUI;
use ilDashboardGUI;

/**
 * Class LearningHistoryMainBarProvider
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class LearningHistoryMainBarProvider extends AbstractStaticMainMenuProvider
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
        $entries = [];

        $title = $this->dic->language()->txt("mm_learning_history");
        $icon = $this->dic->ui()->factory()->symbol()->icon()->standard("lhist", $title)->withIsOutlined(true);
        $icon = $this->dic->ui()->factory()->symbol()->icon()->custom(\ilUtil::getImagePath("simpleline/rocket.svg"), $title);

        $entries[] = $this->mainmenu->link($this->if->identifier('learning_history'))
            ->withTitle($title)
            ->withAction($this->dic->ctrl()->getLinkTargetByClass([
                ilDashboardGUI::class,
                ilAchievementsGUI::class,
                ilLearningHistoryGUI::class,
            ], ""))
            ->withParent(StandardTopItemsProvider::getInstance()->getAchievementsIdentification())
            ->withPosition(10)
	        ->withSymbol($icon)
            ->withAvailableCallable(
                function () {
                    $achievements = new \ilAchievements();

                    return (bool) $achievements->isAnyActive();
                }
            );

        return $entries;
    }
}
