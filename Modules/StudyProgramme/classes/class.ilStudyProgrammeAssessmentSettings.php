<?php declare(strict_types=1);

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

use ILIAS\UI\Component\Input\Field;
use ILIAS\Refinery\Factory as Refinery;

class ilStudyProgrammeAssessmentSettings
{
    public const STATUS_DRAFT = 10;
    public const STATUS_ACTIVE = 20;
    public const STATUS_OUTDATED = 30;

    public static array $STATUS = [
        self::STATUS_DRAFT,
        self::STATUS_ACTIVE,
        self::STATUS_OUTDATED
    ];

    protected int $points;
    protected int $status;

    public function __construct(int $points, int $status)
    {
        if (0 > $points) {
            throw new InvalidArgumentException('Numbers less than 0 are not allowed');
        }

        if (!in_array($status, self::$STATUS)) {
            throw new InvalidArgumentException("No valid status: '$status'");
        }

        $this->points = $points;
        $this->status = $status;
    }

    public function getPoints() : int
    {
        return $this->points;
    }

    public function withPoints(int $points) : ilStudyProgrammeAssessmentSettings
    {
        if (0 > $points) {
            throw new InvalidArgumentException('Numbers less than 0 are not allowed');
        }

        $clone = clone $this;
        $clone->points = $points;
        return $clone;
    }

    public function getStatus() : int
    {
        return $this->status;
    }

    public function withStatus(int $status) : ilStudyProgrammeAssessmentSettings
    {
        if (!in_array($status, self::$STATUS)) {
            throw new InvalidArgumentException("No valid status: '$status'");
        }

        $clone = clone $this;
        $clone->status = $status;
        return $clone;
    }

    public function toFormInput(
        Field\Factory $input,
        ilLanguage $lng,
        Refinery $refinery
    ) : Field\Input {
        $num = $input
            ->numeric($lng->txt('prg_points'), $lng->txt('prg_points_byline'))
            ->withValue($this->getPoints())
            ->withAdditionalTransformation($refinery->int()->isGreaterThanOrEqual(0))
        ;
        $select = $input
            ->select(
                $lng->txt('prg_status'),
                $this->getStatusOptions($lng),
                $lng->txt('prg_status_byline')
            )
            ->withValue($this->getStatus())
            ->withRequired(true)
        ;

        return $input->section(
            [
                'points' => $num,
                'status' => $select
            ],
            $lng->txt('prg_assessment')
        )
        ->withAdditionalTransformation($refinery->custom()->transformation(function ($vals) {
            return new ilStudyProgrammeAssessmentSettings(
                (int) $vals['points'],
                (int) $vals['status']
            );
        }));
    }

    protected function getStatusOptions(ilLanguage $lng) : array
    {
        return [
            ilStudyProgrammeSettings::STATUS_DRAFT => $lng->txt("prg_status_draft"),
            ilStudyProgrammeSettings::STATUS_ACTIVE => $lng->txt("prg_status_active"),
            ilStudyProgrammeSettings::STATUS_OUTDATED => $lng->txt("prg_status_outdated")
        ];
    }
}
