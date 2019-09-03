<?php

namespace ILIAS\AssessmentQuestion\UserInterface\Web\Form\Config;

use Exception;
use ILIAS\AssessmentQuestion\DomainModel\QuestionPlayConfiguration;
use ILIAS\AssessmentQuestion\DomainModel\Answer\Option\AnswerOption;
use ILIAS\AssessmentQuestion\DomainModel\Answer\Option\AnswerOptions;
use ilImageFileInputGUI;
use ilNumberInputGUI;
use ilTemplate;
use ilTextInputGUI;
use ilHiddenInputGUI;
use ILIAS\AssessmentQuestion\UserInterface\Web\Form\QuestionFormGUI;
use ilRadioGroupInputGUI;
use ilRadioOption;

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

	/**
	 * @var array
	 */
	private $definitions;
	/**
	 * @var array
	 */
	private $options;
	/**
	 * @var QuestionPlayConfiguration
	 */
	private $configuration;

	public function __construct(string $title, ?QuestionPlayConfiguration $configuration, array $options) {
		parent::__construct($title);
		
		//TODO every question that needs answer options requires them until now, if not --> dont set by default
		$this->setRequired(true);
		$this->configuration = $configuration;
		$this->definitions = $this->collectFields($configuration);

		//add empty row if there are no answers
		if (sizeof($options) === 0) {
			$this->options[] = null;
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
		$tpl = new ilTemplate("tpl.AnswerOptionTable.html", true, true, "Services/AssessmentQuestion");

		/** @var AnswerOptionFormFieldDefinition $definition */
		foreach ($this->definitions as $definition) {
			$tpl->setCurrentBlock('header_entry');
			$tpl->setVariable('HEADER_TEXT', $definition->getHeader());
			$tpl->parseCurrentBlock();
		}

		$tpl->setCurrentBlock('commands');
		$tpl->setVariable('COMMANDS_TEXT', 'Actions');
		$tpl->parseCurrentBlock();

		$row_id = 1;

		/** @var AnswerOption $option */
		foreach ($this->options as $option) {
			/** @var AnswerOptionFormFieldDefinition $definition */
			foreach ($this->definitions as $definition) {
				$tpl->setCurrentBlock('body_entry');
				$tpl->setVariable('ENTRY_CLASS', ''); //TODO get class by type
				$tpl->setVariable('ENTRY', $this->generateField($definition, $row_id, $option !== null ? $option->rawValues()[$definition->getPostVar()] : null));
				$tpl->parseCurrentBlock();
			}

			$tpl->setCurrentBlock('row');
			$tpl->setVariable('ID', $row_id);
			$tpl->parseCurrentBlock();

			$row_id += 1;
		}

		$tpl->setCurrentBlock('count');
		$tpl->setVariable('COUNT_POST_VAR', self::COUNT_POST_VAR);
		$tpl->setVariable('COUNT', sizeof($this->options));
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
	public function readAnswerOptions() : AnswerOptions {
	    $options = new AnswerOptions();

	    $sd_class = QuestionPlayConfiguration::getScoringClass($this->configuration)::getScoringDefinitionClass();
	    $dd_class = QuestionPlayConfiguration::getEditorClass($this->configuration)::getDisplayDefinitionClass();
	    
	    $count = intval($_POST[Answeroptionform::COUNT_POST_VAR]);
	    
	    for ($i = 1; $i <= $count; $i++) {
	        $options->addOption(new AnswerOption
	            (
	                $i,
	                $dd_class::getValueFromPost($i),
	                $sd_class::getValueFromPost($i)
	                ));
	    }
	    
	    return $options;
	}

	/**
	 * @param QuestionPlayConfiguration $play
	 *
	 * @return array
	 */
	private function collectFields(?QuestionPlayConfiguration $play) : array {
	    $sd_class = QuestionPlayConfiguration::getScoringClass($play)::getScoringDefinitionClass();
	    $dd_class = QuestionPlayConfiguration::getEditorClass($play)::getDisplayDefinitionClass();
	    
	    
	    return array_merge($dd_class::getFields(), $sd_class::getFields());
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
	            break;
	        case AnswerOptionFormFieldDefinition::TYPE_IMAGE:
	            return $this->generateImageField($row_id . $definition->getPostVar(), $value);
	            break;
	        case AnswerOptionFormFieldDefinition::TYPE_NUMBER:
	            return $this->generateNumberField($row_id . $definition->getPostVar(), $value);
	            break;
	        case AnswerOptionFormFieldDefinition::TYPE_RADIO;
	        return $this->generateRadioField($row_id . $definition->getPostVar(), $value, $definition->getOptions());
	        break;
	        default:
	            throw new Exception('Please implement all fieldtypes you define');
	            break;
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

	/**
	 * @param string $post_var
	 * @param        $value
	 *
	 * @return ilImageFileInputGUI
	 */
	private function generateImageField(string $post_var, $value) {
		$field = new ilImageFileInputGUI('', $post_var);
		
		$this->setFieldValue($post_var, $value, $field);
		
		$hidden = '<input type="hidden" name="' . $post_var . QuestionFormGUI::IMG_PATH_SUFFIX . '" value="' . $value . '" />';
		return $field->render() . $hidden;
	}

	/**
	 * @param string $post_var
	 * @param        $value
	 *
	 * @return ilNumberInputGUI
	 */
	private function generateNumberField(string $post_var, $value) {
		$field = new ilNumberInputGUI('', $post_var);
		
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