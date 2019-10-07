<?php

namespace ILIAS\AssessmentQuestion\UserInterface\Web\Form\Config;

use ILIAS\AssessmentQuestion\DomainModel\Answer\Option\AnswerOptionFeedback;
use ILIAS\AssessmentQuestion\DomainModel\QuestionPlayConfiguration;
use ILIAS\AssessmentQuestion\DomainModel\Answer\Option\AnswerOption;
use ILIAS\AssessmentQuestion\DomainModel\Answer\Option\AnswerOptions;
use Exception;
use ilImageFileInputGUI;
use ilNumberInputGUI;
use ilRadioGroupInputGUI;
use ilRadioOption;
use ilTemplate;
use ilTextInputGUI;
use ILIAS\AssessmentQuestion\UserInterface\Web\Fields\AsqImageUpload;
use ilButton;

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
class AnswerOptionForm extends ilTextInputGUI {

	const COUNT_POST_VAR = 'option_count';

	const OPTION_ORDER = 'AnswerOptionOrder';

    /**
     * @var int
     */
    private $question_ind_id;
	/**
	 * @var array
	 */
	private $definitions;
	/**
	 * @var AnswerOptions
	 */
	private $options;
	/**
	 * @var QuestionPlayConfiguration
	 */
	private $configuration;
	
	/**
	 * @var array
	 */
	private $form_configuration;

	public function __construct(string $title, int $question_int_id, ?QuestionPlayConfiguration $configuration, AnswerOptions $options, array $definitions = null) {
		parent::__construct($title);

		$this->question_ind_id = $question_int_id;
		//TODO every question that needs answer options requires them until now, if not --> dont set by default
		$this->setRequired(true);
		$this->configuration = $configuration;
		
		if(is_null($definitions)) {
		    $this->definitions = $this->collectFields($configuration);
		}
		else {
		    $this->definitions = $definitions;
		}
		
		if (!is_null($configuration)) {
		    $this->form_configuration = $this->collectConfigurations($configuration);
		}
		
		$this->options = new AnswerOptions($question_int_id);
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
		    $this->readAnswerOptions();
		} else {
			$this->options = $options;
		}
	}

	/**
	 * @param QuestionPlayConfiguration $configuration
	 */
	public function setConfiguration(QuestionPlayConfiguration $configuration) {
	    $this->configuration = $configuration;
	}
	
	/**
	 * @param string $a_mode
	 *
	 * @return string
	 * @throws \ilTemplateException
	 */
	public function render($a_mode = '') {
	    global $DIC;
	    
		$tpl = new ilTemplate("tpl.AnswerOptionTable.html", true, true, "Services/AssessmentQuestion");

		/** @var AnswerOptionFormFieldDefinition $definition */
		foreach ($this->definitions as $definition) {
			$tpl->setCurrentBlock('header_entry');
			$tpl->setVariable('HEADER_TEXT', $definition->getHeader());
			$tpl->parseCurrentBlock();
		}

		$tpl->setCurrentBlock('commands');
		$tpl->setVariable('COMMANDS_TEXT', $DIC->language()->txt('asq_label_actions'));
		$tpl->parseCurrentBlock();

		$row_id = 1;

		//add dummy object if no options are defined so that one empty line will be printed
		if (count($this->options->getOptions()) === 0) {
		    $this->options->addOption(null);
		}
		
		/** @var AnswerOption $option */
		foreach ($this->options->getOptions() as $option) {
			/** @var AnswerOptionFormFieldDefinition $definition */
			foreach ($this->definitions as $definition) {
				$tpl->setCurrentBlock('body_entry');
				$tpl->setVariable('ENTRY_CLASS', ''); //TODO get class by type
				$tpl->setVariable('ENTRY', $this->generateField($definition, $row_id, $option !== null ? $option->rawValues()[$definition->getPostVar()] : null));
				$tpl->parseCurrentBlock();
			}

			if (in_array(self::OPTION_ORDER, $this->form_configuration)) {
    			$tpl->setCurrentBlock('move');
    			$tpl->setVariable('X', "");
    			$tpl->parseCurrentBlock();
			}

			
			$tpl->setCurrentBlock('row');
			$tpl->setVariable('ID', $row_id);
			$tpl->parseCurrentBlock();

			$row_id += 1;
		}

		$tpl->setCurrentBlock('count');
		$tpl->setVariable('COUNT_POST_VAR', self::COUNT_POST_VAR);
		$tpl->setVariable('COUNT', sizeof($this->options->getOptions()));
		$tpl->parseCurrentBlock();


		return $tpl->get();
	}

	/**
	 * @return bool
	 */
	public function checkInput() : bool {    
	    $count = intval($_POST[Answeroptionform::COUNT_POST_VAR]);
	    
	    $sd_class = QuestionPlayConfiguration::getScoringClass($this->configuration)::getScoringDefinitionClass();
	    $dd_class = QuestionPlayConfiguration::getEditorClass($this->configuration)::getDisplayDefinitionClass();
	    
	    for ($i = 1; $i <= $count; $i++) {
	        if(!$dd_class::checkInput($i)) {
	            $this->setAlert($dd_class::getErrorMessage());
	            return false;
	        }
	        
	        if(!$sd_class::checkInput($i)) {
	            $this->setAlert($sd_class::getErrorMessage());
	            return false;
	        }
	    }
	    
	    return true;
	}

	/**
	 * @param QuestionPlayConfiguration $play
	 *
	 * @return AnswerOptions
	 */
	public function readAnswerOptions() {
	    $sd_class = QuestionPlayConfiguration::getScoringClass($this->configuration)::getScoringDefinitionClass();
	    $dd_class = QuestionPlayConfiguration::getEditorClass($this->configuration)::getDisplayDefinitionClass();
        $fd_class = AnswerOptionFeedback::class;
	    
	    $count = intval($_POST[Answeroptionform::COUNT_POST_VAR]);
	    
	    $this->options = new AnswerOptions();
	    for ($i = 1; $i <= $count; $i++) {
	        $this->options->addOption(new AnswerOption
	            (
	                $i,
	                $dd_class::getValueFromPost($i),
	                $sd_class::getValueFromPost($i),
                    $fd_class::getValueFromPost($i)
	                ));
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
	
	/**
	 * @param AnswerOptionFormFieldDefinition $definition
	 * @param int                             $row_id
	 * @param                                 $value
	 *
	 * @return string
	 * @throws Exception
	 */
	private function generateField(AnswerOptionFormFieldDefinition $definition, int $row_id, $value)
	{
	    switch ($definition->getType()) {
	        case AnswerOptionFormFieldDefinition::TYPE_TEXT:
	            return $this->generateTextField($row_id . $definition->getPostVar(), $value);
	        case AnswerOptionFormFieldDefinition::TYPE_TEXT_AREA:
	            return $this->generateTextArea($row_id . $definition->getPostVar(), $value);
	        case AnswerOptionFormFieldDefinition::TYPE_IMAGE:
	            return $this->generateImageField($row_id . $definition->getPostVar(), $value);
	        case AnswerOptionFormFieldDefinition::TYPE_NUMBER:
	            return $this->generateNumberField($row_id . $definition->getPostVar(), $value);
	        case AnswerOptionFormFieldDefinition::TYPE_RADIO:
    	        return $this->generateRadioField($row_id . $definition->getPostVar(), $value, $definition->getOptions());
	        case AnswerOptionFormFieldDefinition::TYPE_DROPDOWN:
	            return $this->generateDropDownField($row_id . $definition->getPostVar(), $value, $definition->getOptions());
	        case AnswerOptionFormFieldDefinition::TYPE_BUTTON:
	            return $this->generateButton($row_id . $definition->getPostVar(), $definition->getOptions());
	        default:
	            throw new Exception('Please implement all fieldtypes you define');
	    }
	}
	
	/**
	 * @param string $post_var
	 * @param        $value
	 *
	 * @return ilTextInputGUI
	 */
	private function generateTextField(string $post_var, $value) {
		$field = new ilTextInputGUI('', $post_var);
		
		$this->setFieldValue($post_var, $value, $field);
		
		return $field->render();
	}
	
	private function generateTextArea(string $post_var, $value) {
	    $tpl = new ilTemplate("tpl.TextAreaField.html", true, true, "Services/AssessmentQuestion");
	    
	    $tpl->setCurrentBlock('textarea');
	    $tpl->setVariable('POST_NAME', $post_var);
	    $tpl->setVariable('VALUE', $value);
	    $tpl->parseCurrentBlock();
	    
	    return $tpl->get();
	}

	/**
	 * @param string $post_var
	 * @param        $value
	 *
	 * @return ilImageFileInputGUI
	 */
	private function generateImageField(string $post_var, $value) {
		$field = new AsqImageUpload('', $post_var);

		$field->setImagePath($value);
		
		return $field->render();
	}

	/**
	 * @param string $post_var
	 * @param        $value
	 *
	 * @return ilNumberInputGUI
	 */
	private function generateNumberField(string $post_var, $value) {
		$field = new ilNumberInputGUI('', $post_var);
		$field->setSize(2);
		
		$this->setFieldValue($post_var, $value, $field);
		
		return $field->render();
	}
	
	private function generateRadioField(string $post_var, $value, $options) {
	    $field = new ilRadioGroupInputGUI('', $post_var);
	    
	    $this->setFieldValue($post_var, $value, $field);
	    
	    foreach ($options as $key=>$value)
	    {
    	    $option = new ilRadioOption($key, $value);
    	    $field->addOption($option);	        
	    }
	    return $field->render();
	}
	
	private function generateDropDownField(string $post_var, $value, $options) {
	    $field = new \ilSelectInputGUI('', $post_var);
	    
	    $field->setOptions($options);
	    
	    $this->setFieldValue($post_var, $value, $field);
	    
	    return $field->render();
	}
	
	private function generateButton(string $id, $options) {
	    $css = 'btn btn-default';
	    if (array_key_exists('css', $options)) {
	        $css .= ' ' . $options['css'];
	    }
	    
	    $title = '';
	    if (array_key_exists('title', $options)) {
	        $title .= ' ' . $options['title'];
	    }
	    
	    return sprintf('<input type="Button" id="%s" class="%s" value="%s" />', $id, $css, $title);
	}
	
	/**
	 * @param string $post_var
	 * @param $value
	 * @param $field
	 */
	private function setFieldValue(string $post_var, $value, $field)
	 {
	     if (array_key_exists($post_var, $_POST)) {
	         $field->setValue($_POST[$post_var]);
	     }
	     else if ($value !== null) {
	         $field->setValue($value);
	     }
	}
}