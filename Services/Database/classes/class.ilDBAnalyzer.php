<?php

declare(strict_types=1);

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
 * This class gives all kind of DB information using the database manager
 * and reverse module.
 *
 * @author     Alex Killing <alex.killing@gmx.de>
 * @author     Fabian Schmid <fs@studer-raimann.ch>
 *
 * @ingroup    ServicesDatabase
 *
 * @deprecated Use global ilDB only. If something is missing there, please contact fs@studer-raimann.ch
 */
class ilDBAnalyzer
{
    protected ilDBManager $manager;

    protected ilDBReverse $reverse;

    protected ilDBInterface$il_db;

    protected array $allowed_attributes;


    /**
     * ilDBAnalyzer constructor.
     *
     *
     * @deprecated Use global ilDB only. If something is missing there, please contact fs@studer-raimann.ch
     */
    public function __construct(ilDBInterface $ilDBInterface = null)
    {
        if (!$ilDBInterface instanceof ilDBInterface) {
            global $DIC;
            $ilDB = $DIC->database();
            $ilDBInterface = $ilDB;
        }

        $this->manager = $ilDBInterface->loadModule(ilDBConstants::MODULE_MANAGER);
        $this->reverse = $ilDBInterface->loadModule(ilDBConstants::MODULE_REVERSE);
        $this->il_db = $ilDBInterface;
        $this->allowed_attributes = $ilDBInterface->getAllowedAttributes();
    }


    /**
     * Get field information of a table.
     *
     * @return array field information array
     */
    public function getFieldInformation(string $a_table, bool $a_remove_not_allowed_attributes = false): array
    {
        $fields = $this->manager->listTableFields($a_table);
        $inf = array();
        foreach ($fields as $field) {
            $rdef = $this->reverse->getTableFieldDefinition($a_table, $field);
            // is this possible?
            if (isset($rdef["mdb2type"], $rdef["type"]) && $rdef["type"] !== $rdef["mdb2type"]) {
                throw new ilDatabaseException("ilDBAnalyzer::getFielInformation: Found type != mdb2type: $a_table, $field");
            }

            $best_alt = $this->getBestDefinitionAlternative($rdef);

            // collect other alternatives
            reset($rdef);
            $alt_types = "";
            foreach (array_keys($rdef) as $k) {
                if ($k !== $best_alt) {
                    $alt_types .= ($rdef[$k]["type"] ?? "") . ($rdef[$k]["length"] ?? "") . " ";
                }
            }

            $inf[$field] = array(
                "notnull" => $rdef[$best_alt]["notnull"] ?? null,
                "nativetype" => $rdef[$best_alt]["nativetype"] ?? null,
                "length" => $rdef[$best_alt]["length"] ?? null,
                "unsigned" => $rdef[$best_alt]["unsigned"] ?? null,
                "default" => $rdef[$best_alt]["default"] ?? null,
                "fixed" => $rdef[$best_alt]["fixed"] ?? null,
                "autoincrement" => $rdef[$best_alt]["autoincrement"] ?? null,
                "type" => $rdef[$best_alt]["type"] ?? null,
                "alt_types" => $alt_types,
            );

            if ($a_remove_not_allowed_attributes) {
                foreach (array_keys($inf[$field]) as $k) {
                    if ($k !== "type" && !in_array($k, $this->allowed_attributes[$inf[$field]["type"]])) {
                        unset($inf[$field][$k]);
                    }
                }
            }
        }

        return $inf;
    }


    /**
     * @return int|string
     */
    public function getBestDefinitionAlternative(array $a_def)
    {
        // determine which type to choose
        $car = array(
            "boolean" => 10,
            "integer" => 20,
            "decimal" => 30,
            "float" => 40,
            "date" => 50,
            "time" => 60,
            "timestamp" => 70,
            "text" => 80,
            "clob" => 90,
            "blob" => 100,
        );

        $cur_car = 0;
        $best_alt = 0;    // best alternatice
        foreach ($a_def as $k => $rd) {
            if ($car[$rd["type"]] > $cur_car) {
                $cur_car = $car[$rd["type"]];
                $best_alt = $k;
            }
        }

        return $best_alt;
    }


    /**
     * Gets the auto increment field of a table.
     * This should be used on ILIAS 3.10.x "MySQL" tables only.
     *
     * @return string|bool name of autoincrement field
     */
    public function getAutoIncrementField(string $a_table)
    {
        $fields = $this->manager->listTableFields($a_table);

        foreach ($fields as $field) {
            $rdef = $this->reverse->getTableFieldDefinition($a_table, $field);
            if ($rdef[0]["autoincrement"]) {
                return $field;
            }
        }

        return false;
    }


    /**
     * Get primary key of a table
     *
     * @return array primary key information array
     */
    public function getPrimaryKeyInformation(string $a_table): array
    {
        $constraints = $this->manager->listTableConstraints($a_table);

        $pk = false;
        foreach ($constraints as $c) {
            $info = $this->reverse->getTableConstraintDefinition($a_table, $c);

            if ($info["primary"]) {
                $pk["name"] = $c;
                foreach ($info["fields"] as $k => $f) {
                    $pk["fields"][$k] = array(
                        "position" => $f["position"],
                        "sorting" => $f["sorting"],
                    );
                }
            }
        }

        return $pk;
    }


    /**
     * Get information on indices of a table.
     * Primary key is NOT included!
     * Fulltext indices are included and marked.
     *
     * @return array indices information array
     */
    public function getIndicesInformation(string $a_table, bool $a_abstract_table = false): array
    {
        //$constraints = $this->manager->listTableConstraints($a_table);
        $indexes = $this->manager->listTableIndexes($a_table);

        // get additional information if database is MySQL
        $mysql_info = array();

        $set = $this->il_db->query("SHOW INDEX FROM " . $a_table);
        while ($rec = $this->il_db->fetchAssoc($set)) {
            if (!empty($rec["Key_name"])) {
                $mysql_info[$rec["Key_name"]] = $rec;
            } else {
                $mysql_info[$rec["key_name"]] = $rec;
            }
        }


        $ind = array();
        foreach ($indexes as $c) {
            $info = $this->reverse->getTableIndexDefinition($a_table, $c);

            $i = array();
            if (!$info["primary"]) {
                $i["name"] = $c;
                $i["fulltext"] = false;

                if ($mysql_info[$i["name"]]["Index_type"] === "FULLTEXT"
                    || $mysql_info[$i["name"] . "_idx"]["Index_type"] === "FULLTEXT"
                    || $mysql_info[$i["name"]]["index_type"] === "FULLTEXT"
                    || $mysql_info[$i["name"] . "_idx"]["index_type"] === "FULLTEXT"
                ) {
                    $i["fulltext"] = true;
                }
                foreach ($info["fields"] as $k => $f) {
                    $i["fields"][$k] = array(
                        "position" => $f["position"],
                        "sorting" => $f["sorting"],
                    );
                }
                $ind[] = $i;
            }
        }

        return $ind;
    }


    /**
     * Get information on constraints of a table.
     * Primary key is NOT included!
     * Fulltext indices are included and marked.
     *
     * @return array indices information array
     */
    public function getConstraintsInformation(string $a_table, bool $a_abstract_table = false): array
    {
        $constraints = $this->manager->listTableConstraints($a_table);

        $cons = array();
        foreach ($constraints as $c) {
            $info = $this->reverse->getTableConstraintDefinition($a_table, $c);
            $i = array();
            if ($info["unique"] ?? null) {
                $i["name"] = $c;
                $i["type"] = "unique";
                foreach ($info["fields"] as $k => $f) {
                    $i["fields"][$k] = array(
                        "position" => $f["position"],
                        "sorting" => $f["sorting"],
                    );
                }
                $cons[] = $i;
            }
        }

        return $cons;
    }


    /**
     * Check whether sequence is defined for current table (only works on "abstraced" tables)
     *
     * @return float|int|bool false, if no sequence is defined, start number otherwise
     *
     * @throws \ilDatabaseException
     * @deprecated Please do not use since this method will lead to a ilDatabaseException. Will be removed later
     */
    public function hasSequence(string $a_table)
    {
        $seq = $this->manager->listSequences();
        if (is_array($seq) && in_array($a_table, $seq)) {
            // sequence field is (only) primary key field of table
            $pk = $this->getPrimaryKeyInformation($a_table);
            if (is_array($pk["fields"]) && count($pk["fields"]) === 1) {
                $seq_field = key($pk["fields"]);
            } else {
                throw new ilDatabaseException("ilDBAnalyzer::hasSequence: Error, sequence defined, but no one-field primary key given. Table: "
                                              . $a_table . ".");
            }

            $set = $this->il_db->query("SELECT MAX(" . $this->il_db->quoteIdentifier($seq_field) . ") ma FROM " . $this->il_db->quoteIdentifier($a_table));
            $rec = $this->il_db->fetchAssoc($set);

            return $rec["ma"] + 1;
        }

        return false;
    }
}
