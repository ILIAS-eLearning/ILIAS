<?php

namespace ILIAS\AssessmentQuestion\DomainModel\Scoring;

use ILIAS\AssessmentQuestion\DomainModel\Answer\Option\ScoringDefinition;
use ILIAS\AssessmentQuestion\UserInterface\Web\Form\Config\AnswerOptionFormFieldDefinition;
use stdClass;

/**
 * Class KprimChoiceScoringDefinition
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class KprimChoiceScoringDefinition extends ScoringDefinition {

    const VAR_KPSD_CORRECT = 'kpsd_correct';
    
    const STR_TRUE = "True";
    const STR_FALSE = "False";
    
    /**
     * @var bool
     */
    protected $correct_value;

    public function __construct(bool $correct_value) {
        $this->correct_value = $correct_value;
    }
    
    /**
     * @return boolean
     */
    public function isCorrect_value()
    {
        return $this->correct_value;
    }

    /**
     * @return array
     */
    public static function getFields(): array
    {
        global $DIC;
        
        $fields = [];
        $fields[] = new AnswerOptionFormFieldDefinition(
            $DIC->language()->txt('asq_label_options'),
            AnswerOptionFormFieldDefinition::TYPE_RADIO,
            self::VAR_KPSD_CORRECT,
            [
                $DIC->language()->txt('asq_label_right') => self::STR_TRUE, 
                $DIC->language()->txt('asq_label_wrong') => self::STR_FALSE
            ]);
        
        return $fields;
    }

    /**
     * {@inheritDoc}
     * @see \ILIAS\AssessmentQuestion\DomainModel\Answer\Option\ScoringDefinition::getValues()
     */
    public function getValues(): array
    {
        return [self::VAR_KPSD_CORRECT => $this->correct_value ? self::STR_TRUE : self::STR_FALSE];
    }

    public static function getValueFromPost(string $index)
    {
        return new KprimChoiceScoringDefinition($_POST[$index . self::VAR_KPSD_CORRECT] === self::STR_TRUE);
    }

    public static function deserialize(stdClass $data)
    {
        return new KprimChoiceScoringDefinition($data->correct_value);
    }
}