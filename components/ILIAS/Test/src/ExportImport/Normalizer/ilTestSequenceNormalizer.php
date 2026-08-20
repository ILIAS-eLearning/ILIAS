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

namespace ILIAS\Test\ExportImport\Normalizer;

use ilDBInterface;
use ILIAS\DI\Container;
use ILIAS\Test\TestDIC;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Contracts\Transformations;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalizing\Envelopes\Id;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Contracts\Normalizer;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalizing\Attributes\Normalizes;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalizing\NormalizingException;
use ILIAS\TestQuestionPool\Questions\GeneralQuestionPropertiesRepository;
use ilTestSequence;

/**
 * @implements Normalizer<ilTestSequence, array>
 */
#[Normalizes(ilTestSequence::class)]
class ilTestSequenceNormalizer implements Normalizer
{
    private readonly ilDBInterface $db;
    private readonly GeneralQuestionPropertiesRepository $repository;

    public function __construct(
        private readonly Transformations $tt,
        Container $dic,
        TestDIC $local_dic,
    ) {
        $this->db = $dic->database();
        $this->repository = $local_dic['question.general_properties.repository'];
    }

    /**
     * @inheritDoc
     */
    public function normalize($value): array|float|bool|int|string|null
    {
        if (!$value instanceof ilTestSequence) {
            throw new NormalizingException('Invalid value', $value);
        }

        return [
            'active_id' => $this->tt->normalize(new Id($value->getActiveId(), 'participant')),
            'attempt' => $value->getPass(),
            'sequence' => $value->sequencedata['sequence'],
            'postponed' => $this->normalizeQuestions($value->sequencedata['postponed'] ?? []),
            'hidden' => $this->normalizeQuestions($value->sequencedata['hidden'] ?? []),
            'ans_opt_confirmed' => $value->isAnsweringOptionalQuestionsConfirmed(),
            'optional_questions' => $this->normalizeQuestions($value->getOptionalQuestions()),
        ];
    }

    private function normalizeQuestions(array $questions): array
    {
        return array_map(
            fn(int $question_id): mixed => $this->tt->normalize(new Id($question_id, 'question')),
            $questions
        );
    }

    /**
     * @inheritDoc
     */
    public function denormalize(array|float|bool|int|string|null $value, string $type): ilTestSequence
    {
        if ($type !== ilTestSequence::class) {
            throw new NormalizingException("Invalid type for ilTestSequence: {$type}");
        }

        $active_id = $this->tt->denormalize($value['active_id'], Id::class)->getId();
        $attempt = $this->tt->int($value['attempt']);

        $sequence = new ilTestSequence($this->db, $active_id, $attempt, $this->repository);

        $sequence->setAnsweringOptionalQuestionsConfirmed($this->tt->bool($value['ans_opt_confirmed']));
        $sequence->sequencedata['sequence'] = $this->denormalizeSequence($value['sequence']);
        $sequence->sequencedata['postponed'] = $this->denormalizeQuestions($value['postponed']);
        $sequence->sequencedata['hidden'] = $this->denormalizeQuestions($value['hidden']);

        $optional = $this->denormalizeQuestions($value['optional_questions']);
        foreach ($optional as $question_id) {
            $sequence->setQuestionOptional($question_id);
        }

        return $sequence;
    }

    private function denormalizeSequence(array $normalized): array
    {
        $sequence = [];
        foreach ($normalized as $key => $item) {
            $sequence[$this->tt->int($key)] = $this->tt->int($item);
        }
        return $sequence;
    }

    private function denormalizeQuestions(array $normalized): array
    {
        return array_map(
            fn(mixed $item): mixed => $this->tt->denormalize($item, Id::class)->getId(),
            $normalized
        );
    }
}
