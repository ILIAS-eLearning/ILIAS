<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

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
        global $ilDB;
        
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
        global $ilDB;
        
        if (!$a_force && $this->ini->readVariable("db", "structure_reload") != "1") {
            return;
        }

        require_once('./Services/UICore/classes/class.ilCachedCtrl.php');
        ilCachedCtrl::flush();
        require_once('./Services/GlobalCache/classes/class.ilGlobalCache.php');
        ilGlobalCache::flushAll();
    
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

    /**
    * read structure into internal variables
    *
    * @access private
    */
    public function read($a_cdir)
    {
        global $ilDB, $lng;
        
        // check wether $a_cdir is a directory
        if (!@is_dir($a_cdir)) {
            return false;
        }

        // read current directory
        $dir = opendir($a_cdir);

        while ($file = readdir($dir)) {
            if ($file != "." and
                $file != "..") {
                // directories
                if (@is_dir($a_cdir . "/" . $file)) {
                    if ($a_cdir . "/" . $file != ILIAS_ABSOLUTE_PATH . "/data" &&
                        $a_cdir . "/" . $file != ILIAS_ABSOLUTE_PATH . "/Customizing") {
                        $this->read($a_cdir . "/" . $file);
                    }
                }

                // files
                if (@is_file($a_cdir . "/" . $file)) {
                    if (preg_match("~^class.*php$~i", $file) || preg_match("~^ilSCORM13Player.php$~i", $file)) {
                        $handle = fopen($a_cdir . "/" . $file, "r");
                        //echo "<br>".$a_cdir."/".$file;
                        while (!feof($handle)) {
                            $line = fgets($handle, 4096);

                            // handle @ilctrl_calls
                            $pos = strpos(strtolower($line), "@ilctrl_calls");
                            if (is_int($pos)) {
                                $com = substr($line, $pos + 14);
                                $pos2 = strpos($com, ":");
                                if (is_int($pos2)) {
                                    $com_arr = explode(":", $com);
                                    $parent = strtolower(trim($com_arr[0]));
                                    
                                    // check file duplicates
                                    if ($parent != "" && isset($this->class_script[$parent]) &&
                                        $this->class_script[$parent] != $a_cdir . "/" . $file) {
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

                                        throw new \Exception(
                                            sprintf(
                                                $lng->txt("duplicate_ctrl"),
                                                $parent,
                                                $this->class_script[$parent],
                                                $a_cdir . "/" . $file
                                            )
                                        );
                                    }

                                    $this->class_script[$parent] = $a_cdir . "/" . $file;
                                    $childs = explode(",", $com_arr[1]);
                                    foreach ($childs as $child) {
                                        $child = trim(strtolower($child));
                                        if (!is_array($this->class_childs[$parent]) || !in_array($child, $this->class_childs[$parent])) {
                                            $this->class_childs[$parent][] = $child;
                                        }
                                    }
                                }
                            }

                            // handle isCalledBy comments
                            $pos = strpos(strtolower($line), "@ilctrl_iscalledby");
                            if (is_int($pos)) {
                                $com = substr($line, $pos + 19);
                                $pos2 = strpos($com, ":");
                                if (is_int($pos2)) {
                                    $com_arr = explode(":", $com);
                                    $child = strtolower(trim($com_arr[0]));
                                    $this->class_script[$child] = $a_cdir . "/" . $file;

                                    $parents = explode(",", $com_arr[1]);
                                    foreach ($parents as $parent) {
                                        $parent = trim(strtolower($parent));
                                        if (!is_array($this->class_childs[$parent]) || !in_array($child, $this->class_childs[$parent])) {
                                            $this->class_childs[$parent][] = $child;
                                        }
                                    }
                                }
                            }
                            
                            if (preg_match("~^class\.(.*GUI)\.php$~i", $file, $res)) {
                                $cl = strtolower($res[1]);
                                $pos = strpos(strtolower($line), "class " . $cl);
                                if (is_int($pos) && $this->class_script[$cl] == "") {
                                    $this->class_script[$cl] = $a_cdir . "/" . $file;
                                    //echo "<br>".$cl."-".$this->class_script[$cl]."-";
                                }
                            }
                        }
                        fclose($handle);
                    }
                }
            }
        }
    }

    /**
    * read structure into internal variables
    *
    * @access private
    */
    public function store($a_cdir = "./..")
    {
        global $ilDB;

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
        global $ilDB;
    
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
}
