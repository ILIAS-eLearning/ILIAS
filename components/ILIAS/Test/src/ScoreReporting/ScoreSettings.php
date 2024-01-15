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

namespace ILIAS\Test\ScoreSettings;

use ILIAS\Test\MainSettings\TestSettings;

class ScoreSettings
{
    protected int $test_id;
    protected SettingsScoring $settings_scoring;
    protected SettingsResultSummary $settings_result_summary;
    protected SettingsResultDetails $settings_result_details;
    protected SettingsGamification $settings_gamification;

    public function __construct(
        int $test_id,
        SettingsScoring $settings_scoring,
        SettingsResultSummary $settings_result_summary,
        SettingsResultDetails $settings_result_details,
        SettingsGamification $settings_gamification
    ) {
        $this->test_id = $test_id;

        foreach ([
            $settings_scoring,
            $settings_result_summary,
            $settings_result_details,
            $settings_gamification
        ] as $setting) {
            $this->throwOnDifferentTestId($setting);
        }

        $settings_result_summary = $settings_result_summary
            ->withShowPassDetails($settings_result_details->getShowPassDetails());

        $this->settings_scoring = $settings_scoring;
        $this->settings_result_summary = $settings_result_summary;
        $this->settings_result_details = $settings_result_details;
        $this->settings_gamification = $settings_gamification;
    }

    protected function throwOnDifferentTestId(TestSettings $setting): void
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
        $clone->settings_scoring = $clone->settings_scoring->withTestId($test_id);
        $clone->settings_result_summary = $clone->settings_result_summary->withTestId($test_id);
        $clone->settings_result_details = $clone->settings_result_details->withTestId($test_id);
        $clone->settings_gamification = $clone->settings_gamification->withTestId($test_id);
        return $clone;
    }


    public function getScoringSettings(): SettingsScoring
    {
        return $this->settings_scoring;
    }
    public function withScoringSettings(SettingsScoring $settings): self
    {
        $this->throwOnDifferentTestId($settings);
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
        $this->throwOnDifferentTestId($settings);
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
        $this->throwOnDifferentTestId($settings);
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
        $this->throwOnDifferentTestId($settings);
        $clone = clone $this;
        $clone->settings_gamification = $settings;
        return $clone;
    }
}
