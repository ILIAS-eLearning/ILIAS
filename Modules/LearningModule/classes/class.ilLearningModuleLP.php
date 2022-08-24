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
 * LM to lp connector
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilLearningModuleLP extends ilObjectLP
{
    public static function getDefaultModes(bool $lp_active): array
    {
        if (!$lp_active) {
            return array(
                ilLPObjSettings::LP_MODE_DEACTIVATED,
                ilLPObjSettings::LP_MODE_QUESTIONS,
                ilLPObjSettings::LP_MODE_VISITED_PAGES
            );
        }

        return array(
            ilLPObjSettings::LP_MODE_DEACTIVATED,
            ilLPObjSettings::LP_MODE_MANUAL,
            ilLPObjSettings::LP_MODE_QUESTIONS,
            ilLPObjSettings::LP_MODE_VISITED_PAGES
        );
    }

    public function getDefaultMode(): int
    {
        return ilLPObjSettings::LP_MODE_MANUAL;
    }

    public function getValidModes(): array
    {
        return array(
            ilLPObjSettings::LP_MODE_DEACTIVATED,
            ilLPObjSettings::LP_MODE_MANUAL,
            ilLPObjSettings::LP_MODE_COLLECTION_MANUAL,
            ilLPObjSettings::LP_MODE_VISITS,
            ilLPObjSettings::LP_MODE_TLT,
            ilLPObjSettings::LP_MODE_COLLECTION_TLT,
            ilLPObjSettings::LP_MODE_QUESTIONS,
            ilLPObjSettings::LP_MODE_VISITED_PAGES
        );
    }
}
