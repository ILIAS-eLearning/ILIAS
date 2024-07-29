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

declare(strict_types=1);

use ILIAS\Test\Logging\TestLogger;

class ilTestResultsImportParser extends ilSaxParser
{
    private $table;
    private $active_id_mapping;
    private $question_id_mapping;
    private string $user_criteria_field = '';
    private string $user_criteria_type = '';
    private bool $user_criteria_checked = false;

    protected $src_pool_def_id_mapping;

    /**
    * Constructor
    */
    public function __construct(
        ?string $a_xml_file,
        private ilObjTest $test_obj,
        private ilDBInterface $db,
        private TestLogger $log
    ) {
        parent::__construct($a_xml_file, true);
        $this->table = '';
        $this->active_id_mapping = [];
        $this->question_id_mapping = [];
        $this->user_criteria_checked = false;
        $this->src_pool_def_id_mapping = [];
    }

    /**
     * @return array
     */
    public function getQuestionIdMapping(): array
    {
        return $this->question_id_mapping;
    }

    /**
     * @param array $question_id_mapping
     */
    public function setQuestionIdMapping(array $question_id_mapping): void
    {
        $this->question_id_mapping = $question_id_mapping;
    }

    /**
     * @return array
     */
    public function getSrcPoolDefIdMapping(): array
    {
        return $this->src_pool_def_id_mapping;
    }

    /**
     * @param array $src_pool_def_id_mapping
     */
    public function setSrcPoolDefIdMapping(array $src_pool_def_id_mapping): void
    {
        $this->src_pool_def_id_mapping = $src_pool_def_id_mapping;
    }

    /**
    * set event handler
    * should be overwritten by inherited class
    * @access	private
    */
    public function setHandlers($a_xml_parser): void
    {
        xml_set_object($a_xml_parser, $this);
        xml_set_element_handler($a_xml_parser, 'handlerBeginTag', 'handlerEndTag');
        xml_set_character_data_handler($a_xml_parser, 'handlerParseCharacterData');
    }

    /**
    * handler for begin of element parser
    */
    public function handlerBeginTag($a_xml_parser, $a_name, $a_attribs): void
    {
        switch (strtolower($a_name)) {
            case "results":
                break;
            case "row":
                switch ($this->table) {
                    case 'tst_active':
                        if (!$this->user_criteria_checked) {
                            $this->user_criteria_checked = true;
                            if (isset($a_attribs['user_criteria'])
                                && $this->db->tableColumnExists('usr_data', $a_attribs['user_criteria'])) {
                                $analyzer = new ilDBAnalyzer();
                                $info = $analyzer->getFieldInformation('usr_data');
                                $this->user_criteria_field = $a_attribs['user_criteria'];
                                $this->user_criteria_type = $info[$a_attribs['user_criteria']]['type'];
                            }
                        }
                        $usr_id = ANONYMOUS_USER_ID;
                        if ($this->user_criteria_field !== '') {
                            $result = $this->db->queryF(
                                'SELECT usr_id FROM usr_data WHERE '
                                    . $this->user_criteria_field . ' =  %s',
                                [$this->user_criteria_type],
                                [$a_attribs[$this->user_criteria_field]]
                            );
                            if ($result->numRows()) {
                                $row = $this->db->fetchAssoc($result);
                                $usr_id = $row['usr_id'];
                            }
                        }
                        $next_id = $this->db->nextId('tst_active');

                        $this->db->insert('tst_active', [
                            'active_id' => ['integer', $next_id],
                            'user_fi' => ['integer', $usr_id],
                            'anonymous_id' => ['text', strlen($a_attribs['anonymous_id']) ? $a_attribs['anonymous_id'] : null],
                            'test_fi' => ['integer', $this->test_obj->getTestId()],
                            'lastindex' => ['integer', $a_attribs['lastindex']],
                            'tries' => ['integer', $a_attribs['tries']],
                            'submitted' => ['integer', $a_attribs['submitted']],
                            'submittimestamp' => ['timestamp', strlen($a_attribs['submittimestamp']) ? $a_attribs['submittimestamp'] : null],
                            'tstamp' => ['integer', $a_attribs['tstamp']],
                            'importname' => ['text', $a_attribs['fullname']],
                            'last_finished_pass' => ['integer', $this->fetchLastFinishedPass($a_attribs)],
                            'last_started_pass' => ['integer', $this->fetchLastStartedPass($a_attribs)],
                            'answerstatusfilter' => ['integer', $this->fetchAttribute($a_attribs, 'answer_status_filter')],
                            'objective_container' => ['integer', $this->fetchAttribute($a_attribs, 'objective_container')]
                        ]);
                        $this->active_id_mapping[$a_attribs['active_id']] = $next_id;
                        break;
                    case 'tst_test_rnd_qst':
                        $nextId = $this->db->nextId('tst_test_rnd_qst');
                        $newActiveId = $this->active_id_mapping[$a_attribs['active_fi']];
                        $newQuestionId = $this->question_id_mapping[$a_attribs['question_fi']];
                        $newSrcPoolDefId = $this->src_pool_def_id_mapping[$a_attribs['src_pool_def_fi']];
                        $this->db->insert('tst_test_rnd_qst', [
                            'test_random_question_id' => ['integer', $nextId],
                            'active_fi' => ['integer', $newActiveId],
                            'question_fi' => ['integer', $newQuestionId],
                            'sequence' => ['integer', $a_attribs['sequence']],
                            'pass' => ['integer', $a_attribs['pass']],
                            'tstamp' => ['integer', $a_attribs['tstamp']],
                            'src_pool_def_fi' => ['integer', $newSrcPoolDefId]
                        ]);
                        break;
                    case 'tst_pass_result':
                        $affectedRows = $this->db->manipulateF(
                            "INSERT INTO tst_pass_result (active_fi, pass, points, maxpoints, questioncount, answeredquestions, workingtime, tstamp) VALUES (%s,%s,%s,%s,%s,%s,%s,%s)",
                            [
                                'integer',
                                'integer',
                                'float',
                                'float',
                                'integer',
                                'integer',
                                'integer',
                                'integer'
                            ],
                            [
                                $this->active_id_mapping[$a_attribs['active_fi']],
                                strlen($a_attribs['pass']) ? $a_attribs['pass'] : 0,
                                ($a_attribs["points"]) ? $a_attribs["points"] : 0,
                                ($a_attribs["maxpoints"]) ? $a_attribs["maxpoints"] : 0,
                                $a_attribs["questioncount"],
                                $a_attribs["answeredquestions"],
                                ($a_attribs["workingtime"]) ? $a_attribs["workingtime"] : 0,
                                $a_attribs["tstamp"]
                            ]
                        );
                        break;
                    case 'tst_result_cache':
                        $affectedRows = $this->db->manipulateF(
                            "INSERT INTO tst_result_cache (active_fi, pass, max_points, reached_points, mark_short, mark_official, passed, failed, tstamp) VALUES (%s,%s,%s,%s,%s,%s,%s,%s,%s)",
                            [
                                'integer',
                                'integer',
                                'float',
                                'float',
                                'text',
                                'text',
                                'integer',
                                'integer',
                                'integer'
                            ],
                            [
                                $this->active_id_mapping[$a_attribs['active_fi']],
                                strlen($a_attribs['pass']) ? $a_attribs['pass'] : 0,
                                ($a_attribs["max_points"]) ? $a_attribs["max_points"] : 0,
                                ($a_attribs["reached_points"]) ? $a_attribs["reached_points"] : 0,
                                strlen($a_attribs["mark_short"]) ? $a_attribs["mark_short"] : " ",
                                strlen($a_attribs["mark_official"]) ? $a_attribs["mark_official"] : " ",
                                ($a_attribs["passed"]) ? 1 : 0,
                                ($a_attribs["failed"]) ? 1 : 0,
                                $a_attribs["tstamp"]
                            ]
                        );
                        break;
                    case 'tst_sequence':
                        $affectedRows = $this->db->insert("tst_sequence", [
                            "active_fi" => ["integer", $this->active_id_mapping[$a_attribs['active_fi']]],
                            "pass" => ["integer", $a_attribs['pass']],
                            "sequence" => ["clob", $a_attribs['sequence']],
                            "postponed" => ["text", (strlen($a_attribs['postponed'])) ? $a_attribs['postponed'] : null],
                            "hidden" => ["text", (strlen($a_attribs['hidden'])) ? $a_attribs['hidden'] : null],
                            "tstamp" => ["integer", $a_attribs['tstamp']]
                        ]);
                        break;
                    case 'tst_solutions':
                        $next_id = $this->db->nextId('tst_solutions');
                        $affectedRows = $this->db->insert("tst_solutions", [
                            "solution_id" => ["integer", $next_id],
                            "active_fi" => ["integer", $this->active_id_mapping[$a_attribs['active_fi']]],
                            "question_fi" => ["integer", $this->question_id_mapping[$a_attribs['question_fi']]],
                            "value1" => ["clob", (strlen($a_attribs['value1'])) ? $a_attribs['value1'] : null],
                            "value2" => ["clob", (strlen($a_attribs['value2'])) ? $a_attribs['value2'] : null],
                            "pass" => ["integer", $a_attribs['pass']],
                            "tstamp" => ["integer", $a_attribs['tstamp']]
                        ]);
                        break;
                    case 'tst_test_result':
                        $next_id = $this->db->nextId('tst_test_result');
                        $affectedRows = $this->db->manipulateF(
                            "INSERT INTO tst_test_result (test_result_id, active_fi, question_fi, points, pass, manual, tstamp) VALUES (%s, %s, %s, %s, %s, %s, %s)",
                            ['integer', 'integer','integer', 'float', 'integer', 'integer','integer'],
                            [$next_id, $this->active_id_mapping[$a_attribs['active_fi']], $this->question_id_mapping[$a_attribs['question_fi']], $a_attribs['points'], $a_attribs['pass'], (strlen($a_attribs['manual'])) ? $a_attribs['manual'] : 0, $a_attribs['tstamp']]
                        );
                        break;
                    case 'tst_times':
                        $next_id = $this->db->nextId('tst_times');
                        $affectedRows = $this->db->manipulateF(
                            "INSERT INTO tst_times (times_id, active_fi, started, finished, pass, tstamp) VALUES (%s, %s, %s, %s, %s, %s)",
                            ['integer', 'integer', 'timestamp', 'timestamp', 'integer', 'integer'],
                            [$next_id, $this->active_id_mapping[$a_attribs['active_fi']], $a_attribs['started'], $a_attribs['finished'], $a_attribs['pass'], $a_attribs['tstamp']]
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
    public function handlerEndTag($a_xml_parser, $a_name): void
    {
        switch (strtolower($a_name)) {
            case 'tst_active':
                $this->log->info('active id mapping: ' . print_r($this->active_id_mapping, true));
                break;
            case 'tst_test_question':
                $this->log->info('question id mapping: ' . print_r($this->question_id_mapping, true));
                break;
        }
    }

    /**
      * handler for character data
      */
    public function handlerParseCharacterData($a_xml_parser, $a_data): void
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

    private function fetchLastFinishedPass($attribs): ?int
    {
        if (isset($attribs['last_finished_pass'])) {
            return (int) $attribs['last_finished_pass'];
        }

        if ($attribs['tries'] > 0) {
            return $attribs['tries'] - 1;
        }

        return null;
    }

    private function fetchLastStartedPass($attribs): ?int
    {
        if (isset($attribs['last_started_pass'])) {
            return (int) $attribs['last_started_pass'];
        }

        if ($attribs['tries'] > 0) {
            return (int) $attribs['tries'] - 1;
        }

        return null;
    }
}
