<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * This class includes methods that help to abstract ILIAS 3.10.x MySQL tables
 * for the use with MDB2 abstraction layer and full compliance mode support.
 *
 * @author  Alex Killing <alex.killing@gmx.de>
 * @version $Id: class.ilDBUpdate.php 18649 2009-01-21 09:59:23Z akill $
 * @ingroup ServicesDatabase
 */
class ilMySQLAbstraction
{

    /**
     * @var \ilDBAnalyzer
     */
    public $analyzer;
    /**
     * @var \ilDBInterface
     */
    protected $ilDBInterface;
    /**
     * @var \ilDBManager
     */
    protected $manager;
    /**
     * @var \ilDBReverse
     */
    protected $reverse;
    /**
     * @var bool
     */
    protected $testmode;


    /**
     * Constructor
     */
    public function __construct()
    {
        global $DIC;
        $ilDB = $DIC->database();

        $this->ilDBInterface = $ilDB;
        $this->manager = $ilDB->loadModule(ilDBConstants::MODULE_MANAGER);
        $this->reverse = $ilDB->loadModule(ilDBConstants::MODULE_REVERSE);
        if (@is_file('../Services/Database/classes/class.ilDBAnalyzer.php')) {
            include_once '../Services/Database/classes/class.ilDBAnalyzer.php';
        } else {
            include_once './Services/Database/classes/class.ilDBAnalyzer.php';
        }
        $this->analyzer = new ilDBAnalyzer();
        $this->setTestMode(false);
    }


    /**
     * @param $a_testmode
     */
    public function setTestMode($a_testmode)
    {
        $this->testmode = $a_testmode;
    }


    /**
     * @return bool
     */
    public function getTestMode()
    {
        return $this->testmode;
    }


    /**
     * Converts an existing (MySQL) ILIAS table in an abstract table.
     * This means the table conforms to the MDB2 field types, uses
     * sequences instead of auto_increment.
     *
     * @param string $a_table_name string
     *
     * @param bool   $a_set_text_ts_fields_notnull_false
     *
     * @throws ilDatabaseException
     */
    public function performAbstraction($a_table_name, $a_set_text_ts_fields_notnull_false = true)
    {
        // to do: log this procedure

        // count number of records at the beginning
        $nr_rec = $this->countRecords($a_table_name);

        // convert table name to lowercase
        if (!$this->getTestMode()) {
            $this->lowerCaseTableName($a_table_name);
            $a_table_name = strtolower($a_table_name);
            $this->storeStep($a_table_name, 10);
        }

        // get auto increment information
        $auto_inc_field = $this->analyzer->getAutoIncrementField($a_table_name);

        // get primary key information
        $pk = $this->analyzer->getPrimaryKeyInformation($a_table_name);

        // get indices information
        $indices = $this->analyzer->getIndicesInformation($a_table_name);

        // get constraints information
        $constraints = $this->analyzer->getConstraintsInformation($a_table_name);

        // get field information
        $fields = $this->analyzer->getFieldInformation($a_table_name);

        if (!$this->getTestMode()) {
            // remove auto increment
            $this->removeAutoIncrement($a_table_name, $auto_inc_field, $fields);
            $this->storeStep($a_table_name, 20);

            // remove primary key
            $this->removePrimaryKey($a_table_name, $pk);
            $this->storeStep($a_table_name, 30);

            // remove indices
            $this->removeIndices($a_table_name, $indices);
            $this->storeStep($a_table_name, 40);

            // remove constraints
            $this->removeConstraints($a_table_name, $constraints);
            $this->storeStep($a_table_name, 45);
        }

        // alter table using mdb2 field types
        $this->alterTable($a_table_name, $fields, $a_set_text_ts_fields_notnull_false, $pk);
        if ($this->getTestMode()) {
            $a_table_name = strtolower($a_table_name) . "_copy";
        } else {
            $this->storeStep($a_table_name, 50);
        }

        // lower case field names
        $this->lowerCaseColumnNames($a_table_name);
        if (!$this->getTestMode()) {
            $this->storeStep($a_table_name, 60);
        }

        // add primary key
        $this->addPrimaryKey($a_table_name, $pk);
        if (!$this->getTestMode()) {
            $this->storeStep($a_table_name, 70);
        }

        // add indices
        $this->addIndices($a_table_name, $indices);
        if (!$this->getTestMode()) {
            $this->storeStep($a_table_name, 80);
        }

        // add constraints
        $this->addConstraints($a_table_name, $constraints);
        if (!$this->getTestMode()) {
            $this->storeStep($a_table_name, 85);
        }

        // add "auto increment" sequence
        if ($auto_inc_field != "") {
            $this->addAutoIncrementSequence($a_table_name, $auto_inc_field);
        }
        if (!$this->getTestMode()) {
            $this->storeStep($a_table_name, 90);
        }

        // replace empty strings with null values in text fields
        $this->replaceEmptyStringsWithNull($a_table_name);
        if (!$this->getTestMode()) {
            $this->storeStep($a_table_name, 100);
        }

        // replace empty "0000-00-00..." dates with null
        $this->replaceEmptyDatesWithNull($a_table_name);
        if (!$this->getTestMode()) {
            $this->storeStep($a_table_name, 110);
        }

        $nr_rec2 = $this->countRecords($a_table_name);

        if (!$this->getTestMode()) {
            if ($nr_rec != $nr_rec2) {
                throw new ilDatabaseException(
                    "ilMySQLAbstraction: Unexpected difference in table record number, table '" . $a_table_name . "'." . " Before: " . ((int) $nr_rec) . ", After: " . ((int) $nr_rec2) . "."
                );
            }
        }
    }


    /**
     * Check number of records before and after
     *
     * @param string $a_table_name
     *
     * @return int
     */
    public function countRecords($a_table_name)
    {
        global $DIC;
        $ilDB = $DIC->database();

        $st = $ilDB->prepare("SELECT count(*) AS cnt FROM `" . $a_table_name . "`");
        $res = $ilDB->execute($st);
        $rec = $ilDB->fetchAssoc($res);

        return $rec["cnt"];
    }


    /**
     * Store performed step
     *
     * @param $a_table
     * @param $a_step
     */
    public function storeStep($a_table, $a_step)
    {
        $st = $this->ilDBInterface->prepareManip(
            "REPLACE INTO abstraction_progress (table_name, step)" . " VALUES (?,?)",
            array(
                "text",
                "integer",
            )
        );
        $this->ilDBInterface->execute(
            $st,
            array(
                $a_table,
                $a_step,
            )
        );
    }


    /**
     * Replace empty strings with null values
     *
     * @param $a_table
     *
     * @throws \ilDatabaseException
     */
    public function replaceEmptyStringsWithNull($a_table)
    {
        global $DIC;
        $ilDB = $DIC->database();

        $fields = $this->analyzer->getFieldInformation($a_table);
        $upfields = array();
        foreach ($fields as $field => $def) {
            if ($def["type"] == "text"
                && ($def["length"] >= 1 && $def["length"] <= 4000)
            ) {
                $upfields[] = $field;
            }
        }
        foreach ($upfields as $uf) {
            $ilDB->query("UPDATE `" . $a_table . "` SET `" . $uf . "` = null WHERE `" . $uf . "` = ''");
        }
    }


    /**
     * Replace empty dates with null
     *
     * @param $a_table
     *
     * @throws \ilDatabaseException
     */
    public function replaceEmptyDatesWithNull($a_table)
    {
        global $DIC;
        $ilDB = $DIC->database();

        if (!$this->ilDBInterface->tableExists($a_table)) {
            return;
        }

        $fields = $this->analyzer->getFieldInformation($a_table);
        $upfields = array();
        foreach ($fields as $field => $def) {
            if ($def["type"] == "timestamp") {
                $upfields[] = $field;
            }
        }
        foreach ($upfields as $uf) {
            $ilDB->query("UPDATE `" . $a_table . "` SET `" . $uf . "` = null WHERE `" . $uf . "` = '0000-00-00 00:00:00'");
        }

        $upfields = array();
        reset($fields);
        foreach ($fields as $field => $def) {
            if ($def["type"] == "date") {
                $upfields[] = $field;
            }
        }
        foreach ($upfields as $uf) {
            $ilDB->query("UPDATE `" . $a_table . "` SET `" . $uf . "` = null WHERE `" . $uf . "` = '0000-00-00'");
        }
    }


    /**
     * Lower case table and field names
     *
     * @param string $a_table_name
     */
    public function lowerCaseTableName($a_table_name)
    {
        global $DIC;
        $ilDB = $DIC->database();

        if ($a_table_name != strtolower($a_table_name)) {
            // this may look strange, but it does not work directly
            // (seems that mysql does not see no difference whether upper or lowercase characters are used
            mysql_query("ALTER TABLE `" . $a_table_name . "` RENAME `" . strtolower($a_table_name) . "xxx" . "`");
            mysql_query("ALTER TABLE `" . strtolower($a_table_name) . "xxx" . "` RENAME `" . strtolower($a_table_name) . "`");
        }
    }


    /**
     * lower case column names
     *
     * @param string $a_table_name
     */
    public function lowerCaseColumnNames($a_table_name)
    {
        global $DIC;
        $ilDB = $DIC->database();

        $result = mysql_query("SHOW COLUMNS FROM `" . $a_table_name . "`");
        while ($row = mysql_fetch_assoc($result)) {
            if ($row["Field"] != strtolower($row["Field"])) {
                $ilDB->renameTableColumn($a_table_name, $row["Field"], strtolower($row["Field"]));
            }
        }
    }


    /**
     * Remove auto_increment attribute of a field
     *
     * @param    string        table name
     * @param    string        autoincrement field
     */
    public function removeAutoIncrement($a_table_name, $a_auto_inc_field)
    {
        if ($a_auto_inc_field != "") {
            $this->ilDBInterface->modifyTableColumn($a_table_name, $a_auto_inc_field, array());
        }
    }


    /**
     * Remove primary key from table
     *
     * @param    string        table name
     * @param    array         primary key information
     */
    public function removePrimaryKey($a_table, $a_pk)
    {
        if ($a_pk["name"] != "") {
            $this->ilDBInterface->dropPrimaryKey($a_table, $a_pk["name"]);
        }
    }


    /**
     * Remove Indices
     *
     * @param    string        table name
     * @param    array         indices information
     */
    public function removeIndices($a_table, $a_indices)
    {
        if (is_array($a_indices)) {
            foreach ($a_indices as $index) {
                $this->ilDBInterface->query("ALTER TABLE `" . $a_table . "` DROP INDEX `" . $index["name"] . "`");
            }
        }
    }


    /**
     * Remove Constraints
     *
     * @param    string        table name
     * @param    array         constraints information
     */
    public function removeConstraints($a_table, $a_constraints)
    {
        if (is_array($a_constraints)) {
            foreach ($a_constraints as $c) {
                if ($c["type"] == "unique") {
                    $this->ilDBInterface->query("ALTER TABLE `" . $a_table . "` DROP INDEX `" . $c["name"] . "`");
                }
            }
        }
    }


    /**
     * @param        $a_table
     * @param        $a_fields
     * @param bool   $a_set_text_ts_fields_notnull_false
     * @param string $pk
     *
     * @return mixed
     * @throws \ilDatabaseException
     */
    public function alterTable($a_table, $a_fields, $a_set_text_ts_fields_notnull_false = true, $pk = "")
    {
        $n_fields = array();
        foreach ($a_fields as $field => $d) {
            $def = $this->reverse->getTableFieldDefinition($a_table, $field);
            $this->ilDBInterface->handleError($def);
            $best_alt = $this->analyzer->getBestDefinitionAlternative($def);
            $def = $def[$best_alt];

            // remove "current_timestamp" default for timestamps (not supported)
            if (strtolower($def["nativetype"]) == "timestamp"
                && strtolower($def["default"]) == "current_timestamp"
            ) {
                unset($def["default"]);
            }

            if (strtolower($def["type"]) == "float") {
                unset($def["length"]);
            }

            // remove all invalid attributes
            foreach ($def as $k => $v) {
                if (!in_array(
                    $k,
                    array(
                        "type",
                        "default",
                        "notnull",
                        "length",
                        "unsigned",
                        "fixed",
                    )
                )
                ) {
                    unset($def[$k]);
                }
            }

            // determine length for decimal type
            if ($def["type"] == "decimal") {
                $l_arr = explode(",", $def["length"]);
                $def["length"] = $l_arr[0];
            }

            // remove length values for float
            if ($def["type"] == "float") {
                unset($def["length"]);
            }

            // set notnull to false for text/timestamp/date fields
            if ($a_set_text_ts_fields_notnull_false
                && ($def["type"] == "text"
                    || $def["type"] == "timestamp"
                    || $def["type"] == "date")
                && (!is_array($pk) || !isset($field, $pk["fields"][$field]))
            ) {
                $def["notnull"] = false;
            }

            // set unsigned to false for integers
            if ($def["type"] == "integer") {
                $def["unsigned"] = false;
            }

            // set notnull to false for blob and clob
            if ($def["type"] == "blob" || $def["type"] == "clob") {
                $def["notnull"] = false;
            }

            // remove "0000-00-00..." default values
            if (($def["type"] == "timestamp" && $def["default"] == "0000-00-00 00:00:00")
                || ($def["type"] == "date" && $def["default"] == "0000-00-00")
            ) {
                unset($def["default"]);
            }

            $a = array();
            foreach ($def as $k => $v) {
                $a[$k] = $v;
            }
            $def["definition"] = $a;

            $n_fields[$field] = $def;
        }

        $changes = array(
            "change" => $n_fields,
        );

        if (!$this->getTestMode()) {
            $r = $this->manager->alterTable($a_table, $changes, false);
        } else {
            $r = $this->manager->createTable(strtolower($a_table) . "_copy", $n_fields);
        }

        return true;
    }


    /**
     * @param $a_table
     * @param $a_pk
     */
    public function addPrimaryKey($a_table, $a_pk)
    {
        if (is_array($a_pk["fields"])) {
            $fields = array();
            foreach ($a_pk["fields"] as $f => $pos) {
                $fields[] = strtolower($f);
            }
            $this->ilDBInterface->addPrimaryKey($a_table, $fields);
        }
    }


    /**
     * Add indices
     *
     * @param    string        table name
     * @param    array         indices information
     */
    public function addIndices($a_table, $a_indices)
    {
        if (is_array($a_indices)) {
            $all_valid = true;

            foreach ($a_indices as $index) {
                if (strlen($index["name"]) > 3) {
                    $all_valid = false;
                }
            }

            $cnt = 1;
            foreach ($a_indices as $index) {
                if (is_array($index["fields"])) {
                    if (!$all_valid) {
                        $index["name"] = "i" . $cnt;
                    }
                    $fields = array();
                    foreach ($index["fields"] as $f => $pos) {
                        $fields[] = strtolower($f);
                    }
                    $this->ilDBInterface->addIndex($a_table, $fields, strtolower($index["name"]), $index["fulltext"]);
                    $cnt++;
                }
            }
        }
    }


    /**
     * Add constraints
     *
     * @param    string        table name
     * @param    array         constraints information
     */
    public function addConstraints($a_table, $a_constraints)
    {
        if (is_array($a_constraints)) {
            $all_valid = true;

            foreach ($a_constraints as $c) {
                if (strlen($c["name"]) > 3) {
                    $all_valid = false;
                }
            }

            $cnt = 1;
            foreach ($a_constraints as $c) {
                if (is_array($c["fields"])) {
                    if (!$all_valid) {
                        $c["name"] = "c" . $cnt;
                    }
                    $fields = array();
                    foreach ($c["fields"] as $f => $pos) {
                        $fields[] = strtolower($f);
                    }
                    $this->ilDBInterface->addUniqueConstraint($a_table, $fields, strtolower($c["name"]));
                    $cnt++;
                }
            }
        }
    }


    /**
     * This is only used on tables that have already been abstracted
     * but missed the "full treatment".
     */
    public function fixIndexNames($a_table)
    {
        if (!$this->ilDBInterface->tableExists($a_table)) {
            return;
        }
        $all_valid = true;
        $indices = $this->analyzer->getIndicesInformation($a_table);
        foreach ($indices as $index) {
            if (strlen($index["name"]) > 3) {
                $all_valid = false;
            }
        }

        if (!$all_valid) {
            foreach ($indices as $index) {
                $this->ilDBInterface->dropIndex($a_table, $index["name"]);
            }
            $this->addIndices($a_table, $indices);
        }
    }


    /**
     * Add autoincrement sequence
     *
     * @param    string        table name
     * @param    string        autoincrement field
     */
    public function addAutoIncrementSequence($a_table, $a_auto_inc_field)
    {
        if ($a_auto_inc_field != "") {
            $set = $this->ilDBInterface->query("SELECT MAX(`" . strtolower($a_auto_inc_field) . "`) ma FROM `" . $a_table . "`");
            $rec = $this->ilDBInterface->fetchAssoc($set);
            $next = $rec["ma"] + 1;
            $this->ilDBInterface->createSequence($a_table, $next);
        }
    }


    /**
     * This is only used on tables that have already been abstracted
     * but missed the "full treatment".
     */
    public function fixClobNotNull($a_table)
    {
        if (!$this->ilDBInterface->tableExists($a_table)) {
            return;
        }
        $all_valid = true;
        $fields = $this->analyzer->getFieldInformation($a_table);
        foreach ($fields as $name => $def) {
            if ($def["type"] == "clob" && $def["notnull"] == true) {
                $this->ilDBInterface->modifyTableColumn(
                    $a_table,
                    $name,
                    array(
                        "type"    => "clob",
                        "notnull" => false,
                    )
                );
            }
        }
    }


    /**
     * This is only used on tables that have already been abstracted
     * but missed the "full treatment".
     */
    public function fixDatetimeValues($a_table)
    {
        if (!$this->ilDBInterface->tableExists($a_table)) {
            return;
        }
        $all_valid = true;
        $fields = $this->analyzer->getFieldInformation($a_table);
        foreach ($fields as $name => $def) {
            if ($def["type"] == "timestamp"
                && ($def["notnull"] == true || $def["default"] == "0000-00-00 00:00:00")
            ) {
                $nd = array(
                    "type"    => "timestamp",
                    "notnull" => false,
                );
                if ($def["default"] == "0000-00-00 00:00:00") {
                    $nd["default"] = null;
                }
                $this->ilDBInterface->modifyTableColumn($a_table, $name, $nd);
            }
            if ($def["type"] == "date"
                && ($def["notnull"] == true || $def["default"] == "0000-00-00")
            ) {
                $nd = array(
                    "type"    => "date",
                    "notnull" => false,
                );
                if ($def["default"] == "0000-00-00") {
                    $nd["default"] = null;
                }
                $this->ilDBInterface->modifyTableColumn($a_table, $name, $nd);
            }
        }
    }
}
