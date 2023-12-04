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

/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package components\ILIAS/Test
 */
class ilAssKprimChoiceAnswer
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
    public function setImage(?string $image): ?string
    {
        return $this->setImageFile($image);
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
}
