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

namespace ILIAS\Wiki\Editing;

use ILIAS\Repository;

class EditingGUIRequest
{
    use Repository\BaseGUIRequest;

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

    public function getOldNr() : int
    {
        return $this->int("old_nr");
    }

    public function getUserId() : int
    {
        return $this->int("user");
    }

    public function getWikiPageId() : int
    {
        return $this->int("wpg_id");
    }

    /** @return int[] */
    public function getWikiPageIds() : array
    {
        return $this->intArray("obj_id");
    }

    public function getNotification() : int
    {
        return $this->int("ntf");
    }

    public function getAssignmentId() : int
    {
        return $this->int("ass");
    }

    public function getWithComments() : bool
    {
        return $this->int("with_comments");
    }

    public function getPage() : string
    {
        return (string) $this->raw("page");
    }

    public function getFromPage() : string
    {
        return (string) $this->raw("from_page");
    }

    public function getNewType() : string
    {
        return $this->str("new_type");
    }

    public function getSearchString() : string
    {
        return $this->str("srcstring");
    }

    public function getSearchTerm() : string
    {
        return trim($this->str("search_term"));
    }

    public function getTerm() : string
    {
        return trim($this->str("term"));
    }

    /** @return int[] */
    public function getUserIds() : array
    {
        return $this->intArray("user_id");
    }

    /** @return string[] */
    public function getMarks() : array
    {
        return $this->strArray("mark");
    }

    /** @return string[] */
    public function getComments() : array
    {
        return $this->strArray("lcomment");
    }

    /** @return string[] */
    public function getStatus() : array
    {
        return $this->strArray("status");
    }

    public function getImportantPageId() : int
    {
        return $this->int("imp_page_id");
    }

    /** @return int[] */
    public function getImportantPageIds() : array
    {
        return $this->intArray("imp_page_id");
    }

    /** @return int[] */
    public function getPrintOrdering() : array
    {
        return $this->intArray("wordr");
    }

    public function getStyleId() : int
    {
        return $this->int("style_id");
    }

    public function getImportantPageOrdering() : array
    {
        return $this->intArray("ord");
    }

    public function getImportantPageIndentation() : array
    {
        return $this->intArray("indent");
    }

    public function getFormat() : string
    {
        return trim($this->str("format"));
    }

    public function getPageTemplateId() : int
    {
        $templ_id = $this->int("page_templ");
        if ($templ_id === 0) {
            $templ_id = $this->int("templ_page_id");
        }
        return $templ_id;
    }

    public function getObjId() : int
    {
        return $this->int("obj_id");
    }

    public function getSelectedPrintType() : string
    {
        return $this->str("sel_type");
    }

    /** @return int[] */
    public function getIds() : array
    {
        return $this->intArray("id");
    }

    /** @return int[] */
    public function getAllIds() : array
    {
        return $this->intArray("all_ids");
    }

    /** @return int[] */
    public function getNewPages() : array
    {
        return $this->intArray("new_pages");
    }

    /** @return int[] */
    public function getAddToPage() : array
    {
        return $this->intArray("add_to_page");
    }

    public function getEmptyPageTemplate() : int
    {
        return $this->int("empty_page_templ");
    }

    public function getStatFig() : int
    {
        return $this->int("fig");
    }

    public function getStatTfr() : string
    {
        return $this->str("tfr");
    }

    public function getStatScp() : int
    {
        return $this->int("scp");
    }
}
