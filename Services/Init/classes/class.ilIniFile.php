<?php

declare(strict_types=1);
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * INIFile Parser
 * Early access in init proceess!
 * Avoid further dependencies like logging or other services
 * Description:
 * A Simpe Ini File Implementation to keep settings
 * in a simple file instead of in a DB
 * Based upon class.INIfile.php by Mircho Mirev <mircho@macropoint.com>
 * Usage Examples:
 * $ini = new IniFile("./ini.ini");
 * Read entire group in an associative array
 * $grp = $ini->read_group("MAIN");
 * //prints the variables in the group
 * if ($grp)
 * for(reset($grp); $key=key($grp); next($grp))
 * {
 * echo "GROUP ".$key."=".$grp[$key]."<br>";
 * }
 * //set a variable to a value
 * $ini->setVariable("NEW","USER","JOHN");
 * //Save the file
 * $ini->save_data();
 * @author  Mircho Mirev <mircho@macropoint.com>
 * @author  Peter Gabriel <peter@gabriel-online.net>
 * @version $Id$
 */
class ilIniFile
{
    /**
     * name of file
     */
    public string $INI_FILE_NAME = "";

    /**
     * error var
     */
    public string $ERROR = "";

    /**
     * sections in ini-file
     */
    public array $GROUPS = array();

    /**
     * actual section
     */
    public string $CURRENT_GROUP = "";

    /**
     * Constructor
     */
    public function __construct(string $a_ini_file_name)
    {
        //check if a filename is given
        if (empty($a_ini_file_name)) {
            $this->error("no_file_given");
        }

        $this->INI_FILE_NAME = $a_ini_file_name;
    }

    /**
     * read from ini file
     */
    public function read(): bool
    {
        //check if file exists
        if (!file_exists($this->INI_FILE_NAME)) {
            $this->error("file_does_not_exist");
            return false;
        } elseif ($this->parse() == false) {
            //parse the file
            return false;
        }
        return true;
    }

    /**
     * load and parse an inifile
     */
    public function parse(): bool
    {
        //use php4 function parse_ini_file
        $this->GROUPS = @parse_ini_file($this->INI_FILE_NAME, true);

        //check if groups are filled
        if ($this->GROUPS == false) {
            // second try
            $this->fixIniFile();

            $this->GROUPS = @parse_ini_file($this->INI_FILE_NAME, true);
            if ($this->GROUPS == false) {
                $this->error("file_not_accessible");
                return false;
            }
        }
        //set current group
        $temp = array_keys($this->GROUPS);
        $this->CURRENT_GROUP = $temp[count($temp) - 1];
        return true;
    }

    /**
     * Fix ini file (make it compatible for PHP 5.3)
     */
    public function fixIniFile(): void
    {
        // first read content
        $lines = array();
        $fp = @fopen($this->INI_FILE_NAME, "r");
        $starttag = '';
        while (!feof($fp)) {
            $l = fgets($fp, 4096);
            $skip = false;
            if ((substr($l, 0, 2) == "/*" && $starttag) ||
                substr($l, 0, 5) == "*/ ?>") {
                $skip = true;
            }
            $starttag = false;
            if (substr($l, 0, 5) == "<?php") {
                $l = "; <?php exit; ?>";
                $starttag = true;
            }
            if (!$skip) {
                $l = str_replace("\n", "", $l);
                $l = str_replace("\r", "", $l);
                $lines[] = $l;
            }
        }
        fclose($fp);

        // now write it back
        $fp = @fopen($this->INI_FILE_NAME, "w");

        if (!empty($fp)) {
            foreach ($lines as $l) {
                fwrite($fp, $l . "\r\n");
            }
        }
        fclose($fp);
    }

    /**
     * save ini-file-data to filesystem
     */
    public function write(): bool
    {
        $fp = @fopen($this->INI_FILE_NAME, "w");

        if (empty($fp)) {
            $this->error("Cannot create file $this->INI_FILE_NAME");
            return false;
        }

        //write php tags (security issue)
        $result = fwrite($fp, "; <?php exit; ?>\r\n");

        $groups = $this->readGroups();
        $group_cnt = count($groups);

        for ($i = 0; $i < $group_cnt; $i++) {
            $group_name = $groups[$i];
            //prevent empty line at beginning of ini-file
            if ($i == 0) {
                $res = sprintf("[%s]\r\n", $group_name);
            } else {
                $res = sprintf("\r\n[%s]\r\n", $group_name);
            }

            $result = fwrite($fp, $res);
            $group = $this->readGroup($group_name);

            for (reset($group); $key = key($group); next($group)) {
                $res = sprintf("%s = %s\r\n", $key, "\"" . $group[$key] . "\"");
                $result = fwrite($fp, $res);
            }
        }

        fclose($fp);
        return true;
    }

    /**
     * returns the content of IniFile
     */
    public function show(): string
    {
        $groups = $this->readGroups();
        $group_cnt = count($groups);

        //clear content
        $content = "";

        // go through all groups
        for ($i = 0; $i < $group_cnt; $i++) {
            $group_name = $groups[$i];
            //prevent empty line at beginning of ini-file
            if ($i == 0) {
                $content = sprintf("[%s]\n", $group_name);
            } else {
                $content .= sprintf("\n[%s]\n", $group_name);
            }

            $group = $this->readGroup($group_name);

            //go through group an display all variables
            for (reset($group); $key = key($group); next($group)) {
                $content .= sprintf("%s = %s\n", $key, $group[$key]);
            }
        }

        return $content;
    }

    /**
     * returns number of groups
     */
    public function getGroupCount(): int
    {
        return count($this->GROUPS);
    }

    /**
     * returns an array with the names of all the groups
     */
    public function readGroups(): array
    {
        $groups = array();

        for (reset($this->GROUPS); $key = key($this->GROUPS); next($this->GROUPS)) {
            $groups[] = $key;
        }

        return $groups;
    }

    /**
     * checks if a group exists
     */
    public function groupExists(string $a_group_name): bool
    {
        if (!isset($this->GROUPS[$a_group_name])) {
            return false;
        }

        return true;
    }

    /**
     * returns an associative array of the variables in one group
     */
    public function readGroup(string $a_group_name): array
    {
        if (!$this->groupExists($a_group_name)) {
            $this->error("Group '" . $a_group_name . "' does not exist");
            return [];
        }

        return $this->GROUPS[$a_group_name];
    }

    /**
     * adds a new group
     */
    public function addGroup(string $a_group_name): bool
    {
        if ($this->groupExists($a_group_name)) {
            $this->error("Group '" . $a_group_name . "' exists");
            return false;
        }

        $this->GROUPS[$a_group_name] = array();
        return true;
    }

    /**
     * removes a group
     */
    public function removeGroup(string $a_group_name): bool
    {
        if (!$this->groupExists($a_group_name)) {
            $this->error("Group '" . $a_group_name . "' does not exist");
            return false;
        }

        unset($this->GROUPS[$a_group_name]);
        return true;
    }

    /**
     * returns if a variable exists or not
     */
    public function variableExists(string $a_group, string $a_var_name): bool
    {
        return isset($this->GROUPS[$a_group][$a_var_name]);
    }

    /**
     * reads a single variable from a group
     */
    public function readVariable(string $a_group, string $a_var_name): string
    {
        if (!isset($this->GROUPS[$a_group][$a_var_name])) {
            $this->error("'" . $a_var_name . "' does not exist in '" . $a_group . "'");
            return '';
        }

        return trim($this->GROUPS[$a_group][$a_var_name]);
    }

    /**
     * sets a variable in a group
     */
    public function setVariable(string $a_group_name, string $a_var_name, string $a_var_value): bool
    {
        if (!$this->groupExists($a_group_name)) {
            $this->error("Group '" . $a_group_name . "' does not exist");
            return false;
        }

        $this->GROUPS[$a_group_name][$a_var_name] = $a_var_value;
        return true;
    }

    public function error(string $a_errmsg): bool
    {
        $this->ERROR = $a_errmsg;

        return true;
    }

    public function getError(): string
    {
        return $this->ERROR;
    }
} //END class.ilIniFile
