<?php

namespace ILIAS\AssessmentQuestion\UserInterface\Web\Component\Editor;

use ILIAS\AssessmentQuestion\DomainModel\AbstractConfiguration;
use ILIAS\AssessmentQuestion\DomainModel\Question;
use ILIAS\AssessmentQuestion\DomainModel\QuestionDto;
use ilNumberInputGUI;
use ilSelectInputGUI;
use ilTemplate;

/**
 * Class OrderingEditor
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class OrderingEditor extends AbstractEditor {
    const VAR_VERTICAL = "oe_vertical";
    const VAR_MINIMUM_SIZE = "oe_minimum_size";
    const VERTICAL = "vertical";
    const HORICONTAL = "horicontal";
    
    /**
     * @var OrderingEditorConfiguration
     */
    private $configuration;
    /**
     * @var string
     */
    private $answer;
    
    public function __construct(QuestionDto $question) {
        $this->configuration = $question->getPlayConfiguration()->getEditorConfiguration();
        
        parent::__construct($question);
    }

    /**
     * @return string
     */
    public function generateHtml() : string
    {
        $tpl = new ilTemplate("tpl.OrderingEditor.html", true, true, "Services/AssessmentQuestion");

        if (empty($this->answer)) {
            $items = $this->question->getAnswerOptions()->getOptions();
            shuffle($items);

            $this->answer = implode(',',
                array_map(
                    function($answer_option)
                    {
                        return $answer_option->getOptionId();
                    },
                    $items));
        }
        else {
            $items = $this->orderItemsByAnswer();
        }

        foreach ($items as $item) {
            $tpl->setCurrentBlock('item');
            $tpl->setVariable('OPTION_ID', $item->getOptionId());
            $tpl->setVariable('ITEM_TEXT', $item->getDisplayDefinition()->getText());
            
            if (!empty($this->configuration->getMinimumSize())) {
                $tpl->setVariable('HEIGHT', sprintf(' style="height: %spx" ', $this->configuration->getMinimumSize()));
            }
            
            $tpl->parseCurrentBlock();
        }

        $tpl->setCurrentBlock('editor');
        
        if (!$this->configuration->isVertical()) {
            $tpl->setVariable('ADD_CLASS', 'horizontal');
        }
        
        $tpl->setVariable('POST_NAME', $this->question->getId());
        $tpl->setVariable('ANSWER', $this->answer);
        $tpl->parseCurrentBlock();

        return $tpl->get();
    }

    private function orderItemsByAnswer() : array {
        $ordering = array_map('intval',explode(',', $this->answer));
        $answers = $this->question->getAnswerOptions()->getOptions();

        $items = [];

        foreach ($ordering as $index) {
            $items[] = $answers[$index - 1];
        }

        return $items;
    }

    public function readAnswer(): string
    {
        return $_POST[$this->question->getId()];
    }
    
    public function setAnswer(string $answer): void
    {
        $this->answer = $answer;
    }
    
    public static function generateFields(?AbstractConfiguration $config): ?array {
        /** @var OrderingEditorConfiguration $config */
        global $DIC;
        
        $fields = [];
        
        $is_vertical = new ilSelectInputGUI($DIC->language()->txt('asq_label_is_vertical'), self::VAR_VERTICAL);
        $is_vertical->setOptions([
            self::VERTICAL => $DIC->language()->txt('asq_label_vertical'),
            self::HORICONTAL => $DIC->language()->txt('asq_label_horicontal')
        ]);
        $fields[self::VAR_VERTICAL] = $is_vertical;
        
        $minimum_size = new ilNumberInputGUI($DIC->language()->txt('asq_label_min_size'), self::VAR_MINIMUM_SIZE);
        $minimum_size->setInfo($DIC->language()->txt('asq_description_min_size'));
        $minimum_size->setSize(6);
        $fields[self::VAR_MINIMUM_SIZE] = $minimum_size;
        
        if ($config !== null) {
            $minimum_size->setValue($config->getMinimumSize());
            $is_vertical->setValue($config->isVertical() ? self::VERTICAL : self::HORICONTAL);
        }
        else {
            $is_vertical->setValue(self::VERTICAL);
        }
        
        return $fields;
    }
    
    public static function readConfig()
    {
        return OrderingEditorConfiguration::create(
            $_POST[self::VAR_VERTICAL] === self::VERTICAL, 
            !empty($_POST[self::VAR_MINIMUM_SIZE]) ? intval($_POST[self::VAR_MINIMUM_SIZE]) : null);
    }
    
    /**
     * @return string
     */
    static function getDisplayDefinitionClass() : string {
        return ImageAndTextDisplayDefinition::class;
    }
    
    public static function isComplete(Question $question): bool
    {
        foreach ($question->getAnswerOptions()->getOptions() as $option) {
            /** @var ImageAndTextDisplayDefinition $option_config */
            $option_config = $option->getScoringDefinition();
            
            if (empty($option_config->getText()))
            {
                return false;
            }
        }
        
        return true;
    }
}