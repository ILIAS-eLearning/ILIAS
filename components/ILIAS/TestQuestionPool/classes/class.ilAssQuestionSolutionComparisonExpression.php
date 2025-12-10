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

class ilAssQuestionSolutionComparisonExpression
{
    protected ilDBInterface $db;

    private ?int $question_id = null;

    private ?int $skill_base_id = null;

    private ?int $skill_tref_id = null;

    private ?int $order_index = null;

    private ?string $expression = null;

    private ?int $points = null;

    public function save(): void
    {
        $this->db->replace(
            'qpl_qst_skl_sol_expr',
            [
                'question_fi' => [ilDBConstants::T_INTEGER, $this->getQuestionId()],
                'skill_base_fi' => [ilDBConstants::T_INTEGER, $this->getSkillBaseId()],
                'skill_tref_fi' => [ilDBConstants::T_INTEGER, $this->getSkillTrefId()],
                'order_index' => [ilDBConstants::T_INTEGER, $this->getOrderIndex()]
            ],
            [
                'expression' => [ilDBConstants::T_TEXT, $this->getExpression()],
                'points' => [ilDBConstants::T_INTEGER, $this->getPoints()]
            ]
        );
    }

    public function setDb(ilDBInterface $db): void
    {
        $this->db = $db;
    }

    public function getQuestionId(): ?int
    {
        return $this->question_id;
    }

    public function setQuestionId(?int $question_id): void
    {
        $this->question_id = $question_id;
    }

    public function getSkillBaseId(): ?int
    {
        return $this->skill_base_id;
    }

    public function setSkillBaseId(?int $skill_base_id): void
    {
        $this->skill_base_id = $skill_base_id;
    }

    public function getSkillTrefId(): ?int
    {
        return $this->skill_tref_id;
    }

    public function setSkillTrefId(?int $skill_tref_id): void
    {
        $this->skill_tref_id = $skill_tref_id;
    }

    public function getOrderIndex(): ?int
    {
        return $this->order_index;
    }

    public function setOrderIndex(?int $order_index): void
    {
        $this->order_index = $order_index;
    }

    public function getExpression(): ?string
    {
        return $this->expression;
    }

    public function setExpression(?string $expression): void
    {
        $this->expression = $expression;
    }

    public function getPoints(): ?int
    {
        return $this->points;
    }

    public function setPoints(?int $points): void
    {
        $this->points = $points;
    }

    public function initInstanceFromArray(array $data): void
    {
        $this->setQuestionId($data['question_fi']);
        $this->setSkillBaseId($data['skill_base_fi']);
        $this->setSkillTrefId($data['skill_tref_fi']);

        $this->setOrderIndex($data['order_index']);
        $this->setExpression($data['expression']);
        $this->setPoints($data['points']);
    }
}
