<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionSolutionComparisonExpression.php';

/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package     Modules/Test
 */
class ilAssQuestionSolutionComparisonExpressionList
{
    /**
     * @var ilDBInterface
     */
    protected $db;

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
     * @var array
     */
    private $expressions;
    
    /**
     * @param ilDBInterface $db
     */
    public function __construct(ilDBInterface $db)
    {
        $this->db = $db;
        
        $this->questionId = null;
        $this->skillBaseId = null;
        $this->skillTrefId = null;
        
        $this->expressions = array();
    }
    
    public function load()
    {
        $query = "
			SELECT *
			FROM qpl_qst_skl_sol_expr
			WHERE question_fi = %s AND skill_base_fi = %s AND skill_tref_fi = %s
		";

        $res = $this->db->queryF(
            $query,
            array('integer', 'integer', 'integer'),
            array($this->getQuestionId(), $this->getSkillBaseId(), $this->getSkillTrefId())
        );

        while ($row = $this->db->fetchAssoc($res)) {
            $expression = new ilAssQuestionSolutionComparisonExpression();
            $expression->setDb($this->db);
            $expression->initInstanceFromArray($row);

            $this->add($expression);
        }
    }
    
    public function save()
    {
        $this->delete();
        
        foreach ($this->expressions as $orderIndex => $expression) {
            /* @var ilAssQuestionSolutionComparisonExpression $expression */
            
            $expression->setQuestionId($this->getQuestionId());
            $expression->save();
        }
    }
    
    public function delete()
    {
        $query = "
			DELETE FROM qpl_qst_skl_sol_expr
			WHERE question_fi = %s AND skill_base_fi = %s AND skill_tref_fi = %s
		";
        
        $this->db->manipulateF(
            $query,
            array('integer', 'integer', 'integer'),
            array($this->getQuestionId(), $this->getSkillBaseId(), $this->getSkillTrefId())
        );
    }
    
    public function add(ilAssQuestionSolutionComparisonExpression $expression)
    {
        $expression->setDb($this->db);
        $expression->setQuestionId($this->getQuestionId());
        $expression->setSkillBaseId($this->getSkillBaseId());
        $expression->setSkillTrefId($this->getSkillTrefId());
        
        $this->expressions[$expression->getOrderIndex()] = $expression;
    }

    public function get()
    {
        return $this->expressions;
    }
    
    public function reset()
    {
        $this->expressions = array();
    }

    /**
     * @return int
     */
    public function getQuestionId()
    {
        return $this->questionId;
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
    public function getSkillBaseId()
    {
        return $this->skillBaseId;
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
    public function getSkillTrefId()
    {
        return $this->skillTrefId;
    }

    /**
     * @param int $skillTrefId
     */
    public function setSkillTrefId($skillTrefId)
    {
        $this->skillTrefId = $skillTrefId;
    }
}
