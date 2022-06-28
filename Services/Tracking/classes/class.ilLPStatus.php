<?php declare(strict_types=0);
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Abstract class ilLPStatus for all learning progress modes
 * E.g  ilLPStatusManual, ilLPStatusObjectives ...
 * @author  Stefan Meyer <meyer@leifos.com>
 * @version $Id$
 * @ingroup ServicesTracking
 */
class ilLPStatus
{
    protected int $obj_id;

    protected ilDBInterface $db;
    protected ilObjectDataCache $ilObjDataCache;

    public static $list_gui_cache;

    public const LP_STATUS_NOT_ATTEMPTED = 'trac_no_attempted';
    public const LP_STATUS_IN_PROGRESS = 'trac_in_progress';
    public const LP_STATUS_COMPLETED = 'trac_completed';
    public const LP_STATUS_FAILED = 'trac_failed';

    public const LP_STATUS_NOT_ATTEMPTED_NUM = 0;
    public const LP_STATUS_IN_PROGRESS_NUM = 1;
    public const LP_STATUS_COMPLETED_NUM = 2;
    public const LP_STATUS_FAILED_NUM = 3;

    public const LP_STATUS_REGISTERED = 'trac_registered';
    public const LP_STATUS_NOT_REGISTERED = 'trac_not_registered';
    public const LP_STATUS_PARTICIPATED = 'trac_participated';
    public const LP_STATUS_NOT_PARTICIPATED = 'trac_not_participated';

    public function __construct(int $a_obj_id)
    {
        global $DIC;

        $this->obj_id = $a_obj_id;
        $this->db = $DIC->database();
        $this->ilObjDataCache = $DIC['ilObjDataCache'];
    }

    public static function _getCountNotAttempted(int $a_obj_id) : int
    {
        return 0;
    }

    /**
     * @param int $a_obj_id
     * @return int[]
     */
    public static function _getNotAttempted(int $a_obj_id) : array
    {
        return array();
    }

    public static function _getCountInProgress(int $a_obj_id) : int
    {
        return 0;
    }

    public static function _getInProgress(int $a_obj_id) : array
    {
        return array();
    }

    public static function _getCountCompleted(int $a_obj_id) : int
    {
        return 0;
    }

    /**
     * @param int $a_obj_id
     * @return int[]
     */
    public static function _getCompleted(int $a_obj_id) : array
    {
        return array();
    }

    /**
     * @param int $a_obj_id
     * @return int[]
     */
    public static function _getFailed(int $a_obj_id) : array
    {
        return array();
    }

    public static function _getCountFailed(int $a_obj_id) : int
    {
        return 0;
    }

    public static function _getStatusInfo(int $a_obj_id) : array
    {
        return array();
    }

    public static function _getTypicalLearningTime(int $a_obj_id) : int
    {
        return ilMDEducational::_getTypicalLearningTimeSeconds($a_obj_id);
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
    ) : void {
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
    ) : int {
        return 0;
    }

    public function determineStatus(
        int $a_obj_id,
        int $a_usr_id,
        object $a_obj = null
    ) : int {
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
    ) : void {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        //@todo: there maybe the need to add extra handling for sessions here, since the
        // "in progress" status is time dependent here. On the other hand, if they registered
        // to the session, they already accessed the course and should have a "in progress"
        // anyway. But the status on the session itself may not be correct.

        $sql = "SELECT usr_id FROM ut_lp_marks WHERE " .
            " obj_id = " . $ilDB->quote($a_obj_id, "integer") . " AND " .
            " status_dirty = " . $ilDB->quote(1, "integer");
        if (is_array($a_users) && count($a_users) > 0) {
            $sql .= " AND " . $ilDB->in("usr_id", $a_users, false, "integer");
        }
        $set = $ilDB->query($sql);
        $dirty = false;
        if ($rec = $ilDB->fetchAssoc($set)) {
            $dirty = true;
        }

        // check if any records are missing
        $missing = false;
        if (!$dirty && is_array($a_users) && count($a_users) > 0) {
            $set = $ilDB->query(
                "SELECT count(usr_id) cnt FROM ut_lp_marks WHERE " .
                " obj_id = " . $ilDB->quote($a_obj_id, "integer") . " AND " .
                $ilDB->in("usr_id", $a_users, false, "integer")
            );
            $r = $ilDB->fetchAssoc($set);
            if ($r["cnt"] < count($a_users)) {
                $missing = true;
            }
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
    ) : void {
        global $DIC;

        $ilAppEventHandler = $DIC['ilAppEventHandler'];

        $log = ilLoggerFactory::getLogger('trac');
        $log->debug(
            "obj_id: " . $a_obj_id . ", user id: " . $a_usr_id . ", status: " .
            $a_status . ", percentage: " . $a_percentage
        );

        $ilAppEventHandler->raise(
            "Services/Tracking",
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
    public function refreshStatus(int $a_obj_id, ?array $a_users = null) : void
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
    ) : bool {
        global $DIC;

        $ilDB = $DIC->database();
        $log = $DIC->logger()->trac();

        $log->debug(
            'Write status for:  ' . "obj_id: " . $a_obj_id . ", user id: " . $a_user_id . ", status: " . $a_status . ", percentage: " . $a_percentage . ", force: " . $a_force_per
        );
        $update_dependencies = false;

        $a_old_status = self::LP_STATUS_NOT_ATTEMPTED_NUM;

        // get status in DB
        $set = $ilDB->query(
            "SELECT usr_id,status,status_dirty FROM ut_lp_marks WHERE " .
            " obj_id = " . $ilDB->quote($a_obj_id, "integer") . " AND " .
            " usr_id = " . $ilDB->quote($a_user_id, "integer")
        );
        $rec = $ilDB->fetchAssoc($set);

        // update
        if ($rec) {
            $a_old_status = $rec["status"];

            // status has changed: update
            if ($rec["status"] != $a_status) {
                $ret = $ilDB->manipulate(
                    "UPDATE ut_lp_marks SET " .
                    " status = " . $ilDB->quote($a_status, "integer") . "," .
                    " status_changed = " . $ilDB->now() . "," .
                    " status_dirty = " . $ilDB->quote(0, "integer") .
                    " WHERE usr_id = " . $ilDB->quote($a_user_id, "integer") .
                    " AND obj_id = " . $ilDB->quote($a_obj_id, "integer")
                );
                if ($ret != 0) {
                    $update_dependencies = true;
                }
            } // status has not changed: reset dirty flag
            elseif ($rec["status_dirty"]) {
                $ilDB->manipulate(
                    "UPDATE ut_lp_marks SET " .
                    " status_dirty = " . $ilDB->quote(0, "integer") .
                    " WHERE usr_id = " . $ilDB->quote($a_user_id, "integer") .
                    " AND obj_id = " . $ilDB->quote($a_obj_id, "integer")
                );
            }
        } // insert
        else {
            // #13783
            $ilDB->replace(
                "ut_lp_marks",
                array(
                    "obj_id" => array("integer", $a_obj_id),
                    "usr_id" => array("integer", $a_user_id)
                ),
                array(
                    "status" => array("integer", $a_status),
                    "status_changed" => array("timestamp", date("Y-m-d H:i:s")),
                    // was $ilDB->now()
                    "status_dirty" => array("integer", 0)
                )
            );

            $update_dependencies = true;
        }

        // update percentage
        if ($a_percentage || $a_force_per) {
            $a_percentage = max(0, $a_percentage);
            $a_percentage = min(100, $a_percentage);
            $ret = $ilDB->manipulate(
                "UPDATE ut_lp_marks SET " .
                " percentage = " . $ilDB->quote($a_percentage, "integer") .
                " WHERE usr_id = " . $ilDB->quote($a_user_id, "integer") .
                " AND obj_id = " . $ilDB->quote($a_obj_id, "integer")
            );
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
                    array("crs", "grp", "fold")
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
    ) : void {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        // #11513

        $needs_update = false;

        $set = $ilDB->query(
            "SELECT usr_id, status FROM ut_lp_marks WHERE " .
            " obj_id = " . $ilDB->quote($a_obj_id, "integer") . " AND " .
            " usr_id = " . $ilDB->quote($a_user_id, "integer")
        );
        if ($rec = $ilDB->fetchAssoc($set)) {
            // current status is not attempted, so we need to update
            if ($rec["status"] == self::LP_STATUS_NOT_ATTEMPTED_NUM) {
                $needs_update = true;
            }
        } else {
            // no ut_lp_marks yet, we should update
            $needs_update = true;
        }

        if ($needs_update) {
            ilLPStatusWrapper::_updateStatus($a_obj_id, $a_user_id);
        }
    }

    /**
     * Sets all status to dirty. For testing puproses.
     */
    public static function setAllDirty() : void
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $ilDB->manipulate(
            "UPDATE ut_lp_marks SET " .
            " status_dirty = " . $ilDB->quote(1, "integer")
        );
    }

    /**
     * Sets status of an object to dirty.
     */
    public static function setDirty(int $a_obj_id) : void
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $ilDB->manipulate(
            "UPDATE ut_lp_marks SET " .
            " status_dirty = " . $ilDB->quote(1, "integer") .
            " WHERE obj_id = " . $ilDB->quote($a_obj_id, "integer")
        );
    }

    /**
     * Lookup status
     */
    public static function _lookupStatus(
        int $a_obj_id,
        int $a_user_id,
        bool $a_create = true
    ) : ?int {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $set = $ilDB->query(
            "SELECT status FROM ut_lp_marks WHERE " .
            " status_dirty = " . $ilDB->quote(0, "integer") .
            " AND usr_id = " . $ilDB->quote($a_user_id, "integer") .
            " AND obj_id = " . $ilDB->quote($a_obj_id, "integer")
        );
        if ($rec = $ilDB->fetchAssoc($set)) {
            return (int) $rec["status"];
        } elseif ($a_create) {
            ilLPStatusWrapper::_updateStatus($a_obj_id, $a_user_id);
            $set = $ilDB->query(
                "SELECT status FROM ut_lp_marks WHERE " .
                " status_dirty = " . $ilDB->quote(0, "integer") .
                " AND usr_id = " . $ilDB->quote($a_user_id, "integer") .
                " AND obj_id = " . $ilDB->quote($a_obj_id, "integer")
            );
            if ($rec = $ilDB->fetchAssoc($set)) {
                return (int) $rec["status"];
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
    ) : ?int {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $set = $ilDB->query(
            "SELECT percentage FROM ut_lp_marks WHERE " .
            " status_dirty = " . $ilDB->quote(0, "integer") .
            " AND usr_id = " . $ilDB->quote($a_user_id, "integer") .
            " AND obj_id = " . $ilDB->quote($a_obj_id, "integer")
        );
        if ($rec = $ilDB->fetchAssoc($set)) {
            return $rec["percentage"];
        }
        return null;
    }

    /**
     * Lookup user object completion
     */
    public static function _hasUserCompleted(
        int $a_obj_id,
        int $a_user_id
    ) : bool {
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
    ) : ?string {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $set = $ilDB->query(
            "SELECT status_changed FROM ut_lp_marks WHERE " .
            " status_dirty = " . $ilDB->quote(0, "integer") .
            " AND usr_id = " . $ilDB->quote($a_user_id, "integer") .
            " AND obj_id = " . $ilDB->quote($a_obj_id, "integer")
        );
        if ($rec = $ilDB->fetchAssoc($set)) {
            return (string) $rec["status_changed"];
        } else {
            ilLPStatusWrapper::_updateStatus($a_obj_id, $a_user_id);
            $set = $ilDB->query(
                "SELECT status_changed FROM ut_lp_marks WHERE " .
                " status_dirty = " . $ilDB->quote(0, "integer") .
                " AND usr_id = " . $ilDB->quote($a_user_id, "integer") .
                " AND obj_id = " . $ilDB->quote($a_obj_id, "integer")
            );
            if ($rec = $ilDB->fetchAssoc($set)) {
                return (string) $rec["status_changed"];
            }
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
    ) : array {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $sql = "SELECT usr_id, status, status_dirty FROM ut_lp_marks" .
            " WHERE obj_id = " . $ilDB->quote($a_obj_id, "integer") .
            " AND status = " . $ilDB->quote($a_status, "integer");
        if ($a_user_ids) {
            $sql .= " AND " . $ilDB->in("usr_id", $a_user_ids, "", "integer");
        }

        $set = $ilDB->query($sql);
        $res = array();
        while ($rec = $ilDB->fetchAssoc($set)) {
            // @fixme this was broken due to wrong $res['status_dirty'] access
            // check how to update status without recursion
            // check consequences of the old implementation
            if ($rec["status_dirty"]) {
                // update status and check again
                if (self::_lookupStatus(
                    $a_obj_id,
                    $rec["usr_id"]
                ) != $a_status) {
                    // update status: see comment
                }
            }
            $res[] = (int) $rec["usr_id"];
        }

        return $res;
    }

    /**
     * Get completed users for object
     */
    public static function _lookupCompletedForObject(
        int $a_obj_id,
        ?array $a_user_ids = null
    ) : array {
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
    ) : array {
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
    ) : array {
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
    ) : array {
        $lp_invalid = array();

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

    /**
     * Process lp modes for given objects
     */
    protected static function checkLPModesForObjects(
        array $a_obj_ids,
        array &$a_coll_obj_ids
    ) : array {
        $valid = array();

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
        if (sizeof($existing) != sizeof($a_obj_ids)) {
            foreach (array_diff($a_obj_ids, $existing) as $obj_id) {
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
    ) : array {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $res = array();

        // get user lp data
        $sql = "SELECT status, status_dirty, obj_id FROM ut_lp_marks" .
            " WHERE " . $ilDB->in("obj_id", $a_obj_ids, "", "integer") .
            " AND usr_id = " . $ilDB->quote($a_user_id, "integer");
        $set = $ilDB->query($sql);
        while ($row = $ilDB->fetchAssoc($set)) {
            if (!$row["status_dirty"]) {
                $res[$row["obj_id"]] = $row["status"];
            } else {
                $res[$row["obj_id"]] = self::_lookupStatus(
                    $row["obj_id"],
                    $a_user_id
                );
            }
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

    public static function preloadListGUIData(array $a_obj_ids) : void
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
        $res = array();
        if ($ilUser->getId() != ANONYMOUS_USER_ID &&
            ilObjUserTracking::_enabledLearningProgress() &&
            ilObjUserTracking::_hasLearningProgressLearner() && // #12042
            ilObjUserTracking::_hasLearningProgressListGUI()) {
            // -- validate

            // :TODO: we need the parent ref id, but this is awful
            // this step removes all "not attempted" from the list, which we usually do not want
            //$a_obj_ids = self::validateLPForObjects($user_id, $a_obj_ids, $requested_ref_id);

            // we are not handling the collections differently yet
            $coll_obj_ids = array();
            $a_obj_ids = self::checkLPModesForObjects(
                $a_obj_ids,
                $coll_obj_ids
            );

            // -- gather

            $res = self::getLPStatusForObjects($user_id, $a_obj_ids);

            // -- render

            // value to icon
            $lng->loadLanguageModule("trac");
            foreach ($res as $obj_id => $status) {
                $path = ilLearningProgressBaseGUI::_getImagePathForStatus(
                    $status
                );
                $text = ilLearningProgressBaseGUI::_getStatusText(
                    (int) $status
                );
                $res[$obj_id] = [
                    "image" => ilUtil::img($path, $text),
                    "status" => $status
                ];
            }
        }

        self::$list_gui_cache = $res;
    }

    /**
     * @return string|array
     */
    public static function getListGUIStatus(
        int $a_obj_id,
        bool $a_image_only = true
    ) {
        if ($a_image_only) {
            $image = '';
            if (isset(self::$list_gui_cache[$a_obj_id]["image"])) {
                $image = self::$list_gui_cache[$a_obj_id]["image"];
            }

            return $image;
        }
        return self::$list_gui_cache[$a_obj_id] ?? "";
    }

    public static function hasListGUIStatus(int $a_obj_id) : bool
    {
        if (isset(self::$list_gui_cache[$a_obj_id])) {
            return true;
        }
        return false;
    }
}
