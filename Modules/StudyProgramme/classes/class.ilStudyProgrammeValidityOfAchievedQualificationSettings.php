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

use ILIAS\Data\Factory;
use ILIAS\UI\Component\Input\Field;
use ILIAS\Refinery\Factory as Refinery;

class ilStudyProgrammeValidityOfAchievedQualificationSettings
{
    protected ?int $qualification_period;
    protected ?DateTime $qualification_date;
    protected ?int $restart_period;

    public function __construct(
        ?int $qualification_period,
        ?DateTime $qualification_date,
        ?int $restart_period
    ) {
        if (!is_null($qualification_period) && 0 > $qualification_period) {
            throw new InvalidArgumentException(
                'Numbers less than 0 are not allowed for qualification_period'
            );
        }

        if (!is_null($restart_period) && 0 > $restart_period) {
            throw new InvalidArgumentException(
                'Numbers less than 0 are not allowed for restart_period'
            );
        }

        $this->qualification_period = $qualification_period;
        $this->qualification_date = $qualification_date;
        $this->restart_period = $restart_period;
    }

    public function getQualificationPeriod() : ?int
    {
        return $this->qualification_period;
    }

    public function withQualificationPeriod(
        ?int $qualification_period
    ) : ilStudyProgrammeValidityOfAchievedQualificationSettings {
        if (!is_null($qualification_period) && 0 > $qualification_period) {
            throw new InvalidArgumentException(
                'Numbers less than 0 are not allowed'
            );
        }
        $clone = clone $this;
        $clone->qualification_period = $qualification_period;

        return $clone;
    }

    public function getQualificationDate() : ?DateTime
    {
        return $this->qualification_date;
    }

    public function withQualificationDate(
        ?DateTime $qualification_date
    ) : ilStudyProgrammeValidityOfAchievedQualificationSettings {
        $clone = clone $this;
        $clone->qualification_date = $qualification_date;

        return $clone;
    }

    public function getRestartPeriod() : ?int
    {
        return $this->restart_period;
    }

    public function withRestartPeriod(
        ?int $restart_period
    ) : ilStudyProgrammeValidityOfAchievedQualificationSettings {
        if (!is_null($restart_period) && 0 > $restart_period) {
            throw new InvalidArgumentException(
                'Numbers less than 0 are not allowed'
            );
        }
        $clone = clone $this;
        $clone->restart_period = $restart_period;

        return $clone;
    }

    public function toFormInput(
        Field\Factory $input,
        ilLanguage $lng,
        Refinery $refinery,
        Factory $data_factory
    ) : Field\Input {
        $format = $data_factory->dateFormat()->germanShort();
        $grp1 = $input->group([], $lng->txt('prg_no_validity_qualification'));
        $grp2 = $input->group(
            [
                'vq_period' => $input->numeric(
                    '',
                    $lng->txt('validity_qualification_period_desc')
                )
                ->withAdditionalTransformation($refinery->int()->isGreaterThanOrEqual(1))
                ->withValue($this->getQualificationPeriod())
            ],
            $lng->txt('validity_qualification_period')
        );
        $grp3 = $input->group(
            [
                'vq_date' => $input->dateTime(
                    '',
                    $lng->txt('validity_qualification_date_desc')
                )
                ->withFormat($format)
                ->withValue($this->getQualificationDate() !== null ? $this->getQualificationDate()->format('d.m.Y') : '')
                ->withRequired(true)
            ],
            $lng->txt('validity_qualification_date')
        );
        $grp4 = $input->group([], $lng->txt('prg_no_restart'));
        $grp5 = $input->group(
            [
                'vq_restart_period' => $input->numeric(
                    '',
                    $lng->txt('restart_period_desc')
                )
                ->withAdditionalTransformation($refinery->int()->isGreaterThanOrEqual(1))
                ->withValue($this->getRestartPeriod())
            ],
            $lng->txt('restart_period')
        );

        $sg1 = $input->switchableGroup(
            [
                'opt_no_validity_qualification' => $grp1,
                'opt_validity_qualification_period' => $grp2,
                'opt_validity_qualification_date' => $grp3
            ],
            ''
        );

        $sg2 = $input->switchableGroup(
            [
                'opt_no_restart' => $grp4,
                'opt_restart_period' => $grp5,
            ],
            ''
        );

        $validity_qualification = "opt_no_validity_qualification";
        if (!is_null($this->getQualificationPeriod()) && $this->getQualificationPeriod() > 0) {
            $validity_qualification = 'opt_validity_qualification_period';
        }

        if (!is_null($this->getQualificationDate())) {
            $validity_qualification = 'opt_validity_qualification_date';
        }

        $restart_value = 'opt_no_restart';
        if (!is_null($this->getRestartPeriod()) && $this->getRestartPeriod() > 0) {
            $restart_value = 'opt_restart_period';
        }

        return $input->section(
            [
                'validity_qualification' => $sg1->withValue($validity_qualification),
                'restart' => $sg2->withValue($restart_value)
            ],
            $lng->txt('prg_validity_of_qualification')
        )
        ->withAdditionalTransformation($refinery->custom()->transformation(function (array $vals) {
            $vq_period = null;
            $vq_date = null;
            $restart = null;

            if (isset($vals['validity_qualification'][1]['vq_period'])) {
                $vq_period = (int) $vals['validity_qualification'][1]['vq_period'];
            }

            if (isset($vals['validity_qualification'][1]['vq_date'])) {
                $vq_date = new DateTime($vals['validity_qualification'][1]['vq_date']);
            }

            if (
                count($vals['restart'][1]) > 0 &&
                !is_null($vals['restart'][1]['vq_restart_period'])
            ) {
                $restart = (int) $vals['restart'][1]['vq_restart_period'];
            }

            return new ilStudyProgrammeValidityOfAchievedQualificationSettings(
                $vq_period,
                $vq_date,
                $restart
            );
        }));
    }
}
