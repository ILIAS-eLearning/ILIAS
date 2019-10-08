<?php

namespace ILIAS\AssessmentQuestion\DomainModel\Answer\Option;

use JsonSerializable;
use stdClass;

/**
 * Interface AnswerOption
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class AnswerOption implements JsonSerializable {

	const DISPLAY_DEF_CLASS = "ddclass";
	const SCORING_DEF_CLASS = "sdclass";
	const ANSWER_OPTION_FEEDBACK_CLASS = "fdclass";


	/**
	 * @var int
	 */
	private $option_id;
	/**
	 * @var ?AnswerDefinition
	 */
	private $display_definition;
	/**
	 * @var ?AnswerDefinition
	 */
	private $scoring_definition;
    /**
     * @var ?AnswerOptionFeedback
     */
    private $answer_option_feedback;

	public function __construct(int $id, 
	                            ?AnswerDefinition $display_definition = null, 
	                            ?AnswerDefinition $scoring_definition = null, 
	                            ?AnswerOptionFeedback $answer_option_feedback = null)
	{
		$this->option_id = $id;
		$this->display_definition = $display_definition;
		$this->scoring_definition = $scoring_definition;
		$this->answer_option_feedback = $answer_option_feedback;
	}


	/**
	 * @return string
	 */
	public function getOptionId(): string {
		return $this->option_id;
	}


	/**
	 * @return AnswerDefinition
	 */
	public function getDisplayDefinition() {
		return $this->display_definition;
	}


	/**
	 * @return mixed
	 */
	public function getScoringDefinition() {
		return $this->scoring_definition;
	}


    /**
     * @return mixed
     */
    public function getAnswerOptionFeedback()
    {
        return $this->answer_option_feedback;
    }




	/**
	 * @return array
	 */
	public function rawValues() : array {
		$dd_fields = $this->display_definition !== null ? $this->display_definition->getValues() : [];
		$sd_fields = $this->scoring_definition !== null ? $this->scoring_definition->getValues() : [];
        $fd_fields = $this->answer_option_feedback !== null ? $this->answer_option_feedback->getValues() : [];

		return array_merge($dd_fields, $sd_fields, $fd_fields);
	}

	public function equals(AnswerOption $other) : bool {
	    if (get_class($this->display_definition) !== get_class($other->display_definition) ||
	        get_class($this->scoring_definition) !== get_class($other->scoring_definition) ||
            get_class($this->answer_option_feedback) !== get_class($other->answer_option_feedback))
	    {
	       return false;        
	    }

	    $my_values = $this->rawValues();
	    $other_values = $other->rawValues();

	    foreach ($my_values as $key => $value)
	    {
	        if ($my_values[$key] !== $other_values[$key]) 
	        {
	            return false;
	        }
	    }
	    
	    return true;
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
		$vars = get_object_vars($this);
		$vars[self::DISPLAY_DEF_CLASS] = get_class($this->display_definition);
		$vars[self::SCORING_DEF_CLASS] = get_class($this->scoring_definition);
        $vars[self::ANSWER_OPTION_FEEDBACK_CLASS] = get_class($this->answer_option_feedback);
		return $vars;
	}

	public function deserialize(stdClass $option) {

		$dd_class = $option->{self::DISPLAY_DEF_CLASS};
		$this->display_definition = call_user_func(array($dd_class, 'deserialize'), $option->display_definition);

		$sd_class = $option->{self::SCORING_DEF_CLASS};
		$this->scoring_definition = call_user_func(array($sd_class, 'deserialize'), $option->scoring_definition);

        $fd_class = $option->{self::ANSWER_OPTION_FEEDBACK_CLASS};
        $this->answer_option_feedback = call_user_func(array($fd_class, 'deserialize'), $option->answer_option_feedback);
	}
}
