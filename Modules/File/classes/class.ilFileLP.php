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
 * File to lp connector
 *
 * @author  Michael Jansen <mjansen@databay.de>
 * @package ModulesFile
 */
class ilFileLP extends ilObjectLP
{
    /**
     * @return int[]
     */
    public static function getDefaultModes(bool $a_lp_active): array
    {
        return array(
            ilLPObjSettings::LP_MODE_DEACTIVATED,
            ilLPObjSettings::LP_MODE_CONTENT_VISITED,
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
            ilLPObjSettings::LP_MODE_CONTENT_VISITED,
        );
    }
}
