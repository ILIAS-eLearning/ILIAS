<?php

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
 ********************************************************************
 */

namespace ILIAS\Skill\Provider;

use ILIAS\GlobalScreen\Scope\MainMenu\Provider\AbstractStaticMainMenuProvider;
use ILIAS\MainMenu\Provider\StandardTopItemsProvider;
use ILIAS\UI\Component\Symbol\Icon\Icon;
use ILIAS\UI\Component\Symbol\Icon\Standard;
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

        $title = $this->dic->language()->txt("mm_skills");
        $icon = $this->dic->ui()->factory()->symbol()->icon()->standard("skmg", $title)->withIsOutlined(true);
        $icon = $this->dic->ui()->factory()->symbol()->icon()->standard(Standard::SKMG, $title)->withIsOutlined(true);

        $ctrl = $DIC->ctrl();
        $ctrl->clearParametersByClass("ilPersonalSkillsGUI");
        $ctrl->setParameterByClass("ilPersonalSkillsGUI", "list_mode", \ilPersonalSkillsGUI::LIST_PROFILES);
        $link = $ctrl->getLinkTargetByClass(["ilDashboardGUI", "ilAchievementsGUI", "ilPersonalSkillsGUI"]);
        $ctrl->clearParameterByClass("ilPersonalSkillsGUI", "list_mode");
        return [
            $this->mainmenu->link($this->if->identifier('mm_pd_skill'))
                ->withTitle($title)
                ->withAction($link)
                ->withParent(StandardTopItemsProvider::getInstance()->getAchievementsIdentification())
                ->withPosition(20)
                ->withSymbol($icon)
                ->withNonAvailableReason($this->dic->ui()->factory()->legacy($this->dic->language()->txt('component_not_active')))
                ->withAvailableCallable(
                    static function () : bool {
                        $skmg_set = new ilSetting("skmg");

                        return (bool) ($skmg_set->get("enable_skmg"));
                    }
                ),
        ];
    }
}
