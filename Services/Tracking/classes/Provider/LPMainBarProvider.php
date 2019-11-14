<?php namespace ILIAS\LearningProgress;

use ILIAS\GlobalScreen\Scope\MainMenu\Provider\AbstractStaticMainMenuProvider;
use ILIAS\MainMenu\Provider\StandardTopItemsProvider;
use ilObjUserTracking;

/**
 * Class LPMainBarProvider
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class LPMainBarProvider extends AbstractStaticMainMenuProvider
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
        global $DIC;

        $title = $this->dic->language()->txt("mm_learning_progress");
        $icon = $this->dic->ui()->factory()->symbol()->icon()->standard("trac", $title)->withIsOutlined(true);
        $icon = $this->dic->ui()->factory()->symbol()->icon()->custom(\ilUtil::getImagePath("simpleline/graph.svg"), $title);

        $ctrl = $DIC->ctrl();
        return [
            $this->mainmenu->link($this->if->identifier('mm_pd_lp'))
                ->withTitle($title)
                ->withAction($ctrl->getLinkTargetByClass(["ilDashboardGUI",
                    "ilAchievementsGUI","ilLearningProgressGUI","ilLPListOfProgressGUI"]))
                ->withParent(StandardTopItemsProvider::getInstance()->getAchievementsIdentification())
                ->withPosition(30)
	            ->withSymbol($icon)
                ->withNonAvailableReason($this->dic->ui()->factory()->legacy("{$this->dic->language()->txt('component_not_active')}"))
                ->withAvailableCallable(
                    function () {
                        return (bool) (ilObjUserTracking::_enabledLearningProgress()
                            && (ilObjUserTracking::_hasLearningProgressOtherUsers()
                                || ilObjUserTracking::_hasLearningProgressLearner()));
                    }
                ),
        ];
    }
}
