<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2005 ILIAS open source, University of Cologne            |
    |                                                                             |
    | This program is free software; you can redistribute it and/or               |
    | modify it under the terms of the GNU General Public License                 |
    | as published by the Free Software Foundation; either version 2              |
    | of the License, or (at your option) any later version.                      |
    |                                                                             |
    | This program is distributed in the hope that it will be useful,             |
    | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
    | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
    | GNU General Public License for more details.                                |
    |                                                                             |
    | You should have received a copy of the GNU General Public License           |
    | along with this program; if not, write to the Free Software                 |
    | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
    +-----------------------------------------------------------------------------+
*/

/**
* Class ilObj<module_name>
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
* @extends ilObject
*/

class ilCourseStart
{
    public $db;

    public $ref_id;
    public $id;
    public $start_objs = array();

    /**
     * Constructor
     * @access	public
     * @param	int	reference_id or object_id
     * @param	boolean	treat the id as reference_id (true) or object_id (false)
     */
    public function __construct($a_course_ref_id, $a_course_obj_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $this->db = $ilDB;

        $this->ref_id = $a_course_ref_id;
        $this->id = $a_course_obj_id;

        $this->__read();
    }
    public function setId($a_id)
    {
        $this->id = $a_id;
    }
    public function getId()
    {
        return $this->id;
    }
    public function setRefId($a_ref_id)
    {
        $this->ref_id = $a_ref_id;
    }
    public function getRefId()
    {
        return $this->ref_id;
    }
    public function getStartObjects()
    {
        return $this->start_objs ? $this->start_objs : array();
    }
    
    /**
     * Clone dependencies
     *
     * @access public
     * @param int target id
     * @param int copy id
     *
     */
    public function cloneDependencies($a_target_id, $a_copy_id)
    {
        global $DIC;

        $ilObjDataCache = $DIC['ilObjDataCache'];
        $ilLog = $DIC['ilLog'];
        
        $ilLog->write(__METHOD__ . ': Begin course start objects...');
        
        $new_obj_id = $ilObjDataCache->lookupObjId($a_target_id);
        $start = new ilCourseStart($a_target_id, $new_obj_id);
        
        include_once('Services/CopyWizard/classes/class.ilCopyWizardOptions.php');
        $cwo = ilCopyWizardOptions::_getInstance($a_copy_id);
        $mappings = $cwo->getMappings();
        foreach ($this->getStartObjects() as $start_id => $data) {
            $item_ref_id = $data['item_ref_id'];
            if (isset($mappings[$item_ref_id]) and $mappings[$item_ref_id]) {
                $ilLog->write(__METHOD__ . ': Clone start object nr. ' . $item_ref_id);
                $start->add($mappings[$item_ref_id]);
            } else {
                $ilLog->write(__METHOD__ . ': No mapping found for start object nr. ' . $item_ref_id);
            }
        }
        $ilLog->write(__METHOD__ . ': ... end course start objects');
        return true;
    }

    public function delete($a_crs_start_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = "DELETE FROM crs_start " .
            "WHERE crs_start_id = " . $ilDB->quote($a_crs_start_id, 'integer') . " " .
            "AND crs_id = " . $ilDB->quote($this->getId(), 'integer') . " ";
        $res = $ilDB->manipulate($query);
        return true;
    }

    public function exists($a_item_ref_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = "SELECT * FROM crs_start " .
            "WHERE crs_id = " . $ilDB->quote($this->getId(), 'integer') . " " .
            "AND item_ref_id = " . $ilDB->quote($a_item_ref_id, 'integer') . " ";
        $res = $this->db->query($query);

        return $res->numRows() ? true : false;
    }

    public function add($a_item_ref_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        if ($a_item_ref_id) {
            $next_id = $ilDB->nextId('crs_start');
            $query = "INSERT INTO crs_start (crs_start_id,crs_id,item_ref_id) " .
                "VALUES( " .
                $ilDB->quote($next_id, 'integer') . ", " .
                $ilDB->quote($this->getId(), 'integer') . ", " .
                $ilDB->quote($a_item_ref_id, 'integer') . " " .
                ")";
            $res = $ilDB->manipulate($query);
            return true;
        }
        return false;
    }

    public function __deleteAll()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = "DELETE FROM crs_start " .
            "WHERE crs_id = " . $ilDB->quote($this->getId(), 'integer') . " ";
        $res = $ilDB->manipulate($query);

        return true;
    }

    public function getPossibleStarters()
    {
        include_once "Services/Object/classes/class.ilObjectActivation.php";
        foreach (ilObjectActivation::getItems($this->getRefId(), false) as $node) {
            switch ($node['type']) {
                case 'lm':
                case 'sahs':
                case 'svy':
                case 'tst':
                    $poss_items[] = $node['ref_id'];
                    break;
            }
        }
        return $poss_items ? $poss_items : array();
    }

    public function allFullfilled($user_id)
    {
        foreach ($this->getStartObjects() as $item) {
            if (!$this->isFullfilled($user_id, $item['item_ref_id'])) {
                return false;
            }
        }
        return true;
    }


    public function isFullfilled($user_id, $item_id)
    {
        global $DIC;

        $ilObjDataCache = $DIC['ilObjDataCache'];

        include_once './Modules/Course/classes/class.ilCourseLMHistory.php';
        $lm_continue = new ilCourseLMHistory($this->getRefId(), $user_id);
        $continue_data = $lm_continue->getLMHistory();

        $obj_id = $ilObjDataCache->lookupObjId($item_id);
        $type = $ilObjDataCache->lookupType($obj_id);
        
        switch ($type) {
            case 'tst':
                include_once './Modules/Test/classes/class.ilObjTestAccess.php';
                include_once './Services/Conditions/classes/class.ilConditionHandler.php';
                
                if (!ilObjTestAccess::checkCondition($obj_id, ilConditionHandler::OPERATOR_FINISHED, '', $user_id)) {
                    return false;
                }
                break;
            case 'svy':
                include_once './Modules/Survey/classes/class.ilObjSurveyAccess.php';
                if (!ilObjSurveyAccess::_lookupFinished($obj_id, $user_id)) {
                    return false;
                }
                break;
            case 'sahs':
                include_once 'Services/Tracking/classes/class.ilLPStatus.php';
                if (!ilLPStatus::_hasUserCompleted($obj_id, $user_id)) {
                    return false;
                }
                break;

            default:
                if (!isset($continue_data[$item_id])) {
                    return false;
                }
        }
        return true;
    }


    // PRIVATE
    public function __read()
    {
        global $DIC;

        $tree = $DIC['tree'];
        $ilDB = $DIC['ilDB'];

        $this->start_objs = array();

        $query = "SELECT * FROM crs_start " .
            "WHERE crs_id = " . $ilDB->quote($this->getId(), 'integer') . " ";

        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            if ($tree->isInTree($row->item_ref_id)) {
                $this->start_objs[$row->crs_start_id]['item_ref_id'] = $row->item_ref_id;
            } else {
                $this->delete($row->item_ref_id);
            }
        }
        return true;
    }
} // END class.ilObjCourseGrouping
