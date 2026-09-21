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

use ILIAS\TestQuestionPool\ExportImport\Foundation\Contracts\FromNormalized;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Contracts\ToNormalized;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalizing\Transformations;
use ILIAS\TestQuestionPool\ExportImport\Envelopes\QuestionImage;

/**
* Class for matching question terms
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @ingroup components\ILIASTestQuestionPool
*/
class assAnswerMatchingTerm implements ToNormalized, FromNormalized
{
    protected string $text;
    protected string $picture;
    protected int $identifier;

    public function __construct(string $text = "", string $picture = "", int $identifier = 0)
    {
        $this->text = $text;
        $this->picture = $picture;

        $this->identifier = ($identifier !== 0) ? $identifier : $this->createIdentifier();
    }

    protected function createIdentifier(): int
    {
        $id = random_int(1, 100000);
        return $id;
    }

    public function getText(): string
    {
        return $this->text;
    }
    public function withText(string $text): self
    {
        $clone = clone $this;
        $clone->text = $text;
        return $clone;
    }

    public function getPicture(): string
    {
        return $this->picture;
    }
    public function withPicture(string $picture): self
    {
        $clone = clone $this;
        $clone->picture = $picture;
        return $clone;
    }

    public function getIdentifier(): int
    {
        return $this->identifier;
    }
    public function withIdentifier(int $identifier): self
    {
        $clone = clone $this;
        $clone->identifier = $identifier;
        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function toNormalized(
        Transformations $transformations,
        array $context = []
    ): array|float|bool|int|string|null
    {
        return [
            'text' => $this->text,
            'picture' => $this->picture
                ? $transformations->normalize(new QuestionImage($this->picture, $context['question_id'] ?? null))
                : null,
            'identifier' => $this->identifier,
        ];
    }

    /**
     * @inheritDoc
     */
    public function fromNormalized(
        array $normalized,
        Transformations $transformations
    ): self
    {
        return $this->withText($transformations->string($normalized['text']))
            ->withPicture($transformations->denormalize($normalized['picture'], QuestionImage::class)?->getFilename() ?? '')
            ->withIdentifier($transformations->int($normalized['identifier']));
    }
}
