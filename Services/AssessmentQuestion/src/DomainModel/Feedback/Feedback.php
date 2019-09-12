<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Services\AssessmentQuestion\DomainModel\Feedback;

/**
 * Class Feedback
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class Feedback
{
    /**
     * @var int
     */
    private $intId;

    /**
     * @var string
     */
    private $content;


    public function __construct()
    {
        $this->intId = 2728;
        $this->content = 'Any Wright or Wrong or Answer Behaviour Related Feedback';
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
}
