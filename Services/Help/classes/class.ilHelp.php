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
 * Online help application class
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilHelp
{
    /**
     * @deprecated
     */
    public static function getObjCreationTooltipText(
        string $a_type
    ): string {
        global $DIC;
        return $DIC->help()->internal()->domain()->tooltips()->getTooltipPresentationText($a_type . "_create");
    }
}
