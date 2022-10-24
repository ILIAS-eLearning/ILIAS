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

use ILIAS\Data\Factory;
use ILIAS\UI\Component\Input\Field;
use ILIAS\Refinery\Factory as Refinery;

class ilStudyProgrammeDeadlineSettings
{
    protected ?int $deadline_period;
    protected ?DateTimeImmutable $deadline_date;

    public function __construct(?int $deadline_period, ?DateTimeImmutable $deadline_date)
    {
        if (!is_null($deadline_period) && 0 > $deadline_period) {
            throw new InvalidArgumentException('Numbers less than 0 are not allowed');
        }

        $this->deadline_period = $deadline_period;
        $this->deadline_date = $deadline_date;
    }

    public function getDeadlinePeriod(): ?int
    {
        return $this->deadline_period;
    }

    public function withDeadlinePeriod(?int $deadline_period): ilStudyProgrammeDeadlineSettings
    {
        if (!is_null($deadline_period) && 0 > $deadline_period) {
            throw new InvalidArgumentException('Numbers less than 0 are not allowed');
        }

        $clone = clone $this;
        $clone->deadline_period = $deadline_period;
        return $clone;
    }

    public function getDeadlineDate(): ?DateTimeImmutable
    {
        return $this->deadline_date;
    }

    public function withDeadlineDate(?DateTimeImmutable $deadline_date): ilStudyProgrammeDeadlineSettings
    {
        $clone = clone $this;
        $clone->deadline_date = $deadline_date;
        return $clone;
    }

    public function toFormInput(
        Field\Factory $input,
        ilLanguage $lng,
        Refinery $refinery,
        Factory $data_factory
    ): Field\Input {
        $format = $data_factory->dateFormat()->germanShort();

        $grp1 = $input->group([], $lng->txt('prg_no_deadline'));
        $grp2 = $input->group(
            [
                'deadline_period' => $input->numeric(
                    '',
                    $lng->txt('prg_deadline_period_desc')
                )
                ->withAdditionalTransformation($refinery->int()->isGreaterThanOrEqual(1))
                ->withValue($this->getDeadlinePeriod())
            ],
            $lng->txt('prg_deadline_period')
        );
        $grp3 = $input->group(
            [
                'deadline_date' => $input->dateTime(
                    '',
                    $lng->txt('prg_deadline_date_desc')
                )
                ->withFormat($format)
                ->withValue($this->getDeadlineDate() !== null ? $this->getDeadlineDate()->format('d.m.Y') : '')
                ->withRequired(true)
            ],
            $lng->txt('prg_deadline_date')
        );

        $sg = $input->switchableGroup(
            [
                'opt_no_deadline' => $grp1,
                'opt_deadline_period' => $grp2,
                'opt_deadline_date' => $grp3
            ],
            ''
        );

        $deadline = "opt_no_deadline";
        if (!is_null($this->getDeadlinePeriod()) && $this->getDeadlinePeriod() > 0) {
            $deadline = 'opt_deadline_period';
        }

        if (!is_null($this->getDeadlineDate())) {
            $deadline = 'opt_deadline_date';
        }

        return $input->section(
            ['prg_deadline' => $sg->withValue($deadline)],
            $lng->txt('prg_deadline_settings')
        )
        ->withAdditionalTransformation($refinery->custom()->transformation(
            function ($vals) {
                $period = null;
                $date = null;

                if (isset($vals['prg_deadline'][1]['deadline_period'])) {
                    $period = (int) $vals['prg_deadline'][1]['deadline_period'];
                }

                if (isset($vals['prg_deadline'][1]['deadline_date'])) {
                    $date = $vals['prg_deadline'][1]['deadline_date'];
                }

                return new ilStudyProgrammeDeadlineSettings($period, $date);
            }
        ));
    }
}
