<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Class for search actions in ILIAS survey tool
 *
 * The SurveySearch class defines and encapsulates basic methods and attributes
 * to search the ILIAS survey tool for questions.
 *
 * @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
 */
class SurveySearch
{
    /**
     * @var ilRbacSystem
     */
    protected $rbacsystem;

    const CONCAT_AND = 0;
    const CONCAT_OR = 1;
    
    /**
    * Search terms
    *
    * An array containing all search terms
    *
    * @var array
    */
    public $search_terms;

    /**
    * Concatenation
    *
    * The concatenation type of the search terms
    *
    * @var integer
    */
    public $concatenation;

    /**
    * Search field
    *
    * A database field to restrict the search results
    *
    * @var string
    */
    public $search_field;

    /**
    * Search type
    *
    * A question type to restrict the search results
    *
    * @var string
    */
    public $search_type;

    /**
    * Search results
    *
    * An array containing the results of a search
    *
    * @var array
    */
    public $search_results;

    /**
    * The reference to the ILIAS database class
    *
    * The reference to the ILIAS database class
    *
    * @var object
    */
    public $ilDB;


    /**
    * SurveySearch constructor
    *
    * The constructor takes possible arguments an creates an instance of the SurveySearch object.
    *
    * @param string $title A title string to describe the question
    * @param string $description A description string to describe the question
    * @param string $author A string containing the name of the questions author
    * @param integer $owner A numerical ID to identify the owner/creator
    * @access public
    */
    public function __construct($search_text = "", $concatenation = self::CONCAT_AND, $search_field = "all", $search_type = "all")
    {
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
    
    /**
    * Executes a search
    *
    * Executes a search
    *
    * @access public
    */
    public function search()
    {
        $ilDB = $this->ilDB;
        
        $where = "";
        $fields = array();
        if (strcmp($this->search_type, "all") != 0) {
            $where = "svy_qtype.type_tag = " . $ilDB->quote($this->search_type, 'text');
        }
        foreach ($this->search_terms as $term) {
            switch ($this->search_field) {
                case "all":
                    $fields["$term"] = array();
                    array_push($fields["$term"], $ilDB->like("svy_question.title", 'text', "%" . $term . "%"));
                    array_push($fields["$term"], $ilDB->like("svy_question.description", 'text', "%" . $term . "%"));
                    array_push($fields["$term"], $ilDB->like("svy_question.author", 'text', "%" . $term . "%"));
                    array_push($fields["$term"], $ilDB->like("svy_question.questiontext", 'text', "%" . $term . "%"));
                    break;
                default:
                    $fields["$term"] = array();
                    array_push($fields["$term"], $ilDB->like("svy_question." . $this->search_field, 'text', "%" . $term . "%"));
                    break;
            }
        }
        $cumulated_fields = array();
        foreach ($fields as $params) {
            array_push($cumulated_fields, "(" . join(" OR ", $params) . ")");
        }
        $str_where = "";
        if ($this->concatenation == self::CONCAT_AND) {
            $str_where = "(" . join(" AND ", $cumulated_fields) . ")";
        } else {
            $str_where = "(" . join(" OR ", $cumulated_fields) . ")";
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
                if (($row["complete"] == 1) and ($rbacsystem->checkAccess('write', $row["ref_id"]))) {
                    array_push($result_array, $row);
                }
            }
        }
        $this->search_results = &$result_array;
    }
}
