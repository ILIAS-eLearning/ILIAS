<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Learning history provider: First Login
 *
 * @author killing@leifos.de
 * @ingroup ServicesTracking
 */
class ilFirstLoginLearningHistoryProvider extends ilAbstractLearningHistoryProvider implements ilLearningHistoryProviderInterface
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
        $entries = [];
        $ts = ilObjUser::_lookupFirstLogin($this->getUserId());
        if ($ts != "") {
            $ts = new ilDateTime($ts, IL_CAL_DATETIME);
            $ts = $ts->get(IL_CAL_UNIX);

            $lng = $this->getLanguage();
            $lng->loadLanguageModule("lhist");

            $text1 = $lng->txt("lhist_first_login");
            $entries[] = $this->getFactory()->entry(
                $text1,
                $text1,
                ilUtil::getImagePath("icon_rate_on_user.svg"),
                $ts,
                0
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
        $lng->loadLanguageModule("lhist");

        return $lng->txt("lhist_first_login");
    }
}
