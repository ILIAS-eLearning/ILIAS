<?php

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */
/* Copyright (c) 2019 Stefan Hecken <stefan.hecken@concepts-and-training.de> Extended GPL, see docs/LICENSE */

declare(strict_types = 1);

/**
 * Class ilStudyProgramme
 *
 * @author: Richard Klees <richard.klees@concepts-and-training.de>
 * @author: Denis Klöpfer <richard.klees@concepts-and-training.de>
 * @version: 0.1.0
 */

class ilStudyProgrammeSettings
{
    
    // There are two different modes the programs calculation of the learning
    // progress can run in. It is also possible, that the mode is not defined
    // yet.
    const MODE_UNDEFINED = 0;
    
    // User is successful if he collected enough points in the subnodes of
    // this node.
    const MODE_POINTS = 1;

    // User is successful if he has the "completed" learning progress in any
    // subobject.
    const MODE_LP_COMPLETED = 2;

    public static $MODES = array(
        self::MODE_UNDEFINED,
        self::MODE_POINTS,
        self::MODE_LP_COMPLETED
    );


    // A program tree has a lifecycle during which it has three status.

    // The program is a draft, that is users won't be assigned to the program
    // already.
    const STATUS_DRAFT = 10;

    // The program is active, that is users can be assigned to it.
    const STATUS_ACTIVE = 20;

    // The program is outdated, that is users won't be assigned to it but can
    // still complete the program.
    const STATUS_OUTDATED = 30;

    const NO_RESTART = -1;
    const NO_VALIDITY_OF_QUALIFICATION_PERIOD = -1;

    // Defaults
    const DEFAULT_POINTS = 100;
    const DEFAULT_SUBTYPE = 0; // TODO: What should that be?
    
    const DATE_TIME_FORMAT = 'Y-m-d H:i:s';
    const DATE_FORMAT = 'Y-m-d';

    /**
     * Id of this study program and the corresponding ILIAS-object as well.
     *
     * @var int
     */
    protected $obj_id;
    
    /**
     * Timestamp of the moment the last change was made on this object or any
     * object in the subtree of the program.
     *
     * @var string
     */
    protected $last_change;

    /**
     * Mode the calculation of the learning progress on this node is run in.
     *
     * @var int
     */
    protected $lp_mode;

    /**
     * @var \ilStudyProgrammeTypeSettings
     */
    protected $type_settings;

    /**
     * @var \ilStudyProgrammeAssessmentSettings
     */
    protected $assessment_settings;

    /**
     * @var \ilStudyProgrammeDeadlineSettings
     */
    protected $deadline_settings;

    /**
     * @var \ilStudyProgrammeValidityOfAchievedQualificationSettings
     */
    protected $validity_of_qualification_settings;

    /**
     * Is the access control governed by positions?
     *
     * @var bool
     */
    protected $access_ctrl_positions;

    /**
     * @var \ilStudyProgrammeAutoMailSettings
     */
    protected $automail_settings;

    public function __construct(
        int $a_id,
        \ilStudyProgrammeTypeSettings $type_settings,
        \ilStudyProgrammeAssessmentSettings $assessment_settings,
        \ilStudyProgrammeDeadlineSettings $deadline_settings,
        \ilStudyProgrammeValidityOfAchievedQualificationSettings $validity_of_qualification_settings,
        \ilStudyProgrammeAutoMailSettings $automail_settings
    ) {
        $this->obj_id = $a_id;
        $this->type_settings = $type_settings;
        $this->assessment_settings = $assessment_settings;
        $this->deadline_settings = $deadline_settings;
        $this->validity_of_qualification_settings = $validity_of_qualification_settings;
        $this->automail_settings = $automail_settings;
    }
    
    /**
     * Get the id of the study program.
     *
     * @return integer
     */
    public function getObjId() : int
    {
        return (int) $this->obj_id;
    }

    /**
     * Get the timestamp of the last change on this program or a sub program.
     *
     * @return DateTime
     */
    public function getLastChange() : DateTime
    {
        return DateTime::createFromFormat(self::DATE_TIME_FORMAT, $this->last_change);
    }

    /**
     * Update the last change timestamp to the current time.
     *
     * @return $this
     */
    public function updateLastChange() : ilStudyProgrammeSettings
    {
        $this->setLastChange(new DateTime());
        return $this;
    }

    /**
     * Set the last change timestamp to the given time.
     *
     * Throws when given time is smaller then current timestamp
     * since that is logically impossible.
     *
     * @return $this
     */
    public function setLastChange(DateTime $a_timestamp) : ilStudyProgrammeSettings
    {
        $this->last_change = $a_timestamp->format(self::DATE_TIME_FORMAT);
        return $this;
    }

    /**
     * Set the lp mode.
     *
     * Throws when program is not in draft status.
     *
     * @param integer $a_mode       - one of self::$MODES
     * @return $this
     */
    public function setLPMode(int $a_mode) : ilStudyProgrammeSettings
    {
        $a_mode = (int) $a_mode;
        if (!in_array($a_mode, self::$MODES)) {
            throw new ilException("ilStudyProgramme::setLPMode: No lp mode: "
                                 . "'$a_mode'");
        }
        $this->lp_mode = $a_mode;
        $this->updateLastChange();
        return $this;
    }

    /**
     * Get the lp mode.
     *
     * @return integer  - one of self::$MODES
     */
    public function getLPMode() : int
    {
        return (int) $this->lp_mode;
    }

    public function getTypeSettings() : \ilStudyProgrammeTypeSettings
    {
        return $this->type_settings;
    }

    public function withTypeSettings(
        \ilStudyProgrammeTypeSettings $type_settings
    ) : ilStudyProgrammeSettings {
        $clone = clone $this;
        $clone->type_settings = $type_settings;
        return $clone;
    }

    public function getAssessmentSettings() : \ilStudyProgrammeAssessmentSettings
    {
        return $this->assessment_settings;
    }

    public function withAssessmentSettings(
        \ilStudyProgrammeAssessmentSettings $assessment_settings
    ) : ilStudyProgrammeSettings {
        $clone = clone $this;
        $clone->assessment_settings = $assessment_settings;
        $clone->updateLastChange();
        return $clone;
    }

    public function getDeadlineSettings() : \ilStudyProgrammeDeadlineSettings
    {
        return $this->deadline_settings;
    }

    public function withDeadlineSettings(
        \ilStudyProgrammeDeadlineSettings $deadline_settings
    ) : ilStudyProgrammeSettings {
        $clone = clone $this;
        $clone->deadline_settings = $deadline_settings;
        return $clone;
    }

    public function getValidityOfQualificationSettings() : \ilStudyProgrammeValidityOfAchievedQualificationSettings
    {
        return $this->validity_of_qualification_settings;
    }

    public function withValidityOfQualificationSettings(
        \ilStudyProgrammeValidityOfAchievedQualificationSettings $validity_of_qualification_settings
    ) : ilStudyProgrammeSettings {
        $clone = clone $this;
        $clone->validity_of_qualification_settings = $validity_of_qualification_settings;
        return $clone;
    }

    public function validationExpires() : bool
    {
        return !is_null($this->getValidityOfQualificationSettings()->getQualificationDate()) ||
                $this->getValidityOfQualificationSettings()->getQualificationPeriod() != -1;
    }

    public function getAutoMailSettings() : \ilStudyProgrammeAutoMailSettings
    {
        return $this->automail_settings;
    }

    public function withAutoMailSettings(
        \ilStudyProgrammeAutoMailSettings $automail_settings
    ) : ilStudyProgrammeSettings {
        $clone = clone $this;
        $clone->automail_settings = $automail_settings;
        return $clone;
    }
}
