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
 * Learning history provider: First Login
 * @author Alexander Killing <killing@leifos.de>
 */
class ilFirstLoginLearningHistoryProvider extends ilAbstractLearningHistoryProvider implements ilLearningHistoryProviderInterface
{
    public function isActive() : bool
    {
        return true;
    }

    public function getEntries(int $ts_start, int $ts_end) : array
    {
        $entries = [];
        $ts = ilObjUser::_lookupFirstLogin($this->getUserId());
        if ($ts !== "") {
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

    public function getName() : string
    {
        $lng = $this->getLanguage();
        $lng->loadLanguageModule("lhist");

        return $lng->txt("lhist_first_login");
    }
}
