<?php

namespace ILIAS\LTI\ToolProvider;

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
/**
 * Class to represent an outcome
 *
 * @author  Stephen P Vickers <stephen@spvsoftwareproducts.com>
 * @copyright  SPV Software Products
 * @license  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3
 */
class Outcome
{

    /**
     * Allowed values for Activity Progress.
     */
    const ALLOWED_ACTIVITY_PROGRESS = array(
        'Initialized',
        'Started',
        'InProgress',
        'Submitted',
        'Completed'
    );

    /**
     * Allowed values for Grading Progress.
     */
    const ALLOWED_GRADING_PROGRESS = array(
        'FullyGraded',
        'Pending',
        'PendingManual',
        'Failed',
        'NotReady'
    );

    /**
     * Language value.
     *
     * @var string|null $language
     */
    public ?string $language = null;

    /**
     * Outcome status value.
     *
     * @var string|null $status
     */
    public ?string $status = null;

    /**
     * Outcome date value.
     *
     * @var string|null $date
     */
    public ?string $date = null;

    /**
     * Outcome type value.
     *
     * @var string|null $type
     */
    public ?string $type = null;

    /**
     * Activity progress.
     *
     * @var string|null $activityProgress
     */
    public ?string $activityProgress = null;

    /**
     * Grading progress.
     *
     * @var string|null $gradingProgress
     */
    public ?string $gradingProgress = null;

    /**
     * Comment.
     *
     * @var string|null $comment
     */
    public ?string $comment = null;

    /**
     * Outcome data source value.
     *
     * @var string|null $dataSource
     */
    public ?string $dataSource = null;

    /**
     * Outcome value.
     *
     * @var mixed|string|null $value
     */
    private $value = null;

    /**
     * Points possible value.
     *
     * @var int $pointsPossible
     */
    private int $pointsPossible = 1;

    /**
     * Class constructor.
     * @param mixed  $value            Outcome value (optional, default is none)
     * @param int    $pointsPossible   Points possible value (optional, default is none)
     * @param string $activityProgress Activity progress (optional, default is 'Completed')
     * @param string $gradingProgress  Grading progress (optional, default is 'FullyGraded')
     */
    public function __construct(
        $value = null,
        int $pointsPossible = 1,
        string $activityProgress = 'Completed',
        string $gradingProgress = 'FullyGraded'
    ) {
        $this->value = $value;
        $this->pointsPossible = $pointsPossible;
        $this->language = 'en-US';
        $this->date = gmdate('Y-m-d\TH:i:s\Z', time());
        $this->type = 'decimal';
        if (in_array($activityProgress, self::ALLOWED_ACTIVITY_PROGRESS)) {
            $this->activityProgress = $activityProgress;
        } else {
            $this->activityProgress = 'Completed';
        }
        if (in_array($gradingProgress, self::ALLOWED_GRADING_PROGRESS)) {
            $this->gradingProgress = $gradingProgress;
        } else {
            $this->gradingProgress = 'FullyGraded';
        }
        $this->comment = '';
    }

    /**
     * Get the outcome value.
     *
     * @return string Outcome value
     */
    public function getValue() : ?string
    {
        return $this->value;
    }

    /**
     * Set the outcome value.
     * @param string $value Outcome value
     */
    public function setValue(string $value)
    {
        $this->value = $value;
    }

    /**
     * Get the points possible value.
     *
     * @return int|null Points possible value
     */
    public function getPointsPossible() : ?int
    {
        return $this->pointsPossible;
    }

    /**
     * Set the points possible value.
     * @param int|null $pointsPossible Points possible value
     */
    public function setPointsPossible(?int $pointsPossible)
    {
        $this->pointsPossible = $pointsPossible;
    }
}
