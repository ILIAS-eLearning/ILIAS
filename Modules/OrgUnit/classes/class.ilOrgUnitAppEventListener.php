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
 ********************************************************************
 */

/**
 * Class ilOrgUnitAppEventListener
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilOrgUnitAppEventListener
{
    protected static array $ref_ids = [];

    /**
     * Handle an event in a listener.
     * @param string $a_component component, e.g. "Modules/Forum" or "Services/User"
     * @param string $a_event     event e.g. "createUser", "updateUser", "deleteUser", ...
     * @param array  $a_parameter parameter array (assoc), array("name" => ..., "phone_office" => ...)
     */
    public static function handleEvent(string $a_component, string $a_event, array $a_parameter) : void
    {
        switch ($a_component) {
            case 'Services/Tree':
                switch ($a_event) {
                    case 'moveTree':
                        $moved_ref_id = $a_parameter['source_id'];
                        if (ilObject2::_lookupType($moved_ref_id, true) == 'orgu') {
                            self::rebuildOrguPathRecurvice($moved_ref_id);
                        }

                        break;
                }
                break;
        }
    }

    protected static function rebuildOrguPathRecurvice(int $ref_id): void
    {
        global $DIC;
        $tree = $DIC['tree'];
        /**
         * @var $tree ilTree
         */
        ilOrgUnitPathStorage::writePathByRefId($ref_id);
        foreach ($tree->getChildsByType($ref_id, 'orgu') as $item) {
            self::rebuildOrguPathRecurvice($item['ref_id']);
        }
    }
}
