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

use DateTimeImmutable;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Contracts\Normalizer;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Contracts\Transformations;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalizing\Attributes\Normalizes;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalizing\Envelopes\Id;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalizing\NormalizingException;
use ILIAS\TestQuestionPool\ExportImport\Envelopes\QuestionImage;
use ILIAS\TestQuestionPool\Questions\SuggestedSolution\SuggestedSolution;
use ILIAS\TestQuestionPool\Questions\SuggestedSolution\SuggestedSolutionFile;
use ILIAS\TestQuestionPool\Questions\SuggestedSolution\SuggestedSolutionLink;

/**
 * @implements Normalizer<SuggestedSolution, array>
 */
#[Normalizes(SuggestedSolution::class)]
class SuggestedSolutionNormalizer implements Normalizer
{
    public function __construct(
        private readonly Transformations $tt,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function normalize($value): array|float|bool|int|string|null
    {
        if (!$value instanceof SuggestedSolution) {
            throw new NormalizingException('Invalid value', $value);
        }

        $normalized = [
            'id' => $this->tt->normalize(new Id($value->getId(), 'suggested_solution')),
            'question_id' => $this->tt->normalize(new Id($value->getQuestionId(), 'question')),
            'subquestion_index' => $value->getSubquestionIndex(),
            'import_id' => $value->getImportId(),
            'last_update' => $this->tt->normalize($value->getLastUpdate()),
        ];

        if ($value instanceof SuggestedSolutionLink) {
            $normalized['type'] = $value->getType();
            $normalized['internal_link'] = $value->getInternalLink();
        }

        if ($value instanceof SuggestedSolutionFile) {
            $normalized['type'] = $value->getType();
            $normalized['title'] = $value->getTitle();
            $normalized['mime'] = $value->getMime();
            $normalized['size'] = $value->getSize();

            $normalized['file'] = $this->tt->normalize(
                new QuestionImage($value->getFilename(), $value->getQuestionId(), QuestionImage::TYPE_SOLUTION)
            );

        }

        return $normalized;
    }

    /**
     * @inheritDoc
     */
    public function denormalize(array|float|bool|int|string|null $value, string $type): SuggestedSolution
    {
        if ($type !== SuggestedSolution::class && !in_array(SuggestedSolution::class, class_parents($type))) {
            throw new NormalizingException("Invalid type: {$type}");
        }

        // If abstract class expected we need to lookup the concrete type from the normalized value
        $denormalized_type = $this->tt->string($value['type']);
        if ($type === SuggestedSolution::class) {
            $type = match($denormalized_type) {
                SuggestedSolution::TYPE_FILE => SuggestedSolutionFile::class,
                SuggestedSolution::TYPE_LM => SuggestedSolutionLink::class,
                SuggestedSolution::TYPE_LM_CHAPTER => SuggestedSolutionLink::class,
                SuggestedSolution::TYPE_LM_PAGE => SuggestedSolutionLink::class,
                SuggestedSolution::TYPE_GLOSARY_TERM => SuggestedSolutionLink::class,
                default => throw new NormalizingException("Invalid denormalized type: {$denormalized_type}"),
            };
        }

        $id = $this->tt->denormalize($value['id'], Id::class)->getId();
        $question_id = $this->tt->denormalize($value['question_id'], Id::class)->getId();
        $subquestion_index = $this->tt->int($value['subquestion_index']);
        $import_id = $this->tt->string($value['import_id']);
        $last_update = $this->tt->denormalize($value['last_update'], DateTimeImmutable::class);

        switch ($type) {
            case SuggestedSolutionFile::class:
                return new SuggestedSolutionFile(
                    $id,
                    $question_id,
                    $subquestion_index,
                    $import_id,
                    $last_update,
                    $denormalized_type,
                    ''
                )
                ->withTitle($this->tt->string($value['title']))
                ->withFilename($this->tt->denormalize($value['file'], QuestionImage::class)->getFilename())
                ->withMime($this->tt->string($value['mime']))
                ->withSize($this->tt->int($value['size']));
            case SuggestedSolutionLink::class:
                return new SuggestedSolutionLink(
                    $id,
                    $question_id,
                    $subquestion_index,
                    $import_id,
                    $last_update,
                    $denormalized_type,
                    $this->tt->string($value['internal_link'])
                );
            default:
                throw new NormalizingException("Invalid type: {$type}");
        }
    }
}
