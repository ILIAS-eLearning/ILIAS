<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Services\AssessmentQuestion\PublicApi\Common;

use ILIAS\UI\Component\Link\Standard as UiStandardLink;

/**
 * Class AuthoringContextContainer
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 *
 * @package ILIAS\Services\AssessmentQuestion\PublicApi\Common
 */
class AuthoringContextContainer
{
    /**
     * @var UiStandardLink
     */
    protected $backLink;

    /**
     * @var int
     */
    protected $refId;

    /**
     * @var int
     */
    protected $objId;

    /**
     * @var string
     */
    protected $objType;

    /**
     * @var int
     */
    protected $actorId;

    /**
     * @var bool
     */
    protected $writeAccess;

    /**
     * @var array
     */
    protected $afterQuestionCreationCtrlClassPath;

    /**
     * @var string
     */
    protected $afterQuestionCreationCtrlCommand;


    /**
     * AuthoringContextContainer constructor.
     *
     * @param UiStandardLink $backLink
     * @param int            $refId
     * @param int            $objId
     * @param string         $objType
     * @param int            $actorId
     * @param bool           $writeAccess
     */
    public function __construct(
        UiStandardLink $backLink,
        int $refId,
        int $objId,
        string $objType,
        int $actorId,
        bool $writeAccess,
        array $afterQuestionCreationCtrlClassPath,
        string $afterQuestionCreationCtrlCommand
    )
    {
        $this->backLink = $backLink;
        $this->refId = $refId;
        $this->objId = $objId;
        $this->objType = $objType;
        $this->actorId = $actorId;
        $this->writeAccess = $writeAccess;
        $this->afterQuestionCreationCtrlClassPath = $afterQuestionCreationCtrlClassPath;
        $this->afterQuestionCreationCtrlCommand = $afterQuestionCreationCtrlCommand;
    }


    /**
     * @return UiStandardLink
     */
    public function getBackLink() : UiStandardLink
    {
        return $this->backLink;
    }


    /**
     * @return int
     */
    public function getRefId() : int
    {
        return $this->refId;
    }


    /**
     * @return int
     */
    public function getObjId() : int
    {
        return $this->objId;
    }


    /**
     * @return string
     */
    public function getObjType() : string
    {
        return $this->objType;
    }


    /**
     * @return int
     */
    public function getActorId() : int
    {
        return $this->actorId;
    }


    /**
     * @return bool
     */
    public function hasWriteAccess() : bool
    {
        return $this->writeAccess;
    }


    /**
     * @return array
     */
    public function getAfterQuestionCreationCtrlClassPath() : array
    {
        return $this->afterQuestionCreationCtrlClassPath;
    }


    /**
     * @return string
     */
    public function getAfterQuestionCreationCtrlCmdClass() : string
    {
        return end($this->afterQuestionCreationCtrlClassPath);
    }


    /**
     * @return string
     */
    public function getAfterQuestionCreationCtrlCommand() : string
    {
        return $this->afterQuestionCreationCtrlCommand;
    }
}
