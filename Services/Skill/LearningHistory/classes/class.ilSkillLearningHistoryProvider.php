<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Learning history provider: Skills
 *
 * @author killing@leifos.de
 * @ingroup ServicesSkill
 */
class ilSkillLearningHistoryProvider extends ilAbstractLearningHistoryProvider implements ilLearningHistoryProviderInterface
{

    /**
     * @inheritdoc
     */
    public function isActive()
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
    public function getEntries($ts_start, $ts_end)
    {
        $lng = $this->getLanguage();
        $lng->loadLanguageModule("skll");
        $from = new ilDateTime($ts_start, IL_CAL_UNIX);
        $to = new ilDateTime($ts_end, IL_CAL_UNIX);

        // achievements
        $completions = ilBasicSkill::getNewAchievementsPerUser($from->get(IL_CAL_DATETIME), $to->get(IL_CAL_DATETIME), $this->getUserId());

        $entries = [];
        if (is_array($completions[$this->getUserId()])) {
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

        if (is_array($completions[$this->getUserId()])) {
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
        return $entries;
    }

    /**
     * @inheritdoc
     */
    public function getName() : string
    {
        $lng = $this->getLanguage();
        $lng->loadLanguageModule("skmg");

        return $lng->txt("skills");
    }
}
