<?php declare(strict_types=0);

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilLPStatusFactory
 * Creates status class instances for learning progress modes of an object.
 * E.g obj_id of course returns an instance of ilLPStatusManual, ilLPStatusObjectives ...
 * @author  Stefan Meyer <meyer@leifos.com>
 * @ingroup ServicesTracking
 */
class ilLPStatusFactory
{
    private static self $instance;
    private static array $class_by_obj_id = array();

    private ilLogger $logger;

    private static function getFactoryInstance() : ilLPStatusFactory
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        global $DIC;

        $this->logger = $DIC->logger()->trac();
    }

    private function getLogger() : ilLogger
    {
        return $this->logger;
    }

    public static function _getClassById(
        int $a_obj_id,
        ?int $a_mode = null
    ) : string {
        if ($a_mode === null) {
            $olp = ilObjectLP::getInstance($a_obj_id);
            $a_mode = $olp->getCurrentMode();

            // please keep the cache in this if-block, otherwise default values
            if (isset(self::$class_by_obj_id[$a_obj_id])) {
                return self::$class_by_obj_id[$a_obj_id];
            }
        }

        $map = ilLPObjSettings::getClassMap();

        if (array_key_exists($a_mode, $map)) {
            $class = $map[$a_mode];

            // undefined? try object lp directly
            if ($class === null) {
                $olp = ilObjectLP::getInstance($a_obj_id);
                $mode = $olp->getCurrentMode();
                if ($mode != ilLPObjSettings::LP_MODE_UNDEFINED) {
                    return self::_getClassById($a_obj_id, $mode);
                }
            } else {
                self::$class_by_obj_id[$a_obj_id] = $class;
                return $class;
            }
        }

        $factory = self::getFactoryInstance();
        $message = 'Unknown LP mode given: ' . $a_mode;
        $factory->getLogger()->logStack(ilLogLevel::ERROR, $message);
        throw new ilInvalidLPStatusException($message);
    }

    public static function _getClassByIdAndType(
        int $a_obj_id,
        string $a_type
    ) : string {
        // id is ignored in the moment
        switch ($a_type) {
            case 'event':
                return 'ilLPStatusEvent';

            default:
                $factory = self::getFactoryInstance();
                $message = 'Unknown LP type given: ' . $a_type;
                $factory->getLogger()->logStack(ilLogLevel::ERROR, $message);
                throw new ilInvalidLPStatusException($message);
        }
    }

    public static function _getInstance(
        int $a_obj_id,
        ?int $a_mode = null
    ) : ilLPStatus {
        if ($a_mode === null) {
            $olp = ilObjectLP::getInstance($a_obj_id);
            $a_mode = $olp->getCurrentMode();
        }

        $map = ilLPObjSettings::getClassMap();

        if (array_key_exists($a_mode, $map)) {
            $class = $map[$a_mode];

            // undefined? try object lp directly
            if ($class === null) {
                $olp = ilObjectLP::getInstance($a_obj_id);
                $mode = $olp->getCurrentMode();
                if ($mode != ilLPObjSettings::LP_MODE_UNDEFINED) {
                    return self::_getInstance($a_obj_id, $mode);
                }
            } else {
                return new $class($a_obj_id);
            }
        }

        $factory = self::getFactoryInstance();
        $message = 'Unknown LP mode given: ' . $a_mode;
        $factory->getLogger()->logStack(ilLogLevel::ERROR, $message);
        throw new ilInvalidLPStatusException($message);
    }
}
