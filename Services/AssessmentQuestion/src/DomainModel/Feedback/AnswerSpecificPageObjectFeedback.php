<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Services\AssessmentQuestion\DomainModel\Feedback;

use ILIAS\AssessmentQuestion\DomainModel\AbstractConfiguration;
use ILIAS\AssessmentQuestion\UserInterface\Web\Component\Editor\ImageAndTextDisplayDefinition;
use ilRadioGroupInputGUI;
use ilRadioOption;

/**
 * Class AnswerSpecificPageObjectFeedback
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class AnswerSpecificPageObjectFeedback extends AbstractAnswerSpecificFeedback
{

    /**
     * @return array|null
     */
    public static function generateFields(?AbstractConfiguration $config) : ?array
    {
        /** @var AnswerSpecificPageObjectFeedbackConfiguration $config */
        global $DIC;

        $fields = [];


        $feedback_setting = new ilRadioGroupInputGUI($DIC->language()->txt('asq_label_feedback_setting'), self::VAR_FEEDBACK_SETTING);
        $feedback_setting->addOption(new ilRadioOption($DIC->language()->txt('asq_option_feedback_all'), self::OPT_FEEDBACK_SETTING_ALL));
        $feedback_setting->addOption(new ilRadioOption($DIC->language()->txt('asq_option_feedback_checked'), self::OPT_FEEDBACK_SETTING_CHECKED));
        $feedback_setting->addOption(new ilRadioOption($DIC->language()->txt('asq_option_feedback_correct'), self::OPT_FEEDBACK_SETTING_CORRECT));
        $feedback_setting->setRequired(true);
        $fields[] =  $feedback_setting;

        foreach($config->getAnswerOptions()->getOptions() as $answer_option) {
            $answer_specific_feedback = new \ilNonEditableValueGUI($answer_option->getDisplayDefinition()->getValues()[ImageAndTextDisplayDefinition::VAR_MCDD_TEXT],self::VAR_FEEDBACK_FOR_ANSWER.'['.$answer_option->getOptionId().']', true);
            $answer_specific_feedback->setValue($config->getPage()->getPageEditingLink());
            $fields[] = $answer_specific_feedback;

        }

        return $fields;
    }
}