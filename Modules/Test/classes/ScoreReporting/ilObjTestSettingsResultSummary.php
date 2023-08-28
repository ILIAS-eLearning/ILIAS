<?php

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

declare(strict_types=1);

use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;
use ILIAS\UI\Component\Input\Container\Form\FormInput;
use ILIAS\Refinery\Factory as Refinery;

class ilObjTestSettingsResultSummary extends TestSettings
{
    public const SCORE_REPORTING_DISABLED = 0;
    public const SCORE_REPORTING_FINISHED = 1;
    public const SCORE_REPORTING_IMMIDIATLY = 2;
    public const SCORE_REPORTING_DATE = 3;
    public const SCORE_REPORTING_AFTER_PASSED = 4;

    protected int $score_reporting = 0;
    protected ?\DateTimeImmutable $reporting_date = null;
    protected bool $pass_deletion_allowed = false;
    /**
     * this is derived from results_presentation with RESULTPRES_BIT_PASS_DETAILS;
     * see ilObjTestSettingsResultDetails
     */
    protected bool $show_pass_details = false;
    protected bool $show_grading_status = false;
    protected bool $show_grading_mark = false;

    public function __construct(int $test_id)
    {
        parent::__construct($test_id);
    }

    public function toForm(
        \ilLanguage $lng,
        FieldFactory $f,
        Refinery $refinery,
        array $environment = null
    ): FormInput {
        $trafo = $refinery->custom()->transformation(
            function ($v) {
                list($mode, $date) = $v;
                if (count($date) < 1) {
                    $date = null;
                } else {
                    $date = array_shift($date);
                }
                return [(int) $mode, $date];
            }
        );

        $results_time_group = $f->switchableGroup(
            [
                self::SCORE_REPORTING_IMMIDIATLY => $f->group([], $lng->txt('tst_results_access_always'), $lng->txt('tst_results_access_always_desc')),
                self::SCORE_REPORTING_FINISHED => $f->group([], $lng->txt('tst_results_access_finished'), $lng->txt('tst_results_access_finished_desc')),
                self::SCORE_REPORTING_AFTER_PASSED => $f->group([], $lng->txt('tst_results_access_passed'), $lng->txt('tst_results_access_passed_desc')),
                self::SCORE_REPORTING_DATE => $f->group(
                    [
                    $f->dateTime($lng->txt('tst_reporting_date'), "")
                        ->withTimezone($environment['user_time_zone'])
                        ->withFormat($environment['user_date_format'])
                        ->withValue(
                            $this->getReportingDate()?->setTimezone(
                                new DateTimeZone($environment['user_time_zone'])
                            )
                        )
                        ->withRequired(true)
                    ],
                    $lng->txt('tst_results_access_date'),
                    $lng->txt('tst_results_access_date_desc')
                )
            ],
            $lng->txt('tst_results_access_setting'),
            ""
        )
        ->withRequired(true)
        ->withAdditionalTransformation($trafo);

        if ($this->getScoreReporting() > 0) {
            $results_time_group = $results_time_group->withValue($this->getScoreReporting());
        }


        $optional_group = $f->optionalGroup(
            [
                'score_reporting_mode' => $results_time_group,
                'show_grading_status' => $f->checkbox(
                    $lng->txt('tst_results_grading_opt_show_status'),
                    $lng->txt('tst_results_grading_opt_show_status_desc')
                ),
                'show_grading_mark' => $f->checkbox(
                    $lng->txt('tst_results_grading_opt_show_mark'),
                    $lng->txt('tst_results_grading_opt_show_mark_desc')
                ),
                'show_pass_details' => $f->checkbox(
                    $lng->txt('tst_results_grading_opt_show_details'),
                    $lng->txt('tst_results_grading_opt_show_details_desc')
                ),
                'pass_deletion_allowed' => $f->checkbox(
                    $lng->txt('tst_pass_deletion'),
                    $lng->txt('tst_pass_deletion_allowed')
                )
            ],
            $lng->txt('tst_results_access_enabled'),
            $lng->txt('tst_results_access_enabled_desc')
        );

        if ($this->getScoreReportingEnabled()) {
            $optional_group = $optional_group->withValue(
                [
                    "score_reporting_mode" => $this->getScoreReporting(),
                    "show_grading_status" => $this->getShowGradingStatusEnabled(),
                    "show_grading_mark" => $this->getShowGradingMarkEnabled(),
                    "show_pass_details" => $this->getShowPassDetails(),
                    "pass_deletion_allowed" => $this->getPassDeletionAllowed()
                ]
            );
        } else {
            $optional_group = $optional_group->withValue(null);
        }

        $fields = ['score_reporting' => $optional_group];
        return $f->section($fields, $lng->txt('test_results'))
            ->withAdditionalTransformation(
                $refinery->custom()->transformation(
                    function ($v) {
                        $settings = clone $this;
                        $mode = 0;
                        $date = null;
                        if ($v['score_reporting']) {
                            list($mode, $date) = $v['score_reporting']['score_reporting_mode'];
                            $settings = $settings
                                ->withShowGradingStatusEnabled($v['score_reporting']['show_grading_status'])
                                ->withShowGradingMarkEnabled($v['score_reporting']['show_grading_mark'])
                                ->withShowPassDetails($v['score_reporting']['show_pass_details'])
                                ->withPassDeletionAllowed($v['score_reporting']['pass_deletion_allowed'])
                            ;
                        }
                        return $settings
                            ->withScoreReporting((int) $mode)
                            ->withReportingDate($date);
                    }
                )
            );
    }

    public function toStorage(): array
    {
        $dat = $this->getReportingDate()->setTimezone(new DateTimeZone('UTC'));
        if ($dat) {
            $dat = $dat->format(ilObjTestScoreSettingsDatabaseRepository::STORAGE_DATE_FORMAT);
        }
        return [
            'pass_deletion_allowed' => ['integer', (int) $this->getPassDeletionAllowed()],
            'score_reporting' => ['integer', $this->getScoreReporting()],
            'reporting_date' => ['text', (string) $dat],
            'show_grading_status' => ['integer', (int) $this->getShowGradingStatusEnabled()],
            'show_grading_mark' => ['integer', (int) $this->getShowGradingMarkEnabled()]
            //show_pass_details
        ];
    }


    public function getScoreReporting(): int
    {
        return $this->score_reporting;
    }
    public function withScoreReporting(int $score_reporting): self
    {
        $clone = clone $this;
        $clone->score_reporting = $score_reporting;
        return $clone;
    }

    public function getScoreReportingEnabled(): bool
    {
        return $this->score_reporting !== self::SCORE_REPORTING_DISABLED;
    }

    public function getReportingDate(): ?\DateTimeImmutable
    {
        return $this->reporting_date;
    }
    public function withReportingDate(?\DateTimeImmutable $reporting_date): self
    {
        $clone = clone $this;
        $clone->reporting_date = $reporting_date;
        return $clone;
    }

    public function getShowGradingStatusEnabled(): bool
    {
        return $this->show_grading_status;
    }
    public function withShowGradingStatusEnabled(bool $show_grading_status): self
    {
        $clone = clone $this;
        $clone->show_grading_status = $show_grading_status;
        return $clone;
    }

    public function getShowGradingMarkEnabled(): bool
    {
        return $this->show_grading_mark;
    }
    public function withShowGradingMarkEnabled(bool $show_grading_mark): self
    {
        $clone = clone $this;
        $clone->show_grading_mark = $show_grading_mark;
        return $clone;
    }

    public function getPassDeletionAllowed(): bool
    {
        return $this->pass_deletion_allowed;
    }
    public function withPassDeletionAllowed(bool $pass_deletion_allowed): self
    {
        $clone = clone $this;
        $clone->pass_deletion_allowed = $pass_deletion_allowed;
        return $clone;
    }

    public function getShowPassDetails(): bool
    {
        return $this->show_pass_details;
    }
    public function withShowPassDetails(bool $flag): self
    {
        $clone = clone $this;
        $clone->show_pass_details = $flag;
        return $clone;
    }
}
