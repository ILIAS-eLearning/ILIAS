<?php

namespace ILIAS\AssessmentQuestion\UserInterface\Web\Component\Editor;

use ILIAS\AssessmentQuestion\DomainModel\AbstractConfiguration;
use ILIAS\AssessmentQuestion\DomainModel\QuestionDto;
use ilNumberInputGUI;
use ilCheckboxInputGUI;
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
    const VAR_GEOMETRY = "oe_geometry";

    /**
     * @var OrderingEditorConfiguration
     */
    private $configuration;
    /**
     * @var string
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
        
        $fields = [];
        
        $is_vertical = new ilCheckboxInputGUI('is vertical', self::VAR_VERTICAL);
        $is_vertical->setValue(true);
        $fields[] = $is_vertical;
        
        $mimimum_size = new ilNumberInputGUI('min size', self::VAR_MINIMUM_SIZE);
        $fields[] = $mimimum_size;
        
        $geometry = new ilNumberInputGUI('geometry', self::VAR_GEOMETRY);
        $geometry->setRequired(true);
        $fields[] = $geometry;
        
        if ($config !== null) {
            $is_vertical->setChecked($config->isVertical());
            $mimimum_size->setValue($config->getMinimumSize());
            $geometry->setValue($config->getGeometry());
        }
        else {
            //TODO sane default? why required?
            $geometry->setValue(100);
        }
        
        return $fields;
    }
    
    public static function readConfig()
    {
        return OrderingEditorConfiguration::create(
            boolval($_POST[self::VAR_VERTICAL]), 
            intval($_POST[self::VAR_MINIMUM_SIZE]), 
            intval($_POST[self::VAR_GEOMETRY]));
    }
    
    /**
     * @return string
     */
    static function getDisplayDefinitionClass() : string {
        return ImageAndTextDisplayDefinition::class;
    }
}