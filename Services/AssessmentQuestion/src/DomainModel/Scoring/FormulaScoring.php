<?php

namespace ILIAS\AssessmentQuestion\DomainModel\Scoring;

use ILIAS\AssessmentQuestion\ilAsqHtmlPurifier;
use ILIAS\AssessmentQuestion\DomainModel\AbstractConfiguration;
use ILIAS\AssessmentQuestion\DomainModel\AnswerScoreDto;
use ILIAS\AssessmentQuestion\DomainModel\Question;
use ILIAS\AssessmentQuestion\DomainModel\QuestionDto;
use ILIAS\AssessmentQuestion\DomainModel\Answer\Answer;
use ILIAS\AssessmentQuestion\DomainModel\Answer\Option\AnswerOptions;
use EvalMath;
use ilNumberInputGUI;
use ilRadioGroupInputGUI;
use ilRadioOption;
use ilTextInputGUI;
use ILIAS\AssessmentQuestion\UserInterface\Web\Fields\AsqTableInput;
use ILIAS\AssessmentQuestion\UserInterface\Web\Fields\AsqTableInputFieldDefinition;

/**
 * Class FormulaScoring
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class FormulaScoring extends AbstractScoring {
    const VAR_UNITS = 'fs_units';
    const VAR_PRECISION = 'fs_precision';
    const VAR_TOLERANCE = 'fs_tolerance';
    const VAR_RESULT_TYPE = 'fs_type';
    const VAR_VARIABLES = 'fs_variables';
    
    /**
     * @var AsqTableInput
     */
    private static $variables_table;
    
    /**
     * @var FormulaScoringConfiguration
     */
    protected $configuration;
    
    /**
     * @param QuestionDto $question
     */
    public function __construct($question) {
        parent::__construct($question);
        
        $this->configuration = $question->getPlayConfiguration()->getScoringConfiguration();
    }
    
    public function score(Answer $answer): AnswerScoreDto
    {
        $reached_points = 0;
        $max_points = 0;

        foreach ($this->question->getAnswerOptions()->getOptions() as $option) {
            /** @var $result FormulaScoringDefinition */
            $result = $option->getScoringDefinition();
            
            $answers = json_decode($answer->getValue(), true);
            $formula = $result->getFormula();
            
            foreach($answers as $key => $value) {
                $formula = str_replace($key, $value, $formula);
            }
            
            $math = new EvalMath();
            
            $result_expected = $math->evaluate($formula);
            
            $result_given = null;
            $raw_result = $answers['$r' . $option->getOptionId()];
            
            //get decimal value of answer if allowed
            if (($this->configuration->getResultType() === FormulaScoringConfiguration::TYPE_ALL ||
                $this->configuration->getResultType() === FormulaScoringConfiguration::TYPE_DECIMAL) &&
                is_numeric($raw_result)) 
            {
                $result_given = floatval($raw_result);
            }
            
            //get compound result if no value yet and it is allowed
            if (is_null($result_given) &&
                $this->configuration->getResultType() !== FormulaScoringConfiguration::TYPE_DECIMAL &&
                strpos($raw_result, '/')) 
            {
                $split = explode('/', $raw_result);
                $numerator = floatval($split[0]);
                $denominator = floatval($split[1]);
                
                $result_given = $numerator / $denominator;
                
                // invalidate result if not coprime and option is set
                if ($this->configuration->getResultType() === FormulaScoringConfiguration::TYPE_COPRIME_FRACTION &&
                    $this->greatest_common_divisor($numerator, $denominator) !== 1) 
                {
                    $result_given = null;
                }
            }
            
            if (!is_null($result_given)) {
                $difference = abs($result_expected - $result_given);
                $max_allowed_difference = $result_expected / 100 * $this->configuration->getTolerance();
                
                if ($difference <= $max_allowed_difference) {
                    $reached_points += $result->getPoints();
                }
            }
            
            $max_points += $result->getPoints();
        }

        return $this->createScoreDto($answer, $max_points, $reached_points, $this->getAnswerFeedbackType($reached_points,$max_points));
    }

    /**
     * Euclids gcd algorithm
     * 
     * @param int $a
     * @param int $b
     * @return int
     */
    private function greatest_common_divisor(int $a, int $b) {
        return ($a % $b) ? $this->greatest_common_divisor($b,$a % $b) : $b;
    }
    
    public function getBestAnswer(): Answer
    {
        
    }
    
    /**
     * @return array|null
     */
    public static function generateFields(?AbstractConfiguration $config, AnswerOptions $options = null): ?array {
        global $DIC;
        
        /** @var $config FormulaScoringConfiguration */
        
        $fields = [];
        
        $units = new ilTextInputGUI($DIC->language()->txt('asq_label_units'), self::VAR_UNITS);
        $units->setInfo($DIC->language()->txt('asq_description_units'));
        $fields[self::VAR_UNITS] = $units;
        
        $precision = new ilNumberInputGUI($DIC->language()->txt('asq_label_precision'), self::VAR_PRECISION);
        $precision->setInfo($DIC->language()->txt('asq_description_precision'));
        $precision->setRequired(true);
        $fields[self::VAR_PRECISION] = $precision;
        
        $tolerance = new ilNumberInputGUI($DIC->language()->txt('asq_label_tolerance'), self::VAR_TOLERANCE);
        $tolerance->setInfo($DIC->language()->txt('asq_description_tolerance'));
        $tolerance->setSuffix('%');
        $fields[self::VAR_TOLERANCE] = $tolerance;
        
        $result_type = new ilRadioGroupInputGUI($DIC->language()->txt('asq_label_result_type'), self::VAR_RESULT_TYPE);
        $result_type->setRequired(true);
        $result_type->addOption(new ilRadioOption($DIC->language()->txt('asq_label_result_all'),
                                                  FormulaScoringConfiguration::TYPE_ALL,
                                                  $DIC->language()->txt('asq_description_result_all')));
        $result_type->addOption(new ilRadioOption($DIC->language()->txt('asq_label_result_decimal'),
                                                  FormulaScoringConfiguration::TYPE_DECIMAL,
                                                  $DIC->language()->txt('asq_description_result_decimal')));
        $result_type->addOption(new ilRadioOption($DIC->language()->txt('asq_label_result_fraction'),
                                                  FormulaScoringConfiguration::TYPE_FRACTION,
                                                  $DIC->language()->txt('asq_description_result_fraction')));
        $result_type->addOption(new ilRadioOption($DIC->language()->txt('asq_label_result_coprime_fraction'),
                                                  FormulaScoringConfiguration::TYPE_COPRIME_FRACTION,
                                                  $DIC->language()->txt('asq_description_result_coprime_fraction')));
        $fields[self::VAR_RESULT_TYPE] = $result_type;
        
        self::$variables_table = new AsqTableInput($DIC->language()->txt('asq_label_variables'), self::VAR_VARIABLES, $config->getVariablesArray(), [
            new AsqTableInputFieldDefinition(
                $DIC->language()->txt('asq_header_min'),
                AsqTableInputFieldDefinition::TYPE_TEXT,
                FormulaScoringVariable::VAR_MIN),            
            new AsqTableInputFieldDefinition(
                $DIC->language()->txt('asq_header_max'),
                AsqTableInputFieldDefinition::TYPE_TEXT,
                FormulaScoringVariable::VAR_MAX),            
            new AsqTableInputFieldDefinition(
                $DIC->language()->txt('asq_header_unit'),
                AsqTableInputFieldDefinition::TYPE_TEXT,
                FormulaScoringVariable::VAR_UNIT),            
            new AsqTableInputFieldDefinition(
                $DIC->language()->txt('asq_header_multiple_of'),
                AsqTableInputFieldDefinition::TYPE_TEXT,
                FormulaScoringVariable::VAR_MULTIPLE_OF)
        ]);
        $fields[self::VAR_VARIABLES] = self::$variables_table;
        
        if ($config !== null) {

            $units->setValue($config->getUnits());
            $precision->setValue($config->getPrecision());
            $tolerance->setValue($config->getTolerance());
            $result_type->setValue($config->getResultType());
        }
        else {
            $tolerance->setValue(0);
            $result_type->setValue(FormulaScoringConfiguration::TYPE_ALL);
        }
        
        return $fields;
    }
    
    public static function readConfig()
    {
        $variables = [];
        $raw_variables = self::$variables_table->readValues();
        
        foreach ($raw_variables as $raw_variable) {
            $variables[] = FormulaScoringVariable::create(
                floatval($raw_variable[FormulaScoringVariable::VAR_MIN]),
                floatval($raw_variable[FormulaScoringVariable::VAR_MAX]),
                ilAsqHtmlPurifier::getInstance()->purify($raw_variable[FormulaScoringVariable::VAR_UNIT]),
                floatval($raw_variable[FormulaScoringVariable::VAR_MULTIPLE_OF]));
        }
        
        return FormulaScoringConfiguration::create(ilAsqHtmlPurifier::getInstance()->purify($_POST[self::VAR_UNITS]),
                                                   intval($_POST[self::VAR_PRECISION]), 
                                                   floatval($_POST[self::VAR_TOLERANCE]), 
                                                   intval($_POST[self::VAR_RESULT_TYPE]),
                                                   $variables);
    }
    
    public static function isComplete(Question $question): bool
    {
        //TODO
        /** @var FormulaScoringConfiguration $config */
        $config = $question->getPlayConfiguration()->getScoringConfiguration();
        
        if (false) {
            return false;
        }
        
        foreach ($question->getAnswerOptions()->getOptions() as $option) {
            /** @var FormulaScoringDefinition $option_config */
            $option_config = $option->getScoringDefinition();
            
            if (false)
            {
                return false;
            }
        }
        
        return true;
    }
}