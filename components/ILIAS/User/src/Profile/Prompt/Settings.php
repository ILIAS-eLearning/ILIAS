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

namespace ILIAS\User\Profile\Prompt;

use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;
use ILIAS\UI\Component\Input\Field\SwitchableGroup;
use ILIAS\Refinery\Factory as Refinery;

/**
 * Profile prompt settings
 * @author Alexander Killing <killing@leifos.de>
 */
class Settings
{
    public const MODE_INCOMPLETE_ONLY = 0;
    public const MODE_ONCE_AFTER_LOGIN = 1;
    public const MODE_REPEAT = 2;

    /**
     * @param array<string, string> $info_texts
     * @param array<string, string> $promp_texts
     */
    public function __construct(
        private int $mode,
        private ?int $days,
        private array $info_texts,
        private array $prompt_texts
    ) {
    }

    public function getDays(): ?int
    {
        return $this->days;
    }

    public function getMode(): int
    {
        return $this->mode;
    }

    /**
     * @return array<string>
     */
    public function getInfoTexts(): array
    {
        return $this->info_texts;
    }

    /**
     * @return array<string>
     */
    public function getPromptTexts(): array
    {
        return $this->prompt_texts;
    }

    public function getInfoText(string $lang): string
    {
        return $this->info_texts[$lang] ?? '';
    }

    public function getPromptText(string $lang): string
    {
        return $this->prompt_texts[$lang] ?? '';
    }

    public function withFormData(array $data): self
    {
        $clone = clone $this;
        $clone->mode = $data['prompting_settings']['prompt_mode']['mode'];
        $clone->days = $data['prompting_settings']['prompt_mode']['days'];
        $clone->info_texts = $data['profile_info'];
        $clone->prompt_texts = $data['prompting_settings']['prompt_texts'];
        return $clone;
    }

    /**
     * @return array<\ILIAS\UI\Component\Input\Input>
     */
    public function toForm(
        UIFactory $ui_factory,
        \ilLanguage $lng,
        Refinery $refinery
    ): array {
        $lng->loadLanguageModule('meta');

        return [
            'profile_info' => $ui_factory->input()->field()->section(
                $this->buildLangTextAreaInputs(
                    $ui_factory->input()->field(),
                    $lng,
                    fn(string $lang): string => $this->info_texts[$lang] ?? ''
                ),
                $lng->txt('user_profile_info_std'),
                $lng->txt('user_profile_info_text_info')
            ),
            'prompting_settings' => $ui_factory->input()->field()->section(
                $this->buildPromptingSettingsInputs($ui_factory->input()->field(), $lng, $refinery),
                $lng->txt('user_prompting_settings')
            )
        ];
    }

    /**
     * @return array<\ILIAS\UI\Component\Input\Field\TextArea>
     */
    private function buildLangTextAreaInputs(
        FieldFactory $field_factory,
        \ilLanguage $lng,
        \Closure $value_closure
    ): array {
        return array_reduce(
            $lng->getInstalledLanguages(),
            function (array $c, string $v) use ($field_factory, $lng, $value_closure): array {
                $c[$v] = $field_factory
                    ->textarea($lng->txt("meta_l_{$v}"))
                    ->withValue($value_closure($v));
                return $c;
            },
            []
        );
    }

    /**
     * @return array<\ILIAS\UI\Component\Input\Input>
     */
    private function buildPromptingSettingsInputs(
        FieldFactory $field_factory,
        \ilLanguage $lng,
        Refinery $refinery
    ): array {
        $trafo = $refinery->custom()->transformation(
            function ($vs) {
                return array_filter($vs);
            }
        );
        return [
            'prompt_mode' => $this->buildPromptModeInput($field_factory, $lng, $refinery),
            'prompt_texts' => $field_factory->section(
                $this->buildLangTextAreaInputs(
                    $field_factory,
                    $lng,
                    fn(string $lang): string => $this->prompt_texts[$lang] ?? ''
                ),
                $lng->txt('user_profile_prompt_text'),
                $lng->txt('user_profile_prompt_text_info')
            )->withAdditionalTransformation($trafo)
        ];
    }

    private function buildPromptModeInput(
        FieldFactory $field_factory,
        \ilLanguage $lng,
        Refinery $refinery
    ): SwitchableGroup {
        $trafo = $refinery->custom()->transformation(
            static fn(array $vs): array => [
                    'mode' => $refinery->kindlyTo()->int()->transform($vs[0]),
                    'days' => $vs[1]['days'] ?? null
                ]
        );
        return $field_factory->switchableGroup(
            [
                Settings::MODE_INCOMPLETE_ONLY => $field_factory->group(
                    [],
                    $lng->txt('user_prompt_incomplete'),
                    $lng->txt('user_prompt_incomplete_info')
                ),
                Settings::MODE_ONCE_AFTER_LOGIN => $field_factory->group(
                    [
                            'days' => $field_factory
                                ->numeric($lng->txt('days'))
                                ->withRequired(true)
                                ->withAdditionalTransformation($refinery->int()->isGreaterThan(0))
                                ->withValue($this->getDays())
                        ],
                    $lng->txt('user_prompt_once_after_login'),
                    $lng->txt('user_prompt_once_after_login_info')
                ),
                Settings::MODE_REPEAT => $field_factory->group(
                    [
                            'days' => $field_factory
                                ->numeric($lng->txt('days'))
                                ->withRequired(true)
                                ->withAdditionalTransformation($refinery->int()->isGreaterThan(0))
                                ->withValue($this->getDays())
                        ],
                    $lng->txt('user_prompt_repeat'),
                    $lng->txt('user_prompt_repeat_info')
                ),
            ],
            $lng->txt('user_prompting_recurrence')
        )->withAdditionalTransformation($trafo)
        ->withValue($this->getMode());
    }
}
