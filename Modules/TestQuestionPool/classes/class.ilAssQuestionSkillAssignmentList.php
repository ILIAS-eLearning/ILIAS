<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionSkillAssignment.php';

/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package     Modules/Test
 */
class ilAssQuestionSkillAssignmentList
{
    /**
     * @var ilDBInterface
     */
    private $db;

    /**
     * @var integer
     */
    private $parentObjId;

    /**
     * @var array
     */
    private $assignments;

    /**
     * @var array
     */
    private $numAssignsBySkill;

    /**
     * @var array
     */
    private $maxPointsBySkill;

    /**
     * @var integer
     */
    private $questionIdFilter;

    public function __construct(ilDBInterface $db)
    {
        $this->db = $db;
        
        $this->parentObjId = null;
        $this->assignments = array();
        $this->numAssignsBySkill = array();
        $this->maxPointsBySkill = array();
        $this->questionIdFilter = null;
    }

    /**
     * @param int $parentObjId
     */
    public function setParentObjId($parentObjId)
    {
        $this->parentObjId = $parentObjId;
    }

    /**
     * @return int
     */
    public function getParentObjId()
    {
        return $this->parentObjId;
    }

    /**
     * @return int
     */
    public function getQuestionIdFilter()
    {
        return $this->questionIdFilter;
    }

    /**
     * @param int $questionIdFilter
     */
    public function setQuestionIdFilter($questionIdFilter)
    {
        $this->questionIdFilter = $questionIdFilter;
    }

    public function reset()
    {
        $this->assignments = array();
        $this->numAssignsBySkill = array();
        $this->maxPointsBySkill = array();
    }

    public function addAssignment(ilAssQuestionSkillAssignment $assignment)
    {
        if (!isset($this->assignments[$assignment->getQuestionId()])) {
            $this->assignments[$assignment->getQuestionId()] = array();
        }

        $this->assignments[$assignment->getQuestionId()][] = $assignment;
    }

    private function incrementNumAssignsBySkill(ilAssQuestionSkillAssignment $assignment)
    {
        $key = $this->buildSkillKey($assignment->getSkillBaseId(), $assignment->getSkillTrefId());

        if (!isset($this->numAssignsBySkill[$key])) {
            $this->numAssignsBySkill[$key] = 0;
        }

        $this->numAssignsBySkill[$key]++;
    }

    private function incrementMaxPointsBySkill(ilAssQuestionSkillAssignment $assignment)
    {
        $key = $this->buildSkillKey($assignment->getSkillBaseId(), $assignment->getSkillTrefId());

        if (!isset($this->maxPointsBySkill[$key])) {
            $this->maxPointsBySkill[$key] = 0;
        }

        $this->maxPointsBySkill[$key] += $assignment->getMaxSkillPoints();
    }

    public function loadFromDb()
    {
        $this->reset();

        $res = $this->db->query("
			SELECT obj_fi, question_fi, skill_base_fi, skill_tref_fi, skill_points, eval_mode
			FROM qpl_qst_skl_assigns
			WHERE {$this->getWhereConditions()}
		");

        while ($row = $this->db->fetchAssoc($res)) {
            $assignment = $this->buildSkillQuestionAssignmentByArray($row);

            if ($assignment->hasEvalModeBySolution()) {
                $assignment->loadComparisonExpressions(); // db query
            }
            
            $this->addAssignment($assignment);
            $this->incrementNumAssignsBySkill($assignment);
            $this->incrementMaxPointsBySkill($assignment);
        }
    }
    
    private function getWhereConditions()
    {
        $conditions = array(
            'obj_fi = ' . $this->db->quote($this->getParentObjId(), 'integer')
        );
        
        if ($this->getQuestionIdFilter()) {
            $conditions[] = 'question_fi = ' . $this->db->quote($this->getQuestionIdFilter(), 'integer');
        }
        
        return implode(' AND ', $conditions);
    }

    /**
     * @param array $data
     * @return ilAssQuestionSkillAssignment
     */
    private function buildSkillQuestionAssignmentByArray($data)
    {
        $assignment = new ilAssQuestionSkillAssignment($this->db);

        $assignment->setParentObjId($data['obj_fi']);
        $assignment->setQuestionId($data['question_fi']);
        $assignment->setSkillBaseId($data['skill_base_fi']);
        $assignment->setSkillTrefId($data['skill_tref_fi']);
        $assignment->setSkillPoints($data['skill_points']);
        $assignment->setEvalMode($data['eval_mode']);

        return $assignment;
    }

    private function buildSkillKey($skillBaseId, $skillTrefId)
    {
        return $skillBaseId . ':' . $skillTrefId;
    }

    public function loadAdditionalSkillData()
    {
        foreach ($this->assignments as $assignmentsByQuestion) {
            foreach ($assignmentsByQuestion as $assignment) {
                $assignment->loadAdditionalSkillData();
            }
        }
    }

    public function getAssignmentsByQuestionId($questionId)
    {
        if (!isset($this->assignments[$questionId])) {
            return array();
        }

        return $this->assignments[$questionId];
    }

    public function isAssignedToQuestionId($skillBaseId, $skillTrefId, $questionId)
    {
        if (!isset($this->assignments[$questionId])) {
            return false;
        }
        
        foreach ($this->assignments[$questionId] as $assignment) {
            if ($assignment->getSkillBaseId() != $skillBaseId) {
                continue;
            }
            
            if ($assignment->getSkillTrefId() != $skillTrefId) {
                continue;
            }
            
            return true;
        }

        return false;
    }

    public function getUniqueAssignedSkills()
    {
        require_once 'Services/Skill/classes/class.ilBasicSkill.php';

        $skills = array();

        foreach ($this->assignments as $assignmentsByQuestion) {
            foreach ($assignmentsByQuestion as $assignment) {
                /* @var ilAssQuestionSkillAssignment $assignment */
                
                $key = $this->buildSkillKey($assignment->getSkillBaseId(), $assignment->getSkillTrefId());

                if (!isset($skills[$key])) {
                    $skills[$key] = array(
                        'skill' => new ilBasicSkill($assignment->getSkillBaseId()),
                        'skill_base_id' => $assignment->getSkillBaseId(),
                        'skill_tref_id' => $assignment->getSkillTrefId(),
                        'skill_title' => $assignment->getSkillTitle(),
                        'skill_path' => $assignment->getSkillPath(),
                        'num_assigns' => $this->getNumAssignsBySkill(
                            $assignment->getSkillBaseId(),
                            $assignment->getSkillTrefId()
                        ),
                        'max_points' => $this->getMaxPointsBySkill(
                            $assignment->getSkillBaseId(),
                            $assignment->getSkillTrefId()
                        )
                    );
                }
            }
        }

        return $skills;
    }

    public function isAssignedSkill($skillBaseId, $skillTrefId)
    {
        foreach ($this->getUniqueAssignedSkills() as $assignedSkill) {
            if ($assignedSkill['skill_base_id'] != $skillBaseId) {
                continue;
            }

            if ($assignedSkill['skill_tref_id'] == $skillTrefId) {
                return true;
            }
        }

        return false;
    }

    public function getNumAssignsBySkill($skillBaseId, $skillTrefId)
    {
        return $this->numAssignsBySkill[$this->buildSkillKey($skillBaseId, $skillTrefId)];
    }

    public function getMaxPointsBySkill($skillBaseId, $skillTrefId)
    {
        return $this->maxPointsBySkill[$this->buildSkillKey($skillBaseId, $skillTrefId)];
    }
    
    public function hasSkillsAssignedLowerThanBarrier()
    {
        require_once 'Modules/Test/classes/class.ilObjAssessmentFolder.php';
        $globalBarrier = ilObjAssessmentFolder::getSkillTriggerAnswerNumberBarrier();
        
        foreach ($this->getUniqueAssignedSkills() as $skillData) {
            if ($skillData['num_assigns'] < $globalBarrier) {
                return true;
            }
        }
        
        return false;
    }
}
