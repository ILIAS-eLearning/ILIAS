<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Interface ilAsqQuestion
 *
 * @author    Björn Heyser <info@bjoernheyser.de>
 * @version    $Id$
 *
 * @package    Services/AssessmentQuestion
 */
interface ilAsqQuestion
{
	/**
	 * @param int $parentId
	 * @return void
	 */
	public function setParentId($parentId);
	
	/**
	 * @return int
	 */
	public function getParentId();
	
	/**
	 * @param int $questionId
	 * @return void
	 */
	public function setId($questionId);
	
	/**
	 * @return int $questionId
	 */
	public function getId();
	
	/**
	 * @return string
	 */
	public function getTitle();
	
	/**
	 * @return string
	 */
	public function getComment();
	
	/**
	 * @return string
	 */
	public function getOwner();
	
	/**
	 * @return string $questionType
	 */
	public function getQuestionType();
	
	/**
	 * @return string
	 */
	public function getQuestionText();
	
	/**
	 * @return string
	 */
	public function getPoints();
	
	/**
	 * @return string
	 */
	public function getEstimatedWorkingTime();
	
	/**
	 * @return void
	 */
	public function load();
	
	/**
	 * @return void
	 */
	public function save();
	
	/**
	 * @return void
	 */
	public function delete();
	
	/**
	 * @param ilQTIItem $qtiItem
	 * @return void
	 */
	public function fromQtiItem(ilQTIItem $qtiItem);
	
	/**
	 * @return string
	 */
	public function toQtiXML();
	
	/**
	 * @return bool
	 */
	public function isComplete();
	
	/**
	 * @return ilAsqQuestionSolution
	 */
	public function getBestSolution();
	
	/**
	 * @return ILIAS\UI\Component\Legacy\Legacy
	 */
	public function getSuggestedSolutionOutput();
	
	/**
	 * @return string
	 */
	public function toJSON();
	
	/**
	 * @param string $offlineExportImagePath
	 */
	public function setOfflineExportImagePath($offlineExportImagePath = null);
	
	/**
	 * @param string $offlineExportPagePresentationMode
	 */
	public function setOfflineExportPagePresentationMode($offlineExportPagePresentationMode = 'presentation');
	
	/**
	 * @param integer $questionId
	 * @return ILIAS\UI\Component\Legacy\Legacy
	 */
	public static function _getSuggestedSolutionOutput($questionId);
	
	/**
	 * @param integer $questionId
	 * @return ILIAS\UI\Component\Legacy\Legacy
	 */
	public static function _getQuestionInfo($questionId);
	
	/**
	 * @param integer $questionId
	 * @return integer
	 */
	public static function _getMaximumPoints($questionId);
	
	/**
	 * @return bool
	 */
	public static function questionTitleExists($questionTitle);
}