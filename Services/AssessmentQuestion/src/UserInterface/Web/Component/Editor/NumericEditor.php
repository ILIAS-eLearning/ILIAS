<?php

namespace ILIAS\AssessmentQuestion\UserInterface\Web\Component\Editor;

use ILIAS\AssessmentQuestion\DomainModel\AbstractConfiguration;
use ILIAS\AssessmentQuestion\DomainModel\Answer\Answer;
use ILIAS\AssessmentQuestion\DomainModel\Answer\Option\AnswerOption;
use ILIAS\AssessmentQuestion\DomainModel\QuestionDto;
use ilNumberInputGUI;
use ilTemplate;

/**
 * Class NumericEditor
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class NumericEditor extends AbstractEditor {

    const VAR_MAX_NR_OF_CHARS = 'ne_max_nr_of_chars';

    /**
     * @var NumericEditorConfiguration
     */
    private $configuration;
    /**
     * @var ?float
     */
    private $answer;

    public function __construct(QuestionDto $question) {
        parent::__construct($question);

        $this->configuration = $question->getPlayConfiguration()->getEditorConfiguration();
    }

    /**
     * @return string
     */
    public function generateHtml() : string
    {
        $tpl = new ilTemplate("tpl.NumericEditor.html", true, true, "Services/AssessmentQuestion");

        $tpl->setCurrentBlock('editor');
        $tpl->setVariable('POST_NAME', $this->question->getId());
        $tpl->setVariable('NUMERIC_SIZE', $this->configuration->getMaxNumOfChars());

        if ($this->answer !== null) {
            $tpl->setVariable('CURRENT_VALUE', 'value="' . $this->answer . '"');
        }

        $tpl->parseCurrentBlock();

        return $tpl->get();
    }

    /**
     * @return Answer
     */
    public function readAnswer() : string
    {
        return $_POST[$this->question->getId()];
    }


    /**
     * @param string $answer
     */
    public function setAnswer(string $answer) : void
    {
        $this->answer = floatval($answer);
    }

    public static function generateFields(?AbstractConfiguration $config): ?array {
        /** @var NumericEditorConfiguration $config */

        $fields = [];

        $max_chars = new ilNumberInputGUI('thumb size', self::VAR_MAX_NR_OF_CHARS);
        $fields[] = $max_chars;

        if ($config !== null) {
            $max_chars->setValue($config->getMaxNumOfChars());
        }

        return $fields;
    }

    /**
     * @return JsonSerializable|null
     */
    public static function readConfig() : ?AbstractConfiguration {
        return NumericEditorConfiguration::create(intval($_POST[self::VAR_MAX_NR_OF_CHARS]));
    }
}