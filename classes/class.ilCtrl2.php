<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./classes/class.ilCtrl.php");

/**
* This class provides processing control methods.
* A global instance is available via variable $ilCtrl
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id: class.ilCtrl.php 20093 2009-05-29 07:17:46Z akill $
*/
class ilCtrl2 extends ilCtrl
{
	/**
	* Get last cid of node id
	*/
	function getCurrentCidOfNode($a_node)
	{
		$n_arr = explode(":", $a_node);
		return $n_arr[count($n_arr) - 1];
	}

	/**
	* Remove last cid of node
	*/
	function removeLastCid($a_node)
	{
		$lpos = strrpos($a_node, ":");
		return substr($a_node, 0, $lpos);
	}

	/**
	* Get last but one cid of node id
	*/
	function getParentCidOfNode($a_node)
	{
		$n_arr = explode(":", $a_node);
		return $n_arr[count($n_arr) - 2];
	}
	
	function getNodeIdForTargetClass($a_par_node, $a_class)
	{
		$class = strtolower($a_class);
		$this->readClassInfo($class);
		
		if ($a_par_node === 0 || $a_par_node == "")
		{
			return $this->getCidForClass($class);
		}
		
//return parent::getNodeIdForTargetClass($a_par_node, $a_class);
		$this->readNodeInfo($a_par_node);
		
		$node_cid = $this->getCurrentCidOfNode($a_par_node);

		// target class is class of current node id
		if ($class == $this->getClassForCid($node_cid))
		{
			return $a_par_node;
		}

		// target class is child of current node id
		if (is_array($this->calls[$this->getClassForCid($node_cid)]) &&
			in_array($a_class, $this->calls[$this->getClassForCid($node_cid)]))
		{
			return $a_par_node.":".$this->getCidForClass($class);
		}

		// target class is sibling
		$par_cid = $this->getParentCidOfNode($a_par_node);
		if ($par_cid != "")
		{
			if (is_array($this->calls[$this->getClassForCid($par_cid)]) &&
				in_array($a_class, $this->calls[$this->getClassForCid($par_cid)]))
			{
				return $this->removeLastCid($a_par_node).":".$this->getCidForClass($class);;
			}
		}

		// target class is parent
		$temp_node = $this->removeLastCid($a_par_node);
		while($temp_node != "")
		{
			$temp_cid = $this->getCurrentCidOfNode($temp_node);
			if ($this->getClassForCid($temp_cid) == $a_class)
			{
				return $temp_node;
			}
			$temp_node = $this->removeLastCid($temp_node);
		}
		
		// Please do NOT change these lines.
		// Developers must be aware, if they use classes unknown to the controller
		// otherwise certain problem will be extremely hard to track down...
		echo "ERROR: Can't find target class $a_class for node $a_par_node ".
			"(".$this->cid_class[$this->getParentCidOfNode($a_par_node)].").<br>";
		error_log( "ERROR: Can't find target class $a_class for node $a_par_node ".
			"(".$this->cid_class[$this->getParentCidOfNode($a_par_node)].")");
			
		if (DEVMODE == 1)
		{
			try
			{
				throw new Exception("");
			}
			catch(Exception $e)
			{
				echo "<pre>".$e->getTraceAsString()."</pre>";
			}
		}

		exit;
	}
	
	function getCallStructure($a_class)
	{
		$this->readClassInfo($a_class);
	}
	
	/**
	 * Read info of class
	 *  
	 * @param	object	$a_class	class name
	 */
	function readClassInfo($a_class)
	{
		global $ilDB;
		
		$a_class = strtolower($a_class);
		if ($this->info_read_class[$a_class])
		{
			return;
		}
		$set = $ilDB->query("SELECT * FROM ctrl_classfile ".
			" WHERE class = ".$ilDB->quote($a_class, "text")
			);
		if ($rec  = $ilDB->fetchAssoc($set))
		{
			$this->cid_class[$rec["cid"]] = $a_class;
			$this->class_cid[$a_class] = $rec["cid"];
		}
		
		$set = $ilDB->query("SELECT * FROM ctrl_calls ".
			" WHERE parent = ".$ilDB->quote($a_class, "text")
			);
		while ($rec  = $ilDB->fetchAssoc($set))
		{
			if (!is_array($this->calls[$a_class]) || !in_array($rec["child"], $this->calls[$a_class]))
			{
				if ($rec["child"] != "")
				{
					$this->calls[$a_class][] = $rec["child"];
				}
			}
		}
		
		$this->info_read_class[$a_class] = true;
		$this->info_read_cid[$this->class_cid[$a_class]] = true;
	}
	
	/**
	 * Read info of node
	 *  
	 * @param	object	$a_class	class name
	 */
	function readNodeInfo($a_node)
	{
		$n_arr = explode(":", $a_node);
		foreach ($n_arr as $cid)
		{
			$this->readCidInfo($cid);
		}
	}
		
	/**
	 * Read information of class per cid
	 * @return 
	 * @param object $a_cid		cid
	 */
	function readCidInfo($a_cid)
	{
		global $ilDB;
		
		if ($this->info_read_cid[$a_cid])
		{
			return;
		}
		$set = $ilDB->query("SELECT * FROM ctrl_classfile ".
			" WHERE cid = ".$ilDB->quote($a_cid, "text")
			);
		if ($rec  = $ilDB->fetchAssoc($set))
		{
			$this->cid_class[$a_cid] = $rec["class"];
			$this->class_cid[$rec["class"]] = $a_cid;
		
			$set = $ilDB->query("SELECT * FROM ctrl_calls ".
				" WHERE parent = ".$ilDB->quote($rec["class"], "text")
				);
			while ($rec2  = $ilDB->fetchAssoc($set))
			{
				if (!is_array($this->calls[$rec["class"]]) || !in_array($rec2["child"], $this->calls[$rec["class"]]))
				{
					if ($rec2["child"] != "")
					{
						$this->calls[$rec["class"]][] = $rec2["child"];
					}
				}
			}
			$this->info_read_class[$rec["class"]] = true;
		}
		
		$this->info_read_cid[$a_cid] = true;
	}

	/**
	* Get Cid for Class
	*/
	function getCidForClass($a_class)
	{
		if ($this->class_cid[$a_class] == "")
		{
			$this->readClassInfo($a_class);
		}
		if ($this->class_cid[$a_class] == "")
		{
			if (DEVMODE == 1)
			{
				$add = "<br><br>Please make sure your GUI class name ends with 'GUI' and that the filename is 'class.[YourClassName].php'. In exceptional cases you
					may solve the issue by putting an empty * @ilCtrl_Calls [YourClassName]: into your class header.".
					" In both cases you need to reload the control structure in the setup.";
			}
			die("Cannot find cid for class ".$a_class.".".$add);
		}
		return $this->class_cid[$a_class];
	}

	/**
	* Get class for cid
	*/
	function getClassForCid($a_cid)
	{
		if ($this->cid_class[$a_cid] == "")
		{
			$this->readCidInfo($a_cid);
		}
		if ($this->cid_class[$a_cid] == "")
		{
			die("Cannot find class for cid ".$a_cid.".");
		}
		return $this->cid_class[$a_cid];
	}

	/**
	 * Get path from node a to node b
	 * @return 
	 * @param object $a_source_node
	 * @param object $a_target_node
	 */	
	function getPathNew($a_source_node, $a_target_node)
	{
//if ($this->getCmdClass() == "ilmailfoldergui") echo "-".$a_source_node."-".$a_target_node."-";
		if ($a_source_node == 1)
		{
			$a_source_node = "";
		}
		if (substr($a_target_node, 0, strlen($a_source_node)) != $a_source_node)
		{
			echo "ERROR: Path not found. Source:".$a_source_node.
				", Target:".$a_target_node;
			exit;
		}
		
		$temp_node = $a_source_node;
		
		$path = array();
		if ($a_source_node != "")
		{
			$path = array($a_source_node);
		}
		
		$diffstart = ($a_source_node == "")
			? 0
			: strlen($a_source_node) + 1;
		$diff = substr($a_target_node, $diffstart);
//echo ":$diff:";
		$diff_arr = explode(":", $diff);
		foreach($diff_arr as $cid)
		{
			if ($temp_node != "")
			{
				$temp_node.= ":";
			}
			$temp_node.= $cid;
			$path[] = $temp_node;
		}
//if ($this->getCmdClass() == "ilmailfoldergui") var_dump($path);
		return $path;
	}

	/**
	* get current return class
	*/
	function searchReturnClass($a_class)
	{
		$a_class = strtolower($a_class);

		$node = $this->getNodeIdForTargetClass($this->current_node, $a_class);
		$n_arr = explode(":", $node);
		for($i = count($n_arr)-2; $i>=0; $i--)
		{
			if ($this->return[$this->getClassForCid($n_arr[$i])] != "")
			{
				return $this->getClassForCid($n_arr[$i]);
			}
		}

		return false;
	}
	
	
	/////////////////
	/////////////////
	
	/**
	* forward flow of control to next gui class
	* this invokes the executeCommand() method of the
	* gui object that is passed via reference
	*
	* @param	object		$a_gui_object		gui object that should receive
	*											the flow of control
	* @access	public
	*
	* @return	mixed		return data of invoked executeCommand() method
	*/
	function &forwardCommand(&$a_gui_object)
	{
		$class = strtolower(get_class($a_gui_object));

		$nr = $this->getNodeIdForTargetClass($this->current_node, $class);
		if ($nr != "")
		{
			$current_node = $this->current_node;
			
			$this->current_node = $nr;

			if (DEVMODE == "1")
			{
				$this->call_hist[] = array("class" => get_class($a_gui_object),
					"mode" => "execComm", "cmd" => $this->getCmd());
			}
			
			$html = $a_gui_object->executeCommand();
			
			// reset current node
			$this->current_node = $current_node;
			
			return $html;

		}
		echo "ERROR: Can't forward to class $class."; exit;
//echo "end forward<br>";
	}
	
	/**
	* Gets an HTML output from another GUI class and
	* returns the flow of control to the calling class.
	*
	* @param	object		$a_gui_object		gui object that returns the HTML block
	* @access	public
	*
	* @return	string		HTML
	*/
	function &getHTML(&$a_gui_object)
	{
		$class = strtolower(get_class($a_gui_object));

		$nr = $this->getNodeIdForTargetClass($this->current_node, $class);
		if ($nr != "")
		{
			$current_node = $this->current_node;
			
			// set current node to new gui class
			$this->current_node = $nr;
			
			if (DEVMODE == "1")
			{
				$this->call_hist[] = array("class" => get_class($a_gui_object),
					"mode" => "getHtml", "cmd" => $this->getCmd());
			}
			
			// get block
			$html = $a_gui_object->getHTML();
			
			// reset current node
			$this->current_node = $current_node;
			
			// return block
			return $html;
		}
		echo "ERROR: Can't getHTML from class $class."; exit;
	}

	/**
	* Get next class in the control path from the current class
	* to the target command class. This is the class that should
	* be instantiated and be invoked via $ilCtrl->forwardCommand($class)
	* next.
	*
	* @return	string		class name of next class
	*/
	function getNextClass()
	{
//echo "getNextClass:";
		$cmdNode = $this->getCmdNode();
		if ($cmdNode == "")
		{
			return false;
		}
		else
		{
			if ($this->current_node == $cmdNode)
			{
//echo "1:".$this->call_node[$cmdNode]["class"]."<br>";
				//return $this->call_node[$cmdNode]["class"];
				return "";
			}
			else
			{
				$path = $this->getPathNew($this->current_node, $cmdNode);

//echo "2:".$this->call_node[$path[1]]["class"]."<br>";
				$this->readCidInfo($this->getCurrentCidOfNode($path[1]));
//echo ":".$this->cid_class[$this->getCurrentCidOfNode($path[1])].":".$this->getCurrentCidOfNode($path[1]).":";
				return $this->cid_class[$this->getCurrentCidOfNode($path[1])];
			}
		}
	}

	/**
	*
	*/
	function getParameterArrayByClass($a_class, $a_cmd = "")
	{
		if ($a_class == "")
		{
			return array();
		}

		if (!is_array($a_class))
		{
			$a_class = array($a_class);
		}

		$nr = $this->current_node;
		foreach ($a_class as $class)
		{
			$class = strtolower($class);
			$nr = $this->getNodeIdForTargetClass($nr, $class);
			$target_class = $class;
		}

		$path = $this->getPathNew(1, $nr);
		$params = array();

		// append parameters of parent classes
		foreach($path as $node_id)
		{
			$class = ($node_id == "")
				? strtolower($_GET["baseClass"])
				: $this->getClassForCid($this->getCurrentCidOfNode($node_id));
			if (is_array($this->save_parameter[$class]))
			{
				foreach($this->save_parameter[$class] as $par)
				{
					$params[$par] = $_GET[$par];
				}
			}

			if (is_array($this->parameter[$class]))
			{
				foreach($this->parameter[$class] as $par => $value)
				{
					$params[$par] = $value;
				}
			}
		}

		if ($a_cmd != "")
		{
			$params["cmd"] = $a_cmd;
		}

		$params["cmdClass"] = $target_class;
		$params["cmdNode"] = $nr;
		$params["baseClass"] = $_GET["baseClass"];

		return $params;
	}

}
?>
