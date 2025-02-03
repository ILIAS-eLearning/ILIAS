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
 * Learning history provider: completed lp objects
 * @author  killing@leifos.de
 * @ingroup ServicesTracking
 */
class ilTrackingLearningHistoryProvider extends ilAbstractLearningHistoryProvider implements ilLearningHistoryProviderInterface
{
    /**
     * @inheritdoc
     */
    public function isActive(): bool
    {
        if (ilObjUserTracking::_enabledLearningProgress() &&
            ilObjUserTracking::_hasLearningProgressLearner()) {
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
        $this->lng->loadLanguageModule("trac");
        $from = new ilDateTime($ts_start, IL_CAL_UNIX);
        $to = new ilDateTime($ts_end, IL_CAL_UNIX);
        $completions = ilLPMarks::getCompletionsOfUser(
            $this->getUserId(),
            $from->get(IL_CAL_DATETIME),
            $to->get(IL_CAL_DATETIME)
        );
        $entries = [];
        foreach ($completions as $c) {
            $ts = new ilDateTime($c["status_changed"], IL_CAL_DATETIME);
            $in_txt = ilObject::_lookupType((int) $c["obj_id"]) === "crs"
                ? $this->lng->txt("trac_lhist_obj_completed")
                : $this->lng->txt("trac_lhist_obj_completed_in");
            $entries[] = $this->getFactory()->entry(
                $this->lng->txt("trac_lhist_obj_completed"),
                $in_txt,
                ilObject::_getIcon((int) $c["obj_id"]),
                $ts->get(IL_CAL_UNIX),
                $c["obj_id"]
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
        $this->lng->loadLanguageModule("lp");

        return $this->lng->txt("learning_progress");
    }
}
