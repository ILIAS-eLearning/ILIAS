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

class ilAssQuestionSkillAssignmentList
{
    private ?int $parent_obj_id = null;

    private array $assignments = [];

    private array $num_assigns_by_skill = [];

    private array $max_points_by_skill = [];

    private ?int $question_id_filter = null;

    public function __construct(
        private readonly ilDBInterface $db
    ) {
    }

    public function setParentObjId(?int $parent_obj_id): void
    {
        $this->parent_obj_id = $parent_obj_id;
    }

    public function getParentObjId(): ?int
    {
        return $this->parent_obj_id;
    }

    public function getQuestionIdFilter(): ?int
    {
        return $this->question_id_filter;
    }

    public function setQuestionIdFilter(?int $question_id_filter): void
    {
        $this->question_id_filter = $question_id_filter;
    }

    public function reset(): void
    {
        $this->assignments = [];
        $this->num_assigns_by_skill = [];
        $this->max_points_by_skill = [];
    }

    public function addAssignment(ilAssQuestionSkillAssignment $assignment): void
    {
        $this->assignments[$assignment->getQuestionId()] ??= [];
        $this->assignments[$assignment->getQuestionId()][] = $assignment;
    }

    private function incrementNumAssignsBySkill(ilAssQuestionSkillAssignment $assignment): void
    {
        $key = $this->buildSkillKey($assignment->getSkillBaseId(), $assignment->getSkillTrefId());

        $this->num_assigns_by_skill[$key] ??= 0;
        $this->num_assigns_by_skill[$key]++;
    }

    private function incrementMaxPointsBySkill(ilAssQuestionSkillAssignment $assignment): void
    {
        $key = $this->buildSkillKey($assignment->getSkillBaseId(), $assignment->getSkillTrefId());

        $this->max_points_by_skill[$key] ??= 0;
        $this->max_points_by_skill[$key] += $assignment->getMaxSkillPoints();
    }

    public function loadFromDb(): void
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
                $assignment->loadComparisonExpressions();
            }

            $this->addAssignment($assignment);
            $this->incrementNumAssignsBySkill($assignment);
            $this->incrementMaxPointsBySkill($assignment);
        }
    }

    private function getWhereConditions(): string
    {
        $conditions = ["obj_fi = {$this->db->quote($this->getParentObjId(), ilDBConstants::T_INTEGER)}"];

        if ($this->getQuestionIdFilter()) {
            $conditions[] = "question_fi = {$this->db->quote($this->getQuestionIdFilter(), ilDBConstants::T_INTEGER)}";
        }

        return implode(' AND ', $conditions);
    }

    private function buildSkillQuestionAssignmentByArray(array $data): ilAssQuestionSkillAssignment
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

    private function buildSkillKey(int $skill_base_id, int $skill_tref_id): string
    {
        return "{$skill_base_id}:{$skill_tref_id}";
    }

    public function loadAdditionalSkillData(): void
    {
        foreach ($this->assignments as $assignments_by_question) {
            foreach ($assignments_by_question as $assignment) {
                $assignment->loadAdditionalSkillData();
            }
        }
    }

    /**
     * @return ilAssQuestionSkillAssignment[]
     */
    public function getAssignmentsByQuestionId(int $question_id): array
    {
        return $this->assignments[$question_id] ?? [];
    }

    public function isAssignedToQuestionId(int $skill_base_id, int $skill_tref_id, int $question_id): bool
    {
        if (!isset($this->assignments[$question_id])) {
            return false;
        }

        foreach ($this->assignments[$question_id] as $assignment) {
            if ($assignment->getSkillBaseId() !== $skill_base_id) {
                continue;
            }

            if ($assignment->getSkillTrefId() !== $skill_tref_id) {
                continue;
            }

            return true;
        }

        return false;
    }

    public function getUniqueAssignedSkills(): array
    {
        $skills = [];

        foreach ($this->assignments as $assignments_by_question) {
            foreach ($assignments_by_question as $assignment) {
                $key = $this->buildSkillKey($assignment->getSkillBaseId(), $assignment->getSkillTrefId());
                $skills[$key] ??= [
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
                ];
            }
        }

        return $skills;
    }

    public function isAssignedSkill(int $skill_base_id, int $skill_tref_id): bool
    {
        foreach ($this->getUniqueAssignedSkills() as $assignedSkill) {
            if ($assignedSkill['skill_base_id'] !== $skill_base_id) {
                continue;
            }

            if ($assignedSkill['skill_tref_id'] === $skill_tref_id) {
                return true;
            }
        }

        return false;
    }

    public function getNumAssignsBySkill(int $skill_base_id, int $skill_tref_id)
    {
        return $this->num_assigns_by_skill[$this->buildSkillKey($skill_base_id, $skill_tref_id)] ?? null;
    }

    public function getMaxPointsBySkill(int $skill_base_id, int $skill_tref_id)
    {
        return $this->max_points_by_skill[$this->buildSkillKey($skill_base_id, $skill_tref_id)] ?? null;
    }

    public function hasSkillsAssignedLowerThanBarrier(): bool
    {
        $global_barrier = (new ilObjTestFolder())
            ->getGlobalSettingsRepository()
            ->getGlobalSettings()
            ->getSkillTriggeringNumberOfAnswers();

        foreach ($this->getUniqueAssignedSkills() as $skill_data) {
            if ($skill_data['num_assigns'] < $global_barrier) {
                return true;
            }
        }

        return false;
    }
}
