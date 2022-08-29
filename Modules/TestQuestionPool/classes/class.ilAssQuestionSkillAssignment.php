<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\Skill\Service\SkillTreeService;

require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionSolutionComparisonExpressionList.php';

/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package     Modules/Test
 */
class ilAssQuestionSkillAssignment
{
    public const DEFAULT_COMPETENCE_POINTS = 1;

    public const EVAL_MODE_BY_QUESTION_RESULT = 'result';
    public const EVAL_MODE_BY_QUESTION_SOLUTION = 'solution';


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

    private SkillTreeService $skill_tree_service;

    public function __construct(ilDBInterface $db)
    {
        global $DIC;

        $this->db = $db;

        $this->solutionComparisonExpressionList = new ilAssQuestionSolutionComparisonExpressionList($this->db);
        $this->skill_tree_service = $DIC->skills()->tree();
    }

    public function loadFromDb(): void
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

    public function loadComparisonExpressions(): void
    {
        $this->initSolutionComparisonExpressionList();
        $this->solutionComparisonExpressionList->load();
    }

    public function saveToDb(): void
    {
        if ($this->dbRecordExists()) {
            $this->db->update(
                'qpl_qst_skl_assigns',
                array(
                    'skill_points' => array('integer', $this->getSkillPoints()),
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
                'skill_points' => array('integer', $this->getSkillPoints()),
                'eval_mode' => array('text', $this->getEvalMode())
            ));
        }

        if ($this->getEvalMode() == self::EVAL_MODE_BY_QUESTION_SOLUTION) {
            $this->saveComparisonExpressions();
        }
    }

    public function saveComparisonExpressions(): void
    {
        $this->initSolutionComparisonExpressionList();
        $this->solutionComparisonExpressionList->save();
    }

    public function deleteFromDb(): void
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

    public function deleteComparisonExpressions(): void
    {
        $this->initSolutionComparisonExpressionList();
        $this->solutionComparisonExpressionList->delete();
    }

    public function dbRecordExists(): bool
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

    public function isSkillUsed(): bool
    {
        $query = "
			SELECT COUNT(*) cnt
			FROM qpl_qst_skl_assigns
			WHERE obj_fi = %s
			AND skill_base_fi = %s
			AND skill_tref_fi = %s
		";

        $res = $this->db->queryF(
            $query,
            array('integer', 'integer', 'integer'),
            array($this->getParentObjId(), $this->getSkillBaseId(), $this->getSkillTrefId())
        );

        $row = $this->db->fetchAssoc($res);

        return $row['cnt'] > 0;
    }

    /**
     * @param int $skillPoints
     */
    public function setSkillPoints($skillPoints): void
    {
        $this->skillPoints = $skillPoints;
    }

    /**
     * @return int
     */
    public function getSkillPoints(): int
    {
        return $this->skillPoints;
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
    public function getQuestionId(): int
    {
        return $this->questionId;
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
    public function getSkillBaseId(): int
    {
        return $this->skillBaseId;
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
    public function getSkillTrefId(): int
    {
        return $this->skillTrefId;
    }

    /**
     * @param int $parentObjId
     */
    public function setParentObjId($parentObjId): void
    {
        $this->parentObjId = $parentObjId;
    }

    /**
     * @return int
     */
    public function getParentObjId(): int
    {
        return $this->parentObjId;
    }

    public function loadAdditionalSkillData(): void
    {
        $this->setSkillTitle(
            ilBasicSkill::_lookupTitle($this->getSkillBaseId(), $this->getSkillTrefId())
        );

        $path = $this->skill_tree_service->getSkillTreePath(
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

    public function setSkillTitle($skillTitle): void
    {
        $this->skillTitle = $skillTitle;
    }

    public function getSkillTitle(): string
    {
        return $this->skillTitle;
    }

    public function setSkillPath($skillPath): void
    {
        $this->skillPath = $skillPath;
    }

    public function getSkillPath(): string
    {
        return $this->skillPath;
    }

    public function getEvalMode(): string
    {
        return $this->evalMode;
    }

    public function setEvalMode($evalMode): void
    {
        $this->evalMode = $evalMode;
    }

    public function hasEvalModeBySolution(): bool
    {
        return $this->getEvalMode() == self::EVAL_MODE_BY_QUESTION_SOLUTION;
    }

    public function initSolutionComparisonExpressionList(): void
    {
        $this->solutionComparisonExpressionList->setQuestionId($this->getQuestionId());
        $this->solutionComparisonExpressionList->setSkillBaseId($this->getSkillBaseId());
        $this->solutionComparisonExpressionList->setSkillTrefId($this->getSkillTrefId());
    }

    public function getSolutionComparisonExpressionList(): ilAssQuestionSolutionComparisonExpressionList
    {
        return $this->solutionComparisonExpressionList;
    }

    public function getMaxSkillPoints(): int
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
    public function isValidSkillPoint($skillPoints): bool
    {
        return (
            is_numeric($skillPoints) &&
            str_replace(array('.', ','), '', $skillPoints) == $skillPoints &&
            $skillPoints > 0
        );
    }
}
