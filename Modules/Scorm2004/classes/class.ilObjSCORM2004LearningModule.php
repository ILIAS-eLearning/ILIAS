<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "./Modules/ScormAicc/classes/class.ilObjSCORMLearningModule.php";

/**
* Class ilObjSCORM2004LearningModule
*
* @author Alex Killing <alex.killing@gmx.de>
* $Id: class.ilObjSCORMLearningModule.php 13123 2007-01-29 13:57:16Z smeyer $
*
* @ingroup ModulesScormAicc
*/
class ilObjSCORM2004LearningModule extends ilObjSCORMLearningModule
{
    /**
     * @var ilObjUser
     */
    protected $user;

    /**
     * @var ilTabsGUI
     */
    protected $tabs;

    public $validator;
    //	var $meta_data;
    
    const CONVERT_XSL = './Modules/Scorm2004/templates/xsl/op/scorm12To2004.xsl';
    const WRAPPER_HTML = './Modules/Scorm2004/scripts/converter/GenericRunTimeWrapper1.0_aadlc/GenericRunTimeWrapper.htm';
    const WRAPPER_JS = './Modules/Scorm2004/scripts/converter/GenericRunTimeWrapper1.0_aadlc/SCOPlayerWrapper.js';

    /**
    * Constructor
    * @access	public
    * @param	integer	reference_id or object_id
    * @param	boolean	treat the id as reference_id (true) or object_id (false)
    */
    public function __construct($a_id = 0, $a_call_by_reference = true)
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->error = $DIC["ilErr"];
        $this->db = $DIC->database();
        $this->log = $DIC["ilLog"];
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
    public function setImportSequencing($a_val)
    {
        $this->import_sequencing = $a_val;
    }
    
    /**
     * Get import sequencing
     *
     * @return boolean import sequencing information
     */
    public function getImportSequencing()
    {
        return $this->import_sequencing;
    }
    
    /**
    * Validate all XML-Files in a SCOM-Directory
    *
    * @access       public
    * @return       boolean true if all XML-Files are wellfomred and valid
    */
    public function validate($directory)
    {
        //$this->validator = new ilObjSCORMValidator($directory);
        //$returnValue = $this->validator->validate();
        return true;
    }

    /**
    * read manifest file
    * @access	public
    */
    public function readObject()
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
            return;
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
                    fputs($f_write_handler, utf8_encode($zeile));
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
                return;
            }
        } else {
            // check whether file starts with BOM (that confuses some sax parsers, see bug #1795)
            $hmani = fopen($manifest_file, "r");
            $start = fread($hmani, 3);
            if (strtolower(bin2hex($start)) == "efbbbf") {
                $f_write_handler = fopen($manifest_file . ".new", "w");
                while (!feof($hmani)) {
                    $n = fread($hmani, 900);
                    fputs($f_write_handler, $n);
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

        //validate the XML-Files in the SCORM-Package
        if ($_POST["validate"] == "y") {
            if (!$this->validate($this->getDataDirectory())) {
                $ilErr->raiseError(
                    "<b>Validation Error(s):</b><br>" . $this->getValidationSummary(),
                    $ilErr->WARNING
                );
            }
        }
            
        
        //check for SCORM 1.2
        $this->convert_1_2_to_2004($manifest_file);
        
        // start SCORM 2004 package parser/importer
        include_once("./Modules/Scorm2004/classes/ilSCORM13Package.php");
        $newPack = new ilSCORM13Package();
        if ($this->getEditable()) {
            return $newPack->il_importLM(
                $this,
                $this->getDataDirectory(),
                $this->getImportSequencing()
            );
        } else {
            return $newPack->il_import($this->getDataDirectory(), $this->getId(), $DIC["ilias"], $_POST["validate"]);
        }
    }


    public function fixReload()
    {
        $out = file_get_contents($this->imsmanifestFile);
        $check = '/xmlns="http:\/\/www.imsglobal.org\/xsd\/imscp_v1p1"/';
        $replace = "xmlns=\"http://www.imsproject.org/xsd/imscp_rootv1p1p2\"";
        $out = preg_replace($check, $replace, $out);
        file_put_contents($this->imsmanifestFile, $out);
    }
    
    
    public function convert_1_2_to_2004($manifest)
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
        if (strtolower(trim($schema)) == "cam 1.3" || strtolower(trim($schema)) == "2004 3rd edition" || strtolower(trim($schema)) == "2004 4th edition") {
            //no conversion
            $this->converted = false;
            return true;
        } else {
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
                $ident = $organization->item(0)->getAttribute("identifier");
                $organizations->item(0)->setAttribute("default", $ident);
            }
            
            //validate the fixed mainfest. If it's still not valid, don't transform an throw error
            
                    
            //first copy wrappers
            $wrapperdir = $this->packageFolder . "/GenericRunTimeWrapper1.0_aadlc";
            mkdir($wrapperdir);
            copy(self::WRAPPER_HTML, $wrapperdir . "/GenericRunTimeWrapper.htm");
            copy(self::WRAPPER_JS, $wrapperdir . "/SCOPlayerWrapper.js");
            
            //backup manifestfile
            $this->backupManifest = $this->packageFolder . "/imsmanifest.xml.back";
            $ret = copy($this->imsmanifestFile, $this->backupManifest);
            
            //transform manifest file
            $this->totransform = $doc;
            $ilLog->write("SCORM: about to transform to SCORM 2004");
            
            $xsl = new DOMDocument;
            $xsl->async = false;
            $xsl->load(self::CONVERT_XSL);
            $prc = new XSLTProcessor;
            $r = @$prc->importStyleSheet($xsl);
            
            file_put_contents($this->imsmanifestFile, $prc->transformToXML($this->totransform));

            $ilLog->write("SCORM: Transformation completed");
            return true;
        }
    }
    
    /**
    * Return the last access timestamp for a given user
    *
    * @param	int		$a_obj_id		object id
    * @param	int		$user_id		user id
    * @return timestamp
    */
    public static function _lookupLastAccess($a_obj_id, $a_usr_id)
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
            return $row["last_access"];
        }
        
        return "";
    }

    /**
    * get all tracked items of current user
    */
    // function getTrackedUsers($a_search)
    // {
    // global $DIC;
    // $ilUser = $DIC['ilUser'];
    // $ilDB = $DIC['ilDB'];
    // $ilUser = $DIC['ilUser'];

    // $sco_set = $ilDB->queryF('
    // SELECT DISTINCT user_id,MAX(c_timestamp) last_access
    // FROM cmi_node, cp_node
    // WHERE cmi_node.cp_node_id = cp_node.cp_node_id
    // AND cp_node.slm_id = %s
    // GROUP BY user_id',
    // array('integer'),
    // array($this->getId()));
        
    // $items = array();
    // $temp = array();
        
    // while($sco_rec = $ilDB->fetchAssoc($sco_set))
    // {
    // $name = ilObjUser::_lookupName($sco_rec["user_id"]);
    // if ($sco_rec['last_access'] != 0) {
    // //				$sco_rec['last_access'] = $sco_rec['last_access'];
    // } else {
    // $sco_rec['last_access'] = "";
    // }
    // if (ilObject::_exists($sco_rec['user_id'])  && ilObject::_lookUpType($sco_rec['user_id'])=="usr" ) {
    // $user = new ilObjUser($sco_rec['user_id']);
    // $temp = array("user_full_name" => $name["lastname"].", ".
    // $name["firstname"]." [".ilObjUser::_lookupLogin($sco_rec["user_id"])."]",
    // "user_id" => $sco_rec["user_id"],"last_access" => $sco_rec['last_access'],
    // "version"=> $this->getModuleVersionForUser($sco_rec["user_id"]),
    // "attempts" => $this->getAttemptsForUser($sco_rec["user_id"]),
    // "username" =>  $user->getLastname().", ".$user->getFirstname()
    // );
    // if ($a_search != "" && (strpos(strtolower($user->getLastname()), strtolower($a_search)) !== false || strpos(strtolower($user->getFirstname()), strtolower($a_search)) !== false ) ) {
    // $items[] = $temp;
    // } else if ($a_search == "") {
    // $items[] = $temp;
    // }
    // }
    // }

    // return $items;
    // }

    public function deleteTrackingDataOfUsers($a_users)
    {
        $ilDB = $this->db;
        include_once("./Modules/Scorm2004/classes/class.ilSCORM2004DeleteData.php");
        include_once("./Services/Tracking/classes/class.ilLPStatusWrapper.php");
        include_once("./Services/Tracking/classes/class.ilChangeEvent.php");
        ilChangeEvent::_deleteReadEventsForUsers($this->getId(), $a_users);
        
        foreach ($a_users as $user) {
            ilSCORM2004DeleteData::removeCMIDataForUserAndPackage($user, $this->getId());
            ilLPStatusWrapper::_updateStatus($this->getId(), $user);
        }
    }
    
    
    /**
    * get all tracked items of current user
    */
    public function getTrackedItems()
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
            $items[count($items)] = $item;
        }
        return $items;
    }
    
    
    public function getTrackingDataAgg($a_user_id, $raw = false)
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
            array_push($scos, $val_rec['cp_node_id']);
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
                if ($data_rec["success_status"] != "" && $data_rec["success_status"] != "unknown") {
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
    public function getAttemptsForUser($a_user_id)
    {
        $ilDB = $this->db;
        $val_set = $ilDB->queryF(
            'SELECT package_attempts FROM sahs_user WHERE user_id = %s AND obj_id = %s',
            array('integer','integer'),
            array($a_user_id, $this->getId())
        );

        $val_rec = $ilDB->fetchAssoc($val_set);

        if ($val_rec["package_attempts"] == null) {
            $val_rec["package_attempts"] = "";
        }

        return $val_rec["package_attempts"];
    }
    
    
    /**
    * get module version that tracking data for a user was recorded on
    */
    public function getModuleVersionForUser($a_user_id)
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
    
    
    // function exportSelected($a_exportall = 0, $a_user = array())
    // {
    // include_once("./Modules/Scorm2004/classes/class.ilSCORM2004TrackingItemsExport.php");
    // ilSCORM2004TrackingItemsExport::exportSelected($a_exportall = 0, $a_user = array());
    // }
    
    
    
    public function importSuccess($a_file)
    {
        $ilDB = $this->db;
        $ilUser = $this->user;
        include_once("./Services/Tracking/classes/class.ilLPStatus.php");
        $scos = array();
        //get all SCO's of this object ONLY RELEVANT!
        include_once './Services/Object/classes/class.ilObjectLP.php';
        $olp = ilObjectLP::getInstance($this->getId());
        $collection = $olp->getCollectionInstance();
        if ($collection) {
            $scos = $collection->getItems();
        }
        
        $fhandle = fopen($a_file, "r");

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
                                if (($row["completion_status"] == "completed" && $row["success_status"] != "failed") || $row["success_status"] == "passed") {
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
        include_once("./Services/Tracking/classes/class.ilLPStatusWrapper.php");
        ilLPStatusWrapper::_refreshStatus($this->getId(), $users);

        return 0;
    }
    
    /**
    * convert ISO 8601 Timeperiods to centiseconds
    * ta
    *
    * @access static
    */
    public static function _ISODurationToCentisec($str)
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
            for ($i = 0; $i < count($aT); $i++) {
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
                    if ($aT[$i] == "S") {
                        $aV[$i] = substr($str, 0, $p);
                    } else {
                        $aV[$i] = intval(substr($str, 0, $p));
                    }
                    if (!is_numeric($aV[$i])) {
                        $bErr = true;
                        break;
                    } elseif ($i > 2 && !$bTFound) {
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
            return;
        }
        return $aV[0] * 3155760000 + $aV[1] * 262980000 + $aV[2] * 8640000 + $aV[3] * 360000 + $aV[4] * 6000 + round($aV[5] * 100);
    }
    
    public function getCourseCompletionForUser($a_user)
    {
        $ilDB = $this->db;
        $ilUser = $this->user;
        
        $scos = array();
        //get all SCO's of this object

        $val_set = $ilDB->queryF(
            '
		SELECT 	cp_node.cp_node_id FROM cp_node,cp_resource,cp_item 
		WHERE  	cp_item.cp_node_id = cp_node.cp_node_id 
		AND 	cp_item.resourceid = cp_resource.id 
		AND scormtype = %s
		AND nodename = %s
		AND cp_node.slm_id = %s ',
            array('text','text','integer'),
            array('sco','item',$this->getId())
        );
        
        while ($val_rec = $ilDB->fetchAssoc($val_set)) {
            array_push($scos, $val_rec['cp_node_id']);
        }
        
        
        $scos_c = $scos;
        //copy SCO_array
        //check if all SCO's are completed
        for ($i = 0;$i < count($scos);$i++) {
            $val_set = $ilDB->queryF(
                '
				SELECT * FROM cmi_node 
				WHERE (user_id= %s
				AND cp_node_id= %s
				AND (completion_status=%s OR success_status=%s))',
                array('integer','integer','text', 'text'),
                array($a_user,$scos[$i],'completed','passed')
            );
            
            if ($ilDB->numRows($val_set) > 0) {
                //delete from array
                $key = array_search($scos[$i], $scos_c);
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
    * Get the completion of a SCORM module for a given user
    * @param int $a_id Object id
    * @param int $a_user User id
    * @return boolean Completion status
    */
    public static function _getCourseCompletionForUser($a_id, $a_user)
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
            array_push($scos, $val_rec['cp_node_id']);
        }
        
        $scos_c = $scos;
        //copy SCO_array
        //check if all SCO's are completed
        for ($i = 0;$i < count($scos);$i++) {
            $val_set = $ilDB->queryF(
                '
				SELECT * FROM cmi_node 
				WHERE (user_id= %s
				AND cp_node_id= %s
				AND (completion_status = %s OR success_status = %s))',
                array('integer','integer','text','text'),
                array($a_user,$scos[$i],'completed','passed')
            );
            
            if ($ilDB->numRows($val_set) > 0) {
                //delete from array
                $key = array_search($scos[$i], $scos_c);
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
    * @param int $a_id Object id
    * @param int $a_user User id
    * @return float scaled score, -1 if not unique
    */
    public static function _getUniqueScaledScoreForUser($a_id, $a_user)
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
            array_push($scos, $val_rec['cp_node_id']);
        }
        $set = 0;   //numbers of SCO that set cmi.score.scaled
        $scaled = null;
        for ($i = 0;$i < count($scos);$i++) {
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
        $retVal = ($set == 1) ? $scaled : null ;
        return $retVal;
    }

    /**
    * get all tracking items of scorm object
    *
    * currently a for learning progress only
    */
    public static function _getTrackingItems($a_obj_id)
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
                if ($res["scormtype"] == "sco") {
                    $items[] = array("id" => $item_rec["cp_node_id"],
                        "title" => $item_rec["title"]);
                }
            }
        }

        return $items;
    }

    public static function _getStatus($a_obj_id, $a_user_id)
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

    public static function _getSatisfied($a_obj_id, $a_user_id)
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

    public static function _getMeasure($a_obj_id, $a_user_id)
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
            return $status_rec["measure"];
        }

        return false;
    }
    
    public static function _lookupItemTitle($a_node_id)
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
     * Create Scorm 2004 Tree used by Editor
     */
    public function createScorm2004Tree()
    {
        include_once("./Modules/Scorm2004/classes/class.ilSCORM2004Tree.php");
        $this->slm_tree = new ilSCORM2004Tree($this->getId());

        //$this->slm_tree = new ilTree($this->getId());
        //$this->slm_tree->setTreeTablePK("slm_id");
        //$this->slm_tree->setTableNames('sahs_sc13_tree', 'sahs_sc13_tree_node');
        $this->slm_tree->addTree($this->getId(), 1);
        
        //add seqinfo for rootNode
        include_once("./Modules/Scorm2004/classes/seq_editor/class.ilSCORM2004Sequencing.php");
        $seq_info = new ilSCORM2004Sequencing($this->getId(), true);
        $seq_info->insert();
    }

    public function getTree()
    {
        $this->slm_tree = new ilTree($this->getId());
        $this->slm_tree->setTreeTablePK("slm_id");
        $this->slm_tree->setTableNames('sahs_sc13_tree', 'sahs_sc13_tree_node');
        return $this->slm_tree;
    }
    
    public function getSequencingSettings()
    {
        $ilTabs = $this->tabs;
        $ilTabs->setTabActive("sequencing");
        
        include_once("./Modules/Scorm2004/classes/seq_editor/class.ilSCORM2004Sequencing.php");
        $control_settings = new ilSCORM2004Sequencing($this->getId(), true);
        
        return $control_settings;
    }

    public function updateSequencingSettings()
    {
        include_once("./Modules/Scorm2004/classes/seq_editor/class.ilSCORM2004Sequencing.php");
        
        $control_settings = new ilSCORM2004Sequencing($this->getId(), true);
        $control_settings->setChoice(ilUtil::yn2tf($_POST["choice"]));
        $control_settings->setFlow(ilUtil::yn2tf($_POST["flow"]));
        $control_settings->setForwardOnly(ilUtil::yn2tf($_POST["forwardonly"]));
        $control_settings->insert();
        
        return true;
    }

    /**
    * Execute Drag Drop Action
    *
    * @param	string	$source_id		Source element ID
    * @param	string	$target_id		Target element ID
    * @param	string	$first_child	Insert as first child of target
    * @param	string	$movecopy		Position ("move" | "copy")
    */
    public function executeDragDrop($source_id, $target_id, $first_child, $as_subitem = false, $movecopy = "move")
    {
        $this->slm_tree = new ilTree($this->getId());
        $this->slm_tree->setTableNames('sahs_sc13_tree', 'sahs_sc13_tree_node');
        $this->slm_tree->setTreeTablePK("slm_id");
        
        require_once("./Modules/Scorm2004/classes/class.ilSCORM2004NodeFactory.php");
        
        $source_obj = ilSCORM2004NodeFactory::getInstance($this, $source_id, true);
        //$source_obj->setLMId($this->getId());

        if (!$first_child) {
            $target_obj = ilSCORM2004NodeFactory::getInstance($this, $target_id, true);
            //$target_obj->setLMId($this->getId());
            $target_parent = $this->slm_tree->getParentId($target_id);
        }
        //echo "-".$source_obj->getType()."-";
        // handle pages
        if ($source_obj->getType() == "page") {
            if ($this->slm_tree->isInTree($source_obj->getId())) {
                $node_data = $this->slm_tree->getNodeData($source_obj->getId());

                // cut on move
                if ($movecopy == "move") {
                    $parent_id = $this->slm_tree->getParentId($source_obj->getId());
                    $this->slm_tree->deleteTree($node_data);

                    // write history entry
/*					require_once("./Services/History/classes/class.ilHistory.php");
                    ilHistory::_createEntry($source_obj->getId(), "cut",
                        array(ilLMObject::_lookupTitle($parent_id), $parent_id),
                        $this->getType().":pg");
                    ilHistory::_createEntry($parent_id, "cut_page",
                        array(ilLMObject::_lookupTitle($source_obj->getId()), $source_obj->getId()),
                        $this->getType().":st");
*/
                }
                /*				else			// this is not implemented here
                                {
                                    // copy page
                                    $new_page =& $source_obj->copy();
                                    $source_id = $new_page->getId();
                                    $source_obj =& $new_page;
                                }
                */

                // paste page
                if (!$this->slm_tree->isInTree($source_obj->getId())) {
                    if ($first_child) {			// as first child
                        $target_pos = IL_FIRST_NODE;
                        $parent = $target_id;
                    } elseif ($as_subitem) {		// as last child
                        $parent = $target_id;
                        $target_pos = IL_FIRST_NODE;
                        $pg_childs = $this->slm_tree->getChildsByType($parent, "page");
                        if (count($pg_childs) != 0) {
                            $target_pos = $pg_childs[count($pg_childs) - 1]["obj_id"];
                        }
                    } else {						// at position
                        $target_pos = $target_id;
                        $parent = $target_parent;
                    }

                    // insert page into tree
                    $this->slm_tree->insertNode(
                        $source_obj->getId(),
                        $parent,
                        $target_pos
                    );

                    // write history entry
/*					if ($movecopy == "move")
                    {
                        // write history comments
                        include_once("./Services/History/classes/class.ilHistory.php");
                        ilHistory::_createEntry($source_obj->getId(), "paste",
                            array(ilLMObject::_lookupTitle($parent), $parent),
                            $this->getType().":pg");
                        ilHistory::_createEntry($parent, "paste_page",
                            array(ilLMObject::_lookupTitle($source_obj->getId()), $source_obj->getId()),
                            $this->getType().":st");
                    }
*/
                }
            }
        }

        // handle scos
        if ($source_obj->getType() == "sco" || $source_obj->getType() == "ass") {
            //echo "2";
            $source_node = $this->slm_tree->getNodeData($source_id);
            $subnodes = $this->slm_tree->getSubtree($source_node);

            // check, if target is within subtree
            foreach ($subnodes as $subnode) {
                if ($subnode["obj_id"] == $target_id) {
                    return;
                }
            }

            $target_pos = $target_id;

            if ($first_child) {		// as first sco
                $target_pos = IL_FIRST_NODE;
                $target_parent = $target_id;
                
                $pg_childs = $this->slm_tree->getChildsByType($target_parent, "page");
                if (count($pg_childs) != 0) {
                    $target_pos = $pg_childs[count($pg_childs) - 1]["obj_id"];
                }
            } elseif ($as_subitem) {		// as last sco
                $target_parent = $target_id;
                $target_pos = IL_FIRST_NODE;
                $childs = $this->slm_tree->getChilds($target_parent);
                if (count($childs) != 0) {
                    $target_pos = $childs[count($childs) - 1]["obj_id"];
                }
            }

            // delete source tree
            if ($movecopy == "move") {
                $this->slm_tree->deleteTree($source_node);
            }
            /*			else
                        {
                            // copy chapter (incl. subcontents)
                            $new_chapter =& $source_obj->copy($this->slm_tree, $target_parent, $target_pos);
                        }
            */

            if (!$this->slm_tree->isInTree($source_id)) {
                $this->slm_tree->insertNode($source_id, $target_parent, $target_pos);

                // insert moved tree
                if ($movecopy == "move") {
                    foreach ($subnodes as $node) {
                        if ($node["obj_id"] != $source_id) {
                            $this->slm_tree->insertNode($node["obj_id"], $node["parent"]);
                        }
                    }
                }
            }

            // check the tree
//			$this->checkTree();
        }

        // handle chapters
        if ($source_obj->getType() == "chap") {
            //echo "2";
            $source_node = $this->slm_tree->getNodeData($source_id);
            $subnodes = $this->slm_tree->getSubtree($source_node);

            // check, if target is within subtree
            foreach ($subnodes as $subnode) {
                if ($subnode["obj_id"] == $target_id) {
                    return;
                }
            }

            $target_pos = $target_id;

            if ($first_child) {		// as first chapter
                $target_pos = IL_FIRST_NODE;
                $target_parent = $target_id;
                
            //$sco_childs = $this->slm_tree->getChildsByType($target_parent, "sco");
                //if (count($sco_childs) != 0)
                //{
                //	$target_pos = $sco_childs[count($sco_childs) - 1]["obj_id"];
                //}
            } elseif ($as_subitem) {		// as last chapter
                $target_parent = $target_id;
                $target_pos = IL_FIRST_NODE;
                $childs = $this->slm_tree->getChilds($target_parent);
                if (count($childs) != 0) {
                    $target_pos = $childs[count($childs) - 1]["obj_id"];
                }
            }

            // delete source tree
            if ($movecopy == "move") {
                $this->slm_tree->deleteTree($source_node);
            }
            /*			else
                        {
                            // copy chapter (incl. subcontents)
                            $new_chapter =& $source_obj->copy($this->slm_tree, $target_parent, $target_pos);
                        }
            */

            if (!$this->slm_tree->isInTree($source_id)) {
                $this->slm_tree->insertNode($source_id, $target_parent, $target_pos);

                // insert moved tree
                if ($movecopy == "move") {
                    foreach ($subnodes as $node) {
                        if ($node["obj_id"] != $source_id) {
                            $this->slm_tree->insertNode($node["obj_id"], $node["parent"]);
                        }
                    }
                }
            }

            // check the tree
//			$this->checkTree();
        }

        //		$this->checkTree();
    }
    
    public function getExportFiles()
    {
        $file = array();

        require_once("./Modules/Scorm2004/classes/class.ilSCORM2004Export.php");

        $export = new ilSCORM2004Export($this);
        foreach ($export->getSupportedExportTypes() as $type) {
            $dir = $export->getExportDirectoryForType($type);
            // quit if import dir not available
            if (!@is_dir($dir) or !is_writeable($dir)) {
                continue;
            }
            // open directory
            $cdir = dir($dir);

            // get files and save the in the array
            while ($entry = $cdir->read()) {
                if ($entry != "." and
                $entry != ".." and
                (
                    preg_match("~^[0-9]{10}_{2}[0-9]+_{2}(" . $this->getType() . "_)*[0-9]+\.zip\$~", $entry) or
                    preg_match("~^[0-9]{10}_{2}[0-9]+_{2}(" . $this->getType() . "_)*[0-9]+\.pdf\$~", $entry) or
                    preg_match("~^[0-9]{10}_{2}[0-9]+_{2}(" . $this->getType() . "_)*[0-9]+\.iso\$~", $entry)
                )) {
                    $file[$entry . $type] = array("type" => $type, "file" => $entry,
                        "size" => filesize($dir . "/" . $entry));
                }
            }

            // close import directory
            $cdir->close();
        }

        // sort files
        ksort($file);
        reset($file);
        return $file;
    }

    /**
     * Export (authoring) scorm package
     */
    public function exportScorm($a_inst, $a_target_dir, $ver, &$expLog)
    {
        $a_xml_writer = new ilXmlWriter;

        // export metadata
        $this->exportXMLMetaData($a_xml_writer);
        $metadata_xml = $a_xml_writer->xmlDumpMem(false);
        $a_xml_writer->_XmlWriter;
        
        $xsl = file_get_contents("./Modules/Scorm2004/templates/xsl/metadata.xsl");
        $args = array( '/_xml' => $metadata_xml , '/_xsl' => $xsl );
        $xh = xslt_create();
        $output = xslt_process($xh, "arg:/_xml", "arg:/_xsl", null, $args, null);
        xslt_free($xh);
        file_put_contents($a_target_dir . '/indexMD.xml', $output);

        // export glossary
        if ($this->getAssignedGlossary() != 0) {
            ilUtil::makeDir($a_target_dir . "/glossary");
            include_once("./Modules/Glossary/classes/class.ilObjGlossary.php");
            include_once("./Modules/Glossary/classes/class.ilGlossaryExport.php");
            $glo_xml_writer = new ilXmlWriter();
            
            $glo_xml_writer->xmlSetDtdDef("<!DOCTYPE ContentObject SYSTEM \"http://www.ilias.de/download/dtd/ilias_co_3_7.dtd\">");
            // set xml header
            $glo_xml_writer->xmlHeader();
            $glos = new ilObjGlossary($this->getAssignedGlossary(), false);
            //$glos->exportHTML($a_target_dir."/glossary", $expLog);
            $glos_export = new ilGlossaryExport($glos, "xml");
            $glos->exportXML($glo_xml_writer, $glos_export->getInstId(), $a_target_dir . "/glossary", $expLog);
            $glo_xml_writer->xmlDumpFile($a_target_dir . "/glossary/glossary.xml");
            $glo_xml_writer->_XmlWriter;
        }
        
        $a_xml_writer = new ilXmlWriter;
        // set dtd definition
        $a_xml_writer->xmlSetDtdDef("<!DOCTYPE ContentObject SYSTEM \"http://www.ilias.de/download/dtd/ilias_co_3_7.dtd\">");

        // set generated comment
        $a_xml_writer->xmlSetGenCmt("Export of ILIAS Content Module " . $this->getId() . " of installation " . $a_inst . ".");

        // set xml header
        $a_xml_writer->xmlHeader();

        $a_xml_writer->xmlStartTag("ContentObject", array("Type" => "SCORM2004LearningModule"));

        // MetaData
        $this->exportXMLMetaData($a_xml_writer);

        $this->exportXMLStructureObjects($a_xml_writer, $a_inst, $expLog);
        
        // SCO Objects
        $expLog->write(date("[y-m-d H:i:s] ") . "Start Export Sco Objects");
        $this->exportXMLScoObjects($a_inst, $a_target_dir, $ver, $expLog);
        $expLog->write(date("[y-m-d H:i:s] ") . "Finished Export Sco Objects");
    
        $a_xml_writer->xmlEndTag("ContentObject");
        $a_xml_writer->xmlDumpFile($a_target_dir . '/index.xml', false);
        
        if ($ver == "2004 4th") {
            $revision = "4th";
            $ver = "2004";
        }
    
        if ($ver == "2004 3rd") {
            $revision = "3rd";
            $ver = "2004";
        }

        // add content css (note: this is also done per item)
        $css_dir = $a_target_dir . "/ilias_css_4_2";
        ilUtil::makeDir($css_dir);
        include_once("./Modules/Scorm2004/classes/class.ilScormExportUtil.php");
        ilScormExportUtil::exportContentCSS($this, $css_dir);

        // add manifest
        include_once("./Modules/Scorm2004/classes/class.ilContObjectManifestBuilder.php");
        $manifestBuilder = new ilContObjectManifestBuilder($this);
        $manifestBuilder->buildManifest($ver, $revision);
        $manifestBuilder->dump($a_target_dir);
            
        $xsl = file_get_contents("./Modules/Scorm2004/templates/xsl/module.xsl");
        $args = array( '/_xml' => file_get_contents($a_target_dir . "/imsmanifest.xml"), '/_xsl' => $xsl );
        $xh = xslt_create();
        $output = xslt_process($xh, "arg:/_xml", "arg:/_xsl", null, $args, null);
        xslt_free($xh);
        fputs(fopen($a_target_dir . '/index.html', 'w+'), $output);
        // copy xsd files to target
        switch ($ver) {
            case "2004":
                if ($revision == "3rd") {
                    ilUtil::rCopy('./libs/ilias/Scorm2004/xsd/adlcp_130_export_2004', $a_target_dir, false);
                }
    
                if ($revision == "4th") {
                    ilUtil::rCopy('./libs/ilias/Scorm2004/xsd/adlcp_130_export_2004_4th', $a_target_dir, false);
                }
                break;
            case "12":
                ilUtil::rCopy('./libs/ilias/Scorm2004/xsd/adlcp_120_export_12', $a_target_dir, false);
                break;
        }
        
        $a_xml_writer->_XmlWriter;
    }

     
    public function exportHTML4PDF($a_inst, $a_target_dir, &$expLog)
    {
        $tree = new ilTree($this->getId());
        $tree->setTableNames('sahs_sc13_tree', 'sahs_sc13_tree_node');
        $tree->setTreeTablePK("slm_id");
        foreach ($tree->getSubTree($tree->getNodeData($tree->getRootId()), true, 'sco') as $sco) {
            include_once("./Modules/Scorm2004/classes/class.ilSCORM2004Sco.php");
            $sco_folder = $a_target_dir . "/" . $sco['obj_id'];
            ilUtil::makeDir($sco_folder);
            $node = new ilSCORM2004Sco($this, $sco['obj_id']);
            $node->exportHTML4PDF($a_inst, $sco_folder, $expLog);
        }
    }
    
    public function exportPDF($a_inst, $a_target_dir, &$expLog)
    {
        $a_xml_writer = new ilXmlWriter;
        $a_xml_writer->xmlStartTag("ContentObject", array("Type" => "SCORM2004SCO"));
        $this->exportXMLMetaData($a_xml_writer);
        $tree = new ilTree($this->getId());
        $tree->setTableNames('sahs_sc13_tree', 'sahs_sc13_tree_node');
        $tree->setTreeTablePK("slm_id");
        foreach ($tree->getSubTree($tree->getNodeData($tree->getRootId()), true, 'sco') as $sco) {
            include_once("./Modules/Scorm2004/classes/class.ilSCORM2004Sco.php");
            $sco_folder = $a_target_dir . "/" . $sco['obj_id'];
            ilUtil::makeDir($sco_folder);
            $node = new ilSCORM2004Sco($this, $sco['obj_id']);
            $node->exportPDFPrepareXmlNFiles($a_inst, $a_target_dir, $expLog, $a_xml_writer);
        }
        if ($this->getAssignedGlossary() != 0) {
            ilUtil::makeDir($a_target_dir . "/glossary");
            include_once("./Modules/Glossary/classes/class.ilObjGlossary.php");
            include_once("./Modules/Glossary/classes/class.ilGlossaryExport.php");
            $glos = new ilObjGlossary($this->getAssignedGlossary(), false);
            $glos_export = new ilGlossaryExport($glos, "xml");
            $glos->exportXML($a_xml_writer, $glos_export->getInstId(), $a_target_dir . "/glossary", $expLog);
        }
        $a_xml_writer->xmlEndTag("ContentObject");
        include_once 'Services/Transformation/classes/class.ilXML2FO.php';
        $xml2FO = new ilXML2FO();
        $xml2FO->setXSLTLocation('./Modules/Scorm2004/templates/xsl/contentobject2fo.xsl');
        $xml2FO->setXMLString($a_xml_writer->xmlDumpMem());
        $xml2FO->setXSLTParams(array('target_dir' => $a_target_dir));
        $xml2FO->transform();
        $fo_string = $xml2FO->getFOString();
        $fo_xml = simplexml_load_string($fo_string);
        $fo_ext = $fo_xml->xpath("//fo:declarations");
        $fo_ext = $fo_ext[0];
        $results = array();
        include_once "./Services/Utilities/classes/class.ilFileUtils.php";
        ilFileUtils::recursive_dirscan($a_target_dir . "/objects", $results);
        if (is_array($results["file"])) {
            foreach ($results["file"] as $key => $value) {
                $e = $fo_ext->addChild("fox:embedded-file", "", "http://xml.apache.org/fop/extensions");
                $e->addAttribute("src", $results[path][$key] . $value);
                $e->addAttribute("name", $value);
                $e->addAttribute("desc", "");
            }
        }
        $fo_string = $fo_xml->asXML();
        $a_xml_writer->_XmlWriter;
        return $fo_string;
    }
    
    public function exportHTMLOne($a_inst, $a_target_dir, &$expLog)
    {
        $one_file = fopen($a_target_dir . '/index.html', 'w+');
        $this->exportHTML($a_inst, $a_target_dir, $expLog, $one_file);
        fclose($one_file);
    }
    
    /**
     * Export SCORM package to HTML
     */
    public function exportHTML($a_inst, $a_target_dir, &$expLog, $a_one_file = "")
    {
        //		$a_xml_writer = new ilXmlWriter;
        // set dtd definition
        //		$a_xml_writer->xmlSetDtdDef("<!DOCTYPE ContentObject SYSTEM \"http://www.ilias.de/download/dtd/ilias_co_3_7.dtd\">");

        // set generated comment
        //		$a_xml_writer->xmlSetGenCmt("Export of ILIAS Content Module ".	$this->getId()." of installation ".$a_inst.".");

        // set xml header
        //		$a_xml_writer->xmlHeader();


        //		$a_xml_writer->xmlStartTag("ContentObject", array("Type"=>"SCORM2004LearningModule"));

        //		$expLog->write(date("[y-m-d H:i:s] ")."Start Export Sco Objects");
        $this->exportHTMLScoObjects($a_inst, $a_target_dir, $expLog, $a_one_file);
        //		$expLog->write(date("[y-m-d H:i:s] ")."Finished Export Sco Objects");
    
        //		$a_xml_writer->xmlEndTag("ContentObject");
        

        /*$toc_tpl = new ilTemplate("tpl.main.html", true, true, false);
        $style_name = $ilUser->prefs["style"].".css";
        $tpl->setCurrentBlock("css_file");
        $tpl->setVariable("CSS_FILE", $style_name);
        $tpl->parseCurrentBlock();*/

        if ($a_one_file == "") {
            include_once("./Modules/Scorm2004/classes/class.ilContObjectManifestBuilder.php");
            $manifestBuilder = new ilContObjectManifestBuilder($this);
            $manifestBuilder->buildManifest('12');

            include_once("Services/Frameset/classes/class.ilFramesetGUI.php");
            $fs_gui = new ilFramesetGUI();
            $fs_gui->setFramesetTitle($this->getTitle());
            $fs_gui->setMainFrameSource("");
            $fs_gui->setSideFrameSource("toc.html");
            $fs_gui->setMainFrameName("content");
            $fs_gui->setSideFrameName("toc");
            $output = $fs_gui->get();
            fputs(fopen($a_target_dir . '/index.html', 'w+'), $output);
            
            $xsl = file_get_contents("./Modules/Scorm2004/templates/xsl/module.xsl");
            $xml = simplexml_load_string($manifestBuilder->writer->xmlDumpMem());
            $args = array( '/_xml' => $xml->organizations->organization->asXml(), '/_xsl' => $xsl );
            $xh = xslt_create();
            $output = xslt_process($xh, "arg:/_xml", "arg:/_xsl", null, $args, null);
            xslt_free($xh);
            fputs(fopen($a_target_dir . '/toc.html', 'w+'), $output);
        }
        //		$a_xml_writer->_XmlWriter;
    }

    /**
     * export content objects meta data to xml (see ilias_co.dtd)
     *
     * @param	object		$a_xml_writer	ilXmlWriter object that receives the
     *										xml data
     */
    public function exportXMLMetaData(&$a_xml_writer)
    {
        include_once("Services/MetaData/classes/class.ilMD2XML.php");
        $md2xml = new ilMD2XML($this->getId(), 0, $this->getType());
        $md2xml->setExportMode(true);
        $md2xml->startExport();
        $a_xml_writer->appendXML($md2xml->getXML());
    }

    /**
     * export structure objects to xml (see ilias_co.dtd)
     *
     * @param	object		$a_xml_writer	ilXmlWriter object that receives the
     *										xml data
     */
    public function exportXMLStructureObjects(&$a_xml_writer, $a_inst, &$expLog)
    {
        include_once("Services/MetaData/classes/class.ilMD2XML.php");
        $tree = new ilTree($this->getId());
        $tree->setTableNames('sahs_sc13_tree', 'sahs_sc13_tree_node');
        $tree->setTreeTablePK("slm_id");
        $a_xml_writer->xmlStartTag("StructureObject");
        foreach ($tree->getFilteredSubTree($tree->getRootId(), array('page')) as $obj) {
            if ($obj['type'] == '') {
                continue;
            }
            
            //$md2xml = new ilMD2XML($obj['obj_id'], 0, $obj['type']);
            $md2xml = new ilMD2XML($this->getId(), $obj['obj_id'], $obj['type']);
            $md2xml->setExportMode(true);
            $md2xml->startExport();
            $a_xml_writer->appendXML($md2xml->getXML());
        }
        $a_xml_writer->xmlEndTag("StructureObject");
    }


    /**
     * export page objects to xml (see ilias_co.dtd)
     *
     * @param	object		$a_xml_writer	ilXmlWriter object that receives the
     *										xml data
     */
    public function exportXMLScoObjects($a_inst, $a_target_dir, $ver, &$expLog)
    {
        $tree = new ilTree($this->getId());
        $tree->setTableNames('sahs_sc13_tree', 'sahs_sc13_tree_node');
        $tree->setTreeTablePK("slm_id");
        foreach ($tree->getSubTree($tree->getNodeData($tree->getRootId()), true, array('sco','ass')) as $sco) {
            if ($sco['type'] == "sco") {
                include_once("./Modules/Scorm2004/classes/class.ilSCORM2004Sco.php");
                $sco_folder = $a_target_dir . "/" . $sco['obj_id'];
                ilUtil::makeDir($sco_folder);
                $node = new ilSCORM2004Sco($this, $sco['obj_id']);
                $node->exportScorm($a_inst, $sco_folder, $ver, $expLog);
            }
            if ($sco['type'] == "ass") {
                include_once("./Modules/Scorm2004/classes/class.ilSCORM2004Asset.php");
                $sco_folder = $a_target_dir . "/" . $sco['obj_id'];
                ilUtil::makeDir($sco_folder);
                $node = new ilSCORM2004Asset($this, $sco['obj_id']);
                $node->exportScorm($a_inst, $sco_folder, $ver, $expLog);
            }
        }
    }

    /* export page objects to xml (see ilias_co.dtd)
     *
     * @param	object		$a_xml_writer	ilXmlWriter object that receives the
     *										xml data
     */
    public function exportHTMLScoObjects($a_inst, $a_target_dir, &$expLog, $a_one_file = "")
    {
        $tree = new ilTree($this->getId());
        $tree->setTableNames('sahs_sc13_tree', 'sahs_sc13_tree_node');
        $tree->setTreeTablePK("slm_id");
        
        // copy all necessary files now
        if ($a_one_file != "") {
            $this->prepareHTMLExporter($a_target_dir);
            
            // put header into file
            $sco_tpl = new ilTemplate("tpl.sco.html", true, true, "Modules/Scorm2004");
            include_once("./Services/COPage/classes/class.ilCOPageHTMLExport.php");
            $page_html_export = new ilCOPageHTMLExport($a_target_dir);
            $sco_tpl = $page_html_export->getPreparedMainTemplate($sco_tpl);
            
            $sco_tpl->setCurrentBlock("js_file");
            $sco_tpl->setVariable("JS_FILE", "./js/pure.js");
            $sco_tpl->parseCurrentBlock();
            $sco_tpl->setCurrentBlock("js_file");
            $sco_tpl->setVariable("JS_FILE", "./js/question_handling.js");
            $sco_tpl->parseCurrentBlock();
            
            
            $sco_tpl->setCurrentBlock("head");
            $sco_tpl->parseCurrentBlock();
            fputs($a_one_file, $sco_tpl->get("head"));
            
            // toc
            include_once("./Modules/Scorm2004/classes/class.ilContObjectManifestBuilder.php");
            $manifestBuilder = new ilContObjectManifestBuilder($this);
            $manifestBuilder->buildManifest('12');
            $xsl = file_get_contents("./Modules/Scorm2004/templates/xsl/module.xsl");
            $xml = simplexml_load_string($manifestBuilder->writer->xmlDumpMem());
            $args = array( '/_xml' => $xml->organizations->organization->asXml(), '/_xsl' => $xsl );
            $xh = xslt_create();
            $params = array("one_page" => "y");
            $output = xslt_process($xh, "arg:/_xml", "arg:/_xsl", null, $args, $params);
            xslt_free($xh);
            fputs($a_one_file, $output);
        }
        
        foreach ($tree->getSubTree($tree->getNodeData($tree->getRootId()), true, 'sco') as $sco) {
            include_once("./Modules/Scorm2004/classes/class.ilSCORM2004Sco.php");
            $sco_folder = $a_target_dir . "/" . $sco['obj_id'];
            ilUtil::makeDir($sco_folder);
            $node = new ilSCORM2004Sco($this, $sco['obj_id']);

            if ($a_one_file == "") {
                $node->exportHTML($a_inst, $sco_folder, $expLog, $a_one_file);
            } else {
                $node->exportHTMLPageObjects(
                    $a_inst,
                    $a_target_dir,
                    $expLog,
                    'full',
                    "sco",
                    $a_one_file,
                    $sco_tpl
                );
            }
            if ($this->getAssignedGlossary() != 0) {
                include_once("./Modules/Glossary/classes/class.ilObjGlossary.php");
                $glos = new ilObjGlossary($this->getAssignedGlossary(), false);
                //$glos->exportHTML($sco_folder."/glossary", $expLog);
            }
        }
        
        // copy all necessary files now
        if ($a_one_file != "") {
            // put tail into file
            fputs($a_one_file, $sco_tpl->get("tail"));
        }
    }

    /**
     * Prepare HTML exporter
     *
     * @param
     * @return
     */
    public function prepareHTMLExporter($a_target_dir)
    {
        // system style html exporter
        include_once("./Services/Style/System/classes/class.ilSystemStyleHTMLExport.php");
        $this->sys_style_html_export = new ilSystemStyleHTMLExport($a_target_dir);
        $this->sys_style_html_export->export();

        // init co page html exporter
        include_once("./Services/COPage/classes/class.ilCOPageHTMLExport.php");
        $this->co_page_html_export = new ilCOPageHTMLExport($a_target_dir);
        include_once("./Services/Style/Content/classes/class.ilObjStyleSheet.php");
        $this->co_page_html_export->setContentStyleId(
            ilObjStyleSheet::getEffectiveContentStyleId($this->getStyleSheetId())
        );
        $this->co_page_html_export->createDirectories();
        $this->co_page_html_export->exportStyles();
        $this->co_page_html_export->exportSupportScripts();

        include_once("./Services/MediaObjects/classes/class.ilPlayerUtil.php");
        $this->flv_dir = $a_target_dir . "/" . ilPlayerUtil::getFlashVideoPlayerDirectory();
        
        ilUtil::makeDir($a_target_dir . '/css/yahoo');
        ilUtil::makeDir($a_target_dir . '/objects');
        ilUtil::makeDir($a_target_dir . '/players');
        ilUtil::makeDir($this->flv_dir);

        include_once("./Services/MediaObjects/classes/class.ilPlayerUtil.php");
        //		copy(ilPlayerUtil::getFlashVideoPlayerFilename(true),
        //			$a_target_dir.'/js/'.ilPlayerUtil::getFlashVideoPlayerFilename());
        ilPlayerUtil::copyPlayerFilesToTargetDirectory($this->flv_dir);
        
        copy('./Modules/Scorm2004/scripts/scorm_2004.js', $a_target_dir . '/js/scorm.js');
        copy('./Modules/Scorm2004/scripts/pager.js', $a_target_dir . '/js/pager.js');
        copy('./Modules/Scorm2004/scripts/questions/pure.js', $a_target_dir . '/js/pure.js');
        copy(
            './Modules/Scorm2004/scripts/questions/question_handling.js',
            $a_target_dir . '/js/question_handling.js'
        );
    }
    
    /**
     * get public export file
     *
     * @param	string		$a_type		type ("xml" / "html")
     *
     * @return	string		$a_file		file name
     */
    public function getPublicExportFile($a_type)
    {
        return $this->public_export_file[$a_type];
    }

    /**
     * export files of file itmes
     *
     */
    public function exportFileItems($a_target_dir, &$expLog)
    {
        include_once("./Modules/File/classes/class.ilObjFile.php");

        foreach ($this->file_ids as $file_id) {
            $expLog->write(date("[y-m-d H:i:s] ") . "File Item " . $file_id);
            $file_obj = new ilObjFile($file_id, false);
            $file_obj->export($a_target_dir);
            unset($file_obj);
        }
    }

    /**
     *
     */
    public function setPublicExportFile($a_type, $a_file)
    {
        $this->public_export_file[$a_type] = $a_file;
    }
    
    /**
     *
     * Returns score.max for the learning module, refered to the last sco where score.max is set.
     *
     * @param	integer $a_id
     * @param	integer $a_user
     * @static
     * @return	float
     *
     */
    public static function _getMaxScoreForUser($a_id, $a_user)
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
            array_push($scos, $row['cp_node_id']);
        }
        
        $set = 0; //numbers of SCO that set cmi.score.scaled
        $max = null;
        for ($i = 0; $i < count($scos); $i++) {
            $res = $ilDB->queryF(
                'SELECT c_max FROM cmi_node WHERE (user_id = %s AND cp_node_id = %s)',
                array('integer', 'integer'),
                array($a_user, $scos[$i])
            );
            
            if ($ilDB->numRows($res) > 0) {
                $row = $ilDB->fetchAssoc($res);
                if ($row['c_max'] != null) {
                    $set++;
                    $max = $row['c_max'];
                }
            }
        }
        $retVal = ($set == 1) ? $max : null;
        
        return $retVal;
    }

    public static function _getScores2004ForUser($a_cp_node_id, $a_user)
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
    
    /**
     * Copy authored content (everything done with the editor
     *
     * @param
     * @return
     */
    public function copyAuthoredContent($a_new_obj)
    {
        // set/copy stylesheet
        include_once("./Services/Style/Content/classes/class.ilObjStyleSheet.php");
        $style_id = $this->getStyleSheetId();
        if ($style_id > 0 && !ilObjStyleSheet::_lookupStandard($style_id)) {
            $style_obj = ilObjectFactory::getInstanceByObjId($style_id);
            $new_id = $style_obj->ilClone();
            $a_new_obj->setStyleSheetId($new_id);
            $a_new_obj->update();
        }
        
        $a_new_obj->createScorm2004Tree();
        $source_tree = $this->getTree();
        $target_tree_root_id = $a_new_obj->getTree()->readRootId();
        $childs = $source_tree->getChilds($source_tree->readRootId());
        $a_copied_nodes = array();
        include_once("./Modules/Scorm2004/classes/class.ilSCORM2004Node.php");
        foreach ($childs as $c) {
            ilSCORM2004Node::pasteTree(
                $a_new_obj,
                $c["child"],
                $target_tree_root_id,
                IL_LAST_NODE,
                "",
                $a_copied_nodes,
                true,
                false
            );
        }
    }
}
