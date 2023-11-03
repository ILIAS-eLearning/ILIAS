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

class ilObjTestMainSettings
{
    protected int $test_id;
    protected ilObjTestSettingsIntroduction $settings_introduction;
    protected ilObjTestSettingsFinishing $settings_finishing;
    protected ilObjTestSettingsAccess $settings_access;
    protected ilObjTestSettingsTestBehaviour $settings_test_behaviour;
    protected ilObjTestSettingsQuestionBehaviour $settings_question_behaviour;
    protected ilObjTestSettingsParticipantFunctionality $settings_participant_functionality;
    protected ilObjTestSettingsGeneral $settings_general;
    protected ilObjTestSettingsAdditional $settings_additional;

    public function __construct(
        int $test_id,
        ilObjTestSettingsGeneral $settings_general,
        ilObjTestSettingsIntroduction $settings_introduction,
        ilObjTestSettingsAccess $settings_access,
        ilObjTestSettingsTestBehaviour $settings_test_behaviour,
        ilObjTestSettingsQuestionBehaviour $settings_question_behaviour,
        ilObjTestSettingsParticipantFunctionality $settings_participant_functionality,
        ilObjTestSettingsFinishing $settings_finishing,
        ilObjTestSettingsAdditional $settings_additional
    ) {
        $this->test_id = $test_id;

        foreach ([
            $settings_general,
            $settings_introduction,
            $settings_access,
            $settings_test_behaviour,
            $settings_question_behaviour,
            $settings_participant_functionality,
            $settings_finishing,
            $settings_additional
        ] as $setting) {
            $this->throwOnDifferentTestId($setting);
        }

        $this->settings_general = $settings_general;
        $this->settings_introduction = $settings_introduction;
        $this->settings_access = $settings_access;
        $this->settings_test_behaviour = $settings_test_behaviour;
        $this->settings_question_behaviour = $settings_question_behaviour;
        $this->settings_participant_functionality = $settings_participant_functionality;
        $this->settings_finishing = $settings_finishing;
        $this->settings_additional = $settings_additional;
    }

    protected function throwOnDifferentTestId(TestSettings $setting)
    {
        if ($setting->getTestId() !== $this->getTestId()) {
            throw new \LogicException('TestId mismatch in ' . get_class($setting));
        }
    }

    public function getTestId(): int
    {
        return $this->test_id;
    }
    public function withTestId(int $test_id): self
    {
        $clone = clone $this;
        $clone->test_id = $test_id;
        $clone->settings_introduction = $clone->settings_introduction->withTestId($test_id);
        return $clone;
    }

    public function getGeneralSettings(): ilObjTestSettingsGeneral
    {
        return $this->settings_general;
    }
    public function withGeneralSettings(ilObjTestSettingsGeneral $settings): self
    {
        $this->throwOnDifferentTestId($settings);
        $clone = clone $this;
        $clone->settings_general = $settings;
        return $clone;
    }

    public function getIntroductionSettings(): ilObjTestSettingsIntroduction
    {
        return $this->settings_introduction;
    }
    public function withIntroductionSettings(ilObjTestSettingsIntroduction $settings): self
    {
        $this->throwOnDifferentTestId($settings);
        $clone = clone $this;
        $clone->settings_introduction = $settings;
        return $clone;
    }

    public function getAccessSettings(): ilObjTestSettingsAccess
    {
        return $this->settings_access;
    }
    public function withAccessSettings(ilObjTestSettingsAccess $settings): self
    {
        $this->throwOnDifferentTestId($settings);
        $clone = clone $this;
        $clone->settings_access = $settings;
        return $clone;
    }

    public function getTestBehaviourSettings(): ilObjTestSettingsTestBehaviour
    {
        return $this->settings_test_behaviour;
    }
    public function withTestBehaviourSettings(ilObjTestSettingsTestBehaviour $settings): self
    {
        $this->throwOnDifferentTestId($settings);
        $clone = clone $this;
        $clone->settings_test_behaviour = $settings;
        return $clone;
    }

    public function getQuestionBehaviourSettings(): ilObjTestSettingsQuestionBehaviour
    {
        return $this->settings_question_behaviour;
    }
    public function withQuestionBehaviourSettings(ilObjTestSettingsQuestionBehaviour $settings): self
    {
        $this->throwOnDifferentTestId($settings);
        $clone = clone $this;
        $clone->settings_question_behaviour = $settings;
        return $clone;
    }

    public function getParticipantFunctionalitySettings(): ilObjTestSettingsParticipantFunctionality
    {
        return $this->settings_participant_functionality;
    }
    public function withParticipantFunctionalitySettings(ilObjTestSettingsParticipantFunctionality $settings): self
    {
        $this->throwOnDifferentTestId($settings);
        $clone = clone $this;
        $clone->settings_participant_functionality = $settings;
        return $clone;
    }

    public function getFinishingSettings(): ilObjTestSettingsFinishing
    {
        return $this->settings_finishing;
    }
    public function withFinishingSettings(ilObjTestSettingsFinishing $settings): self
    {
        $this->throwOnDifferentTestId($settings);
        $clone = clone $this;
        $clone->settings_finishing = $settings;
        return $clone;
    }

    public function getAdditionalSettings(): ilObjTestSettingsAdditional
    {
        return $this->settings_additional;
    }
    public function withAdditionalSettings(ilObjTestSettingsAdditional $settings): self
    {
        $this->throwOnDifferentTestId($settings);
        $clone = clone $this;
        $clone->settings_additional = $settings;
        return $clone;
    }
}
