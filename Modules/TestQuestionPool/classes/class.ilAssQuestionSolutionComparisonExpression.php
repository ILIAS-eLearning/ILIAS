<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package     Modules/TestQuestionPool
 */
class ilAssQuestionSolutionComparisonExpression
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
     * @var integer
     */
    private $orderIndex;

    /**
     * @var string
     */
    private $expression;

    /**
     * @var integer
     */
    private $points;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->questionId = null;
        $this->skillBaseId = null;
        $this->skillTrefId = null;
        $this->orderIndex = null;
        $this->expression = null;
        $this->points = null;
    }

    public function save()
    {
        $this->db->replace(
            'qpl_qst_skl_sol_expr',
            array(
                'question_fi' => array('integer', $this->getQuestionId()),
                'skill_base_fi' => array('integer', $this->getSkillBaseId()),
                'skill_tref_fi' => array('integer', $this->getSkillTrefId()),
                'order_index' => array('integer', $this->getOrderIndex())
            ),
            array(
                'expression' => array('text', $this->getExpression()),
                'points' => array('integer', $this->getPoints())
            )
        );
    }

    /**
     * @return ilDBInterface
     */
    public function getDb()
    {
        return $this->db;
    }

    /**
     * @param ilDBInterface $db
     */
    public function setDb($db)
    {
        $this->db = $db;
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

    /**
     * @return int
     */
    public function getOrderIndex()
    {
        return $this->orderIndex;
    }

    /**
     * @param int $orderIndex
     */
    public function setOrderIndex($orderIndex)
    {
        $this->orderIndex = $orderIndex;
    }

    /**
     * @return string
     */
    public function getExpression()
    {
        return $this->expression;
    }

    /**
     * @param string $expression
     */
    public function setExpression($expression)
    {
        $this->expression = $expression;
    }

    /**
     * @return int
     */
    public function getPoints()
    {
        return $this->points;
    }

    /**
     * @param int $points
     */
    public function setPoints($points)
    {
        $this->points = $points;
    }

    /**
     * @param array $data
     */
    public function initInstanceFromArray($data)
    {
        $this->setQuestionId($data['question_fi']);
        $this->setSkillBaseId($data['skill_base_fi']);
        $this->setSkillTrefId($data['skill_tref_fi']);

        $this->setOrderIndex($data['order_index']);
        $this->setExpression($data['expression']);
        $this->setPoints($data['points']);
    }
}
