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

namespace ILIAS\Test\Settings\ScoreReporting;

use ILIAS\Test\ExportImport\Contracts\Normalizable;
use ILIAS\Test\Scoring\Settings\Settings as SettingsScoring;
use ILIAS\Test\Logging\AdditionalInformationGenerator;

class ScoreSettings implements Normalizable
{
    public function __construct(
        protected int $id,
        protected SettingsScoring $settings_scoring,
        protected SettingsResultSummary $settings_result_summary,
        protected SettingsResultDetails $settings_result_details,
        protected SettingsGamification $settings_gamification
    ) {
        $this->settings_result_summary = $settings_result_summary
            ->withShowPassDetails($settings_result_details->getShowPassDetails());
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

    public function getScoringSettings(): SettingsScoring
    {
        return $this->settings_scoring;
    }

    public function withScoringSettings(SettingsScoring $settings): self
    {
        $clone = clone $this;
        $clone->settings_scoring = $settings;
        return $clone;
    }

    public function getResultSummarySettings(): SettingsResultSummary
    {
        return $this->settings_result_summary;
    }

    public function withResultSummarySettings(SettingsResultSummary $settings): self
    {
        $clone = clone $this;
        $clone->settings_result_summary = $settings;
        return $clone;
    }

    public function getResultDetailsSettings(): SettingsResultDetails
    {
        return $this->settings_result_details;
    }

    public function withResultDetailsSettings(SettingsResultDetails $settings): self
    {
        $clone = clone $this;
        $clone->settings_result_details = $settings;
        return $clone;
    }

    public function getGamificationSettings(): SettingsGamification
    {
        return $this->settings_gamification;
    }

    public function withGamificationSettings(SettingsGamification $settings): self
    {
        $clone = clone $this;
        $clone->settings_gamification = $settings;
        return $clone;
    }

    public function getArrayForLog(AdditionalInformationGenerator $additional_info): array
    {
        return $this->settings_scoring->toLog($additional_info)
            + $this->settings_result_summary->toLog($additional_info)
            + $this->settings_result_details->toLog($additional_info)
            + $this->settings_gamification->toLog($additional_info);
    }

    public function normalize(): array
    {
        return [
            'settings_scoring' => $this->settings_scoring->normalize(),
            'settings_result_summary' => $this->settings_result_summary->normalize(),
            'settings_result_details' => $this->settings_result_details->normalize(),
            'settings_gamification' => $this->settings_gamification->normalize()
        ];
    }

    public static function denormalize(array $data): static
    {
        return new self(
            $data['id'] ?? -1,
            SettingsScoring::denormalize($data['settings_scoring']),
            SettingsResultSummary::denormalize($data['settings_result_summary']),
            SettingsResultDetails::denormalize($data['settings_result_details']),
            SettingsGamification::denormalize($data['settings_gamification'])
        );
    }
}
