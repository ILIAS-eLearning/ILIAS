<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Test results import parser
*
* @author Helmut Schottmüller <ilias@aurealis.de>
* @version $Id$
* @ingroup ModulesTest
*/
include_once("./Services/Xml/classes/class.ilSaxParser.php");

class ilTestResultsImportParser extends ilSaxParser
{
    private $tst_obj;
    private $table;
    private $active_id_mapping;
    private $question_id_mapping;
    private $user_criteria_field;
    private $user_criteria_type;
    private $user_criteria_checked = false;
    
    protected $src_pool_def_id_mapping;
    
    /**
    * Constructor
    */
    public function __construct($a_xml_file, &$test_object)
    {
        parent::__construct($a_xml_file, true);
        $this->tst_obj = &$test_object;
        $this->table = '';
        $this->active_id_mapping = array();
        $this->question_id_mapping = array();
        $this->user_criteria_checked = false;
        $this->src_pool_def_id_mapping = array();
    }

    /**
     * @return array
     */
    public function getQuestionIdMapping()
    {
        return $this->question_id_mapping;
    }

    /**
     * @param array $question_id_mapping
     */
    public function setQuestionIdMapping($question_id_mapping)
    {
        $this->question_id_mapping = $question_id_mapping;
    }

    /**
     * @return array
     */
    public function getSrcPoolDefIdMapping()
    {
        return $this->src_pool_def_id_mapping;
    }

    /**
     * @param array $src_pool_def_id_mapping
     */
    public function setSrcPoolDefIdMapping($src_pool_def_id_mapping)
    {
        $this->src_pool_def_id_mapping = $src_pool_def_id_mapping;
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
        xml_set_character_data_handler($a_xml_parser, 'handlerParseCharacterData');
    }

    /**
    * handler for begin of element parser
    */
    public function handlerBeginTag($a_xml_parser, $a_name, $a_attribs)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        
        $this->sametag = false;
        $this->characterbuffer = "";
        $this->depth[$a_xml_parser]++;
        $this->path[$this->depth[$a_xml_parser]] = strtolower($a_name);
        $this->qti_element = $a_name;
        
        switch (strtolower($a_name)) {
            case "results":
                break;
            case "row":
                switch ($this->table) {
                    case 'tst_active':
                        if (!$this->user_criteria_checked) {
                            $this->user_criteria_checked = true;
                            if ($ilDB->tableColumnExists('usr_data', $a_attribs['user_criteria'])) {
                                include_once './Services/Database/classes/class.ilDBAnalyzer.php';
                                $analyzer = new ilDBAnalyzer();
                                $info = $analyzer->getFieldInformation('usr_data');
                                $this->user_criteria_field = $a_attribs['user_criteria'];
                                $this->user_criteria_type = $info[$a_attribs['user_criteria']]['type'];
                            }
                        }
                        $usr_id = ANONYMOUS_USER_ID;
                        if (strlen($this->user_criteria_field)) {
                            $result = $ilDB->queryF(
                                "SELECT usr_id FROM usr_data WHERE " . $this->user_criteria_field . " =  %s",
                                array($this->user_criteria_type),
                                array($a_attribs[$this->user_criteria_field])
                            );
                            if ($result->numRows()) {
                                $row = $ilDB->fetchAssoc($result);
                                $usr_id = $row['usr_id'];
                            }
                        }
                        $next_id = $ilDB->nextId('tst_active');
                        
                        $ilDB->insert('tst_active', array(
                            'active_id' => array('integer', $next_id),
                            'user_fi' => array('integer', $usr_id),
                            'anonymous_id' => array('text', strlen($a_attribs['anonymous_id']) ? $a_attribs['anonymous_id'] : null),
                            'test_fi' => array('integer', $this->tst_obj->getTestId()),
                            'lastindex' => array('integer', $a_attribs['lastindex']),
                            'tries' => array('integer', $a_attribs['tries']),
                            'submitted' => array('integer', $a_attribs['submitted']),
                            'submittimestamp' => array('timestamp', strlen($a_attribs['submittimestamp']) ? $a_attribs['submittimestamp'] : null),
                            'tstamp' => array('integer', $a_attribs['tstamp']),
                            'importname' => array('text', $a_attribs['fullname']),
                            'last_finished_pass' => array('integer', $this->fetchLastFinishedPass($a_attribs)),
                            'last_started_pass' => array('integer', $this->fetchLastStartedPass($a_attribs)),
                            'answerstatusfilter' => array('integer', $this->fetchAttribute($a_attribs, 'answer_status_filter')),
                            'objective_container' => array('integer', $this->fetchAttribute($a_attribs, 'objective_container'))
                        ));
                        $this->active_id_mapping[$a_attribs['active_id']] = $next_id;
                        break;
                    case 'tst_test_rnd_qst':
                        $nextId = $ilDB->nextId('tst_test_rnd_qst');
                        $newActiveId = $this->active_id_mapping[$a_attribs['active_fi']];
                        $newQuestionId = $this->question_id_mapping[$a_attribs['question_fi']];
                        $newSrcPoolDefId = $this->src_pool_def_id_mapping[$a_attribs['src_pool_def_fi']];
                        $ilDB->insert('tst_test_rnd_qst', array(
                            'test_random_question_id' => array('integer', $nextId),
                            'active_fi' => array('integer', $newActiveId),
                            'question_fi' => array('integer', $newQuestionId),
                            'sequence' => array('integer', $a_attribs['sequence']),
                            'pass' => array('integer', $a_attribs['pass']),
                            'tstamp' => array('integer', $a_attribs['tstamp']),
                            'src_pool_def_fi' => array('integer', $newSrcPoolDefId)
                        ));
                        break;
                    case 'tst_pass_result':
                        $affectedRows = $ilDB->manipulateF(
                            "INSERT INTO tst_pass_result (active_fi, pass, points, maxpoints, questioncount, answeredquestions, workingtime, tstamp) VALUES (%s,%s,%s,%s,%s,%s,%s,%s)",
                            array(
                                'integer',
                                'integer',
                                'float',
                                'float',
                                'integer',
                                'integer',
                                'integer',
                                'integer'
                            ),
                            array(
                                $this->active_id_mapping[$a_attribs['active_fi']],
                                strlen($a_attribs['pass']) ? $a_attribs['pass'] : 0,
                                ($a_attribs["points"]) ? $a_attribs["points"] : 0,
                                ($a_attribs["maxpoints"]) ? $a_attribs["maxpoints"] : 0,
                                $a_attribs["questioncount"],
                                $a_attribs["answeredquestions"],
                                ($a_attribs["workingtime"]) ? $a_attribs["workingtime"] : 0,
                                $a_attribs["tstamp"]
                            )
                        );
                        break;
                    case 'tst_result_cache':
                        $affectedRows = $ilDB->manipulateF(
                            "INSERT INTO tst_result_cache (active_fi, pass, max_points, reached_points, mark_short, mark_official, passed, failed, tstamp) VALUES (%s,%s,%s,%s,%s,%s,%s,%s,%s)",
                            array(
                                'integer',
                                'integer',
                                'float',
                                'float',
                                'text',
                                'text',
                                'integer',
                                'integer',
                                'integer'
                            ),
                            array(
                                $this->active_id_mapping[$a_attribs['active_fi']],
                                strlen($a_attribs['pass']) ? $a_attribs['pass'] : 0,
                                ($a_attribs["max_points"]) ? $a_attribs["max_points"] : 0,
                                ($a_attribs["reached_points"]) ? $a_attribs["reached_points"] : 0,
                                strlen($a_attribs["mark_short"]) ? $a_attribs["mark_short"] : " ",
                                strlen($a_attribs["mark_official"]) ? $a_attribs["mark_official"] : " ",
                                ($a_attribs["passed"]) ? 1 : 0,
                                ($a_attribs["failed"]) ? 1 : 0,
                                $a_attribs["tstamp"]
                            )
                        );
                        break;
                    case 'tst_sequence':
                        $affectedRows = $ilDB->insert("tst_sequence", array(
                            "active_fi" => array("integer", $this->active_id_mapping[$a_attribs['active_fi']]),
                            "pass" => array("integer", $a_attribs['pass']),
                            "sequence" => array("clob", $a_attribs['sequence']),
                            "postponed" => array("text", (strlen($a_attribs['postponed'])) ? $a_attribs['postponed'] : null),
                            "hidden" => array("text", (strlen($a_attribs['hidden'])) ? $a_attribs['hidden'] : null),
                            "tstamp" => array("integer", $a_attribs['tstamp'])
                        ));
                        break;
                    case 'tst_solutions':
                        $next_id = $ilDB->nextId('tst_solutions');
                        $affectedRows = $ilDB->insert("tst_solutions", array(
                            "solution_id" => array("integer", $next_id),
                            "active_fi" => array("integer", $this->active_id_mapping[$a_attribs['active_fi']]),
                            "question_fi" => array("integer", $this->question_id_mapping[$a_attribs['question_fi']]),
                            "value1" => array("clob", (strlen($a_attribs['value1'])) ? $a_attribs['value1'] : null),
                            "value2" => array("clob", (strlen($a_attribs['value2'])) ? $a_attribs['value2'] : null),
                            "pass" => array("integer", $a_attribs['pass']),
                            "tstamp" => array("integer", $a_attribs['tstamp'])
                        ));
                        break;
                    case 'tst_test_result':
                        $next_id = $ilDB->nextId('tst_test_result');
                        $affectedRows = $ilDB->manipulateF(
                            "INSERT INTO tst_test_result (test_result_id, active_fi, question_fi, points, pass, manual, tstamp) VALUES (%s, %s, %s, %s, %s, %s, %s)",
                            array('integer', 'integer','integer', 'float', 'integer', 'integer','integer'),
                            array($next_id, $this->active_id_mapping[$a_attribs['active_fi']], $this->question_id_mapping[$a_attribs['question_fi']], $a_attribs['points'], $a_attribs['pass'], (strlen($a_attribs['manual'])) ? $a_attribs['manual'] : 0, $a_attribs['tstamp'])
                        );
                        break;
                    case 'tst_times':
                        $next_id = $ilDB->nextId('tst_times');
                        $affectedRows = $ilDB->manipulateF(
                            "INSERT INTO tst_times (times_id, active_fi, started, finished, pass, tstamp) VALUES (%s, %s, %s, %s, %s, %s)",
                            array('integer', 'integer', 'timestamp', 'timestamp', 'integer', 'integer'),
                            array($next_id, $this->active_id_mapping[$a_attribs['active_fi']], $a_attribs['started'], $a_attribs['finished'], $a_attribs['pass'], $a_attribs['tstamp'])
                        );
                        break;
                }
                break;
            default:
                $this->table = $a_name;
                break;
        }
    }

    /**
    * handler for end of element
    */
    public function handlerEndTag($a_xml_parser, $a_name)
    {
        switch (strtolower($a_name)) {
            case "tst_active":
                global $DIC;
                $ilLog = $DIC['ilLog'];
                $ilLog->write("active id mapping: " . print_r($this->active_id_mapping, true));
                break;
            case "tst_test_question":
                global $DIC;
                $ilLog = $DIC['ilLog'];
                $ilLog->write("question id mapping: " . print_r($this->question_id_mapping, true));
                break;
        }
    }

    /**
      * handler for character data
      */
    public function handlerParseCharacterData($a_xml_parser, $a_data)
    {
        // do nothing
    }
    
    private function fetchAttribute($attributes, $name)
    {
        if (isset($attributes[$name])) {
            return $attributes[$name];
        }
        
        return null;
    }
    
    private function fetchLastFinishedPass($attribs)
    {
        if (isset($attribs['last_finished_pass'])) {
            return $attribs['last_finished_pass'];
        }
        
        if ($attribs['tries'] > 0) {
            return $attribs['tries'] - 1;
        }
        
        return null;
    }
    
    private function fetchLastStartedPass($attribs)
    {
        if (isset($attribs['last_started_pass'])) {
            return $attribs['last_started_pass'];
        }
        
        if ($attribs['tries'] > 0) {
            return $attribs['tries'] - 1;
        }
        
        return null;
    }
}
