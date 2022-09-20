<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package     Modules/Test
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

    public function setImageFile($imageFile): void
    {
        $this->imageFile = $imageFile;
    }

    public function getImageFile()
    {
        return $this->imageFile;
    }

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
