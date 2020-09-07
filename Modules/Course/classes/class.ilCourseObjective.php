<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* class ilcourseobjective
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
* @extends Object
*/
class ilCourseObjective
{
    public $db = null;

    public $course_obj = null;
    public $objective_id = null;
    
    // begin-patch lok
    protected $active = true;
    protected $passes = 0;
    // end-patch lok
    
    /**
     * Constructor
     * @param ilObject $course_obj
     * @param int $a_objective_id
     */
    public function __construct($course_obj, $a_objective_id = 0)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $this->db = $ilDB;
        $this->course_obj = $course_obj;

        $this->objective_id = $a_objective_id;
        if ($this->objective_id) {
            $this->__read();
        }
    }
    
    /**
     * @return ilObjCourse
     */
    public function getCourse()
    {
        return $this->course_obj;
    }
    
    /**
     * Get container of object
     *
     * @access public
     * @static
     *
     * @param int objective id
     */
    public static function _lookupContainerIdByObjectiveId($a_objective_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = "SELECT crs_id FROM crs_objectives " .
            "WHERE objective_id = " . $ilDB->quote($a_objective_id, 'integer');
        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return $row->crs_id;
        }
        return false;
    }
    
    /**
     * get count objectives
     *
     * @access public
     * @param int obj_id
     * @return
     * @static
     */
    // begin-patch lok
    public static function _getCountObjectives($a_obj_id, $a_activated_only = false)
    {
        return count(ilCourseObjective::_getObjectiveIds($a_obj_id, $a_activated_only));
    }
    
    public static function lookupMaxPasses($a_objective_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = 'SELECT passes from crs_objectives ' .
                'WHERE objective_id = ' . $ilDB->quote($a_objective_id, 'integer');
        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return (int) $row->passes;
        }
        return 0;
    }
    
    public static function lookupObjectiveTitle($a_objective_id, $a_add_description = false)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = 'SELECT title,description from crs_objectives ' .
                'WHERE objective_id = ' . $ilDB->quote($a_objective_id, 'integer');
        $res = $ilDB->query($query);
        while ($row = $ilDB->fetchAssoc($res)) {
            if (!$a_add_description) {
                return $row['title'];
            } else {
                return $row;
            }
        }
        return "";
    }
    // end-patch lok
    
    /**
     * clone objectives
     *
     * @access public
     * @param int target id
     * @param int copy id
     *
     */
    public function ilClone($a_target_id, $a_copy_id)
    {
        global $DIC;

        $ilLog = $DIC['ilLog'];
        
        ilLoggerFactory::getLogger('crs')->debug('Start cloning learning objectives');
        
        $query = "SELECT * FROM crs_objectives " .
            "WHERE crs_id  = " . $this->db->quote($this->course_obj->getId(), 'integer') . ' ' .
            "ORDER BY position ";
        $res = $this->db->query($query);
        if (!$res->numRows()) {
            ilLoggerFactory::getLogger('crs')->debug('.. no objectives found');
            return true;
        }
        
        if (!is_object($new_course = ilObjectFactory::getInstanceByRefId($a_target_id, false))) {
            ilLoggerFactory::getLogger('crs')->warning('Cannot create course instance');
            return true;
        }
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $new_objective = new ilCourseObjective($new_course);
            $new_objective->setTitle($row->title);
            $new_objective->setDescription($row->description);
            $new_objective->setActive($row->active);
            $objective_id = $new_objective->add();
            ilLoggerFactory::getLogger('crs')->debug('Added new objective nr: ' . $objective_id);
            
            // Clone crs_objective_tst entries
            include_once('Modules/Course/classes/class.ilCourseObjectiveQuestion.php');
            $objective_qst = new ilCourseObjectiveQuestion($row->objective_id);
            $objective_qst->cloneDependencies($objective_id, $a_copy_id);
            
            include_once './Modules/Course/classes/Objectives/class.ilLORandomTestQuestionPools.php';
            include_once './Modules/Course/classes/Objectives/class.ilLOSettings.php';
            $random_i = new ilLORandomTestQuestionPools(
                $this->getCourse()->getId(),
                $row->objective_id,
                ilLOSettings::TYPE_TEST_INITIAL,
                0
            );
            $random_i->copy($a_copy_id, $new_course->getId(), $objective_id);
            
            $random_q = new ilLORandomTestQuestionPools(
                $this->getCourse()->getId(),
                $row->objective_id,
                ilLOSettings::TYPE_TEST_QUALIFIED,
                0
            );
            $random_q->copy($a_copy_id, $new_course->getId(), $objective_id);
            
            include_once './Modules/Course/classes/Objectives/class.ilLOTestAssignments.php';
            $assignments = ilLOTestAssignments::getInstance($this->course_obj->getId());
            $assignment_it = $assignments->getAssignmentByObjective($row->objective_id, ilLOSettings::TYPE_TEST_INITIAL);
            if ($assignment_it) {
                $assignment_it->cloneSettings($a_copy_id, $new_course->getId(), $objective_id);
            }

            $assignment_qt = $assignments->getAssignmentByObjective($row->objective_id, ilLOSettings::TYPE_TEST_QUALIFIED);
            if ($assignment_qt) {
                $assignment_qt->cloneSettings($a_copy_id, $new_course->getId(), $objective_id);
            }

            ilLoggerFactory::getLogger('crs')->debug('Finished copying question dependencies');
            
            // Clone crs_objective_lm entries (assigned course materials)
            include_once('Modules/Course/classes/class.ilCourseObjectiveMaterials.php');
            $objective_material = new ilCourseObjectiveMaterials($row->objective_id);
            $objective_material->cloneDependencies($objective_id, $a_copy_id);
        }
        ilLoggerFactory::getLogger('crs')->debug('Finished copying objectives');
    }
    
    // begin-patch lok
    public function setActive($a_stat)
    {
        $this->active = $a_stat;
    }
    
    public function isActive()
    {
        return $this->active;
    }
    
    public function setPasses($a_passes)
    {
        $this->passes = $a_passes;
    }
    
    public function getPasses()
    {
        return $this->passes;
    }
    
    public function arePassesLimited()
    {
        return $this->passes > 0;
    }
    // end-patch lok

    public function setTitle($a_title)
    {
        $this->title = $a_title;
    }
    public function getTitle()
    {
        return $this->title;
    }
    public function setDescription($a_description)
    {
        $this->description = $a_description;
    }
    public function getDescription()
    {
        return $this->description;
    }
    public function setObjectiveId($a_objective_id)
    {
        $this->objective_id = $a_objective_id;
    }
    public function getObjectiveId()
    {
        return $this->objective_id;
    }
    
    // begin-patch optes_lok_export
    public function setPosition($a_pos)
    {
        $this->position = $a_pos;
    }
    // end-patch optes_lok_export

    public function add()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        // begin-patch lok
        $next_id = $ilDB->nextId('crs_objectives');
        $query = "INSERT INTO crs_objectives (crs_id,objective_id,active,title,description,position,created,passes) " .
            "VALUES( " .
            $ilDB->quote($this->course_obj->getId(), 'integer') . ", " .
            $ilDB->quote($next_id, 'integer') . ", " .
            $ilDB->quote($this->isActive(), 'integer') . ', ' .
            $ilDB->quote($this->getTitle(), 'text') . ", " .
            $ilDB->quote($this->getDescription(), 'text') . ", " .
            $ilDB->quote($this->__getLastPosition() + 1, 'integer') . ", " .
            $ilDB->quote(time(), 'integer') . ", " .
            $ilDB->quote($this->getPasses(), 'integer') . ' ' .
            ")";
        $res = $ilDB->manipulate($query);
        // end-patch lok
        
        // refresh learning progress status after adding new objective
        include_once("./Services/Tracking/classes/class.ilLPStatusWrapper.php");
        ilLPStatusWrapper::_refreshStatus($this->course_obj->getId());
        
        return $this->objective_id = $next_id;
    }

    public function update()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        // begin-patch lok
        $query = "UPDATE crs_objectives " .
            "SET title = " . $ilDB->quote($this->getTitle(), 'text') . ", " .
            'active = ' . $ilDB->quote($this->isActive(), 'integer') . ', ' .
            "description = " . $ilDB->quote($this->getDescription(), 'text') . ", " .
            'passes = ' . $ilDB->quote($this->getPasses(), 'integer') . ' ' .
            "WHERE objective_id = " . $ilDB->quote($this->getObjectiveId(), 'integer') . " " .
            "AND crs_id = " . $ilDB->quote($this->course_obj->getId(), 'integer') . " ";
        $res = $ilDB->manipulate($query);
        // end-patch lok
        
        return true;
    }
    
    /**
     * write position
     *
     * @access public
     * @param int new position
     * @return
     */
    public function writePosition($a_position)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = "UPDATE crs_objectives " .
            "SET position = " . $this->db->quote((string) $a_position, 'integer') . " " .
            "WHERE objective_id = " . $this->db->quote($this->getObjectiveId(), 'integer') . " ";
        $res = $ilDB->manipulate($query);
    }
    
    /**
     * validate
     *
     * @access public
     * @param
     * @return
     */
    public function validate()
    {
        return (bool) strlen($this->getTitle());
    }
    
    public function delete()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        include_once './Modules/Course/classes/class.ilCourseObjectiveQuestion.php';

        $tmp_obj_qst = new ilCourseObjectiveQuestion($this->getObjectiveId());
        $tmp_obj_qst->deleteAll();

        include_once './Modules/Course/classes/class.ilCourseObjectiveMaterials.php';

        $tmp_obj_lm = new ilCourseObjectiveMaterials($this->getObjectiveId());
        $tmp_obj_lm->deleteAll();


        $query = "DELETE FROM crs_objectives " .
            "WHERE crs_id = " . $ilDB->quote($this->course_obj->getId(), 'integer') . " " .
            "AND objective_id = " . $ilDB->quote($this->getObjectiveId(), 'integer') . " ";
        $res = $ilDB->manipulate($query);

        // refresh learning progress status after deleting objective
        include_once("./Services/Tracking/classes/class.ilLPStatusWrapper.php");
        ilLPStatusWrapper::_refreshStatus($this->course_obj->getId());

        return true;
    }

    public function moveUp()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        if (!$this->getObjectiveId()) {
            return false;
        }
        // Stop if position is first
        if ($this->__getPosition() == 1) {
            return false;
        }

        $query = "UPDATE crs_objectives " .
            "SET position = position + 1 " .
            "WHERE position = " . $ilDB->quote($this->__getPosition() - 1, 'integer') . " " .
            "AND crs_id = " . $ilDB->quote($this->course_obj->getId(), 'integer') . " ";
        $res = $ilDB->manipulate($query);
        
        $query = "UPDATE crs_objectives " .
            "SET position = position - 1 " .
            "WHERE objective_id = " . $ilDB->quote($this->getObjectiveId(), 'integer') . " " .
            "AND crs_id = " . $ilDB->quote($this->course_obj->getId(), 'integer') . " ";
        $res = $ilDB->manipulate($query);

        $this->__read();

        return true;
    }

    public function moveDown()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        if (!$this->getObjectiveId()) {
            return false;
        }
        // Stop if position is last
        if ($this->__getPosition() == $this->__getLastPosition()) {
            return false;
        }
        
        $query = "UPDATE crs_objectives " .
            "SET position = position - 1 " .
            "WHERE position = " . $ilDB->quote($this->__getPosition() + 1, 'integer') . " " .
            "AND crs_id = " . $ilDB->quote($this->course_obj->getId(), 'integer') . " ";
        $res = $ilDB->manipulate($query);
        
        $query = "UPDATE crs_objectives " .
            "SET position = position + 1 " .
            "WHERE objective_id = " . $ilDB->quote($this->getObjectiveId(), 'integer') . " " .
            "AND crs_id = " . $ilDB->quote($this->course_obj->getId(), 'integer') . " ";
        $res = $ilDB->manipulate($query);

        $this->__read();

        return true;
    }

    // PRIVATE
    public function __setPosition($a_position)
    {
        $this->position = $a_position;
    }
    public function __getPosition()
    {
        return $this->position;
    }
    public function __setCreated($a_created)
    {
        $this->created = $a_created;
    }
    public function __getCreated()
    {
        return $this->created;
    }


    public function __read()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        if ($this->getObjectiveId()) {
            $query = "SELECT * FROM crs_objectives " .
                "WHERE crs_id = " . $ilDB->quote($this->course_obj->getId(), 'integer') . " " .
                "AND objective_id = " . $ilDB->quote($this->getObjectiveId(), 'integer') . " ";


            $res = $this->db->query($query);
            while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
                // begin-patch lok
                $this->setActive($row->active);
                $this->setPasses($row->passes);
                // end-patch lok
                $this->setObjectiveId($row->objective_id);
                $this->setTitle($row->title);
                $this->setDescription($row->description);
                $this->__setPosition($row->position);
                $this->__setCreated($row->created);
            }
            return true;
        }
        return false;
    }

    public function __getOrderColumn()
    {
        switch ($this->course_obj->getOrderType()) {
            case ilContainer::SORT_MANUAL:
                return 'ORDER BY position';

            case ilContainer::SORT_TITLE:
                return 'ORDER BY title';

            case ilContainer::SORT_ACTIVATION:
                return 'ORDER BY create';
        }
        return false;
    }

    public function __updateTop()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = "UPDATE crs_objectives " .
            "SET position = position - 1 " .
            "WHERE position > " . $ilDB->quote($this->__getPosition(), 'integer') . " " .
            "AND crs_id = " . $ilDB->quote($this->course_obj->getId(), 'integer') . " ";
        $res = $ilDB->manipulate($query);

        return true;
    }

    public function __getLastPosition()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = "SELECT MAX(position) pos FROM crs_objectives " .
            "WHERE crs_id = " . $ilDB->quote($this->course_obj->getId(), 'integer') . " ";

        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return $row->pos;
        }
        return 0;
    }

    // STATIC
    // begin-patch lok
    public static function _getObjectiveIds($course_id, $a_activated_only = false)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        if ($a_activated_only) {
            $query = "SELECT objective_id FROM crs_objectives " .
                "WHERE crs_id = " . $ilDB->quote($course_id, 'integer') . " " .
                'AND active = ' . $ilDB->quote(1, 'integer') . ' ' .
                "ORDER BY position";
        } else {
            $query = "SELECT objective_id FROM crs_objectives " .
                "WHERE crs_id = " . $ilDB->quote($course_id, 'integer') . " " .
                "ORDER BY position";
        }

        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $ids[] = $row->objective_id;
        }

        return $ids ? $ids : array();
    }
    // end-patch lok

    public static function _deleteAll($course_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        // begin-patch lok
        $ids = ilCourseObjective::_getObjectiveIds($course_id, false);
        // end-patch lok
        if (!count($ids)) {
            return true;
        }

        $in = $ilDB->in('objective_id', $ids, false, 'integer');


        $query = "DELETE FROM crs_objective_lm WHERE  " . $in;
        $res = $ilDB->manipulate($query);

        $query = "DELETE FROM crs_objective_tst WHERE " . $in;
        $res = $ilDB->manipulate($query);
        
        $query = "DELETE FROM crs_objective_qst WHERE " . $in;
        $res = $ilDB->manipulate($query);
        
        $query = "DELETE FROM crs_objectives WHERE crs_id = " . $ilDB->quote($course_id, 'integer');
        $res = $ilDB->manipulate($query);

        // refresh learning progress status after deleting objectives
        include_once("./Services/Tracking/classes/class.ilLPStatusWrapper.php");
        ilLPStatusWrapper::_refreshStatus($course_id);

        return true;
    }
    
    // begin-patch optes_lok_export
    /**
     * write objective xml
     * @param ilXmlWriter $writer
     */
    public function toXml(ilXmlWriter $writer)
    {
        $writer->xmlStartTag(
            'Objective',
            array(
                'online' => (int) $this->isActive(),
                'position' => (int) $this->position,
                'id' => (int) $this->getObjectiveId()
            )
        );
        $writer->xmlElement('Title', array(), $this->getTitle());
        $writer->xmlElement('Description', array(), $this->getDescription());
        
        // materials
        include_once './Modules/Course/classes/class.ilCourseObjectiveMaterials.php';
        $materials = new ilCourseObjectiveMaterials($this->getObjectiveId());
        $materials->toXml($writer);
        
        // test/questions
        include_once './Modules/Course/classes/class.ilCourseObjectiveQuestion.php';
        $test = new ilCourseObjectiveQuestion($this->getObjectiveId());
        $test->toXml($writer);
        
        include_once './Modules/Course/classes/Objectives/class.ilLOTestAssignments.php';
        $assignments = ilLOTestAssignments::getInstance($this->course_obj->getId());
        $assignments->toXml($writer, $this->getObjectiveId());
        
        include_once './Modules/Course/classes/Objectives/class.ilLORandomTestQuestionPools.php';
        ilLORandomTestQuestionPools::toXml($writer, $this->getObjectiveId());
        
        $writer->xmlEndTag('Objective');
    }
    // end-patch optes_lok_export
}
