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
    public $executed;
    public $db = null;
    protected $read_plugins = false;

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


    // ----------------------
    // READING CTRL STRUCTURE
    // ----------------------

    public function readStructure(
        $a_force = false,
        $a_dir = "",
        $a_comp_prefix = "",
        $a_plugin_path = ""
    ) : void {
        $ilDB = $this->getDB();

        if (!$a_force && $this->ini->readVariable("db", "structure_reload") != "1") {
            return;
        }

        // only run one time per db_update request
        if ($this->executed) {
            return;
        }

        $this->flushCaches();
    
        // prefix for component
        $this->comp_prefix = $a_comp_prefix;

        // plugin path
        $this->plugin_path = $a_plugin_path;

        if ($this->plugin_path != "" && $this->comp_prefix != "") {
            $this->read_plugins = true;
        }

        if ($a_dir == "") {
            $a_dir = $this->getILIASAbsolutePath();
        }

        $ctrl_structure = $this->readDirTo($a_dir, new \ilCtrlStructure());
        $this->storeToDB($ctrl_structure, $a_dir);
        $this->setClassFileIdsInDB();

        $this->executed = true;
        if (!$a_force) {
            $this->ini->setVariable("db", "structure_reload", "0");
            $this->ini->write();
        }
    }

    protected function flushCaches() : void
    {
        ilCachedCtrl::flush();
        ilGlobalCache::flushAll();
    }

    protected function readDirTo(string $a_cdir, \ilCtrlStructure $cs) : \ilCtrlStructure
    {
        // check wether $a_cdir is a directory
        if (!@is_dir($a_cdir)) {
            throw new \LogicException("'$a_cdir' is not a directory.");
        }

        foreach ($this->getFilesIn($a_cdir) as list($file, $full_path)) {
            if (!$this->isInterestingFile($file)) {
                continue;
            }

            $content = file_get_contents($full_path);
            try {
                $cs = $this->parseFileTo($cs, $full_path, $content);
            } catch (\LogicException $e) {
                throw new \LogicException("In file \"$full_path\": " . $e->getMessage(), $e->getCode(), $e);
            } catch (\RuntimeException $e) {
                if (!isset($e->class) || !isset($e->file_path)) {
                    throw $e;
                }
                $this->panicOnDuplicateClass(
                    $e->file_path,
                    $cs->getClassScriptOf($e->class),
                    $e->class
                );
            }
        }

        return $cs;
    }


    // ----------------------
    // DIRECTORY TRAVERSAL
    // ----------------------

    protected function getFilesIn(string $dir) : \Generator
    {
        foreach (scandir($dir) as $e) {
            if ($e == "." || $e == "..") {
                continue;
            }
            $f = $this->normalizePath("$dir/$e");
            if (@is_dir($f)) {
                if (!$this->shouldDescendToDirectory($dir)) {
                    continue;
                }
                foreach ($this->getFilesIn($f) as $s) {
                    yield $s;
                }
            }
            if (@is_file($f)) {
                yield [$e, $f];
            }
        }
    }

    protected function shouldDescendToDirectory(string $dir) : bool
    {
        $il_absolute_path = $this->getILIASAbsolutePath();
        $data_dir = $this->normalizePath($il_absolute_path . "/data");
        $customizing_dir = $this->normalizePath($il_absolute_path . "/Customizing");

        $dir = $this->normalizePath($dir);
        if ($this->read_plugins) {
            return $dir != $data_dir;
        }
        return $dir != $customizing_dir && $dir != $data_dir;
    }

    protected function normalizePath(string $path) : string
    {
        return realpath(str_replace(['//'], ['/'], $path));
    }

    const INTERESTING_FILES_REGEXP = "~^(class\..*\.php)$~i";

    protected function isInterestingFile(string $file) : bool
    {
        return preg_match(self::INTERESTING_FILES_REGEXP, $file);
    }


    // ----------------------
    // RESULT STORAGE
    // ----------------------

    protected function panicOnDuplicateClass(string $full_path, string $other_path, string $parent) : void
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
                $other_path,
                $full_path
            )
        );
    }

    protected function storeToDB(\ilCtrlStructure $ctrl_structure, string $start_dir) : void
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

        foreach ($ctrl_structure->getClassScripts() as $class => $script) {
            $file = substr(realpath($script), strlen(realpath($start_dir)) + 1);
            // store class to file assignment
            $ilDB->manipulate(sprintf(
                "INSERT IGNORE INTO ctrl_classfile (class, filename, comp_prefix, plugin_path) " .
                " VALUES (%s,%s,%s,%s)",
                $ilDB->quote($class, "text"),
                $ilDB->quote($file, "text"),
                $ilDB->quote($this->comp_prefix, "text"),
                $ilDB->quote($this->plugin_path, "text")
            ));
        }
        //$this->class_childs[$parent][] = $child;
        foreach ($ctrl_structure->getClassChildren() as $parent => $children) {
            if (!strlen($parent)) {
                continue;
            }
            foreach ($children as $child) {
                if (!strlen(trim($child))) {
                    continue;
                }
                // store call entry
                $ilDB->manipulate(sprintf(
                    "INSERT IGNORE INTO ctrl_calls (parent, child, comp_prefix) " .
                    "VALUES (%s,%s,%s)",
                    $ilDB->quote($parent, "text"),
                    $ilDB->quote($child, "text"),
                    $ilDB->quote($this->comp_prefix, "text")
                ));
            }
        }
    }

    protected function setClassFileIdsInDB() : void
    {
        $ilDB = $this->getDB();

        $ilDB->manipulate(
            "UPDATE ctrl_classfile SET " .
            " cid = " . $ilDB->quote("", "text")
        );
        $set = $ilDB->query("SELECT * FROM ctrl_classfile ");
        $cnt = 1;
        while ($rec = $ilDB->fetchAssoc($set)) {
            $cid = base_convert((string) $cnt, 10, 36);
            $ilDB->manipulate(
                "UPDATE ctrl_classfile SET " .
                " cid = " . $ilDB->quote($cid, "text") .
                " WHERE class = " . $ilDB->quote($rec["class"], "text")
            );
            $cnt++;
        }
    }


    // ----------------------
    // PARSING
    // ----------------------

    /**
     * @throw \LogicException if some file declares control structure for multiple classes
     * @throw \RuntimeException if there are different locations defined for some class.
     */
    protected function parseFileTo(\ilCtrlStructure $cs, string $full_path, string $content) : \ilCtrlStructure
    {
        list($parent, $children) = $this->getIlCtrlCalls($content);
        if ($parent) {
            $cs = $cs->withClassScript($parent, $full_path);
        }
        if ($children) {
            foreach ($children as $child) {
                $cs = $cs->withClassChild($parent, $child);
            }
        }

        list($child, $parents) = $this->getIlCtrlIsCalledBy($content);
        if ($child) {
            $cs = $cs->withClassScript($child, $full_path);
        }
        if ($parents) {
            foreach ($parents as $parent) {
                $cs = $cs->withClassChild($parent, $child);
            }
        }

        $cl = $this->getGUIClassNameFromClassPath($full_path);
        if ($cl && $this->containsClassDefinitionFor($cl, $content)) {
            $cs = $cs->withClassScript($cl, $full_path);
        }

        return $cs;
    }

    // ----------------------
    // GUI CLASS FINDING
    // ----------------------

    const GUI_CLASS_FILE_REGEXP = "~^.*[/\\\\]class\.(.*GUI)\.php$~i";

    protected function getGUIClassNameFromClassPath(string $path) : ?string
    {
        $res = [];
        if (preg_match(self::GUI_CLASS_FILE_REGEXP, $path, $res)) {
            return strtolower($res[1]);
        }
        return null;
    }

    protected function containsClassDefinitionFor(string $class, string $content) : bool
    {
        $regexp = "~.*class\s+$class~mi";
        return preg_match($regexp, $content) != 0;
    }


    // ----------------------
    // ILCTRL DECLARATION FINDING
    // ----------------------

    const IL_CTRL_DECLARATION_REGEXP = '~^.*@{WHICH}\s+([\w\\\\]+)\s*:\s*([\w\\\\]+(\s*,\s*[\w\\\\]+)*)\s*$~mi';

    /**
     * @return null|(string,string[])
     */
    protected function getIlCtrlCalls(string $content) : ?array
    {
        return $this->getIlCtrlDeclarations($content, "ilctrl_calls");
    }

    /**
     * @return null|(string,string[])
     */
    protected function getIlCtrlIsCalledBy(string $content) : ?array
    {
        return $this->getIlCtrlDeclarations($content, "ilctrl_iscalledby");
    }

    /**
     * @return null|(string,string[])
     */
    protected function getIlCtrlDeclarations(string $content, string $which) : ?array
    {
        $regexp = str_replace("{WHICH}", $which, self::IL_CTRL_DECLARATION_REGEXP);
        $res = [];
        if (!preg_match_all($regexp, $content, $res)) {
            return null;
        }

        $class_names = array_unique($res[1]);
        if (count($class_names) != 1) {
            throw new \LogicException(
                "Found different class names in ilctrl_calls: " . join(",", $class_names)
            );
        }

        $declaration = [];
        foreach ($res[2] as $ls) {
            foreach (explode(",", $ls) as $l) {
                $declaration[] = strtolower(trim($l));
            }
        }

        return [strtolower(trim($class_names[0])), $declaration];
    }


    // ----------------------
    // DEPENDENCIES
    // ----------------------

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

    protected function getILIASAbsolutePath() : string
    {
        if (defined("ILIAS_ABSOLUTE_PATH")) {
            return $this->normalizePath(ILIAS_ABSOLUTE_PATH);
        } else {
            return dirname(__FILE__, 5);
        }
    }
}
