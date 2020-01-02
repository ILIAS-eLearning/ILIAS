<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionSolutionComparisonExpressionList.php';

/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package     Modules/Test
 */
class ilAssQuestionSkillAssignment
{
    const DEFAULT_COMPETENCE_POINTS = 1;
    
    const EVAL_MODE_BY_QUESTION_RESULT = 'result';
    const EVAL_MODE_BY_QUESTION_SOLUTION = 'solution';


    /**
     * @var ilDBInterface
     */
    private $db;

    /**
     * @var integer
     */
    private $parentObjId;

    /**
     * @var integer
     */
    private $questionId;

    /**
     * @var integer
     */
    private $skillBaseId;

    /**
     * @var integer
     */
    private $skillTrefId;

    /**
     * @var integer
     */
    private $skillPoints;

    /**
     * @var string
     */
    private $skillTitle;

    /**
     * @var string
     */
    private $skillPath;

    /**
     * @var string
     */
    private $evalMode;

    /**
     * @var ilAssQuestionSolutionComparisonExpressionList
     */
    private $solutionComparisonExpressionList;
    
    public function __construct(ilDBInterface $db)
    {
        $this->db = $db;
        
        $this->solutionComparisonExpressionList = new ilAssQuestionSolutionComparisonExpressionList($this->db);
    }

    public function loadFromDb()
    {
        $query = "
			SELECT obj_fi, question_fi, skill_base_fi, skill_tref_fi, skill_points, eval_mode
			FROM qpl_qst_skl_assigns
			WHERE obj_fi = %s
			AND question_fi = %s
			AND skill_base_fi = %s
			AND skill_tref_fi = %s
		";

        $res = $this->db->queryF(
            $query,
            array('integer', 'integer', 'integer', 'integer'),
            array($this->getParentObjId(), $this->getQuestionId(), $this->getSkillBaseId(), $this->getSkillTrefId())
        );

        $row = $this->db->fetchAssoc($res);

        if (is_array($row)) {
            $this->setSkillPoints($row['skill_points']);
            $this->setEvalMode($row['eval_mode']);
        }
        
        if ($this->getEvalMode() == self::EVAL_MODE_BY_QUESTION_SOLUTION) {
            $this->loadComparisonExpressions();
        }
    }
    
    public function loadComparisonExpressions()
    {
        $this->initSolutionComparisonExpressionList();
        $this->solutionComparisonExpressionList->load();
    }

    public function saveToDb()
    {
        if ($this->dbRecordExists()) {
            $this->db->update(
                'qpl_qst_skl_assigns',
                array(
                    'skill_points' => array('integer', (int) $this->getSkillPoints()),
                    'eval_mode' => array('text', $this->getEvalMode())
                ),
                array(
                    'obj_fi' => array('integer', $this->getParentObjId()),
                    'question_fi' => array('integer', $this->getQuestionId()),
                    'skill_base_fi' => array('integer', $this->getSkillBaseId()),
                    'skill_tref_fi' => array('integer', $this->getSkillTrefId())
                )
            );
        } else {
            $this->db->insert('qpl_qst_skl_assigns', array(
                'obj_fi' => array('integer', $this->getParentObjId()),
                'question_fi' => array('integer', $this->getQuestionId()),
                'skill_base_fi' => array('integer', $this->getSkillBaseId()),
                'skill_tref_fi' => array('integer', $this->getSkillTrefId()),
                'skill_points' => array('integer', (int) $this->getSkillPoints()),
                'eval_mode' => array('text', $this->getEvalMode())
            ));
        }

        if ($this->getEvalMode() == self::EVAL_MODE_BY_QUESTION_SOLUTION) {
            $this->saveComparisonExpressions();
        }
    }

    public function saveComparisonExpressions()
    {
        $this->initSolutionComparisonExpressionList();
        $this->solutionComparisonExpressionList->save();
    }

    public function deleteFromDb()
    {
        $query = "
			DELETE FROM qpl_qst_skl_assigns
			WHERE obj_fi = %s
			AND question_fi = %s
			AND skill_base_fi = %s
			AND skill_tref_fi = %s
		";

        $this->db->manipulateF(
            $query,
            array('integer', 'integer', 'integer', 'integer'),
            array($this->getParentObjId(), $this->getQuestionId(), $this->getSkillBaseId(), $this->getSkillTrefId())
        );
        
        $this->deleteComparisonExpressions();
    }

    public function deleteComparisonExpressions()
    {
        $this->initSolutionComparisonExpressionList();
        $this->solutionComparisonExpressionList->delete();
    }

    public function dbRecordExists()
    {
        $query = "
			SELECT COUNT(*) cnt
			FROM qpl_qst_skl_assigns
			WHERE obj_fi = %s
			AND question_fi = %s
			AND skill_base_fi = %s
			AND skill_tref_fi = %s
		";

        $res = $this->db->queryF(
            $query,
            array('integer', 'integer', 'integer', 'integer'),
            array($this->getParentObjId(), $this->getQuestionId(), $this->getSkillBaseId(), $this->getSkillTrefId())
        );

        $row = $this->db->fetchAssoc($res);

        return $row['cnt'] > 0;
    }

    /**
     * @param int $skillPoints
     */
    public function setSkillPoints($skillPoints)
    {
        $this->skillPoints = $skillPoints;
    }

    /**
     * @return int
     */
    public function getSkillPoints()
    {
        return $this->skillPoints;
    }

    /**
     * @param int $questionId
     */
    public function setQuestionId($questionId)
    {
        $this->questionId = $questionId;
    }

    /**
     * @return int
     */
    public function getQuestionId()
    {
        return $this->questionId;
    }

    /**
     * @param int $skillBaseId
     */
    public function setSkillBaseId($skillBaseId)
    {
        $this->skillBaseId = $skillBaseId;
    }

    /**
     * @return int
     */
    public function getSkillBaseId()
    {
        return $this->skillBaseId;
    }

    /**
     * @param int $skillTrefId
     */
    public function setSkillTrefId($skillTrefId)
    {
        $this->skillTrefId = $skillTrefId;
    }

    /**
     * @return int
     */
    public function getSkillTrefId()
    {
        return $this->skillTrefId;
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

    public function loadAdditionalSkillData()
    {
        require_once 'Services/Skill/classes/class.ilBasicSkill.php';
        require_once 'Services/Skill/classes/class.ilSkillTree.php';

        $this->setSkillTitle(
            ilBasicSkill::_lookupTitle($this->getSkillBaseId(), $this->getSkillTrefId())
        );

        $tree = new ilSkillTree();

        $path = $tree->getSkillTreePath(
            $this->getSkillBaseId(),
            $this->getSkillTrefId()
        );

        $nodes = array();
        foreach ($path as $node) {
            if ($node['child'] > 1 && $node['skill_id'] != $this->getSkillBaseId()) {
                $nodes[] = $node['title'];
            }
        }

        $this->setSkillPath(implode(' > ', $nodes));
    }

    public function setSkillTitle($skillTitle)
    {
        $this->skillTitle = $skillTitle;
    }

    public function getSkillTitle()
    {
        return $this->skillTitle;
    }

    public function setSkillPath($skillPath)
    {
        $this->skillPath = $skillPath;
    }

    public function getSkillPath()
    {
        return $this->skillPath;
    }

    public function getEvalMode()
    {
        return $this->evalMode;
    }

    public function setEvalMode($evalMode)
    {
        $this->evalMode = $evalMode;
    }
    
    public function hasEvalModeBySolution()
    {
        return $this->getEvalMode() == self::EVAL_MODE_BY_QUESTION_SOLUTION;
    }

    public function initSolutionComparisonExpressionList()
    {
        $this->solutionComparisonExpressionList->setQuestionId($this->getQuestionId());
        $this->solutionComparisonExpressionList->setSkillBaseId($this->getSkillBaseId());
        $this->solutionComparisonExpressionList->setSkillTrefId($this->getSkillTrefId());
    }

    public function getSolutionComparisonExpressionList()
    {
        return $this->solutionComparisonExpressionList;
    }

    public function getMaxSkillPoints()
    {
        if ($this->hasEvalModeBySolution()) {
            $maxPoints = 0;
            
            foreach ($this->solutionComparisonExpressionList->get() as $expression) {
                if ($expression->getPoints() > $maxPoints) {
                    $maxPoints = $expression->getPoints();
                }
            }
            
            return $maxPoints;
        }
        
        return $this->getSkillPoints();
    }

    /**
     * @param mixed $skillPoints
     * @return bool
     */
    public function isValidSkillPoint($skillPoints)
    {
        return (
            is_numeric($skillPoints) &&
            str_replace(array('.', ','), '', $skillPoints) == $skillPoints &&
            $skillPoints > 0
        );
    }
}
