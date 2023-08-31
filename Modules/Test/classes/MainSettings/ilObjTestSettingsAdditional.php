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

class ilObjTestSettingsAdditional extends TestSettings
{
    public function __construct(
        int $test_id,
        protected bool $skills_service_enabled = false,
        protected bool $hide_info_tab = false,
    ) {
        parent::__construct($test_id);
    }

    public function toForm(
        \ilLanguage $lng,
        FieldFactory $f,
        Refinery $refinery,
        array $environment = null
    ): array|FormInput
    {
        $inputs['activate_skill_service'] = $f->checkbox(
            $lng->txt('tst_activate_skill_service'),
            $lng->txt('tst_activate_skill_service_desc')
        )->withValue($this->getSkillsServiceEnabled());

        $inputs['hide_info_tab'] = $f->checkbox(
            $lng->txt('tst_hide_info_tab'),
            $lng->txt('tst_hide_info_tab_desc')
        )->withValue($this->getHideInfoTab());

        if ($environment['participant_data_exists']) {
            $inputs['activate_skill_service'] = $inputs['activate_skill_service']->withDisabled(true);
        }

        return $f->section($inputs, $lng->txt('tst_settings_header_additional'));
    }

    public function toStorage(): array
    {
        return [
            'skill_service' => ['integer', (int) $this->getSkillsServiceEnabled()],
            'hide_info_tab' => ['integer', (int) $this->getHideInfoTab()],
        ];
    }

    public function getSkillsServiceEnabled(): bool
    {
        return $this->skills_service_enabled;
    }

    public function withSkillsServiceEnabled(bool $skills_service_enabled): self
    {
        $clone = clone $this;
        $clone->skills_service_enabled = $skills_service_enabled;
        return $clone;
    }

    public function getHideInfoTab(): bool
    {
        return $this->hide_info_tab;
    }
    public function withHideInfoTab(bool $hide_info_tab): self
    {
        $clone = clone $this;
        $clone->hide_info_tab = $hide_info_tab;
        return $clone;
    }
}
