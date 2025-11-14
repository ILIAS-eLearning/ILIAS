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

declare(strict_types=1);

class ilAssQuestionSolutionComparisonExpressionList
{
    private ?int $question_id = null;

    private ?int $skill_base_id = null;

    private ?int $skill_tref_id = null;

    private array $expressions = [];

    public function __construct(
        protected readonly ilDBInterface $db
    ) {
    }

    public function load(): void
    {
        $res = $this->db->queryF(
            'SELECT * FROM qpl_qst_skl_sol_expr WHERE question_fi = %s AND skill_base_fi = %s AND skill_tref_fi = %s',
            [ilDBConstants::T_INTEGER, ilDBConstants::T_INTEGER, ilDBConstants::T_INTEGER],
            [$this->getQuestionId(), $this->getSkillBaseId(), $this->getSkillTrefId()]
        );

        while ($row = $this->db->fetchAssoc($res)) {
            $expression = new ilAssQuestionSolutionComparisonExpression();
            $expression->setDb($this->db);
            $expression->initInstanceFromArray($row);

            $this->add($expression);
        }
    }

    public function save(): void
    {
        $this->delete();

        /* @var ilAssQuestionSolutionComparisonExpression $expression */
        foreach ($this->expressions as $expression) {
            $expression->setQuestionId($this->getQuestionId());
            $expression->save();
        }
    }

    public function delete(): void
    {
        $this->db->manipulateF(
            'DELETE FROM qpl_qst_skl_sol_expr WHERE question_fi = %s AND skill_base_fi = %s AND skill_tref_fi = %s',
            [ilDBConstants::T_INTEGER, ilDBConstants::T_INTEGER, ilDBConstants::T_INTEGER],
            [$this->getQuestionId(), $this->getSkillBaseId(), $this->getSkillTrefId()]
        );
    }

    public function add(ilAssQuestionSolutionComparisonExpression $expression): void
    {
        $expression->setDb($this->db);
        $expression->setQuestionId($this->getQuestionId());
        $expression->setSkillBaseId($this->getSkillBaseId());
        $expression->setSkillTrefId($this->getSkillTrefId());

        $this->expressions[$expression->getOrderIndex()] = $expression;
    }

    public function get(): array
    {
        return $this->expressions;
    }

    public function reset(): void
    {
        $this->expressions = [];
    }

    public function getQuestionId(): ?int
    {
        return $this->question_id;
    }

    public function setQuestionId(int $question_id): void
    {
        $this->question_id = $question_id;
    }

    public function getSkillBaseId(): ?int
    {
        return $this->skill_base_id;
    }

    public function setSkillBaseId(int $skill_base_id): void
    {
        $this->skill_base_id = $skill_base_id;
    }

    public function getSkillTrefId(): ?int
    {
        return $this->skill_tref_id;
    }

    public function setSkillTrefId(int $skill_tref_id): void
    {
        $this->skill_tref_id = $skill_tref_id;
    }
}
