<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
    |                                                                             |
    | This program is free software; you can redistribute it and/or               |
    | modify it under the terms of the GNU General Public License                 |
    | as published by the Free Software Foundation; either version 2              |
    | of the License, or (at your option) any later version.                      |
    |                                                                             |
    | This program is distributed in the hope that it will be useful,             |
    | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
    | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
    | GNU General Public License for more details.                                |
    |                                                                             |
    | You should have received a copy of the GNU General Public License           |
    | along with this program; if not, write to the Free Software                 |
    | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
    +-----------------------------------------------------------------------------+
*/

require_once("./Services/Xml/classes/class.ilSaxParser.php");

/**
* Survey Question Import Parser
*
* @author Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version $Id$
*
* @extends ilSaxParser
* @ingroup ServicesSurvey
*/
class SurveyImportParser extends ilSaxParser
{
    public $path;
    public $depth;
    public $activequestion;
    public $spl;
    public $error_code;
    public $error_line;
    public $error_col;
    public $error_msg;
    public $has_error;
    public $size;
    public $elements;
    public $attributes;
    public $texts;
    public $text_size;
    public $characterbuffer;
    public $activetag;
    public $material;
    public $metadata;
    public $responses;
    public $variables;
    public $response_id;
    public $matrix;
    public $matrixrowattribs;
    public $is_matrix;
    public $adjectives;
    public $spl_exists;
    public $in_survey;
    public $survey;
    public $anonymisation;
    public $surveyaccess;
    public $questions;
    public $original_question_id;
    public $constraints;
    public $textblock;
    public $textblocks;
    public $survey_status;
    public $in_questionblock;
    public $questionblock;
    public $questionblocks;
    public $questionblocktitle;

    /**
    * Constructor
    *
    * @param	string		$a_xml_file		xml file
    *
    * @access	public
    */
    public function __construct($a_spl_id, $a_xml_file = '', $spl_exists = false, $a_mapping = null)
    {
        parent::__construct($a_xml_file);
        $this->spl_id = $a_spl_id;
        $this->has_error = false;
        $this->characterbuffer = "";
        $this->survey_status = 0;
        $this->activetag = "";
        $this->material = array();
        $this->depth = array();
        $this->path = array();
        $this->metadata = array();
        $this->responses = array();
        $this->variables = array();
        $this->response_id = "";
        $this->matrix = array();
        $this->is_matrix = false;
        $this->adjectives = array();
        $this->spl_exists = $spl_exists;
        $this->survey = null;
        $this->in_survey = false;
        $this->anonymisation = 0;
        $this->surveyaccess = "restricted";
        $this->questions = array();
        $this->original_question_id = "";
        $this->constraints = array();
        $this->textblock = "";
        $this->textblocks = array();
        $this->in_questionblock = false;
        $this->questionblocks = array();
        $this->questionblock = array();
        $this->showQuestiontext = 1;
        $this->questionblocktitle = "";
        $this->mapping = $a_mapping;
    }
    
    /**
    * Sets a reference to a survey object
    * @access	public
    */
    public function setSurveyObject($a_svy)
    {
        $this->survey = $a_svy;
    }

    /**
    * set event handler
    * should be overwritten by inherited class
    * @access	private
    */
    public function setHandlers($a_xml_parser)
    {
        xml_set_object($a_xml_parser, $this);
        xml_set_element_handler($a_xml_parser, 'handlerBeginTag', 'handlerEndTag');
        xml_set_character_data_handler($a_xml_parser, 'handlerCharacterData');
    }

    /**
    * start the parser
    */
    public function startParsing()
    {
        parent::startParsing();
    }

    /**
    * parse xml file
    *
    * @access	private
    */
    public function parse($a_xml_parser, $a_fp = null)
    {
        switch ($this->getInputType()) {
            case 'file':

                while ($data = fread($a_fp, 4096)) {
                    $parseOk = xml_parse($a_xml_parser, $data, feof($a_fp));
                }
                break;
                
            case 'string':
                $parseOk = xml_parse($a_xml_parser, $this->getXMLContent());
                break;
        }
        if (!$parseOk
           && (xml_get_error_code($a_xml_parser) != XML_ERROR_NONE)) {
            $this->error_code = xml_get_error_code($a_xml_parser);
            $this->error_line = xml_get_current_line_number($a_xml_parser);
            $this->error_col = xml_get_current_column_number($a_xml_parser);
            $this->error_msg = xml_error_string($a_xml_parser);
            $this->has_error = true;
            return false;
        }
        return true;
    }
    
    public function getParent($a_xml_parser)
    {
        if ($this->depth[$a_xml_parser] > 0) {
            return $this->path[$this->depth[$a_xml_parser] - 1];
        } else {
            return "";
        }
    }
    
    /**
    * handler for begin of element
    */
    public function handlerBeginTag($a_xml_parser, $a_name, $a_attribs)
    {
        $this->depth[$a_xml_parser]++;
        $this->path[$this->depth[$a_xml_parser]] = strtolower($a_name);
        $this->characterbuffer = "";
        $this->activetag = $a_name;
        $this->elements++;
        $this->attributes += count($a_attribs);
        switch ($a_name) {
            case "questionblock":
                $this->in_questionblock = true;
                $this->questionblock = array();
                $this->questionblocktitle = "";
                $this->showQuestiontext = 1;
                foreach ($a_attribs as $attrib => $value) {
                    switch ($attrib) {
                        case "showQuestiontext":
                            $this->showQuestiontext = $value;
                            break;
                    }
                }
                break;
            case "surveyquestions":
                foreach ($a_attribs as $attrib => $value) {
                    switch ($attrib) {
                        case "online":
                            if ($this->spl_id > 0) {
                                include_once "./Modules/SurveyQuestionPool/classes/class.ilObjSurveyQuestionPool.php";
                                $spl = new ilObjSurveyQuestionPool($this->spl_id, false);
                                $spl->setOnline($value);
                                $spl->saveToDb();
                            }
                            break;
                    }
                }
                break;
            case "survey":
                $this->in_survey = true;
                foreach ($a_attribs as $attrib => $value) {
                    switch ($attrib) {
                        case "title":
                            if (is_object($this->survey)) {
                                $this->survey->setTitle($value);
                                $this->survey->update(true);
                            }
                            break;
                    }
                }
                break;
            case "anonymisation":
                foreach ($a_attribs as $attrib => $value) {
                    switch ($attrib) {
                        case "enabled":
                            $this->anonymisation = $value;
                            break;
                    }
                }
                break;
            case "access":
                foreach ($a_attribs as $attrib => $value) {
                    switch ($attrib) {
                        case "type":
                            $this->surveyaccess = $value;
                            break;
                    }
                }
                break;
            case "constraint":
                array_push(
                    $this->constraints,
                    array(
                        "sourceref" => $a_attribs["sourceref"],
                        "destref" => $a_attribs["destref"],
                        "relation" => $a_attribs["relation"],
                        "value" => $a_attribs["value"],
                                            
                        // might be missing in old export files
                        "conjunction" => (int) $a_attribs["conjuction"]
                    )
                );
                break;
            case "question":
                // start with a new survey question
                $type = $a_attribs["type"];
                // patch due to changes in question types
                switch ($type) {
                    case 'SurveyNominalQuestion':
                        $type = 'SurveyMultipleChoiceQuestion';
                        foreach ($a_attribs as $key => $value) {
                            switch ($key) {
                                case "subtype":
                                    if ($value == 1) {
                                        $type = 'SurveySingleChoiceQuestion';
                                    } else {
                                        $type = 'SurveyMultipleChoiceQuestion';
                                    }
                                    break;
                            }
                        }
                        break;
                    case 'SurveyOrdinalQuestion':
                        $type = 'SurveySingleChoiceQuestion';
                        break;
                }
                if (strlen($type)) {
                    include_once "./Modules/SurveyQuestionPool/classes/class.SurveyQuestion.php";
                    if (SurveyQuestion::_includeClass($type)) {
                        $this->activequestion = new $type();
                        
                        // if no pool is given, question will reference survey
                        $q_obj_id = $this->spl_id;
                        if ($this->spl_id < 0) {
                            $q_obj_id = $this->survey->getId();
                        }
                        
                        $this->activequestion->setObjId($q_obj_id);
                    }
                } else {
                    $this->activequestion = null;
                }
                $this->original_question_id = $a_attribs["id"];
                if ($this->in_questionblock) {
                    array_push($this->questionblock, $this->original_question_id);
                }
                if (is_object($this->activequestion)) {
                    foreach ($a_attribs as $key => $value) {
                        switch ($key) {
                            case "title":
                                $this->activequestion->setTitle($value);
                                break;
                            case "subtype":
                                $this->activequestion->setSubtype($value);
                                break;
                            case "obligatory":
                                $this->activequestion->setObligatory($value);
                                break;
                        }
                    }
                }
                break;
            case "material":
                switch ($this->getParent($a_xml_parser)) {
                    case "question":
                    case "questiontext":
                        $this->material = array();
                        break;
                }
                array_push($this->material, array("text" => "", "image" => "", "label" => $a_attribs["label"]));
                break;
            case "matimage":
                case "label":
                    if (array_key_exists("label", $a_attribs)) {
                        if (preg_match("/(il_([0-9]+)_mob_([0-9]+))/", $a_attribs["label"], $matches)) {
                            // import an mediaobject which was inserted using tiny mce
                            if (!is_array($_SESSION["import_mob_xhtml"])) {
                                $_SESSION["import_mob_xhtml"] = array();
                            }
                            array_push($_SESSION["import_mob_xhtml"], array("mob" => $a_attribs["label"], "uri" => $a_attribs["uri"], "type" => $a_attribs["type"], "id" => $a_attribs["id"]));
                        }
                    }
                break;
            case "metadata":
                $this->metadata = array();
                break;
            case "metadatafield":
                array_push($this->metadata, array("label" => "", "entry" => ""));
                break;
            case "matrix":
                $this->is_matrix = true;
                $this->matrix = array();
                break;
            case "matrixrow":
                $this->material = array();
                array_push($this->matrix, "");
                $this->matrixrowattribs = array("id" => $a_attribs["id"], "label" => $a_attribs["label"], "other" => $a_attribs["other"]);
                break;
            case "responses":
                $this->material = array();
                $this->responses = array();
                break;
            case "variables":
                $this->variables = array();
                break;
            case "response_single":
                $this->material = array();
                $this->responses[$a_attribs["id"]] = array("type" => "single", "id" => $a_attribs["id"], "label" => $a_attribs["label"], "other" => $a_attribs["other"], "neutral" => $a_attribs["neutral"], "scale" => $a_attribs["scale"]);
                $this->response_id = $a_attribs["id"];
                break;
            case "response_multiple":
                $this->material = array();
                $this->responses[$a_attribs["id"]] = array("type" => "multiple", "id" => $a_attribs["id"], "label" => $a_attribs["label"], "other" => $a_attribs["other"], "neutral" => $a_attribs["neutral"], "scale" => $a_attribs["scale"]);
                $this->response_id = $a_attribs["id"];
                break;
            case "response_text":
                $this->material = array();
                $this->responses[$a_attribs["id"]] = array("type" => "text", "id" => $a_attribs["id"], "columns" => $a_attribs["columns"], "maxlength" => $a_attribs["maxlength"], "rows" => $a_attribs["rows"], "label" => $a_attribs["label"]);
                $this->response_id = $a_attribs["id"];
                break;
            case "response_num":
                $this->material = array();
                $this->responses[$a_attribs["id"]] = array("type" => "num", "id" => $a_attribs["id"], "format" => $a_attribs["format"], "max" => $a_attribs["max"], "min" => $a_attribs["min"], "size" => $a_attribs["size"], "label" => $a_attribs["label"]);
                $this->response_id = $a_attribs["id"];
                break;
            case "response_time":
                $this->material = array();
                $this->responses[$a_attribs["id"]] = array("type" => "time", "id" => $a_attribs["id"], "format" => $a_attribs["format"], "max" => $a_attribs["max"], "min" => $a_attribs["min"], "label" => $a_attribs["label"]);
                $this->response_id = $a_attribs["id"];
                break;
            case "bipolar_adjectives":
                $this->adjectives = array();
                break;
            case "adjective":
                array_push($this->adjectives, array("label" => $a_attribs["label"], "text" => ""));
                break;
        }
    }

    /**
    * handler for character data
    */
    public function handlerCharacterData($a_xml_parser, $a_data)
    {
        $this->texts++;
        $this->text_size += strlen($a_data);
        $this->characterbuffer .= $a_data;
        $a_data = $this->characterbuffer;
    }

    /**
    * handler for end of element
    */
    public function handlerEndTag($a_xml_parser, $a_name)
    {
        switch ($a_name) {
            case "surveyobject":
                if (is_object($this->survey)) {
                    $this->survey->setOfflineStatus(!$this->survey_status);
                    $this->survey->saveToDb();

                    // write question blocks
                    if (count($this->questionblocks)) {
                        foreach ($this->questionblocks as $data) {
                            $questionblock = $data["questions"];
                            $title = $data["title"];
                            $qblock = array();
                            foreach ($questionblock as $question_id) {
                                array_push($qblock, $this->questions[$question_id]);
                            }
                            $this->survey->createQuestionblock($title, $this->showQuestiontext, false, $qblock);
                        }
                    }
                    
                    // #13878 - write constraints
                    if (count($this->constraints)) {
                        $relations = $this->survey->getAllRelations(true);
                        foreach ($this->constraints as $constraint) {
                            $constraint_id = $this->survey->addConstraint($this->questions[$constraint["destref"]], $relations[$constraint["relation"]]["id"], $constraint["value"], $constraint["conjunction"]);
                            $this->survey->addConstraintToQuestion($this->questions[$constraint["sourceref"]], $constraint_id);
                        }
                    }
                    
                    // write textblocks
                    if (count($this->textblocks)) {
                        foreach ($this->textblocks as $original_id => $textblock) {
                            $this->survey->saveHeading($textblock, $this->questions[$original_id]);
                        }
                    }
                }
                break;
            case "survey":
                $this->in_survey = false;
                if (is_object($this->survey)) {
                    if (strcmp($this->surveyaccess, "free") == 0) {
                        $this->survey->setAnonymize(2);
                    } else {
                        if ($this->anonymisation == 0) {
                            $this->survey->setAnonymize(0);
                        } else {
                            $this->survey->setAnonymize(1);
                        }
                    }
                }
                break;
            case "startingtime":
                if (preg_match("/(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2}):(\d{2}).*/", $this->characterbuffer, $matches)) {
                    if (is_object($this->survey)) {
                        $this->survey->setStartDate(sprintf("%04d%02d%02d%02d%02d%02d", $matches[1], $matches[2], $matches[3], $matches[4], $matches[5], $matches[6]));
                    }
                }
                break;
            case "endingtime":
                if (preg_match("/(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2}):(\d{2}).*/", $this->characterbuffer, $matches)) {
                    if (is_object($this->survey)) {
                        $this->survey->setEndDate(sprintf("%04d%02d%02d%02d%02d%02d", $matches[1], $matches[2], $matches[3], $matches[4], $matches[5], $matches[6]));
                    }
                }
                break;
            case "description":
                if ($this->in_survey) {
                    if (is_object($this->survey)) {
                        $this->survey->setDescription($this->characterbuffer);
                        $this->survey->update(true);
                    }
                } else {
                    if (is_object($this->activequestion)) {
                        $this->activequestion->setDescription($this->characterbuffer);
                    }
                }
                break;
            case "question":
                if (is_object($this->activequestion)) {
                    if (strlen($this->textblock)) {
                        $this->textblocks[$this->original_question_id] = $this->textblock;
                    }
                    $this->activequestion->saveToDb();
                    // duplicate the question for the survey (if pool is to be used)
                    if (is_object($this->survey) &&
                        $this->spl_id > 0) {
                        $question_id = $this->activequestion->duplicate(true, "", "", "", $this->survey->getId());
                    } else {
                        $question_id = $this->activequestion->getId();
                    }
                    if (is_object($this->survey)) { // #15452
                        $this->survey->addQuestion($question_id);
                    }
                    $this->questions[$this->original_question_id] = $question_id;
                    if ($this->mapping) {
                        $this->mapping->addMapping("Modules/Survey", "svy_q", $this->original_question_id, $question_id);
                    }
                    $this->activequestion = null;
                }
                $this->textblock = "";
                break;
            case "author":
                if ($this->in_survey) {
                    if (is_object($this->survey)) {
                        $this->survey->setAuthor($this->characterbuffer);
                    }
                } else {
                    if (is_object($this->activequestion)) {
                        $this->activequestion->setAuthor($this->characterbuffer);
                    }
                }
                break;
            case "mattext":
                $this->material[count($this->material) - 1]["text"] = $this->characterbuffer;
                break;
            case "matimage":
                $this->material[count($this->material) - 1]["image"] = $this->characterbuffer;
                break;
            case "material":
                if ($this->in_survey) {
                    if (strcmp($this->getParent($a_xml_parser), "objectives") == 0) {
                        if (strcmp($this->material[0]["label"], "introduction") == 0) {
                            if (is_object($this->survey)) {
                                $this->survey->setIntroduction($this->material[0]["text"]);
                            }
                        }
                        if (strcmp($this->material[0]["label"], "outro") == 0) {
                            if (is_object($this->survey)) {
                                $this->survey->setOutro($this->material[0]["text"]);
                            }
                        }
                        $this->material = array();
                    }
                } else {
                    if (strcmp($this->getParent($a_xml_parser), "question") == 0) {
                        $this->activequestion->setMaterial($this->material[0]["text"], true, $this->material[0]["label"]);
                    }
                }
                break;
            case "questiontext":
                if (is_object($this->activequestion)) {
                    $questiontext = "";
                    foreach ($this->material as $matarray) {
                        $questiontext .= $matarray["text"];
                    }
                    $this->activequestion->setQuestiontext($questiontext);
                }
                $this->material = array();
                break;
            case "fieldlabel":
                $this->metadata[count($this->metadata) - 1]["label"] = $this->characterbuffer;
                break;
            case "fieldentry":
                $this->metadata[count($this->metadata) - 1]["entry"] = $this->characterbuffer;
                break;
            case "metadata":
                if (strcmp($this->getParent($a_xml_parser), "question") == 0) {
                    if (is_object($this->activequestion)) {
                        $this->activequestion->importAdditionalMetadata($this->metadata);
                    }
                }
                if (strcmp($this->getParent($a_xml_parser), "survey") == 0) {
                    foreach ($this->metadata as $key => $value) {
                        switch ($value["label"]) {
                            case "SCORM":
                                if (strlen($value["entry"])) {
                                    if (is_object($this->survey)) {
                                        include_once "./Services/MetaData/classes/class.ilMDSaxParser.php";
                                        include_once "./Services/MetaData/classes/class.ilMD.php";
                                        $md_sax_parser = new ilMDSaxParser();
                                        $md_sax_parser->setXMLContent($value["entry"]);
                                        $md_sax_parser->setMDObject($tmp = new ilMD($this->survey->getId(), 0, "svy"));
                                        $md_sax_parser->enableMDParsing(true);
                                        $md_sax_parser->startParsing();
                                        $this->survey->MDUpdateListener("General");
                                    }
                                }
                                break;
                            case "display_question_titles":
                                if ($value["entry"] == 1) {
                                    $this->survey->showQuestionTitles();
                                } else {
                                    $this->survey->hideQuestionTitles();
                                }
                                break;
                            case "status":
                                $this->survey_status = $value["entry"];
                                break;
                            case "evaluation_access":
                                $this->survey->setEvaluationAccess($value["entry"]);
                                break;
                            case "pool_usage":
                                $this->survey->setPoolUsage($value["entry"]);
                                break;
                            case "own_results_view":
                                $this->survey->setViewOwnResults($value["entry"]);
                                break;
                            case "own_results_mail":
                                $this->survey->setMailOwnResults($value["entry"]);
                                break;
                            case "confirmation_mail":
                                $this->survey->setMailConfirmation($value["entry"]);
                                break;
                            case "anon_user_list":
                                $this->survey->setAnonymousUserList($value["entry"]);
                                break;
                            case "mode":
                                $this->survey->setMode($value["entry"]);
                                break;
                            case "mode_360_self_eval":
                                $this->survey->set360SelfEvaluation($value["entry"]);
                                break;
                            case "mode_360_self_rate":
                                $this->survey->set360SelfRaters($value["entry"]);
                                break;
                            case "mode_360_self_appr":
                                $this->survey->set360SelfAppraisee($value["entry"]);
                                break;
                            case "mode_360_results":
                                $this->survey->set360Results($value["entry"]);
                                break;
                            case "mode_self_eval_results":
                                $this->survey->setSelfEvaluationResults($value["entry"]);
                                break;
                            case "mode_skill_service":
                                $this->survey->setSkillService($value["entry"]);
                                break;
                        }
                    }
                }
                if (!$this->spl_exists) {
                    if (strcmp($this->getParent($a_xml_parser), "surveyquestions") == 0) {
                        foreach ($this->metadata as $key => $value) {
                            if (strcmp($value["label"], "SCORM") == 0) {
                                if (strlen($value["entry"])) {
                                    if ($this->spl_id > 0) {
                                        include_once "./Services/MetaData/classes/class.ilMDSaxParser.php";
                                        include_once "./Services/MetaData/classes/class.ilMD.php";
                                        include_once "./Modules/SurveyQuestionPool/classes/class.ilObjSurveyQuestionPool.php";
                                        $md_sax_parser = new ilMDSaxParser();
                                        $md_sax_parser->setXMLContent($value["entry"]);
                                        $md_sax_parser->setMDObject($tmp = new ilMD($this->spl_id, 0, "spl"));
                                        $md_sax_parser->enableMDParsing(true);
                                        $md_sax_parser->startParsing();
                                        $spl = new ilObjSurveyQuestionPool($this->spl_id, false);
                                        $spl->MDUpdateListener("General");
                                    }
                                }
                            }
                        }
                    }
                }
                break;
            case "responses":
                if (is_object($this->activequestion)) {
                    $this->activequestion->importResponses($this->responses);
                }
                $this->is_matrix = false;
                break;
            case "variable":
                array_push($this->variables, $this->characterbuffer);
                break;
            case "variables":
                if (is_object($this->activequestion)) {
                    $this->activequestion->importVariables($this->variables);
                }
                break;
            case "response_single":
            case "response_multiple":
            case "response_text":
            case "response_num":
            case "response_time":
                $this->responses[$this->response_id]["material"] = $this->material;
                break;
            case "adjective":
                $this->adjectives[count($this->adjectives) - 1]["text"] = $this->characterbuffer;
                break;
            case "bipolar_adjectives":
                if (is_object($this->activequestion)) {
                    $this->activequestion->importAdjectives($this->adjectives);
                }
                break;
            case "matrixrow":
                $row = "";
                foreach ($this->material as $material) {
                    $row .= $material["text"];
                }
                $this->matrix[count($this->matrix) - 1] = array('title' => $row, 'id' => $this->matrixrowattribs['id'], 'label' => $this->matrixrowattribs['label'], 'other' => $this->matrixrowattribs['other']);
                break;
            case "matrix":
                if (is_object($this->activequestion)) {
                    $this->activequestion->importMatrix($this->matrix);
                }
                break;
            case "textblock":
                $this->textblock = $this->characterbuffer;
                break;
            case "questionblocktitle":
                $this->questionblocktitle = $this->characterbuffer;
                break;
            case "questionblock":
                $this->in_questionblock = false;
                array_push($this->questionblocks, array("title" => $this->questionblocktitle, "questions" => $this->questionblock));
                break;
        }
        $this->depth[$a_xml_parser]--;
    }

    public function getErrorCode()
    {
        return $this->error_code;
    }
  
    public function getErrorLine()
    {
        return $this->error_line;
    }
  
    public function getErrorColumn()
    {
        return $this->error_col;
    }
  
    public function getErrorMessage()
    {
        return $this->error_msg;
    }
  
    public function getFullError()
    {
        return "Error: " . $this->error_msg . " at line:" . $this->error_line . " column:" . $this->error_col;
    }
  
    public function getXMLSize()
    {
        return $this->size;
    }
  
    public function getXMLElements()
    {
        return $this->elements;
    }
  
    public function getXMLAttributes()
    {
        return $this->attributes;
    }
  
    public function getXMLTextSections()
    {
        return $this->texts;
    }
  
    public function getXMLTextSize()
    {
        return $this->text_size;
    }
  
    public function hasError()
    {
        return $this->has_error;
    }
}
