<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Learning history provider: Course learning objectives
 *
 * @author killing@leifos.de
 * @ingroup ServicesTracking
 */
class ilCourseLearningHistoryProvider extends ilAbstractLearningHistoryProvider implements ilLearningHistoryProviderInterface
{

    /**
     * @inheritdoc
     */
    public function isActive()
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function getEntries($ts_start, $ts_end)
    {
        $lng = $this->getLanguage();
        $lng->loadLanguageModule("crs");
        $completions = ilLOUserResults::getCompletionsOfUser($this->getUserId(), $ts_start, $ts_end);

        $entries = [];
        foreach ($completions as $c) {
            $text = str_replace("$3$", $this->getEmphasizedTitle($c["title"]), $lng->txt("crs_lhist_objective_completed"));
            $entries[] = $this->getFactory()->entry(
                $text,
                $text,
                ilUtil::getImagePath("icon_obj.svg"),
                $c["tstamp"],
                $c["course_id"]
            );
        }
        return $entries;
    }

    /**
     * @inheritdoc
     */
    public function getName() : string
    {
        $lng = $this->getLanguage();
        $lng->loadLanguageModule("crs");

        return $lng->txt("crs_objectives");
    }
}
