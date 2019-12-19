<?php

namespace ILIAS\AssessmentQuestion\UserInterface\Web\Component\Editor;

use ILIAS\AssessmentQuestion\ilAsqHtmlPurifier;
use ILIAS\AssessmentQuestion\DomainModel\Question;
use ILIAS\AssessmentQuestion\DomainModel\QuestionDto;
use ILIAS\AssessmentQuestion\DomainModel\Scoring\FormulaScoringConfiguration;
use ILIAS\AssessmentQuestion\DomainModel\Scoring\FormulaScoringDefinition;
use ILIAS\AssessmentQuestion\DomainModel\Scoring\FormulaScoringVariable;

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
        $this->selected_answers = [];
        $this->configuration = $question->getPlayConfiguration()->getScoringConfiguration();
        
        parent::__construct($question);
    }
    
    public function readAnswer(): string
    {
        $answers = [];
        
        $index = 1;
        $continue = true;
        while ($continue) {
            $continue = false;

            $continue |= $this->processVar('$v' . $index, $answers);
            $continue |= $this->processVar('$r' . $index, $answers);            
            $index += 1;
        }
        
        return json_encode($answers);
    }
    
    private function processVar($name, &$answers) : bool {
        $postname = $this->getPostVariable($name);
        
        if (array_key_exists($postname, $_POST)) {
            $answers[$name] = ilAsqHtmlPurifier::getInstance()->purify($_POST[$postname]);
            return true;
        }
        
        return false;
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
        $output = $this->question->getData()->getQuestionText();
        
        $resindex = 1;
        foreach ($this->question->getAnswerOptions()->getOptions() as $option) {
            $output = $this->createResult($resindex, $output);
            $resindex += 1;
        }
        
        $varindex = 1;
        foreach ($this->configuration->getVariables() as $variable) {
                $output = $this->createVariable($varindex, $output, $variable);
                $varindex += 1;
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
    
    private function createVariable(int $index, string $output, FormulaScoringVariable $def) :string {
        $name = '$v' . $index;
        
        $html = sprintf('<input type="hidden" name="%1$s" value="%2$s" />%2$s',
            $this->getPostVariable($name),
            $this->answers[$name] ?? $this->generateVariableValue($def));
        
        return str_replace($name, $html, $output);
    }
    
    private function generateVariableValue(FormulaScoringVariable $def) : string {
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
    
    public static function isComplete(Question $question): bool
    {
        /** @var FormulaEditorConfiguration $config */
        $config = $question->getPlayConfiguration()->getEditorConfiguration();
        
        if (false) {
            return false;
        }
        
        return true;
    }
}