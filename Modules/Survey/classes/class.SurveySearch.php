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

/**
 * Class for search actions in ILIAS survey tool
 * The SurveySearch class defines and encapsulates basic methods and attributes
 * to search the ILIAS survey tool for questions.
 * @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
 */
class SurveySearch
{
    public const CONCAT_AND = 0;
    public const CONCAT_OR = 1;

    protected ilRbacSystem $rbacsystem;

    // An array containing all search terms
    public array $search_terms;

    // self::CONCAT_AND | self::CONCAT_OR
    public int $concatenation;

    // database field to restrict the search results
    public string $search_field;

    // A question type to restrict the search results
    public string $search_type;

    // array containing the results of a search
    public array $search_results;

    public ilDBInterface $ilDB;


    public function __construct(
        string $search_text = "",
        int $concatenation = self::CONCAT_AND,
        string $search_field = "all",
        string $search_type = "all"
    ) {
        global $DIC;

        $this->rbacsystem = $DIC->rbac()->system();
        $ilDB = $DIC->database();

        $this->ilDB = $ilDB;

        $this->search_terms = explode(" +", $search_text);
        $this->concatenation = $concatenation;
        $this->search_field = $search_field;
        $this->search_type = $search_type;
        $this->search_results = array();
    }

    // perform search and store results in $this->search_results
    public function search() : void
    {
        $ilDB = $this->ilDB;
        
        $where = "";
        $fields = array();
        if (strcmp($this->search_type, "all") !== 0) {
            $where = "svy_qtype.type_tag = " . $ilDB->quote($this->search_type, 'text');
        }
        foreach ($this->search_terms as $term) {
            $fields[(string) $term] = array();
            switch ($this->search_field) {
                case "all":
                    $fields[(string) $term][] = $ilDB->like("svy_question.title", 'text', "%" . $term . "%");
                    $fields[(string) $term][] = $ilDB->like("svy_question.description", 'text', "%" . $term . "%");
                    $fields[(string) $term][] = $ilDB->like("svy_question.author", 'text', "%" . $term . "%");
                    $fields[(string) $term][] = $ilDB->like("svy_question.questiontext", 'text', "%" . $term . "%");
                    break;
                default:
                    $fields[(string) $term][] = $ilDB->like("svy_question." . $this->search_field, 'text', "%" . $term . "%");
                    break;
            }
        }
        $cumulated_fields = array();
        foreach ($fields as $params) {
            $cumulated_fields[] = "(" . implode(" OR ", $params) . ")";
        }
        $str_where = "";
        if ($this->concatenation === self::CONCAT_AND) {
            $str_where = "(" . implode(" AND ", $cumulated_fields) . ")";
        } else {
            $str_where = "(" . implode(" OR ", $cumulated_fields) . ")";
        }
        if ($str_where) {
            $str_where = " AND $str_where";
        }
        if ($where) {
            $str_where .= " AND (" . $where . ")";
        }
        $result = $ilDB->query("SELECT svy_question.*, svy_qtype.type_tag, object_reference.ref_id FROM " .
            "svy_question, svy_qtype, object_reference WHERE svy_question.questiontype_fi = svy_qtype.questiontype_id " .
            "AND svy_question.original_id IS NULL AND svy_question.obj_fi = object_reference.obj_id AND " .
            "svy_question.obj_fi > 0$str_where");
        $result_array = array();
        $rbacsystem = $this->rbacsystem;
        if ($result->numRows() > 0) {
            while ($row = $ilDB->fetchAssoc($result)) {
                if (((int) $row["complete"]) === 1 && $rbacsystem->checkAccess('write', $row["ref_id"])) {
                    $result_array[] = $row;
                }
            }
        }
        $this->search_results = $result_array;
    }
}
