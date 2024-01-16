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

class MainSettings
{
    protected SettingsIntroduction $settings_introduction;
    protected SettingsFinishing $settings_finishing;
    protected SettingsAccess $settings_access;
    protected SettingsTestBehaviour $settings_test_behaviour;
    protected SettingsQuestionBehaviour $settings_question_behaviour;
    protected SettingsParticipantFunctionality $settings_participant_functionality;
    protected SettingsGeneral $settings_general;
    protected SettingsAdditional $settings_additional;

    public function __construct(
        protected int $test_id,
        protected int $obj_id,
        SettingsGeneral $settings_general,
        SettingsIntroduction $settings_introduction,
        SettingsAccess $settings_access,
        SettingsTestBehaviour $settings_test_behaviour,
        SettingsQuestionBehaviour $settings_question_behaviour,
        SettingsParticipantFunctionality $settings_participant_functionality,
        SettingsFinishing $settings_finishing,
        SettingsAdditional $settings_additional
    ) {
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
        $clone->settings_general = $clone->settings_general->withTestId($test_id);
        $clone->settings_introduction = $clone->settings_introduction->withTestId($test_id);
        $clone->settings_access = $clone->settings_access->withTestId($test_id);
        $clone->settings_test_behaviour = $clone->settings_test_behaviour->withTestId($test_id);
        $clone->settings_question_behaviour = $clone->settings_question_behaviour->withTestId($test_id);
        $clone->settings_participant_functionality = $clone->settings_participant_functionality->withTestId($test_id);
        $clone->settings_finishing = $clone->settings_finishing->withTestId($test_id);
        $clone->settings_additional = $clone->settings_additional->withTestId($test_id);
        return $clone;
    }

    public function getObjId(): int
    {
        return $this->obj_id;
    }

    public function withObjId(int $obj_id): int
    {
        $clone = clone $this;
        $clone->obj_id = $obj_id;
        return $clone;
    }

    public function getGeneralSettings(): SettingsGeneral
    {
        return $this->settings_general;
    }
    public function withGeneralSettings(SettingsGeneral $settings): self
    {
        $this->throwOnDifferentTestId($settings);
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
        $this->throwOnDifferentTestId($settings);
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
        $this->throwOnDifferentTestId($settings);
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
        $this->throwOnDifferentTestId($settings);
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
        $this->throwOnDifferentTestId($settings);
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
        $this->throwOnDifferentTestId($settings);
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
        $this->throwOnDifferentTestId($settings);
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
        $this->throwOnDifferentTestId($settings);
        $clone = clone $this;
        $clone->settings_additional = $settings;
        return $clone;
    }

    public function getArrayForLog(\ilLanguage $lng): array
    {
        return [
            $this->settings_general->toLog($lng),
            $this->settings_introduction->toLog($lng),
            $this->settings_access->toLog($lng),
            $this->settings_test_behaviour->toLog($lng),
            $this->settings_question_behaviour->toLog($lng),
            $this->settings_participant_functionality->toLog($lng),
            $this->settings_finishing->toLog($lng),
            $this->settings_additional->toLog($lng)
        ];
    }
}
