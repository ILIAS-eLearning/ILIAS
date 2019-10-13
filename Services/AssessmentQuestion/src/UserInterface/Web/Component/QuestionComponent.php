<?php

namespace ILIAS\AssessmentQuestion\UserInterface\Web\Component;

use ILIAS\AssessmentQuestion\DomainModel\Answer\Answer;
use ILIAS\AssessmentQuestion\DomainModel\QuestionDto;
use ILIAS\AssessmentQuestion\DomainModel\QuestionPlayConfiguration;
use ILIAS\AssessmentQuestion\UserInterface\Web\Component\Editor\AbstractEditor;
use ILIAS\AssessmentQuestion\UserInterface\Web\Component\Presenter\AbstractPresenter;
use ILIAS\Services\AssessmentQuestion\PublicApi\Common\QuestionCommands;
use ILIAS\Services\AssessmentQuestion\PublicApi\Common\QuestionConfig;
use ilTemplate;

/**
 * Class QuestionComponent
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class QuestionComponent
{

    /**
     * @var QuestionDto
     */
    private $question_dto;
    /**
     * @var AbstractPresenter
     */
    private $presenter;
    /**
     * @var AbstractEditor
     */
    private $editor;
    /**
     * @var QuestionConfig
     */
    private $question_config;

    /**
     * @var QuestionCommands
     */
    private $question_commands;


    public function __construct(QuestionDto $question_dto, QuestionConfig $question_config, QuestionCommands $question_commands)
    {
        $this->question_dto = $question_dto;
        $this->question_config = $question_config;
        $this->question_commands = $question_commands;

        $presenter_class = QuestionPlayConfiguration::getPresenterClass($question_dto->getPlayConfiguration());
        $presenter = new $presenter_class($question_dto);

        $editor_class = QuestionPlayConfiguration::getEditorClass($question_dto->getPlayConfiguration());
        $editor = new $editor_class($question_dto);

        $this->presenter = $presenter;
        $this->editor = $editor;
    }


    public function renderHtml() : string
    {
        global $DIC;

        $tpl = new ilTemplate("tpl.question_view.html", true, true, "Services/AssessmentQuestion");

        if ($this->question_config->isFeedbackOnDemand()) {
            $tpl->setCurrentBlock('feedback_button');
            $tpl->setVariable('FEEDBACK_BUTTON_TITLE', $DIC->language()->txt('asq_feedback_buttom_title'));
            $tpl->setVariable('FEEDBACK_COMMAND', $this->question_commands->getShowFeedbackCommand());
            $tpl->parseCurrentBlock();
        }
        if ($this->question_config->isHintsActivated() && is_object($this->question_dto->getQuestionHints()) && count($this->question_dto->getQuestionHints()->getHints())) {
            $tpl->setCurrentBlock('hint_button');
            $tpl->setVariable('HINT_BUTTON_TITLE', $DIC->language()->txt('asq_hint_buttom_title'));
            $tpl->setVariable('HINT_COMMAND', $this->question_commands->getGetHintCommand());
            $tpl->parseCurrentBlock();
        }
        $tpl->setCurrentBlock('question');
        $tpl->setVariable('SCORE_COMMAND', $this->question_commands->getSubmitCommand());
        $tpl->setVariable('QUESTION_OUTPUT', $this->presenter->generateHtml($this->editor));
        $tpl->setVariable('BUTTON_TITLE', $DIC->language()->txt('check'));
        $tpl->parseCurrentBlock();

        return $tpl->get();
    }


    public function readAnswer() : string
    {
        return $this->editor->readAnswer();
    }


    public function setAnswer(Answer $answer)
    {
        $this->editor->setAnswer($answer->getValue());
    }


    /**
     * @deprecated
     * to be removed, but neccessary for the moment
     */
    public function getQuestionDto()
    {
        return $this->question_dto;
    }
}