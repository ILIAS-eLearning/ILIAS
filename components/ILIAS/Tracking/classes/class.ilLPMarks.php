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

use ILIAS\Tracking\DB\LPMarks\Element\LPMarkInterface;
use ILIAS\Tracking\Factory as TrackingFactory;
use ILIAS\Tracking\FactoryInterface as TrackingFactoryInterface;

/**
 * Class ilLPMarks
 * @author  Stefan Meyer <meyer@leifos.com>
 * @version $Id$
 * @package ilias-tracking
 */
class ilLPMarks
{
    protected TrackingFactoryInterface $tracking_factory;
    protected LPMarkInterface $lp_mark;

    protected int $obj_id;
    protected int $usr_id;
    protected ?string $obj_type;

    protected bool $completed = false;
    protected string $comment = '';
    protected string $mark = '';
    protected string $status_changed = '';

    public function __construct(
        int $a_obj_id,
        int $a_usr_id
    ) {
        global $DIC;
        $this->tracking_factory = new TrackingFactory();
        $this->lp_mark = $this->tracking_factory->db()->lpMarks()->element()->lpMark()
            ->withObjectId($a_obj_id)
            ->withUserId($a_usr_id);

        $ilObjectDataCache = $DIC['ilObjDataCache'];
        $this->obj_type = $ilObjectDataCache->lookupType($this->lp_mark->getObjectId());

        $this->__read();
    }

    public static function deleteObject(
        int $a_obj_id
    ): void {
        (new TrackingFactory())->db()->lpMarks()->repository()->delete($a_obj_id);
    }

    public function getUserId(): int
    {
        return $this->lp_mark->getUserId();
    }

    public function setMark(
        string $a_mark
    ): void {
        $this->lp_mark = $this->lp_mark
            ->withMark($a_mark);
    }

    public function getMark(): string
    {
        return $this->mark;
    }

    public function setComment(
        string $a_comment
    ): void {
        $this->lp_mark = $this->lp_mark
            ->withComment($a_comment);
    }

    public function getComment(): string
    {
        return $this->comment;
    }

    public function setCompleted(
        bool $a_status
    ): void {
        $this->lp_mark = $this->lp_mark
            ->withCompletedStatus($a_status);
    }

    public function getCompleted(): bool
    {
        return $this->lp_mark->isCompleted();
    }

    public function getStatusChanged(): string
    {
        return $this->status_changed;
    }

    public function getObjId(): int
    {
        return $this->obj_id;
    }

    public function update(): void
    {
        $this->tracking_factory->db()->lpMarks()->repository()->write($this->lp_mark);
    }

    public static function _hasCompleted(
        int $a_usr_id,
        int $a_obj_id
    ): bool {
        return (new TrackingFactory())->db()->lpMarks()->repository()->readEntryForUserOfObject(
            $a_obj_id,
            $a_usr_id
        )->isCompleted();
    }

    public static function getCompletionsOfUser(
        int $user_id,
        string $from,
        string $to
    ): array {
        $collection = (new TrackingFactory())->db()->lpMarks()->repository()->readByUserIdAndStatusAndTimeInterval(
            $user_id,
            ilLPStatus::LP_STATUS_COMPLETED_NUM,
            $from,
            $to
        );
        return $collection->asDataArray();
    }

    public static function _lookupMark(
        int $a_usr_id,
        int $a_obj_id
    ): string {
        $lp_mark = (new TrackingFactory())->db()->lpMarks()->repository()->readEntryForUserOfObject(
            $a_obj_id,
            $a_usr_id
        );
        return is_null($lp_mark) ? '' : (string) $lp_mark->getMark();
    }

    public static function _lookupComment(
        int $a_usr_id,
        int $a_obj_id
    ): string {
        $lp_mark = (new TrackingFactory())->db()->lpMarks()->repository()->readEntryForUserOfObject(
            $a_obj_id,
            $a_usr_id
        );
        return is_null($lp_mark) ? '' : (string) $lp_mark->getComment();
    }

    public function __read(): bool
    {
        $new_lp_mark = $this->tracking_factory->db()->lpMarks()->repository()->readEntryForUserOfObject(
            $this->lp_mark->getObjectId(),
            $this->lp_mark->getUserId()
        );
        if (is_null($new_lp_mark)) {
            return false;
        }
        $this->lp_mark = $new_lp_mark;
        return true;
    }

    public static function _deleteForUsers(
        int $a_obj_id,
        array $a_user_ids
    ): void {
        (new TrackingFactory())->db()->lpMarks()->repository()->deleteByUserIds(
            $a_obj_id,
            ...$a_user_ids
        );
    }

    public static function _getAllUserIds(int $a_obj_id): array
    {
        $collection = (new TrackingFactory())->db()->lpMarks()->repository()->readAllEntriesOfObject($a_obj_id);
        return $collection->asUserIdArray();
    }
}
