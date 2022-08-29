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
 * Mediacast to lp connector
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilMediaCastLP extends ilObjectLP
{
    public static function getDefaultModes(bool $a_lp_active): array
    {
        return array(
            ilLPObjSettings::LP_MODE_DEACTIVATED
        );
    }

    public function getDefaultMode(): int
    {
        return ilLPObjSettings::LP_MODE_DEACTIVATED;
    }

    public function getValidModes(): array
    {
        return array(
            ilLPObjSettings::LP_MODE_DEACTIVATED,
            ilLPObjSettings::LP_MODE_COLLECTION_MOBS
        );
    }
}
