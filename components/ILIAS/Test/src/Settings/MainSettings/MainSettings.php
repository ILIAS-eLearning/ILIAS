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

class MainSettings
{
    public function __construct(
        protected SettingsGeneral $settings_general,
        protected SettingsIntroduction $settings_introduction,
        protected SettingsAccess $settings_access,
        protected SettingsTestBehaviour $settings_test_behaviour,
        protected SettingsQuestionBehaviour $settings_question_behaviour,
        protected SettingsParticipantFunctionality $settings_participant_functionality,
        protected SettingsFinishing $settings_finishing,
        protected SettingsAdditional $settings_additional
    ) {
    }

    public function getGeneralSettings(): SettingsGeneral
    {
        return $this->settings_general;
    }

    public function withGeneralSettings(SettingsGeneral $settings): self
    {
        $clone = clone $this;
        $clone->settings_general = $settings;
        return $clone;
    }

    public function getIntroductionSettings(): SettingsIntroduction
    {
        return $this->settings_introduction;
    }

    public function withIntroductionSettings(SettingsIntroduction $settings): self
    {
        $clone = clone $this;
        $clone->settings_introduction = $settings;
        return $clone;
    }

    public function getAccessSettings(): SettingsAccess
    {
        return $this->settings_access;
    }

    public function withAccessSettings(SettingsAccess $settings): self
    {
        $clone = clone $this;
        $clone->settings_access = $settings;
        return $clone;
    }

    public function getTestBehaviourSettings(): SettingsTestBehaviour
    {
        return $this->settings_test_behaviour;
    }

    public function withTestBehaviourSettings(SettingsTestBehaviour $settings): self
    {
        $clone = clone $this;
        $clone->settings_test_behaviour = $settings;
        return $clone;
    }

    public function getQuestionBehaviourSettings(): SettingsQuestionBehaviour
    {
        return $this->settings_question_behaviour;
    }

    public function withQuestionBehaviourSettings(SettingsQuestionBehaviour $settings): self
    {
        $clone = clone $this;
        $clone->settings_question_behaviour = $settings;
        return $clone;
    }

    public function getParticipantFunctionalitySettings(): SettingsParticipantFunctionality
    {
        return $this->settings_participant_functionality;
    }

    public function withParticipantFunctionalitySettings(SettingsParticipantFunctionality $settings): self
    {
        $clone = clone $this;
        $clone->settings_participant_functionality = $settings;
        return $clone;
    }

    public function getFinishingSettings(): SettingsFinishing
    {
        return $this->settings_finishing;
    }

    public function withFinishingSettings(SettingsFinishing $settings): self
    {
        $clone = clone $this;
        $clone->settings_finishing = $settings;
        return $clone;
    }

    public function getAdditionalSettings(): SettingsAdditional
    {
        return $this->settings_additional;
    }

    public function withAdditionalSettings(SettingsAdditional $settings): self
    {
        $clone = clone $this;
        $clone->settings_additional = $settings;
        return $clone;
    }

    public function getArrayForLog(
        AdditionalInformationGenerator $additional_info
    ): array {
        return $this->settings_general->toLog($additional_info)
            + $this->settings_introduction->toLog($additional_info)
            + $this->settings_access->toLog($additional_info)
            + $this->settings_test_behaviour->toLog($additional_info)
            + $this->settings_question_behaviour->toLog($additional_info)
            + $this->settings_participant_functionality->toLog($additional_info)
            + $this->settings_finishing->toLog($additional_info)
            + $this->settings_additional->toLog($additional_info);
    }
}
