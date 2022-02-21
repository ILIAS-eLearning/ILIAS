<?php declare(strict_types=1);

/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use ILIAS\UI\Component\Input\Field;
use ILIAS\Refinery\Factory as Refinery;

class ilStudyProgrammeAssessmentSettings
{
    const STATUS_DRAFT = 10;
    const STATUS_ACTIVE = 20;
    const STATUS_OUTDATED = 30;

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
