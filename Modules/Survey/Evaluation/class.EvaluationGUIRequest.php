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

namespace ILIAS\Survey\Evaluation;

use ILIAS\Repository\BaseGUIRequest;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class EvaluationGUIRequest
{
    use BaseGUIRequest;

    protected array $params;

    public function __construct(
        \ILIAS\HTTP\Services $http,
        \ILIAS\Refinery\Factory $refinery,
        ?array $get = null,
        ?array $post = null
    ) {
        $this->initRequest(
            $http,
            $refinery,
            $get,
            $post
        );
    }

    public function getShowTable() : bool
    {
        $vw = $this->str("vw");
        return $vw === "" || is_int(strpos($vw, "t"));
    }

    public function getShowChart() : bool
    {
        $vw = $this->str("vw");
        return $vw === "" || is_int(strpos($vw, "c"));
    }

    public function getVW() : string
    {
        return $this->str("vw");
    }

    public function getShowAbsolute() : bool
    {
        $cp = $this->str("cp");
        return $cp === "" || is_int(strpos($cp, "a"));
    }

    public function getShowPercentage() : bool
    {
        $cp = $this->str("cp");
        return $cp === "" || is_int(strpos($cp, "p"));
    }

    public function getCP() : string
    {
        return $this->str("cp");
    }

    public function getAppraiseeId() : int
    {
        return $this->int("appr_id");
    }

    public function getRaterId() : string
    {
        return $this->str("rater_id");
    }

    public function getRefId() : int
    {
        return $this->int("ref_id");
    }

    public function getCompEvalMode() : string
    {
        return $this->str("comp_eval_mode");
    }

    public function getSurveyCode() : string
    {
        return $this->str("surveycode");
    }

    public function getExportLabel() : string
    {
        return $this->str("export_label");
    }

    public function getExportFormat() : string
    {
        return $this->str("export_format");
    }

    public function getPrintSelection() : string
    {
        return $this->str("print_selection");
    }

    public function getQuestionIds() : array
    {
        return $this->intArray("qids");
    }

    public function getActiveIds() : array
    {
        return $this->intArray("active_ids");
    }
}
