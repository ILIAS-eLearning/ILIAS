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

class ilObjTestSettingsIntroduction extends TestSettings
{
    public function __construct(
        int $test_id,
        protected bool $introduction_enabled = false,
        protected ?string $introduction_text = null,
        protected ?int $introduction_page_id = null,
        protected bool $conditions_checkbox_enabled = false,
    ) {
        parent::__construct($test_id);
    }

    public function toForm(
        \ilLanguage $lng,
        FieldFactory $f,
        Refinery $refinery,
        array $environment = null
    ): FormInput {
        $inputs['introduction_enabled'] = $f->checkbox(
            $lng->txt('tst_introduction'),
            $lng->txt('tst_introduction_desc')
        )->withValue($this->getIntroductionEnabled());

        $inputs['conditions_checkbox_enabled'] = $f->checkbox(
            $lng->txt('tst_conditions_checkbox_enabled'),
            $lng->txt('tst_conditions_checkbox_enabled_desc')
        )->withValue($this->getExamConditionsCheckboxEnabled());

        return $f->section($inputs, $lng->txt('tst_settings_header_intro'));
    }

    public function toStorage(): array
    {
        return [
            'intro_enabled' => ['integer', (int) $this->getIntroductionEnabled()],
            'introduction' => ['text', $this->getIntroductionText()],
            'introduction_page_id' => ['integer', $this->getIntroductionPageId()],
            'conditions_checkbox_enabled' => ['integer', (int) $this->getExamConditionsCheckboxEnabled()],
        ];
    }

    public function getIntroductionEnabled(): bool
    {
        return $this->introduction_enabled;
    }
    public function withIntroductionEnabled(bool $introduction_enabled): self
    {
        $clone = clone $this;
        $clone->introduction_enabled = $introduction_enabled;
        return $clone;
    }

    public function getIntroductionText(): string
    {
        return $this->introduction_text ?? '';
    }
    public function withIntroductionText(?string $introduction_text): self
    {
        $clone = clone $this;
        $clone->introduction_text = $introduction_text;
        return $clone;
    }

    public function getIntroductionPageId(): ?int
    {
        return $this->introduction_page_id;
    }
    public function withIntroductionPageId(?int $introduction_page_id): self
    {
        $clone = clone $this;
        $clone->introduction_page_id = $introduction_page_id;
        return $clone;
    }

    public function getExamConditionsCheckboxEnabled(): bool
    {
        return $this->conditions_checkbox_enabled;
    }
    public function withExamConditionsCheckboxEnabled(bool $conditions_checkbox_enabled): self
    {
        $clone = clone $this;
        $clone->conditions_checkbox_enabled = $conditions_checkbox_enabled;
        return $clone;
    }
}
