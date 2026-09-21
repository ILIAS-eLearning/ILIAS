<?php

use ILIAS\TestQuestionPool\ExportImport\Envelopes\QuestionImage;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Contracts\FromNormalized;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Contracts\ToNormalized;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalizing\Transformations;

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
 * @package components\ILIAS/Test
 */
class ilAssKprimChoiceAnswer implements ToNormalized, FromNormalized
{
    private $position;

    private $answertext;

    private $imageFile;

    private $imageFsDir;

    private $imageWebDir;

    private $thumbPrefix;

    private $correctness;

    public function setPosition($position): void
    {
        $this->position = $position;
    }

    public function getPosition()
    {
        return $this->position;
    }

    public function setAnswertext($answertext): void
    {
        $this->answertext = $answertext;
    }

    public function getAnswertext()
    {
        return $this->answertext;
    }

    public function setImageFile(?string $imageFile): void
    {
        $this->imageFile = $imageFile;
    }

    public function getImageFile(): ?string
    {
        return $this->imageFile;
    }

    // sk 2023-12-01: These are proxy functions to make things work like the other answertypes for Choice Questions
    public function setImage(?string $image): void
    {
        $this->setImageFile($image);
    }

    public function getImage(): ?string
    {
        return $this->getImageFile();
    }
    // End proxy functions

    public function setImageFsDir($imageFsDir): void
    {
        $this->imageFsDir = $imageFsDir;
    }

    public function getImageFsDir()
    {
        return $this->imageFsDir;
    }

    public function setImageWebDir($imageWebDir): void
    {
        $this->imageWebDir = $imageWebDir;
    }

    public function getImageWebDir()
    {
        return $this->imageWebDir;
    }

    /**
     * @param mixed $thumbPrefix
     */
    public function setThumbPrefix($thumbPrefix): void
    {
        $this->thumbPrefix = $thumbPrefix;
    }

    /**
     * @return mixed
     */
    public function getThumbPrefix()
    {
        return $this->thumbPrefix;
    }

    public function setCorrectness($correctness): void
    {
        $this->correctness = $correctness;
    }

    public function getCorrectness()
    {
        return $this->correctness;
    }

    public function getImageFsPath(): string
    {
        return $this->getImageFsDir() . $this->getImageFile();
    }

    public function getThumbFsPath(): string
    {
        return $this->getImageFsDir() . $this->getThumbPrefix() . $this->getImageFile();
    }

    public function getImageWebPath(): string
    {
        return $this->getImageWebDir() . $this->getImageFile();
    }

    public function getThumbWebPath(): string
    {
        return $this->getImageWebDir() . $this->getThumbPrefix() . $this->getImageFile();
    }

    public function toNormalized(
        Transformations $transformations,
        array $context = []
    ): array|float|bool|int|string|null
    {
        return [
            'position' => $this->position,
            'answertext' => $this->answertext,
            'image' => $this->imageFile
                ? $transformations->normalize(new QuestionImage($this->imageFile, $context['question_id'] ?? null))
                : null,
            'correctness' => $this->correctness,
        ];
    }

    public function fromNormalized(
        array $normalized,
        Transformations $transformations
    ): static
    {
        $clone = clone $this;
        $clone->position = $transformations->int($normalized['position']);
        $clone->answertext = $transformations->nullableString($normalized['answertext']);
        $clone->imageFile = $transformations->denormalize($normalized['image'], QuestionImage::class)?->getFilename();
        $clone->correctness = $transformations->int($normalized['correctness']);
        return $clone;
    }
}
