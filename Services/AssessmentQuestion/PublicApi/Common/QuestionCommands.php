<?php
declare(strict_types=1);

namespace ILIAS\Services\AssessmentQuestion\PublicApi\Common;

/**
 * Class QuestionCommands
 *
 * @package ILIAS\Services\AssessmentQuestion\PublicApi
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>$
 */
class QuestionCommands
{

    const DEFAULT_SUBMIT_CMD = "submitAnswer";
    const DEFAULT_SHOW_FEEDBACK_CMD = "showFeedback";
    const DEFAULT_GET_HINT_CMD = "getHint";
    /**
     * @var string
     */
    protected $submit_command = self::DEFAULT_SUBMIT_CMD;
    /**
     * @var string
     */
    protected $show_feedback_command = self::DEFAULT_SHOW_FEEDBACK_CMD;
    /**
     * @var string
     */
    protected $get_hint_command = self::DEFAULT_GET_HINT_CMD;


    /**
     * @return string
     */
    public function getSubmitCommand() : string
    {
        return $this->submit_command;
    }


    /**
     * @param string $submit_command
     */
    public function setSubmitCommand(string $submit_command) : void
    {
        $this->submit_command = $submit_command;
    }


    /**
     * @return string
     */
    public function getShowFeedbackCommand() : string
    {
        return $this->show_feedback_command;
    }


    /**
     * @param string $show_feedback_command
     */
    public function setShowFeedbackCommand(string $show_feedback_command) : void
    {
        $this->show_feedback_command = $show_feedback_command;
    }


    /**
     * @return string
     */
    public function getGetHintCommand() : string
    {
        return $this->get_hint_command;
    }


    /**
     * @param string $get_hint_command
     */
    public function setGetHintCommand(string $get_hint_command) : void
    {
        $this->get_hint_command = $get_hint_command;
    }
}