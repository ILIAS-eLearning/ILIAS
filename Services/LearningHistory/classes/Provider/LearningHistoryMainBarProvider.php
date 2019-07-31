<?php namespace ILIAS\LearningHistory;

use ilAchievementsGUI;
use ILIAS\GlobalScreen\Scope\MainMenu\Provider\AbstractStaticMainMenuProvider;
use ILIAS\MainMenu\Provider\StandardTopItemsProvider;
use ilLearningHistoryGUI;
use ilPersonalDesktopGUI;

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

        $entries[] = $this->mainmenu->link($this->if->identifier('learning_history'))
            ->withTitle($this->dic->language()->txt("mm_learning_history"))
            ->withAction($this->dic->ctrl()->getLinkTargetByClass([
                ilPersonalDesktopGUI::class,
                ilAchievementsGUI::class,
                ilLearningHistoryGUI::class,
            ], ""))
            ->withParent(StandardTopItemsProvider::getInstance()->getAchievementsIdentification())
            ->withPosition(10)
	        ->withSymbol($this->dic->ui()->factory()->symbol()->icon()->standard("lhist", "")->withIsOutlined(true))
            ->withAvailableCallable(
                function () {
                    $achievements = new \ilAchievements();

                    return (bool) $achievements->isAnyActive();
                }
            );

        return $entries;
    }
}
