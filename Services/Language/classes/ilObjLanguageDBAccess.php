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
 ********************************************************************
 */
class ilObjLanguageDBAccess
{
    protected ilDBInterface $ilDB;
    protected string $key;
    protected array $content;
    protected string $scope;
    protected array $local_changes;
    protected ?string $change_date = null;
    protected string $separator;
    protected string $comment_separator;
    
    public function __construct(ilDBInterface $ilDB, string $key, array $content, array $local_changes, string $scope = "", string $separator = "#:#", string $comment_separator = "###")
    {
        $this->ilDB = $ilDB;
        $this->key = $key;
        $this->content = $content;
        $this->local_changes = $local_changes;
        $this->scope = $scope;
        if ($scope === "local") {
            $this->change_date = date("Y-m-d H:i:s", time());
        }
        $this->separator = $separator;
        $this->comment_separator = $comment_separator;
    }
    
    public function insertLangEntries(string $lang_file): array
    {
        // initialize the array for updating lng_modules below
        $lang_array = array();
        $lang_array["common"] = array();
        
        $double_checker = [];
        $query_check = false;
        $query = "INSERT INTO lng_data (module,identifier,lang_key,value,local_change,remarks) VALUES ";
        foreach ($this->content as $val) {
            // split the line of the language file
            // [0]: module
            // [1]: identifier
            // [2]: value
            // [3]: comment (optional)
            $separated = explode($this->separator, trim($val));
            $pos = strpos($separated[2], $this->comment_separator);
            if ($pos !== false) {
                $separated[3] = substr($separated[2], $pos + strlen($this->comment_separator));
                $separated[2] = substr($separated[2], 0, $pos);
            }
            
            // check if the value has a local change
            $local_value = $this->local_changes[$separated[0]][$separated[1]] ?? "";
            
            if (empty($this->scope)) {
                // import of a global language file
                if ($local_value !== "" && $local_value !== $separated[2]) {
                    // keep an existing and different local value
                    $lang_array[$separated[0]][$separated[1]] = $local_value;
                    continue;
                }
            } elseif ($this->scope === "local") {
                // import of a local language file
                if ($local_value !== "") {
                    // keep a locally changed value that is newer than the file
                    $lang_array[$separated[0]][$separated[1]] = $local_value;
                    continue;
                }
            }
            if ($double_checker[$separated[0]][$separated[1]][$this->key] ?? false) {
                global $DIC;
                /** @var ilErrorHandling $ilErr */
                $ilErr = $DIC["ilErr"];
                $ilErr->raiseError(
                    "Duplicate Language Entry in $lang_file:\n$val",
                    $ilErr->MESSAGE
                );
            }
            $double_checker[$separated[0]][$separated[1]][$this->key] = true;
            
            $query .= sprintf(
                "(%s,%s,%s,%s,%s,%s),",
                $this->ilDB->quote($separated[0], "text"),
                $this->ilDB->quote($separated[1], "text"),
                $this->ilDB->quote($this->key, "text"),
                $this->ilDB->quote($separated[2], "text"),
                $this->ilDB->quote($this->change_date, "timestamp"),
                $this->ilDB->quote($separated[3] ?? null, "text")
            );
            $query_check = true;
            $lang_array[$separated[0]][$separated[1]] = $separated[2];
        }
        $query = rtrim($query, ",") . " ON DUPLICATE KEY UPDATE value=VALUES(value),remarks=VALUES(remarks);";
        if ($query_check) {
            $this->ilDB->manipulate($query);
        }
        
        return $lang_array;
    }
    
    public function replaceLangModules(array $lang_array): void
    {
        // avoid flushing the whole cache (see mantis #28818)
        ilCachedLanguage::getInstance($this->key)->deleteInCache();
        
        $query = "INSERT INTO lng_modules (module, lang_key, lang_array) VALUES ";
        foreach ($lang_array as $module => $lang_arr) {
            if ($this->scope === "local") {
                $q = "SELECT * FROM lng_modules WHERE " .
                    " lang_key = " . $this->ilDB->quote($this->key, "text") .
                    " AND module = " . $this->ilDB->quote($module, "text");
                $set = $this->ilDB->query($q);
                $row = $this->ilDB->fetchAssoc($set);
                $arr2 = isset($row["lang_array"]) ? unserialize($row["lang_array"], ["allowed_classes" => false]) : "";
                if (is_array($arr2)) {
                    $lang_arr = array_merge($arr2, $lang_arr);
                }
            }
            $query .= sprintf(
                "(%s,%s,%s),",
                $this->ilDB->quote($module, "text"),
                $this->ilDB->quote($this->key, "text"),
                $this->ilDB->quote(serialize($lang_arr), "clob"),
            );
        }
        $this->ilDB->manipulate(sprintf(
            "DELETE FROM lng_modules WHERE lang_key = %s",
            $this->ilDB->quote($this->key, "text"),
        ));
        
        $query = rtrim($query, ",") . ";";
        $this->ilDB->manipulate($query);
        
        // check if the module is correctly saved
        // see mantis #20046 and #19140
        $this->checkModules();
    }
    
    protected function checkModules(): void
    {
        $result = $this->ilDB->queryF(
            "SELECT module, lang_array FROM lng_modules WHERE lang_key = %s",
            array("text"),
            array($this->key)
        );
        
        foreach ($this->ilDB->fetchAll($result) as $module) {
            $unserialied = unserialize($module["lang_array"], ["allowed_classes" => false]);
            if (!is_array($unserialied)) {
                global $DIC;
                /** @var ilErrorHandling $ilErr */
                $ilErr = $DIC["ilErr"];
                $ilErr->raiseError(
                    "Data for module '" . $module["module"] . "' of  language '" . $this->key . "' is not correctly saved. " .
                    "Please check the collation of your database tables lng_data and lng_modules. It must be utf8_unicode_ci.",
                    $ilErr->MESSAGE
                );
            }
        }
    }
}