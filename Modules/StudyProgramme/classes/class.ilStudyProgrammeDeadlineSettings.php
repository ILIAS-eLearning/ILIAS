<?php

declare(strict_types=1);

use ILIAS\Data\Factory;
use \ILIAS\UI\Component\Input\Field;
use \ILIAS\Refinery\Factory as Refinery;

class ilStudyProgrammeDeadlineSettings
{
    /**
     * @var int|null
     */
    protected $deadline_period;

    /**
     * @var DateTime|null
     */
    protected $deadline_date;

    public function __construct(
        ?int $deadline_period,
        ?DateTime $deadline_date
    ) {
        if (!is_null($deadline_period) && 0 > $deadline_period) {
            throw new InvalidArgumentException(
                'Numbers less than 0 are not allowed'
            );
        }

        $this->deadline_period = $deadline_period;
        $this->deadline_date = $deadline_date;
    }

    public function getDeadlinePeriod() : ?int
    {
        return $this->deadline_period;
    }

    public function withDeadlinePeriod(?int $deadline_period) : ilStudyProgrammeDeadlineSettings
    {
        if (!is_null($deadline_period) && 0 > $deadline_period) {
            throw new InvalidArgumentException(
                'Numbers less than 0 are not allowed'
            );
        }

        $clone = clone $this;
        $clone->deadline_period = $deadline_period;
        return $clone;
    }

    public function getDeadlineDate() : ?DateTime
    {
        return $this->deadline_date;
    }

    public function withDeadlineDate(?DateTime $deadline_date) : ilStudyProgrammeDeadlineSettings
    {
        $clone = clone $this;
        $clone->deadline_date = $deadline_date;
        return $clone;
    }

    public function toFormInput(
        Field\Factory $input,
        \ilLanguage $lng,
        Refinery $refinery,
        Factory $data_factory
    ) : Field\Input {
        $format = $data_factory->dateFormat()->germanShort();

        $grp1 = $input->group([], $lng->txt('prg_no_deadline'));
        $grp2 = $input->group(
            [
                'deadline_period' => $input->numeric(
                    '',
                    $lng->txt('prg_deadline_period_desc')
                )
                ->withAdditionalTransformation($refinery->int()->isGreaterThan(-1))
                ->withValue($this->getDeadlinePeriod() !== null ? $this->getDeadlinePeriod() : null)
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
                ->withMinValue(new DateTimeImmutable())
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
                    $date = new DateTime($vals['prg_deadline'][1]['deadline_date']);
                }

                return new ilStudyProgrammeDeadlineSettings($period, $date);
            }
        ));
    }
}
