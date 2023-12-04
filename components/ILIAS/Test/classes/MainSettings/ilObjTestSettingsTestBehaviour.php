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

class ilObjTestSettingsTestBehaviour extends TestSettings
{
    private const DEFAULT_PROCESSING_TIME_MINUTES = 90;

    public function __construct(
        int $test_id,
        protected int $number_of_tries = 0,
        protected bool $block_after_passed_enabled = false,
        protected ?string $pass_waiting = null,
        protected bool $processing_time_enabled = false,
        protected ?string $processing_time = null,
        protected bool $reset_processing_time = false,
        protected int $kiosk_mode = 0,
        protected bool $examid_in_test_pass_enabled = false
    ) {
        $this->pass_waiting = $this->cleanupPassWaiting($this->pass_waiting);
        parent::__construct($test_id);
    }

    public function toForm(
        \ilLanguage $lng,
        FieldFactory $f,
        Refinery $refinery,
        array $environment = null
    ): FormInput {
        $inputs['limit_attempts'] = $this->getInputLimitAttempts(
            $lng,
            $f,
            $refinery,
            $environment
        );
        $inputs['force_waiting_between_attempts'] = $this->getInputForceWaitingBetweenAttempts(
            $lng,
            $f,
            $refinery,
            $environment
        );
        $inputs['time_limit_for_completion'] = $this->getInputTimeLimitForCompletion(
            $lng,
            $f,
            $refinery,
            $environment
        );
        $inputs['kiosk_mode'] = $this->getInputKioskMode($lng, $f, $refinery);

        $inputs['show_exam_id'] = $f->checkbox(
            $lng->txt('examid_in_test_pass'),
            $lng->txt('examid_in_test_pass_desc')
        )->withValue($this->getExamIdInTestPassEnabled());

        return $f->section($inputs, $lng->txt('tst_settings_header_test_run'));
    }

    private function getInputLimitAttempts(
        \ilLanguage $lng,
        FieldFactory $f,
        Refinery $refinery,
        array $environment
    ): FormInput {
        $trafo = $refinery->custom()->transformation(
            static function (?array $vs): array {
                if ($vs === null) {
                    return [
                        'number_of_available_attempts' => 0,
                        'block_after_passed' => false
                    ];
                }

                return $vs;
            }
        );

        $sub_inputs['number_of_available_attempts'] = $f->numeric($lng->txt('tst_nr_of_tries'));
        $sub_inputs['block_after_passed'] = $f->checkbox(
            $lng->txt('tst_block_passes_after_passed'),
            $lng->txt('tst_block_passes_after_passed_info')
        );

        if (!$environment['participant_data_exists']) {
            $sub_inputs['number_of_available_attempts'] =
                $sub_inputs['number_of_available_attempts']->withRequired(true)
                    ->withAdditionalTransformation($refinery->int()->isGreaterThan(0));
        }

        $limit_attempts = $f->optionalGroup(
            $sub_inputs,
            $lng->txt('tst_limit_nr_of_tries'),
            $lng->txt('tst_nr_of_tries_desc')
        )->withValue(null)
            ->withAdditionalTransformation($trafo);

        if ($this->getNumberOfTries() > 0) {
            $limit_attempts = $limit_attempts->withValue(
                [
                    'number_of_available_attempts' => $this->getNumberOfTries(),
                    'block_after_passed' => $this->getBlockAfterPassedEnabled()
                ]
            );
        }

        if (!$environment['participant_data_exists']) {
            return $limit_attempts;
        }

        return $limit_attempts->withDisabled(true);
    }

    private function getInputForceWaitingBetweenAttempts(
        \ilLanguage $lng,
        FieldFactory $f,
        Refinery $refinery,
        array $environment
    ): FormInput {
        $constraint = $refinery->custom()->constraint(
            static function (?string $vs): bool {
                return $vs !== '0:0:0';
            },
            sprintf($lng->txt('not_greater_than'), $lng->txt('tst_pass_waiting_time'), 0)
        );

        $trafo = $refinery->custom()->transformation(
            static function (?array $vs): ?string {
                return $vs === null ? null : implode(':', $vs);
            }
        );

        $force_waiting_between_attempts = $f->optionalGroup(
            $this->getSubInputsForceWaitingBetweenAttempts($lng, $f, $refinery, ),
            $lng->txt('tst_pass_waiting_enabled'),
            $lng->txt('tst_pass_waiting_info')
        )->withValue(null)
            ->withAdditionalTransformation($trafo);

        if ($this->getPassWaitingEnabled()) {
            list($days, $hours, $minutes) = explode(':', $this->getPassWaiting());
            $force_waiting_between_attempts = $force_waiting_between_attempts->withValue(
                [
                    'days' => $days,
                    'hours' => $hours,
                    'minutes' => $minutes
                ]
            );
        }

        if (!$environment['participant_data_exists']) {
            return $force_waiting_between_attempts->withAdditionalTransformation($constraint);
        }

        return $force_waiting_between_attempts->withDisabled(true);
    }

    private function getSubInputsForceWaitingBetweenAttempts(
        \ilLanguage $lng,
        FieldFactory $f,
        Refinery $refinery
    ): array {
        $sub_inputs_force_waiting_between_attempts['days'] = $f->numeric($lng->txt('days'))
            ->withAdditionalTransformation($refinery->int()->isGreaterThanOrEqual(0))
            ->withAdditionalTransformation($refinery->int()->isLessThanOrEqual(31))
            ->withRequired(true)
            ->withValue(0);
        $sub_inputs_force_waiting_between_attempts['hours'] = $f->numeric($lng->txt('hours'))
            ->withAdditionalTransformation($refinery->int()->isGreaterThanOrEqual(0))
            ->withAdditionalTransformation($refinery->int()->isLessThanOrEqual(24))
            ->withRequired(true)
            ->withValue(0);
        $sub_inputs_force_waiting_between_attempts['minutes'] = $f->numeric($lng->txt('minutes'))
            ->withAdditionalTransformation($refinery->int()->isGreaterThanOrEqual(0))
            ->withAdditionalTransformation($refinery->int()->isLessThanOrEqual(60))
            ->withRequired(true)
            ->withValue(0);

        return $sub_inputs_force_waiting_between_attempts;
    }

    private function cleanupPassWaiting(?string $pass_waiting): ?string
    {
        if ($pass_waiting === null) {
            return null;
        }

        $pass_waiting_array = explode(':', $pass_waiting);
        if (count($pass_waiting_array) !== 4) {
            return $pass_waiting;
        }

        $month = array_shift($pass_waiting_array);
        $pass_waiting_array[0] += $month * 31;
        return implode(':', $pass_waiting_array);
    }

    private function getInputTimeLimitForCompletion(
        \ilLanguage $lng,
        FieldFactory $f,
        Refinery $refinery,
        array $environment
    ): FormInput {
        $trafo = $refinery->custom()->transformation(
            static function (?array $vs): array {
                if ($vs === null) {
                    return [
                        'processing_time_limit' => false,
                        'time_limit_for_completion_value' => null,
                        'reset_time_limit_for_completion_by_attempt' => false
                    ];
                }

                $vs['processing_time_limit'] = true;
                $vs['time_limit_for_completion_value'] = sprintf(
                    '%02d:%02d:00',
                    floor(
                        $vs['time_limit_for_completion_value'] / 60
                    ),
                    $vs['time_limit_for_completion_value'] % 60
                );
                return $vs;
            }
        );

        $sub_inputs_time_limit_for_completion['time_limit_for_completion_value'] = $f
            ->numeric(
                $lng->txt('tst_processing_time_duration'),
                $lng->txt('tst_processing_time_desc')
            )
            ->withRequired(true)
            ->withAdditionalTransformation($refinery->int()->isGreaterThan(0))
            ->withValue(self::DEFAULT_PROCESSING_TIME_MINUTES);
        $sub_inputs_time_limit_for_completion['reset_time_limit_for_completion_by_attempt'] = $f->checkbox(
            $lng->txt('tst_reset_processing_time'),
            $lng->txt('tst_reset_processing_time_desc')
        );

        $time_limit_for_completion = $f->optionalGroup(
            $sub_inputs_time_limit_for_completion,
            $lng->txt('tst_processing_time'),
            $lng->txt('tst_processing_time_desc')
        )->withValue(null)
            ->withAdditionalTransformation($trafo);

        if ($this->getProcessingTimeEnabled()) {
            $time_limit_for_completion = $time_limit_for_completion->withValue(
                [
                    'time_limit_for_completion_value' => (int) $this->getProcessingTimeAsMinutes(),
                    'reset_time_limit_for_completion_by_attempt' => (bool) $this->getResetProcessingTime()
                ]
            );
        }

        if (!$environment['participant_data_exists']) {
            return $time_limit_for_completion;
        }

        return $time_limit_for_completion->withDisabled(true);
    }

    private function getInputKioskMode(
        \ilLanguage $lng,
        FieldFactory $f,
        Refinery $refinery
    ): FormInput {
        $trafo = $refinery->custom()->transformation(
            static function (?array $vs): ?int {
                if ($vs === null) {
                    return 0;
                }

                $kiosk_mode = 1;

                if ($vs['show_title'] === true) {
                    $kiosk_mode += 2;
                }

                if ($vs['show_participant_name'] === true) {
                    $kiosk_mode += 4;
                }

                return $kiosk_mode;
            }
        );

        $sub_inputs_kiosk_mode['show_title'] = $f->checkbox($lng->txt('kiosk_show_title'));

        $sub_inputs_kiosk_mode['show_participant_name'] = $f->checkbox($lng->txt('kiosk_show_participant'));

        $kiosk_mode = $f->optionalGroup(
            $sub_inputs_kiosk_mode,
            $lng->txt('kiosk'),
            $lng->txt('kiosk_description')
        )->withValue(null)
            ->withAdditionalTransformation($trafo);

        if (!$this->getKioskMode()) {
            return $kiosk_mode;
        }

        return $kiosk_mode->withValue([
            'show_title' => $this->getShowTitleInKioskMode(),
            'show_participant_name' => $this->getShowParticipantNameInKioskMode()
        ]);
    }

    public function toStorage(): array
    {
        return [
            'nr_of_tries' => ['integer', $this->getNumberOfTries()],
            'block_after_passed' => ['integer', (int) $this->getBlockAfterPassedEnabled()],
            'pass_waiting' => ['string', $this->getPassWaiting()],
            'enable_processing_time' => ['integer', (int) $this->getProcessingTimeEnabled()],
            'processing_time' => ['string', $this->getProcessingTime()],
            'reset_processing_time' => ['integer', (int) $this->getResetProcessingTime()],
            'kiosk' => ['integer', $this->getKioskMode()],
            'examid_in_test_pass' => ['integer', (int) $this->getExamIdInTestPassEnabled()]
        ];
    }

    public function getNumberOfTries(): int
    {
        return $this->number_of_tries;
    }

    public function withNumberOfTries(int $number_of_tries): self
    {
        $clone = clone $this;
        $clone->number_of_tries = $number_of_tries;
        return $clone;
    }

    public function getBlockAfterPassedEnabled(): bool
    {
        return $this->block_after_passed_enabled;
    }

    public function withBlockAfterPassedEnabled(bool $block_after_passed_enabled): self
    {
        $clone = clone $this;
        $clone->block_after_passed_enabled = $block_after_passed_enabled;
        return $clone;
    }

    public function getPassWaiting(): ?string
    {
        return $this->pass_waiting;
    }

    public function withPassWaiting(?string $pass_waiting): self
    {
        $clone = clone $this;
        $clone->pass_waiting = $this->cleanupPassWaiting($pass_waiting);
        return $clone;
    }

    public function getPassWaitingEnabled(): bool
    {
        if ($this->pass_waiting === null) {
            return false;
        }
        if (array_sum(explode(':', $this->pass_waiting)) > 0) {
            return true;
        }
        return false;
    }

    public function getProcessingTimeEnabled(): bool
    {
        return $this->processing_time_enabled;
    }

    public function withProcessingTimeEnabled(bool $processing_time_enabled): self
    {
        $clone = clone $this;
        $clone->processing_time_enabled = $processing_time_enabled;
        return $clone;
    }

    public function getProcessingTime(): ?string
    {
        return $this->processing_time;
    }

    public function withProcessingTime(?string $processing_time): self
    {
        $clone = clone $this;
        $clone->processing_time = $processing_time;
        return $clone;
    }

    public function getProcessingTimeAsMinutes(): int
    {
        if ($this->processing_time !== null && preg_match("/(\d{2}):(\d{2}):(\d{2})/is", $this->processing_time, $matches)) {
            return ((int) $matches[1] * 60) + (int) $matches[2];
        }

        return self::DEFAULT_PROCESSING_TIME_MINUTES;
    }

    public function getResetProcessingTime(): bool
    {
        return $this->reset_processing_time;
    }

    public function withResetProcessingTime(bool $reset_processing_time): self
    {
        $clone = clone $this;
        $clone->reset_processing_time = $reset_processing_time;
        return $clone;
    }

    public function getKioskMode(): int
    {
        return $this->kiosk_mode;
    }

    public function withKioskMode(int $kiosk_mode): self
    {
        $clone = clone $this;
        $clone->kiosk_mode = $kiosk_mode;
        return $clone;
    }

    public function getKioskModeEnabled(): bool
    {
        return ($this->kiosk_mode & 1) > 0;
    }

    public function getShowTitleInKioskMode(): bool
    {
        return ($this->kiosk_mode & 2) > 0;
    }

    public function getShowParticipantNameInKioskMode(): bool
    {
        return ($this->kiosk_mode & 4) > 0;
    }

    public function getExamIdInTestPassEnabled(): bool
    {
        return $this->examid_in_test_pass_enabled;
    }

    public function withExamIdInTestPassEnabled(bool $exam_id_in_test_pass_enabled): self
    {
        $clone = clone $this;
        $clone->examid_in_test_pass_enabled = $exam_id_in_test_pass_enabled;
        return $clone;
    }
}
