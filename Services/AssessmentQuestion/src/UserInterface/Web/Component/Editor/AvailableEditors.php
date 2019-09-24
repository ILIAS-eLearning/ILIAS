<?php

namespace ILIAS\AssessmentQuestion\UserInterface\Web\Component\Editor;

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
		$editors[MultipleChoiceEditor::class] = 'MultipleChoiceEditor';
		$editors[KprimChoiceEditor::class] = 'KprimChoiceEditor';
        $editors[NumericEditor::class] = 'NumericEditor';
        $editors[TextSubsetEditor::class] = 'TextSubsetEditor';
        $editors[ErrorTextEditor::class] = 'ErrorTextEditor';
        $editors[OrderingEditor::class] = 'OrderingEditor';
        $editors[ImageMapEditor::class] = 'ImageMapEditor';
		return $editors;
	}

	public static function getDefaultEditor() {
		return MultipleChoiceEditor::class;
	}
}