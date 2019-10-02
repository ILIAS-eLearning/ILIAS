<?php

namespace ILIAS\AssessmentQuestion\DomainModel\Scoring;

use ILIAS\AssessmentQuestion\ilAsqHtmlPurifier;
use ILIAS\AssessmentQuestion\DomainModel\AbstractConfiguration;
use ILIAS\AssessmentQuestion\DomainModel\Answer\Answer;
use ilTextInputGUI;
use ilNumberInputGUI;
use ilRadioGroupInputGUI;
use ilRadioOption;
use EvalMath;
use ILIAS\AssessmentQuestion\DomainModel\QuestionDto;

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
    const VAR_FORMULA = 'fs_formula';
    const VAR_UNITS = 'fs_units';
    const VAR_PRECISION = 'fs_precision';
    const VAR_TOLERANCE = 'fs_tolerance';
    const VAR_RESULT_TYPE = 'fs_type';
    
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
    
    public function score(Answer $answer): int
    {
        $answers = json_decode($answer->getValue(), true);
        $formula = $this->configuration->getFormula();
        
        foreach($answers as $key => $value) {
            $formula = str_replace($key, $value, $formula);
        }
        
        $split = explode('=', $formula);
        
        
        
        $math = new EvalMath();
        
        $calculation = $math->evaluate($split[0]);
        $result = $math->evaluate($split[1]);
        
        if($calculation == $result) {
            return 5;
        }
        else {
            return 0;
        }
    }

    /**
     * @return array|null
     */
    public static function generateFields(?AbstractConfiguration $config): ?array {
        global $DIC;
        
        /** @var $config FormulaScoringConfiguration */
        
        $fields = [];
        
        $formula = new ilTextInputGUI($DIC->language()->txt('asq_label_formula'), self::VAR_FORMULA);
        $formula->setRequired(true);
        $formula->setInfo($DIC->language()->txt('asq_description_formula'));
        $fields[] = $formula;
        
        $units = new ilTextInputGUI($DIC->language()->txt('asq_label_units'), self::VAR_UNITS);
        $units->setInfo($DIC->language()->txt('asq_description_units'));
        $fields[] = $units;
        
        $precision = new ilNumberInputGUI($DIC->language()->txt('asq_label_precision'), self::VAR_PRECISION);
        $precision->setInfo($DIC->language()->txt('asq_description_precision'));
        $precision->setRequired(true);
        $fields[] = $precision;
        
        $tolerance = new ilNumberInputGUI($DIC->language()->txt('asq_label_tolerance'), self::VAR_TOLERANCE);
        $tolerance->setInfo($DIC->language()->txt('asq_description_tolerance'));
        $tolerance->setSuffix('%');
        $fields[] = $tolerance;
        
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
        $fields[] = $result_type;
        
        if ($config !== null) {
            $formula->setValue($config->getFormula());
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
        return FormulaScoringConfiguration::create(ilAsqHtmlPurifier::getInstance()->purify($_POST[self::VAR_FORMULA]), 
                                                   ilAsqHtmlPurifier::getInstance()->purify($_POST[self::VAR_UNITS]),
                                                   intval($_POST[self::VAR_PRECISION]), 
                                                   floatval($_POST[self::VAR_TOLERANCE]), 
                                                   intval($_POST[self::VAR_RESULT_TYPE]));
    }
}