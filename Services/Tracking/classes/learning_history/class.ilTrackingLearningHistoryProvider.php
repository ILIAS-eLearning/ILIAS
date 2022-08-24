<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

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
            $entries[] = $this->getFactory()->entry(
                $this->lng->txt("trac_lhist_obj_completed"),
                $this->lng->txt("trac_lhist_obj_completed_in"),
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
