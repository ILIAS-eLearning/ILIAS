<?php

declare(strict_types=1);

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

use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;
use ILIAS\Skill\Profile\SkillProfileCompletionManager;
use ILIAS\Skill\Profile\SkillProfileManager;

/**
 * Learning history provider: Skills
 *
 * @author killing@leifos.de
 * @ingroup ServicesSkill
 */
class ilSkillLearningHistoryProvider extends ilAbstractLearningHistoryProvider implements ilLearningHistoryProviderInterface
{
    protected ilCtrl $ctrl;
    protected Factory $ui_fac;
    protected Renderer $ui_ren;
    protected SkillProfileManager $profile_manager;
    protected SkillProfileCompletionManager $profile_completion_manager;

    public function __construct(
        int $user_id,
        ilLearningHistoryFactory $factory,
        ilLanguage $lng,
        ?ilTemplate $template = null
    ) {
        global $DIC;

        parent::__construct($user_id, $factory, $lng, $template);
        $this->ctrl = $DIC->ctrl();
        $this->ui_fac = $DIC->ui()->factory();
        $this->ui_ren = $DIC->ui()->renderer();
        $this->profile_manager = $DIC->skills()->internal()->manager()->getProfileManager();
        $this->profile_completion_manager = $DIC->skills()->internal()->manager()->getProfileCompletionManager();
    }

    /**
     * @inheritdoc
     */
    public function isActive(): bool
    {
        $skmg_set = new ilSetting("skmg");
        if ($skmg_set->get("enable_skmg")) {
            return true;
        }
        return false;
    }

    /**
     * @inheritdoc
     */
    public function getEntries(int $ts_start, int $ts_end): array
    {
        $lng = $this->getLanguage();
        $lng->loadLanguageModule("skll");
        $from = new ilDateTime($ts_start, IL_CAL_UNIX);
        $to = new ilDateTime($ts_end, IL_CAL_UNIX);

        // achievements
        $completions = ilBasicSkill::getNewAchievementsPerUser($from->get(IL_CAL_DATETIME), $to->get(IL_CAL_DATETIME), $this->getUserId());

        $entries = [];
        if (isset($completions[$this->getUserId()])) {
            foreach ($completions[$this->getUserId()] as $c) {
                $ts = new ilDateTime($c["status_date"], IL_CAL_DATETIME);
                $text = str_replace("$3$", $this->getEmphasizedTitle(ilBasicSkill::_lookupTitle($c["skill_id"], $c["tref_id"])), $lng->txt("skll_lhist_skill_achieved"));
                $text = str_replace("$4$", $this->getEmphasizedTitle(ilBasicSkill::lookupLevelTitle($c["level_id"])), $text);
                $entries[] = $this->getFactory()->entry(
                    $text,
                    $text,
                    ilUtil::getImagePath("icon_skmg.svg"),
                    $ts->get(IL_CAL_UNIX),
                    $c["trigger_obj_id"]
                );
            }
        }

        // self evaluations
        $completions = ilBasicSkill::getNewAchievementsPerUser($from->get(IL_CAL_DATETIME), $to->get(IL_CAL_DATETIME), $this->getUserId(), 1);

        if (isset($completions[$this->getUserId()])) {
            foreach ($completions[$this->getUserId()] as $c) {
                $txt = ($c["trigger_obj_id"] > 0)
                    ? $lng->txt("skll_lhist_skill_self_eval_in")
                    : $lng->txt("skll_lhist_skill_self_eval");
                $ts = new ilDateTime($c["status_date"], IL_CAL_DATETIME);
                $text1 = str_replace("$3$", $this->getEmphasizedTitle(ilBasicSkill::_lookupTitle($c["skill_id"], $c["tref_id"])), $txt);
                $text1 = str_replace("$4$", $this->getEmphasizedTitle(ilBasicSkill::lookupLevelTitle($c["level_id"])), $text1);
                $entries[] = $this->getFactory()->entry(
                    $text1,
                    $text1,
                    ilUtil::getImagePath("icon_skmg.svg"),
                    $ts->get(IL_CAL_UNIX),
                    $c["trigger_obj_id"]
                );
            }
        }

        // profiles
        $completions = $this->profile_completion_manager->getFulfilledEntriesForUser($this->getUserId());

        foreach ($completions as $c) {
            $this->ctrl->setParameterByClass("ilpersonalskillsgui", "profile_id", $c->getProfileId());
            $p_link = $this->ui_fac->link()->standard(
                $this->profile_manager->lookupTitle($c->getProfileId()),
                $this->ctrl->getLinkTargetByClass("ilpersonalskillsgui", "listassignedprofile")
            );
            $ts = new ilDateTime($c->getDate(), IL_CAL_DATETIME);
            $text = str_replace(
                "$3$",
                $this->getEmphasizedTitle($this->ui_ren->render($p_link)),
                $lng->txt("skll_lhist_skill_profile_fulfilled")
            );
        }

        return $entries;
    }

    /**
     * @inheritdoc
     */
    public function getName(): string
    {
        $lng = $this->getLanguage();
        $lng->loadLanguageModule("skmg");

        return $lng->txt("skills");
    }
}
