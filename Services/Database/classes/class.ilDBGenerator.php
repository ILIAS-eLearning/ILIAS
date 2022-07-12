<?php declare(strict_types=1);

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
 * This class provides methods for building a DB generation script,
 * getting a full overview on abstract table definitions and more...
 * @author  Alex Killing <alex.killing@gmx.de>
 * @version $Id: class.ilDBUpdate.php 18649 2009-01-21 09:59:23Z akill $
 * @ingroup ServicesDatabase
 */
class ilDBGenerator
{
    protected string $target_encoding = 'UTF-8';
    protected array $whitelist = array();
    protected array $blacklist = array();
    protected array $tables = array();
    protected array $filter = array();
    protected ilDBManager $manager;
    protected ilDBReverse $reverse;
    protected ilDBInterface $il_db;
    protected ilDBAnalyzer $analyzer;
    protected array $allowed_attributes = [];
    protected array $fields = [];

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
        $this->analyzer = new ilDBAnalyzer();
        $this->allowed_attributes = $ilDB->getAllowedAttributes();
    }

    /**
     * @return string[]
     * @deprecated abstraction_progress is no longer used in ILIAS
     */
    public static function lookupAbstractedTables() : array
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
     * E.g:
     * il_meta_keyword keyword(4000) target encoding: UTF16
     * =>
     * <code>
     * $value = mb_convert_encoding($value,'UTF-8','UTF-16');
     * $value = mb_strcut($value,0,4000,'UTF16');
     * $value = mb_convert_encoding($value,'UTF-16','UTF-8');
     * </code>
     */
    public function setTargetEncoding(string $a_encoding) : void
    {
        $this->target_encoding = $a_encoding;
    }

    public function getTargetEncoding() : string
    {
        return $this->target_encoding;
    }

    /**
     * Set Table Black List.
     * (Tables that should not be included in the processing)
     * @param string[] $a_blacklist Table Black List
     */
    public function setBlackList(array $a_blacklist) : void
    {
        $this->blacklist = $a_blacklist;
    }

    public function getBlackList() : array
    {
        return $this->blacklist;
    }

    /**
     * Set Table White List.
     * Per default all tables are included in the processing. If a white
     * list ist provided, only them will be used.
     * @param string[] $a_whitelist Table White List
     */
    public function setWhiteList(array $a_whitelist) : void
    {
        $this->whitelist = $a_whitelist;
    }

    public function getWhiteList() : array
    {
        return $this->whitelist;
    }

    public function setFilter(string $a_filter, string $a_value) : void
    {
        $this->filter[$a_filter] = $a_value;
    }

    /**
     * @return string[]
     */
    public function getTables() : array
    {
        return $this->tables = $this->manager->listTables();
    }

    /**
     * Check whether a table should be processed or not
     */
    public function checkProcessing(string $a_table) : bool
    {
        // check black list
        if (in_array($a_table, $this->blacklist, true)) {
            return false;
        }

        // check white list
        if (count($this->whitelist) > 0 && !in_array($a_table, $this->whitelist, true)) {
            return false;
        }

        return true;
    }

    /**
     * @return resource
     */
    protected function openFile(string $a_path)
    {
        $start = '';
        $file = fopen($a_path, 'wb');
        $start .= "\t" . 'global $ilDB;' . "\n\n";
        fwrite($file, $start);

        return $file;
    }

    /**
     * @param resource $fp
     */
    protected function closeFile($fp) : void
    {
        fclose($fp);
    }

    /**
     * Build DB generation script
     * @param string        output filename, if no filename is given, script is echoed
     */
    public function buildDBGenerationScript(string $a_filename = "") : void
    {
        if (@is_dir($a_filename)) {
            $is_dir = true;
            $path = $a_filename;
        } else {
            $is_dir = false;
            $path = '';
        }

        $file = "";
        if ($a_filename !== "" && !$is_dir) {
            $file = fopen($a_filename, 'wb');

            $start = '<?php' . "\n" . 'function setupILIASDatabase()' . "\n{\n";
            $start .= "\t" . 'global $ilDB;' . "\n\n";
            fwrite($file, $start);
        } else {
            echo "<pre>";
        }

        foreach ($this->getTables() as $table) {
            if ($this->checkProcessing($table)) {
                if ($a_filename !== "") {
                    flush();
                }

                if ($is_dir) {
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

                if (in_array($table, array('usr_session_stats', 'usr_session_raw', 'il_plugin'))) {
                    continue;
                }

                // inserts
                if ($is_dir) {
                    $this->buildInsertStatement($table, $path);
                #$this->buildInsertStatementsXML($table,$path);
                } else {
                    $this->buildInsertStatements($table, $file);
                }

                if ($is_dir) {
                    $this->closeFile($file);
                }
            } elseif ($a_filename !== "") {
                echo "<br><b>missing: " . $table . "</b>";
                flush();
            }
        }

        // sequence(s) without table (of same name)
        $this->buildSingularSequenceStatement($file);

        if ($a_filename === "") {
            echo "</pre>";
        } elseif (!$is_dir) {
            $end = "\n}\n?>\n";
            $ok = fwrite($file, $end);
            fclose($file);
        }
    }

    /**
     * @param string   $a_table
     * @param resource $a_file
     * @throws ilDatabaseException
     */
    public function buildCreateTableStatement(string $a_table, $a_file = null) : void
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
                if ($k !== "nativetype" && $k !== "alt_types" && $k !== "autoincrement" && !is_null($v)) {
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

        if ($a_file === null) {
            echo $create_st;
        } else {
            fwrite($a_file, $create_st);
        }
    }

    /**
     * @param resource $a_file
     */
    public function buildAddPrimaryKeyStatement(string $a_table, $a_file = null) : void
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

            if ($a_file === null) {
                echo $pk_st;
            } else {
                fwrite($a_file, $pk_st);
            }
        }
    }

    /**
     * @param resource $a_file
     */
    public function buildAddIndexStatements(string $a_table, $a_file = null) : void
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

                if ($a_file === null) {
                    echo $in_st;
                } else {
                    fwrite($a_file, $in_st);
                }
            }
        }
    }

    /**
     * @param resource $file_handle
     */
    private function printOrWrite(string $string, $file_handle = null) : void
    {
        if ($file_handle === null) {
            echo $string;
        } else {
            fwrite($file_handle, $string);
        }
    }

    /**
     * @param resource $file_handle
     */
    public function buildAddUniqueConstraintStatements(string $a_table, $file_handle = null) : void
    {
        $con = $this->analyzer->getConstraintsInformation($a_table, true);

        if (is_array($con)) {
            $in_st = '';
            foreach ($con as $i) {
                $in_st = "\n" . '$in_fields = array(';
                $sep = "";
                foreach ($i["fields"] as $f => $pos) {
                    $in_st .= $sep . '"' . $f . '"';
                    $sep = ",";
                }
                $in_st .= ");\n";
                $in_st .= '$ilDB->addUniqueConstraint("' . $a_table . '", $in_fields, "' . $i["name"] . '");' . "\n";
            }
            $this->printOrWrite($in_st, $file_handle);
        }
    }

    /**
     * @param resource $file_handle
     * @throws ilDatabaseException
     */
    public function buildCreateSequenceStatement(string $a_table, $file_handle = null) : void
    {
        $seq = $this->analyzer->hasSequence($a_table);
        if ($seq !== false) {
            $seq_st = "\n" . '$ilDB->createSequence("' . $a_table . '", ' . (int) $seq . ');' . "\n";

            $this->printOrWrite($seq_st, $file_handle);
        }
    }

    /**
     * @param resource $file_handle
     */
    public function buildSingularSequenceStatement($file_handle = null) : void
    {
        $r = $this->manager->listSequences();

        foreach ($r as $seq) {
            if (!in_array($seq, $this->tables, true)) {
                // 12570
                if ($seq === "sahs_sc13_seq") {
                    continue;
                }

                $create_st = "\n" . '$ilDB->createSequence("' . $seq . '");' . "\n";

                $this->printOrWrite($create_st, $file_handle);
            }
        }
    }

    /**
     * Write seerialized insert data to array
     */
    public function buildInsertStatement(string $a_table, string $a_basedir) : bool
    {
        global $DIC;
        $ilLogger = $DIC->logger()->root();

        $ilLogger->log('Starting export of:' . $a_table);

        $set = $this->il_db->query("SELECT * FROM " . $this->il_db->quoteIdentifier($a_table));
        $row = 0;

        umask(0000);
        if (!mkdir(
            $concurrentDirectory = $a_basedir . '/' . $a_table . '_inserts',
            fileperms($a_basedir)
        ) && !is_dir($concurrentDirectory)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
        }

        $filenum = 1;
        while ($rec = $this->il_db->fetchAssoc($set)) {
            $values = array();
            foreach ($rec as $f => $v) {
                if ($this->fields[$f]['type'] === 'text' && $this->fields[$f]['length'] >= 1000) {
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
                $fp = fopen($a_basedir . '/' . $a_table . '_inserts/' . $filenum++ . '.data', 'wb');
                fwrite($fp, serialize($rows));
                fclose($fp);

                $row = 0;
                unset($rows);
            }
        }
        if (isset($rows)) {
            $fp = fopen($a_basedir . '/' . $a_table . '_inserts/' . $filenum++ . '.data', 'wb');
            fwrite($fp, serialize($rows) . "\n");
            fclose($fp);
        }

        $ilLogger->log('Finished export of: ' . $a_table);
        if (function_exists('memory_get_usage')) {
            $ilLogger->log('Memory usage: ' . memory_get_usage(true));
        }

        return true;
    }

    /**
     * @param resource $file_handle
     */
    public function buildInsertStatements(string $a_table, $file_handle = null) : void
    {
        if ($a_table === "lng_data") {
            return;
        }

        $set = $this->il_db->query("SELECT * FROM " . $this->il_db->quoteIdentifier($a_table));
        $ins_st = "";
        while ($rec = $this->il_db->fetchAssoc($set)) {
            $fields = array();
            $types = array();
            $values = array();
            $i_str = array();
            foreach ($rec as $f => $v) {
                $v = str_replace('\\', '\\\\', $v);
                $i_str[] = "'" . $f . "' => array('" . $this->fields[$f]["type"] . "', '" . str_replace(
                    "'",
                    "\'",
                    $v
                ) . "')";
            }
            $ins_st = "\n" . '$ilDB->insert("' . $a_table . '", array(' . "\n";
            $ins_st .= implode(", ", $i_str) . "));\n";

            $this->printOrWrite($ins_st, $file_handle);
            $ins_st = "";
        }
    }

    /**
     * Shorten text depending on target encoding
     */
    protected function shortenText(string $table, string $field, string $a_value, int $a_size) : string
    {
        global $DIC;
        $ilLogger = $DIC->logger()->root();

        if ($this->getTargetEncoding() === 'UTF-8') {
            return $a_value;
        }
        // Convert to target encoding
        $shortened = mb_convert_encoding($a_value, $this->getTargetEncoding(), 'UTF-8');
        // Shorten
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
