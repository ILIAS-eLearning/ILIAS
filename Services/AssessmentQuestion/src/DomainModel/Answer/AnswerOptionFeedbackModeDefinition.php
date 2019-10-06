<?php

namespace ILIAS\AssessmentQuestion\DomainModel\Answer\Option;

use ilRadioGroupInputGUI;
use ilRadioOption;
use JsonSerializable;
use stdClass;

/**
 * Abstract Class FeedbackDefinition
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class AnswerOptionFeedbackModeDefinition implements JsonSerializable {

    const OPT_ANSWER_OPTION_FEEDBACK_MODE_NONE = 0;
    const OPT_ANSWER_OPTION_FEEDBACK_MODE_ALL = 1;
    const OPT_ANSWER_OPTION_FEEDBACK_MODE_CHECKED = 2;
    const OPT_ANSWER_OPTION_FEEDBACK_MODE_CORRECT = 3;

    const VAR_ANSWER_OPTION_FEEDBACK_MODE_SETTING = 'answer_option_feedback_mode_setting';
    /**
     * var int
     */
    protected $answer_option_feedback_mode_setting;

    public function __construct($answer_option_feedback_mode_setting) {
        $this->answer_option_feedback_mode_setting = $answer_option_feedback_mode_setting;
    }


	public static function getFields() : array {
	    global $DIC;

        $fields = [];

        $feedback_setting = new ilRadioGroupInputGUI($DIC->language()->txt('asq_label_feedback_setting'), self::VAR_ANSWER_OPTION_FEEDBACK_MODE_SETTING);
        $feedback_setting->addOption(new ilRadioOption($DIC->language()->txt('asq_option_feedback_all'), self::OPT_ANSWER_OPTION_FEEDBACK_MODE_ALL));
        $feedback_setting->addOption(new ilRadioOption($DIC->language()->txt('asq_option_feedback_checked'), self::OPT_ANSWER_OPTION_FEEDBACK_MODE_CHECKED));
        $feedback_setting->addOption(new ilRadioOption($DIC->language()->txt('asq_option_feedback_correct'), self::OPT_ANSWER_OPTION_FEEDBACK_MODE_CORRECT));
        $feedback_setting->setRequired(true);
        $fields[] =  $feedback_setting;

        return $fields;
    }

    public static function getValueFromPost() {
        return new AnswerOptionFeedbackModeDefinition(
            intval($_POST[self::VAR_ANSWER_OPTION_FEEDBACK_MODE_SETTING]));
    }

    public function getValues(): array {
        return [self::VAR_ANSWER_OPTION_FEEDBACK_MODE_SETTING => $this->answer_option_feedback_mode_setting];
    }


    public static function deserialize(stdClass $data) {
        return new AnswerOptionFeedbackModeDefinition($data->answer_option_feedback_mode_setting);
    }
	
	/**
	 * Specify data which should be serialized to JSON
	 *
	 * @link  https://php.net/manual/en/jsonserializable.jsonserialize.php
	 * @return mixed data which can be serialized by <b>json_encode</b>,
	 * which is a value of any type other than a resource.
	 * @since 5.4.0
	 */
	public function jsonSerialize() {
	    return get_object_vars($this);
	}
	
	/**
	 * @return bool
	 */
	public static function checkInput(string $index) : bool {
	    return true;
	}
	
	/**
	 * @return string
	 */
	public static function getErrorMessage() : string {
	    return '';
	}
}