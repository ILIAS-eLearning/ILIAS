<?php
/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */
//require_once "./Services/Object/classes/class.ilObject.php";

/**
* Class ilSCORMOfflineMode
*
* Class for scorm offline player connection
*
* @author Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
* @author Stefan Schneider <schneider@hrz.uni-marburg.de>
* @version $Id: class.ilSCORMOfflineMode.php  $
*
* @ingroup ModulesScormAicc
*/
class ilSCORMOfflineMode
{
    public $type;
    public $obj_id;
    public $offlineMode;
    public $cmd_url;
    public $lm_cmd_url;
    public $player12_url;
    public $player2004_url;
    public $som_url;
    
    public $sop_dir;
    public $som_dir;
    public $scripts_dir;
    
    public $sop_index;
    public $sop_appcache;
    public $lm_dir;
    public $lm_index;
    public $lm_appcache;
    public $lm_imsmanifest_xml;
    public $imsmanifest;
    public $debug = false; // omit caching sop and som files for debugging

    /**
    * Constructor
    *
    * @param	int		$a_id		Object ID
    * @access	public
    */
    public function __construct()
    {
        global $DIC;
        $ilias = $DIC['ilias'];
        $this->ilias = $ilias;
        $this->id = $_GET['ref_id'];
        $this->obj_id = ilObject::_lookupObjectId($_GET['ref_id']);
        include_once "./Modules/ScormAicc/classes/class.ilObjSAHSLearningModule.php";
        $this->type = ilObjSAHSLearningModule::_lookupSubType($this->obj_id);
        $this->cmd_url = './ilias.php?baseClass=ilSAHSPresentationGUI&cmd=';
        $this->lm_cmd_url = './ilias.php?baseClass=ilSAHSPresentationGUI&ref_id=' . $this->id . '&cmd=';
        $this->lm_info_url = $this->lm_cmd_url . 'infoScreen';
        $this->player12_url = $this->cmd_url . 'offlineMode_player12';
        $this->player2004_url = $this->cmd_url . 'offlineMode_player2004';
        $this->som_url = $this->cmd_url . 'offlineMode_som';
        $this->offlineMode = 'online';
        $this->sop_index = './Modules/ScormAicc/sop/sop_index.html';
        $this->sop_appcache = './Modules/ScormAicc/sop/sop.appcache';
        $this->sop_dir = './Modules/ScormAicc/templates/sop/';
        $this->som_dir = './Modules/ScormAicc/templates/som/';
        $this->scripts_dir = './Modules/ScormAicc/scripts/';
        $this->images_dir = './templates/default/images/scorm/';
        $this->pouchdb_js = './libs/bower/bower_components/pouchdb/dist/pouchdb.min.js';
        $this->jquery_js = './libs/bower/bower_components/jquery/dist/jquery.min.js';
        $this->bootstrap_js = './libs/bower/bower_components/bootstrap/dist/js/bootstrap.min.js';
        $this->bootstrap_css = './libs/bower/bower_components/bootstrap/dist/css/bootstrap.min.css';
        $this->read();
    }
    
    public function getSopManifestEntries()
    {
        global $DIC;
        $log = $DIC['log'];
        $log->write("getSopManifestEntries ");
        $manifest_string = "";
        if (!$this->debug) {
            // if ($this->type == "scorm2004") {
            $BASE_DIR = './Modules/Scorm2004/';
            $manifest_string .= ilUtil::getImagePath("scorm/asset.svg", false) . "\n";
            $manifest_string .= ilUtil::getImagePath("scorm/completed.svg", false) . "\n";
            $manifest_string .= ilUtil::getImagePath("scorm/not_attempted.svg", false) . "\n";
            $manifest_string .= ilUtil::getImagePath("scorm/running.svg", false) . "\n";
            $manifest_string .= ilUtil::getImagePath("scorm/incomplete.svg", false) . "\n";
            $manifest_string .= ilUtil::getImagePath("scorm/passed.svg", false) . "\n";
            $manifest_string .= ilUtil::getImagePath("scorm/failed.svg", false) . "\n";
            // $manifest_string .= ilUtil::getImagePath("scorm/browsed.svg",false) . "\n";
            $manifest_string .= ilUtil::getStyleSheetLocation() . "\n";
            $manifest_string .= $BASE_DIR . 'templates/default/player.css' . "\n";
            $manifest_string .= $BASE_DIR . 'scripts/buildrte/rte.js' . "\n";
            $manifest_string .= $BASE_DIR . 'scripts/ilNestedList.js' . "\n";
            $manifest_string .= iljQueryUtil::getLocaljQueryPath() . "\n";
            $manifest_string .= $this->player2004_url . "\n";
            // } else {
            $manifest_string .= $this->player12_url . "\n";
            // }
            $manifest_string .= $this->som_url . "\n";
            // $log->write("Manifest: ".$manifest_string);
            $objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->sop_dir));
            foreach ($objects as $name => $object) {
                if (preg_match('/\/\.+/', $name)) {
                    continue;
                }
                //$manifest_string .= preg_replace('/^\./','./Modules/ScormAicc',$name) . "\n"; // for cli
                $manifest_string .= self::encodeuri($name) . "\n";
            }
            $objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->som_dir));
            foreach ($objects as $name => $object) {
                if (preg_match('/\/\.+/', $name)) {
                    continue;
                }
                //$manifest_string .= preg_replace('/^\./','./Modules/ScormAicc',$name) . "\n"; // for cli
                $manifest_string .= self::encodeuri($name) . "\n";
            }
            
            $objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->scripts_dir));
            foreach ($objects as $name => $object) {
                if (preg_match('/\/\.+/', $name)) { //UK statt .+ .js und SCORM-Scripts weg
                    continue;
                }
                //$manifest_string .= preg_replace('/^\./','./Modules/ScormAicc',$name) . "\n"; // for cli
                $manifest_string .= self::encodeuri($name) . "\n";
            }
            $objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->images_dir));
            foreach ($objects as $name => $object) {
                if (preg_match('/\/\.+/', $name)) {
                    continue;
                }
                //$manifest_string .= preg_replace('/^\./','./Modules/ScormAicc',$name) . "\n"; // for cli
                $manifest_string .= self::encodeuri($name) . "\n";
            }
            
            $manifest_string .= $this->pouchdb_js . "\n";
            $manifest_string .= $this->jquery_js . "\n";
            $manifest_string .= $this->bootstrap_js . "\n";
            $manifest_string .= $this->bootstrap_css . "\n";
        }
        //$log->write($manifest_string);
        return $manifest_string;
    }
    
    public function getLmManifestEntries()
    { // ToDo: database support !!
        global $DIC;
        $log = $DIC['log'];
        $log->write("getLmManifestEntries");
        $this->lm_dir = ilUtil::getWebspaceDir("filesystem") . '/lm_data/lm_' . $this->obj_id;
        $manifest_string = "";
        $objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->lm_dir));
        foreach ($objects as $name => $object) {
            if (preg_match('/\/\.+/', $name)) {
                continue;
            }
            if (preg_match('/\.zip$/', $name)) {
                continue;
            }
            //$manifest_string .= preg_replace('/^\./','./Modules/ScormAicc',$name) . "\n"; // for cli
            $manifest_string .= self::encodeuri($name) . "\n";
        }
        //$log->write($manifest_string);
        return $manifest_string;
    }

    // function il2sop() {
    public function tracking2sop()
    {
        global $DIC;
        $ilUser = $DIC['ilUser'];
        $ilias = $DIC['ilias'];
        // $this->setOfflineMode("il2sop");
        header('Content-Type: text/javascript; charset=UTF-8');

        include_once "./Modules/ScormAicc/classes/class.ilObjSAHSLearningModule.php";
        $ob = new ilObjSAHSLearningModule($this->id);
        $module_version = $ob->getModuleVersion();
        $sahs_user = $this->il2sopSahsUser();
        $support_mail = "";//TODO
        $scorm_version = "1.2";
        if ($this->type == "scorm2004") {
            $scorm_version = "2004";
        }
        $tree="";
        
        $learning_progress_enabled = 1;
        include_once './Services/Object/classes/class.ilObjectLP.php';
        $olp = ilObjectLP::getInstance($this->obj_id);
        if ($olp->getCurrentMode() == 0) {
            $learning_progress_enabled = 0;
        }
        
        $certificate_enabled = 0;

        $adlact_data = null;
        $ilias_version = $ilias->getSetting("ilias_version");

        if ($this->type == 'scorm2004') {
            include_once "./Modules/Scorm2004/classes/ilSCORM13Player.php";
            $ob2004 = new ilSCORM13Player();
            $init_data = json_encode($ob2004->getConfigForPlayer());
            $resources = json_decode($ob2004->getCPDataInit());
            $cmi = $ob2004->getCMIData($ilUser->getID(), $this->obj_id);
            $max_attempt = $ob2004->get_max_attempts();
            $adlact_data = json_decode($ob2004->getADLActDataInit());
        //$globalobj_data = $ob2004->readGObjectiveInit();
        } else {
            include_once "./Modules/ScormAicc/classes/SCORM/class.ilObjSCORMInitData.php";
            $slm_obj = new ilObjSCORMLearningModule($_GET["ref_id"]);
            $init_data = ilObjSCORMInitData::getIliasScormVars($slm_obj);
            $resources = json_decode(ilObjSCORMInitData::getIliasScormResources($this->obj_id));
            $tree = json_decode(ilObjSCORMInitData::getIliasScormTree($this->obj_id));
            $cmi = json_decode(ilObjSCORMInitData::getIliasScormData($this->obj_id));
            $max_attempt = ilObjSCORMInitData::get_max_attempts($this->obj_id);
        }
        //UK max_attempt weg!
        // if ($max_attempt == null) $max_attempt = 0;
        $result = array(
            'client_data' => array(
                $support_mail
            ),
            'user_data' => $this->il2sopUserData(),
            'lm' => array(
                ilObject::_lookupTitle($this->obj_id),
                ilObject::_lookupDescription($this->obj_id),
                $scorm_version,
                1,//active
                $init_data,
                $resources,
                $tree,
                $module_version,
                "", //offline_zip_created!!!!!!!!
                $learning_progress_enabled,
                $certificate_enabled,
                $max_attempt,
                $adlact_data,
                $ilias_version
            ),
            'sahs_user' => $sahs_user,
            'cmi' => $cmi
        );
        
        print(json_encode($result));
    }
    
    public function getClientIdSop()
    {
        $iliasDomain = substr(ILIAS_HTTP_PATH, 7);
        if (substr($iliasDomain, 0, 1) == "\/") {
            $iliasDomain = substr($iliasDomain, 1);
        }
        if (substr($iliasDomain, 0, 4) == "www.") {
            $iliasDomain = substr($iliasDomain, 4);
        }
        return $iliasDomain . ';' . CLIENT_ID;
    }
    
    public function il2sopUserData()
    {
        global $DIC;
        $ilUser = $DIC['ilUser'];
        return array(
            $ilUser->getLogin(),
            "",
            $ilUser->getFirstname(),
            $ilUser->getLastname(),
            $ilUser->getUTitle(),
            $ilUser->getGender(),
            $ilUser->getID()
            );
    }
    public function il2sopSahsUser()
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $ilUser = $DIC['ilUser'];
        $package_attempts	= 0;
        $module_version		= 1;//if module_version in sop is different...
        $last_visited		= "";
        $first_access		= null;
        $last_access		= null;
        $last_status_change	= null;
        $total_time_sec		= null;
        $sco_total_time_sec	= 0;
        $status				= 0;
        $percentage_completed = 0;
        $user_data			= "";

        global $DIC;
        $ilDB = $DIC['ilDB'];
        $ilUser = $DIC['ilUser'];
        $res = $ilDB->queryF(
            'SELECT * FROM sahs_user WHERE obj_id=%s AND user_id=%s',
            array('integer','integer'),
            array($this->obj_id,$ilUser->getID())
        );
        while ($row = $ilDB->fetchAssoc($res)) {
            $package_attempts = $row['package_attempts'];
            $module_version = $row['module_version'];
            $last_visited = $row['last_visited'];
            if ($row['first_access'] != null) {
                $first_access = strtotime($row['first_access'])*1000;//check Oracle!
            }
            if ($row['last_access'] != null) {
                $last_access = strtotime($row['last_access'])*1000;//check Oracle!
            }
            $total_time_sec = $row['total_time_sec'];
            $sco_total_time_sec = $row['sco_total_time_sec'];
            $status = $row['status'];
            $percentage_completed = $row['percentage_completed'];
        }
        if ($first_access == null) {
            include_once './Services/Tracking/classes/class.ilChangeEvent.php';
            $all = ilChangeEvent::_lookupReadEvents($this->obj_id, $ilUser->getID());
            foreach ($all as $event) {
                $first_access = strtotime($event['first_access'])*1000;//
            }
        }
        return array($package_attempts, $module_version, $last_visited, $first_access, $last_access, $last_status_change, $total_time_sec, $sco_total_time_sec, $status, $percentage_completed, $user_data);
    }

    public function sop2il()
    {
        //		sleep(5);
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $ilUser = $DIC['ilUser'];
        $in = file_get_contents("php://input");
        $GLOBALS['DIC']['ilLog']->write($in);
        $ret = array('msg'=>array(),'err'=>array());
        
        if (!$in || $in == "") {
            $ret['err'][] = "no post data recieved";
            print(json_encode($ret));
            exit;
        }
        $userId=$ilUser->getID();
        $result=true;

        if ($this->type == 'scorm2004') {
            $lm_set = $ilDB->queryF('SELECT default_lesson_mode, interactions, objectives, comments, time_from_lms FROM sahs_lm WHERE id = %s', array('integer'), array($this->obj_id));
            while ($lm_rec = $ilDB->fetchAssoc($lm_set)) {
                $defaultLessonMode=($lm_rec["default_lesson_mode"]);
                $interactions=(ilUtil::yn2tf($lm_rec["interactions"]));
                $objectives=(ilUtil::yn2tf($lm_rec["objectives"]));
                $comments=(ilUtil::yn2tf($lm_rec["comments"]));
                $time_from_lms=(ilUtil::yn2tf($lm_rec["time_from_lms"]));
            }
            include_once './Modules/Scorm2004/classes/class.ilSCORM2004StoreData.php';
            $data = json_decode($in);
            $GLOBALS['DIC']['ilLog']->write('cmi_count=' . count($data->cmi));
            for ($i=0; $i<count($data->cmi); $i++) {
                if ($result==true) {
                    //$a_r=array();
                    $cdata=$data->cmi[$i];
                    $a_r = ilSCORM2004StoreData::setCMIData(
                        $userId,
                        $this->obj_id,
                        $data->cmi[$i],//json_decode($data->cmi[$i]),
                        $comments,
                        $interactions,
                        $objectives
                    );
                    if (!is_array($a_r)) {
                        $result=false;
                    }
                }
            }
            if ($result==true) {
                $result=ilSCORM2004StoreData::syncGlobalStatus($userId, $this->obj_id, $data, $data->now_global_status, $time_from_lms);
            }
        } else {
            include_once "./Modules/ScormAicc/classes/SCORM/class.ilObjSCORMTracking.php";
            $data = json_decode($in);
            $result=ilObjSCORMTracking::storeJsApiCmi($userId, $this->obj_id, $data);
            if ($result==true) {
                $result=ilObjSCORMTracking::syncGlobalStatus($userId, $this->obj_id, $data, $data->now_global_status);
            }
        }
        if ($result==true) {
            $result=self::scormPlayerUnloadForSOP2il($data);
        }

        if ($result==false) {
            $ret['err'][] = "invalid post data recieved";
        } else {
            $ret['msg'][]  = "post data recieved";
        }
        header('Content-Type: text/plain; charset=UTF-8');
        print json_encode($ret);
    }
    
    public function scormPlayerUnloadForSop2il($data)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $ilUser = $DIC['ilUser'];
        $first_access=null;
        if ($data->first_access != null) {
            $first_access=date('Y-m-d H:i:s', round($data->first_access/1000));
        }
        $last_access=null;
        $i_last_access=null;
        if ($data->last_access != null) {
            $i_last_access = round($data->last_access/1000);
            $last_access=date('Y-m-d H:i:s', $i_last_access);
            include_once("./Services/Tracking/classes/class.ilChangeEvent.php");
            ilChangeEvent::_updateAccessForScormOfflinePlayer($this->obj_id, $ilUser->getId(), $i_last_access, $first_access);
        }
        $last_status_change=null;
        if ($data->last_status_change != null) {
            $last_status_change=date('Y-m-d H:i:s', round($data->last_status_change/1000));
        }
        $GLOBALS['DIC']['ilLog']->write('first_access=' . $first_access);
        $res = $ilDB->queryF(
            'UPDATE sahs_user SET first_access=%s, last_access=%s, last_status_change=%s, last_visited=%s, module_version=%s WHERE obj_id=%s AND user_id=%s',
            array('timestamp','timestamp','timestamp','text','integer','integer','integer'),
            array($first_access,$last_access,$last_status_change,$data->last_visited,$data->module_version, $this->obj_id,$ilUser->getId())
        );

        //populate last_status_change
        return true;
    }

    //offlineMode: offline, online, il2sop, sop2il
    public function setOfflineMode($a_mode)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $ilUser = $DIC['ilUser'];
        $res = $ilDB->queryF(
            'UPDATE sahs_user SET offline_mode=%s WHERE obj_id=%s AND user_id=%s',
            array('text','integer','integer'),
            array($a_mode, $this->obj_id,$ilUser->getId())
        );
        $this->offlineMode=$a_mode;
    }
    public function getOfflineMode()
    {
        return $this->offlineMode;
    }
    
    private function read()
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $ilUser = $DIC['ilUser'];
        $res = $ilDB->queryF(
            'SELECT offline_mode FROM sahs_user WHERE obj_id=%s AND user_id=%s',
            array('integer','integer'),
            array($this->obj_id,$ilUser->getId())
        );
        while ($row = $ilDB->fetchAssoc($res)) {
            if ($row['offline_mode'] != null && $row['offline_mode'] != '') {
                $this->offlineMode = $row['offline_mode'];
            } else {
                $this->offlineMode = "online";
            }
        }
    }
    
    public static function checkIfAnyoneIsInOfflineMode($obj_id)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $res = $ilDB->queryF(
            "SELECT count(*) cnt FROM sahs_user WHERE obj_id=%s AND offline_mode = 'offline'",
            array('integer'),
            array($obj_id)
        );
        $val_rec = $ilDB->fetchAssoc($res);
        if ($val_rec["cnt"] == 0) {
            return false;
        }
        return true;
    }

    public static function usersInOfflineMode($obj_id)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $users = array();
        $res = $ilDB->queryF(
            "SELECT user_id, lastname, firstname FROM sahs_user, usr_data "
                            . "WHERE sahs_user.obj_id=%s AND sahs_user.offline_mode = 'offline' AND sahs_user.user_id=usr_data.usr_id",
            array('integer'),
            array($obj_id)
        );
        while ($row = $ilDB->fetchAssoc($res)) {
            $users[] = $row;
        }
        return $users;
    }

    public static function stopOfflineModeForUser($obj_id, $user_id)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $res = $ilDB->queryF(
            "UPDATE sahs_user SET offline_mode='online' WHERE obj_id=%s AND user_id=%s",
            array('integer','integer'),
            array($obj_id,$user_id)
        );
    }

    public static function encodeuri($path)
    {
        return implode('/', array_map('rawurlencode', explode('/', $path)));
    }
}
