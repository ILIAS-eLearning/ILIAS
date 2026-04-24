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

namespace ILIAS\TestQuestionPool\ExportImport\Normalizer;

use ilAssQuestionSkillAssignment;
use ilAssQuestionSolutionComparisonExpression;
use ilDBInterface;
use ILIAS\DI\Container;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Contracts\Normalizer;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Contracts\Transformations;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalizing\Attributes\Normalizes;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalizing\Envelopes\Id;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalizing\NormalizingException;

/**
 * @implements Normalizer<ilAssQuestionSkillAssignment, array>
 */
#[Normalizes(ilAssQuestionSkillAssignment::class)]
class ilAssQuestionSkillAssignmentNormalizer implements Normalizer
{
    private readonly ilDBInterface $db;

    public function __construct(
        private readonly Transformations $tt,
        Container $dic
    ) {
        $this->db = $dic->database();
    }

    /**
     * @inheritDoc
     */
    public function normalize($value): array|float|bool|int|string|null
    {
        if (!$value instanceof ilAssQuestionSkillAssignment) {
            throw new NormalizingException('Invalid value', $value);
        }

        $normalized = [
            'parent_id' => $this->tt->normalize(new Id($value->getParentObjId(), 'object')),
            'question_id' => $this->tt->normalize(new Id($value->getQuestionId(), 'question')),
            'base_id' => $this->tt->normalize(new Id($value->getSkillBaseId(), 'skill_base')),
            'tref_id' => $this->tt->normalize(new Id($value->getSkillTrefId(), 'skill_tref')),
            'original_title' => $value->getSkillTitle(),
            'original_path' => $value->getSkillPath(),
            'eval_mode' => $value->getEvalMode(),
        ];

        switch ($value->getEvalMode()) {
            case ilAssQuestionSkillAssignment::EVAL_MODE_BY_QUESTION_RESULT:
                $normalized['points'] = $value->getSkillPoints();
                break;

            case ilAssQuestionSkillAssignment::EVAL_MODE_BY_QUESTION_SOLUTION:
                $normalized['solution_comparison_expressions'] = $this->normalizeExpressionList($value);
                break;
        }
        return $normalized;
    }

    private function normalizeExpressionList(ilAssQuestionSkillAssignment $value): array
    {
        $value->initSolutionComparisonExpressionList();

        $list = [];
        foreach ($value->getSolutionComparisonExpressionList()->get() as $expression) {
            $list[] = [
                'points' => $expression->getPoints(),
                'expression' => $expression->getExpression(),
                'order_index' => $expression->getOrderIndex(),
            ];
        }

        return $list;
    }

    /**
     * @inheritDoc
     */
    public function denormalize(array|float|bool|int|string|null $value, string $type): ilAssQuestionSkillAssignment
    {
        if ($type !== ilAssQuestionSkillAssignment::class) {
            throw new NormalizingException("Invalid type for ilAssQuestionSkillAssignment: {$type}");
        }

        $assignment = new ilAssQuestionSkillAssignment($this->db);
        $assignment->setParentObjId($this->tt->denormalize($value['parent_id'], Id::class)->getId());
        $assignment->setQuestionId($this->tt->denormalize($value['question_id'], Id::class)->getId());
        $assignment->setSkillBaseId($this->tt->denormalize($value['base_id'], Id::class)->getId());
        $assignment->setSkillTrefId($this->tt->denormalize($value['tref_id'], Id::class)->getId());
        $assignment->setSkillTitle($this->tt->string($value['original_title']));
        $assignment->setSkillPath($this->tt->string($value['original_path']));
        $assignment->setEvalMode($this->tt->string($value['eval_mode']));
        $assignment->initSolutionComparisonExpressionList();

        switch ($assignment->getEvalMode()) {
            case ilAssQuestionSkillAssignment::EVAL_MODE_BY_QUESTION_RESULT:
                $assignment->setSkillPoints($this->tt->int($value['points']));
                break;

            case ilAssQuestionSkillAssignment::EVAL_MODE_BY_QUESTION_SOLUTION:
                $list = $assignment->getSolutionComparisonExpressionList();
                foreach ($value['solution_comparison_expressions'] as $normalized) {
                    $list->add($this->denormalizeExpression($normalized, $assignment));
                }
                break;
        }

        return $assignment;
    }

    private function denormalizeExpression(
        array $normalized,
        ilAssQuestionSkillAssignment $assignment
    ): ilAssQuestionSolutionComparisonExpression {
        $expression = new ilAssQuestionSolutionComparisonExpression();
        $expression->setQuestionId($assignment->getQuestionId());
        $expression->setSkillBaseId($assignment->getSkillBaseId());
        $expression->setSkillTrefId($assignment->getSkillTrefId());

        $expression->setOrderIndex($this->tt->int($normalized['order_index']));
        $expression->setExpression($this->tt->string($normalized['expression']));
        $expression->setPoints($this->tt->int($normalized['points']));

        return $expression;
    }
}
