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

use ILIAS\Test\Logging\AdditionalInformationGenerator;
use ILIAS\Test\ExportImport\Exportable;

class MainSettings implements Exportable
{
    public function __construct(
        protected int $id,
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

    public function getId(): int
    {
        return $this->id;
    }

    public function withId(int $id): self
    {
        $clone = clone $this;
        $clone->id = $id;
        return $clone;
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

    public function toExport(): array
    {
        return [
            'settings_general' => $this->settings_general->toExport(),
            'settings_introduction' => $this->settings_introduction->toExport(),
            'settings_access' => $this->settings_access->toExport(),
            'settings_test_behaviour' => $this->settings_test_behaviour->toExport(),
            'settings_question_behaviour' => $this->settings_question_behaviour->toExport(),
            'settings_participant_functionality' => $this->settings_participant_functionality->toExport(),
            'settings_finishing' => $this->settings_finishing->toExport(),
            'settings_additional' => $this->settings_additional->toExport(),
        ];
    }

    public static function fromExport(array $data): static
    {
        return new self(
            $data['id'] ?? -1,
            SettingsGeneral::fromExport($data['settings_general']),
            SettingsIntroduction::fromExport($data['settings_introduction']),
            SettingsAccess::fromExport($data['settings_access']),
            SettingsTestBehaviour::fromExport($data['settings_test_behaviour']),
            SettingsQuestionBehaviour::fromExport($data['settings_question_behaviour']),
            SettingsParticipantFunctionality::fromExport($data['settings_participant_functionality']),
            SettingsFinishing::fromExport($data['settings_finishing']),
            SettingsAdditional::fromExport($data['settings_additional']),
        );
    }
}
