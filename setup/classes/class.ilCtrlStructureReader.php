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
	var $class_script;
	var $class_childs;
	var $executed;
	var $db = null;

	function __construct($a_ini_file = null)
	{
		$this->class_script = array();
		$this->class_childs = array();
		$this->executed = false;
		$this->ini = $a_ini_file;
	}

	function setIniFile($a_ini_file)
	{
		$this->ini = $a_ini_file;
	}
	
	/**
	* parse code files and store call structure in db
	*/
	function getStructure()
	{
		$ilDB = $this->getDB();

		$this->ini->setVariable("db","structure_reload", "1");
		$this->ini->write();
		if ($this->ini->readVariable("db","structure_reload") != "1")
		{
			echo "Error Cannot write client.ini.file.";
		}
		//$this->get_structure = true;
	}
		
	/**
	* read structure
	*/
	function readStructure($a_force = false, $a_dir = "", $a_comp_prefix = "",
		$a_plugin_path = "")
	{
		$ilDB = $this->getDB();

		if (!$a_force && $this->ini->readVariable("db","structure_reload") != "1")
		{
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
		if (!$this->executed)
		{
			if ($a_dir == "")
			{
				$this->start_dir = ILIAS_ABSOLUTE_PATH;
				$this->read(ILIAS_ABSOLUTE_PATH);
			}
			else
			{
				$this->start_dir = $a_dir;
				$this->read($a_dir);
			}
			$this->store();
			$this->determineClassFileIds();
			$this->executed = true;
			if (!$a_force)
			{
				$this->ini->setVariable("db","structure_reload", "0");
				$this->ini->write();
			}
		}
	}

	/**
	* read structure into internal variables
	*
	* @access private
	*/
	function read($a_cdir)
	{
		$ilDB = $this->getDB();
		$il_absolute_path = realpath(dirname(__FILE__) .'/../../');

        $a_cdir = preg_replace('#//#', '/', $a_cdir);
        if (!is_dir($a_cdir) || !is_readable($a_cdir)) {
            return false;
        }

        $directory_iter = new RecursiveDirectoryIterator(
            $a_cdir,
            FilesystemIterator::SKIP_DOTS
        );

        $filtered_directory_iter = new class($directory_iter, $il_absolute_path) extends RecursiveFilterIterator {
            private $directory_blacklist = [
                '.git', 'CI', 'cron', 'Customizing', 'data', 'dicto', 'docs', 'include', 'lang',
                'libs', 'setup', 'src', 'sso', 'templates', 'tests', 'webservice', 'xml', 
            ];

            private $file_matching_regex = '/^((class\..*?\.php)|(ilSCORM13Player\.php))$/i';

            private $ilias_absolute_path = '';
            
            public function __construct(RecursiveIterator $iter, string $ilias_absolute_path)
            {
                $this->ilias_absolute_path = $ilias_absolute_path;
                parent::__construct($iter);
            }

            public function accept()
            {
                /** @var SplFileInfo $file */
                $file = $this->current();
                if (!$file->isDir()) {
                    return preg_match($this->file_matching_regex, $file->getFilename());
                }

                return !in_array(
                    $file->getFilename(),
                    $this->directory_blacklist,
                    true
                );
            }

            public function getChildren() 
            {
                return new self($this->getInnerIterator()->getChildren(), $this->ilias_absolute_path);
            }
        };

        foreach (new RecursiveIteratorIterator($filtered_directory_iter,  RecursiveIteratorIterator::LEAVES_ONLY) as $file) {
            /** @var SplFileInfo $file */
            $this->readFile($file, $ilDB);
        }
    }

    private function readFile(SplFileInfo $file, ilDBInterface $ilDB) : void
    {
        $handle = fopen($file->getPathname(), 'r');
        if (!is_resource($handle)) {
            throw new \Exception(sprintf(
                'Error: Could not open file: %s',
                $file->getPathname()
            ));
        }

        while (!feof($handle)) {
            $line = fgets($handle, 4096);

            $pos = strpos(strtolower($line), "@ilctrl_calls");
            if (is_int($pos)) {
                $com = substr($line, $pos + 14);
                $pos2 = strpos($com, ":");
                if (is_int($pos2)) {
                    $com_arr = explode(":", $com);
                    $parent = strtolower(trim($com_arr[0]));

                    // check file duplicates
                    if ($parent != "" && isset($this->class_script[$parent]) &&
                        $this->class_script[$parent] != $file->getPathname()) {
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
                                $file->getPathname()
                            )
                        );
                    }

                    $this->class_script[$parent] = $file->getPathname();
                    $childs = explode(",", $com_arr[1]);
                    foreach ($childs as $child) {
                        $child = trim(strtolower($child));
                        if (!is_array($this->class_childs[$parent]) || !in_array($child,
                                $this->class_childs[$parent])) {
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
                    $this->class_script[$child] = $file->getPathname();

                    $parents = explode(",", $com_arr[1]);
                    foreach ($parents as $parent) {
                        $parent = trim(strtolower($parent));
                        if (!is_array($this->class_childs[$parent]) || !in_array($child,
                                $this->class_childs[$parent])) {
                            $this->class_childs[$parent][] = $child;
                        }
                    }
                }
            }

            if (preg_match("~^class\.(.*GUI)\.php$~i", $file->getFilename(), $res)) {
                $cl = strtolower($res[1]);
                $pos = strpos(strtolower($line), "class " . $cl);
                if (is_int($pos) && $this->class_script[$cl] == "") {
                    $this->class_script[$cl] = $file->getPathname();
                }
            }
        }
        fclose($handle);
    }

	/**
	* read structure into internal variables
	*
	* @access private
	*/
	function store($a_cdir = "./..")
	{
		$ilDB = $this->getDB();

		// delete all class to file assignments
		$ilDB->manipulate("DELETE FROM ctrl_classfile WHERE comp_prefix = ".
			$ilDB->quote($this->comp_prefix, "text"));
		if ($this->comp_prefix == "")
		{
			$ilDB->manipulate($q = "DELETE FROM ctrl_classfile WHERE ".
				$ilDB->equals("comp_prefix", "", "text", true));
		}

		// delete all call entries
		$ilDB->manipulate("DELETE FROM ctrl_calls WHERE comp_prefix = ".
			$ilDB->quote($this->comp_prefix, "text"));
		if ($this->comp_prefix == "")
		{
			$ilDB->manipulate("DELETE FROM ctrl_calls WHERE ".
				$ilDB->equals("comp_prefix", "", "text", true));
		}

		foreach($this->class_script as $class => $script)
		{
			$file = substr($script, strlen($this->start_dir));
			
			// store class to file assignment
			$ilDB->manipulate(sprintf("INSERT INTO ctrl_classfile (class, filename, comp_prefix, plugin_path) ".
				" VALUES (%s,%s,%s,%s)",
				$ilDB->quote($class, "text"),
				$ilDB->quote($file, "text"),
				$ilDB->quote($this->comp_prefix, "text"),
				$ilDB->quote($this->plugin_path, "text")
				));
		}
//$this->class_childs[$parent][] = $child;
		foreach($this->class_childs as $parent => $v)
		{
			if (is_array($this->class_childs[$parent]))
			{
				foreach($this->class_childs[$parent] as $child)
				{
					if(strlen(trim($child)) and strlen(trim($parent)))
					{
						// store call entry
						$ilDB->manipulate(sprintf("INSERT INTO ctrl_calls (parent, child, comp_prefix) ".
							"VALUES (%s,%s,%s)",
							$ilDB->quote($parent, "text"),
							$ilDB->quote($child, "text"),
							$ilDB->quote($this->comp_prefix, "text")));
					}
				}
			}
		}

	}

	/**
	* Determine class file IDS
	*/
	function determineClassFileIds()
	{
		$ilDB = $this->getDB();

		$ilDB->manipulate("UPDATE ctrl_classfile SET ".
			" cid = ".$ilDB->quote("", "text")
			);
		$set = $ilDB->query("SELECT * FROM ctrl_classfile ");
		$cnt = 1;
		while ($rec  = $ilDB->fetchAssoc($set))
		{
			$cid = base_convert((string) $cnt, 10, 36);
			$ilDB->manipulate("UPDATE ctrl_classfile SET ".
				" cid = ".$ilDB->quote($cid, "text").
				" WHERE class = ".$ilDB->quote($rec["class"], "text")
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

	protected function getDB(): \ilDBInterface
	{
		if(! is_null($this->db)) {
			return $this->db;
		}
		//return ilDB in any case - backward compat.
		global $ilDB;
		return $ilDB;
	}
}
