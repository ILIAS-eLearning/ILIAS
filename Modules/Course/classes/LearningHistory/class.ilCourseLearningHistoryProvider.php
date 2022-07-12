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
 *********************************************************************/
 
/**
 * Learning history provider: Course learning objectives
 * @author  killing@leifos.de
 * @ingroup ServicesTracking
 */
class ilCourseLearningHistoryProvider extends ilAbstractLearningHistoryProvider implements ilLearningHistoryProviderInterface
{

    /**
     * @inheritdoc
     */
    public function isActive() : bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function getEntries(int $ts_start, int $ts_end) : array
    {
        $lng = $this->getLanguage();
        $lng->loadLanguageModule("crs");
        $completions = ilLOUserResults::getCompletionsOfUser($this->getUserId(), $ts_start, $ts_end);

        $entries = [];
        foreach ($completions as $c) {
            $text = str_replace(
                "$3$",
                $this->getEmphasizedTitle($c["title"]),
                $lng->txt("crs_lhist_objective_completed")
            );
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
