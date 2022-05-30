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
 * Interface ilQueryUtilsInterface
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ilQueryUtilsInterface
{

    /**
     * @param string[] $values
     */
    public function in(string $field, array $values, bool $negate = false, string $type = "") : string;


    /**
     * @param mixed $value
     */
    public function quote($value, ?string $type = null) : string;


    public function concat(array $values, bool $allow_null = true) : string;


    public function locate(string $a_needle, string $a_string, int $a_start_pos = 1) : string;


    public function free(ilPDOStatement $statement) : bool;


    public function quoteIdentifier(string $identifier) : string;


    /**
     * @throws \ilDatabaseException
     */
    public function createTable(string $name, array $fields, array $options = []) : string;


    /**
     * @throws \ilDatabaseException
     */
    public function like(string $column, string $type, string $value = "?", bool $case_insensitive = true) : string;


    public function now() : string;


    public function lock(array $tables) : string;


    public function unlock() : string;


    public function createDatabase(string $name, string $charset = "utf8", string $collation = "") : string;


    public function groupConcat(string $a_field_name, string $a_seperator = ",", string $a_order = null) : string;


    /**
     * @param mixed $a_dest_type
     */
    public function cast(string $a_field_name, $a_dest_type) : string;
}
