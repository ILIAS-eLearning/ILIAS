<?php

declare(strict_types=1);

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

use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;
use ILIAS\UI\Component\Input\Field\Input;
use ILIAS\Refinery\Factory as Refinery;

class ilObjTestSettingsGamification extends TestSettings
{
    public const HIGHSCORE_SHOW_OWN_TABLE = 1;
    public const HIGHSCORE_SHOW_TOP_TABLE = 2;
    public const HIGHSCORE_SHOW_ALL_TABLES = 3;

    protected bool $highscore_enabled = false;
    protected bool $highscore_anon = true;
    protected bool $highscore_achieved_ts = true;
    protected bool $highscore_score = true;
    protected bool $highscore_percentage = true;
    protected bool $highscore_hints = true;
    protected bool $highscore_wtime = true;
    protected bool $highscore_own_table = true;
    protected bool $highscore_top_table = true;
    protected int $highscore_top_num = 10;


    public function __construct(int $test_id)
    {
        parent::__construct($test_id);
    }

    public function toForm(
        \ilLanguage $lng,
        FieldFactory $f,
        Refinery $refinery,
        array $environment = null
    ): Input {
        $optional_group = $f->optionalGroup(
            [
                'highscore_mode' => $f->radio($lng->txt('tst_highscore_mode'), "")
                    ->withOption((string) self::HIGHSCORE_SHOW_OWN_TABLE, $lng->txt('tst_highscore_own_table'), $lng->txt('tst_highscore_own_table_description'))
                    ->withOption((string) self::HIGHSCORE_SHOW_TOP_TABLE, $lng->txt('tst_highscore_top_table'), $lng->txt('tst_highscore_top_table_description'))
                    ->withOption((string) self::HIGHSCORE_SHOW_ALL_TABLES, $lng->txt('tst_highscore_all_tables'), $lng->txt('tst_highscore_all_tables_description'))
                    ->withValue($this->getHighScoreMode() > 0 ? (string) $this->getHighScoreMode() : '')
                    ->withRequired(true)
                    ,
                'highscore_top_num' => $f->numeric($lng->txt('tst_highscore_top_num'), $lng->txt('tst_highscore_top_num_description'))
                    ->withRequired(true)
                    ->withValue($this->getHighscoreTopNum()),
                'highscore_anon' => $f->checkbox(
                    $lng->txt('tst_highscore_anon'),
                    $lng->txt('tst_highscore_anon_description')
                )->withValue($this->getHighscoreAnon()),
                'highscore_achieved_ts' => $f->checkbox(
                    $lng->txt('tst_highscore_achieved_ts'),
                    $lng->txt('tst_highscore_achieved_ts_description')
                )->withValue($this->getHighscoreAchievedTS()),
                'highscore_score' => $f->checkbox(
                    $lng->txt('tst_highscore_score'),
                    $lng->txt('tst_highscore_score_description')
                )->withValue($this->getHighscoreScore()),
                'highscore_percentage' => $f->checkbox(
                    $lng->txt('tst_highscore_percentage'),
                    $lng->txt('tst_highscore_percentage_description')
                )->withValue($this->getHighscorePercentage()),
                'highscore_hints' => $f->checkbox(
                    $lng->txt('tst_highscore_hints'),
                    $lng->txt('tst_highscore_hints_description')
                )->withValue($this->getHighscoreHints()),
                'highscore_wtime' => $f->checkbox(
                    $lng->txt('tst_highscore_wtime'),
                    $lng->txt('tst_highscore_wtime_description')
                )->withValue($this->getHighscoreWTime())

            ],
            $lng->txt('tst_highscore_enabled'),
            $lng->txt('tst_highscore_description')
        );

        if (!$this->getHighscoreEnabled()) {
            $optional_group = $optional_group->withValue(null);
        }

        $fields = ['highscore' => $optional_group];
        return $f->section($fields, $lng->txt('tst_results_gamification'))
            ->withAdditionalTransformation(
                $refinery->custom()->transformation(
                    function ($v) {
                        $settings = clone $this;

                        if (! $v['highscore']) {
                            return $settings->withHighscoreEnabled(false);
                        }

                        return $settings
                            ->withHighscoreEnabled(true)
                            ->withHighscoreOwnTable(
                                (int) $v['highscore']['highscore_mode'] == self::HIGHSCORE_SHOW_OWN_TABLE ||
                                (int) $v['highscore']['highscore_mode'] == self::HIGHSCORE_SHOW_ALL_TABLES
                            )
                            ->withHighscoreTopTable(
                                (int) $v['highscore']['highscore_mode'] == self::HIGHSCORE_SHOW_TOP_TABLE ||
                                (int) $v['highscore']['highscore_mode'] == self::HIGHSCORE_SHOW_ALL_TABLES
                            )
                            ->withHighscoreTopNum($v['highscore']['highscore_top_num'])
                            ->withHighscoreAnon($v['highscore']['highscore_anon'])
                            ->withHighscoreAchievedTS($v['highscore']['highscore_achieved_ts'])
                            ->withHighscoreScore($v['highscore']['highscore_score'])
                            ->withHighscorePercentage($v['highscore']['highscore_percentage'])
                            ->withHighscoreHints($v['highscore']['highscore_hints'])
                            ->withHighscoreWTime($v['highscore']['highscore_wtime']);
                    }
                )
            );
    }

    public function toStorage(): array
    {
        return [
            'highscore_enabled' => ['integer', (int) $this->getHighscoreEnabled()],
            'highscore_anon' => ['integer', (int) $this->getHighscoreAnon()],
            'highscore_achieved_ts' => ['integer', (int) $this->getHighscoreAchievedTS()],
            'highscore_score' => ['integer', (int) $this->getHighscoreScore()],
            'highscore_percentage' => ['integer', (int) $this->getHighscorePercentage()],
            'highscore_hints' => ['integer', (int) $this->getHighscoreHints()],
            'highscore_wtime' => ['integer', (int) $this->getHighscoreWTime()],
            'highscore_own_table' => ['integer', (int) $this->getHighscoreOwnTable()],
            'highscore_top_table' => ['integer', (int) $this->getHighscoreTopTable()],
            'highscore_top_num' => ['integer', $this->getHighscoreTopNum()]
        ];
    }

    public function getHighscoreEnabled(): bool
    {
        return $this->highscore_enabled;
    }
    public function withHighscoreEnabled(bool $highscore_enabled): self
    {
        $clone = clone $this;
        $clone->highscore_enabled = $highscore_enabled;
        return $clone;
    }

    public function getHighscoreOwnTable(): bool
    {
        return $this->highscore_own_table;
    }
    public function withHighscoreOwnTable(bool $highscore_own_table): self
    {
        $clone = clone $this;
        $clone->highscore_own_table = $highscore_own_table;
        return $clone;
    }
    public function getHighscoreTopTable(): bool
    {
        return $this->highscore_top_table;
    }
    public function withHighscoreTopTable(bool $highscore_top_table): self
    {
        $clone = clone $this;
        $clone->highscore_top_table = $highscore_top_table;
        return $clone;
    }

    public function getHighScoreMode(): int
    {
        if ($this->getHighscoreTopTable() && $this->getHighscoreOwnTable()) {
            return self::HIGHSCORE_SHOW_ALL_TABLES;
        }

        if ($this->getHighscoreTopTable()) {
            return self::HIGHSCORE_SHOW_TOP_TABLE;
        }

        if ($this->getHighscoreOwnTable()) {
            return self::HIGHSCORE_SHOW_OWN_TABLE;
        }

        return 0;
    }

    public function getHighscoreTopNum(): int
    {
        return $this->highscore_top_num;
    }
    public function withHighscoreTopNum(int $highscore_top_num): self
    {
        $clone = clone $this;
        $clone->highscore_top_num = $highscore_top_num;
        return $clone;
    }

    public function getHighscoreAnon(): bool
    {
        return $this->highscore_anon;
    }
    public function withHighscoreAnon(bool $highscore_anon): self
    {
        $clone = clone $this;
        $clone->highscore_anon = $highscore_anon;
        return $clone;
    }

    public function getHighscoreAchievedTS(): bool
    {
        return $this->highscore_achieved_ts;
    }
    public function withHighscoreAchievedTS(bool $highscore_achieved_ts): self
    {
        $clone = clone $this;
        $clone->highscore_achieved_ts = $highscore_achieved_ts;
        return $clone;
    }

    public function getHighscoreScore(): bool
    {
        return $this->highscore_score;
    }
    public function withHighscoreScore(bool $highscore_score): self
    {
        $clone = clone $this;
        $clone->highscore_score = $highscore_score;
        return $clone;
    }

    public function getHighscorePercentage(): bool
    {
        return $this->highscore_percentage;
    }
    public function withHighscorePercentage(bool $highscore_percentage): self
    {
        $clone = clone $this;
        $clone->highscore_percentage = $highscore_percentage;
        return $clone;
    }

    public function getHighscoreHints(): bool
    {
        return $this->highscore_hints;
    }
    public function withHighscoreHints(bool $highscore_hints): self
    {
        $clone = clone $this;
        $clone->highscore_hints = $highscore_hints;
        return $clone;
    }

    public function getHighscoreWTime(): bool
    {
        return $this->highscore_wtime;
    }
    public function withHighscoreWTime(bool $highscore_wtime): self
    {
        $clone = clone $this;
        $clone->highscore_wtime = $highscore_wtime;
        return $clone;
    }
}
