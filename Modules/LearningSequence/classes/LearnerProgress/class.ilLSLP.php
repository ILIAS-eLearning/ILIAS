<?php

/**
<<<<<<< HEAD
=======
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

declare(strict_types=1);

/**
>>>>>>> 5ae4bd192a1... LSO: 34712, return LP_MODE_COLLECTION in LP defaults
 * lp connector
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 */
class ilLSLP extends ilObjectLP
{
    public static function getDefaultModes($a_lp_active)
    {
        if (!$a_lp_active) {
            return [
                ilLPObjSettings::LP_MODE_DEACTIVATED,
            ];
        }
        return [
            ilLPObjSettings::LP_MODE_DEACTIVATED,
            ilLPObjSettings::LP_MODE_COLLECTION
        ];
    }

    public function getDefaultMode()
    {
        return ilLPObjSettings::LP_MODE_DEACTIVATED;
    }

    public function getValidModes()
    {
        return array(
            ilLPObjSettings::LP_MODE_DEACTIVATED,
            ilLPObjSettings::LP_MODE_COLLECTION
        );
    }
}
