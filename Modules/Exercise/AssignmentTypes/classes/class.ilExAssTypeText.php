<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Modules/Exercise/AssignmentTypes/classes/interface.ilExAssignmentTypeInterface.php");

/**
 * Text type
 *
 * @author Alex Killing <killing@leifos.de>
 */
class ilExAssTypeText implements ilExAssignmentTypeInterface
{
    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * Constructor
     *
     * @param ilLanguage|null $a_lng
     */
    public function __construct(ilLanguage $a_lng = null)
    {
        global $DIC;

        $this->lng = ($a_lng)
            ? $a_lng
            : $DIC->language();
    }

    /**
     * @inheritdoc
     */
    public function isActive()
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function usesTeams()
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function usesFileUpload()
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function getTitle()
    {
        $lng = $this->lng;

        return $lng->txt("exc_type_text");
    }

    /**
     * @inheritdoc
     */
    public function getSubmissionType()
    {
        return ilExSubmission::TYPE_TEXT;
    }

    /**
     * @inheritdoc
     */
    public function isSubmissionAssignedToTeam()
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function cloneSpecificProperties(ilExAssignment $source, ilExAssignment $target)
    {
    }
}
