<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Services\AssessmentQuestion\DomainModel\Feedback;

/**
 * Class FeedbackDto
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class FeedbackDto
{
    /**
     * @var int
     */
    private $intId;
    /**
     * @var string
     */
    private $content;


    /**
     * FeedbackDto constructor.
     *
     * @param int $intId
     * @param string $content
     */
    public function __construct(int $intId, string $content)
    {
        $this->intId = $intId;
        $this->content = $content;
    }


    /**
     * @return int
     */
    public function getIntId() : int
    {
        return $this->intId;
    }


    /**
     * @param int $intId
     */
    public function setIntId(int $intId) : void
    {
        $this->intId = $intId;
    }


    /**
     * @return string
     */
    public function getContent() : string
    {
        return $this->content;
    }


    /**
     * @param string $content
     */
    public function setContent(string $content) : void
    {
        $this->content = $content;
    }


    /**
     * @param Feedback $feedback
     *
     * @return FeedbackDto
     */
    public static function createFromFeedback(Feedback $feedback) : self
    {
        return new self($feedback->getIntId(), $feedback->getContent());
    }
}
