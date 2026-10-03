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

use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalize\FromNormalized;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalize\ToNormalized;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalize\Transformations;

/**
* Class for matching question pairs
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @ingroup components\ILIASTestQuestionPool
*/
class assAnswerMatchingPair implements ToNormalized, FromNormalized
{
    protected assAnswerMatchingTerm $term;
    protected assAnswerMatchingDefinition $definition;
    protected float $points;

    public function __construct(
        assAnswerMatchingTerm $term,
        assAnswerMatchingDefinition $definition,
        float $points = 0.0
    ) {
        $this->term = $term;
        $this->definition = $definition;
        $this->points = $points;
    }

    public function getTerm(): assAnswerMatchingTerm
    {
        return $this->term;
    }
    public function withTerm(assAnswerMatchingTerm $term): self
    {
        $clone = clone $this;
        $clone->term = $term;
        return $clone;
    }

    public function getDefinition(): assAnswerMatchingDefinition
    {
        return $this->definition;
    }
    public function withDefinition(assAnswerMatchingDefinition $definition): self
    {
        $clone = clone $this;
        $clone->definition = $definition;
        return $clone;
    }

    public function getPoints(): float
    {
        return $this->points;
    }
    public function withPoints(float $points): self
    {
        $clone = clone $this;
        $clone->points = $points;
        return $clone;
    }

    #[\Override]
    public function toNormalized(
        Transformations $transformations,
        array $context = []
    ): array|float|bool|int|string|null
    {
        return [
            'points' => $this->points,
            'term' => $transformations->normalize($this->term, $context),
            'definition' => $transformations->normalize($this->definition, $context),
        ];
    }

    #[\Override]
    public function fromNormalized(
        array $normalized,
        Transformations $transformations
    ): self
    {
        return $this->withPoints($transformations->float($normalized['points']))
            ->withTerm($transformations->denormalize($normalized['term'], $this->term))
            ->withDefinition($transformations->denormalize($normalized['definition'], $this->definition));
    }
}
