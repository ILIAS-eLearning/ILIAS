<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\AssessmentQuestion\DomainModel;

/**
 * Class ContentEditingModeDto
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class ContentEditingModeDto
{
    /**
     * @var string
     */
    private $mode;


    /**
     * ContentEditingModeDto constructor.
     *
     * @param string $mode
     */
    public static function createFromContentEditingMode(ContentEditingMode $mode)
    {
        $dto = new self();
        $dto->mode = $mode->getMode();

        return $dto;
    }


    /**
     * @return bool
     */
    public function isRteTextarea() : bool
    {
        return $this->mode == ContentEditingMode::RTE_TEXTAREA;
    }


    /**
     * @return bool
     */
    public function isPageObject() : bool
    {
        return $this->mode == ContentEditingMode::PAGE_OBJECT;
    }
}
