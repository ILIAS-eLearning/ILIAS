<?php namespace ILIAS\Skill\Provider;

use ILIAS\GlobalScreen\Scope\MainMenu\Provider\AbstractStaticMainMenuProvider;
use ILIAS\MainMenu\Provider\StandardTopItemsProvider;
use ilSetting;

/**
 * Class SkillMainBarProvider
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class SkillMainBarProvider extends AbstractStaticMainMenuProvider
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

        $ctrl = $DIC->ctrl();
        return [
            $this->mainmenu->link($this->if->identifier('mm_pd_skill'))
                ->withTitle($this->dic->language()->txt("mm_skills"))
                ->withAction($ctrl->getLinkTargetByClass(["ilPersonalDesktopGUI", "ilAchievementsGUI","ilPersonalSkillsGUI"]))
                ->withParent(StandardTopItemsProvider::getInstance()->getAchievementsIdentification())
                ->withPosition(20)
	            ->withSymbol($this->dic->ui()->factory()->symbol()->icon()->standard("skmg", "")->withIsOutlined(true))
                ->withNonAvailableReason($this->dic->ui()->factory()->legacy("{$this->dic->language()->txt('component_not_active')}"))
                ->withAvailableCallable(
                    function () {
                        $skmg_set = new ilSetting("skmg");

                        return (bool) ($skmg_set->get("enable_skmg"));
                    }
                ),
        ];
    }
}
