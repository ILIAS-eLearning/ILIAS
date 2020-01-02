<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("./Modules/ScormAicc/classes/SCORM/class.ilSCORMObject.php");

/**
* SCORM Item
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ModulesScormAicc
*/
class ilSCORMItem extends ilSCORMObject
{
    public $import_id;
    public $identifierref;
    public $isvisible;
    public $parameters;
    public $prereq_type;
    public $prerequisites;
    public $maxtimeallowed;
    public $timelimitaction;
    public $datafromlms;
    public $masteryscore;

    /**
    * Constructor
    *
    * @param	int		$a_id		Object ID
    * @access	public
    */
    public function __construct($a_id = 0)
    {
        parent::__construct($a_id);
        $this->setType("sit");
    }

    public function getImportId()
    {
        return $this->import_id;
    }

    public function setImportId($a_import_id)
    {
        $this->import_id = $a_import_id;
    }

    public function getIdentifierRef()
    {
        return $this->identifierref;
    }

    public function setIdentifierRef($a_id_ref)
    {
        $this->identifierref = $a_id_ref;
    }

    public function getVisible()
    {
        return $this->isvisible;
    }

    public function setVisible($a_visible)
    {
        $this->isvisible = $a_visible;
    }

    public function getParameters()
    {
        return $this->parameters;
    }

    public function setParameters($a_par)
    {
        $this->parameters = $a_par;
    }

    public function getPrereqType()
    {
        return $this->prereq_type;
    }

    public function setPrereqType($a_p_type)
    {
        $this->prereq_type = $a_p_type;
    }

    public function getPrerequisites()
    {
        return $this->prerequisites;
    }

    public function setPrerequisites($a_pre)
    {
        $this->prerequisites = $a_pre;
    }

    public function getMaxTimeAllowed()
    {
        return $this->maxtimeallowed;
    }

    public function setMaxTimeAllowed($a_max)
    {
        $this->maxtimeallowed = $a_max;
    }

    public function getTimeLimitAction()
    {
        return $this->timelimitaction;
    }

    public function setTimeLimitAction($a_lim_act)
    {
        $this->timelimitaction = $a_lim_act;
    }

    public function getDataFromLms()
    {
        return $this->datafromlms;
    }

    public function setDataFromLms($a_data)
    {
        $this->datafromlms = $a_data;
    }

    public function getMasteryScore()
    {
        return $this->masteryscore;
    }

    public function setMasteryScore($a_score)
    {
        $this->masteryscore = $a_score;
    }

    public function read()
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        
        parent::read();

        $obj_set = $ilDB->queryF(
            'SELECT * FROM sc_item WHERE obj_id = %s',
            array('integer'),
            array($this->getId())
        );
        $obj_rec = $ilDB->fetchAssoc($obj_set);
        
        $this->setImportId($obj_rec["import_id"]);
        $this->setIdentifierRef($obj_rec["identifierref"]);
        if (strtolower($obj_rec["isvisible"]) == "false") {
            $this->setVisible(false);
        } else {
            $this->setVisible(true);
        }
        $this->setParameters($obj_rec["parameters"]);
        $this->setPrereqType($obj_rec["prereq_type"]);
        $this->setPrerequisites($obj_rec["prerequisites"]);
        $this->setMaxTimeAllowed($obj_rec["maxtimeallowed"]);
        $this->setTimeLimitAction($obj_rec["timelimitaction"]);
        $this->setDataFromLms($obj_rec["datafromlms"]);
        $this->setMasteryScore($obj_rec["masteryscore"]);
    }

    public function create()
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        
        parent::create();

        $str_visible = ($this->getVisible()) ? 'true' : 'false';
        
        $ilDB->insert('sc_item', array(
            'obj_id'			=> array('integer', $this->getId()),
            'import_id'			=> array('text', $this->getImportId()),
            'identifierref'		=> array('text', $this->getIdentifierRef()),
            'isvisible'			=> array('text', $str_visible),
            'parameters'		=> array('text', $this->getParameters()),
            'prereq_type'		=> array('text', $this->getPrereqType()),
            'prerequisites'		=> array('text', $this->getPrerequisites()),
            'maxtimeallowed'	=> array('text', $this->getMaxTimeAllowed()),
            'timelimitaction'	=> array('text', $this->getTimeLimitAction()),
            'datafromlms'		=> array('clob', $this->getDataFromLms()),
            'masteryscore'		=> array('text', $this->getMasteryScore())
        ));
    }

    public function update()
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        parent::update();
        
        $str_visible = ($this->getVisible()) ? 'true' : 'false';
        
        $ilDB->update(
            'sc_item',
            array(
                'import_id'			=> array('text', $this->getImportId()),
                'identifierref'		=> array('text', $this->getIdentifierRef()),
                'isvisible'			=> array('text', $str_visible),
                'parameters'		=> array('text', $this->getParameters()),
                'prereq_type'		=> array('text', $this->getPrereqType()),
                'prerequisites'		=> array('text', $this->getPrerequisites()),
                'maxtimeallowed'	=> array('text', $this->getMaxTimeAllowed()),
                'timelimitaction'	=> array('text', $this->getTimeLimitAction()),
                'datafromlms'		=> array('clob', $this->getDataFromLms()),
                'masteryscore'		=> array('text', $this->getMasteryScore())
            ),
            array(
                'obj_id'			=> array('integer', $this->getId())
            )
        );
    }

    /**
    * get tracking data of specified or current user
    *
    *
    */
    public function getTrackingDataOfUser($a_user_id = 0)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $ilUser = $DIC['ilUser'];

        if ($a_user_id == 0) {
            $a_user_id = $ilUser->getId();
        }
        
        $track_set = $ilDB->queryF(
            '
			SELECT lvalue, rvalue FROM scorm_tracking 
			WHERE sco_id = %s 
			AND user_id =  %s
			AND obj_id = %s',
            array('integer', 'integer', 'integer'),
            array($this->getId(), $a_user_id, $this->getSLMId())
        );
        
        $trdata = array();
        while ($track_rec = $ilDB->fetchAssoc($track_set)) {
            $trdata[$track_rec["lvalue"]] = $track_rec["rvalue"];
        }

        return $trdata;
    }

    public static function _lookupTrackingDataOfUser($a_item_id, $a_user_id = 0, $a_obj_id = 0)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $ilUser = $DIC['ilUser'];

        if ($a_user_id == 0) {
            $a_user_id = $ilUser->getId();
        }

        $track_set = $ilDB->queryF(
            '
			SELECT lvalue, rvalue FROM scorm_tracking 
			WHERE sco_id = %s 
			AND user_id =  %s
			AND obj_id = %s',
            array('integer', 'integer', 'integer'),
            array($a_item_id, $a_user_id, $a_obj_id)
        );
        
        $trdata = array();
        while ($track_rec = $ilDB->fetchAssoc($track_set)) {
            $trdata[$track_rec["lvalue"]] = $track_rec["rvalue"];
        }

        return $trdata;
    }

    public function delete()
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $ilLog = $DIC['ilLog'];

        parent::delete();

        $ilDB->manipulateF(
            'DELETE FROM sc_item WHERE obj_id = %s',
            array('integer'),
            array($this->getId())
        );
        
        $ilLog->write("SAHS Delete(ScormItem): " .
            'DELETE FROM scorm_tracking WHERE sco_id = ' . $this->getId() . ' AND obj_id = ' . $this->getSLMId());
        $ilDB->manipulateF(
            'DELETE FROM scorm_tracking WHERE sco_id = %s AND obj_id = %s',
            array('integer', 'integer'),
            array($this->getId(), $this->getSLMId())
        );
        
        include_once("./Services/Tracking/classes/class.ilLPStatusWrapper.php");
        ilLPStatusWrapper::_refreshStatus($this->getSLMId());
    }

    //function insertTrackData($a_lval, $a_rval, $a_ref_id)
    public function insertTrackData($a_lval, $a_rval, $a_obj_id)
    {
        require_once("./Modules/ScormAicc/classes/SCORM/class.ilObjSCORMTracking.php");
        //ilObjSCORMTracking::_insertTrackData($this->getId(), $a_lval, $a_rval, $a_ref_id);
        ilObjSCORMTracking::_insertTrackData($this->getId(), $a_lval, $a_rval, $a_obj_id);
    }

    // Static
    public static function _getItems($a_obj_id)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $res = $ilDB->queryF(
            '
			SELECT obj_id FROM scorm_object 
			WHERE slm_id = %s
			AND c_type = %s',
            array('integer', 'text'),
            array($a_obj_id, 'sit')
        );
        while ($row = $ilDB->fetchObject($res)) {
            $item_ids[] = $row->obj_id;
        }
        return $item_ids ? $item_ids : array();
    }

    public static function _lookupTitle($a_obj_id)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $res = $ilDB->queryF(
            'SELECT title FROM scorm_object WHERE obj_id = %s',
            array('integer'),
            array($a_obj_id)
        );
        
        while ($row = $ilDB->fetchObject($res)) {
            return $row->title;
        }
        return '';
    }
}
