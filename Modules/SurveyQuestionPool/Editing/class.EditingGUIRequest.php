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

namespace ILIAS\SurveyQuestionPool\Editing;

use ILIAS\Repository\BaseGUIRequest;
use ilArrayUtil;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class EditingGUIRequest
{
    use BaseGUIRequest;

    protected array $params;

    public function __construct(
        \ILIAS\HTTP\Services $http,
        \ILIAS\Refinery\Factory $refinery,
        ?array $passed_query_params = null,
        ?array $passed_post_data = null
    ) {
        $this->initRequest(
            $http,
            $refinery,
            $passed_query_params,
            $passed_post_data
        );
    }

    public function getRefId() : int
    {
        return $this->int("ref_id");
    }

    public function getQuestionId() : int
    {
        return $this->int("q_id");
    }

    /** @return int[] */
    public function getQuestionIds() : array
    {
        $ids = $this->intArray("q_id");
        if (count($ids) === 0) {
            $ids = $this->intArray("qid");  // this one is used in SurveyQuestionGUI
        }
        return $ids;
    }

    public function getPreview() : int
    {
        return $this->int("preview");
    }

    public function getSelectedQuestionTypes() : string
    {
        return $this->str("sel_question_types");
    }

    public function getBaseClass() : string
    {
        return $this->str("baseClass");
    }

    /** @return string[] */
    public function getSort() : array
    {
        return $this->strArray("sort");
    }


    public function getPhraseId() : int
    {
        return $this->int("p_id");
    }

    public function getPhraseIds() : array
    {
        return $this->intArray("phrase");
    }

    public function getPhraseTitle() : string
    {
        return $this->str("phrase_title");
    }


    public function getAnswers() : array
    {
        $ans = $this->arrayArray("answers");
        return ilArrayUtil::stripSlashesRecursive($ans);
    }

    public function getColumns() : array
    {
        $ans = $this->arrayArray("columns");
        return ilArrayUtil::stripSlashesRecursive($ans);
    }

    public function getRows() : array
    {
        $ans = $this->arrayArray("rows");
        return ilArrayUtil::stripSlashesRecursive($ans);
    }

    public function getNeutralScale() : string
    {
        return $this->str("answers_neutral_scale");
    }

    public function getNeutral() : string
    {
        $ans = $this->strArray("answers");
        return $ans["neutral"];
    }

    public function getColumnNeutralScale() : string
    {
        return $this->str("columns_neutral_scale");
    }

    public function getNewLinkType() : string
    {
        return $this->str("internalLinkType");
    }

    public function getNewForSurvey() : int
    {
        return $this->int("new_for_survey");
    }

    public function getNewType() : string
    {
        return $this->str("new_type");
    }

    public function getLinkSourceId() : int
    {
        return $this->int("source_id");
    }

    public function getLinkItemId($type) : int
    {
        return $this->int($type);
    }

    public function getReturn() : bool
    {
        return (bool) $this->int("rtrn");
    }

    /** @return string[] */
    public function getFiles() : array
    {
        return $this->strArray("file");
    }

    public function getMaterialIndexes() : array
    {
        return $this->intArray("idx");
    }

    public function getPercentRow() : int
    {
        return $this->int("percent_row");
    }

    public function getPercentColumns() : int
    {
        return $this->int("percent_columns");
    }

    public function getPercentBipAdj1() : int
    {
        return $this->int("percent_bipolar_adjective1");
    }

    public function getPercentBipAdj2() : int
    {
        return $this->int("percent_bipolar_adjective2");
    }

    public function getPercentNeutral() : int
    {
        return $this->int("percent_neutral");
    }

    public function getObligatory() : array
    {
        return $this->intArray("obligatory");
    }
}
