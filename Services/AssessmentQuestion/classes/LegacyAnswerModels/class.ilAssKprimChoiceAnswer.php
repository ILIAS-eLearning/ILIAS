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

	public function setPosition($position)
	{
		$this->position = $position;
	}

	public function getPosition()
	{
		return $this->position;
	}

	public function setAnswertext($answertext)
	{
		$this->answertext = $answertext;
	}

	public function getAnswertext()
	{
		return $this->answertext;
	}

	public function setImageFile($imageFile)
	{
		$this->imageFile = $imageFile;
	}

	public function getImageFile()
	{
		return $this->imageFile;
	}

	public function setImageFsDir($imageFsDir)
	{
		$this->imageFsDir = $imageFsDir;
	}

	public function getImageFsDir()
	{
		return $this->imageFsDir;
	}

	public function setImageWebDir($imageWebDir)
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
	public function setThumbPrefix($thumbPrefix)
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

	public function setCorrectness($correctness)
	{
		$this->correctness = $correctness;
	}

	public function getCorrectness()
	{
		return $this->correctness;
	}

	public function getImageFsPath()
	{
		return $this->getImageFsDir().$this->getImageFile();
	}

	public function getThumbFsPath()
	{
		return $this->getImageFsDir().$this->getThumbPrefix().$this->getImageFile();
	}

	public function getImageWebPath()
	{
		return $this->getImageWebDir().$this->getImageFile();
	}

	public function getThumbWebPath()
	{
		return $this->getImageWebDir().$this->getThumbPrefix().$this->getImageFile();
	}
} 