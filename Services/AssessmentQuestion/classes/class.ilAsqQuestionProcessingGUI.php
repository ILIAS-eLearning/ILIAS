<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\AssessmentQuestion\Application\ProcessingApplicationService;
use ILIAS\Services\AssessmentQuestion\PublicApi\Common\AuthoringContextContainer;
use ILIAS\Services\AssessmentQuestion\PublicApi\Common\AssessmentEntityId;
use ILIAS\Services\AssessmentQuestion\PublicApi\Authoring\AuthoringService;
use ILIAS\AssessmentQuestion\UserInterface\Web\Component\QuestionComponent;
use ILIAS\AssessmentQuestion\Application\AuthoringApplicationService;
use ILIAS\AssessmentQuestion\DomainModel\QuestionDto;
use ILIAS\AssessmentQuestion\DomainModel\QuestionPlayConfiguration;
use ILIAS\AssessmentQuestion\DomainModel\Answer\Answer;
use ILIAS\Services\AssessmentQuestion\PublicApi\Common\ProcessingContextContainer;
use ILIAS\Services\AssessmentQuestion\PublicApi\Common\QuestionCommands;
use ILIAS\Services\AssessmentQuestion\PublicApi\Common\QuestionConfig;

/**
 * Class ilAsqQuestionProcessingGUI
 *
 * @author       studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author       Adrian Lüthi <al@studer-raimann.ch>
 * @author       Björn Heyser <bh@bjoernheyser.de>
 * @author       Martin Studer <ms@studer-raimann.ch>
 * @author       Theodor Truffer <tt@studer-raimann.ch>
 *
 * @ilCtrl_Calls ilAsqQuestionProcessingGUI: ilAsqQuestionPageGUI
 */
class ilAsqQuestionProcessingGUI
{

    const CMD_SHOW_QUESTION = 'showQuestion';
    const CMD_SAVE_ANSWER = 'saveAnswer';
    const CMD_SHWOW_FEEDBACK = 'showFeedback';
    const CMD_CHOOSE_NEW_QUESTION = 'choose_new_question_cmd_not_set';
    //const CMD_SCORE_PREVIEW = 'scorePreview';
    /**
     * @var ProcessingApplicationService
     */
    protected $processing_application_service;
    /**
     * @var string
     */
    protected $revision_key;
    /**
     * @var QuestionConfig
     */
    protected $question_config;
    /**
     * var string
     */
    protected $choose_new_question_cmd = self::CMD_CHOOSE_NEW_QUESTION;
    /**
     * @var QuestionCommands
     */
    protected $question_comands;


    /**
     * ilAsqQuestionCreationGUI constructor.
     *
     * @param AuthoringContextContainer $contextContainer
     */
    public function __construct(
        string $choose_new_question_cmd,
        string $revision_key,
        ProcessingContextContainer $processing_context_container,
        QuestionConfig $question_config
    ) {
        global $DIC;

        //we could use this in future in constructer
        $lng_key = $DIC->language()->getDefaultLanguage();

        if (!empty($choose_new_question_cmd)) {
            $this->choose_new_question_cmd = $choose_new_question_cmd;
        }

        $this->processing_application_service = new ProcessingApplicationService($processing_context_container->getObjId(), $processing_context_container->getActorId(), $question_config, $lng_key);
        $this->revision_key = $revision_key;
        $this->question_config = $question_config;

        $this->question_comands = new QuestionCommands();
    }


    /**
     * @throws ilAsqException
     */
    public function executeCommand()
    {
        global $DIC;
        /* @var ILIAS\DI\Container $DIC */

        switch ($DIC->ctrl()->getCmd()) {
            case self::CMD_SAVE_ANSWER:
                $this->saveAnswer();
                break;
            case self::CMD_SHWOW_FEEDBACK:
                $this->showFeedback();
                break;
            case self::CMD_CHOOSE_NEW_QUESTION:
            case $this->choose_new_question_cmd:
                throw new ilAsqException("Please implement the the choose_new_question_cmd for your GUI class");
                break;
            case self::CMD_SHOW_QUESTION:
            default:
                $this->showQuestion();
                break;
        }
    }


    public function showQuestion()
    {
        global $DIC;
        $DIC->ui()->mainTemplate()->setContent($this->getQuestionTpl()->get());
    }


    public function saveAnswer()
    {
        global $DIC;
        $question_dto = $this->processing_application_service->getQuestion($this->revision_key);
        $answer = $this->processing_application_service->getCurrentAnswer($question_dto);
        $this->processing_application_service->answerQuestion($answer);

        $DIC->ctrl()->redirect($this, $this->choose_new_question_cmd);
    }


    public function showFeedback()
    {
        global $DIC;
        $question_tpl = $this->getQuestionTpl();
        $question_dto = $this->processing_application_service->getQuestion($this->revision_key);

        $feedback_component = $this->processing_application_service->getFeedbackComponent($question_dto);
        $question_tpl->setCurrentBlock('instant_feedback');
        $question_tpl->setVariable('INSTANT_FEEDBACK', $feedback_component->getHtml());
        $question_tpl->parseCurrentBlock();

        $DIC->ui()->mainTemplate()->setContent($question_tpl->get());
    }


    private function getQuestionTpl() : ilTemplate
    {
        global $DIC;
        /* @var \ILIAS\DI\Container $DIC */
        $question_dto = $this->processing_application_service->getQuestion($this->revision_key);

        //TODO
        $question_commands = new QuestionCommands();
        $question_page = $this->processing_application_service->getQuestionPresentation($question_dto, $question_commands);

        $tpl_question_navigation_html = "";
        if (!is_null($this->question_config->getBtnNext()) || !is_null($this->question_config->getBtnPrev())) {
            $tpl_question_navigation = new ilTemplate('tpl.question_navigation.html', true, true, 'Services/AssessmentQuestion');
            $tpl_question_navigation->setVariable('BTN_NEXT', $DIC->ui()->renderer()->render($this->question_config->getBtnNext()));
            $tpl_question_navigation_html = $tpl_question_navigation->get();
        }

        $tpl = new ilTemplate('tpl.question_container.html', true, true, 'Services/AssessmentQuestion');
        $tpl->setVariable('FORMACTION', $DIC->ctrl()->getFormAction($this, self::CMD_SAVE_ANSWER));
        $tpl->setVariable('QUESTION_NAVIGATION', $tpl_question_navigation_html);
        $tpl->setVariable('QUESTION_OUTPUT', $question_page->preview());

        return $tpl;
    }


    public function scorePreview()
    {
        global $DIC;
        $question_dto = $this->processing_application_service->getQuestion($this->question_id->getId());
        $scoring_component = $this->processing_application_service->getScoringComponent($question_dto);

        /**
         * TODO: we should think about the QuestionComponent again (later).
         * Currently it handles rendering of the question inputs
         * as well as reading from request,
         * altough the answer behavior is settable from outside.
         */

        $answer = new Answer(
            $this->context_container->getActorId(),
            $this->question_id->getId(),
            $this->context_container->getObjId(),
            $question_dto->getRevisionId(),
            $this->questionComponent->readAnswer()
        );

        $this->questionComponent->setAnswer($answer);

        $scoring_class = QuestionPlayConfiguration::getScoringClass($this->questionComponent->getQuestionDto()->getPlayConfiguration());
        $scoring = new $scoring_class($this->questionComponent->getQuestionDto());

        ilUtil::sendInfo("Score: " . $scoring->score($answer));

        $this->showQuestion();
    }
}
