<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once 'Services/Tracking/classes/class.ilLPObjSettings.php';

/**
* Class ilLPStatusFactory
* Creates status class instances for learning progress modes of an object.
* E.g obj_id of course returns an instance of ilLPStatusManual, ilLPStatusObjectives ...
*
* @author Stefan Meyer <meyer@leifos.com>
*
* @version $Id$
*
* @ingroup ServicesTracking
*
*/
class ilLPStatusFactory
{
    private static $class_by_obj_id = array();
    
    public static function _getClassById($a_obj_id, $a_mode = null)
    {
        if ($a_mode === null) {
            include_once 'Services/Object/classes/class.ilObjectLP.php';
            $olp = ilObjectLP::getInstance($a_obj_id);
            $a_mode = $olp->getCurrentMode();
            
            // please keep the cache in this if-block, otherwise default values
            // will not trigger the include_once calls
            if (isset(self::$class_by_obj_id[$a_obj_id])) {
                return self::$class_by_obj_id[$a_obj_id];
            }
        }

        $map = ilLPObjSettings::getClassMap();
        
        if (array_key_exists($a_mode, $map)) {
            $class = $map[$a_mode];
                        
            // undefined? try object lp directly
            if ($class === null) {
                include_once 'Services/Object/classes/class.ilObjectLP.php';
                $olp = ilObjectLP::getInstance($a_obj_id);
                $mode = $olp->getCurrentMode();
                if ($mode != ilLPObjSettings::LP_MODE_UNDEFINED) {
                    return self::_getClassById($a_obj_id, $mode);
                }
            } else {
                self::includeClass($class);
                self::$class_by_obj_id[$a_obj_id] = $class;
                return $class;
            }
        }

        // we probably can do better
        echo "ilLPStatusFactory: unknown type " . $a_mode;
        exit;
    }
    
    protected static function includeClass($a_class)
    {
        $path = ($a_class == 'ilLPStatus')
            ? 'Services/Tracking/classes/'
            : 'Services/Tracking/classes/status/';
        include_once $path . 'class.' . $a_class . '.php';
    }

    public static function _getClassByIdAndType($a_obj_id, $a_type)
    {
        // id is ignored in the moment
        switch ($a_type) {
            case 'event':
                self::includeClass('ilLPStatusEvent');
                return 'ilLPStatusEvent';

            default:
                echo "ilLPStatusFactory: unknown type: " . $a_type;
                exit;
        }
    }

    public static function _getInstance($a_obj_id, $a_mode = null)
    {
        if ($a_mode === null) {
            include_once 'Services/Object/classes/class.ilObjectLP.php';
            $olp = ilObjectLP::getInstance($a_obj_id);
            $a_mode = $olp->getCurrentMode();
        }
        
        $map = ilLPObjSettings::getClassMap();
        
        if (array_key_exists($a_mode, $map)) {
            $class = $map[$a_mode];
                        
            // undefined? try object lp directly
            if ($class === null) {
                include_once 'Services/Object/classes/class.ilObjectLP.php';
                $olp = ilObjectLP::getInstance($a_obj_id);
                $mode = $olp->getCurrentMode();
                if ($mode != ilLPObjSettings::LP_MODE_UNDEFINED) {
                    return self::_getInstance($a_obj_id, $mode);
                }
            } else {
                self::includeClass($class);
                return new $class($a_obj_id);
            }
        }
        
        // we probably can do better
        echo "ilLPStatusFactory: unknown type " . $a_mode;
        exit;
    }
}
