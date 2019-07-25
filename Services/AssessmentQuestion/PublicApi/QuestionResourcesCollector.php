<?php

namespace ILIAS\Services\AssessmentQuestion\Collector;

/**
 * Class QuestionResourcesCollector
 *
 * @package ILIAS\Services\AssessmentQuestion\Collector
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Björn Heyser <info@bjoernheyser.de>
 */
class QuestionResourcesCollector {

	/**
	 * @var array
	 */
	protected $mobs = [];
	/**
	 * @var array
	 */
	protected $mediaFiles = [];
	/**
	 * @var array
	 */
	protected $jsFiles = [];
	/**
	 * @var array
	 */
	protected $cssFiles = [];


	/**
	 * QuestionResourcesCollector constructor
	 */
	public function __construct() {

	}


	/**
	 * @return array
	 */
	public function getMobs(): array {
		return $this->mobs;
	}


	/**
	 * @param string $mob
	 */
	public function addMob(string $mob) {
		$this->mobs[] = $mob;
	}


	/**
	 * @return array
	 */
	public function getMediaFiles(): array {
		return $this->mediaFiles;
	}


	/**
	 * @param string $mediaFile
	 */
	public function addMediaFile(string $mediaFile) {
		$this->mediaFiles[] = $mediaFile;
	}


	/**
	 * @return array
	 */
	public function getJsFiles(): array {
		return $this->jsFiles;
	}


	/**
	 * @param string $jsFile
	 */
	public function addJsFile(string $jsFile) {
		$this->jsFiles[] = $jsFile;
	}


	/**
	 * @return array
	 */
	public function getCssFiles(): array {
		return $this->cssFiles;
	}


	/**
	 * @param string $cssFile
	 */
	public function setCssFile(string $cssFile) {
		$this->cssFiles[] = $cssFile;
	}
}
