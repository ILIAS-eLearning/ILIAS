<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilLPStatusCourseReference
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilLPStatusCourseReference extends \ilLPStatus
{
    /**
     * @var \ilLPStatusCourseReference[]
     */
    private static $instances = [];

    /**
     * @var \ilLogger|null
     */
    private $logger = null;

    /**
     * @var int
     */
    private $target_obj_id = 0;

    private $status_info = [];


    /**
     * ilLPStatusCourseReference constructor.
     * @param int $a_obj_id
     */
    public function __construct($a_obj_id)
    {
        global $DIC;

        $this->logger = $DIC->logger()->trac();

        parent::__construct($a_obj_id);
        $this->readTargetObjId($a_obj_id);
        $this->readStatusInfo($a_obj_id);
    }

    /**
     * @inheritdoc
     */
    public static function _getCountNotAttempted($a_obj_id)
    {
        $self = self::getInstanceByObjId($a_obj_id);
        return count($self->getNotAttempted());
    }

    /**
     * @inheritdoc
     */
    public static function _getNotAttempted($a_obj_id)
    {
        $self = self::getInstanceByObjId($a_obj_id);
        return $self->getNotAttempted();
    }

    /**
     * @return int[]
     */
    public function getNotAttempted()
    {
        return $this->status_info[\ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM];
    }

    /**
     * @inheritdoc
     */
    public static function _getCountInProgress($a_obj_id)
    {
        $self = self::getInstanceByObjId($a_obj_id);
        return count($self->getInProgress());
    }

    /**
     * @inheritdoc
     */
    public static function _getInProgress($a_obj_id)
    {
        $self = self::getInstanceByObjId($a_obj_id);
        return $self->getInProgress();
    }

    /**
     * @return int[]
     */
    public function getInProgress()
    {
        return $this->status_info[\ilLPStatus::LP_STATUS_IN_PROGRESS_NUM];
    }

    /**
     * @inheritdoc
     */
    public static function _getCountCompleted($a_obj_id)
    {
        $self = self::getInstanceByObjId($a_obj_id);
        return count($self->getCompleted());
    }

    /**
     * @inheritdoc
     */
    public static function _getCompleted($a_obj_id)
    {
        $self = self::getInstanceByObjId($a_obj_id);
        return $self->getCompleted();
    }

    /**
     * @return int[]
     */
    public function getCompleted()
    {
        return $this->status_info[\ilLPStatus::LP_STATUS_COMPLETED_NUM];
    }


    /**
     * @inheritdoc
     */
    public static function _getStatusInfo($a_obj_id)
    {
        $self = self::getInstanceByObjId($a_obj_id);
        return $self->getStatusInfo();
    }

    public function getStatusInfo()
    {
        return $this->status_info;
    }

    /**
     * @inheritdoc
     */
    public function readStatusInfo($a_obj_id)
    {
        global $DIC;

        $database = $DIC->database();
        $query = 'select status,usr_id from ut_lp_marks ' .
            'where obj_id = ' . $database->quote($this->target_obj_id, \ilDBConstants::T_INTEGER);
        $res = $database->query($query);

        $info = [
            \ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM => [],
            \ilLPStatus::LP_STATUS_IN_PROGRESS_NUM => [],
            \ilLPStatus::LP_STATUS_COMPLETED_NUM => [],
            \ilLPStatus::LP_STATUS_FAILED_NUM => []
        ];
        while ($row = $res->fetchRow(\ilDBConstants::FETCHMODE_OBJECT)) {
            if (array_key_exists((int) $row->status, $info)) {
                $info[(int) $row->status][] = (int) $row->usr_id;
            }
        }
        $this->status_info = $info;
    }


    /**
     * @inheritdoc
     */
    public function determineStatus($a_obj_id, $a_usr_id, $a_obj = null)
    {
        $status = \ilLPStatus::_lookupStatus($this->target_obj_id, $a_usr_id, false);
        if ($status) {
            return $status;
        }
        return \ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM;
    }


    /**
     * @param $a_reference_obj_id
     * @return \ilLPStatusCourseReference
     */
    private static function getInstanceByObjId($a_reference_obj_id)
    {
        if (!isset(self::$instances[$a_reference_obj_id])) {
            self::$instances[$a_reference_obj_id] = new self($a_reference_obj_id);
        }
        return self::$instances[$a_reference_obj_id];
    }

    /**
     * @param $a_obj_id
     */
    private function readTargetObjId($a_obj_id)
    {
        $this->target_obj_id = ilObject::_lookupObjId(ilObjCourseReference::_lookupTargetRefId($a_obj_id));
    }
}
