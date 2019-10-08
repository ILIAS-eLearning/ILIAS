<?php

namespace ILIAS\AssessmentQuestion\DomainModel\Scoring;

use ILIAS\AssessmentQuestion\DomainModel\QuestionPlayConfiguration;
use ILIAS\AssessmentQuestion\DomainModel\Answer\Option\AnswerDefinition;
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
class KprimChoiceScoringDefinition extends AnswerDefinition {

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
    public static function getFields(QuestionPlayConfiguration $play): array
    {
        global $DIC;
        
        /** @var $conf KprimChoiceEditorConfiguration */
        $conf = $play->getEditorConfiguration();
        
        $fields = [];
        $fields[] = new AnswerOptionFormFieldDefinition(
            $DIC->language()->txt('asq_label_options'),
            AnswerOptionFormFieldDefinition::TYPE_RADIO,
            self::VAR_KPSD_CORRECT,
            [
                $conf->getLabelTrue() ?? $DIC->language()->txt('asq_label_right') => self::STR_TRUE, 
                $conf->getLabelFalse() ?? $DIC->language()->txt('asq_label_wrong') => self::STR_FALSE
            ]);
        
        return $fields;
    }

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