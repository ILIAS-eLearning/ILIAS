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

declare(strict_types=1);

use ILIAS\DI\Container;
use ILIAS\Tracking\DB\Factory as TrackingDBFactory;
use ILIAS\Tracking\DB\FactoryInterface as TrackingDBFactoryInterface;

class ilLPStatusCourseReference extends ilLPStatus
{
    protected const string LNG_TEXT = 'trac_mode_course_reference';
    protected const string LNG_TEXT_INFO = 'trac_mode_course_reference_info';
    protected ilLanguage $lng;

    /**
     * @var ilLPStatusCourseReference[]
     */
    private static array $instances = [];
    private int $target_obj_id = 0;
    private array $status_info = [];
    protected TrackingDBFactoryInterface $db_factory;

    public function __construct(int $a_obj_id)
    {
        global $DIC;
        $this->db_factory = new TrackingDBFactory($DIC->database());
        parent::__construct($a_obj_id);
        $this->readTargetObjId($a_obj_id);
        $this->readStatusInfo($a_obj_id);
    }

    public static function _getCountNotAttempted(int $a_obj_id): int
    {
        $self = self::getInstanceByObjId($a_obj_id);
        return count($self->getNotAttempted());
    }

    public static function _getNotAttempted(int $a_obj_id): array
    {
        $self = self::getInstanceByObjId($a_obj_id);
        return $self->getNotAttempted();
    }

    /**
     * @return int[]
     */
    public function getNotAttempted(): array
    {
        return $this->status_info[\ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM];
    }

    public static function _getCountInProgress(int $a_obj_id): int
    {
        $self = self::getInstanceByObjId($a_obj_id);
        return count($self->getInProgress());
    }

    public static function _getInProgress(int $a_obj_id): array
    {
        $self = self::getInstanceByObjId($a_obj_id);
        return $self->getInProgress();
    }

    /**
     * @return int[]
     */
    public function getInProgress(): array
    {
        return $this->status_info[\ilLPStatus::LP_STATUS_IN_PROGRESS_NUM];
    }

    public static function _getCountCompleted(int $a_obj_id): int
    {
        $self = self::getInstanceByObjId($a_obj_id);
        return count($self->getCompleted());
    }

    public static function _getCompleted(int $a_obj_id): array
    {
        $self = self::getInstanceByObjId($a_obj_id);
        return $self->getCompleted();
    }

    /**
     * @return int[]
     */
    public function getCompleted(): array
    {
        return $this->status_info[\ilLPStatus::LP_STATUS_COMPLETED_NUM];
    }

    public static function _getStatusInfo(int $a_obj_id): array
    {
        $self = self::getInstanceByObjId($a_obj_id);
        return $self->getStatusInfo();
    }

    public function getStatusInfo(): array
    {
        return $this->status_info;
    }

    public function readStatusInfo(int $a_obj_id): void
    {
        $collection = $this->db_factory->lpMarks()->repository()->readAllEntriesOfObject($this->target_obj_id);
        $info = [
            ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM => [],
            ilLPStatus::LP_STATUS_IN_PROGRESS_NUM => [],
            ilLPStatus::LP_STATUS_COMPLETED_NUM => [],
            ilLPStatus::LP_STATUS_FAILED_NUM => []
        ];
        foreach ($collection as $lp_mark) {
            if (array_key_exists($lp_mark->getStatus(), $info)) {
                $info[$lp_mark->getStatus()][] = $lp_mark->getUserId();
            }
        }
        $this->status_info = $info;
    }

    public function determineStatus(
        int $a_obj_id,
        int $a_usr_id,
        ?object $a_obj = null
    ): int {
        $status = \ilLPStatus::_lookupStatus(
            $this->target_obj_id,
            $a_usr_id,
            false
        );
        if ($status) {
            return $status;
        }
        return \ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM;
    }

    private static function getInstanceByObjId(
        int $a_reference_obj_id
    ): ilLPStatusCourseReference {
        if (!isset(self::$instances[$a_reference_obj_id])) {
            self::$instances[$a_reference_obj_id] = new self(
                $a_reference_obj_id
            );
        }
        return self::$instances[$a_reference_obj_id];
    }

    private function readTargetObjId(int $a_obj_id): void
    {
        $this->target_obj_id = ilObject::_lookupObjId(
            (int) ilObjCourseReference::_lookupTargetRefId($a_obj_id)
        );
    }

    public function init(
        Container $DIC
    ): void {
        $this->lng = $DIC->language();
    }

    public function getLPStatusId(): string
    {
        return (string) ilLPObjSettings::LP_MODE_COURSE_REFERENCE;
    }

    public function getLabel(): string
    {
        return $this->lng->txt(self::LNG_TEXT);
    }

    public function getInfo(): string
    {
        return $this->lng->txt(self::LNG_TEXT_INFO);
    }
}
