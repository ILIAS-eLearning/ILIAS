<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilAsqQuestionResourcesCollector
 *
 * @author    Björn Heyser <info@bjoernheyser.de>
 * @version    $Id$
 *
 * @package    Services/AssessmentQuestion
 */
class ilAsqQuestionResourcesCollector
{
	/**
	 * @var array
	 */
	protected $mobs = array();
	
	/**
	 * @var array
	 */
	protected $mediaFiles = array();
	
	/**
	 * @var array
	 */
	protected $jsFiles = array();
	
	/**
	 * @var array
	 */
	protected $cssFiles = array();
	
	/**
	 * @return array
	 */
	public function getMobs(): array
	{
		return $this->mobs;
	}
	
	/**
	 * @param string $mob
	 */
	public function addMob(string $mob)
	{
		$this->mobs[] = $mob;
	}
	
	/**
	 * @return array
	 */
	public function getMediaFiles(): array
	{
		return $this->mediaFiles;
	}
	
	/**
	 * @param string $mediaFile
	 */
	public function addMediaFile(string $mediaFile)
	{
		$this->mediaFiles[] = $mediaFile;
	}
	
	/**
	 * @return array
	 */
	public function getJsFiles(): array
	{
		return $this->jsFiles;
	}
	
	/**
	 * @param string $jsFiles
	 */
	public function addJsFile(string $jsFile)
	{
		$this->jsFiles[] = $jsFile;
	}
	
	/**
	 * @return array
	 */
	public function getCssFiles(): array
	{
		return $this->cssFiles;
	}
	
	/**
	 * @param string $cssFiles
	 */
	public function setCssFile(string $cssFile)
	{
		$this->cssFiles[] = $cssFile;
	}
}