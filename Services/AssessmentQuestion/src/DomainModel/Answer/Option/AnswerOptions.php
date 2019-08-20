<?php

namespace ILIAS\AssessmentQuestion\DomainModel\Answer\Option;

/**
 * Class AnswerOptions
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class AnswerOptions {

	/**
	 * @var array
	 */
	private $options;

	public function __construct() {
		$this->options = [];
	}

	public function addOption(AnswerOption $option) {
		$this->options[] = $option;
	}


    /**
     * @return AnswerOption[]
     */
	public function getOptions() : array {
		return $this->options;
	}
	
	public static function deserialize(string $json_data) : AnswerOptions {
	    $data = json_decode($json_data);
	    $options = new AnswerOptions();
	    
	    foreach($data as $option) {
	        $aoption = new AnswerOption($option->option_id);
	        $aoption->deserialize($option);
	        $options->addOption($aoption);
	    }
	    
	    return $options;
	}
}
