<?php

namespace ILIAS\AssessmentQuestion\UserInterface\Web\Component\Feecback\Form;

use ilFormPropertyGUI;
use ILIAS\AssessmentQuestion\DomainModel\Answer\Answer;
use ILIAS\AssessmentQuestion\DomainModel\Answer\Option\AnswerOption;
use ILIAS\AssessmentQuestion\DomainModel\Answer\Option\AnswerOptionFeedback;
use ILIAS\AssessmentQuestion\DomainModel\Answer\Option\AnswerOptions;
use ILIAS\AssessmentQuestion\UserInterface\Web\Component\Editor\ImageAndTextDisplayDefinition;
use ilObjAdvancedEditing;

/**
 * Class FeedbackFieldAnswerOptionsContentRte
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class FeedbackFieldAnswerOptionsContentRte
{

    /**
     * FeedbackFieldContentRte constructor.
     *
     * @param AnswerOptions $content
     * @param int    $container_obj_id
     * @param string $container_obj_type
     * @param string $post_var
     */
    public function __construct(AnswerOptions $answer_options, int $container_obj_id, string $container_obj_type, string $post_var) {
        $this->answer_options = $answer_options;
        $this->container_obj_id = $container_obj_id;
        $this->container_obj_type = $container_obj_type;
        $this->post_var = $post_var;
    }


    /**
     * @return ilFormPropertyGUI[]
     */
    public function getFields(): array  {
        global $DIC;

        $fields = [];

        foreach($this->answer_options->getOptions() as $answer_option) {
            /** @var AnswerOption $answer_option */
            $arr_option = $answer_option->getDisplayDefinition()->getValues();
            $label = $arr_option[ImageAndTextDisplayDefinition::VAR_MCDD_TEXT];


            $field = new FeedbackFieldContentRte($answer_option->getAnswerOptionFeedback()->getAnswerFeedback(),$this->container_obj_id,  $this->container_obj_type, $label,  $this->post_var."[".$answer_option->getOptionId()."]");

            $fields[] = $field;
        }

        return $fields;
    }


    /**
     * @param AnswerOptions $answer_options
     * @param string        $post_var
     *
     * @return AnswerOptions$
     */
    public static function getValueFromPostAnswerOptions(AnswerOptions $answer_options, string $post_var):AnswerOptions {

        $new_answer_options = new AnswerOptions();

        foreach($answer_options->getOptions() as $answer_option) {
            $data = filter_input(INPUT_POST, $post_var, FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
            /** @var AnswerOption $answer_option */
            $new_answer_options->addOption(AnswerOption::createWithNewAnswerOptionFeedback($answer_option,new AnswerOptionFeedback($data[$answer_option->getOptionId()])));
        }

        return $new_answer_options;
    }
}