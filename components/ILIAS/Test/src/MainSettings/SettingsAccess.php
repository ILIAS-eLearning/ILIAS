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

namespace ILIAS\Test\MainSettings;

use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;
use ILIAS\UI\Component\Input\Container\Form\FormInput;
use ILIAS\UI\Component\Input\Field\OptionalGroup;
use ILIAS\UI\Component\Input\Field\Group;
use ILIAS\Refinery\Factory as Refinery;

class SettingsAccess extends TestSettings
{
    private const MAX_PASSWORD_LENGTH = 20;

    public function __construct(
        int $test_id,
        protected bool $start_time_enabled = false,
        protected ?DateTimeImmutable $start_time = null,
        protected bool $end_time_enabled = false,
        protected ?DateTimeImmutable $end_time = null,
        protected bool $password_enabled = false,
        protected ?string $password = null,
        protected ?string $ip_range_from = null,
        protected ?string $ip_range_to = null,
        protected bool $fixed_participants = false
    ) {
        parent::__construct($test_id);
    }

    public function toForm(
        \ilLanguage $lng,
        FieldFactory $f,
        Refinery $refinery,
        array $environment = null
    ): FormInput {
        $inputs['access_window'] = $this->getInputAccessWindow($lng, $f, $refinery, $environment);
        $inputs['test_password'] = $this->getInputPassword($lng, $f, $refinery);
        $inputs['ip_range'] = $this->getInputIpRange($lng, $f, $refinery);

        $inputs['fixed_participants_enabled'] = $f->checkbox(
            $lng->txt('participants_invitation'),
            $lng->txt('participants_invitation_description')
        )->withValue($this->getFixedParticipants());
        if ($environment['participant_data_exists']) {
            $inputs['fixed_participants_enabled'] = $inputs['fixed_participants_enabled']
                ->withDisabled(true);
        }

        return $f->section($inputs, $lng->txt('tst_settings_header_execution'));
    }

    private function getInputAccessWindow(
        \ilLanguage $lng,
        FieldFactory $f,
        Refinery $refinery,
        array $environment = null
    ): Group {
        $constraint = $refinery->custom()->constraint(
            static function (array $vs): bool {
                if ($vs['start_time'] === null
                    || $vs['end_time'] === null) {
                    return true;
                }
                return $vs['start_time'] < $vs['end_time'];
            },
            $lng->txt('duration_end_must_not_be_earlier_than_start')
        );

        $trafo = $refinery->custom()->transformation(
            static function (array $vs): array {
                if ($vs['start_time'] === null) {
                    $vs['start_time_enabled'] = false;
                } else {
                    $vs['start_time_enabled'] = true;
                    $vs['start_time'] = $vs['start_time'];
                }

                if ($vs['end_time'] === null) {
                    $vs['end_time_enabled'] = false;
                    return $vs;
                }

                $vs['end_time_enabled'] = true;
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
            ->withFormat($environment['user_date_format'])
            ->withUseTime(true);
        if ($this->getStartTime() !== null) {
            $sub_inputs_access_window['start_time'] = $sub_inputs_access_window['start_time']
                ->withValue($this->getStartTime()->setTimezone(new DateTimeZone($environment['user_time_zone'])));
        }
        if ($environment['participant_data_exists']) {
            $sub_inputs_access_window['start_time'] = $sub_inputs_access_window['start_time']->withDisabled(true);
        }

        $sub_inputs_access_window['end_time'] = $f->dateTime(
            $lng->txt('tst_ending_time'),
            $lng->txt('tst_ending_time_desc')
        )->withTimezone($environment['user_time_zone'])
            ->withFormat($environment['user_date_format'])
            ->withUseTime(true);
        if ($this->getEndTime() !== null) {
            $sub_inputs_access_window['end_time'] = $sub_inputs_access_window['end_time']
                ->withValue($this->getEndTime()->setTimezone(new DateTimeZone($environment['user_time_zone'])));
        }

        return $sub_inputs_access_window;
    }

    private function getInputPassword(
        \ilLanguage $lng,
        FieldFactory $f,
        Refinery $refinery
    ): OptionalGroup {
        $trafo = $refinery->custom()->transformation(
            static function (?array $vs): array {
                if ($vs === null) {
                    return [
                        'password_enabled' => false,
                        'password_value' => null
                    ];
                }

                $vs['password_enabled'] = true;
                return $vs;
            }
        );

        $sub_inputs_password['password_value'] = $f->text($lng->txt('tst_password_enter'))
            ->withRequired(true)->withMaxLength(self::MAX_PASSWORD_LENGTH);

        $password_input = $f->optionalGroup(
            $sub_inputs_password,
            $lng->txt('tst_password'),
            $lng->txt('tst_password_details')
        )->withValue(null)
            ->withAdditionalTransformation($trafo);

        if (!$this->getPasswordEnabled()) {
            return $password_input;
        }

        return $password_input->withValue(
            ['password_value' => $this->getPassword()]
        );
    }

    private function getInputIpRange(
        \ilLanguage $lng,
        FieldFactory $f,
        Refinery $refinery
    ): FormInput {
        $validate_ip = $refinery->custom()->constraint(
            static function (?string $v): bool {
                if ($v === null) {
                    return true;
                }
                return filter_var($v, FILTER_VALIDATE_IP) !== false;
            },
            $lng->txt('invalid_ip')
        );

        $validate_order = $refinery->custom()->constraint(
            function (?array $vs): bool {
                if ($vs === null) {
                    return true;
                }
                return $this->checkIpRangeValidity(
                    $vs['ip_range_from'],
                    $vs['ip_range_to']
                );
            },
            sprintf($lng->txt('not_greater_than'), $lng->txt('max_ip_label'), $lng->txt('min_ip_label'))
        );
        $trafo = $refinery->custom()->transformation(
            static function (?array $vs): array {
                if ($vs === null) {
                    $vs = [
                        'ip_range_from' => null,
                        'ip_range_to' => null
                    ];
                }
                return $vs;
            }
        );

        $get_ip_range = $f->optionalGroup(
            [
                'ip_range_from' => $f->text($lng->txt('min_ip_label'))
                    ->withAdditionalTransformation($validate_ip),
                'ip_range_to' => $f->text($lng->txt('max_ip_label'))
                    ->withAdditionalTransformation($validate_ip)
            ],
            $lng->txt('ip_range_label'),
            $lng->txt('ip_range_info')
        )->withValue(null);

        if ($this->isIpRangeEnabled()) {
            $get_ip_range = $get_ip_range->withValue(
                [
                    'ip_range_from' => $this->getIpRangeFrom(),
                    'ip_range_to' => $this->getIpRangeTo()
                ]
            );
        }

        return $get_ip_range->withAdditionalTransformation($validate_order)
            ->withAdditionalTransformation($trafo);
    }

    private function checkIpRangeValidity(string $start, string $end): bool
    {
        if (filter_var($start, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false
           && filter_var($end, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false) {
            return ip2long($start) <= ip2long($end);
        }

        if (filter_var($start, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false
           && filter_var($end, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false) {
            return bin2hex(inet_pton($start)) <= bin2hex(inet_pton($end));
        }
        return false;
    }

    public function toStorage(): array
    {
        return [
            'starting_time_enabled' => ['integer', (int) $this->getStartTimeEnabled()],
            'starting_time' => ['integer', $this->getStartTime() !== null ? $this->getStartTime()->getTimestamp() : 0],
            'ending_time_enabled' => ['integer', (int) $this->getEndTimeEnabled()],
            'ending_time' => ['integer', $this->getEndTime() !== null ? $this->getEndTime()->getTimestamp() : 0],
            'password_enabled' => ['integer', (int) $this->getPasswordEnabled()],
            'password' => ['text', $this->getPassword()],
            'ip_range_from' => ['text', $this->getIpRangeFrom()],
            'ip_range_to' => ['text', $this->getIpRangeTo()],
            'fixed_participants' => ['integer', (int) $this->getFixedParticipants()]
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

    public function getStartTime(): ?DateTimeImmutable
    {
        return $this->start_time;
    }
    public function withStartTime(?DateTimeImmutable $start_time): self
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

    public function getEndTime(): ?DateTimeImmutable
    {
        return $this->end_time;
    }
    public function withEndTime(?DateTimeImmutable $end_time): self
    {
        $clone = clone $this;
        $clone->end_time = $end_time;
        return $clone;
    }

    public function getPasswordEnabled(): bool
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

    public function getIpRangeFrom(): ?string
    {
        return $this->ip_range_from;
    }
    public function withIpRangeFrom(?string $ip_range_from): self
    {
        $clone = clone $this;
        $clone->ip_range_from = $ip_range_from;
        return $clone;
    }

    public function getIpRangeTo(): ?string
    {
        return $this->ip_range_to;
    }
    public function withIpRangeTo(?string $ip_range_to): self
    {
        $clone = clone $this;
        $clone->ip_range_to = $ip_range_to;
        return $clone;
    }

    public function isIpRangeEnabled(): ?bool
    {
        return $this->ip_range_from !== null && $this->ip_range_to !== null;
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
}
