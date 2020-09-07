<?php

/**
 * Class ilOrgUnitAppEventListener
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilOrgUnitAppEventListener
{
    protected static $ref_ids = array();


    /**
     * Handle an event in a listener.
     *
     * @param    string $a_component component, e.g. "Modules/Forum" or "Services/User"
     * @param    string $a_event     event e.g. "createUser", "updateUser", "deleteUser", ...
     * @param    array $a_parameter  parameter array (assoc), array("name" => ..., "phone_office" => ...)
     */
    public static function handleEvent($a_component, $a_event, $a_parameter)
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


    /**
     * @param $ref_id
     */
    protected static function rebuildOrguPathRecurvice($ref_id)
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
