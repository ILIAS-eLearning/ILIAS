<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Services\AssessmentQuestion\DomainModel\Feedback;

use ILIAS\AssessmentQuestion\DomainModel\AbstractConfiguration;

/**
 * Class Feedback
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class CommonPageObjectFeedback extends AbstractCommonFeedback
{

    /**
     * @return array|null
     */
    public static function generateFields(?AbstractConfiguration $config) : ?array
    {
        /** @var CommonPageObjectFeedbackConfiguration $config */
        global $DIC;

        $fields = [];

        $feedback_correct = new \ilNonEditableValueGUI($DIC->language()->txt('asq_input_feedback_correct'), self::VAR_FEEDBACK_CORRECT, true);
        $feedback_correct->setValue($config->getPage()->getPageEditingLink());
        $fields[] = $feedback_correct;

        $feedback_wrong = new \ilNonEditableValueGUI($DIC->language()->txt('asq_input_feedback_correct'), self::VAR_FEEDBACK_WRONG, true);
        $feedback_wrong->setValue($config->getPage()->getPageEditingLink());
        $fields[] = $feedback_wrong;

        return $fields;
    }
}