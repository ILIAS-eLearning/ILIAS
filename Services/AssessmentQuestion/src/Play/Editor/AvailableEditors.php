<?php

namespace ILIAS\AssessmentQuestion\Play\Editor;

/**
 * Class AvailableEditors
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class AvailableEditors {
	public static function getAvailableEditors() {
		//TODO get editors from DB
		$editors = [];
		$editors[MultipleChoiceEditor::class] = "MultipleChoiceEditor";
		return $editors;
	}

	public static function getDefaultEditor() {
		return MultipleChoiceEditor::class;
	}
}