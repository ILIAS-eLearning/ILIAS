<?php

namespace ILIAS\AssessmentQuestion\UserInterface\Web\Component\Editor;

use ILIAS\AssessmentQuestion\ilAsqHtmlPurifier;
use ILIAS\AssessmentQuestion\DomainModel\QuestionDto;
use ILIAS\AssessmentQuestion\DomainModel\Scoring\FormulaScoringConfiguration;
use ILIAS\AssessmentQuestion\DomainModel\Scoring\FormulaScoringDefinition;

/**
 * Class FileUploadEditor
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class FormulaEditor extends AbstractEditor {
    /**
     * @var FormulaScoringConfiguration
     */
    private $configuration;
    /**
     * @var array
     */
    private $answers;
    
    public function __construct(QuestionDto $question) {
        parent::__construct($question);
        
        $this->selected_answers = [];
        $this->configuration = $question->getPlayConfiguration()->getScoringConfiguration();
    }
    
    public function readAnswer(): string
    {
        $answers = [];
        
        $resindex = 1;
        $varindex = 1;
        foreach ($this->question->getAnswerOptions()->getOptions() as $option) {
            /** @var $def FormulaScoringDefinition */
            $def = $option->getScoringDefinition();
            
            if ($def->getType() === FormulaScoringDefinition::TYPE_VARIABLE) {
                $name = '$v' . $varindex;
                $varindex += 1;
            }
            else if ($def->getType() === FormulaScoringDefinition::TYPE_RESULT) {
                $name = '$r' . $resindex;
                $resindex += 1;
            }
            
            $answers[$name] = ilAsqHtmlPurifier::getInstance()->purify($_POST[$this->getPostVariable($name)]);
        }
        
        return json_encode($answers);
    }

    public static function readConfig()
    {
        return new FormulaEditorConfiguration();
    }

    public function setAnswer(string $answer): void
    {
        $this->answers = json_decode($answer, true);
    }

    public function generateHtml(): string
    {
        $output = $this->configuration->getFormula();
        
        $resindex = 1;
        $varindex = 1;
        foreach ($this->question->getAnswerOptions()->getOptions() as $option) {
            /** @var $def FormulaScoringDefinition */
            $def = $option->getScoringDefinition();
            
            if ($def->getType() === FormulaScoringDefinition::TYPE_RESULT) {
                $output = $this->createResult($resindex, $output);
                $resindex += 1;
            }
            else if ($def->getType() === FormulaScoringDefinition::TYPE_VARIABLE) {
                $output = $this->createVariable($varindex, $output, $def);
                $varindex += 1;
            }
        }
        
        return $output;
    }
    
    private function createResult(int $index, string $output) :string {
        $name = '$r' . $index;
        
        $html = sprintf('<input type="text" length="20" name="%s" value="%s" />',
            $this->getPostVariable($name),
            $this->answers[$name] ?? '');
        
        return str_replace($name, $html, $output);
    }
    
    private function createVariable(int $index, string $output, FormulaScoringDefinition $def) :string {
        $name = '$v' . $index;
        
        $html = sprintf('<input type="hidden" name="%1$s" value="%2$s" />%2$s',
            $this->getPostVariable($name),
            $this->answers[$name] ?? $this->generateVariableValue($def));
        
        return str_replace($name, $html, $output);
    }
    
    private function generateVariableValue(FormulaScoringDefinition $def) : string {
        $exp = 10 ** $this->configuration->getPrecision();
        
        $min = $def->getMin() * $exp;
        $max = $def->getMax() * $exp;
        $number = mt_rand($min, $max);
        
        if (!is_null($def->getMultipleOf())) {
            $mult_of = $def->getMultipleOf() * $exp;
            
            $number -= $number % $mult_of;
            
            if ($number < $min) {
                $number += $mult_of;
            }
        }
        
        $number /= $exp;
        
        return sprintf('%.' . $this->configuration->getPrecision() . 'F', $number);
    }
    
    private function getPostVariable(string $name) {
        return $name . $this->question->getId();
    }
    
    /**
     * @return string
     */
    static function getDisplayDefinitionClass() : string {
        return EmptyDisplayDefinition::class;
    }
}