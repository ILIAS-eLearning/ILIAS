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

namespace ILIAS\Test\Settings\MainSettings;

use ILIAS\Test\Settings\TestSettings;
use ILIAS\Test\Logging\AdditionalInformationGenerator;
use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;
use ILIAS\UI\Component\Input\Container\Form\FormInput;
use ILIAS\UI\Component\Input\Field\OptionalGroup;
use ILIAS\Refinery\Factory as Refinery;

class SettingsFinishing extends TestSettings
{
    public function __construct(
        int $test_id,
        protected bool $show_answer_overview = false,
        protected bool $concluding_remarks_enabled = false,
        protected ?string $concluding_remarks_text = '',
        protected ?int $concluding_remarks_page_id = null,
        protected RedirectionModes $redirection_mode = RedirectionModes::NONE,
        protected ?string $redirection_url = null,
        protected int $mail_notification_content_type = 0,
        protected bool $always_send_mail_notification = false
    ) {
        parent::__construct($test_id);
    }

    public function toForm(
        \ilLanguage $lng,
        FieldFactory $f,
        Refinery $refinery,
        ?array $environment = null
    ): FormInput {
        $inputs['show_answer_overview'] = $f->checkbox(
            $lng->txt('enable_examview'),
            $lng->txt('enable_examview_desc')
        )->withValue($this->getShowAnswerOverview());

        $inputs['show_concluding_remarks'] = $f->checkbox(
            $lng->txt('final_statement'),
            $lng->txt('final_statement_show_desc')
        )->withValue((bool) $this->getConcludingRemarksEnabled());

        $inputs['redirect_after_finish'] = $this->getRedirectionInputs($lng, $f, $refinery);

        $inputs['finish_notification'] = $this->getMailNotificationInputs($lng, $f, $refinery);

        return $f->section($inputs, $lng->txt('tst_final_information'));
    }

    private function getRedirectionInputs(
        \ilLanguage $lng,
        FieldFactory $f,
        Refinery $refinery
    ): OptionalGroup {
        $redirection_trafo = $refinery->custom()->transformation(
            static function (?array $v): array {
                if ($v === null) {
                    return [
                        'redirect_mode' => RedirectionModes::NONE,
                        'redirect_url' => ''
                    ];
                }

                return [
                    'redirect_mode' => RedirectionModes::tryFrom($v['redirect_mode'])
                        ?? RedirectionModes::NONE,
                    'redirect_url' => $v['redirect_url']
                ];
            }
        );

        $sub_inputs_redirect = [
            'redirect_mode' => $f
                ->radio($lng->txt('redirect_after_finishing_rule'))
                ->withOption(
                    (string) RedirectionModes::ALWAYS->value,
                    $lng->txt('redirect_always')
                )->withOption(
                    (string) RedirectionModes::ALWAYS_TO_LOGOUT->value,
                    $lng->txt('redirect_always_to_logout')
                )->withOption(
                    (string) RedirectionModes::IF_KIOSK_ACTIVATED->value,
                    $lng->txt('redirect_in_kiosk_mode')
                )->withRequired(true)
                ->withAdditionalTransformation($refinery->kindlyTo()->int()),
            'redirect_url' => $f
                ->text($lng->txt('redirection_url'))
                ->withAdditionalTransformation($refinery->string()->hasMaxLength(4000))
                ->withAdditionalTransformation(
                    $refinery->custom()->constraint(
                        static function ($v) use ($refinery): bool {
                            try {
                                return $v === '' || $refinery->to()->data('uri')->transform($v);
                            } catch (Throwable) {
                                return false;
                            }
                        },
                        $lng->txt('redirect_url_invalid')
                    )
                )
        ];

        $redirection_input = $f
            ->optionalGroup(
                $sub_inputs_redirect,
                $lng->txt('redirect_after_finishing_tst'),
                $lng->txt('redirect_after_finishing_tst_desc')
            )
            ->withValue(null)
            ->withAdditionalTransformation($redirection_trafo)
            ->withAdditionalTransformation(
                $refinery->custom()->constraint(
                    static function (array $v): bool {
                        return in_array(
                            $v['redirect_mode'],
                            [RedirectionModes::NONE, RedirectionModes::ALWAYS_TO_LOGOUT],
                            true
                        ) || $v['redirect_url'] !== '';
                    },
                    static function (\Closure $txt, array $value): string {
                        return sprintf(
                            $txt('redirect_url_required_for_rule'),
                            $value['redirect_mode'] === RedirectionModes::ALWAYS
                                ? $txt('redirect_always')
                                : $txt('redirect_in_kiosk_mode')
                        );
                    }
                )
            );

        if ($this->getRedirectionMode() === RedirectionModes::NONE) {
            return $redirection_input;
        }

        return $redirection_input->withValue(
            [
                'redirect_mode' => $this->getRedirectionMode()->value,
                'redirect_url' => $this->getRedirectionUrl()
            ]
        );
    }

    private function getMailNotificationInputs(
        \ilLanguage $lng,
        FieldFactory $f,
        Refinery $refinery
    ): OptionalGroup {
        $notification_trafo = $refinery->custom()->transformation(
            static function (?array $v): array {
                if ($v === null) {
                    return [
                        'notification_content_type' => 0,
                        'always_notify' => false
                    ];
                }

                return $v;
            }
        );

        $sub_inputs_finish_notification = [
            'notification_content_type' => $f->radio(
                $lng->txt('tst_finish_notification_content_type')
            )->withOption(
                '1',
                $lng->txt('tst_finish_notification_simple')
            )->withOption(
                '2',
                $lng->txt('tst_finish_notification_advanced')
            )->withRequired(true)
            ->withValue('1')
            ->withAdditionalTransformation($refinery->kindlyTo()->int()),
            'always_notify' => $f->checkbox(
                $lng->txt('mailnottype'),
                $lng->txt('mailnottype_desc')
            )
        ];

        $mail_notification_inputs = $f->optionalGroup(
            $sub_inputs_finish_notification,
            $lng->txt('tst_finish_notification'),
            $lng->txt('tst_finish_notification_desc')
        )->withValue(null)
            ->withAdditionalTransformation($notification_trafo);

        if ($this->getMailNotificationContentType() === 0) {
            return $mail_notification_inputs;
        }

        return $mail_notification_inputs->withValue(
            [
                'notification_content_type' => (string) $this->getMailNotificationContentType(),
                'always_notify' => (bool) $this->getAlwaysSendMailNotification()
            ]
        );
    }

    public function toStorage(): array
    {
        return [
            'enable_examview' => ['integer', (int) $this->getShowAnswerOverview()],
            'showfinalstatement' => ['integer', (int) $this->getConcludingRemarksEnabled()],
            'finalstatement' => ['text', $this->getConcludingRemarksText()],
            'concluding_remarks_page_id' => ['integer', $this->getConcludingRemarksPageId()],
            'redirection_mode' => ['integer', $this->getRedirectionMode()->value],
            'redirection_url' => ['text', $this->getRedirectionUrl()],
            'mailnotification' => ['integer', $this->getMailNotificationContentType()],
            'mailnottype' => ['integer', (int) $this->getAlwaysSendMailNotification()]
        ];
    }

    public function toLog(AdditionalInformationGenerator $additional_info): array
    {
        $log_array = [
            AdditionalInformationGenerator::KEY_TEST_ANSWER_OVERVIEW_ENABLED => $additional_info
                ->getEnabledDisabledTagForBool($this->getShowAnswerOverview()),
            AdditionalInformationGenerator::KEY_TEST_CONCLUDING_REMARKS_ENABLED => $additional_info
                ->getEnabledDisabledTagForBool($this->getConcludingRemarksEnabled()),
            AdditionalInformationGenerator::KEY_TEST_REDIRECT_URL => $this->getRedirectionUrl(),

        ];

        switch ($this->getRedirectionMode()) {
            case RedirectionModes::NONE:
                $log_array[AdditionalInformationGenerator::KEY_TEST_REDIRECT_MODE] = $additional_info
                    ->getNoneTag();
                break;
            case RedirectionModes::ALWAYS:
                $log_array[AdditionalInformationGenerator::KEY_TEST_REDIRECT_MODE] = $additional_info
                    ->getTagForLangVar('redirect_always');
                break;
            case RedirectionModes::IF_KIOSK_ACTIVATED:
                $log_array[AdditionalInformationGenerator::KEY_TEST_REDIRECT_MODE] = $additional_info
                    ->getTagForLangVar('redirect_in_kiosk_mode');
                break;
        }

        switch ($this->getMailNotificationContentType()) {
            case 0:
                $log_array[AdditionalInformationGenerator::KEY_TEST_MAIL_NOTIFICATION_CONTENT_TYPE] = $additional_info
                    ->getNoneTag();
                break;
            case 1:
                $log_array[AdditionalInformationGenerator::KEY_TEST_MAIL_NOTIFICATION_CONTENT_TYPE] = $additional_info
                    ->getTagForLangVar('tst_finish_notification_simple');
                $log_array[AdditionalInformationGenerator::KEY_TEST_ALWAYS_SEND_NOTIFICATION] = $additional_info
                    ->getEnabledDisabledTagForBool($this->getAlwaysSendMailNotification());
                break;
            case 2:
                $log_array[AdditionalInformationGenerator::KEY_TEST_MAIL_NOTIFICATION_CONTENT_TYPE] = $additional_info
                    ->getTagForLangVar('tst_finish_notification_advanced');
                $log_array[AdditionalInformationGenerator::KEY_TEST_ALWAYS_SEND_NOTIFICATION] = $additional_info
                    ->getEnabledDisabledTagForBool($this->getAlwaysSendMailNotification());
                break;
        }

        return $log_array;
    }

    public function getShowAnswerOverview(): bool
    {
        return $this->show_answer_overview;
    }

    public function withShowAnswerOverview(bool $show_answer_overview): self
    {
        $clone = clone $this;
        $clone->show_answer_overview = $show_answer_overview;
        return $clone;
    }

    public function getConcludingRemarksEnabled(): bool
    {
        return $this->concluding_remarks_enabled;
    }

    public function withConcludingRemarksEnabled(bool $concluding_remarks_enabled): self
    {
        $clone = clone $this;
        $clone->concluding_remarks_enabled = $concluding_remarks_enabled;
        return $clone;
    }

    public function getConcludingRemarksText(): string
    {
        return $this->concluding_remarks_text ?? '';
    }

    public function getConcludingRemarksPageId(): ?int
    {
        return $this->concluding_remarks_page_id;
    }

    public function withConcludingRemarksPageId(?int $concluding_remarks_page_id): self
    {
        $clone = clone $this;
        $clone->concluding_remarks_page_id = $concluding_remarks_page_id;
        return $clone;
    }

    public function getRedirectionMode(): RedirectionModes
    {
        return $this->redirection_mode;
    }

    public function withRedirectionMode(RedirectionModes $redirection_mode): self
    {
        $clone = clone $this;
        $clone->redirection_mode = $redirection_mode;
        return $clone;
    }

    public function getRedirectionUrl(): ?string
    {
        return $this->redirection_url;
    }

    public function withRedirectionUrl(?string $redirection_url): self
    {
        $clone = clone $this;
        $clone->redirection_url = $redirection_url;
        return $clone;
    }

    public function getMailNotificationContentType(): int
    {
        return $this->mail_notification_content_type;
    }

    public function withMailNotificationContentType(int $mail_notification_content_type): self
    {
        $clone = clone $this;
        $clone->mail_notification_content_type = $mail_notification_content_type;
        return $clone;
    }

    public function getAlwaysSendMailNotification(): bool
    {
        return $this->always_send_mail_notification;
    }

    public function withAlwaysSendMailNotification(bool $always_send_mail_notification): self
    {
        $clone = clone $this;
        $clone->always_send_mail_notification = $always_send_mail_notification;
        return $clone;
    }
}
