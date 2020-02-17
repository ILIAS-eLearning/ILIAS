<?php
/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class ilCtrlStructureReader
*
* Reads call structure of classes into db
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
*/
class ilCtrlStructureReader
{
    public $class_script;
    public $class_childs;
    public $executed;
    public $db = null;

    public function __construct($a_ini_file = null)
    {
        $this->class_script = array();
        $this->class_childs = array();
        $this->executed = false;
        $this->ini = $a_ini_file;
    }

    public function setIniFile($a_ini_file)
    {
        $this->ini = $a_ini_file;
    }
    
    /**
    * parse code files and store call structure in db
    */
    public function getStructure()
    {
        $ilDB = $this->getDB();

        $this->ini->setVariable("db", "structure_reload", "1");
        $this->ini->write();
        if ($this->ini->readVariable("db", "structure_reload") != "1") {
            echo "Error Cannot write client.ini.file.";
        }
        //$this->get_structure = true;
    }
        
    /**
    * read structure
    */
    public function readStructure(
        $a_force = false,
        $a_dir = "",
        $a_comp_prefix = "",
        $a_plugin_path = ""
    ) {
        $ilDB = $this->getDB();

        if (!$a_force && $this->ini->readVariable("db", "structure_reload") != "1") {
            return;
        }

        $this->flushCaches();
    
        // prefix for component
        $this->comp_prefix = $a_comp_prefix;

        // plugin path
        $this->plugin_path = $a_plugin_path;

        // only run one time per db_update request
        if (!$this->executed) {
            if ($a_dir == "") {
                $this->start_dir = ILIAS_ABSOLUTE_PATH;
                $this->read(ILIAS_ABSOLUTE_PATH);
            } else {
                $this->start_dir = $a_dir;
                $this->read($a_dir);
            }
            $this->store();
            $this->determineClassFileIds();
            $this->executed = true;
            if (!$a_force) {
                $this->ini->setVariable("db", "structure_reload", "0");
                $this->ini->write();
            }
        }
    }

    protected function flushCaches()
    {
        ilCachedCtrl::flush();
        ilGlobalCache::flushAll();
    }

    /**
    * read structure into internal variables
    *
    * @access private
    */
    public function read($a_cdir)
    {
        if (defined("ILIAS_ABSOLUTE_PATH")) {
            $il_absolute_path = ILIAS_ABSOLUTE_PATH;
        } else {
            $il_absolute_path = dirname(__FILE__, 5);
        }

        // check wether $a_cdir is a directory
        if (!@is_dir($a_cdir)) {
            return false;
        }

        foreach ($this->getFilesIn($il_absolute_path, $a_cdir) as list($file, $full_path)) {
            if (!$this->isInterestingFile($file)) {
                continue;
            }

            $content = file_get_contents($full_path);
            try {
                list($parent, $children) = $this->getIlCtrlCalls($content);
                if ($parent) {
                    $this->addClassScript($parent, $full_path);
                }
                foreach ($children as $child) {
                    $this->addClassChild($parent, $child);
                }
            } catch (\LogicException $e) {
                $e->setMessage("In file \"$full_path\": " . $e->getMessage());
                throw $e;
            }

            $handle = fopen($full_path, "r");
            while (!feof($handle)) {
                $line = fgets($handle, 4096);

                // handle isCalledBy comments
                $pos = strpos(strtolower($line), "@ilctrl_iscalledby");
                if (is_int($pos)) {
                    $com = substr($line, $pos + 19);
                    $pos2 = strpos($com, ":");
                    if (is_int($pos2)) {
                        $com_arr = explode(":", $com);
                        $child = strtolower(trim($com_arr[0]));
                        $this->addClassScript($child, $full_path);

                        $parents = explode(",", $com_arr[1]);
                        foreach ($parents as $parent) {
                            $parent = trim(strtolower($parent));
                            $this->addClassChild($parent, $child);
                        }
                    }
                }

                $cl = $this->getGUIClassNameFromClassFileName($file);
                if ($cl) {
                    $pos = strpos(strtolower($line), "class " . $cl);
                    if (is_int($pos)) {
                        $this->addClassScript($cl, $full_path);
                    }
                }
            }
            fclose($handle);
        }
    }

    protected function getFilesIn(string $il_absolute_path, string $dir)
    {
        foreach (scandir($dir) as $e) {
            if ($e == "." || $e == "..") {
                continue;
            }
            $f = $this->normalizePath("$dir/$e");
            if (@is_dir($f)) {
                if (!$this->shouldDescendToDirectory($il_absolute_path, $dir)) {
                    continue;
                }
                foreach ($this->getFilesIn($il_absolute_path, $f) as $s) {
                    yield $s;
                }
            }
            if (@is_file($f)) {
                yield [$e, $f];
            }
        }
    }

    protected function addClassScript(string $class, string $file_path) : void
    {
        if ($class == "") {
            throw new \InvalidArgumentException(
                "Can't add class script for an empty class."
            );
        }

        if (isset($this->class_script[$class]) && $this->class_script[$class] != $file_path) {
            $this->panicOnDuplicateClass($file_path, $class);
        }

        $this->class_script[$class] = $file_path;
    }

    protected function addClassChild(string $parent, string $child) : void
    {
        if ($parent == "") {
            throw new \InvalidArgumentException(
                "Can't add class child for an empty parent."
            );
        }

        if (!isset($this->class_childs[$parent])) {
            $this->class_childs[$parent] = [];
        }

        if (!in_array($child, $this->class_childs[$parent])) {
            $this->class_childs[$parent][] = $child;
        }
    }

    const IL_CTRL_CALLS_REGEXP = '~^.*@ilctrl_calls\s+(\w+)\s*:\s*(\w+(\s*,\s*\w+)*)\s*$~mi';

    /**
     * @return null|(string,string[])
     */
    protected function getIlCtrlCalls(string $content) : ?array
    {
        $res = [];
        if (!preg_match_all(self::IL_CTRL_CALLS_REGEXP, $content, $res)) {
            return null;
        }

        $class_names = array_unique($res[1]);
        if (count($class_names) != 1) {
            throw new \LogicException(
                "Found different class names in ilctrl_calls: " . join(",", $class_names)
            );
        }

        $children = [];
        foreach ($res[2] as $ls) {
            foreach (explode(",", $ls) as $l) {
                $children[] = strtolower(trim($l));
            }
        }

        return [strtolower(trim($class_names[0])), $children];
    }

    protected function shouldDescendToDirectory(string $il_absolute_path, string $dir)
    {
        $data_dir = $this->normalizePath($il_absolute_path . "/data");
        $customizing_dir = $this->normalizePath($il_absolute_path . "/Customizing");
        $dir = $this->normalizePath($dir);
        return $dir != $customizing_dir && $dir != $data_dir;
    }

    private function normalizePath(string $path) : string
    {
        return str_replace(['//'], ['/'], $path);
    }

    const INTERESTING_FILES_REGEXP = "~^(class\..*\.php)|(ilSCORM13Player\.php)$~i";

    protected function isInterestingFile(string $file) : bool
    {
        return preg_match(self::INTERESTING_FILES_REGEXP, $file);
    }

    const GUI_CLASS_FILE_REGEXP = "~^class\.(.*GUI)\.php$~i";

    protected function getGUIClassNameFromClassFileName(string $file) : ?string
    {
        $res = [];
        if (preg_match(self::GUI_CLASS_FILE_REGEXP, $file, $res)) {
            return strtolower($res[1]);
        }
        return null;
    }

    protected function panicOnDuplicateClass(string $full_path, string $parent)
    {
        $ilDB = $this->getDB();

        // delete all class to file assignments
        $ilDB->manipulate("DELETE FROM ctrl_classfile WHERE comp_prefix = " .
            $ilDB->quote($this->comp_prefix, "text"));
        if ($this->comp_prefix == "") {
            $ilDB->manipulate($q = "DELETE FROM ctrl_classfile WHERE " .
                $ilDB->equals("comp_prefix", "", "text", true));
        }

        // delete all call entries
        $ilDB->manipulate("DELETE FROM ctrl_calls WHERE comp_prefix = " .
            $ilDB->quote($this->comp_prefix, "text"));
        if ($this->comp_prefix == "") {
            $ilDB->manipulate("DELETE FROM ctrl_calls WHERE comp_prefix IS NULL");
        }

        $msg = implode("\n", [
            "Error: Duplicate call structure definition found (Class %s) in files:",
            "- %s",
            "- %s",
            "",
            "Please remove the file, that does not belong to the official ILIAS distribution.",
            "After that invoke 'Tools' -> 'Reload Control Structure' in the ILIAS Setup."
        ]);

        throw new \Exception(
            sprintf(
                $msg,
                $parent,
                $this->class_script[$parent],
                $full_path
            )
        );
    }

    /**
    * read structure into internal variables
    *
    * @access private
    */
    public function store($a_cdir = "./..")
    {
        $ilDB = $this->getDB();

        // delete all class to file assignments
        $ilDB->manipulate("DELETE FROM ctrl_classfile WHERE comp_prefix = " .
            $ilDB->quote($this->comp_prefix, "text"));
        if ($this->comp_prefix == "") {
            $ilDB->manipulate($q = "DELETE FROM ctrl_classfile WHERE " .
                $ilDB->equals("comp_prefix", "", "text", true));
        }

        // delete all call entries
        $ilDB->manipulate("DELETE FROM ctrl_calls WHERE comp_prefix = " .
            $ilDB->quote($this->comp_prefix, "text"));
        if ($this->comp_prefix == "") {
            $ilDB->manipulate("DELETE FROM ctrl_calls WHERE " .
                $ilDB->equals("comp_prefix", "", "text", true));
        }

        foreach ($this->class_script as $class => $script) {
            $file = substr($script, strlen($this->start_dir) + 1);
            
            // store class to file assignment
            $ilDB->manipulate(sprintf(
                "INSERT INTO ctrl_classfile (class, filename, comp_prefix, plugin_path) " .
                " VALUES (%s,%s,%s,%s)",
                $ilDB->quote($class, "text"),
                $ilDB->quote($file, "text"),
                $ilDB->quote($this->comp_prefix, "text"),
                $ilDB->quote($this->plugin_path, "text")
                ));
        }
        //$this->class_childs[$parent][] = $child;
        foreach ($this->class_childs as $parent => $v) {
            if (is_array($this->class_childs[$parent])) {
                foreach ($this->class_childs[$parent] as $child) {
                    if (strlen(trim($child)) and strlen(trim($parent))) {
                        // store call entry
                        $ilDB->manipulate(sprintf(
                            "INSERT INTO ctrl_calls (parent, child, comp_prefix) " .
                            "VALUES (%s,%s,%s)",
                            $ilDB->quote($parent, "text"),
                            $ilDB->quote($child, "text"),
                            $ilDB->quote($this->comp_prefix, "text")
                        ));
                    }
                }
            }
        }
    }

    /**
    * Determine class file IDS
    */
    public function determineClassFileIds()
    {
        $ilDB = $this->getDB();

        $ilDB->manipulate(
            "UPDATE ctrl_classfile SET " .
            " cid = " . $ilDB->quote("", "text")
            );
        $set = $ilDB->query("SELECT * FROM ctrl_classfile ");
        $cnt = 1;
        while ($rec  = $ilDB->fetchAssoc($set)) {
            $cid = base_convert((string) $cnt, 10, 36);
            $ilDB->manipulate(
                "UPDATE ctrl_classfile SET " .
                " cid = " . $ilDB->quote($cid, "text") .
                " WHERE class = " . $ilDB->quote($rec["class"], "text")
                );
            $cnt++;
        }
    }

    public function withDB(\ilDBInterface $db)
    {
        $clone = clone $this;
        $clone->db = $db;
        return $clone;
    }

    protected function getDB() : \ilDBInterface
    {
        if (!is_null($this->db)) {
            return $this->db;
        }
        //return ilDB in any case - backward compat.
        global $ilDB;
        return $ilDB;
    }
}
