<?php

namespace ILIAS\AssessmentQuestion\Play\Presenter;

/**
 * Class AvailablePresenters
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class AvailablePresenters {
	public static function getAvailablePresenters() {
		//TODO get editors from DB
		$editors = [];
		$editors[DefaultPresenter::class] = "DefaultPresenter";
		return $editors;
	}

	public static function getDefaultPresenter() {
		return DefaultPresenter::class;
	}
}