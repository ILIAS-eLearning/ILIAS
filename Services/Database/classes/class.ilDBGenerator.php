<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * This class provides methods for building a DB generation script,
 * getting a full overview on abstract table definitions and more...
 *
 * @author  Alex Killing <alex.killing@gmx.de>
 * @version $Id: class.ilDBUpdate.php 18649 2009-01-21 09:59:23Z akill $
 * @ingroup ServicesDatabase
 */
class ilDBGenerator
{

    /**
     * @var string
     */
    protected $target_encoding = 'UTF-8';
    /**
     * @var array
     */
    protected $whitelist = array();
    /**
     * @var array
     */
    protected $blacklist = array();
    /**
     * @var array
     */
    protected $tables = array();
    /**
     * @var array
     */
    protected $filter = array();


    /**
     * Constructor
     */
    public function __construct()
    {
        global $DIC;
        $ilDB = $DIC->database();

        $this->manager = $ilDB->loadModule(ilDBConstants::MODULE_MANAGER);
        $this->reverse = $ilDB->loadModule(ilDBConstants::MODULE_REVERSE);
        $this->il_db = $ilDB;
        include_once("./Services/Database/classes/class.ilDBAnalyzer.php");
        $this->analyzer = new ilDBAnalyzer();

        $this->allowed_attributes = $ilDB->getAllowedAttributes();
    }

    /**
     * @return array
     * @deprecated abstraction_progress is no longer used in ILIAS
     */
    public static function lookupAbstractedTables()
    {
        global $DIC;
        $ilDB = $DIC->database();

        $query = "SELECT DISTINCT(table_name) FROM abstraction_progress ";
        $res = $ilDB->query($query);
        $names = array();
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $names[] = $row->table_name;
        }

        // tables that have been already created in an abstracted
        // way or tables that have been renamed after being abstracted
        // (see db_update script)
        $abs_tables = array_merge($names, array(
            'acc_access_key',
            'acc_user_access_key',
            'ldap_rg_mapping',
            'page_anchor',
            'qpl_question_orderinghorizontal',
            'qpl_question_fileupload',
            'chat_smilies',
            'style_color',
            'style_template_class',
            'style_template',
            'page_style_usage',
            'style_setting',
            'page_editor_settings',
            'mep_data',
            'license_data',
            'loginname_history',
            'mep_item',
            'qpl_a_cloze',
            'qpl_a_imagemap',
            'qpl_a_matching',
            'qpl_num_range',
            'qpl_qst_cloze',
            'qpl_qst_essay',
            'qpl_qst_fileupload',
            'qpl_qst_flash',
            'qpl_qst_horder',
            'qpl_qst_imagemap',
            'qpl_qst_javaapplet',
            'qpl_qst_matching',
            'qpl_qst_mc',
            'qpl_qst_numeric',
            'qpl_qst_ordering',
            'qpl_qst_sc',
            'qpl_qst_textsubset',
            'qpl_qst_type',
            'qpl_sol_sug',
            'udf_text',
            'udf_clob',
            'xmlnestedsettmp',
            'cache_text',
            'cache_clob',
            'qpl_a_errortext',
            'qpl_qst_errortext',
            'tst_rnd_cpy',
            'tst_rnd_qpl_title',
            'qpl_a_mdef',
        ));

        return $abs_tables;
    }


    /**
     * Set the desired target encoding
     * If the target encoding os different from UTF-8
     * all text values will be shortened to length of
     * of the current text field
     *
     * E.g:
     * il_meta_keyword keyword(4000) target encoding: UTF16
     *
     * =>
     * <code>
     * $value = mb_convert_encoding($value,'UTF-8','UTF-16');
     * $value = mb_strcut($value,0,4000,'UTF16');
     * $value = mb_convert_encoding($value,'UTF-16','UTF-8');
     * </code>
     *
     *
     * @param object $a_encoding
     * @return
     */
    public function setTargetEncoding($a_encoding)
    {
        $this->target_encoding = $a_encoding;
    }


    /**
     * Returns the target encoding
     *
     * @return string
     */
    public function getTargetEncoding()
    {
        return $this->target_encoding;
    }


    /**
     * Set Table Black List.
     * (Tables that should not be included in the processing)
     *
     * @param    array $a_blacklist Table Black List
     */
    public function setBlackList($a_blacklist)
    {
        $this->blacklist = $a_blacklist;
    }


    /**
     * Get Table Black List.
     *
     * @return    array    Table Black List
     */
    public function getBlackList()
    {
        return $this->blacklist;
    }


    /**
     * Set Table White List.
     * Per default all tables are included in the processing. If a white
     * list ist provided, only them will be used.
     *
     * @param    array $a_whitelist Table White List
     */
    public function setWhiteList($a_whitelist)
    {
        $this->whitelist = $a_whitelist;
    }


    /**
     * Get Table White List.
     *
     * @return    array    Table White List
     */
    public function getWhiteList()
    {
        return $this->whitelist;
    }


    /**
     * @param $a_filter
     * @param $a_value
     */
    public function setFilter($a_filter, $a_value)
    {
        $this->filter[$a_filter] = $a_value;
    }


    /**
     * @return array
     */
    public function getTables()
    {
        $r = $this->manager->listTables();
        $this->tables = $r;

        return $this->tables;
    }


    /**
     * Check whether a table should be processed or not
     */
    public function checkProcessing($a_table)
    {
        // check black list
        if (in_array($a_table, $this->blacklist)) {
            return false;
        }

        // check white list
        if (count($this->whitelist) > 0 && !in_array($a_table, $this->whitelist)) {
            return false;
        }

        return true;
    }


    protected function openFile($a_path)
    {
        if (1) {
            $file = fopen($a_path, "w");
            $start .= "\t" . 'global $ilDB;' . "\n\n";
            fwrite($file, $start);

            return $file;
        }

        $file = fopen($a_path, "w");
        $start = '<?php' . "\n" . 'function setupILIASDatabase()' . "\n{\n";
        $start .= "\t" . 'global $ilDB;' . "\n\n";
        fwrite($file, $start);

        return $file;
    }


    protected function closeFile($fp)
    {
        if (1) {
            #fwrite ($fp, $end);
            fclose($fp);

            return;
        }

        $end = "\n}\n?>\n";
        fwrite($fp, $end);
        fclose($fp);
    }


    /**
     * Build DB generation script
     *
     * @param    string        output filename, if no filename is given, script is echoed
     */
    public function buildDBGenerationScript($a_filename = "")
    {
        if (@is_dir($a_filename)) {
            $isDirectory = true;
            $path = $a_filename;
        } else {
            $isDirectory = false;
            $path = '';
        }

        $file = "";
        if ($a_filename != "" and !$isDirectory) {
            $file = fopen($a_filename, "w");

            $start = '<?php' . "\n" . 'function setupILIASDatabase()' . "\n{\n";
            $start .= "\t" . 'global $ilDB;' . "\n\n";
            fwrite($file, $start);
        } elseif ($isDirectory) {
            ;
        } else {
            echo "<pre>";
        }

        $this->getTables();

        foreach ($this->tables as $table) {
            if ($this->checkProcessing($table)) {
                if ($a_filename != "") {
                    flush();
                }

                if ($isDirectory) {
                    $file = $this->openFile($path . '/' . $table);
                }

                // create table statement
                $this->buildCreateTableStatement($table, $file);

                // primary key
                $this->buildAddPrimaryKeyStatement($table, $file);

                // indices
                $this->buildAddIndexStatements($table, $file);

                // constraints (currently unique keys)
                $this->buildAddUniqueConstraintStatements($table, $file);

                // auto increment sequence
                $this->buildCreateSequenceStatement($table, $file);

                if (in_array($table, array('usr_session_stats', 'usr_session_raw'))) {
                    continue;
                }

                // inserts
                if ($isDirectory) {
                    $this->buildInsertStatement($table, $path);
                #$this->buildInsertStatementsXML($table,$path);
                } else {
                    $this->buildInsertStatements($table, $file);
                }

                if ($isDirectory) {
                    $this->closeFile($file);
                }
            } else {
                if ($a_filename != "") {
                    echo "<br><b>missing: " . $table . "</b>";
                    flush();
                }
            }
        }

        // sequence(s) without table (of same name)
        $this->buildSingularSequenceStatement($file);

        if ($a_filename == "") {
            echo "</pre>";
        } elseif (!$isDirectory) {
            $end = "\n}\n?>\n";
            $ok = fwrite($file, $end);
            var_dump($ok);
            fclose($file);
        }
    }


    /**
     * Build CreateTable statement
     *
     * @param    string        table name
     * @param    file          file resource or empty string
     */
    public function buildCreateTableStatement($a_table, $a_file = "")
    {
        $fields = $this->analyzer->getFieldInformation($a_table, true);
        $this->fields = $fields;
        $create_st = "\n\n//\n// " . $a_table . "\n//\n";
        $create_st .= '$fields = array (' . "\n";
        $f_sep = "";
        foreach ($fields as $f => $def) {
            $create_st .= "\t" . $f_sep . '"' . $f . '" => array (' . "\n";
            $f_sep = ",";
            $a_sep = "";
            foreach ($def as $k => $v) {
                if ($k != "nativetype" && $k != "alt_types" && $k != "autoincrement" && !is_null($v)) {
                    switch ($k) {
                        case "notnull":
                        case "unsigned":
                        case "fixed":
                            $v = $v ? "true" : "false";
                            break;

                        case "default":
                        case "type":
                            $v = '"' . $v . '"';
                            break;

                        default:
                            break;
                    }
                    $create_st .= "\t\t" . $a_sep . '"' . $k . '" => ' . $v . "\n";
                    $a_sep = ",";
                }
            }
            $create_st .= "\t" . ')' . "\n";
        }
        $create_st .= ');' . "\n";
        $create_st .= '$ilDB->createTable("' . $a_table . '", $fields);' . "\n";

        if ($a_file == "") {
            echo $create_st;
        } else {
            fwrite($a_file, $create_st);
        }
    }


    /**
     * Build AddPrimaryKey statement
     *
     * @param    string        table name
     * @param    file          file resource or empty string
     */
    public function buildAddPrimaryKeyStatement($a_table, $a_file = "")
    {
        $pk = $this->analyzer->getPrimaryKeyInformation($a_table);

        if (is_array($pk["fields"]) && count($pk["fields"]) > 0) {
            $pk_st = "\n" . '$pk_fields = array(';
            $sep = "";
            foreach ($pk["fields"] as $f => $pos) {
                $pk_st .= $sep . '"' . $f . '"';
                $sep = ",";
            }
            $pk_st .= ");\n";
            $pk_st .= '$ilDB->addPrimaryKey("' . $a_table . '", $pk_fields);' . "\n";

            if ($a_file == "") {
                echo $pk_st;
            } else {
                fwrite($a_file, $pk_st);
            }
        }
    }


    /**
     * Build AddIndex statements
     *
     * @param    string        table name
     * @param    file          file resource or empty string
     */
    public function buildAddIndexStatements($a_table, $a_file = "")
    {
        $ind = $this->analyzer->getIndicesInformation($a_table, true);

        if (is_array($ind)) {
            foreach ($ind as $i) {
                if ($i["fulltext"]) {
                    $ft = ", true";
                } else {
                    $ft = ", false";
                }
                $in_st = "\n" . '$in_fields = array(';
                $sep = "";
                foreach ($i["fields"] as $f => $pos) {
                    $in_st .= $sep . '"' . $f . '"';
                    $sep = ",";
                }
                $in_st .= ");\n";
                $in_st .= '$ilDB->addIndex("' . $a_table . '", $in_fields, "' . $i["name"] . '"' . $ft . ');' . "\n";

                if ($a_file == "") {
                    echo $in_st;
                } else {
                    fwrite($a_file, $in_st);
                }
            }
        }
    }


    /**
     * Build AddUniqueConstraint statements
     *
     * @param    string        table name
     * @param    file          file resource or empty string
     */
    public function buildAddUniqueConstraintStatements($a_table, $a_file = "")
    {
        $con = $this->analyzer->getConstraintsInformation($a_table, true);

        if (is_array($con)) {
            foreach ($con as $i) {
                $in_st = "\n" . '$in_fields = array(';
                $sep = "";
                foreach ($i["fields"] as $f => $pos) {
                    $in_st .= $sep . '"' . $f . '"';
                    $sep = ",";
                }
                $in_st .= ");\n";
                $in_st .= '$ilDB->addUniqueConstraint("' . $a_table . '", $in_fields, "' . $i["name"] . '");' . "\n";

                if ($a_file == "") {
                    echo $in_st;
                } else {
                    fwrite($a_file, $in_st);
                }
            }
        }
    }


    /**
     * Build CreateSequence statement
     *
     * @param    string        table name
     * @param    file          file resource or empty string
     */
    public function buildCreateSequenceStatement($a_table, $a_file = "")
    {
        $seq = $this->analyzer->hasSequence($a_table);
        if ($seq !== false) {
            $seq_st = "\n" . '$ilDB->createSequence("' . $a_table . '", ' . (int) $seq . ');' . "\n";

            if ($a_file == "") {
                echo $seq_st;
            } else {
                fwrite($a_file, $seq_st);
            }
        }
    }


    /**
     * Build CreateSequence statement (if not belonging to table)
     *
     * @param    file        file resource or empty string
     */
    public function buildSingularSequenceStatement($a_file = "")
    {
        $r = $this->manager->listSequences();

        foreach ($r as $seq) {
            if (!in_array($seq, $this->tables)) {
                // 12570
                if ($seq == "sahs_sc13_seq") {
                    continue;
                }

                $create_st = "\n" . '$ilDB->createSequence("' . $seq . '");' . "\n";

                if ($a_file == "") {
                    echo $create_st;
                } else {
                    fwrite($a_file, $create_st);
                }
            }
        }
    }


    /**
     * Write seerialized insert data to array
     *
     * @param object $a_table
     * @param object $a_basedir
     * @return
     */
    public function buildInsertStatement($a_table, $a_basedir)
    {
        global $DIC;
        $ilLogger = $DIC->logger()->root();

        $ilLogger->log('Starting export of:' . $a_table);

        $set = $this->il_db->query("SELECT * FROM " . $this->il_db->quoteIdentifier($a_table));
        $row = 0;

        umask(0000);
        mkdir($a_basedir . '/' . $a_table . '_inserts', fileperms($a_basedir));

        $filenum = 1;
        while ($rec = $this->il_db->fetchAssoc($set)) {
            $values = array();
            foreach ($rec as $f => $v) {
                if ($this->fields[$f]['type'] == 'text' and $this->fields[$f]['length'] >= 1000) {
                    $v = $this->shortenText($a_table, $f, $v, $this->fields[$f]['length']);
                }

                $values[$f] = array(
                    $this->fields[$f]['type'],
                    $v,
                );
            }

            $rows[$a_table][$row++] = $values;

            if ($row >= 1000) {
                $ilLogger->log('Writing insert statements after 1000 lines...');
                $fp = fopen($a_basedir . '/' . $a_table . '_inserts/' . $filenum++ . '.data', 'w');
                fwrite($fp, serialize((array) $rows));
                fclose($fp);

                $row = 0;
                unset($rows);
            }
        }
        if ($rows) {
            $fp = fopen($a_basedir . '/' . $a_table . '_inserts/' . $filenum++ . '.data', 'w');
            fwrite($fp, serialize((array) $rows) . "\n");
            fclose($fp);
        }

        $ilLogger->log('Finished export of: ' . $a_table);
        if (function_exists('memory_get_usage')) {
            $ilLogger->log('Memory usage: ' . memory_get_usage(true));
        }

        return true;
    }


    /**
     *
     * @param object $a_table
     * @param object $a_file [optional]
     * @return
     */
    public function buildInsertStatementsXML($a_table, $a_basedir)
    {
        include_once './Services/Xml/classes/class.ilXmlWriter.php';
        $w = new ilXmlWriter();
        $w->xmlStartTag('Table', array( 'name' => $a_table ));

        $set = $this->il_db->query("SELECT * FROM " . $this->il_db->quoteIdentifier($a_table));
        $ins_st = "";
        $first = true;
        while ($rec = $this->il_db->fetchAssoc($set)) {
            #$ilLog->write('Num: '.$num++);
            $w->xmlStartTag('Row');

            $fields = array();
            $types = array();
            $values = array();
            foreach ($rec as $f => $v) {
                if ($this->fields[$f]['type'] == 'text' and $this->fields[$f]['length'] >= 1000) {
                    $v = $this->shortenText($a_table, $f, $v, $this->fields[$f]['length']);
                }

                $w->xmlElement('Value', array(
                        'name' => $f,
                        'type' => $this->fields[$f]['type'],
                    ), $v);
            }

            $w->xmlEndTag('Row');
        }
        $w->xmlEndTag('Table');

        $w->xmlDumpFile($a_basedir . '/' . $a_table . '.xml', false);
    }


    /**
     * Build Insert statements
     *
     * @param    string        table name
     * @param    file          file resource or empty string
     */
    public function buildInsertStatements($a_table, $a_file = "")
    {
        if ($a_table == "lng_data") {
            return;
        }

        $set = $this->il_db->query("SELECT * FROM " . $this->il_db->quoteIdentifier($a_table));
        $ins_st = "";
        $first = true;
        while ($rec = $this->il_db->fetchAssoc($set)) {
            $fields = array();
            $types = array();
            $values = array();
            $i_str = array();
            foreach ($rec as $f => $v) {
                $fields[] = $f;
                $types[] = '"' . $this->fields[$f]["type"] . '"';
                $v = str_replace('\\', '\\\\', $v);
                $values[] = "'" . str_replace("'", "\'", $v) . "'";
                $i_str[] = "'" . $f . "' => array('" . $this->fields[$f]["type"] . "', '" . str_replace("'", "\'", $v) . "')";
            }
            $fields_str = "(" . implode($fields, ",") . ")";
            $types_str = "array(" . implode($types, ",") . ")";
            $values_str = "array(" . implode($values, ",") . ")";
            $ins_st = "\n" . '$ilDB->insert("' . $a_table . '", array(' . "\n";
            $ins_st .= implode($i_str, ", ") . "));\n";
            //$ins_st.= "\t".$fields_str."\n";
            //$ins_st.= "\t".'VALUES '."(%s".str_repeat(",%s", count($fields) - 1).')"'.",\n";
            //$ins_st.= "\t".$types_str.','.$values_str.');'."\n";

            if ($a_file == "") {
                echo $ins_st;
            } else {
                fwrite($a_file, $ins_st);
            }
            $ins_st = "";
        }
    }


    /**
     * Get table definition overview in HTML
     *
     * @param    string        output filename, if no filename is given, script is echoed
     */
    public function getHTMLOverview($a_filename = "")
    {
        $tpl = new ilTemplate("tpl.db_overview.html", true, true, "Services/Database");

        $this->getTables();
        $cnt = 1;
        foreach ($this->tables as $table) {
            if ($this->checkProcessing($table)) {
                // create table statement
                if ($this->addTableToOverview($table, $tpl, $cnt)) {
                    $cnt++;
                }
            }
        }

        $tpl->setVariable("TXT_TITLE", "ILIAS Abstract DB Tables (" . ILIAS_VERSION . ")");

        if ($a_filename == "") {
            echo $tpl->get();
        }
    }


    /**
     * Add table to overview template
     */
    public function addTableToOverview($a_table, $a_tpl, $a_cnt)
    {
        $fields = $this->analyzer->getFieldInformation($a_table);
        $indices = $this->analyzer->getIndicesInformation($a_table);
        $constraints = $this->analyzer->getConstraintsInformation($a_table);
        $pk = $this->analyzer->getPrimaryKeyInformation($a_table);
        $auto = $this->analyzer->getAutoIncrementField($a_table);
        $has_sequence = $this->analyzer->hasSequence($a_table);

        // table filter
        if (isset($this->filter["has_sequence"])) {
            if ((!$has_sequence && $auto == "" && $this->filter["has_sequence"])
                || (($has_sequence || $auto != "") && !$this->filter["has_sequence"])
            ) {
                return false;
            }
        }

        // indices
        $indices_output = false;
        if (is_array($indices) && count($indices) > 0 && !$this->filter["skip_indices"]) {
            foreach ($indices as $index => $def) {
                $f2 = array();
                foreach ($def["fields"] as $f => $pos) {
                    $f2[] = $f;
                }
                $a_tpl->setCurrentBlock("index");
                $a_tpl->setVariable("VAL_INDEX", $def["name"]);
                $a_tpl->setVariable("VAL_FIELDS", implode($f2, ", "));
                $a_tpl->parseCurrentBlock();
                $indices_output = true;
            }
            $a_tpl->setCurrentBlock("index_table");
            $a_tpl->parseCurrentBlock();
        }

        // constraints
        $constraints_output = false;
        if (is_array($constraints) && count($constraints) > 0 && !$this->filter["skip_constraints"]) {
            foreach ($constraints as $index => $def) {
                $f2 = array();
                foreach ($def["fields"] as $f => $pos) {
                    $f2[] = $f;
                }
                $a_tpl->setCurrentBlock("constraint");
                $a_tpl->setVariable("VAL_CONSTRAINT", $def["name"]);
                $a_tpl->setVariable("VAL_CTYPE", $def["type"]);
                $a_tpl->setVariable("VAL_CFIELDS", implode($f2, ", "));
                $a_tpl->parseCurrentBlock();
                $constraints_output = true;
            }
            $a_tpl->setCurrentBlock("constraint_table");
            $a_tpl->parseCurrentBlock();
        }

        // fields
        $fields_output = false;
        foreach ($fields as $field => $def) {
            // field filter
            if (isset($this->filter["alt_types"])) {
                if (($def["alt_types"] == "" && $this->filter["alt_types"])
                    || ($def["alt_types"] != "" && !$this->filter["alt_types"])
                ) {
                    continue;
                }
            }
            if (isset($this->filter["type"])) {
                if ($def["type"] != $this->filter["type"]) {
                    continue;
                }
            }
            if (isset($this->filter["nativetype"])) {
                if ($def["nativetype"] != $this->filter["nativetype"]) {
                    continue;
                }
            }
            if (isset($this->filter["unsigned"])) {
                if ($def["unsigned"] != $this->filter["unsigned"]) {
                    continue;
                }
            }

            $a_tpl->setCurrentBlock("field");
            if (empty($pk["fields"][$field])) {
                $a_tpl->setVariable("VAL_FIELD", strtolower($field));
            } else {
                $a_tpl->setVariable("VAL_FIELD", "<u>" . strtolower($field) . "</u>");
            }
            $a_tpl->setVariable("VAL_TYPE", $def["type"]);
            $a_tpl->setVariable("VAL_LENGTH", (!is_null($def["length"])) ? $def["length"] : "&nbsp;");

            if (strtolower($def["default"]) == "current_timestamp") {
                //$def["default"] = "0000-00-00 00:00:00";
                unset($def["default"]);
            }

            $a_tpl->setVariable("VAL_DEFAULT", (!is_null($def["default"])) ? $def["default"] : "&nbsp;");
            $a_tpl->setVariable("VAL_NOT_NULL", (!is_null($def["notnull"])) ? (($def["notnull"]) ? "true" : "false") : "&nbsp;");
            $a_tpl->setVariable("VAL_FIXED", (!is_null($def["fixed"])) ? (($def["fixed"]) ? "true" : "false") : "&nbsp;");
            $a_tpl->setVariable("VAL_UNSIGNED", (!is_null($def["unsigned"])) ? (($def["unsigned"]) ? "true" : "false") : "&nbsp;");
            $a_tpl->setVariable("VAL_ALTERNATIVE_TYPES", ($def["alt_types"] != "") ? $def["alt_types"] : "&nbsp;");
            $a_tpl->setVariable("VAL_NATIVETYPE", ($def["nativetype"] != "") ? $def["nativetype"] : "&nbsp;");
            $a_tpl->parseCurrentBlock();
            $fields_output = true;
        }

        if ($fields_output) {
            $a_tpl->setCurrentBlock("field_table");
            $a_tpl->parseCurrentBlock();
        }

        // table information
        if ($indices_output || $fields_output || $constraints_output) {
            $a_tpl->setCurrentBlock("table");
            $a_tpl->setVariable("TXT_TABLE_NAME", strtolower($a_table));
            if ($has_sequence || $auto != "") {
                $a_tpl->setVariable("TXT_SEQUENCE", "Has Sequence");
            } else {
                $a_tpl->setVariable("TXT_SEQUENCE", "No Sequence");
            }
            $a_tpl->setVariable("VAL_CNT", (int) $a_cnt);
            $a_tpl->parseCurrentBlock();

            return true;
        }

        return false;
    }


    /**
     * Shorten text depending on target encoding
     *
     * @param string $table
     * @param string $field
     * @param string $a_value
     * @param int $a_size
     * @return string
     */
    protected function shortenText($table, $field, $a_value, $a_size)
    {
        global $DIC;
        $ilLogger = $DIC->logger()->root();

        if ($this->getTargetEncoding() == 'UTF-8') {
            return $a_value;
        }
        // Convert to target encoding
        $shortened = mb_convert_encoding($a_value, $this->getTargetEncoding(), 'UTF-8');
        // Shorten
        include_once './Services/Utilities/classes/class.ilStr.php';
        $shortened = ilStr::shortenText($shortened, 0, $a_size, $this->getTargetEncoding());
        // Convert back to UTF-8
        $shortened = mb_convert_encoding($shortened, 'UTF-8', $this->getTargetEncoding());

        if (strlen($a_value) != strlen($shortened)) {
            $ilLogger->log('Table        : ' . $table);
            $ilLogger->log('Field        : ' . $field);
            $ilLogger->log('Type         : ' . $this->fields[$field]['type']);
            $ilLogger->log('Length       : ' . $this->fields[$field]['length']);
            $ilLogger->log('Before       : ' . $a_value);
            $ilLogger->log('Shortened    : ' . $shortened);
            $ilLogger->log('Strlen Before: ' . strlen($a_value));
            $ilLogger->log('Strlen After : ' . strlen($shortened));
        }

        return $shortened;
    }
}
