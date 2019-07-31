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
        return [
            $this->mainmenu->link($this->if->identifier('mm_pd_lp'))
                ->withTitle($this->dic->language()->txt("mm_learning_progress"))
                ->withAction("ilias.php?baseClass=ilPersonalDesktopGUI&cmd=jumpToLP")
                ->withParent(StandardTopItemsProvider::getInstance()->getAchievementsIdentification())
                ->withPosition(30)
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
