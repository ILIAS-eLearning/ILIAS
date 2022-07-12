<?php declare(strict_types=0);

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
 * class ilcourseobjective
 * @author  Stefan Meyer <meyer@leifos.com>
 * @version $Id$
 * @extends Object
 */
class ilCourseObjective
{
    protected ilObject $course_obj;

    private int $objective_id = 0;
    private string $title = '';
    private string $description = '';
    private int $position;
    private bool $active = true;
    private int $passes = 0;
    private int $created = 0;

    protected ilDBInterface $db;
    protected ilLogger $logger;

    public function __construct(ilObject $course_obj, int $a_objective_id = 0)
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->logger = $DIC->logger()->crs();

        $this->course_obj = $course_obj;
        $this->objective_id = $a_objective_id;
        if ($this->objective_id) {
            $this->__read();
        }
    }

    public function getCourse() : ilObject
    {
        return $this->course_obj;
    }

    public static function _lookupContainerIdByObjectiveId(int $a_objective_id) : int
    {
        global $DIC;

        $ilDB = $DIC->database();
        $query = "SELECT crs_id FROM crs_objectives " .
            "WHERE objective_id = " . $ilDB->quote($a_objective_id, 'integer');
        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return (int) $row->crs_id;
        }
        return 0;
    }

    public static function _getCountObjectives(int $a_obj_id, bool $a_activated_only = false) : int
    {
        return count(ilCourseObjective::_getObjectiveIds($a_obj_id, $a_activated_only));
    }

    public static function lookupMaxPasses(int $a_objective_id) : int
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

    /**
     * @return array|string
     */
    public static function lookupObjectiveTitle(int $a_objective_id, bool $a_add_description = false)
    {
        global $DIC;

        $ilDB = $DIC->database();
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

    public function ilClone(int $a_target_id, int $a_copy_id) : void
    {
        $query = "SELECT * FROM crs_objectives " .
            "WHERE crs_id  = " . $this->db->quote($this->course_obj->getId(), 'integer') . ' ' .
            "ORDER BY position ";
        $res = $this->db->query($query);
        if ($res->numRows() === 0) {
            $this->logger->debug('.. no objectives found');
            return;
        }

        if (!is_object($new_course = ilObjectFactory::getInstanceByRefId($a_target_id, false))) {
            $this->logger->warning('Cannot create course instance');
            return;
        }
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $new_objective = new ilCourseObjective($new_course);
            $new_objective->setTitle($row->title);
            $new_objective->setDescription($row->description);
            $new_objective->setActive($row->active);
            $objective_id = $new_objective->add();
            $this->logger->debug('Added new objective nr: ' . $objective_id);

            // Clone crs_objective_tst entries
            $objective_qst = new ilCourseObjectiveQuestion($row->objective_id);
            $objective_qst->cloneDependencies($objective_id, $a_copy_id);

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

            $assignments = ilLOTestAssignments::getInstance($this->course_obj->getId());
            $assignment_it = $assignments->getAssignmentByObjective(
                $row->objective_id,
                ilLOSettings::TYPE_TEST_INITIAL
            );
            if ($assignment_it) {
                $assignment_it->cloneSettings($a_copy_id, $new_course->getId(), $objective_id);
            }

            $assignment_qt = $assignments->getAssignmentByObjective(
                $row->objective_id,
                ilLOSettings::TYPE_TEST_QUALIFIED
            );
            if ($assignment_qt) {
                $assignment_qt->cloneSettings($a_copy_id, $new_course->getId(), $objective_id);
            }

            $this->logger->debug('Finished copying question dependencies');

            // Clone crs_objective_lm entries (assigned course materials)
            $objective_material = new ilCourseObjectiveMaterials($row->objective_id);
            $objective_material->cloneDependencies($objective_id, $a_copy_id);
        }
        $this->logger->debug('Finished copying objectives');
    }

    public function setActive(bool $a_stat) : void
    {
        $this->active = $a_stat;
    }

    public function isActive() : bool
    {
        return $this->active;
    }

    public function setPasses(int $a_passes) : void
    {
        $this->passes = $a_passes;
    }

    public function getPasses() : int
    {
        return $this->passes;
    }

    public function arePassesLimited() : bool
    {
        return $this->passes > 0;
    }

    // end-patch lok

    public function setTitle(string $a_title) : void
    {
        $this->title = $a_title;
    }

    public function getTitle() : string
    {
        return $this->title;
    }

    public function setDescription(string $a_description) : void
    {
        $this->description = $a_description;
    }

    public function getDescription() : string
    {
        return $this->description;
    }

    public function setObjectiveId(int $a_objective_id) : void
    {
        $this->objective_id = $a_objective_id;
    }

    public function getObjectiveId() : int
    {
        return $this->objective_id;
    }

    public function setPosition(int $a_pos) : void
    {
        $this->position = $a_pos;
    }

    public function add() : int
    {
        $next_id = $this->db->nextId('crs_objectives');
        $query = "INSERT INTO crs_objectives (crs_id,objective_id,active,title,description,position,created,passes) " .
            "VALUES( " .
            $this->db->quote($this->course_obj->getId(), 'integer') . ", " .
            $this->db->quote($next_id, 'integer') . ", " .
            $this->db->quote($this->isActive(), 'integer') . ', ' .
            $this->db->quote($this->getTitle(), 'text') . ", " .
            $this->db->quote($this->getDescription(), 'text') . ", " .
            $this->db->quote($this->__getLastPosition() + 1, 'integer') . ", " .
            $this->db->quote(time(), 'integer') . ", " .
            $this->db->quote($this->getPasses(), 'integer') . ' ' .
            ")";
        $res = $this->db->manipulate($query);

        // refresh learning progress status after adding new objective
        ilLPStatusWrapper::_refreshStatus($this->course_obj->getId());
        return $this->objective_id = $next_id;
    }

    public function update() : void
    {
        $query = "UPDATE crs_objectives " .
            "SET title = " . $this->db->quote($this->getTitle(), 'text') . ", " .
            'active = ' . $this->db->quote($this->isActive(), 'integer') . ', ' .
            "description = " . $this->db->quote($this->getDescription(), 'text') . ", " .
            'passes = ' . $this->db->quote($this->getPasses(), 'integer') . ' ' .
            "WHERE objective_id = " . $this->db->quote($this->getObjectiveId(), 'integer') . " " .
            "AND crs_id = " . $this->db->quote($this->course_obj->getId(), 'integer') . " ";
        $res = $this->db->manipulate($query);
    }

    public function writePosition(int $a_position) : void
    {
        $query = "UPDATE crs_objectives " .
            "SET position = " . $this->db->quote((string) $a_position, 'integer') . " " .
            "WHERE objective_id = " . $this->db->quote($this->getObjectiveId(), 'integer') . " ";
        $res = $this->db->manipulate($query);
    }

    public function validate() : bool
    {
        return (bool) strlen($this->getTitle());
    }

    public function delete() : void
    {
        $tmp_obj_qst = new ilCourseObjectiveQuestion($this->getObjectiveId());
        $tmp_obj_qst->deleteAll();

        $tmp_obj_lm = new ilCourseObjectiveMaterials($this->getObjectiveId());
        $tmp_obj_lm->deleteAll();

        $query = "DELETE FROM crs_objectives " .
            "WHERE crs_id = " . $this->db->quote($this->course_obj->getId(), 'integer') . " " .
            "AND objective_id = " . $this->db->quote($this->getObjectiveId(), 'integer') . " ";
        $res = $this->db->manipulate($query);

        // refresh learning progress status after deleting objective
        ilLPStatusWrapper::_refreshStatus($this->course_obj->getId());
    }

    public function moveUp() : void
    {
        if (!$this->getObjectiveId()) {
            return;
        }
        // Stop if position is first
        if ($this->__getPosition() == 1) {
            return;
        }

        $query = "UPDATE crs_objectives " .
            "SET position = position + 1 " .
            "WHERE position = " . $this->db->quote($this->__getPosition() - 1, 'integer') . " " .
            "AND crs_id = " . $this->db->quote($this->course_obj->getId(), 'integer') . " ";
        $res = $this->db->manipulate($query);

        $query = "UPDATE crs_objectives " .
            "SET position = position - 1 " .
            "WHERE objective_id = " . $this->db->quote($this->getObjectiveId(), 'integer') . " " .
            "AND crs_id = " . $this->db->quote($this->course_obj->getId(), 'integer') . " ";
        $res = $this->db->manipulate($query);

        $this->__read();
    }

    public function moveDown() : void
    {
        if (!$this->getObjectiveId()) {
            return;
        }
        // Stop if position is last
        if ($this->__getPosition() == $this->__getLastPosition()) {
            return;
        }

        $query = "UPDATE crs_objectives " .
            "SET position = position - 1 " .
            "WHERE position = " . $this->db->quote($this->__getPosition() + 1, 'integer') . " " .
            "AND crs_id = " . $this->db->quote($this->course_obj->getId(), 'integer') . " ";
        $res = $this->db->manipulate($query);

        $query = "UPDATE crs_objectives " .
            "SET position = position + 1 " .
            "WHERE objective_id = " . $this->db->quote($this->getObjectiveId(), 'integer') . " " .
            "AND crs_id = " . $this->db->quote($this->course_obj->getId(), 'integer') . " ";
        $res = $this->db->manipulate($query);

        $this->__read();
    }

    public function __setPosition(int $a_position) : void
    {
        $this->position = $a_position;
    }

    public function __getPosition() : int
    {
        return $this->position;
    }

    public function __setCreated(int $a_created) : void
    {
        $this->created = $a_created;
    }

    public function __getCreated() : int
    {
        return $this->created;
    }

    public function __read() : void
    {
        if ($this->getObjectiveId()) {
            $query = "SELECT * FROM crs_objectives " .
                "WHERE crs_id = " . $this->db->quote($this->course_obj->getId(), 'integer') . " " .
                "AND objective_id = " . $this->db->quote($this->getObjectiveId(), 'integer') . " ";

            $res = $this->db->query($query);
            while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
                // begin-patch lok
                $this->setActive((bool) $row->active);
                $this->setPasses((int) $row->passes);
                // end-patch lok
                $this->setObjectiveId((int) $row->objective_id);
                $this->setTitle((string) $row->title);
                $this->setDescription((string) $row->description);
                $this->__setPosition((int) $row->position);
                $this->__setCreated((int) $row->created);
            }
        }
    }

    public function __getOrderColumn() : string
    {
        switch ($this->course_obj->getOrderType()) {
            case ilContainer::SORT_MANUAL:
                return 'ORDER BY position';

            case ilContainer::SORT_TITLE:
                return 'ORDER BY title';

            case ilContainer::SORT_ACTIVATION:
                return 'ORDER BY create';
        }
        return '';
    }

    public function __updateTop() : void
    {
        $query = "UPDATE crs_objectives " .
            "SET position = position - 1 " .
            "WHERE position > " . $this->db->quote($this->__getPosition(), 'integer') . " " .
            "AND crs_id = " . $this->db->quote($this->course_obj->getId(), 'integer') . " ";
        $res = $this->db->manipulate($query);
    }

    public function __getLastPosition() : int
    {
        $query = "SELECT MAX(position) pos FROM crs_objectives " .
            "WHERE crs_id = " . $this->db->quote($this->course_obj->getId(), 'integer') . " ";

        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return (int) $row->pos;
        }
        return 0;
    }

    public static function _getObjectiveIds(int $course_id, bool $a_activated_only = false) : array
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
        $ids = [];
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $ids[] = $row->objective_id;
        }
        return $ids;
    }

    public static function _deleteAll(int $course_id) : void
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        // begin-patch lok
        $ids = ilCourseObjective::_getObjectiveIds($course_id, false);
        // end-patch lok
        if ($ids === []) {
            return;
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
        ilLPStatusWrapper::_refreshStatus($course_id);
    }

    public function toXml(ilXmlWriter $writer) : void
    {
        $writer->xmlStartTag(
            'Objective',
            array(
                'online' => (int) $this->isActive(),
                'position' => $this->position,
                'id' => $this->getObjectiveId()
            )
        );
        $writer->xmlElement('Title', array(), $this->getTitle());
        $writer->xmlElement('Description', array(), $this->getDescription());

        // materials
        $materials = new ilCourseObjectiveMaterials($this->getObjectiveId());
        $materials->toXml($writer);

        // test/questions
        $test = new ilCourseObjectiveQuestion($this->getObjectiveId());
        $test->toXml($writer);

        $assignments = ilLOTestAssignments::getInstance($this->course_obj->getId());
        $assignments->toXml($writer, $this->getObjectiveId());

        ilLORandomTestQuestionPools::toXml($writer, $this->getObjectiveId());

        $writer->xmlEndTag('Objective');
    }
}
