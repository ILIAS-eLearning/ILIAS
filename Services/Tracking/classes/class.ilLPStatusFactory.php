<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once 'Services/Tracking/classes/class.ilLPObjSettings.php';

/**
 * Class ilLPStatusFactory
 * Creates status class instances for learning progress modes of an object.
 * E.g obj_id of course returns an instance of ilLPStatusManual, ilLPStatusObjectives ...
 * @author  Stefan Meyer <meyer@leifos.com>
 * @ingroup ServicesTracking
 */
class ilLPStatusFactory
{
    /**
     * @var ilLPStatusFactory
     */
    private static $instance;

    /**
     * @var ilLogger
     */
    private  $logger;



    private static $class_by_obj_id = array();

    /**
     * @return ilLPStatusFactory
     */
    private static function getFactoryInstance() : ilLPStatusFactory
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;

    }

    /**
     * ilLPStatusFactory constructor.
     */
    private function __construct()
    {
        global $DIC;

        $this->logger = $DIC->logger()->trac();
    }

    /**
     * @return ilLogger
     */
    private function getLogger()
    {
        return $this->logger;
    }

    /**
     * @param      $a_obj_id
     * @param null $a_mode
     * @return mixed
     * @throws ilInvalidLPStatusException
     */
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

        $factory = self::getFactoryInstance();
        $message = 'Unknown LP mode given: ' . $a_mode;
        $factory->getLogger()->logStack(ilLogLevel::ERROR, $message);
        throw new ilInvalidLPStatusException($message);
    }
    
    protected static function includeClass($a_class)
    {
        $path = ($a_class == 'ilLPStatus')
            ? 'Services/Tracking/classes/'
            : 'Services/Tracking/classes/status/';
        include_once $path . 'class.' . $a_class . '.php';
    }

    /**
     * @param $a_obj_id
     * @param $a_type
     * @return string
     * @throws ilInvalidLPStatusException
     */
    public static function _getClassByIdAndType($a_obj_id, $a_type)
    {
        // id is ignored in the moment
        switch ($a_type) {
            case 'event':
                self::includeClass('ilLPStatusEvent');
                return 'ilLPStatusEvent';

            default:
                $factory = self::getFactoryInstance();
                $message = 'Unknown LP type given: ' . $a_type;
                $factory->getLogger()->logStack(ilLogLevel::ERROR, $message);
                throw new ilInvalidLPStatusException($message);
        }
    }

    /**
     * @param      $a_obj_id
     * @param null $a_mode
     * @return mixed
     * @throws ilInvalidLPStatusException
     */
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

        $factory = self::getFactoryInstance();
        $message = 'Unknown LP mode given: ' . $a_mode;
        $factory->getLogger()->logStack(ilLogLevel::ERROR, $message);
        throw new ilInvalidLPStatusException($message);
    }
}
