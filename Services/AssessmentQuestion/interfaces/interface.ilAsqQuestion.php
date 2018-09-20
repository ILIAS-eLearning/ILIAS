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
	 * @param $parentId
	 */
	public function setParentId($parentId) : void;
	
	/**
	 * @return int
	 */
	public function getParentId() : int;
	
	/**
	 * @param int $questionId
	 */
	public function setId($questionId) : void;
	
	/**
	 * @return int
	 */
	public function getId() : int;
	
	/**
	 * @return string
	 */
	public function getTitle() : string;
	
	/**
	 * @return string
	 */
	public function getComment() : string;
	
	/**
	 * @return int
	 */
	public function getOwner() : int;
	
	/**
	 * @return string
	 */
	public function getQuestionType() : string;
	
	/**
	 * @return string
	 */
	public function getQuestionText() : string;
	
	/**
	 * @return float
	 */
	public function getPoints() : float;
	
	/**
	 * @return string
	 */
	public function getEstimatedWorkingTime() : string;
	
	/**
	 * Loads question data
	 */
	public function load() : void;
	
	/**
	 * Save question data
	 */
	public function save() : void;
	
	/**
	 * Delete question
	 */
	public function delete() : void;
	
	/**
	 * @param ilQTIItem $qtiItem
	 */
	public function fromQtiItem(ilQTIItem $qtiItem) : void;
	
	/**
	 * @return string
	 */
	public function toQtiXML() : string;
	
	/**
	 * @return bool
	 */
	public function isComplete() : bool;
	
	/**
	 * @return ilAsqQuestionSolution
	 */
	public function getBestSolution() : ilAsqQuestionSolution;
	
	/**
	 * @return \ILIAS\UI\Component\Component
	 */
	public function getSuggestedSolutionOutput() : \ILIAS\UI\Component\Component;
	
	/**
	 * @return string
	 */
	public function toJSON() : string;
	
	/**
	 * @param string $offlineExportImagePath
	 */
	public function setOfflineExportImagePath($offlineExportImagePath = null) : void;
	
	/**
	 * @param string $offlineExportPagePresentationMode
	 */
	public function setOfflineExportPagePresentationMode($offlineExportPagePresentationMode = 'presentation') : void;
}