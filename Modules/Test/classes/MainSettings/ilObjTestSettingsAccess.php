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
use ILIAS\UI\Component\Input\Field\Input;
use ILIAS\UI\Component\Input\Field\OptionalGroup;
use ILIAS\UI\Component\Input\Field\Group;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Refinery\Transformation as TransformationInterface;

class ilObjTestSettingsAccess extends TestSettings
{
    private const MAX_PASSWORD_LENGTH = 20;

    public function __construct(
        int $test_id,
        protected bool $start_time_enabled = false,
        protected int $start_time = 0,
        protected bool $end_time_enabled = false,
        protected int $end_time = 0,
        protected bool $password_enabled = false,
        protected ?string $password = null,
        protected bool $fixed_participants = false,
        protected bool $limited_users_enabled = false,
        protected int $limited_users_amount = 0,
        protected int $limited_users_time_gap = 0
    ) {
        parent::__construct($test_id);
    }

    public function toForm(
        \ilLanguage $lng,
        FieldFactory $f,
        Refinery $refinery,
        array $environment = null
    ): Input {
        $inputs['access_window'] = $this->getInputAccessWindow($lng, $f, $refinery, $environment);
        $inputs['test_password'] = $this->getInputPassword($lng, $f, $refinery);

        $inputs['fixed_participants_enabled'] = $f->checkbox(
            $lng->txt('participants_invitation'),
            $lng->txt('participants_invitation_description')
        )->withValue($this->getFixedParticipants());
        if ($environment['participant_data_exists']) {
            $inputs['fixed_participants_enabled'] = $inputs['fixed_participants_enabled']
                ->withDisabled(true);
        }

        $inputs['limit_simultaneous_users'] = $this->getLimitSimultaneousUsersInput(
            $lng,
            $f,
            $refinery
        );

        return $f->section($inputs, $lng->txt('tst_settings_header_execution'));
    }

    private function getInputAccessWindow(
        \ilLanguage $lng,
        FieldFactory $f,
        Refinery $refinery,
        array $environment = null
    ): Group {
        $constraint = $refinery->custom()->constraint(
            function (array $vs): bool {
                if ($vs['start_time'] === null
                    || $vs['end_time'] === null) {
                    return true;
                }
                return $vs['start_time'] < $vs['end_time'];
            },
            $lng->txt('duration_end_must_not_be_earlier_than_start')
        );

        $trafo = $refinery->custom()->transformation(
            function (array $vs): array {
                if ($vs['start_time'] === null) {
                    $vs['start_time_enabled'] = false;
                    $vs['start_time'] = 0;
                } else {
                    $vs['start_time_enabled'] = true;
                    $vs['start_time'] = $vs['start_time']->getTimestamp();
                }

                if ($vs['end_time'] === null) {
                    $vs['end_time_enabled'] = false;
                    $vs['end_time'] = 0;
                    return $vs;
                }

                $vs['end_time_enabled'] = true;
                $vs['end_time'] = $vs['end_time']->getTimestamp();
                return $vs;
            }
        );

        return $f->group($this->getSubInputsAccessWindow($lng, $f, $environment))
            ->withAdditionalTransformation($constraint)
            ->withAdditionalTransformation($trafo);
    }

    private function getSubInputsAccessWindow(
        \ilLanguage $lng,
        FieldFactory $f,
        array $environment
    ): array {
        $sub_inputs_access_window['start_time'] = $f->dateTime(
            $lng->txt('tst_starting_time'),
            $lng->txt('tst_starting_time_desc')
        )->withTimezone($environment['user_time_zone'])
            ->withFormat($environment['user_date_format']);
        if ($this->getStartTime() !== null
            && $this->getStartTime() !== 0) {
            $sub_inputs_access_window['start_time'] = $sub_inputs_access_window['start_time']
                ->withValue(DateTimeImmutable::createFromFormat('U', (string) $this->getStartTime()));
        }
        if ($environment['participant_data_exists']) {
            $sub_inputs_access_window['start_time'] = $sub_inputs_access_window['start_time']->withDisabled(true);
        }

        $sub_inputs_access_window['end_time'] = $f->dateTime(
            $lng->txt('tst_ending_time'),
            $lng->txt('tst_ending_time_desc')
        )->withTimezone($environment['user_time_zone'])
            ->withFormat($environment['user_date_format']);
        if ($this->getEndTime() !== null
            && $this->getEndTime() !== 0) {
            $sub_inputs_access_window['end_time'] = $sub_inputs_access_window['end_time']
                ->withValue(DateTimeImmutable::createFromFormat('U', (string) $this->getEndTime()));
        }

        return $sub_inputs_access_window;
    }

    private function getInputPassword(
        \ilLanguage $lng,
        FieldFactory $f,
        Refinery $refinery
    ): OptionalGroup {
        $trafo = $refinery->custom()->transformation(
            function (?array $vs): array {
                if ($vs === null) {
                    return [
                        'password_enabled' => false,
                        'password_value' => null
                    ];
                }

                return $vs;
            }
        );

        $sub_inputs_password['test_password_value'] = $f->text($lng->txt('tst_password_enter'))
            ->withRequired(true)->withMaxLength(self::MAX_PASSWORD_LENGTH);

        $password_input = $f->optionalGroup(
            $sub_inputs_password,
            $lng->txt('tst_password'),
            $lng->txt('tst_password_details')
        )->withValue(null)
            ->withAdditionalTransformation($trafo);

        if (!$this->getPasswodEnabled()) {
            return $password_input;
        }

        return $password_input->withValue(
            ['test_password_value' => $this->getPassword()]
        );
    }

    private function getLimitSimultaneousUsersInput(
        \ilLanguage $lng,
        FieldFactory $f,
        Refinery $refinery
    ): OptionalGroup {
        $limit_simultaneous_users = $f->optionalGroup(
            $this->getSubInputsSimultaneousLogins($lng, $f, $refinery),
            $lng->txt('tst_allowed_users'),
            $lng->txt('tst_allowed_users_desc')
        )->withValue(null)
            ->withAdditionalTransformation($this->getTrafoSimultaneousLogins($refinery));

        if (!$this->getLimitedUsersEnabled()) {
            return $limit_simultaneous_users;
        }

        return $limit_simultaneous_users->withValue(
            [
                'max_allowed_simultaneous_users' => $this->getLimitedUsersAmount(),
                'allowed_simultaneous_users_time_gap' => $this->getLimitedUsersTimeGap()
            ]
        );

    }

    private function getSubInputsSimultaneousLogins(
        \ilLanguage $lng,
        FieldFactory $f,
        Refinery $refinery
    ): array {
        $sub_inputs_simultaneous['max_allowed_simultaneous_users'] = $f->numeric(
            $lng->txt('tst_allowed_users_max')
        )->withRequired(true)
            ->withAdditionalTransformation($refinery->int()->isGreaterThan(0));

        $sub_inputs_simultaneous['allowed_simultaneous_users_time_gap'] = $f->numeric(
            $lng->txt('tst_allowed_users_time_gap'),
            $lng->txt('tst_allowed_users_time_gap_desc')
        )->withAdditionalTransformation($refinery->int()->isGreaterThanOrEqual(0));

        return $sub_inputs_simultaneous;
    }

    private function getTrafoSimultaneousLogins(
        Refinery $refinery
    ): TransformationInterface {
        return $refinery->custom()->transformation(
            function (?array $vs): array {
                if ($vs === null) {
                    return [
                        'limit_simultaneous_users' => false,
                        'max_allowed_simultaneous_users' => 0,
                        'allowed_simultaneous_users_time_gap' => 0
                    ];
                }

                return [
                        'limit_simultaneous_users' => true,
                        'max_allowed_simultaneous_users' => $vs['max_allowed_simultaneous_users'],
                        'allowed_simultaneous_users_time_gap' => $vs['allowed_simultaneous_users_time_gap'] ?? 0
                    ];
            }
        );
    }

    public function toStorage(): array
    {
        return [
            'starting_time_enabled' => ['integer', (int) $this->getStartTimeEnabled()],
            'starting_time' => ['integer', $this->getStartTime()],
            'ending_time_enabled' => ['integer', (int) $this->getEndTimeEnabled()],
            'ending_time' => ['integer', $this->getEndTime()],
            'password_enabled' => ['integer', (int) $this->getPasswodEnabled()],
            'password' => ['text', $this->getPassword()],
            'fixed_participants' => ['integer', (int) $this->getFixedParticipants()],
            'limit_users_enabled' => ['integer', (int) $this->getLimitedUsersEnabled()],
            'allowedusers' => ['integer', $this->getLimitedUsersAmount()],
            'alloweduserstimegap' => ['integer', $this->getLimitedUsersTimeGap()]
        ];
    }

    public function getStartTimeEnabled(): bool
    {
        return $this->start_time_enabled;
    }
    public function withStartTimeEnabled(bool $start_time_enabled): self
    {
        $clone = clone $this;
        $clone->start_time_enabled = $start_time_enabled;
        return $clone;
    }

    public function getStartTime(): int
    {
        return $this->start_time;
    }
    public function withStartTime(int $start_time): self
    {
        $clone = clone $this;
        $clone->start_time = $start_time;
        return $clone;
    }

    public function getEndTimeEnabled(): bool
    {
        return $this->end_time_enabled;
    }
    public function withEndTimeEnabled(bool $end_time_enabled): self
    {
        $clone = clone $this;
        $clone->end_time_enabled = $end_time_enabled;
        return $clone;
    }

    public function getEndTime(): int
    {
        return $this->end_time;
    }
    public function withEndTime(int $end_time): self
    {
        $clone = clone $this;
        $clone->end_time = $end_time;
        return $clone;
    }

    public function getPasswodEnabled(): bool
    {
        return $this->password_enabled;
    }
    public function withPasswordEnabled(bool $password_enabled): self
    {
        $clone = clone $this;
        $clone->password_enabled = $password_enabled;
        return $clone;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }
    public function withPassword(?string $password): self
    {
        $clone = clone $this;
        $clone->password = $password;
        return $clone;
    }

    public function getFixedParticipants(): bool
    {
        return $this->fixed_participants;
    }
    public function withFixedParticipants(bool $fixed_participants): self
    {
        $clone = clone $this;
        $clone->fixed_participants = $fixed_participants;
        return $clone;
    }

    public function getLimitedUsersEnabled(): bool
    {
        return $this->limited_users_enabled;
    }
    public function withLimitedUsersEnabled(bool $limited_users_enabled): self
    {
        $clone = clone $this;
        $clone->limited_users_enabled = $limited_users_enabled;
        return $clone;
    }

    public function getLimitedUsersAmount(): int
    {
        return $this->limited_users_amount;
    }
    public function withLimitedUsersAmount(int $limited_users_amount): self
    {
        $clone = clone $this;
        $clone->start_time = $limited_users_amount;
        return $clone;
    }

    public function getLimitedUsersTimeGap(): int
    {
        return $this->limited_users_time_gap;
    }
    public function withLimitedUsersTimeGap(int $limited_users_time_gap): self
    {
        $clone = clone $this;
        $clone->limited_users_time_gap = $limited_users_time_gap;
        return $clone;
    }
}
