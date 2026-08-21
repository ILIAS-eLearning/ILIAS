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

use ILIAS\Tracking\Factory as TrackingFactory;
use ILIAS\Tracking\Status\LPStatusInterface;

/**
 * Abstract class ilLPStatus for all learning progress modes
 * E.g  ilLPStatusManual, ilLPStatusObjectives ...
 * @author  Stefan Meyer <meyer@leifos.com>
 * @version $Id$
 * @ingroup ServicesTracking
 */
class ilLPStatus implements LPStatusInterface
{
    public const string LP_STATUS_NOT_ATTEMPTED = 'trac_no_attempted';
    public const string LP_STATUS_IN_PROGRESS = 'trac_in_progress';
    public const string LP_STATUS_COMPLETED = 'trac_completed';
    public const string LP_STATUS_FAILED = 'trac_failed';
    public const int LP_STATUS_NOT_ATTEMPTED_NUM = 0;
    public const int LP_STATUS_IN_PROGRESS_NUM = 1;
    public const int LP_STATUS_COMPLETED_NUM = 2;
    public const int LP_STATUS_FAILED_NUM = 3;
    public const string LP_STATUS_REGISTERED = 'trac_registered';
    public const string LP_STATUS_NOT_REGISTERED = 'trac_not_registered';
    public const string LP_ut_lp_markSTATUS_PARTICIPATED = 'trac_participated';
    public const string LP_STATUS_NOT_PARTICIPATED = 'trac_not_participated';

    public static array $list_gui_cache;

    protected int $obj_id;
    protected ilDBInterface $db;
    protected ilObjectDataCache $ilObjDataCache;

    public function __construct(int $a_obj_id)
    {
        global $DIC;
        $this->obj_id = $a_obj_id;
        $this->db = $DIC->database();
        $this->ilObjDataCache = $DIC['ilObjDataCache'];
    }

    public static function _getCountNotAttempted(
        int $a_obj_id
    ): int {
        return 0;
    }

    /**
     * @return int[]
     */
    public static function _getNotAttempted(
        int $a_obj_id
    ): array {
        return [];
    }

    public static function _getCountInProgress(
        int $a_obj_id
    ): int {
        return 0;
    }

    public static function _getInProgress(
        int $a_obj_id
    ): array {
        return [];
    }

    public static function _getCountCompleted(
        int $a_obj_id
    ): int {
        return 0;
    }

    /**
     * @return int[]
     */
    public static function _getCompleted(
        int $a_obj_id
    ): array {
        return [];
    }

    /**
     * @return int[]
     */
    public static function _getFailed(
        int $a_obj_id
    ): array {
        return [];
    }

    public static function _getCountFailed(
        int $a_obj_id
    ): int {
        return 0;
    }

    public static function _getStatusInfo(
        int $a_obj_id
    ): array {
        return [];
    }

    public static function _getTypicalLearningTime(
        string $type,
        int $obj_id,
        int $sub_id = 0
    ): int {
        global $DIC;
        $lom_services = $DIC->learningObjectMetadata();
        $paths = $lom_services->paths();
        $data_helper = $lom_services->dataHelper();
        $value = $lom_services->read($obj_id, $sub_id, $type, $paths->firstTypicalLearningTime())
                              ->firstData($paths->firstTypicalLearningTime())
                              ->value();
        return $data_helper->durationToSeconds($value);
    }

    /**
     * New status handling (st: status, nr: accesses, p: percentage, t: time spent, m: mark)
     * Learning progress:
     * - lm: ilLPStatusManual (st, nr, t, ok, p-, m-), ilLPStatusVisits (st, nr, p, t, ok, m-),
     *       ilLPStatusTypicalLearningTime (st, nr, p, t, ok, m-)
     * - dbk: ilLPStatusManual (st, nr, t ok, p-, m-)
     * - htlm: ilLPStatusManual (st, nr, t, m ok, p-) (but mark handling different than lm/dbk)
     * - crs: ilLPStatusManualByTutor (st ok), ilLPStatusObjectives (st ok), ilLPStatusCollection
     * - grp: ilLPStatusManualByTutor, ilLPStatusCollection
     * - fold: ilLPStatusCollection
     * - session: ilLPStatusEvent (st ok, nr and t only for infoscreen, comment and mark are not saved in learning progress table!)
     * - exercise: ilLPStatusExerciseReturned (st, nr, m ok, t-, p-)
     * - scorm: ilLPStatusSCORM (st, nr, p, t, m ok), ilLPStatusSCORMPackage (st, nr, t, m ok, p-)
     * - tst: ilLPStatusTestFinished (st, nr, t, p ok, mark not synced),
     *        ilLPStatusTestPassed (st, nr, t ok, p-, mark not synced)
     * Added determine Status to:
     * - ilLPStatusManual
     * - ilLPStatusVisits
     * - ilLPStatusTypicalLearningTime
     * - ilLPStatusManualByTutor
     * - ilLPStatusObjectives
     * - ilLPStatusCollection
     * - ilLPStatusEvent
     * - ilLPStatusExerciseReturned
     * - ilLPStatusSCORMPackage
     * - ilLPStatusTestFinished
     * - ilLPStatusTestPassed
     * Updating the status:
     * - ilLPStatus::setInProgressIfNotAttempted($a_obj_id, $a_user_id) added to:
     * -- ilLearningProgress->_tracProgress()
     * -- ilTestSession->saveToDb()
     * - ilChangeEvent::_recordReadEvent() added to:
     * -- ilObjSessionGUI->infoScreen()
     * - ilLearningProgress->_tracProgress() added to:
     * --
     * - ilLPStatusWrapper::_updateStatus($a_obj_id, $a_user_id); added to:
     * -- ilInfoScreenGUI->saveProgress()
     * -- ilLMPresentation->ilPage()
     * -- ilLPListOfObjectsGUI->updateUser()
     * -- ilCourseObjectiveResult->reset()
     * -- ilCourseObjectiveResult->__updatePassed()
     * -- ilEventParticipants->updateUser()
     * -- ilEventParticipants->_updateParticipation()
     * -- ilEventParticipants->_register()
     * -- ilEventParticipants->_unregister()
     * -- ilExerciseMembers->assignMember()
     * -- ilExerciseMembers->deassignMember()
     * -- ilExerciseMembers->ilClone()
     * -- ilExerciseMembers->writeStatus()
     * -- ilExerciseMembers->writeReturned()
     * -- ilSCORM13Player->writeGObjective()
     * -- ilObjSCORM2004LearningModule->deleteTrackingDataOfUsers()
     * -- ilObjSCORM2004LearningModule->importSuccess()
     * -- ilObjSCORM2004LearningModuleGUI->confirmedDeleteTracking()
     * -- ilSCORM13Player->removeCMIData()
     * -- ilSCORM13Player->setCMIData()
     * -- ilObjSCORMLearningModule->importSuccess()
     * -- ilObjSCORMLearningModule->importRaw()
     * -- ilObjSCORMLearningModuleGUI->confirmedDelete()
     * -- ilObjSCORMLearningModuleGUI->decreaseAttempt()
     * -- ilObjSCORMTracking->store()
     * -- ilObjSCORMTracking-> _insertTrackData()
     * -- ilSCORMPresentationGUI->increase_attemptAndsave_module_version()
     * -- ilTestScoringGUI->setPointsManual()
     * -- ilTestSession->increaseTestPass()
     * -- ilTestSession->saveToDb()
     * - ilLPStatusWrapper::_refreshStatus($a_ojb_id); aufgenommen in:
     * -- ilCourseObjective->add()
     * -- ilCourseObjective->delete()
     * -- ilCourseObjective->deleteAll()
     * -- ilExerciseMembers->delete()
     * -- ilSCORM13Package->removeCMIData()
     * -- ilAICCCourse->delete()
     * -- ilAICCUnit->delete()
     * -- ilObjAICCLearningModule->delete()
     * -- ilSCORMItem->delete()
     * -- ilLPStatusWrapper->update()
     * -- ilLPListOfSettingsGUI->assign()
     * -- ilLPListOfSettingsGUI->deassign()
     * -- ilLPListOfSettingsGUI->groupMaterials()
     * -- ilLPListOfSettingsGUI->releaseMaterials()
     * -- ilObjTestGUI->confirmDeleteAllUserResultsObject @TODO move to ilObjTest but this can ba called for each single question
     * -- ilConditionHandlerGUI->updateCondition()
     * - external time/access values for read events
     *   ilChangeEvent::_recordReadEvent($a_obj_id, $a_user_id, false, $attempts, $time);
     * -- ilObjSCORMTracking->_syncReadEvent in ilObjSCORMTracking->store() (add to refresh)
     * -- ilSCORM2004Tracking->_syncReadEvent in ilSCORM13Player->setCMIData()
     */

    public function _updateStatus(
        int $a_obj_id,
        int $a_usr_id,
        ?object $a_obj = null,
        bool $a_percentage = false,
        bool $a_force_raise = false
    ): void {
        $log = ilLoggerFactory::getLogger('trac');
        $log->debug(
            sprintf(
                "obj_id: %s, user id: %s, object: %s",
                $a_obj_id,
                $a_usr_id,
                (is_object($a_obj) ? get_class($a_obj) : 'null')
            )
        );
        $status = $this->determineStatus($a_obj_id, $a_usr_id, $a_obj);
        $percentage = $this->determinePercentage($a_obj_id, $a_usr_id, $a_obj);
        $old_status = ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM;
        $changed = self::writeStatus(
            $a_obj_id,
            $a_usr_id,
            $status,
            $percentage,
            false,
            $old_status
        );
        // ak: I don't think that this is a good way to fix 15529, we should not
        // raise the event, if the status does not change imo.
        // for now the changes in the next line just prevent the event being raised twice
        if (!$changed && $a_force_raise) { // #15529
            self::raiseEvent(
                $a_obj_id,
                $a_usr_id,
                $status,
                $old_status,
                $percentage
            );
        }
    }

    public function determinePercentage(
        int $a_obj_id,
        int $a_usr_id,
        ?object $a_obj = null
    ): int {
        return 0;
    }

    public function determineStatus(
        int $a_obj_id,
        int $a_usr_id,
        ?object $a_obj = null
    ): int {
        return 0;
    }

    /**
     * This function checks whether the status for a given number of users is dirty and must be
     * recalculated. "Missing" records are not inserted!
     * @param int $a_obj_id
     * @param ?int[] $a_users
     */
    public static function checkStatusForObject(
        int $a_obj_id,
        ?array $a_users = null
    ): void {
        //@todo: there maybe the need to add extra handling for sessions here, since the
        // "in progress" status is time dependent here. On the other hand, if they registered
        // to the session, they already accessed the course and should have a "in progress"
        // anyway. But the status on the session itself may not be correct.
        $valid_user_array = is_array($a_users) && count($a_users) > 0;
        $db_repository = (new TrackingFactory())->db()->lpMarks()->repository();
        $collection = $db_repository->readAllEntriesOfObject(
            $a_obj_id,
        )->getSubCollectionOfElementsByStatusDirty(1);
        if ($valid_user_array) {
            $collection = $collection->getSubCollectionOfElementsByUserIds(...$a_users);
        }
        $dirty = count($collection) > 0;
        // check if any records are missing
        $missing = false;
        if (!$dirty && $valid_user_array) {
            $collection = $db_repository->readAllEntriesOfObject($a_obj_id)->getSubCollectionOfElementsByUserIds(...$a_users);
            $missing = count($collection) < count($a_users);
        }
        // refresh status, if records are dirty or missing
        if ($dirty || $missing) {
            $trac_obj = ilLPStatusFactory::_getInstance($a_obj_id);
            $trac_obj->refreshStatus($a_obj_id, $a_users);
        }
    }

    protected static function raiseEvent(
        int $a_obj_id,
        int $a_usr_id,
        int $a_status,
        int $a_old_status,
        int $a_percentage
    ): void {
        global $DIC;

        $ilAppEventHandler = $DIC['ilAppEventHandler'];

        $log = ilLoggerFactory::getLogger('trac');
        $log->debug(
            "obj_id: " . $a_obj_id . ", user id: " . $a_usr_id . ", status: " .
            $a_status . ", percentage: " . $a_percentage
        );

        $ilAppEventHandler->raise(
            "components/ILIAS/Tracking",
            "updateStatus",
            array(
            "obj_id" => $a_obj_id,
            "usr_id" => $a_usr_id,
            "status" => $a_status,
            "old_status" => $a_old_status,
            "percentage" => $a_percentage
        )
        );
    }

    /**
     * Refresh status
     */
    public function refreshStatus(int $a_obj_id, ?array $a_users = null): void
    {
        $not_attempted = ilLPStatusWrapper::_getNotAttempted($a_obj_id);
        foreach ($not_attempted as $user_id) {
            $percentage = $this->determinePercentage($a_obj_id, $user_id);
            if (self::writeStatus(
                $a_obj_id,
                $user_id,
                self::LP_STATUS_NOT_ATTEMPTED_NUM,
                $percentage,
                true
            )) {
                //self::raiseEvent($a_obj_id, $user_id, self::LP_STATUS_NOT_ATTEMPTED_NUM, $percentage);
            }
        }
        $in_progress = ilLPStatusWrapper::_getInProgress($a_obj_id);
        foreach ($in_progress as $user_id) {
            $percentage = $this->determinePercentage($a_obj_id, $user_id);
            if (self::writeStatus(
                $a_obj_id,
                $user_id,
                self::LP_STATUS_IN_PROGRESS_NUM,
                $percentage,
                true
            )) {
                //self::raiseEvent($a_obj_id, $user_id, self::LP_STATUS_IN_PROGRESS_NUM, $percentage);
            }
        }
        $completed = ilLPStatusWrapper::_getCompleted($a_obj_id);
        foreach ($completed as $user_id) {
            $percentage = $this->determinePercentage($a_obj_id, $user_id);
            if (self::writeStatus(
                $a_obj_id,
                $user_id,
                self::LP_STATUS_COMPLETED_NUM,
                $percentage,
                true
            )) {
                //self::raiseEvent($a_obj_id, $user_id, self::LP_STATUS_COMPLETED_NUM, $percentage);
            }
        }
        $failed = ilLPStatusWrapper::_getFailed($a_obj_id);
        foreach ($failed as $user_id) {
            $percentage = $this->determinePercentage($a_obj_id, $user_id);
            if (self::writeStatus(
                $a_obj_id,
                $user_id,
                self::LP_STATUS_FAILED_NUM,
                $percentage,
                true
            )) {
                //self::raiseEvent($a_obj_id, $user_id, self::LP_STATUS_FAILED_NUM, $percentage);
            }
        }
        if ($a_users) {
            $missing_users = array_diff(
                $a_users,
                $not_attempted + $in_progress + $completed + $failed
            );
            if ($missing_users) {
                foreach ($missing_users as $user_id) {
                    ilLPStatusWrapper::_updateStatus($a_obj_id, $user_id);
                }
            }
        }
    }

    /**
     * Write status for user and object
     */
    public static function writeStatus(
        int $a_obj_id,
        int $a_user_id,
        int $a_status,
        int $a_percentage = 0,
        bool $a_force_per = false,
        ?int &$a_old_status = self::LP_STATUS_NOT_ATTEMPTED_NUM
    ): bool {
        global $DIC;
        $ilDB = $DIC->database();
        $log = $DIC->logger()->trac();
        $log->debug(
            'Write status for:  ' . "obj_id: " . $a_obj_id . ", user id: " . $a_user_id . ", status: " . $a_status . ", percentage: " . $a_percentage . ", force: " . $a_force_per
        );
        $update_dependencies = false;
        $db_repository = (new TrackingFactory())->db()->lpMarks()->repository();
        $db_element_factory = (new TrackingFactory())->db()->lpMarks()->element();
        $lp_mark_old = $db_repository->readEntryForUserOfObject($a_obj_id, $a_user_id);
        $lp_mark_new = $db_element_factory->lpMark()
            ->withStatus($a_status)
            ->withUserId($a_user_id)
            ->withObjectId($a_obj_id)
            ->withStatusDirty(0);
        $a_old_status = is_null($lp_mark_old) ? self::LP_STATUS_NOT_ATTEMPTED_NUM : $lp_mark_old->getStatus();
        if (
            is_null($lp_mark_old) ||
            $lp_mark_old->getStatus() != $a_status
        ) {
            $lp_mark_new = $lp_mark_new
                ->withStatusChanged(date("Y-m-d H:i:s"));
        }
        if (
            !is_null($lp_mark_old) &&
            $lp_mark_old->getStatus() === $a_status
        ) {
            $lp_mark_new = $lp_mark_new
                ->withStatusChanged($lp_mark_old->getStatusChanged());
        }
        if (
            $a_percentage ||
            $a_force_per
        ) {
            $a_percentage = max(0, $a_percentage);
            $a_percentage = min(100, $a_percentage);
            $lp_mark_new = $lp_mark_new
                ->withPercentage($a_percentage);
        }
        // update dependencies if new entry or the status has changed and rows are affected
        $affected_rows_count = $db_repository->write($lp_mark_new);
        if (
            is_null($lp_mark_old) ||
            ($affected_rows_count > 0 && $lp_mark_old->getStatus() != $a_status)
        ) {
            $update_dependencies = true;
        }

        $log->debug(
            'Update dependecies is ' . ($update_dependencies ? 'true' : 'false')
        );

        // update collections
        if ($update_dependencies) {
            $log->debug('update dependencies');

            // a change occured - remove existing cache entry
            ilLPStatusWrapper::_removeStatusCache($a_obj_id, $a_user_id);

            $set = $ilDB->query(
                "SELECT ut_lp_collections.obj_id obj_id FROM " .
                "object_reference JOIN ut_lp_collections ON " .
                "(object_reference.obj_id = " . $ilDB->quote(
                    $a_obj_id,
                    "integer"
                ) .
                " AND object_reference.ref_id = ut_lp_collections.item_id)"
            );
            while ($rec = $ilDB->fetchAssoc($set)) {
                if (in_array(
                    ilObject::_lookupType($rec["obj_id"]),
                    ["crs", "grp", "fold"]
                )) {
                    $log->debug(
                        'Calling update status for collection obj_id: ' . $rec['obj_id']
                    );
                    // just to make sure - remove existing cache entry
                    ilLPStatusWrapper::_removeStatusCache(
                        (int) $rec["obj_id"],
                        $a_user_id
                    );
                    ilLPStatusWrapper::_updateStatus(
                        (int) $rec["obj_id"],
                        $a_user_id
                    );
                }
            }

            // find all course references
            if (ilObject::_lookupType($a_obj_id) == 'crs') {
                $log->debug('update references');

                $query = 'select obj_id from container_reference ' .
                    'where target_obj_id = ' . $ilDB->quote(
                        $a_obj_id,
                        ilDBConstants::T_INTEGER
                    );
                $res = $ilDB->query($query);
                while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
                    $log->debug(
                        'Calling update status for reference obj_id: ' . $row->obj_id
                    );
                    \ilLPStatusWrapper::_removeStatusCache(
                        (int) $row->obj_id,
                        $a_user_id
                    );
                    \ilLPStatusWrapper::_updateStatus(
                        (int) $row->obj_id,
                        $a_user_id
                    );
                }
            }

            self::raiseEvent(
                $a_obj_id,
                $a_user_id,
                $a_status,
                $a_old_status,
                $a_percentage
            );
        }

        return $update_dependencies;
    }

    /**
     * This function shoudl be clalled for normal "read events".
     * The "in progress" status is only written,
     * if current status is "NOT ATTEMPTED"
     */
    public static function setInProgressIfNotAttempted(
        int $a_obj_id,
        int $a_user_id
    ): void {
        $lp_mark = (new TrackingFactory())->db()->lpMarks()->repository()->readEntryForUserOfObject($a_obj_id, $a_user_id);
        $needs_update = is_null($lp_mark) || $lp_mark->getStatus() === self::LP_STATUS_NOT_ATTEMPTED_NUM;
        if ($needs_update) {
            ilLPStatusWrapper::_updateStatus($a_obj_id, $a_user_id);
        }
    }

    /**
     * Sets all status to dirty. For testing puproses.
     */
    public static function setAllDirty(): void
    {
        (new TrackingFactory())->db()->lpMarks()->repository()->markAllRowsAsDirty();
    }

    /**
     * Sets status of an object to dirty.
     */
    public static function setDirty(int $a_obj_id): void
    {
        $db_repository = (new TrackingFactory())->db()->lpMarks()->repository();
        $collection = $db_repository->readAllEntriesOfObject($a_obj_id);
        $collection = $collection->withChangedStatusDirtyOfAllElements(1);
        $db_repository->writeCollection($collection);
    }

    /**
     * Lookup status
     */
    public static function _lookupStatus(
        int $a_obj_id,
        int $a_user_id,
        bool $a_create = true
    ): ?int {
        $db_repository = (new TrackingFactory())->db()->lpMarks()->repository();
        $lp_mark = $db_repository->readEntryForUserOfObject($a_obj_id, $a_user_id);
        if (
            !is_null($lp_mark) &&
            $lp_mark->getStatusDirty() === 0
        ) {
            return $lp_mark->getStatus();
        }
        if ($a_create) {
            ilLPStatusWrapper::_updateStatus($a_obj_id, $a_user_id);
            $lp_mark = $db_repository->readEntryForUserOfObject($a_obj_id, $a_user_id);
            if (
                !is_null($lp_mark) &&
                $lp_mark->getStatusDirty() === 0
            ) {
                return $lp_mark->getStatus();
            }
        }
        return null;
    }

    /**
     * Lookup percentage
     */
    public static function _lookupPercentage(
        int $a_obj_id,
        int $a_user_id
    ): ?int {
        $db_repository = (new TrackingFactory())->db()->lpMarks()->repository();
        $lp_mark = $db_repository->readEntryForUserOfObject($a_obj_id, $a_user_id);
        if (!is_null($lp_mark) && $lp_mark->getStatusDirty() === 0) {
            return $lp_mark->getPercentage();
        }
        return null;
    }

    /**
     * Lookup user object completion
     */
    public static function _hasUserCompleted(
        int $a_obj_id,
        int $a_user_id
    ): bool {
        return self::_lookupStatus(
            $a_obj_id,
            $a_user_id
        ) == self::LP_STATUS_COMPLETED_NUM;
    }

    /**
     * Lookup status changed
     */
    public static function _lookupStatusChanged(
        int $a_obj_id,
        int $a_user_id
    ): ?string {
        $db_repository = (new TrackingFactory())->db()->lpMarks()->repository();
        $lp_mark = $db_repository->readEntryForUserOfObject($a_obj_id, $a_user_id);
        if (!is_null($lp_mark) && $lp_mark->getStatusDirty() === 0) {
            return $lp_mark->getStatusChanged();
        }
        ilLPStatusWrapper::_updateStatus($a_obj_id, $a_user_id);
        $lp_mark = $db_repository->readEntryForUserOfObject($a_obj_id, $a_user_id);
        if (!is_null($lp_mark) && $lp_mark->getStatusDirty() === 0) {
            return $lp_mark->getStatusChanged();
        }
        return null;
    }

    /**
     * Get users with given status for object
     */
    protected static function _lookupStatusForObject(
        int $a_obj_id,
        int $a_status,
        ?array $a_user_ids = null
    ): array {
        $db_repository = (new TrackingFactory())->db()->lpMarks()->repository();
        $collection = $db_repository->readAllEntriesWithStatusOfObject($a_obj_id, $a_status);
        if (!is_null($a_user_ids) && count($a_user_ids) > 0) {
            $collection = $collection->getSubCollectionOfElementsByUserIds(...$a_user_ids);
        }
        foreach ($collection as $lp_mark) {
            // @fixme this was broken due to wrong $res['status_dirty'] access
            // check how to update status without recursion
            // check consequences of the old implementation
            if ($lp_mark->getStatusDirty()) {
                // update status and check again
                if (self::_lookupStatus($a_obj_id, $lp_mark->getUserId()) != $a_status) {
                    // update status: see comment
                }
            }
        }
        return $collection->asUserIdArray();
    }

    /**
     * Get completed users for object
     */
    public static function _lookupCompletedForObject(
        int $a_obj_id,
        ?array $a_user_ids = null
    ): array {
        return self::_lookupStatusForObject(
            $a_obj_id,
            self::LP_STATUS_COMPLETED_NUM,
            $a_user_ids
        );
    }

    /**
     * Get failed users for object
     */
    public static function _lookupFailedForObject(
        int $a_obj_id,
        ?array $a_user_ids = null
    ): array {
        return self::_lookupStatusForObject(
            $a_obj_id,
            self::LP_STATUS_FAILED_NUM,
            $a_user_ids
        );
    }

    /**
     * Get in progress users for object
     */
    public static function _lookupInProgressForObject(
        int $a_obj_id,
        ?array $a_user_ids = null
    ): array {
        return self::_lookupStatusForObject(
            $a_obj_id,
            self::LP_STATUS_IN_PROGRESS_NUM,
            $a_user_ids
        );
    }

    /**
     * Process given objects for lp-relevance
     */
    protected static function validateLPForObjects(
        int $a_user_id,
        array $a_obj_ids,
        int $a_parent_ref_id
    ): array {
        $lp_invalid = [];
        $memberships = ilObjectLP::getLPMemberships(
            $a_user_id,
            $a_obj_ids,
            $a_parent_ref_id
        );
        foreach ($memberships as $obj_id => $status) {
            if (!$status) {
                $lp_invalid[] = $obj_id;
            }
        }

        return array_diff($a_obj_ids, $lp_invalid);
    }

    /**implements
     * Process lp modes for given objects
     */
    protected static function checkLPModesForObjects(
        array $a_obj_ids,
        array &$a_coll_obj_ids
    ): array {
        $valid = [];
        // all lp modes with collections (gathered separately)
        $coll_modes = ilLPCollection::getCollectionModes();
        // check if objects have LP activated at all (DB entries)
        $existing = ilLPObjSettings::_lookupDBModeForObjects($a_obj_ids);
        foreach ($existing as $obj_id => $obj_mode) {
            if ($obj_mode != ilLPObjSettings::LP_MODE_DEACTIVATED) {
                $valid[$obj_id] = $obj_id;

                if (in_array($obj_mode, $coll_modes)) {
                    $a_coll_obj_ids[] = $obj_id;
                }
            }
        }

        // missing objects in DB (default mode)
        $existing_obj_ids = array_keys($existing);
        if (sizeof($existing) != sizeof($a_obj_ids)) {
            foreach (array_diff($a_obj_ids, $existing_obj_ids) as $obj_id) {
                $olp = ilObjectLP::getInstance($obj_id);
                $mode = $olp->getCurrentMode();
                if ($mode == ilLPObjSettings::LP_MODE_DEACTIVATED) {
                    // #11141
                    unset($valid[$obj_id]);
                } elseif ($mode != ilLPObjSettings::LP_MODE_UNDEFINED) {
                    $valid[$obj_id] = $obj_id;

                    if (in_array($mode, $coll_modes)) {
                        $a_coll_obj_ids[] = $obj_id;
                    }
                }
            }
            unset($existing);
        }
        return array_values($valid);
    }

    /**
     * Get LP status for given objects (and user)
     */
    protected static function getLPStatusForObjects(
        int $a_user_id,
        array $a_obj_ids
    ): array {
        $collection = (new TrackingFactory())->db()->lpMarks()->repository()->readEntriesForUserOfObjects(
            $a_user_id,
            ...$a_obj_ids
        );

        $res = [];
        foreach ($collection as $lp_mark) {
            if (!$lp_mark->getStatusDirty()) {
                $res[$lp_mark->getObjectId()] = $lp_mark->getStatus();
                continue;
            }
            $res[$lp_mark->getObjectId()] = self::_lookupStatus($lp_mark->getObjectId(), $lp_mark->getUserId());
        }

        // process missing user entries (same as dirty entries, see above)
        foreach ($a_obj_ids as $obj_id) {
            if (!isset($res[$obj_id])) {
                $res[$obj_id] = self::_lookupStatus($obj_id, $a_user_id);
                if ($res[$obj_id] === null) {
                    $res[$obj_id] = self::LP_STATUS_NOT_ATTEMPTED_NUM;
                }
            }
        }
        return $res;
    }

    public static function preloadListGUIData(array $a_obj_ids): void
    {
        global $DIC;
        $requested_ref_id = 0;
        if ($DIC->http()->wrapper()->query()->has('ref_id')) {
            $requested_ref_id = $DIC->http()->wrapper()->query()->retrieve(
                'ref_id',
                $DIC->refinery()->kindlyTo()->int()
            );
        }
        $ilUser = $DIC['ilUser'];
        $lng = $DIC['lng'];
        $user_id = $ilUser->getId();
        $res = [];
        if ($ilUser->getId() != ANONYMOUS_USER_ID &&
            ilObjUserTracking::_enabledLearningProgress() &&
            ilObjUserTracking::_hasLearningProgressLearner() && // #12042
            ilObjUserTracking::_hasLearningProgressListGUI()) {
            // -- validate
            // :TODO: we need the parent ref id, but this is awful
            // this step removes all "not attempted" from the list, which we usually do not want
            // $a_obj_ids = self::validateLPForObjects($user_id, $a_obj_ids, $requested_ref_id);
            // we are not handling the collections differently yet
            $coll_obj_ids = [];
            $a_obj_ids = self::checkLPModesForObjects(
                $a_obj_ids,
                $coll_obj_ids
            );
            // -- gather
            $res = self::getLPStatusForObjects($user_id, $a_obj_ids);
            // -- render
            // value to icon
            $lng->loadLanguageModule("trac");
            $icons = ilLPStatusIcons::getInstance(ilLPStatusIcons::ICON_VARIANT_LONG);
            foreach ($res as $obj_id => $status) {
                $res[$obj_id] = [
                    "image" => $icons->renderIconForStatus($status),
                    "status" => $status
                ];
            }
        }
        self::$list_gui_cache = $res;
    }

    public static function getListGUIStatus(
        int $a_obj_id,
        bool $a_image_only = true
    ): string|array {
        if ($a_image_only) {
            $image = '';
            if (isset(self::$list_gui_cache[$a_obj_id]["image"])) {
                $image = self::$list_gui_cache[$a_obj_id]["image"];
            }
            return $image;
        }
        return self::$list_gui_cache[$a_obj_id] ?? "";
    }

    public static function hasListGUIStatus(
        int $a_obj_id
    ): bool {
        if (isset(self::$list_gui_cache[$a_obj_id])) {
            return true;
        }
        return false;
    }

    public function init(\ILIAS\DI\Container $DIC): void
    {
        // TODO: Implement init() method.
    }

    public function getCustomLPSettingsExportXML(
        int $object_id
    ): SimpleXMLElement {
        return new SimpleXMLElement('<EmptyLPStatus></EmptyLPStatus>');
    }

    public function importCustomLPSettingsExportXML(
        int $new_object_id,
        ilImportMapping $a_mapping,
        SimpleXMLElement $additional_xml_root
    ): void {
        # Default implementation does nothing
    }

    public function getLPStatusId(): string
    {
        return (string) ilLPObjSettings::LP_MODE_UNDEFINED;
    }

    public function getLabel(): string
    {
        return '';
    }

    public function getInfo(): string
    {
        return '';
    }
}
