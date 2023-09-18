<?php

declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

class ilStudyProgrammeSettings
{
    // There are two different modes the programs' calculation of the learning
    // progress can run in. It is also possible, that the mode is not defined
    // yet.
    public const MODE_UNDEFINED = 0;

    // User is successful if he collected enough points in the sub nodes of
    // this node.
    public const MODE_POINTS = 1;

    // User is successful if he has the "completed" learning progress in any
    // sub object.
    public const MODE_LP_COMPLETED = 2;

    public static array $MODES = [
        self::MODE_UNDEFINED,
        self::MODE_POINTS,
        self::MODE_LP_COMPLETED
    ];


    // A program tree has a lifecycle during which it has three status.

    // The program is a draft, that is users won't be assigned to the program
    // already.
    public const STATUS_DRAFT = 10;

    // The program is active, that is users can be assigned to it.
    public const STATUS_ACTIVE = 20;

    // The program is outdated, that is users won't be assigned to it but can
    // still complete the program.
    public const STATUS_OUTDATED = 30;

    public const NO_RESTART = -1;
    public const NO_VALIDITY_OF_QUALIFICATION_PERIOD = -1;
    public const NO_DEADLINE = -1;

    // Defaults
    public const DEFAULT_POINTS = 100;
    public const DEFAULT_SUBTYPE = 0;

    public const DATE_TIME_FORMAT = 'Y-m-d H:i:s';
    public const DATE_FORMAT = 'Y-m-d';

    /**
     * Id of this study program and the corresponding ILIAS-object as well.
     */
    protected int $obj_id;

    /**
     * Timestamp of the moment the last change was made on this object or any
     * object in the subtree of the program.
     */
    protected string $last_change;

    /**
     * Mode the calculation of the learning progress on this node is run in.
     */
    protected int $lp_mode;

    /**
     * Is the access control governed by positions?
     */
    protected bool $access_ctrl_positions;

    protected ilStudyProgrammeTypeSettings $type_settings;
    protected ilStudyProgrammeAssessmentSettings $assessment_settings;
    protected ilStudyProgrammeDeadlineSettings $deadline_settings;
    protected ilStudyProgrammeValidityOfAchievedQualificationSettings $validity_of_qualification_settings;
    protected ilStudyProgrammeAutoMailSettings $automail_settings;

    public function __construct(
        int $a_id,
        ilStudyProgrammeTypeSettings $type_settings,
        ilStudyProgrammeAssessmentSettings $assessment_settings,
        ilStudyProgrammeDeadlineSettings $deadline_settings,
        ilStudyProgrammeValidityOfAchievedQualificationSettings $validity_of_qualification_settings,
        ilStudyProgrammeAutoMailSettings $automail_settings
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
     */
    public function getObjId(): int
    {
        return $this->obj_id;
    }

    /**
     * Get the timestamp of the last change on this program or a sub program.
     */
    public function getLastChange(): DateTime
    {
        return DateTime::createFromFormat(self::DATE_TIME_FORMAT, $this->last_change);
    }

    /**
     * Update the last change timestamp to the current time.
     */
    public function updateLastChange(): ilStudyProgrammeSettings
    {
        $this->setLastChange(new DateTime());
        return $this;
    }

    /**
     * Set the last change timestamp to the given time.
     *
     * Throws when given time is smaller than current timestamp
     * since that is logically impossible.
     */
    public function setLastChange(DateTime $a_timestamp): ilStudyProgrammeSettings
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
     */
    public function setLPMode(int $a_mode): ilStudyProgrammeSettings
    {
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
     * @return int one of self::$MODES
     */
    public function getLPMode(): int
    {
        return $this->lp_mode;
    }

    public function getTypeSettings(): ilStudyProgrammeTypeSettings
    {
        return $this->type_settings;
    }

    public function withTypeSettings(ilStudyProgrammeTypeSettings $type_settings): ilStudyProgrammeSettings
    {
        $clone = clone $this;
        $clone->type_settings = $type_settings;
        return $clone;
    }

    public function getAssessmentSettings(): ilStudyProgrammeAssessmentSettings
    {
        return $this->assessment_settings;
    }

    public function withAssessmentSettings(
        ilStudyProgrammeAssessmentSettings $assessment_settings
    ): ilStudyProgrammeSettings {
        $clone = clone $this;
        $clone->assessment_settings = $assessment_settings;
        $clone->updateLastChange();
        return $clone;
    }

    public function getDeadlineSettings(): ilStudyProgrammeDeadlineSettings
    {
        return $this->deadline_settings;
    }

    public function withDeadlineSettings(
        ilStudyProgrammeDeadlineSettings $deadline_settings
    ): ilStudyProgrammeSettings {
        $clone = clone $this;
        $clone->deadline_settings = $deadline_settings;
        return $clone;
    }

    public function getValidityOfQualificationSettings(): ilStudyProgrammeValidityOfAchievedQualificationSettings
    {
        return $this->validity_of_qualification_settings;
    }

    public function withValidityOfQualificationSettings(
        ilStudyProgrammeValidityOfAchievedQualificationSettings $validity_of_qualification_settings
    ): ilStudyProgrammeSettings {
        $clone = clone $this;
        $clone->validity_of_qualification_settings = $validity_of_qualification_settings;
        return $clone;
    }

    public function validationExpires(): bool
    {
        return !is_null($this->getValidityOfQualificationSettings()->getQualificationDate()) ||
                $this->getValidityOfQualificationSettings()->getQualificationPeriod() !== self::NO_VALIDITY_OF_QUALIFICATION_PERIOD;
    }

    public function getAutoMailSettings(): ilStudyProgrammeAutoMailSettings
    {
        return $this->automail_settings;
    }

    public function withAutoMailSettings(
        ilStudyProgrammeAutoMailSettings $automail_settings
    ): ilStudyProgrammeSettings {
        $clone = clone $this;
        $clone->automail_settings = $automail_settings;
        return $clone;
    }
}
