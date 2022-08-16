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

class ilObjTestSettingsScoring extends TestSettings
{
    protected int $count_system = 0;
    protected int $score_cutting = 0;
    protected int $pass_scoring = 0;


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
        $trafo = $refinery->kindlyTo()->Int();
        $fields = [
            'count_system' => $f->radio($lng->txt('tst_text_count_system'), "")
                ->withOption('0', $lng->txt('tst_count_partial_solutions'), $lng->txt('tst_count_partial_solutions_desc'))
                ->withOption('1', $lng->txt('tst_count_correct_solutions'), $lng->txt('tst_count_correct_solutions_desc'))
                ->withValue($this->getCountSystem())
                ->withAdditionalTransformation($trafo),
            'score_cutting' => $f->radio($lng->txt('tst_score_cutting'), "")
                ->withOption('0', $lng->txt('tst_score_cut_question'), $lng->txt('tst_score_cut_question_desc'))
                ->withOption('1', $lng->txt('tst_score_cut_test'), $lng->txt('tst_score_cut_test_desc'))
                ->withValue($this->getScoreCutting())
                ->withAdditionalTransformation($trafo),
            'pass_scoring' => $f->radio($lng->txt('tst_pass_scoring'), "")
                ->withOption('0', $lng->txt('tst_pass_last_pass'), $lng->txt('tst_pass_last_pass_desc'))
                ->withOption('1', $lng->txt('tst_pass_best_pass'), $lng->txt('tst_pass_best_pass_desc'))
                ->withValue($this->getPassScoring())
                ->withAdditionalTransformation($trafo)
        ];
        return $f->section($fields, $lng->txt('test_scoring'))
            ->withAdditionalTransformation(
                $refinery->custom()->transformation(
                    function ($v) {
                        return (clone $this)
                            ->withCountSystem($v['count_system'])
                            ->withScoreCutting($v['score_cutting'])
                            ->withPassScoring($v['pass_scoring']);
                    }
                )
            );
    }

    public function toStorage(): array
    {
        return [
            'count_system' => ['text', $this->getCountSystem()],
            'score_cutting' => ['text', $this->getScoreCutting()],
            'pass_scoring' => ['text', $this->getPassScoring()]
        ];
    }


    public function getCountSystem(): int
    {
        return $this->count_system;
    }
    public function withCountSystem(int $count_system): self
    {
        $clone = clone $this;
        $clone->count_system = $count_system;
        return $clone;
    }

    public function getScoreCutting(): int
    {
        return $this->score_cutting;
    }
    public function withScoreCutting(int $score_cutting): self
    {
        $clone = clone $this;
        $clone->score_cutting = $score_cutting;
        return $clone;
    }

    public function getPassScoring(): int
    {
        return $this->pass_scoring;
    }
    public function withPassScoring(int $pass_scoring): self
    {
        $clone = clone $this;
        $clone->pass_scoring = $pass_scoring;
        return $clone;
    }
}
