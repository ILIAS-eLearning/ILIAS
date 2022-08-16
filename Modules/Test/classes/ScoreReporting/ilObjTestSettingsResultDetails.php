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

class ilObjTestSettingsResultDetails extends TestSettings
{
    public const RESULTPRES_BIT_PASS_DETAILS = 1;
    public const RESULTPRES_BIT_SOLUTION_DETAILS = 2;
    public const RESULTPRES_BIT_SOLUTION_PRINTVIEW = 4;
    public const RESULTPRES_BIT_SOLUTION_FEEDBACK = 8;
    public const RESULTPRES_BIT_SOLUTION_ANSWERS_ONLY = 16;
    public const RESULTPRES_BIT_SOLUTION_SIGNATURE = 32;
    public const RESULTPRES_BIT_SOLUTION_SUGGESTED = 64;
    public const RESULTPRES_BIT_SOLUTION_LISTCOMPARE = 128;
    public const RESULTPRES_BIT_SOLUTION_LISTOWNANSWERS = 256;

    public const EXPORT_BIT_SINGLECHOICE_SHORT = 1;

    protected bool $print_bs_with_res = true;
    protected bool $examid_in_test_res = true;
    protected int $exportsettings = 0;
    protected int $results_presentation = 0;
    protected array $taxonomy_filter_ids = [];


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
        $bool_with_optional_addition = $refinery->custom()->transformation(
            function ($v) {
                if (!$v) {
                    return [false, false]; //[enabled, show_best_solution]
                }
                return [true, array_shift($v)];
            }
        );

        $optgroup_lists = $f->optionalGroup(
            [
                $f->checkbox(
                    $lng->txt('tst_results_print_best_solution'),
                    $lng->txt('tst_results_print_best_solution_info')
                )->withValue($this->getShowSolutionListComparison())
            ],
            $lng->txt('tst_show_solution_details'),
            $lng->txt('tst_show_solution_details_desc')
        )->withAdditionalTransformation($bool_with_optional_addition);

        if (!$this->getShowSolutionListOwnAnswers()) {
            $optgroup_lists = $optgroup_lists->withValue(null);
        }

        $optgroup_singlepage = $f->optionalGroup(
            [
                $f->checkbox(
                    $lng->txt('tst_results_print_best_solution_singlepage'),
                    $lng->txt('tst_results_print_best_solution_singlepage_info')
                )->withValue($this->getPrintBestSolutionWithResult())
            ],
            $lng->txt('tst_show_solution_details_singlepage'),
            $lng->txt('tst_show_solution_details_singlepage_desc')
        )->withAdditionalTransformation($bool_with_optional_addition);
        if (!$this->getShowSolutionDetails()) {
            $optgroup_singlepage = $optgroup_singlepage->withValue(null);
        }


        $taxonomy_options = $environment['taxonomy_options'];
        $taxonomy_ids = $f->multiselect(
            $lng->txt('tst_results_tax_filters'),
            $taxonomy_options,
            ''
        );

        $fields = [
            'solution_details' => $optgroup_lists,
            'solution_details_singlepage' => $optgroup_singlepage,

            'solution_feedback' => $f->checkbox(
                $lng->txt('tst_show_solution_feedback'),
                $lng->txt('tst_show_solution_feedback_desc')
            )->withValue($this->getShowSolutionFeedback()),
            'solution_suggested' => $f->checkbox(
                $lng->txt('tst_show_solution_suggested'),
                $lng->txt('tst_show_solution_suggested_desc')
            )->withValue($this->getShowSolutionSuggested()),
            'solution_printview' => $f->checkbox(
                $lng->txt('tst_show_solution_printview'),
                $lng->txt('tst_show_solution_printview_desc')
            )->withValue($this->getShowSolutionPrintview()),
            'solution_hide_page' => $f->checkbox(
                $lng->txt('tst_hide_pagecontents'),
                $lng->txt('tst_hide_pagecontents_desc')
            )->withValue($this->getShowSolutionAnswersOnly()),

            'solution_signature' => $f->checkbox(
                $lng->txt('tst_show_solution_signature'),
                $lng->txt('tst_show_solution_signature_desc')
            )
            ->withValue($this->getShowSolutionSignature())
            //TODO ?->withDisabled($anonymity)
            ,
            'examid_in_test_res' => $f->checkbox(
                $lng->txt('examid_in_test_res'),
                $lng->txt('examid_in_test_res_desc')
            )->withValue($this->getShowExamIdInTestResults()),
            'exp_sc_short' => $f->checkbox(
                $lng->txt('tst_exp_sc_short'),
                $lng->txt('tst_exp_sc_short_desc')
            )->withValue($this->getExportSettingsSingleChoiceShort()),
            'result_tax_filters' => $taxonomy_ids
                ->withValue($this->getTaxonomyFilterIds())
        ];

        return $f->section($fields, $lng->txt('tst_results_details_options'))
            ->withAdditionalTransformation(
                $refinery->custom()->transformation(
                    function ($v) {
                        list($solution_list_details, $solution_list_best_solution) = $v['solution_details'];
                        list($solution_sp_details, $solution_sp_best_solution) = $v['solution_details_singlepage'];
                        return (clone $this)
                            ->withShowSolutionListOwnAnswers($solution_list_details)
                            ->withShowSolutionListComparison($solution_list_best_solution)
                            ->withShowSolutionDetails($solution_sp_details)
                            ->withPrintBestSolutionWithResult($solution_sp_best_solution)
                            ->withShowSolutionFeedback($v['solution_feedback'])
                            ->withShowSolutionSuggested($v['solution_suggested'])
                            ->withShowSolutionPrintview($v['solution_printview'])
                            ->withShowSolutionAnswersOnly($v['solution_hide_page'])
                            ->withShowSolutionSignature($v['solution_signature'])
                            ->withShowExamIdInTestResults($v["examid_in_test_res"])
                            ->withExportSettingsSingleChoiceShort($v["exp_sc_short"])
                            ->withTaxonomyFilterIds($v["result_tax_filters"] ?? []);
                    }
                )
            );
    }

    public function toStorage(): array
    {
        return [
            'print_bs_with_res' => ['integer', (int) $this->getPrintBestSolutionWithResult()],
            'results_presentation' => ['integer', $this->getResultsPresentation()],
            'examid_in_test_res' => ['integer', (int) $this->getShowExamIdInTestResults()],
            'exportsettings' => ['integer', (int) $this->getExportSettings()],
            'results_presentation' => ['integer', (int) $this->getResultsPresentation()],
            'result_tax_filters' => ['string', serialize($this->getTaxonomyFilterIds())]
        ];
    }


    public function getPrintBestSolutionWithResult(): bool
    {
        return $this->print_bs_with_res;
    }
    public function withPrintBestSolutionWithResult(bool $print_bs_with_res): self
    {
        $clone = clone $this;
        $clone->print_bs_with_res = $print_bs_with_res;
        return $clone;
    }

    public function getResultsPresentation(): int
    {
        return $this->results_presentation;
    }
    public function withResultsPresentation(int $results_presentation): self
    {
        $clone = clone $this;
        $clone->results_presentation = $results_presentation;
        return $clone;
    }

    public function getShowExamIdInTestResults(): bool
    {
        return $this->examid_in_test_res;
    }
    public function withShowExamIdInTestResults(bool $examid_in_test_res): self
    {
        $clone = clone $this;
        $clone->examid_in_test_res = $examid_in_test_res;
        return $clone;
    }

    protected function compareResultPresentation(int $bit): bool
    {
        return ($this->results_presentation & $bit) > 0;
    }
    protected function modifyResultPresentation(int $bit, bool $flag): self
    {
        $clone = clone $this;
        $v = $clone->results_presentation;

        if ($flag) {
            $v = $v | $bit;
        } else {
            if ($this->compareResultPresentation($bit)) {
                $v = $v ^ $bit;
            }
        }
        $clone->results_presentation = $v;
        return $clone;
    }

    public function getShowPassDetails(): bool
    {
        return $this->compareResultPresentation(self::RESULTPRES_BIT_PASS_DETAILS);
    }
    public function withShowPassDetails(bool $flag): self
    {
        return $this->modifyResultPresentation(self::RESULTPRES_BIT_PASS_DETAILS, $flag);
    }

    public function getShowSolutionDetails(): bool
    {
        return $this->compareResultPresentation(self::RESULTPRES_BIT_SOLUTION_DETAILS);
    }
    public function withShowSolutionDetails(bool $flag): self
    {
        return $this->modifyResultPresentation(self::RESULTPRES_BIT_SOLUTION_DETAILS, $flag);
    }

    public function getShowSolutionPrintview(): bool
    {
        return $this->compareResultPresentation(self::RESULTPRES_BIT_SOLUTION_PRINTVIEW);
    }
    public function withShowSolutionPrintview(bool $flag): self
    {
        return $this->modifyResultPresentation(self::RESULTPRES_BIT_SOLUTION_PRINTVIEW, $flag);
    }

    public function getShowSolutionFeedback(): bool
    {
        return $this->compareResultPresentation(self::RESULTPRES_BIT_SOLUTION_FEEDBACK);
    }
    public function withShowSolutionFeedback(bool $flag): self
    {
        return $this->modifyResultPresentation(self::RESULTPRES_BIT_SOLUTION_FEEDBACK, $flag);
    }

    public function getShowSolutionAnswersOnly(): bool
    {
        return $this->compareResultPresentation(self::RESULTPRES_BIT_SOLUTION_ANSWERS_ONLY);
    }
    public function withShowSolutionAnswersOnly(bool $flag): self
    {
        return $this->modifyResultPresentation(self::RESULTPRES_BIT_SOLUTION_ANSWERS_ONLY, $flag);
    }

    public function getShowSolutionSignature(): bool
    {
        return $this->compareResultPresentation(self::RESULTPRES_BIT_SOLUTION_SIGNATURE);
    }
    public function withShowSolutionSignature(bool $flag): self
    {
        return $this->modifyResultPresentation(self::RESULTPRES_BIT_SOLUTION_SIGNATURE, $flag);
    }

    public function getShowSolutionSuggested(): bool
    {
        return $this->compareResultPresentation(self::RESULTPRES_BIT_SOLUTION_SUGGESTED);
    }
    public function withShowSolutionSuggested(bool $flag): self
    {
        return $this->modifyResultPresentation(self::RESULTPRES_BIT_SOLUTION_SUGGESTED, $flag);
    }

    public function getShowSolutionListComparison(): bool
    {
        return $this->compareResultPresentation(self::RESULTPRES_BIT_SOLUTION_LISTCOMPARE);
    }
    public function withShowSolutionListComparison(bool $flag): self
    {
        return $this->modifyResultPresentation(self::RESULTPRES_BIT_SOLUTION_LISTCOMPARE, $flag);
    }

    public function getShowSolutionListOwnAnswers(): bool
    {
        return $this->compareResultPresentation(self::RESULTPRES_BIT_SOLUTION_LISTOWNANSWERS);
    }
    public function withShowSolutionListOwnAnswers(bool $flag): self
    {
        return $this->modifyResultPresentation(self::RESULTPRES_BIT_SOLUTION_LISTOWNANSWERS, $flag);
    }

    public function getExportSettings(): int
    {
        return $this->exportsettings;
    }
    public function withExportSettings(int $exportsettings): self
    {
        $clone = clone $this;
        $clone->exportsettings = $exportsettings;
        return $clone;
    }
    protected function compareExportSetting(int $bit): bool
    {
        return ($this->exportsettings & $bit) > 0;
    }
    protected function modifyExportSetting(int $bit, bool $flag): self
    {
        $clone = clone $this;
        $v = $clone->exportsettings;

        if ($flag) {
            $v = $v | $bit;
        } else {
            if ($this->compareExportSetting($bit)) {
                $v = $v ^ $bit;
            }
        }
        $clone->exportsettings = $v;
        return $clone;
    }
    public function getExportSettingsSingleChoiceShort(): bool
    {
        return $this->compareExportSetting(self::EXPORT_BIT_SINGLECHOICE_SHORT);
    }
    public function withExportSettingsSingleChoiceShort(bool $flag): self
    {
        return $this->modifyExportSetting(self::EXPORT_BIT_SINGLECHOICE_SHORT, $flag);
    }

    public function getTaxonomyFilterIds(): array
    {
        return $this->taxonomy_filter_ids;
    }
    public function withTaxonomyFilterIds(array $taxonomy_filter_ids): self
    {
        $clone = clone $this;
        $clone->taxonomy_filter_ids = $taxonomy_filter_ids;
        return $clone;
    }
}
