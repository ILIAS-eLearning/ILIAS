<?php

/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class ilObjSCORMInitData
*
* Class for getting init Data fpr SCORM 1.2 RTE
*
* @author Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
* @version $Id: class.ilObjSCORMInitData.php  $
*
* @ingroup ModulesScormAicc
*/
class ilObjSCORMInitData
{
    public static function encodeURIComponent($str)
    {
        $revert = array('%21' => '!', '%2A' => '*', '%27' => "'", '%28' => '(', '%29' => ')', '%7E' => '~');
        return strtr(rawurlencode($str), $revert);
    }

    public static function getIliasScormVars($slm_obj)
    {
        global $DIC;
        $ilias = $DIC['ilias'];
        $ilLog = $DIC['ilLog'];
        $ilUser = $DIC['ilUser'];
        $lng = $DIC['lng'];
        $ilDB = $DIC['ilDB'];
        $ilSetting = $DIC['ilSetting'];
        //		$slm_obj = new ilObjSCORMLearningModule($_GET["ref_id"]);

        //variables to set in administration interface
        $b_storeObjectives = 'false';
        if ($slm_obj->getObjectives()) {
            $b_storeObjectives = 'true';
        }
        $b_storeInteractions = 'false';
        if ($slm_obj->getInteractions()) {
            $b_storeInteractions = 'true';
        }
        $b_readInteractions = 'false';
        $c_storeSessionTime = 's';//n=no, s=sco, i=ilias
        if ($slm_obj->getTime_from_lms()) {
            $c_storeSessionTime = 'i';
        }
        $i_lessonScoreMax = '-1';
        $i_lessonMasteryScore = $slm_obj->getMasteryScore();
        
        //other variables
        $b_messageLog = 'false';
        if ($ilLog->current_log_level == 30) {
            $b_messageLog = 'true';
        }
        $launchId = '0';
        if ($_GET["autolaunch"] != "") {
            $launchId = $_GET["autolaunch"];
        }
        $session_timeout = 0; //unlimited sessions
        if ($slm_obj->getSession()) {
            require_once('./Services/WebAccessChecker/classes/class.ilWACSignedPath.php');
            $session_timeout = (int) ilWACSignedPath::getCookieMaxLifetimeInSeconds();
            $max_idle = (int) ilSession::getIdleValue();
            if ($session_timeout > $max_idle) {
                $session_timeout = $max_idle;
            }
            $min_idle = (int) $ilSetting->get('session_min_idle', ilSessionControl::DEFAULT_MIN_IDLE) * 60;
            if ($session_timeout > $min_idle) {
                $session_timeout = $min_idle;
            }
            $session_timeout -= 10; //buffer
        }
        $b_autoReview = 'false';
        if ($slm_obj->getAutoReview()) {
            $b_autoReview = 'true';
        }
        $b_autoSuspend = 'false';
        if ($slm_obj->getAutoSuspend()) {
            $b_autoSuspend = 'true';
        }
        $b_debug = 'false';
        if ($slm_obj->getDebug()) {
            $b_debug = 'true';
        }
        $b_autoContinue = 'false';
        if ($slm_obj->getAutoContinue()) {
            $b_autoContinue = 'true';
        }
        $b_checkSetValues = 'false';
        if ($slm_obj->getCheck_values()) {
            $b_checkSetValues = 'true';
        }
        $b_autoLastVisited = 'false';
        if ($slm_obj->getAuto_last_visited()) {
            $b_autoLastVisited = 'true';
            if ($launchId == '0') {
                $launchId = $slm_obj->getLastVisited($ilUser->getID());
            }
        }

        $b_sessionDeactivated = 'false';
        if ($slm_obj->getSessionDeactivated()) {
            $b_sessionDeactivated = 'true';
        }

        //manifestData //extra to IliasScormManifestData
        // $s_man = "";
        $a_man = array();
        $val_set = $ilDB->queryF(
            '
			SELECT sc_item.obj_id,prereq_type,prerequisites,maxtimeallowed,timelimitaction,datafromlms,masteryscore 
			FROM sc_item, scorm_object 
			WHERE scorm_object.obj_id=sc_item.obj_id
			AND scorm_object.c_type = %s
			AND scorm_object.slm_id = %s',
            array('text','integer'),
            array('sit',$slm_obj->getId())
        );
        while ($val_rec = $ilDB->fetchAssoc($val_set)) {
            if ($val_rec["prereq_type"] != null || $val_rec["prerequisites"] != null || $val_rec["maxtimeallowed"] != null || $val_rec["timelimitaction"] != null || $val_rec["datafromlms"] != null || $val_rec["masteryscore"] != null) {
                $tmp_man = array((int) $val_rec["obj_id"],null,null,null,null,null,null);
                if ($val_rec["prereq_type"] != null) {
                    $tmp_man[1] = self::encodeURIComponent($val_rec["prereq_type"]);
                }
                if ($val_rec["prerequisites"] != null) {
                    $tmp_man[2] = self::encodeURIComponent($val_rec["prerequisites"]);
                }
                if ($val_rec["maxtimeallowed"] != null) {
                    $tmp_man[3] = self::encodeURIComponent($val_rec["maxtimeallowed"]);
                }
                if ($val_rec["timelimitaction"] != null) {
                    $tmp_man[4] = self::encodeURIComponent($val_rec["timelimitaction"]);
                }
                if ($val_rec["datafromlms"] != null) {
                    $tmp_man[5] = self::encodeURIComponent($val_rec["datafromlms"]);
                }
                if ($val_rec["masteryscore"] != null) {
                    $tmp_man[6] = self::encodeURIComponent($val_rec["masteryscore"]);
                }
                $a_man[] = $tmp_man;
            }
        }

        $s_out = '{'
            . '"refId":' . $_GET["ref_id"] . ','
            . '"objId":' . $slm_obj->getId() . ','
            . '"clientId":"' . CLIENT_ID . '",'
            . '"launchId":' . $launchId . ','
            . '"launchNr":0,'
            . '"pingSession":' . $session_timeout . ','
            . '"studentId":"' . $slm_obj->getApiStudentId() . '",'
            . '"studentName":"' . self::encodeURIComponent($slm_obj->getApiStudentName()) . '",'
            . '"studentLogin":"' . self::encodeURIComponent($ilias->account->getLogin()) . '",'
            . '"studentOu":"' . self::encodeURIComponent($ilias->account->getDepartment()) . '",'
            . '"credit":"' . str_replace("_", "-", $slm_obj->getCreditMode()) . '",'
            . '"lesson_mode":"' . $slm_obj->getDefaultLessonMode() . '",'
            . '"b_autoReview":' . $b_autoReview . ','
            . '"b_autoSuspend":' . $b_autoSuspend . ','
            . '"b_messageLog":' . $b_messageLog . ','
            . '"b_checkSetValues":' . $b_checkSetValues . ','
            . '"b_storeObjectives":' . $b_storeObjectives . ','
            . '"b_storeInteractions":' . $b_storeInteractions . ','
            . '"b_readInteractions":' . $b_readInteractions . ','
            . '"c_storeSessionTime":"' . $c_storeSessionTime . '",'
            . '"b_autoContinue":' . $b_autoContinue . ','
            . '"b_autoLastVisited":' . $b_autoLastVisited . ','
            . '"b_sessionDeactivated":' . $b_sessionDeactivated . ','
            . '"i_lessonScoreMax":' . $i_lessonScoreMax . ','
            . '"i_lessonMasteryScore":"' . $i_lessonMasteryScore . '",'
            . '"b_debug":' . $b_debug . ','
            . '"a_itemParameter":' . json_encode($a_man) . ','
            . '"status":' . json_encode(self::getStatus($slm_obj->getId(), $ilUser->getID(), $slm_obj->getAuto_last_visited())) . ','
            . '"dataDirectory":"' . self::encodeURIComponent($slm_obj->getDataDirectory("output") . '/') . '",'
            . '"img":{'
                . '"asset":"' . self::encodeURIComponent(ilUtil::getImagePath('scorm/asset.svg')) . '",'
                . '"browsed":"' . self::encodeURIComponent(ilUtil::getImagePath('scorm/browsed.svg')) . '",'
                . '"completed":"' . self::encodeURIComponent(ilUtil::getImagePath('scorm/completed.svg')) . '",'
                . '"failed":"' . self::encodeURIComponent(ilUtil::getImagePath('scorm/failed.svg')) . '",'
                . '"incomplete":"' . self::encodeURIComponent(ilUtil::getImagePath('scorm/incomplete.svg')) . '",'
                . '"not_attempted":"' . self::encodeURIComponent(ilUtil::getImagePath('scorm/not_attempted.svg')) . '",'
                . '"passed":"' . self::encodeURIComponent(ilUtil::getImagePath('scorm/passed.svg')) . '",'
                . '"running":"' . self::encodeURIComponent(ilUtil::getImagePath('scorm/running.svg')) . '"'
            . '},'
            . '"statusTxt":{'
                . '"wait":"' . self::encodeURIComponent($lng->txt("please_wait")) . '",'
                . '"status":"' . self::encodeURIComponent($lng->txt("cont_status")) . '",'
                . '"browsed":"' . self::encodeURIComponent($lng->txt("cont_sc_stat_browsed")) . '",'
                . '"completed":"' . self::encodeURIComponent($lng->txt("cont_sc_stat_completed")) . '",'
                . '"failed":"' . self::encodeURIComponent($lng->txt("cont_sc_stat_failed")) . '",'
                . '"incomplete":"' . self::encodeURIComponent($lng->txt("cont_sc_stat_incomplete")) . '",'
                . '"not_attempted":"' . self::encodeURIComponent($lng->txt("cont_sc_stat_not_attempted")) . '",'
                . '"passed":"' . self::encodeURIComponent($lng->txt("cont_sc_stat_passed")) . '",'
                . '"running":"' . self::encodeURIComponent($lng->txt("cont_sc_stat_running")) . '"'
            . '}'
        . '}';
        return $s_out;
    }
    
    public static function getIliasScormData($a_packageId)
    {
        global $DIC;
        $ilias = $DIC['ilias'];
        $ilUser = $DIC['ilUser'];
        $ilDB = $DIC['ilDB'];
        $b_readInteractions = 'false';
        $a_out = array();
        $tquery = 'SELECT sco_id,lvalue,rvalue FROM scorm_tracking '
                . 'WHERE user_id = %s AND obj_id = %s '
                . "AND sco_id > 0 AND lvalue != 'cmi.core.entry' AND lvalue != 'cmi.core.session_time'";
        if ($b_readInteractions == 'false') {
            $tquery .= " AND SUBSTR(lvalue, 1, 16) != 'cmi.interactions'";
        }
        $val_set = $ilDB->queryF(
            $tquery,
            array('integer','integer'),
            array($ilUser->getId(),$a_packageId)
        );
        while ($val_rec = $ilDB->fetchAssoc($val_set)) {
            if (!strpos($val_rec["lvalue"], "._count")) {
                $a_out[] = array( (int) $val_rec["sco_id"], $val_rec["lvalue"], self::encodeURIComponent($val_rec["rvalue"]) );
            }
        }
        return json_encode($a_out);
    }
    
    public static function getIliasScormResources($a_packageId)
    {
        global $DIC;
        $ilias = $DIC['ilias'];
        $ilDB = $DIC['ilDB'];
        //		$s_out="";
        $a_out = array();
        $s_resourceIds = "";//necessary if resources exist having different href with same identifier
        $val_set = $ilDB->queryF(
            "
			SELECT sc_resource.obj_id
			FROM scorm_tree, sc_resource
			WHERE scorm_tree.slm_id=%s 
			AND sc_resource.obj_id=scorm_tree.child",
            array('integer'),
            array($a_packageId)
        );
        while ($val_rec = $ilDB->fetchAssoc($val_set)) {
            $s_resourceIds .= "," . $val_rec["obj_id"];
        }
        $s_resourceIds = substr($s_resourceIds, 1);

        $tquery = "SELECT scorm_tree.lft, scorm_tree.child, 
			CASE WHEN sc_resource.scormtype = 'asset' THEN 1 ELSE 0 END AS asset,
			sc_resource.href
			FROM scorm_tree, sc_resource, sc_item
			WHERE scorm_tree.slm_id=%s 
			AND sc_item.obj_id=scorm_tree.child 
			AND sc_resource.import_id=sc_item.identifierref 
			AND sc_resource.obj_id in (" . $s_resourceIds . ") 
			ORDER BY scorm_tree.lft";
        $val_set = $ilDB->queryF(
            $tquery,
            array('integer'),
            array($a_packageId)
        );
        while ($val_rec = $ilDB->fetchAssoc($val_set)) {
            //			$s_out.='['.$val_rec["lft"].','.$val_rec["child"].','.$val_rec["asset"].',"'.self::encodeURIComponent($val_rec["href"]).'"],';
            $a_out[] = array( (int) $val_rec["lft"], (int) $val_rec["child"], (int) $val_rec["asset"], self::encodeURIComponent($val_rec["href"]) );
        }
        //		if(substr($s_out,(strlen($s_out)-1))==",") $s_out=substr($s_out,0,(strlen($s_out)-1));
        //		return "[".$s_out."]";
        return json_encode($a_out);
    }
    
    public static function getIliasScormTree($a_packageId)
    {
        global $DIC;
        $ilias = $DIC['ilias'];
        $ilDB = $DIC['ilDB'];
        $a_out = array();
        $tquery = "SELECT scorm_tree.child, scorm_tree.depth-3 depth, scorm_object.title, scorm_object.c_type
			FROM scorm_tree, scorm_object
			WHERE scorm_object.obj_id=scorm_tree.child
			AND scorm_tree.slm_id=%s
			AND (scorm_object.c_type='sor' OR scorm_object.c_type='sit')
			ORDER BY scorm_tree.lft";
        $val_set = $ilDB->queryF(
            $tquery,
            array('integer'),
            array($a_packageId)
        );
        while ($val_rec = $ilDB->fetchAssoc($val_set)) {
            $a_out[] = array((int) $val_rec["child"],(int) $val_rec["depth"],self::encodeURIComponent($val_rec["title"]),$val_rec["c_type"]);
        }
        return json_encode($a_out);
    }

    public static function getStatus($a_packageId, $a_user_id, $auto_last_visited, $scormType = "1.2")
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        include_once './Services/Tracking/classes/class.ilLPStatus.php';
        $oldStatus = ilLPStatus::_lookupStatus($a_packageId, $a_user_id);
        $status['saved_global_status'] = (int) $oldStatus;
        include_once './Services/Object/classes/class.ilObjectLP.php';
        $olp = ilObjectLP::getInstance($a_packageId);
        $status['lp_mode'] = $olp->getCurrentMode();
        $collection = $olp->getCollectionInstance();
        if ($collection) {
            $status['scos'] = $collection->getItems();
        } else {
            $status['scos'] = array();
        }
        $status['hash'] = ilObjSCORMInitData::setHash($a_packageId, $a_user_id);
        $status['p'] = $a_user_id;
        
        $status['last_visited'] = null;
        $status['total_time_sec'] = 0;
        $val_set = $ilDB->queryF(
            'SELECT last_visited, sco_total_time_sec, total_time_sec FROM sahs_user WHERE obj_id = %s AND user_id = %s',
            array('integer','integer'),
            array($a_packageId,$a_user_id)
        );
        $val_rec = $ilDB->fetchAssoc($val_set);
        if ($auto_last_visited) {
            $status['last_visited'] = $val_rec["last_visited"];
        }
        if ($val_rec["total_time_sec"] == null) {
            if ($val_rec["sco_total_time_sec"] == null) {
                //fall back for old ILIAS-Versions
                if ($scormType == "2004") {
                    include_once './Modules/Scorm2004/classes/class.ilSCORM2004Tracking.php';
                    $status['total_time_sec'] = (int) ilSCORM2004Tracking::getSumTotalTimeSecondsFromScos($a_packageId, $a_user_id, true);
                }
            } else {
                $status['total_time_sec'] = (int) $val_rec["sco_total_time_sec"];
            }
        } else {
            $status['total_time_sec'] = (int) $val_rec["total_time_sec"];
        }
        
        
        
        return $status;
    }
    // hash for storing data without session
    private static function setHash($a_packageId, $a_user_id)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $hash = mt_rand(1000000000, 2147483647);
        $endDate = date('Y-m-d H:i:s', mktime(date('H'), date('i'), date('s'), date('m'), date('d') + 1, date('Y')));

        $res = $ilDB->queryF(
            'SELECT count(*) cnt FROM sahs_user WHERE obj_id = %s AND user_id = %s',
            array('integer', 'integer'),
            array($a_packageId,$a_user_id)
        );
        $val_rec = $ilDB->fetchAssoc($res);
        if ($val_rec["cnt"] == 0) { //offline_mode could be inserted
            $ilDB->manipulateF(
                'INSERT INTO sahs_user (obj_id, user_id, hash, hash_end) VALUES(%s, %s, %s, %s)',
                array('integer', 'integer', 'text', 'timestamp'),
                array($a_packageId, $a_user_id, "" . $hash, $endDate)
            );
        } else {
            $ilDB->manipulateF(
                'UPDATE sahs_user SET hash = %s, hash_end = %s WHERE obj_id = %s AND user_id = %s',
                array('text', 'timestamp', 'integer', 'integer'),
                array("" . $hash, $endDate, $a_packageId, $a_user_id)
            );
        }
        //clean table
        // if (fmod($hash,100) == 0) //note: do not use % for large numbers; here php-min-Version: 4.2.0
        // {
        // $endDate = date('Y-m-d H:i:s', mktime(date('H'), date('i'), date('s'), date('m'), date('d')-2, date('Y')));
        // $ilDB->manipulateF('DELETE FROM cmi_custom WHERE lvalue = %s AND c_timestamp < %s',
        // array('text', 'timestamp'),
        // array('hash', $endDate)
        // );
        // }
        return $hash;
    }

    /**
    * Get max. number of attempts allowed for this package
    */
    public static function get_max_attempts($a_packageId)
    {
        //erased in 5.1
        return 0;
    }
}
