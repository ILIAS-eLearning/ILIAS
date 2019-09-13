<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\AssessmentQuestion\DomainModel;

use http\Exception\InvalidArgumentException;

/**
 * Class ContentEditingMode
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class ContentEditingMode
{
    const RTE_TEXTAREA = 'rte_textarea';
    const PAGE_OBJECT = 'page_object';

    /**
     * @var string
     */
    private $mode;


    /**
     * ContentEditingMode constructor.
     *
     * @param string $mode
     */
    public function __construct(string $mode)
    {
        switch($mode)
        {
            case self::RTE_TEXTAREA:
            case self::PAGE_OBJECT:

                $this->mode = $mode;
                break;

            default: throw new InvalidArgumentException(
                'invalid content editing mode given: '.$mode
            );
        }
    }


    /**
     * @return string
     */
    public function getMode() : string
    {
        return $this->mode;
    }
}