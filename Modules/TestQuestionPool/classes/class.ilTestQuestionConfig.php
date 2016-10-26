<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

// fau: testNav - new class ilTestQuestionConfig.

/**
 * Test Question configuration
 */
class ilTestQuestionConfig
{
	protected $isUnchangedAnswerPossible = false;
	protected $useUnchangedAnswerLabel = '';
	protected $enableFormChangeDetection = true;
	protected $enableBackgroundChangeDetection = false;

	/**
	 * ilTestQuestionConfig constructor.
	 */
	public function __construct()
	{
		global $lng;
		$this->useUnchangedAnswerLabel = $lng->txt('tst_unchanged_answer_is_correct');
	}

	/**
	 * Return if the saving of an unchanged answer is supported with an additional checkbox
	 * @return bool
	 */
	public function isUnchangedAnswerPossible()
	{
		return $this->isUnchangedAnswerPossible;
	}

	/**
	 * Set if the saving of an unchanged answer is supported with an additional checkbox
	 * @param 	bool 	$isUnchangedAnswerPossible
	 * @return  ilTestQuestionConfig
	 */
	public function setIsUnchangedAnswerPossible($isUnchangedAnswerPossible)
	{
		$this->isUnchangedAnswerPossible = $isUnchangedAnswerPossible;
		return $this;
	}

	/**
	 * Return the label to be used for the 'use unchanged answer' checkbox
	 * @return string
	 */
	public function getUseUnchangedAnswerLabel()
	{
		return $this->useUnchangedAnswerLabel;
	}

	/**
	 * Return the label to be used for the 'use unchanged answer' checkbox
	 * @param 	string 	$useUnchangedAnswerLabel
	 * @return 	ilTestQuestionConfig
	 */
	public function setUseUnchangedAnswerLabel($useUnchangedAnswerLabel)
	{
		$this->useUnchangedAnswerLabel = $useUnchangedAnswerLabel;
		return $this;
	}

	/**
	 * Return if the detection of form changes is enabled
	 * @return bool
	 */
	public function isFormChangeDetectionEnabled()
	{
		return $this->enableFormChangeDetection;
	}

	/**
	 * Set if the detection of form changes is enabled
	 * @param 	bool 	$enableFormChangeDetection
	 * @return	ilTestQuestionConfig
	 */
	public function setFormChangeDetectionEnabled($enableFormChangeDetection)
	{
		$this->enableFormChangeDetection = $enableFormChangeDetection;
		return $this;
	}

	/**
	 * Return if the detection of background changes is enabled
	 * @return bool
	 */
	public function isBackgroundChangeDetectionEnabled()
	{
		return $this->enableBackgroundChangeDetection;
	}

	/**
	 * Set if the detection of background changes is enabled
	 * This is set by Java and Flash questions to poll for server-side savings
	 *
	 * @param $enableBackgroundChangeDetection
	 * @return	ilTestQuestionConfig
	 */
	public function setBackgroundChangeDetectionEnabled($enableBackgroundChangeDetection)
	{
		$this->enableBackgroundChangeDetection = $enableBackgroundChangeDetection;
		return $this;
	}
}