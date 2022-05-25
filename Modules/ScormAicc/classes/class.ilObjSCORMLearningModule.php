<?php declare(strict_types=1);

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
/**
* Class ilObjSCORMLearningModule
*
* @author Alex Killing <alex.killing@gmx.de>
* $Id$
*
* @ingroup ModulesScormAicc
*/
class ilObjSCORMLearningModule extends ilObjSAHSLearningModule
{
    /**
    * Constructor
    * @param	integer	reference_id or object_id
    * @param	boolean	treat the id as reference_id (true) or object_id (false)
    */
    public function __construct(int $a_id = 0, bool $a_call_by_reference = true)
    {
        $this->type = "sahs";
        parent::__construct($a_id, $a_call_by_reference);
    }

    public function getTrackingItems() : array
    {
        return ilObjSCORMLearningModule::_getTrackingItems($this->getId());
    }

    /**
     * get all tracking items of scorm object
     */
    public static function _getTrackingItems(int $a_obj_id) : array
    {
        $tree = new ilSCORMTree($a_obj_id);
        $root_id = $tree->readRootId();

        $items = array();
        $childs = $tree->getSubTree($tree->getNodeData($root_id));

        foreach ($childs as $child) {
            if ($child["c_type"] === "sit") {
                $sc_item = new ilSCORMItem($child["obj_id"]);
                if ($sc_item->getIdentifierRef() != "") {
                    $items[] = $sc_item;
                }
            }
        }

        return $items;
    }

    /**
     * read manifest file
     * @throws ilSaxParserException
     */
    public function readObject() : string
    {
        global $DIC;
        $ilErr = $DIC['ilErr'];
        
        $needs_convert = false;

        // convert imsmanifest.xml file in iso to utf8 if needed

        $manifest_file = $this->getDataDirectory() . "/imsmanifest.xml";

        // check if manifestfile exists and space left on device...
        $check_for_manifest_file = is_file($manifest_file);

        // if no manifestfile
        if (!$check_for_manifest_file) {
            $ilErr->raiseError($this->lng->txt("Manifestfile $manifest_file not found!"), $ilErr->MESSAGE);
            return "";
        }

        if ($check_for_manifest_file) {
            $manifest_file_array = file($manifest_file);
            foreach ($manifest_file_array as $mfa) {
                // if (seems_not_utf8($mfa))
                if (@iconv('UTF-8', 'UTF-8', $mfa) != $mfa) {
                    $needs_convert = true;
                    break;
                }
            }

            // to copy the file we need some extraspace, counted in bytes *2 ... we need 2 copies....
            $estimated_manifest_filesize = filesize($manifest_file) * 2;
            
            // i deactivated this, because it seems to fail on some windows systems (see bug #1795)
            //$check_disc_free = disk_free_space($this->getDataDirectory()) - $estimated_manifest_filesize;
            $check_disc_free = 2;
        }

        // if $manifest_file needs to be converted to UTF8
        if ($needs_convert) {
            // if file exists and enough space left on device
            if ($check_for_manifest_file && ($check_disc_free > 1)) {

                // create backup from original
                if (!copy($manifest_file, $manifest_file . ".old")) {
                    echo "Failed to copy $manifest_file...<br>\n";
                }

                // read backupfile, convert each line to utf8, write line to new file
                // php < 4.3 style
                $f_write_handler = fopen($manifest_file . ".new", "w");
                $f_read_handler = fopen($manifest_file . ".old", "r");
                while (!feof($f_read_handler)) {
                    $zeile = fgets($f_read_handler);
                    //echo mb_detect_encoding($zeile);
                    fwrite($f_write_handler, utf8_encode($zeile));
                }
                fclose($f_read_handler);
                fclose($f_write_handler);

                // copy new utf8-file to imsmanifest.xml
                if (!copy($manifest_file . ".new", $manifest_file)) {
                    echo "Failed to copy $manifest_file...<br>\n";
                }

                if (!@is_file($manifest_file)) {
                    $ilErr->raiseError($this->lng->txt("cont_no_manifest"), $ilErr->WARNING);
                }
            } else {
                // gives out the specific error

                if (!($check_disc_free > 1)) {
                    $ilErr->raiseError($this->lng->txt("Not enough space left on device!"), $ilErr->MESSAGE);
                }
                return "";
            }
        } else {
            // check whether file starts with BOM (that confuses some sax parsers, see bug #1795)
            $hmani = fopen($manifest_file, "r");
            $start = fread($hmani, 3);
            if (strtolower(bin2hex($start)) === "efbbbf") {
                $f_write_handler = fopen($manifest_file . ".new", "w");
                while (!feof($hmani)) {
                    $n = fread($hmani, 900);
                    fwrite($f_write_handler, $n);
                }
                fclose($f_write_handler);
                fclose($hmani);

                // copy new utf8-file to imsmanifest.xml
                if (!copy($manifest_file . ".new", $manifest_file)) {
                    echo "Failed to copy $manifest_file...<br>\n";
                }
            } else {
                fclose($hmani);
            }
        }

        // todo determine imsmanifest.xml path here...
        $slmParser = new ilSCORMPackageParser($this, $manifest_file);
        $slmParser->startParsing();
        return $slmParser->getPackageTitle();
    }

    /**
     * set settings for learning progress determination per default at upload
     */
    public function setLearningProgressSettingsAtUpload() : void
    {
        global $DIC;
        $ilSetting = $DIC->settings();
        //condition 1
        $lm_set = new ilSetting("lm");
        if ($lm_set->get('scorm_lp_auto_activate') != 1) {
            return;
        }
        if (ilObjUserTracking::_enabledLearningProgress() == false) {
            return;
        }
        $lm_set = new ilLPObjSettings($this->getId());
        $lm_set->setMode(ilLPObjSettings::LP_MODE_SCORM);
        $lm_set->insert();
        $collection = new ilLPCollectionOfSCOs($this->getId(), ilLPObjSettings::LP_MODE_SCORM);
        $scos = array();
        foreach ($collection->getPossibleItems() as $sco_id => $item) {
            $scos[] = $sco_id;
        }
        $collection->activateEntries($scos);
    }

    /**
     * get all tracked items of current user
     */
    public function getTrackedItems() : array
    {
        global $DIC;
        $ilDB = $DIC->database();
        $ilUser = $DIC->user();
        
        $sco_set = $ilDB->queryF(
            '
		SELECT DISTINCT sco_id FROM scorm_tracking WHERE obj_id = %s',
            array('integer'),
            array($this->getId())
        );

        $items = array();
        while ($sco_rec = $ilDB->fetchAssoc($sco_set)) {
            $sc_item = new ilSCORMItem((int) $sco_rec["sco_id"]);
            if ($sc_item->getIdentifierRef() != "") {
                $items[] = $sc_item;
            }
        }

        return $items;
    }
    
//    /**
//    * Return the last access timestamp for a given user
//    *
//    * @param	int		$a_obj_id		object id
//    * @param	int		$user_id		user id
//    * @return timestamp
//    */
//    public static function _lookupLastAccess(int $a_obj_id, $a_usr_id)
//    {
//        global $DIC;
//        $ilDB = $DIC->database();
//
//        $result = $ilDB->queryF(
//            '
    //		SELECT last_access FROM sahs_user
    //		WHERE  obj_id = %s
    //		AND user_id = %s',
//            array('integer','integer'),
//            array($a_obj_id,$a_usr_id)
//        );
//
//        if ($ilDB->numRows($result)) {
//            $row = $ilDB->fetchAssoc($result);
//            return $row["last_access"];
//        }
//        return "";
//    }

    public function getTrackedUsers(string $a_search) : array
    {
        global $DIC;
        $ilDB = $DIC->database();
        $ilUser = $DIC->user();
        //TODO: UK last_access is not correct if no Commit or last_visited_sco
        //		$query = 'SELECT user_id,MAX(c_timestamp) last_access, lastname, firstname FROM scorm_tracking st ' .
        $query = 'SELECT user_id, last_access, lastname, firstname FROM sahs_user st ' .
            'JOIN usr_data ud ON st.user_id = ud.usr_id ' .
            'WHERE obj_id = ' . $ilDB->quote($this->getId(), 'integer');
        if ($a_search) {
            //			$query .= ' AND (' . $ilDB->like('lastname', 'text', '%' . $a_search . '%') . ' OR ' . $ilDB->like('firstname', 'text', '%' . $a_search . '%') .')';
            $query .= ' AND ' . $ilDB->like('lastname', 'text', '%' . $a_search . '%');
        }
        $query .= ' GROUP BY user_id, lastname, firstname, last_access';
        $sco_set = $ilDB->query($query);

        $items = array();
        while ($sco_rec = $ilDB->fetchAssoc($sco_set)) {
            $items[] = $sco_rec;
        }
        return $items;
    }

    //toDo
    /**
     * Get attempts for all users
     *
     * @return int[]
     */
    public function getAttemptsForUsers() : array
    {
        global $DIC;
        $ilDB = $DIC->database();
        $query = 'SELECT user_id, package_attempts FROM sahs_user WHERE obj_id = ' . $ilDB->quote($this->getId(), 'integer') . ' ';
        $res = $ilDB->query($query);

        $attempts = array();
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_ASSOC)) {
            $attempts[$row['user_id']] = (int) $row['package_attempts'];
        }
        return $attempts;
    }

    //todo

    /**
     * get number of attempts for a certain user and package
     */
    public function getAttemptsForUser(int $a_user_id) : int
    {
        global $DIC;
        $ilDB = $DIC->database();
        $val_set = $ilDB->queryF(
            'SELECT package_attempts FROM sahs_user WHERE obj_id = %s AND user_id = %s',
            array('integer','integer'),
            array($this->getId(),$a_user_id)
        );

        $val_rec = $ilDB->fetchAssoc($val_set);
        
        if ($val_rec["package_attempts"] == null) {
            $val_rec["package_attempts"] = 0;
        }
        return (int) $val_rec["package_attempts"];
    }

    /**
     * Get module version for users.
     */
    public function getModuleVersionForUsers() : array
    {
        global $DIC;
        $ilDB = $DIC->database();
        $query = 'SELECT user_id, module_version FROM sahs_user WHERE obj_id = ' . $ilDB->quote($this->getId(), 'integer') . ' ';
        $res = $ilDB->query($query);

        $versions = array();
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_ASSOC)) {
            $versions[$row['user_id']] = (int) $row['module_version'];
        }
        return $versions;
    }

    /**
     * get module version that tracking data for a user was recorded on
     */
    public function getModuleVersionForUser(int $a_user_id) : string
    {
        global $DIC;
        $ilDB = $DIC->database();
        $val_set = $ilDB->queryF(
            'SELECT module_version FROM sahs_user WHERE obj_id = %s AND user_id = %s',
            array('integer','integer'),
            array($this->getId(),$a_user_id,0)
        );

        $val_rec = $ilDB->fetchAssoc($val_set);
        
        if ($val_rec["module_version"] == null) {
            $val_rec["module_version"] = "";
        }
        return $val_rec["module_version"];
    }

    /**
     * Get tracking data per user
     */
    public function getTrackingDataPerUser(int $a_sco_id, int $a_user_id) : array
    {
        global $DIC;
        $ilDB = $DIC->database();

        $data_set = $ilDB->queryF(
            '
		SELECT * FROM scorm_tracking 
		WHERE user_id = %s
		AND sco_id = %s
		AND obj_id = %s
		ORDER BY lvalue',
            array('integer','integer','integer'),
            array($a_user_id,$a_sco_id,$this->getId())
        );
            
        $data = array();
        while ($data_rec = $ilDB->fetchAssoc($data_set)) {
            $data[] = $data_rec;
        }

        return $data;
    }

    public function getTrackingDataAgg(int $a_user_id) : array
    {
        global $DIC;
        $ilDB = $DIC->database();

        // get all users with any tracking data
        $sco_set = $ilDB->queryF(
            '
		SELECT DISTINCT sco_id FROM scorm_tracking 
		WHERE obj_id = %s
		AND user_id = %s
		AND sco_id <> %s',
            array('integer','integer','integer'),
            array($this->getId(),$a_user_id,0)
        );

        $data = array();
        while ($sco_rec = $ilDB->fetchAssoc($sco_set)) {
            $data_set = $ilDB->queryF(
                '
			SELECT * FROM scorm_tracking 
			WHERE  obj_id = %s
			AND sco_id = %s
			AND user_id = %s 
			AND lvalue <> %s
			AND (lvalue = %s
				OR lvalue = %s
				OR lvalue = %s)',
                array('integer','integer','integer','text','text','text','text'),
                array($this->getId(),
                $sco_rec["sco_id"],
                $a_user_id,
                "package_attempts",
                "cmi.core.lesson_status",
                "cmi.core.total_time",
                "cmi.core.score.raw")
            );
            
            $score = $time = $status = "";
            
            while ($data_rec = $ilDB->fetchAssoc($data_set)) {
                switch ($data_rec["lvalue"]) {
                    case "cmi.core.lesson_status":
                        $status = $data_rec["rvalue"];
                        break;

                    case "cmi.core.total_time":
                        $time = $data_rec["rvalue"];
                        break;

                    case "cmi.core.score.raw":
                        $score = $data_rec["rvalue"];
                        break;
                }
            }
            $sc_item = new ilSCORMItem($sco_rec["sco_id"]);
            $data[] = array("sco_id" => $sco_rec["sco_id"], "title" => $sc_item->getTitle(),
            "score" => $score, "time" => $time, "status" => $status);
        }
        return (array) $data;
    }

    public function getTrackingDataAggSco(int $a_sco_id) : array
    {
        global $DIC;
        $ilDB = $DIC->database();

        // get all users with any tracking data
        $user_set = $ilDB->queryF(
            '
	        SELECT DISTINCT user_id FROM scorm_tracking 
	        WHERE obj_id = %s
	        AND sco_id = %s',
            array('integer','integer'),
            array($this->getId(),$a_sco_id)
        );

        $data = array();
        while ($user_rec = $ilDB->fetchAssoc($user_set)) {
            $data_set = $ilDB->queryF(
                '
	            SELECT * FROM scorm_tracking 
	            WHERE obj_id = %s
	            AND sco_id = %s
	            AND user_id = %s
	            AND (lvalue = %s
	            OR lvalue = %s
	            OR lvalue = %s)',
                array('integer','integer','integer','text','text','text'),
                array($this->getId(),
                    $a_sco_id,
                    $user_rec["user_id"],
                    "cmi.core.lesson_status",
                    "cmi.core.total_time",
                    "cmi.core.score.raw")
            );
                
            $score = $time = $status = "";
                  
            while ($data_rec = $ilDB->fetchAssoc($data_set)) {
                switch ($data_rec["lvalue"]) {
                        case "cmi.core.lesson_status":
                            $status = $data_rec["rvalue"];
                            break;

                        case "cmi.core.total_time":
                            $time = $data_rec["rvalue"];
                            break;

                        case "cmi.core.score.raw":
                            $score = $data_rec["rvalue"];
                            break;
                    }
            }

            $data[] = array("user_id" => $user_rec["user_id"],
                    "score" => $score, "time" => $time, "status" => $status);
        }

        return $data;
    }

    /**
     * Export selected user tracking data
     * @global ilObjUser $ilUser
     */
    public function exportSelected(bool $a_all, array $a_users = array()) : void
    {
        global $DIC;
        $ilDB = $DIC->database();
        $ilUser = $DIC->user();
        $privacy = ilPrivacySettings::getInstance();
        $allowExportPrivacy = $privacy->enabledExportSCORM();

        $csv = "";
        $query = 'SELECT * FROM sahs_user WHERE obj_id = %s';
        if (count($a_users) > 0) {
            $query .= ' AND ' . $ilDB->in('user_id', $a_users, false, 'integer');
        }
        $res = $ilDB->queryF(
            $query,
            array('integer'),
            array($this->getId())
        );
        while ($data = $ilDB->fetchAssoc($res)) {
            $csv = $csv . $data["obj_id"]
                . ";\"" . $this->getTitle() . "\""
                . ";" . $data["module_version"]
                . ";\"" . implode("\";\"", ilSCORMTrackingItems::userDataArrayForExport((int) $data["user_id"], $allowExportPrivacy)) . "\""
                . ";\"" . $data["last_access"] . "\""
                . ";\"" . ilLearningProgressBaseGUI::__readStatus((int) $data["obj_id"], (int) $data["user_id"]) . "\"" //not $data["status"] because modifications to learning progress could have made before export
                . ";" . $data["package_attempts"]
                . ";" . $data["percentage_completed"]
                . ";" . $data["sco_total_time_sec"]
//				. ";\"" . $certificateDate ."\""
                . "\n";
        }
        $udh = ilSCORMTrackingItems::userDataHeaderForExport();
        $header = "LearningModuleId;LearningModuleTitle;LearningModuleVersion;" . str_replace(',', ';', $udh["cols"]) . ";"
                . "LastAccess;Status;Attempts;percentageCompletedSCOs;SumTotal_timeSeconds\n";

        $this->sendExportFile($header, $csv);
    }

    public function importTrackingData(string $a_file) : bool
    {
        global $DIC;
        $ilDB = $DIC->database();
        $ilUser = $DIC->user();
        
        $success = false;
        //echo file_get_contents($a_file);
        $method = null;
        
        //lets import
        $fhandle = fopen($a_file, "r");
        
        //the top line is the field names
        $fields = fgetcsv($fhandle, 2 ** 16, ';');
        //lets check the import method
        fclose($fhandle);
       
        switch ($fields[0]) {
            case "Scoid":
            case "SCO-Identifier":
                $success = $this->importRaw($a_file);
                break;
            case "Department":
            case "LearningModuleId":
                $success = $this->importSuccess($a_file);
                break;
            default:
                return false;
        }
        return $success;
    }

    public function importSuccess(string $a_file) : bool
    {
        global $DIC;
        $ilDB = $DIC->database();
        $ilUser = $DIC->user();
        $scos = array();
        $olp = ilObjectLP::getInstance($this->getId());
        $collection = $olp->getCollectionInstance();
        if ($collection) {
            $scos = $collection->getItems();
        }
        
        $fhandle = fopen($a_file, "r");

        $obj_id = $this->getID();
        $fields = fgetcsv($fhandle, 2 ** 16, ';');
        $users = array();
        $usersToDelete = array();
        while (($csv_rows = fgetcsv($fhandle, 2 ** 16, ";")) !== false) {
            $data = array_combine($fields, $csv_rows);
            //no check the format - sufficient to import users
            if ($data["Login"]) {
                $user_id = $this->get_user_id($data["Login"]);
            }
            if ($data["login"]) {
                $user_id = $this->get_user_id($data["login"]);
            }
            //add mail in future
            if ($data["user"] && is_numeric($data["user"])) {
                $user_id = (int) $data["user"];
            }

            if ($user_id > 0) {
                $last_access = ilUtil::now();
                if ($data['Date']) {
                    $date_ex = explode('.', $data['Date']);
                    $last_access = implode('-', array($date_ex[2], $date_ex[1], $date_ex[0]));
                }
                if ($data['LastAccess']) {
                    $last_access = $data['LastAccess'];
                }
                
                $status = ilLPStatus::LP_STATUS_COMPLETED_NUM;
                
                if ($data["Status"]) {
                    if (is_int($data["Status"])) {
                        $status = $data["Status"];
                    } elseif ($data["Status"] == "0" || $data["Status"] == "1" || $data["Status"] == "2" || $data["Status"] == "3") {
                        $status = (int) $data["Status"];
                    } elseif ($data["Status"] == ilLPStatus::LP_STATUS_NOT_ATTEMPTED) {
                        $status = ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM;
                    } elseif ($data["Status"] == ilLPStatus::LP_STATUS_IN_PROGRESS) {
                        $status = ilLPStatus::LP_STATUS_IN_PROGRESS_NUM;
                    } elseif ($data["Status"] == ilLPStatus::LP_STATUS_FAILED) {
                        $status = ilLPStatus::LP_STATUS_FAILED_NUM;
                    }
                }

                $attempts = null;
                if ($data["Attempts"]) {
                    $attempts = $data["Attempts"];
                }
                
                $percentage_completed = 0;
                if ($status == ilLPStatus::LP_STATUS_COMPLETED_NUM) {
                    $percentage_completed = 100;
                }
                if ($data['percentageCompletedSCOs']) {
                    $percentage_completed = $data['percentageCompletedSCOs'];
                }

                $sco_total_time_sec = null;
                if ($data['SumTotal_timeSeconds']) {
                    $sco_total_time_sec = $data['SumTotal_timeSeconds'];
                }
                
                if ($status == ilLPStatus::LP_STATUS_NOT_ATTEMPTED) {
                    $usersToDelete[] = $user_id;
                } else {
                    $this->importSuccessForSahsUser($user_id, $last_access, $status, $attempts, $percentage_completed, $sco_total_time_sec);
                    $users[] = $user_id;
                }
                
                if ($status == ilLPStatus::LP_STATUS_COMPLETED_NUM) {
                    foreach ($scos as $sco_id) {
                        $statement = $ilDB->queryF(
                            '
							SELECT * FROM scorm_tracking 
							WHERE user_id = %s
							AND sco_id = %s 
							AND lvalue = %s
							AND obj_id = %s',
                            array('integer','integer','text','integer'),
                            array($user_id, $sco_id, 'cmi.core.lesson_status',$obj_id)
                        );
                        if ($ilDB->numRows($statement) > 0) {
                            $ilDB->update(
                                'scorm_tracking',
                                array(
                                    'rvalue' => array('clob', 'completed'),
                                    'c_timestamp' => array('timestamp', $last_access)
                                ),
                                array(
                                    'user_id' => array('integer', $user_id),
                                    'sco_id' => array('integer', $sco_id),
                                    'lvalue' => array('text', 'cmi.core.lesson_status'),
                                    'obj_id' => array('integer', $obj_id)
                                )
                            );
                        } else {
                            $ilDB->insert('scorm_tracking', array(
                                'obj_id' => array('integer', $obj_id),
                                'user_id' => array('integer', $user_id),
                                'sco_id' => array('integer', $sco_id),
                                'lvalue' => array('text', 'cmi.core.lesson_status'),
                                'rvalue' => array('clob', 'completed'),
                                'c_timestamp' => array('timestamp', $last_access)
                            ));
                        }
                    }
                }
            } else {
                //echo "Warning! User $csv_rows[0] does not exist in ILIAS. Data for this user was skipped.\n";
            }
        }
        if (count($usersToDelete) > 0) {
            // include_once("./Services/Tracking/classes/class.ilLPMarks.php");
            // ilLPMarks::_deleteForUsers($this->getId(), $usersToDelete);
            $this->deleteTrackingDataOfUsers($usersToDelete);
        }
        ilLPStatusWrapper::_refreshStatus($this->getId(), $users);
        return true;
    }

    public function importSuccessForSahsUser(
        int $user_id,
        string $last_access,
        int $status,
        $attempts = null,
        $percentage_completed = null,
        $sco_total_time_sec = null
    ) : void {
        global $DIC;
        $ilDB = $DIC->database();
        $statement = $ilDB->queryF(
            'SELECT * FROM sahs_user WHERE obj_id = %s AND user_id = %s',
            array('integer','integer'),
            array($this->getID(),$user_id)
        );
        if ($ilDB->numRows($statement) > 0) {
            $ilDB->update(
                'sahs_user',
                array(
                    'last_access' => array('timestamp', $last_access),
                    'status' => array('integer', $status),
                    'package_attempts' => array('integer', $attempts),
                    'percentage_completed' => array('integer', $percentage_completed),
                    'sco_total_time_sec' => array('integer', $sco_total_time_sec)
                ),
                array(
                    'obj_id' => array('integer', $this->getID()),
                    'user_id' => array('integer', $user_id)
                )
            );
        } else {
            $ilDB->insert('sahs_user', array(
                'obj_id' => array('integer', $this->getID()),
                'user_id' => array('integer', $user_id),
                'last_access' => array('timestamp', $last_access),
                'status' => array('integer', $status),
                'package_attempts' => array('integer', $attempts),
                'percentage_completed' => array('integer', $percentage_completed),
                'sco_total_time_sec' => array('integer', $sco_total_time_sec)
            ));
        }
        ilChangeEvent::_recordReadEvent("sahs", $DIC->http()->wrapper()->query()->retrieve('ref_id', $DIC->refinery()->kindlyTo()->int()), $this->getID(), $user_id, false, $attempts, $sco_total_time_sec);
    }

    /**
     * Parse il_usr_123_6 id
     */
    private function parseUserId(string $il_id) : int
    {
        global $DIC;
        $ilSetting = $DIC->settings();

        $parts = explode('_', $il_id);

        if (!count((array) $parts)) {
            return 0;
        }
        if (!isset($parts[2]) or !isset($parts[3])) {
            return 0;
        }
        if ($parts[2] != $ilSetting->get('inst_id', $parts[2])) {
            return 0;
        }
        return (int) $parts[3];
    }

    /**
     * Import raw data
     */
    private function importRaw(string $a_file) : bool
    {
        global $DIC;
        $ilDB = $DIC->database();
        $ilUser = $DIC->user();
        $lng = $DIC->language();
        $lng->loadLanguageModule("scormtrac");

        $fhandle = fopen($a_file, "r");

        $fields = fgetcsv($fhandle, 2 ** 16, ';');
        $users = array();
        $a_last_access = array();
        $a_time = array();
        $a_package_attempts = array();
        $a_module_version = array();
        while (($csv_rows = fgetcsv($fhandle, 2 ** 16, ";")) !== false) {
            $data = array_combine($fields, $csv_rows);
            if ($data['Userid']) {
                $user_id = $this->parseUserId($data['Userid']);
            } elseif ($data[$lng->txt("user")]) {
                if (is_int($data[$lng->txt("user")])) {
                    $user_id = $data[$lng->txt("user")];
                }
            }
            if ($data[$lng->txt("login")]) {
                $user_id = $this->get_user_id($data[$lng->txt("login")]);
            }
            if (!$user_id) {
                continue;
            }

            if ($data['Scoid']) {
                $il_sco_id = $this->lookupSCOId($data['Scoid']);
            }
            if ($data[$lng->txt("identifierref")]) {
                $il_sco_id = $this->lookupSCOId($data[$lng->txt("identifierref")]);
            }
            if (!$il_sco_id) {
                continue;
            }

            $c_timestamp = "";
            if ($data['Timestamp']) {
                $c_timestamp = $data['Timestamp'];
            }
            if ($data[$lng->txt("c_timestamp")]) {
                $c_timestamp = $data[$lng->txt("c_timestamp")];
            }
            if ($c_timestamp == "") {
                $date = new DateTime();
                $c_timestamp = $date->getTimestamp();
            } else {
                if ($a_last_access[$user_id]) {
                    if ($a_last_access[$user_id] < $c_timestamp) {
                        $a_last_access[$user_id] = $c_timestamp;
                    }
                } else {
                    $a_last_access[$user_id] = $c_timestamp;
                }
            }
            
            if (!$data['Key']) {
                continue;
            }
            if (!$data['Value']) {
                $data['Value'] = "";
            }

            if ($data['Key'] === "cmi.core.total_time" && $data['Value'] != "") {
                $tarr = explode(":", $data['Value']);
                $sec = (int) $tarr[2] + (int) $tarr[1] * 60 +
                    (int) substr($tarr[0], strlen($tarr[0]) - 3) * 60 * 60;
                if ($a_time[$user_id]) {
                    $a_time[$user_id] += $sec;
                } else {
                    $a_time[$user_id] = $sec;
                }
            }
            //do the actual import
            if ($il_sco_id > 0) {
                $statement = $ilDB->queryF(
                    '
					SELECT * FROM scorm_tracking 
					WHERE user_id = %s
					AND sco_id = %s 
					AND lvalue = %s
					AND obj_id = %s',
                    array('integer', 'integer', 'text', 'integer'),
                    array($user_id, $il_sco_id, $data['Key'], $this->getID())
                );
                if ($ilDB->numRows($statement) > 0) {
                    $ilDB->update(
                        'scorm_tracking',
                        array(
                            'rvalue' => array('clob', $data['Value']),
                            'c_timestamp' => array('timestamp', $c_timestamp)
                        ),
                        array(
                            'user_id' => array('integer', $user_id),
                            'sco_id' => array('integer', $il_sco_id),
                            'lvalue' => array('text', $data['Key']),
                            'obj_id' => array('integer', $this->getId())
                        )
                    );
                } else {
                    $ilDB->insert('scorm_tracking', array(
                        'obj_id' => array('integer', $this->getId()),
                        'user_id' => array('integer', $user_id),
                        'sco_id' => array('integer', $il_sco_id),
                        'lvalue' => array('text', $data['Key']),
                        'rvalue' => array('clob', $data['Value']),
                        'c_timestamp' => array('timestamp', $data['Timestamp'])
                    ));
                }
            }
            // $package_attempts = 1;
            if ($il_sco_id == 0) {
                if ($data['Key'] === "package_attempts") {
                    $a_package_attempts[$user_id] = $data['Value'];
                }
                // if ($data['Key'] == "module_version") $a_module_version[$user_id] = $data['Value'];
            }
            if (!in_array($user_id, $users)) {
                $users[] = $user_id;
            }
        }
        fclose($fhandle);
        ilLPStatusWrapper::_refreshStatus($this->getId(), $users);
        foreach ($users as $user_id) {
            $attempts = 1;
            if ($a_package_attempts[$user_id]) {
                $attempts = $a_package_attempts[$user_id];
            }
            // $module_version = 1;
            // if ($a_module_version[$user_id]) $module_version = $a_module_version[$user_id];
            $sco_total_time_sec = null;
            if ($a_time[$user_id]) {
                $sco_total_time_sec = $a_time[$user_id];
            }
            $last_access = null;
            if ($a_last_access[$user_id]) {
                $last_access = $a_last_access[$user_id];
            }
            // $status = ilLPStatusWrapper::_determineStatus($this->getId(),$user_id);
            $status = (int) ilLPStatus::_lookupStatus($this->getId(), $user_id);
            // $percentage_completed = ilLPStatusSCORM::determinePercentage($this->getId(),$user_id);
            $percentage_completed = ilLPStatus::_lookupPercentage($this->getId(), $user_id);

            $this->importSuccessForSahsUser($user_id, $last_access, $status, $attempts, $percentage_completed, $sco_total_time_sec);
        }

        return true;
    }


    
    //helper function

    public function get_user_id(string $a_login) : ?int
    {
        global $DIC;
        $ilDB = $DIC->database();
        $ilUser = $DIC->user();
        
        $val_set = $ilDB->queryF(
            'SELECT * FROM usr_data WHERE(login=%s)',
            array('text'),
            array($a_login)
        );
        $val_rec = $ilDB->fetchAssoc($val_set);
        
        if (count($val_rec) > 0) {
            return (int) $val_rec['usr_id'];
        }

        return null;
    }

    /**
     * resolves manifest SCOID to internal ILIAS SCO ID
     */
    private function lookupSCOId(string $a_referrer) : int
    {
        global $DIC;
        $ilDB = $DIC->database();
        $ilUser = $DIC->user();
        
        //non specific SCO entries
        if ($a_referrer == "0") {
            return 0;
        }

        $val_set = $ilDB->queryF(
            '
		SELECT obj_id FROM sc_item,scorm_tree 
		WHERE (obj_id = child 
		AND identifierref = %s 
		AND slm_id = %s)',
            array('text','integer'),
            array($a_referrer,$this->getID())
        );
        $val_rec = $ilDB->fetchAssoc($val_set);
        
        return (int) $val_rec["obj_id"];
    }

    /**
     * assumes that only one account exists for a mailadress
     */
    public function getUserIdEmail(string $a_mail) : int
    {
        global $DIC;
        $ilDB = $DIC->database();
        $ilUser = $DIC->user();
        
        $val_set = $ilDB->queryF(
            'SELECT usr_id FROM usr_data WHERE(email=%s)',
            array('text'),
            array($a_mail)
        );
        $val_rec = $ilDB->fetchAssoc($val_set);
                
        
        return (int) $val_rec["usr_id"];
    }

    //todo
    /**
     * send export file to browser
     */
    public function sendExportFile(string $a_header, string $a_content) : void
    {
        $timestamp = time();
        $refid = $this->getRefId();
        $filename = "scorm_tracking_" . $refid . "_" . $timestamp . ".csv";
        ilUtil::deliverData($a_header . $a_content, $filename);
        exit;
    }
    
    /**
    * Get an array of id's for all Sco's in the module
    * @param int $a_id Object id
    * @return array Sco id's
    */
    public static function _getAllScoIds(int $a_id) : array
    {
        global $DIC;
        $ilDB = $DIC->database();
        
        $scos = array();

        $val_set = $ilDB->queryF(
            '
		SELECT scorm_object.obj_id,
				scorm_object.title,
				scorm_object.c_type,
				scorm_object.slm_id,
				scorm_object.obj_id scoid 
		FROM scorm_object,sc_item,sc_resource 
		WHERE(scorm_object.slm_id = %s
		AND scorm_object.obj_id = sc_item.obj_id 
		AND sc_item.identifierref = sc_resource.import_id 
		AND sc_resource.scormtype = %s)
		GROUP BY scorm_object.obj_id,
				scorm_object.title,
				scorm_object.c_type,
				scorm_object.slm_id,
				scorm_object.obj_id ',
            array('integer', 'text'),
            array($a_id,'sco')
        );

        while ($val_rec = $ilDB->fetchAssoc($val_set)) {
            $scos[] = $val_rec['scoid'];
        }
        return $scos;
    }
    
    /**
    * Get the status of a SCORM module for a given user
    * @param int $a_id Object id
    * @param int $a_user User id
    * @param array $a_allScoIds Array of Sco id's in this module
    * @param boolean $a_numerical Text (false) or boolean result (true)
    * @return bool Status result
    */
    public static function _getStatusForUser(int $a_id, int $a_user, array $a_allScoIds, bool $a_numerical = false) : bool
    {
        global $DIC;
        $ilDB = $DIC->database();
        $lng = $DIC->language();
        
        $scos = $a_allScoIds;
        //check if all SCO's are completed
        $scos_c = implode(',', $scos);

        $val_set = $ilDB->queryF(
            '
		SELECT * FROM scorm_tracking 
		WHERE (user_id = %s
		AND obj_id = %s
		AND ' . $ilDB->in('sco_id', $scos, false, 'integer') . '
		AND ((lvalue = %s AND ' . $ilDB->like('rvalue', 'clob', 'completed') . ') 
			OR (lvalue = %s AND ' . $ilDB->like('rvalue', 'clob', 'passed') . ')))',
            array('integer','integer','text','text'),
            array($a_user,$a_id,'cmi.core.lesson_status', 'cmi.core.lesson_status')
        );
        while ($val_rec = $ilDB->fetchAssoc($val_set)) {
            $key = array_search($val_rec['sco_id'], $scos);
            unset($scos[$key]);
        }
        $completion = false;
        //check for completion
        if (count($scos) == 0) {
            $completion = ($a_numerical === true)  ? true: $lng->txt("cont_complete");
        }
        if (count($scos) > 0) {
            $completion = ($a_numerical === true)  ? false: $lng->txt("cont_incomplete");
        }
        return $completion;
    }

    /**
    * Get the completion of a SCORM module for a given user
    * @param int $a_id Object id
    * @param int $a_user User id
    * @return string Completion status
    */
    public static function _getCourseCompletionForUser(int $a_id, int $a_user) : bool
    {
        return ilObjSCORMLearningModule::_getStatusForUser($a_id, $a_user, ilObjSCORMLearningModule::_getAllScoIds($a_id), true);
    }

    /**
     * @return array
     */
    public function getAllScoIds() : array
    {
        global $DIC;
        $ilDB = $DIC->database();
        
        $scos = array();
        //get all SCO's of this object
        $val_set = $ilDB->queryF(
            '
		SELECT scorm_object.obj_id,
				scorm_object.title,
				scorm_object.c_type,
				scorm_object.slm_id,
				scorm_object.obj_id scoid 
		FROM scorm_object, sc_item,sc_resource 
		WHERE(scorm_object.slm_id = %s 
			AND scorm_object.obj_id = sc_item.obj_id 
			AND sc_item.identifierref = sc_resource.import_id 
			AND sc_resource.scormtype = %s )
		GROUP BY scorm_object.obj_id,
		scorm_object.title,
		scorm_object.c_type,
		scorm_object.slm_id,
		scorm_object.obj_id',
            array('integer','text'),
            array($this->getId(),'sco')
        );

        while ($val_rec = $ilDB->fetchAssoc($val_set)) {
            $scos[] = $val_rec['scoid'];
        }
        return $scos;
    }

    public function getStatusForUser(int $a_user, array $a_allScoIds, bool $a_numerical = false) : bool
    {
        global $DIC;
        $ilDB = $DIC->database();
        $scos = $a_allScoIds;
        //loook up status
        //check if all SCO's are completed
        $scos_c = implode(',', $scos);

        $val_set = $ilDB->queryF(
            '
		SELECT sco_id FROM scorm_tracking 
		WHERE (user_id = %s
			AND obj_id = %s
			AND ' . $ilDB->in('sco_id', $scos, false, 'integer') . '
		 AND ((lvalue = %s AND ' . $ilDB->like('rvalue', 'clob', 'completed') . ') OR (lvalue =  %s AND ' . $ilDB->like('rvalue', 'clob', 'passed') . ') ) )',
            array('integer','integer','text','text',),
            array($a_user,$this->getID(),'cmi.core.lesson_status','cmi.core.lesson_status')
        );
        while ($val_rec = $ilDB->fetchAssoc($val_set)) {
            $key = array_search($val_rec['sco_id'], $scos);
            unset($scos[$key]);
        }
        //check for completion
        if (count($scos) == 0) {
            $completion = ($a_numerical === true)  ? true: $this->lng->txt("cont_complete");
        }
        if (count($scos) > 0) {
            $completion = ($a_numerical === true)  ? false: $this->lng->txt("cont_incomplete");
        }
        return $completion;
    }

    public function getCourseCompletionForUser(int $a_user) : bool
    {
        return $this->getStatusForUser($a_user, $this->getAllScoIds(), true);
    }
    
    /**
     * to be called from IlObjUser
     */
    public static function _removeTrackingDataForUser(int $user_id) : void
    {
        global $DIC;
        $ilDB = $DIC->database();
        //gobjective
        $ilDB->manipulateF(
            'DELETE FROM scorm_tracking WHERE user_id = %s',
            array('integer'),
            array($user_id)
        );
        $ilDB->manipulateF(
            'DELETE FROM sahs_user WHERE user_id = %s',
            array('integer'),
            array($user_id)
        );
    }

    public static function _getScoresForUser(int $a_item_id, int $a_user_id) : array
    {
        global $DIC;
        $ilDB = $DIC->database();

        $retAr = array("raw" => null, "max" => null, "scaled" => null);
        $val_set = $ilDB->queryF(
            "
			SELECT lvalue, rvalue FROM scorm_tracking 
			WHERE sco_id = %s 
			AND user_id =  %s
			AND (lvalue = 'cmi.core.score.raw' OR lvalue = 'cmi.core.score.max')",
            array('integer', 'integer'),
            array($a_item_id, $a_user_id)
        );
        while ($val_rec = $ilDB->fetchAssoc($val_set)) {
            if ($val_rec['lvalue'] === "cmi.core.score.raw") {
                $retAr["raw"] = $val_rec["rvalue"];
            }
            if ($val_rec['lvalue'] === "cmi.core.score.max") {
                $retAr["max"] = $val_rec["rvalue"];
            }
        }
        if ($retAr["raw"] != null && $retAr["max"] != null) {
            $retAr["scaled"] = ($retAr["raw"] / $retAr["max"]);
        }

        return $retAr;
    }

    public function getLastVisited(int $user_id) : string
    {
        global $DIC;
        $ilDB = $DIC->database();
        $val_set = $ilDB->queryF(
            'SELECT last_visited FROM sahs_user WHERE obj_id = %s AND user_id = %s',
            array('integer','integer'),
            array($this->getID(),$user_id)
        );
        while ($val_rec = $ilDB->fetchAssoc($val_set)) {
            if ($val_rec["last_visited"] != null) {
                return "" . $val_rec["last_visited"];
            }
        }
        return '0';
    }

    public function deleteTrackingDataOfUsers(array $a_users) : void
    {
        global $DIC;
        $ilDB = $DIC->database();

        ilChangeEvent::_deleteReadEventsForUsers($this->getId(), $a_users);

        foreach ($a_users as $usr) {
            $user = (int) $usr;
            $ilDB->manipulateF(
                '
				DELETE FROM scorm_tracking
				WHERE user_id = %s
				AND obj_id = %s',
                array('integer', 'integer'),
                array($user, $this->getID())
            );

            $ilDB->manipulateF(
                '
				DELETE FROM sahs_user
				WHERE user_id = %s
				AND obj_id = %s',
                array('integer', 'integer'),
                array($user, $this->getID())
            );

            ilLPStatusWrapper::_updateStatus($this->getId(), $user);
        }
    }
}
