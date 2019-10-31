<?php

namespace ILIAS\AssessmentQuestion\UserInterface\Web\Form\Config;

use ILIAS\AssessmentQuestion\DomainModel\QuestionPlayConfiguration;
use ILIAS\AssessmentQuestion\DomainModel\Answer\Option\AnswerOption;
use ILIAS\AssessmentQuestion\DomainModel\Answer\Option\AnswerOptions;
use ILIAS\AssessmentQuestion\UserInterface\Web\Fields\AsqTableInput;

/**
 * Class AnswerOptionForm
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class AnswerOptionForm extends AsqTableInput {
    const VAR_POST = 'answer_options';
    
    /**
     * @var AnswerOptions
     */
    private $options;
    /**
     * @var QuestionPlayConfiguration
     */
    private $configuration;
    
	public function __construct(string $title, 
	                            ?QuestionPlayConfiguration $configuration, 
	                            AnswerOptions $options, 
	                            ?array $definitions = null,
	                            ?array $form_configuration = null) 
	{
	    $this->setRequired(true);
	    $this->configuration = $configuration;
	    
	    if(is_null($definitions) && !is_null($configuration)) {
	        $definitions = $this->collectFields($configuration);
	    }
	    
	    if (is_null($form_configuration) && !is_null($configuration)) {
	        $form_configuration = $this->collectConfigurations($configuration);
	    }    
	    
		parent::__construct($title, 
		                    self::VAR_POST,
                		    array_map(function($option) {
                		        return $option->rawValues();
                		    }, $options->getOptions()),
		                    $definitions,
		                    $form_configuration);
		
		$this->options = $options;
	}
	
	/**
	 * @param QuestionPlayConfiguration $configuration
	 */
	public function setConfiguration(QuestionPlayConfiguration $configuration) {
	    $this->configuration = $configuration;
	}
	
	/**
	 * @return bool
	 */
	public function checkInput() : bool {    
	    $count = intval($_POST[Answeroptionform::COUNT_POST_VAR]);
	    
	    $sd_class = QuestionPlayConfiguration::getScoringClass($this->configuration)::getScoringDefinitionClass();
	    $dd_class = QuestionPlayConfiguration::getEditorClass($this->configuration)::getDisplayDefinitionClass();
	    
        if(!$dd_class::checkInput($count)) {
            $this->setAlert($dd_class::getErrorMessage());
            return false;
        }
        
        if(!$sd_class::checkInput($count)) {
            $this->setAlert($sd_class::getErrorMessage());
            return false;
        }
	    
	    return true;
	}

	/**
	 * @param QuestionPlayConfiguration $play
	 *
	 * @return AnswerOptions
	 */
	public function readAnswerOptions() {
	    $this->readValues();
	    
	    $sd_class = QuestionPlayConfiguration::getScoringClass($this->configuration)::getScoringDefinitionClass();
	    $dd_class = QuestionPlayConfiguration::getEditorClass($this->configuration)::getDisplayDefinitionClass();
	    
	    $count = intval($_POST[Answeroptionform::VAR_POST]);

	    $this->options = new AnswerOptions();
	    for ($i = 1; $i <= $count; $i++) {
	        $this->options->addOption(new AnswerOption
	            (
	                $i,
	                $dd_class::getValueFromPost($i),
	                $sd_class::getValueFromPost($i)));
	    }
	}

	public function getAnswerOptions() : AnswerOptions {
	    return $this->options;
	}
	
	/**
	 * @param QuestionPlayConfiguration $play
	 *
	 * @return array
	 */
	private function collectFields(?QuestionPlayConfiguration $play) : array {
	    $sd_class = QuestionPlayConfiguration::getScoringClass($play)::getScoringDefinitionClass();
	    $dd_class = QuestionPlayConfiguration::getEditorClass($play)::getDisplayDefinitionClass();
	    
	    
	    return array_merge($dd_class::getFields($play), $sd_class::getFields($play));
	}

	/**
	 * @param QuestionPlayConfiguration $play
	 *
	 * @return array
	 */
	private function collectConfigurations(QuestionPlayConfiguration $play) : array {
	    return array_merge($play->getEditorConfiguration()->getOptionFormConfig(), 
	                       $play->getScoringConfiguration()->getOptionFormConfig());
	}
}