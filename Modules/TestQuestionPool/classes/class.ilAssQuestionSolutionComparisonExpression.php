<?php

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

    public function save(): void
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
    public function getDb(): ilDBInterface
    {
        return $this->db;
    }

    /**
     * @param ilDBInterface $db
     */
    public function setDb($db): void
    {
        $this->db = $db;
    }

    /**
     * @return int
     */
    public function getQuestionId(): ?int
    {
        return $this->questionId;
    }

    /**
     * @param int $questionId
     */
    public function setQuestionId($questionId): void
    {
        $this->questionId = $questionId;
    }

    /**
     * @return int
     */
    public function getSkillBaseId(): ?int
    {
        return $this->skillBaseId;
    }

    /**
     * @param int $skillBaseId
     */
    public function setSkillBaseId($skillBaseId): void
    {
        $this->skillBaseId = $skillBaseId;
    }

    /**
     * @return int
     */
    public function getSkillTrefId(): ?int
    {
        return $this->skillTrefId;
    }

    /**
     * @param int $skillTrefId
     */
    public function setSkillTrefId($skillTrefId): void
    {
        $this->skillTrefId = $skillTrefId;
    }

    /**
     * @return int
     */
    public function getOrderIndex(): ?int
    {
        return $this->orderIndex;
    }

    /**
     * @param int $orderIndex
     */
    public function setOrderIndex($orderIndex): void
    {
        $this->orderIndex = $orderIndex;
    }

    /**
     * @return string
     */
    public function getExpression(): ?string
    {
        return $this->expression;
    }

    /**
     * @param string $expression
     */
    public function setExpression($expression): void
    {
        $this->expression = $expression;
    }

    /**
     * @return int
     */
    public function getPoints(): ?int
    {
        return $this->points;
    }

    /**
     * @param int $points
     */
    public function setPoints($points): void
    {
        $this->points = $points;
    }

    /**
     * @param array $data
     */
    public function initInstanceFromArray($data): void
    {
        $this->setQuestionId($data['question_fi']);
        $this->setSkillBaseId($data['skill_base_fi']);
        $this->setSkillTrefId($data['skill_tref_fi']);

        $this->setOrderIndex($data['order_index']);
        $this->setExpression($data['expression']);
        $this->setPoints($data['points']);
    }
}
