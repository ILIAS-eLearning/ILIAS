<?php

declare(strict_types=1);

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

/**
 * Class ilObjSCORM2004LearningModule
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilObjSCORM2004LearningModule extends ilObjSCORMLearningModule
{
    protected ilObjUser $user;

    protected ilTabsGUI $tabs;

    protected bool $import_sequencing = false;

    protected string $imsmanifestFile;

    public const CONVERT_XSL = './Modules/Scorm2004/templates/xsl/op/scorm12To2004.xsl';
    public const WRAPPER_HTML = './Modules/Scorm2004/scripts/converter/GenericRunTimeWrapper1.0_aadlc/GenericRunTimeWrapper.htm';
    public const WRAPPER_JS = './Modules/Scorm2004/scripts/converter/GenericRunTimeWrapper1.0_aadlc/SCOPlayerWrapper.js';

    /**
    * Constructor
    * @param	integer	reference_id or object_id
    * @param	boolean	treat the id as reference_id (true) or object_id (false)
    */
    public function __construct(int $a_id = 0, bool $a_call_by_reference = true)
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->error = $DIC["ilErr"];
        $this->db = $DIC->database();
        $this->log = ilLoggerFactory::getLogger('sc13');
        $this->user = $DIC->user();
        $this->tabs = $DIC->tabs();
        $this->type = "sahs";
        parent::__construct($a_id, $a_call_by_reference);
    }

    /**
     * Set import sequencing
     *
     * @param boolean $a_val import sequencing information
     */
    public function setImportSequencing(bool $a_val): void
    {
        $this->import_sequencing = $a_val;
    }

    /**
     * Get import sequencing
     *
     * @return boolean import sequencing information
     */
    public function getImportSequencing(): bool
    {
        return $this->import_sequencing;
    }

    /**
    * read manifest file
    */
    public function readObject(): string
    {
        global $DIC;
        $lng = $this->lng;
        $ilErr = $this->error;

        //check for json_encode,json_decode
        if (!function_exists('json_encode') || !function_exists('json_decode')) {
            $ilErr->raiseError($lng->txt('scplayer_phpmysqlcheck'), $ilErr->WARNING);
        }

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

        //check for SCORM 1.2
        $this->convert_1_2_to_2004($manifest_file);

        // start SCORM 2004 package parser/importer
        //        if ($this->getEditable()) {
//            return $newPack->il_importLM(
//                $this,
//                $this->getDataDirectory(),
//                $this->getImportSequencing()
//            );
//        } else {
        return (new ilSCORM13Package())->il_import($this->getDataDirectory(), $this->getId());
//        }
    }


    public function fixReload(): void
    {
        $out = file_get_contents($this->imsmanifestFile);
        $check = '/xmlns="http:\/\/www.imsglobal.org\/xsd\/imscp_v1p1"/';
        $replace = "xmlns=\"http://www.imsproject.org/xsd/imscp_rootv1p1p2\"";
        $out = preg_replace($check, $replace, $out);
        file_put_contents($this->imsmanifestFile, $out);
    }


    public function convert_1_2_to_2004(string $manifest): void
    {
        $ilDB = $this->db;
        $ilLog = $this->log;

        ##check manifest-file for version. Check for schemaversion as this is a required element for SCORM 2004
        ##accept 2004 3rd Edition an CAM 1.3 as valid schemas

        //set variables
        $this->packageFolder = $this->getDataDirectory();
        $this->imsmanifestFile = $manifest;
        $doc = new DomDocument();

        //fix reload errors before loading
        $this->fixReload();
        $doc->load($this->imsmanifestFile);
        $elements = $doc->getElementsByTagName("schemaversion");
        $schema = $elements->item(0)->nodeValue;
        if (strtolower(trim($schema)) === "cam 1.3" || strtolower(trim($schema)) === "2004 3rd edition" || strtolower(trim($schema)) === "2004 4th edition") {
            //no conversion
            $this->converted = false;
            return;
        }

        $this->converted = true;
        //convert to SCORM 2004

        //check for broken SCORM 1.2 manifest file (missing organization default-common error in a lot of manifest files)
        $organizations = $doc->getElementsByTagName("organizations");
        //first check if organizations is in manifest
        if ($organizations->item(0) == null) {
            die("organizations missing in manifest");
        }
        $default = $organizations->item(0)->getAttribute("default");
        if ($default == "" || $default == null) {
            //lookup identifier
            $organization = $doc->getElementsByTagName("organization");
            $item = $organization->item(0);
            if ($item !== null) {
                $ident = $item->getAttribute("identifier");
                $organizations->item(0)->setAttribute("default", $ident);
            }
        }

        //validate the fixed mainfest. If it's still not valid, don't transform an throw error


        //first copy wrappers
        $wrapperdir = $this->packageFolder . "/GenericRunTimeWrapper1.0_aadlc";
        if (!mkdir($wrapperdir) && !is_dir($wrapperdir)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $wrapperdir));
        }
        copy(self::WRAPPER_HTML, $wrapperdir . "/GenericRunTimeWrapper.htm");
        copy(self::WRAPPER_JS, $wrapperdir . "/SCOPlayerWrapper.js");

        //backup manifestfile
        $this->backupManifest = $this->packageFolder . "/imsmanifest.xml.back";
        $ret = copy($this->imsmanifestFile, $this->backupManifest);

        //transform manifest file
        $this->totransform = $doc;
        $ilLog->write("SCORM: about to transform to SCORM 2004");

        $xsl = new DOMDocument();
        $xsl->async = false;
        $xsl->load(self::CONVERT_XSL);
        $prc = new XSLTProcessor();
        $r = @$prc->importStyleSheet($xsl);

        file_put_contents($this->imsmanifestFile, $prc->transformToXML($this->totransform));

        $ilLog->write("SCORM: Transformation completed");
    }

    /**
     * Return the last access timestamp for a given user
     */
    public static function _lookupLastAccess(int $a_obj_id, int $a_usr_id): ?string
    {
        global $DIC;

        $ilDB = $DIC->database();

        $result = $ilDB->queryF(
            '
			SELECT MAX(c_timestamp) last_access 
			FROM cmi_node, cp_node 
			WHERE cmi_node.cp_node_id = cp_node.cp_node_id 
			AND cp_node.slm_id = %s
			AND user_id = %s
			GROUP BY c_timestamp',
            array('integer', 'integer'),
            array($a_obj_id, $a_usr_id)
        );
        if ($ilDB->numRows($result)) {
            $row = $ilDB->fetchAssoc($result);
            return (string) $row["last_access"];
        }

        return null;
    }

    public function deleteTrackingDataOfUsers(array $a_users): void
    {
        $ilDB = $this->db;
        ilChangeEvent::_deleteReadEventsForUsers($this->getId(), $a_users);

        foreach ($a_users as $user) {
            ilSCORM2004DeleteData::removeCMIDataForUserAndPackage($user, $this->getId());
            ilLPStatusWrapper::_updateStatus($this->getId(), $user);
        }
    }


    /**
     * get all tracked items of current user
     * @return array<int, array<string, mixed>>
     */
    public function getTrackedItems(): array
    {
        $ilUser = $this->user;
        $ilDB = $this->db;
        $ilUser = $this->user;

        $sco_set = $ilDB->queryF(
            '
		SELECT DISTINCT cmi_node.cp_node_id id
		FROM cp_node, cmi_node 
		WHERE slm_id = %s
		AND cp_node.cp_node_id = cmi_node.cp_node_id 
		ORDER BY cmi_node.cp_node_id ',
            array('integer'),
            array($this->getId())
        );

        $items = array();

        while ($sco_rec = $ilDB->fetchAssoc($sco_set)) {
            $item['id'] = $sco_rec["id"];
            $item['title'] = self::_lookupItemTitle($sco_rec["id"]);
            $items[] = $item;
        }
        return $items;
    }

    /**
     * @throws ilDateTimeException
     * @return array<int|string, mixed[]>
     */
    public function getTrackingDataAgg(int $a_user_id, ?bool $raw = false): array
    {
        $ilDB = $this->db;

        $scos = array();
        $data = array();
        //get all SCO's of this object

        $val_set = $ilDB->queryF(
            'SELECT cp_node_id FROM cp_node 
			WHERE nodename = %s
			AND cp_node.slm_id = %s',
            array('text', 'integer'),
            array('item',$this->getId())
        );
        while ($val_rec = $ilDB->fetchAssoc($val_set)) {
            $scos[] = $val_rec['cp_node_id'];
        }

        foreach ($scos as $sco) {
            $data_set = $ilDB->queryF(
                '
				SELECT c_timestamp last_access, total_time, success_status, completion_status,
					   c_raw, scaled, cp_node_id
				FROM cmi_node 
				WHERE cp_node_id = %s
				AND user_id = %s',
                array('integer','integer'),
                array($sco,$a_user_id)
            );

            while ($data_rec = $ilDB->fetchAssoc($data_set)) {
                if ($data_rec["success_status"] != "" && $data_rec["success_status"] !== "unknown") {
                    $status = $data_rec["success_status"];
                } else {
                    if ($data_rec["completion_status"] == "") {
                        $status = "unknown";
                    } else {
                        $status = $data_rec["completion_status"];
                    }
                }
                if (!$raw) {
                    $time = ilDatePresentation::secondsToString(self::_ISODurationToCentisec($data_rec["total_time"]) / 100);
                    $score = "";
                    if ($data_rec["c_raw"] != null) {
                        $score = $data_rec["c_raw"];
                        if ($data_rec["scaled"] != null) {
                            $score .= " = ";
                        }
                    }
                    if ($data_rec["scaled"] != null) {
                        $score .= ($data_rec["scaled"] * 100) . "%";
                    }
                    $title = self::_lookupItemTitle($data_rec["cp_node_id"]);
                    $last_access = ilDatePresentation::formatDate(new ilDateTime($data_rec['last_access'], IL_CAL_DATETIME));
                    $data[] = array("sco_id" => $data_rec["cp_node_id"],
                        "score" => $score, "time" => $time, "status" => $status,"last_access" => $last_access,"title" => $title);
                } else {
                    $data_rec["total_time"] = self::_ISODurationToCentisec($data_rec["total_time"]) / 100;
                    $data[$data_rec["cp_node_id"]] = $data_rec;
                }
            }
        }

        return $data;
    }

    /**
     * get number of atttempts for a certain user and package
     */
    public function getAttemptsForUser(int $a_user_id): int
    {
        $ilDB = $this->db;
        $val_set = $ilDB->queryF(
            'SELECT package_attempts FROM sahs_user WHERE user_id = %s AND obj_id = %s',
            array('integer','integer'),
            array($a_user_id, $this->getId())
        );

        $val_rec = $ilDB->fetchAssoc($val_set);

        if ($val_rec["package_attempts"] == null) {
            $val_rec["package_attempts"] = 0;
        }

        return (int) $val_rec["package_attempts"];
    }

    /**
     * get module version that tracking data for a user was recorded on
     */
    public function getModuleVersionForUser(int $a_user_id): string
    {
        $ilDB = $this->db;
        $val_set = $ilDB->queryF(
            'SELECT module_version FROM sahs_user WHERE user_id = %s AND obj_id = %s',
            array('integer','integer'),
            array($a_user_id, $this->getId())
        );

        $val_rec = $ilDB->fetchAssoc($val_set);

        if ($val_rec["module_version"] == null) {
            $val_rec["module_version"] = "";
        }
        return $val_rec["module_version"];
    }

    public function importSuccess(string $a_file): bool
    {
        $ilDB = $this->db;
        $ilUser = $this->user;
        $scos = array();
        $olp = ilObjectLP::getInstance($this->getId());
        $collection = $olp->getCollectionInstance();
        if ($collection) {
            $scos = $collection->getItems();
        }

        $fhandle = fopen($a_file, "rb");//changed from r to rb

        $obj_id = $this->getID();
        $users = array();
        $usersToDelete = array();
        $fields = fgetcsv($fhandle, 4096, ';');
        while (($csv_rows = fgetcsv($fhandle, 4096, ";")) !== false) {
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
                $user_id = $data["user"];
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
                    if (is_numeric($data["Status"])) {
                        $status = $data["Status"];
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
                } elseif ($data['percentageCompletedSCOs']) {
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
                        $res = $ilDB->queryF(
                            '
							SELECT completion_status, success_status, user_id FROM cmi_node WHERE cp_node_id = %s AND user_id  = %s',
                            array('integer','integer'),
                            array($sco_id,$user_id)
                        );

                        if (!$ilDB->numRows($res)) {
                            $nextId = $ilDB->nextId('cmi_node');
                            $val_set = $ilDB->manipulateF(
                                'INSERT INTO cmi_node 
							(cp_node_id,user_id,completion_status,c_timestamp,cmi_node_id) 
							VALUES(%s,%s,%s,%s,%s)',
                                array('integer','integer','text','timestamp','integer'),
                                array($sco_id,$user_id,'completed',$last_access,$nextId)
                            );
                        } else {
                            $doUpdate = false;
                            while ($row = $ilDB->fetchAssoc($res)) {
                                if (($row["completion_status"] === "completed" && $row["success_status"] !== "failed") || $row["success_status"] === "passed") {
                                    if ($doUpdate != true) {
                                        $doUpdate = false;
                                    } //note for issue if there are 2 entries for same sco_id
                                } else {
                                    $doUpdate = true;
                                }
                            }
                            if ($doUpdate == true) {
                                $ilDB->update(
                                    'cmi_node',
                                    array(
                                        'completion_status' => array('text', 'completed'),
                                        'success_status' => array('text', ''),
                                        'suspend_data' => array('text', ''),
                                        'c_timestamp' => array('timestamp', $last_access)
                                    ),
                                    array(
                                        'user_id' => array('integer', $user_id),
                                        'cp_node_id' => array('integer', $sco_id)
                                    )
                                );
                            }
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

    /**
     * convert ISO 8601 Timeperiods to centiseconds
     */
    public static function _ISODurationToCentisec(string $str): float
    {
        $aV = array(0, 0, 0, 0, 0, 0);
        $bErr = false;
        $bTFound = false;
        if (strpos($str, "P") != 0) {
            $bErr = true;
        }
        if (!$bErr) {
            $aT = array("Y", "M", "D", "H", "M", "S");
            $p = 0;
            $i = 0;
            $str = substr($str, 1);
            for ($i = 0, $max = count($aT); $i < $max; $i++) {
                if (strpos($str, "T") === 0) {
                    $str = substr($str, 1);
                    $i = max($i, 3);
                    $bTFound = true;
                }
                $p = strpos($str, $aT[$i]);

                if ($p > -1) {
                    if ($i == 1 && strpos($str, "T") > -1 && strpos($str, "T") < $p) {
                        continue;
                    }
                    if ($aT[$i] === "S") {
                        $aV[$i] = substr($str, 0, $p);
                    } else {
                        $aV[$i] = intval(substr($str, 0, $p));
                    }
                    if (!is_numeric($aV[$i])) {
                        $bErr = true;
                        break;
                    }

                    if ($i > 2 && !$bTFound) {
                        $bErr = true;
                        break;
                    }
                    $str = substr($str, $p + 1);
                }
            }
            if (!$bErr && strlen($str) != 0) {
                $bErr = true;
            }
        }

        if ($bErr) {
            return 0;
        }
        return $aV[0] * 3_155_760_000 + $aV[1] * 262_980_000 + $aV[2] * 8_640_000 + $aV[3] * 360000 + $aV[4] * 6000 + round($aV[5] * 100);
    }

    public static function getQuantityOfSCOs(int $a_slm_id): int
    {
        global $DIC;
        $val_set = $DIC->database()->queryF(
            '
		SELECT 	distinct(cp_node.cp_node_id) FROM cp_node,cp_resource,cp_item 
		WHERE  	cp_item.cp_node_id = cp_node.cp_node_id 
		AND 	cp_item.resourceid = cp_resource.id 
		AND scormtype = %s
		AND nodename = %s
		AND cp_node.slm_id = %s ',
            array('text','text','integer'),
            array('sco','item',$a_slm_id)
        );
        return $DIC->database()->numRows($val_set);
    }

    /**
    * Get the completion of a SCORM module for a given user
    * @return boolean Completion status
    */
    public static function _getCourseCompletionForUser(int $a_id, int $a_user): bool
    {
        global $DIC;

        $ilDB = $DIC->database();
        $ilUser = $DIC->user();
        $scos = array();
        //get all SCO's of the object

        $val_set = $ilDB->queryF(
            '
	 	SELECT cp_node.cp_node_id FROM cp_node,cp_resource,cp_item 
	 	WHERE cp_item.cp_node_id = cp_node.cp_node_id 
	 	AND cp_item.resourceid = cp_resource.id 
	 	AND scormtype = %s
	 	AND nodename =  %s 
	 	AND cp_node.slm_id =  %s',
            array('text','text','integer'),
            array('sco' ,'item',$a_id)
        );
        while ($val_rec = $ilDB->fetchAssoc($val_set)) {
            $scos[] = $val_rec['cp_node_id'];
        }

        $scos_c = $scos;
        //copy SCO_array
        //check if all SCO's are completed
        foreach ($scos as $i => $value) {
            $val_set = $ilDB->queryF(
                '
				SELECT * FROM cmi_node 
				WHERE (user_id= %s
				AND cp_node_id= %s
				AND (completion_status = %s OR success_status = %s))',
                array('integer','integer','text','text'),
                array($a_user, $value,'completed','passed')
            );

            if ($ilDB->numRows($val_set) > 0) {
                //delete from array
                $key = array_search($value, $scos_c);
                unset($scos_c[$key]);
            }
        }
        //check for completion
        if (count($scos_c) == 0) {
            $completion = true;
        } else {
            $completion = false;
        }
        return $completion;
    }

    /**
    * Get the Unique Scaled Score of a course
    * Conditions: Only one SCO may set cmi.score.scaled
    * @return float scaled score, -1 if not unique
    */
    public static function _getUniqueScaledScoreForUser(int $a_id, int $a_user): float
    {
        global $DIC;

        $ilDB = $DIC->database();
        $ilUser = $DIC->user();
        $scos = array();

        $val_set = $ilDB->queryF(
            "SELECT cp_node.cp_node_id FROM cp_node,cp_resource,cp_item WHERE" .
            " cp_item.cp_node_id=cp_node.cp_node_id AND cp_item.resourceId = cp_resource.id AND scormType='sco' AND nodeName='item' AND cp_node.slm_id = %s GROUP BY cp_node.cp_node_id",
            array('integer'),
            array($a_id)
        );
        while ($val_rec = $ilDB->fetchAssoc($val_set)) {
            $scos[] = $val_rec['cp_node_id'];
        }
        $set = 0;   //numbers of SCO that set cmi.score.scaled
        $scaled = null;
        foreach ($scos as $i => $iValue) {
            $val_set = $ilDB->queryF(
                "SELECT scaled FROM cmi_node WHERE (user_id = %s AND cp_node_id = %s)",
                array('integer', 'integer'),
                array($a_user, $scos[$i])
            );
            if ($val_set->numRows() > 0) {
                $val_rec = $ilDB->fetchAssoc($val_set);
                if ($val_rec['scaled'] != null) {
                    $set++;
                    $scaled = $val_rec['scaled'];
                }
            }
        }
        return ($set == 1) ? $scaled : -1;
    }

    /**
     * get all tracking items of scorm object
     * currently a for learning progress only
     * @return array<int, array<string, mixed>>
     */
    public static function _getTrackingItems(int $a_obj_id): array
    {
        global $DIC;

        $ilDB = $DIC->database();


        $item_set = $ilDB->queryF(
            '
			SELECT cp_item.*  FROM cp_node, cp_item WHERE slm_id = %s
			AND cp_node.cp_node_id = cp_item.cp_node_id 
			ORDER BY cp_node.cp_node_id ',
            array('integer'),
            array($a_obj_id)
        );

        $items = array();
        while ($item_rec = $ilDB->fetchAssoc($item_set)) {
            $s2 = $ilDB->queryF(
                '
				SELECT cp_resource.* FROM cp_node, cp_resource 
				WHERE slm_id = %s
				AND cp_node.cp_node_id = cp_resource.cp_node_id 
				AND cp_resource.id = %s ',
                array('integer','text'),
                array($a_obj_id,$item_rec["resourceid"])
            );


            if ($res = $ilDB->fetchAssoc($s2)) {
                if ($res["scormtype"] === "sco") {
                    $items[] = array("id" => $item_rec["cp_node_id"],
                        "title" => $item_rec["title"]);
                }
            }
        }

        return $items;
    }

    /**
     * @return string|bool
     */
    public static function _getStatus(int $a_obj_id, int $a_user_id)
    {
        global $DIC;

        $ilDB = $DIC->database();

        $status_set = $ilDB->queryF(
            '
			SELECT * FROM cmi_gobjective 
			WHERE scope_id = %s
			AND objective_id = %s
			AND user_id = %s',
            array('integer','text','integer'),
            array($a_obj_id,'-course_overall_status-',$a_user_id)
        );

        if ($status_rec = $ilDB->fetchAssoc($status_set)) {
            return $status_rec["status"];
        }

        return false;
    }

    /**
     * @return string|bool
     */
    public static function _getSatisfied(int $a_obj_id, int $a_user_id)
    {
        global $DIC;

        $ilDB = $DIC->database();


        $status_set = $ilDB->queryF(
            '
			SELECT * FROM cmi_gobjective 
			WHERE scope_id = %s
			AND objective_id = %s
			AND user_id = %s',
            array('integer','text','integer'),
            array($a_obj_id,'-course_overall_status-',$a_user_id)
        );

        if ($status_rec = $ilDB->fetchAssoc($status_set)) {
            return $status_rec["satisfied"];
        }

        return false;
    }

    /**
     * @return float|bool
     */
    public static function _getMeasure(int $a_obj_id, int $a_user_id)
    {
        global $DIC;

        $ilDB = $DIC->database();

        $status_set = $ilDB->queryF(
            '
			SELECT * FROM cmi_gobjective 
			WHERE scope_id = %s
			AND objective_id = %s
			AND user_id = %s',
            array('integer','text','integer'),
            array($a_obj_id,'-course_overall_status-',$a_user_id)
        );

        if ($status_rec = $ilDB->fetchAssoc($status_set)) {
            return (float) $status_rec["measure"];
        }

        return false;
    }

    public static function _lookupItemTitle(int $a_node_id): string
    {
        global $DIC;

        $ilDB = $DIC->database();

        $r = $ilDB->queryF(
            '
			SELECT * FROM cp_item
			WHERE cp_node_id = %s',
            array('integer'),
            array($a_node_id)
        );

        if ($i = $ilDB->fetchAssoc($r)) {
            return $i["title"];
        }
        return "";
    }

    /**
     * Returns score.max for the learning module, refered to the last sco where score.max is set.
     */
    public static function _getMaxScoreForUser(int $a_id, int $a_user): ?float
    {
        global $DIC;

        $ilDB = $DIC->database();

        $scos = array();

        $result = $ilDB->query(
            'SELECT cp_node.cp_node_id '
           . 'FROM cp_node, cp_resource, cp_item '
           . 'WHERE cp_item.cp_node_id = cp_node.cp_node_id '
           . 'AND cp_item.resourceId = cp_resource.id '
           . 'AND scormType = ' . $ilDB->quote('sco', 'text') . ' '
           . 'AND nodeName = ' . $ilDB->quote('item', 'text') . ' '
           . 'AND cp_node.slm_id = ' . $ilDB->quote($a_id, 'integer') . ' '
           . 'GROUP BY cp_node.cp_node_id'
        );

        while ($row = $ilDB->fetchAssoc($result)) {
            $scos[] = $row['cp_node_id'];
        }

        $set = 0; //numbers of SCO that set cmi.score.scaled
        $max = null;
        foreach ($scos as $i => $value) {
            $res = $ilDB->queryF(
                'SELECT c_max FROM cmi_node WHERE (user_id = %s AND cp_node_id = %s)',
                array('integer', 'integer'),
                array($a_user, $value)
            );

            if ($ilDB->numRows($res) > 0) {
                $row = $ilDB->fetchAssoc($res);
                if ($row['c_max'] != null) {
                    $set++;
                    $max = $row['c_max'];
                }
            }
        }
        return ($set == 1) ? $max : null;
    }

    /**
     * @return array<string, mixed>
     */
    public static function _getScores2004ForUser(int $a_cp_node_id, int $a_user): array
    {
        global $DIC;

        $ilDB = $DIC->database();
        $retAr = array("raw" => null, "max" => null, "scaled" => null);
        $val_set = $ilDB->queryF(
            "SELECT c_raw, c_max, scaled FROM cmi_node WHERE (user_id = %s AND cp_node_id = %s)",
            array('integer', 'integer'),
            array($a_user, $a_cp_node_id)
        );
        if ($val_set->numRows() > 0) {
            $val_rec = $ilDB->fetchAssoc($val_set);
            $retAr["raw"] = $val_rec['c_raw'];
            $retAr["max"] = $val_rec['c_max'];
            $retAr["scaled"] = $val_rec['scaled'];
            if ($val_rec['scaled'] == null && $val_rec['c_raw'] != null && $val_rec['c_max'] != null) {
                $retAr["scaled"] = ($val_rec['c_raw'] / $val_rec['c_max']);
            }
        }
        return $retAr;
    }
}
