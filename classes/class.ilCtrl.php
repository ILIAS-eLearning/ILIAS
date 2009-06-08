<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* This class provides processing control methods.
* A global instance is available via variable $ilCtrl
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*/
class ilCtrl
{
	const IL_RTOKEN_NAME = 'rtoken';
	
	var $target_script;
	var $forward;			// forward array
	var $parent;			// parent array (reverse forward)
	var $save_parameter;	// save parameter array
	var $return;			// return commmands
	var $call_hist = array();	// calling history
	var $debug = array();

	/**
	* control class constructor
	*/
	function ilCtrl()
	{
		global $ilBench;

		$this->bench =& $ilBench;
		
		// initialisation
		$this->init();
		
		// this information should go to xml files one day
		$this->stored_trees = array
			("ilrepositorygui", "ilpersonaldesktopgui",
			"illmpresentationgui", "illmeditorgui",
			"iladministrationgui");
	}
	
	function debug($str)
	{
//echo "<br>".$str;
		$this->debug[] = $str;
	}
	
	function getDebug()
	{
		return $this->debug;
	}
	
	/**
	* initialisation
	*/
	function init()
	{
		$this->transit = array();
		$this->forward = array();			// forward array
		$this->forwards = array();			// forward array
		$this->parent = array();			// parent array (reverse forward)
		$this->save_parameter = array();	// save parameter array
		$this->parameter = array();			// save parameter array
		$this->return = "";					// return commmands
		$this->location = array();
		$this->tab = array();
		$this->current_node = 0;
		$this->module_dir = "";
		$this->service_dir = "";
		$this->call_node = array();
		$this->root_class = "";
	}

	/**
	* Calls base class of current request. The base class is
	* passed via $_GET["baseClass"] and is the first class in
	* the call sequence of the request. Do not call this method
	* within other scripts than ilias.php.
	*
	* @access	public
	*
	*/
	function callBaseClass()
	{
		global $ilDB;
		
		$baseClass = strtolower($_GET["baseClass"]);
		
		// get class information
		$mc_set = $ilDB->query("SELECT * FROM module_class WHERE LOWER(class) = ".
			$ilDB->quote($baseClass, "text"));
		$mc_rec = $ilDB->fetchAssoc($mc_set);
		$module = $mc_rec["module"];
		$class = $mc_rec["class"];
		$class_dir = $mc_rec["dir"];
		
		if ($module != "")
		{
			$m_set = $ilDB->query("SELECT * FROM il_component WHERE name = ".
				$ilDB->quote($module, "text"));
			$m_rec = $ilDB->fetchAssoc($m_set);
			$this->module_dir = $m_rec["type"]."/".$m_rec["name"];
			include_once $this->module_dir."/".$class_dir."/class.".$class.".php";
		}
		else		// check whether class belongs to a service
		{
			$mc_set = $ilDB->query("SELECT * FROM service_class WHERE LOWER(class) = ".
				$ilDB->quote($baseClass, "text"));
			$mc_rec = $ilDB->fetchAssoc($mc_set);

			$service = $mc_rec["service"];
			$class = $mc_rec["class"];
			$class_dir = $mc_rec["dir"];
			
			if ($service == "")
			{
				echo "Could not find entry in modules.xml or services.xml for".
					$baseClass;
				exit;
			}

			// get service information
			$m_set = $ilDB->query("SELECT * FROM il_component WHERE name = ".
				$ilDB->quote($service, "text"));
			$m_rec = $ilDB->fetchAssoc($m_set);
			$this->service_dir = $m_rec["type"]."/".$m_rec["name"];
			
			include_once $this->service_dir."/".$class_dir."/class.".$class.".php";;
		}
		
		// forward processing to base class
		$this->getCallStructure(strtolower($baseClass));
		$base_class_gui =& new $class();
		$this->forwardCommand($base_class_gui);
	}

	/**
	* get directory of current module
	*/
	function getModuleDir()
	{
		return $this->module_dir;
	}
	
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
		if ($nr > 0)
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
		if ($nr > 0)
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
	* Set context of current user interface.
	*/
	function setContext($a_obj_id, $a_obj_type, $a_sub_obj_id = 0, $a_sub_obj_type = "")
	{
		$this->context_obj_id = $a_obj_id;
		$this->context_obj_type = $a_obj_type;
		$this->context_sub_obj_id = $a_sub_obj_id;
		$this->context_sub_obj_type = $a_sub_obj_type;
	}

	/**
	* Get ContextObjId.
	*
	* @return	int	
	*/
	public function getContextObjId()
	{
		return $this->context_obj_id;
	}

	/**
	* Get ContextObjType.
	*
	* @return	int	
	*/
	public function getContextObjType()
	{
		return $this->context_obj_type;
	}

	/**
	* Get ContextSubObjId.
	*
	* @return	int	
	*/
	public function getContextSubObjId()
	{
		return $this->context_sub_obj_id;
	}

	/**
	* Get ContextSubObjType.
	*
	* @return	int	
	*/
	public function getContextSubObjType()
	{
		return $this->context_sub_obj_type;
	}

	/**
	* Searchs a node for a given class ($a_class) "near" the another
	* node ($a_par_node).
	*
	* It first looks if the given class is a child class of the current node.
	* If such a child node has been found, its id is returned.
	*
	* If not, this method determines wether the given class is a sibling
	* of the current node within the call structure. If this is the case
	* then the corresponding id is returned.
	*
	* At last the methode searchs for the given class along the path from
	* the current node to the root class of the call structure.
	*
	* @param	$a_par_node		id of starting node for the search
	* @param	$a_class		class that should be searched
	*
	* @access	private
	*
	* @return	int				id of target node that has been found
	*/
	function getNodeIdForTargetClass($a_par_node, $a_class)
	{
		$class = strtolower($a_class);

		// target class is class of current node id
		if ($class == $this->call_node[$a_par_node]["class"])
		{
			return $a_par_node;
		}

		// target class is child of current node id
		foreach($this->call_node as $nr => $node)
		{
			if (($node["parent"] == $a_par_node) &&
				($node["class"] == $class))
			{
				return $nr;
			}
		}

		// target class is sibling
		$par = $this->call_node[$a_par_node]["parent"];
		if ($par != 0)
		{
			foreach($this->call_node as $nr => $node)
			{
				if (($node["parent"] == $par) &&
					($node["class"] == $class))
				{
					return $nr;
				}
			}
		}

		// target class is parent
		while($par != 0)
		{
			if ($this->call_node[$par]["class"] == $class)
			{
				return $par;
			}
			$par = $this->call_node[$par]["parent"];
		}

		// Please do NOT change these lines.
		// Developers must be aware, if they use classes unknown to the controller
		// otherwise certain problem will be extremely hard to track down...
		echo "ERROR: Can't find target class $a_class for node $a_par_node ".
			"(".$this->call_node[$a_par_node]["class"].").<br>";
		error_log( "ERROR: Can't find target class $a_class for node $a_par_node ".
			"(".$this->call_node[$a_par_node]["class"].")");
			
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

	/**
	* get command target node
	*
	* @return	int		id of current command target node
	*/
	function getCmdNode()
	{
		return $_GET["cmdNode"];
	}

	/**
	* add a location to the locator array
	*
	* @param	string		$a_title	link text
	* @param	string		$a_link		link
	* @param	string		$a_target	target frame
	*/
	function addLocation($a_title, $a_link, $a_target = "", $a_ref_id = 0)
	{
		$this->location[] = array("title" => $a_title,
			"link" => $a_link, "target" => $a_target, "ref_id" => $a_ref_id);
	}

	/**
	* get locations array
	*
	* @return	array	array of locations (array("title", "link", "target"))
	*/
	function getLocations()
	{
		return $this->location;
	}

	/**
	* add a tab to tabs array
	*
	* @param	string		$a_lang_var		language variable
	* @param	string		$a_link			link
	* @param	string		$a_cmd			command (must be same as in link)
	* @param	string		$a_class		command class (must be same as in link)
	*/
	function addTab($a_lang_var, $a_link, $a_cmd, $a_class)
	{
		$a_class = strtolower($a_class);

		$this->tab[] = array("lang_var" => $a_lang_var,
			"link" => $a_link, "cmd" => $a_cmd, "class" => $a_class);
	}

	/**
	* get tabs array
	*
	* @return	array		array of tab entries (array("lang_var", "link", "cmd", "class))
	*/
	function getTabs()
	{
		return $this->tab;
	}

	/**
	* Get controller call history
	*/
	function getCallHistory()
	{
		return $this->call_hist;
	}
	
	/**
	* Get call structure of class context. This method must be called
	* for the top level gui class in the leading php script. It must be
	* called before the the current command is forwarded to the top level
	* gui class. Example:
	*
	*	include_once "classes/class.ilRepositoryGUI.php";
	*	$ilCtrl->setTargetScript("repository.php");
	*	$ilCtrl->getCallStructure("ilrepositorygui");
	*	$repository_gui =& new ilRepositoryGUI();
	*	$ilCtrl->forwardCommand($repository_gui);
	*
	* @param	string		$a_class	gui class name
	* @param	int			$a_nr		internal counter (don't pass a value here)
	* @param	int			$a_parent	internal counter (don't pass a value here)
	*
	* @access	public
	*/
	function getCallStructure($a_class, $a_nr = 0, $a_parent = 0)
	{
		global $ilDB, $ilLog, $ilUser;
		
		$a_class = strtolower($a_class);
		
		if (in_array($a_class, $this->stored_trees))
		{
			
			$set = $ilDB->query("SELECT * FROM ctrl_structure WHERE root_class = ".
				$ilDB->quote($a_class, "text"));
			$rec = $ilDB->fetchAssoc($set);
			$this->call_node = unserialize($rec["call_node"]);
			$this->forward = unserialize($rec["forward"]);
			$this->parent = unserialize($rec["parent"]);
			$this->root_class = $a_class;
		}
		else
		{
			$this->readCallStructure($a_class, $a_nr, $a_parent);
		}
//var_dump($this->call_node);
//var_dump($this->forward);
//var_dump($this->parent);
//var_dump($this->root_class);
		// check whether command node and command class fit together
		if ($_GET["cmdNode"] > 0)
		{
			if (strtolower($this->call_node[$_GET["cmdNode"]]["class"]) !=
				strtolower($_GET["cmdClass"]))
			{
				if (DEVMODE)
				{
					die ("Internal Error: ilCtrl Node Error. cmdClass: '".$_GET["cmdClass"]
						."', cmdNode: '".$_GET["cmdNode"]."' . Internally cmdNode is assigned to ".
						"class '".$this->call_node[$_GET["cmdNode"]]["class"]."'.");
				}
				else
				{
					if (is_object($ilLog))
					{
						if (is_object($ilUser))
						{
							$user_str = "User: ".$ilUser->getLogin()." (".$ilUser->getId()."), ";
						}
						$ilLog->write("Invalid Request (class ilCtrl). Possible attack or Control Structure broken (see Setup). ".
							$user_str."IP: ".$_SERVER["REMOTE_ADDR"].", URI: ".$_SERVER["REQUEST_URI"]);
					}
					ilUtil::sendFailure("Sorry, but the request includes invalid parameters." ,true);
					ilUtil::redirect("repository.php?cmd=frameset");
				}
			}
		}
	}

	/**
	* stores often used common call structures (called
	* from db_update script!!!)
	*/
	function storeCommonStructures()
	{
		global $ilDB;
		
		$ilDB->manipulate("DELETE FROM ctrl_structure");
		
		foreach ($this->stored_trees as $root_gui_class)
		{
			$this->call_node = array();
			$this->forward = array();
			$this->parent = array();
			$this->readCallStructure($root_gui_class);
/*			$ilDB->manipulate(sprintf("INSERT INTO ctrl_structure ".
				"(root_class, call_node, forward, parent) VALUES (%s,%s,%s,%s)",
				$ilDB->quote($root_gui_class, "text"),
				$ilDB->quote(serialize($this->call_node), "clob"),
				$ilDB->quote(serialize($this->forward), "clob"),
				$ilDB->quote(serialize($this->parent), "clob")));*/
			$ilDB->insert("ctrl_structure", array(
				"root_class" => array("text", $root_gui_class),
				"call_node" => array("text", serialize($this->call_node)),
				"forward" => array("text", serialize($this->forward)),
				"parent" => array("clob", serialize($this->parent))));
		}
	}
	
	/**
	* reads call structure from db
	*/
	function readCallStructure($a_class, $a_nr = 0, $a_parent = 0)
	{
		global $ilDB;

		$a_class = strtolower($a_class);

		$a_nr++;
		
		// determine call node structure
		$this->call_node[$a_nr] = array("class" => $a_class, "parent" => $a_parent);
		
//echo "<br>nr:$a_nr:class:$a_class:parent:$a_parent:";
		$call_set = $ilDB->query("SELECT * FROM ctrl_calls WHERE parent = ".
			$ilDB->quote(strtolower($a_class), "text").
			" ORDER BY child", array("text"));
		$a_parent = $a_nr;
		while ($call_rec = $ilDB->fetchAssoc($call_set))
		{
			$a_nr = $this->readCallStructure($call_rec["child"], $a_nr, $a_parent);
			$forw[] = $call_rec["child"];
		}
		
		// determin forward and parent array
		$this->forwards($a_class, $forw);
//echo "<br>forwards:".$a_class."<br>"; var_dump($forw);

		// determine root class
		$this->root_class = $a_class;
		return $a_nr;
	}


	/**
	* Stores which classes forwards commands to which other classes.
	*
	* @param	string	$a_from_class	source class name
	* @param	string	$a_to_class		target class name
	*
	* @access	private
	*/
	function forwards($a_from_class, $a_to_class)
	{
		$a_from_class = strtolower($a_from_class);

		if (is_array($a_to_class))
		{
			foreach($a_to_class as $to_class)
			{
				if ($a_from_class != "" && $to_class != "")
				{
					if (!is_array($this->forward[$a_from_class]) || !in_array(strtolower($to_class), $this->forward[$a_from_class]))
					{
						$this->forward[$a_from_class][] = strtolower($to_class);
					}
					if (!is_array($this->parent[strtolower($to_class)]) || !in_array($a_from_class, $this->parent[strtolower($to_class)]))
					{
						$this->parent[strtolower($to_class)][] = $a_from_class;
					}
				}
			}
		}
		else
		{
			$to_class = $a_to_class;
			if ($a_from_class != "" && $to_class != "")
			{
				if (!is_array($this->forward[$a_from_class]) || !in_array(strtolower($to_class), $this->forward[$a_from_class]))
				{
					$this->forward[$a_from_class][] = strtolower($to_class);
				}
				if (!is_array($this->parent[strtolower($to_class)]) || !in_array($a_from_class, $this->parent[strtolower($to_class)]))
				{
					$this->parent[strtolower($to_class)][] = $a_from_class;
				}
			}
		}
	}


	/**
	* Set parameters that should be passed in every form and link of a
	* gui class. All links that relate to the specified gui object class and
	* are build e.g. by using getLinkTarger() or getFormAction() will include
	* this parameter. This is the mechanism to add url parameters to the standard
	* url (which is set by the setTargetScript() method) target everytime.
	*
	* A typical example is the "ref_id" that should be included in almost every
	* link or form action url. So the constructor of ilRepositoryGUI includes
	* the command:
	*
	*	$this->ctrl->saveParameter($this, array("ref_id"));
	*
	* @param	object	$a_obj			gui object that will process the parameter
	* @param	mixed	$a_parameter	parameter name (string) or array of parameter
	*									names
	*
	* @access	public
	*/
	function saveParameter(&$a_obj, $a_parameter)
	{
		$this->saveParameterByClass(get_class($a_obj), $a_parameter);
	}
	
	function saveParameterByClass($a_class, $a_parameter)
	{
		if (is_array($a_parameter))
		{
			foreach($a_parameter as $parameter)
			{
				$this->save_parameter[strtolower($a_class)][] = $parameter;
			}
		}
		else
		{
			$this->save_parameter[strtolower($a_class)][] = $a_parameter;
		}
	}


	/**
	* Set parameters that should be passed a form and link of a
	* gui class. All links that relate to the specified gui object class and
	* are build e.g. by using getLinkTarger() or getFormAction() will include
	* this parameter. This is the mechanism to add url parameters to the standard
	* url (which is set by the setTargetScript() method) target. The difference
	* to the saveParameter() method is, that setParameter() does not simply
	* forward the url parameter of the last request. You can set a spefific value.
	*
	* If this parameter is also a "saved parameter" (set by saveParameter() method)
	* the saved value will be overwritten.
	*
	* The method is usually used in conjunction with a getFormAction() or getLinkTarget()
	* call. E.g.:
	*
	*		$this->ctrl->setParameter($this, "obj_id", $data_row["obj_id"]);
	*		$obj_link = $this->ctrl->getLinkTarget($this, "view");
	*
	* @param	object		$a_obj			gui object
	* @param	string		$a_parameter	parameter name
	* @param	string		$a_parameter	parameter value
	*
	* @access	public
	*/
	function setParameter(&$a_obj, $a_parameter, $a_value)
	{
		$this->parameter[strtolower(get_class($a_obj))][$a_parameter] = $a_value;
	}


	/**
	* Same as setParameterByClass, except that a class name is passed.
	*
	* @param	string		$a_class		gui class name
	* @param	string		$a_parameter	parameter name
	* @param	string		$a_parameter	parameter value
	*
	* @access	public
	*/
	function setParameterByClass($a_class, $a_parameter, $a_value)
	{
		$this->parameter[strtolower($a_class)][$a_parameter] = $a_value;
	}
	
	
	/**
	* Clears all parameters that have been set via setParameter for
	* a GUI class.
	*
	* @param	object		$a_obj			gui object
	*/
	function clearParameters(&$a_obj)
	{
		$this->clearParametersByClass(strtolower(get_class($a_obj)));
	}

	/**
	* Clears all parameters that have been set via setParameter for
	* a GUI class.
	*
	* @param	string		$a_class		gui class name
	*/
	function clearParametersByClass($a_class)
	{
		$this->parameter[strtolower($a_class)] = array();
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
				return $this->call_node[$path[1]]["class"];
			}
		}
	}

	/**
	* Get class path that can be used in include statements
	* for a given class name.
	*
	* @param	string		$a_class_name		class name
	*/
	function lookupClassPath($a_class_name)
	{
		global $ilDB;
		$a_class_name = strtolower($a_class_name);

		$class_set = $ilDB->query("SELECT * FROM ctrl_classfile WHERE class = ".
			$ilDB->quote($a_class_name, "text"));
		$class_rec = $ilDB->fetchAssoc($class_set);

		if ($class_rec["plugin_path"] != "")
		{
			return $class_rec["plugin_path"]."/".$class_rec["filename"];
		}
		else
		{
			return $class_rec["filename"];
		}
	}

	/**
	* this method assumes that the class path has the format "dir/class.<class_name>.php"
	*
	* @param	string		$a_class_path		class path
	* @access	public
	*
	* @return	string		class name
	*/
	function getClassForClasspath($a_class_path)
	{
		$path = pathinfo($a_class_path);
		$file = $path["basename"];
		$class = substr($file, 6, strlen($file) - 10);

		return $class;
	}

	/**
	* get path in call structure
	*
	* @param	string		$a_source_node		source node id
	* @param	string		$a_source_node		target node id
	*
	* @access	private
	*/
	function getPathNew($a_source_node, $a_target_node)
	{
//echo "-".$a_source_node."-";
		$path_rev = array();
		$c_target = $a_target_node;
		while ($a_source_node != $c_target)
		{
			$path_rev[] = $c_target;
			$c_target = $this->call_node[$c_target]["parent"];
			if(!($c_target > 0))
			{
				echo "ERROR: Path not found. Source:".$a_source_node.
					" (".$this->call_node[$a_source_node]["class"].")".
					", Target:".$a_target_node.
					" (".$this->call_node[$a_target_node]["class"].")";
				exit;
			}
		}
		if ($a_source_node == $c_target)
		{
			$path_rev[] = $c_target;
		}
		$path = array();
		for ($i=0; $i<count($path_rev); $i++)
		{
			$path[] = $path_rev[count($path_rev) - ($i + 1)];
		}

		foreach($path as $node)
		{
//echo "<br>-->".$node.":".$this->call_node[$node]["class"];
		}
//echo "<br>-".$a_source_node."-".$a_target_node."-";
//var_dump($path);
		return $path;
	}


	/**
	* set target script name
	*
	* @param	string		$a_target_script		target script name
	*/
	function setTargetScript($a_target_script)
	{
		$this->target_script = $a_target_script;
	}


	/**
	* get target script name
	*
	* @return	string		target script name
	*/
	function getTargetScript()
	{
		return $this->target_script;
	}


	/**
	* initialises new base class
	*
	* Note: this resets the whole current ilCtrl context completely.
	* You can call setTargetScript() and callBaseClass() after that.
	*/
	function initBaseClass($a_base_class)
	{
		$_GET["baseClass"] = $a_base_class;
		$_GET["cmd"] = "";
		$_GET["cmdClass"] = "";
		$_GET["cmdNode"] = "";
		$this->init();
	}
	
	/**
	* determines current get/post command
	*
	* @param	string		default command
	* @param	array		safe commands: for these commands no token
	*						is checked for post requests
	*/
	function getCmd($a_default_cmd = "", $a_safe_commands = "")
	{
		$cmd = $_GET["cmd"];
		if($cmd == "post")
		{
			if (is_array($_POST["cmd"]))
			{
				reset($_POST["cmd"]);
			}
			$cmd = @key($_POST["cmd"]);

			// verify command
			if ($this->verified_cmd != "")
			{
				return $this->verified_cmd;
			}
			else
			{
				if (!$this->verifyToken() &&
					(!is_array($a_safe_commands) || !in_array($cmd, $a_safe_commands)))
				{
					return $a_default_cmd;
				}
			}
			
			$this->verified_cmd = $cmd;
			
			if($cmd == "" && isset($_POST["select_cmd"]))		// selected command in multi-list (table2)
			{
				$cmd = $_POST["selected_cmd"];
				$this->verified_cmd = $cmd;
			}
			if($cmd == "")
			{
				$cmd = $_GET["fallbackCmd"];
				$this->verified_cmd = $cmd;
			}
		}
		if($cmd == "")
		{
			$cmd = $a_default_cmd;
		}
		return $cmd;
	}

	/**
	* set the current command
	*
	* IMPORTANT NOTE:
	*
	* please use this function only in exceptional cases
	* it is not intended for setting commands in forms or links!
	* use the corresponding parameters of getFormAction() and
	* getLinkTarget() instead.
	*/
	function setCmd($a_cmd)
	{
		$_GET["cmd"] = $a_cmd;
	}

	/**
	* set the current command class
	*
	* IMPORTANT NOTE:
	*
	* please use this function only in exceptional cases
	* it is not intended for setting the command class in forms or links!
	* use the corresponding parameters of getFormAction() and
	* getLinkTarget() instead.
	*/
	function setCmdClass($a_cmd_class)
	{
		$a_cmd_class = strtolower($a_cmd_class);
		$nr = $this->getNodeIdForTargetClass($this->current_node, $a_cmd_class);
		$_GET["cmdClass"] = $a_cmd_class;
		$_GET["cmdNode"] = $nr;
	}

	/**
	* determines responsible class for current command
	*/
	function getCmdClass()
	{
		return strtolower($_GET["cmdClass"]);
	}

	/**
	* get form action url for gui class object
	*
	* @param	object		$a_gui_obj		gui object
	* @param	string		$a_fallback_cmd	fallback command
	* @param	string		$a_anchor		anchor
	* @param	bool		$a_asnych		async
	*/
	function getFormAction(&$a_gui_obj, $a_fallback_cmd = "", $a_anchor = "", $a_asynch = false)
	{
		$script =  $this->getFormActionByClass(strtolower(get_class($a_gui_obj)),
			$a_fallback_cmd, $a_anchor, $a_asynch);
		return $script;
	}

	/**
	* get form action url for gui class name
	*
	* @param	string		$a_class		gui class name
	*/
	function getFormActionByClass($a_class, $a_fallback_cmd = "", $a_anchor = "", $a_asynch = false)
	{
		$a_class = strtolower($a_class);
		
		$tok = $this->getRequestToken();
//echo "-$tok-";

		$script = $this->getLinkTargetByClass($a_class, "post", "", $a_asynch);
		if ($a_fallback_cmd != "")
		{
			$script = ilUtil::appendUrlParameterString($script, "fallbackCmd=".$a_fallback_cmd);
		}
		$script = ilUtil::appendUrlParameterString($script, self::IL_RTOKEN_NAME.'='.$this->getRequestToken());
		if ($a_anchor != "")
		{
			$script = $script."#".$a_anchor;
		}

		return $script;
	}
	
	/**
	 * append request token as url parameter
	 *
	 * @access public
	 * 
	 */
	public function appendRequestTokenParameterString($a_url)
	{
		return ilUtil::appendUrlParameterString($a_url, self::IL_RTOKEN_NAME.'='.$this->getRequestToken());
	}
	
	/**
	* Get request token.
	*/
	function getRequestToken()
	{
		global $ilDB, $ilUser;
		
		if ($this->rtoken != "")
		{
			return $this->rtoken;
		}
		else
		{
			if (is_object($ilDB) && is_object($ilUser) && $ilUser->getId() > 0 &&
				$ilUser->getId() != ANONYMOUS_USER_ID)
			{
				$res = $ilDB->query("SELECT token FROM il_request_token WHERE user_id = ".
					$ilDB->quote($ilUser->getId(), "integer").
					" AND session_id = ".$ilDB->quote(session_id(), "text"));
				$rec = $ilDB->fetchAssoc($res);
				
				if ($rec["token"] != "")
				{
					return $rec["token"];
				}
				
				$this->rtoken = md5(uniqid(rand(), true));
				
				// IMPORTANT: Please do NOT try to move this implementation to a
				// session basis. This will fail due to framesets that are used
				// occasionally in ILIAS, e.g. in the chat, where multiple
				// forms are loaded in different frames.
				$ilDB->manipulate("INSERT INTO il_request_token (user_id, token, stamp, session_id) VALUES ".
					"(".
					$ilDB->quote($ilUser->getId(), "integer").",".
					$ilDB->quote($this->rtoken, "text").",".
					$ilDB->now().",".
					$ilDB->quote(session_id(), "text").")");
				return $this->rtoken;
			}
			//$this->rtoken = md5(uniqid(rand(), true));
		}
		return "";
	}
	
	/**
	* Verify Token
	*/
	function verifyToken()
	{
		global $ilDB, $ilUser;
		
		if (is_object($ilUser) && is_object($ilDB) && $ilUser->getId() > 0 &&
			$ilUser->getId() != ANONYMOUS_USER_ID)
		{
			if ($_GET["rtoken"] == "")
			{
				echo "ilCtrl::No Request Token Given!";		// for debugging, maybe changed later
				return false;
			}

			$set = $ilDB->query("SELECT * FROM il_request_token WHERE ".
				" user_id = ".$ilDB->quote($ilUser->getId(), "integer")." AND ".  	 	 
				" token = ".$ilDB->quote($_GET[self::IL_RTOKEN_NAME]), "text"); 		 
			if ($ilDB->numRows($set) > 0) 		 
			{
				// remove used token
				/*
				$ilDB->query("DELETE FROM il_request_token WHERE ". 		 
					" user_id = ".$ilDB->quote($ilUser->getId())." AND ". 		 
					" token = ".$ilDB->quote($_GET[self::IL_RTOKEN_NAME]));
				*/

				// remove tokens from older sessions
				$ilDB->manipulate("DELETE FROM il_request_token WHERE ". 		 
					" user_id = ".$ilDB->quote($ilUser->getId(), "integer")." AND ". 		 
					" session_id != ".$ilDB->quote(session_id(), "text"));
				return true; 		 
			} 		 
			else
			{
				return false;
			}
			
			if ($_SESSION["rtokens"][$_GET[self::IL_RTOKEN_NAME]] != "")
			{
				// remove used token
				unset($_SESSION["rtokens"][$_GET[self::IL_RTOKEN_NAME]]);
				
				// remove old tokens
				if (count($_SESSION["rtokens"]) > 100)
				{
					$to_remove = array();
					$sec = 7200;			// two hours

					foreach($_SESSION["rtokens"] as $tok => $time)
					{
						if (time() - $time > $sec)
						{
							$to_remove[] = $tok;
						}
					}
					foreach($to_remove as $tok)
					{
						unset($_SESSION["rtokens"][$tok]);
					}
				}
				
				return true;
			}
			return false;
		}
		else
		{
			return true;		// do not verify, if user or db object is missing
		}
		
		return false;
	}

	function redirect(&$a_gui_obj, $a_cmd = "", $a_anchor = "")
	{
		global $ilBench;
		
//echo "<br>class:".get_class($a_gui_obj).":";
		$script = $this->getLinkTargetByClass(strtolower(get_class($a_gui_obj)), $a_cmd);
		if  (is_object($ilBench))
		{
			$ilBench->save();
		}
		if ($a_anchor != "")
		{
			$script = $script."#".$a_anchor;
		}
		ilUtil::redirect($script);
	}


	/**
	* redirect to other gui class
	*
	* @param	string		$a_class		command target class
	* @param	string		$a_cmd			command
	*/
	function redirectByClass($a_class, $a_cmd = "")
	{
		// $a_class may be an array
		//$a_class = strtolower($a_class);

//echo "<br>class:".get_class($a_gui_obj).":";
		$script = $this->getLinkTargetByClass($a_class, $a_cmd);
//echo "<br>script:$script:";
		ilUtil::redirect($script);
	}
	
	function isAsynch()
	{
		if ($_GET["cmdMode"] == "asynch")
		{
			return true;
		}
		else
		{
			return false;
		}
	}


	/**
	* get link target for (current) gui class
	*
	* @param	object		$a_gui_obj		(current) gui object (usually $this)
	* @param	string		$a_cmd			command
	*
	* @return	string		target link
	*/
	function getLinkTarget(&$a_gui_obj, $a_cmd = "", $a_anchor = "", $a_asynch = false)
	{
//echo "<br>getLinkTarget";
		$script = $this->getLinkTargetByClass(strtolower(get_class($a_gui_obj)), $a_cmd, $a_anchor, $a_asynch);
		return $script;
	}


	/**
	* get link target for a target class
	*
	* @param	string		$a_class		command target class
	* @param	string		$a_cmd			command
	* @param	array		$a_anchor		# anchor
	* @param	array		$a_asynch		asynchronous mode
	*
	* @return	string		target link
	*/
	function getLinkTargetByClass($a_class, $a_cmd  = "", $a_anchor = "", $a_asynch = false)
	{
		// note: $a_class may be an array
		//$a_class = strtolower($a_class);

//echo "<br>getLinkTargetByClass";
		$script = $this->getTargetScript();
		$script = $this->getUrlParameters($a_class, $script, $a_cmd);

		if ($a_asynch)
		{
			$script.= "&cmdMode=asynch";
		}
		
		if ($a_anchor != "")
		{
			$script = $script."#".$a_anchor;
		}

		return $script;
	}

	/**
	* set return command
	*/
	function setReturn(&$a_gui_obj, $a_cmd)
	{
		$script = $this->getTargetScript();
		$script = $this->getUrlParameters(strtolower(get_class($a_gui_obj)), $script, $a_cmd);
//echo "<br>setReturn:".get_class($a_gui_obj).":".$script.":<br>";
		$this->return[strtolower(get_class($a_gui_obj))] = $script;
	}

	/**
	* set return command
	*/
	function setReturnByClass($a_class, $a_cmd)
	{
		// may not be an array!
		$a_class = strtolower($a_class);

		$script = $this->getTargetScript();
		$script = $this->getUrlParameters($a_class, $script, $a_cmd);
//echo "<br>setReturn:".get_class($a_gui_obj).":".$script.":<br>";
		$this->return[strtolower($a_class)] = $script;
	}

	/**
	* redirects to next parent class that used setReturn
	*/
	function returnToParent(&$a_gui_obj, $a_anchor = "")
	{
		$script = $this->getParentReturn($a_gui_obj);

		$script = ilUtil::appendUrlParameterString($script,
			"redirectSource=".strtolower(get_class($a_gui_obj)));
		$script = ilUtil::appendUrlParameterString($script,
			"cmdMode=".$_GET["cmdMode"]);
		if ($a_anchor != "")
		{
			$script = $script."#".$a_anchor;
		}

		ilUtil::redirect($script);
	}


	/**
	* get current redirect source
	*
	* @return	string		redirect source class
	*/
	function getRedirectSource()
	{
		return $_GET["redirectSource"];
	}

	/**
	*
	*/
	function getParentReturn(&$a_gui_obj)
	{
		return $this->getParentReturnByClass(strtolower(get_class($a_gui_obj)));
	}


	function getParentReturnByClass($a_class)
	{
		$a_class = strtolower($a_class);
		$ret_class = $this->searchReturnClass($a_class);
//echo ":$ret_class:";
		if($ret_class)
		{
//echo ":".$this->return[$ret_class].":";
			return $this->return[$ret_class];
		}
	}

	/**
	* get current return class
	*/
	function searchReturnClass($a_class)
	{
		$a_class = strtolower($a_class);

		$nr = $this->getNodeIdForTargetClass($this->current_node, $a_class);
		$path = $this->getPathNew(1, $nr);
//var_dump($path);
		for($i = count($path)-2; $i>=0; $i--)
		{
//echo "<br>:$i:".$path[$i].":".$this->call_node[$path[$i]]["class"]
//	       .":".$this->return[$this->call_node[$path[$i]]["class"]].":";
			if ($this->return[$this->call_node[$path[$i]]["class"]] != "")
			{
				return $this->call_node[$path[$i]]["class"];
			}
		}

		return false;
	}

	function getUrlParameters($a_class, $a_str, $a_cmd = "", $a_transits = "")
	{
		// note: $a_class may be an array!
		//$a_class = strtolower($a_class);

		$params = $this->getParameterArrayByClass($a_class, $a_cmd, $a_transits);

		foreach ($params as $par => $value)
		{
			if (strlen($value))
			{
				$a_str = ilUtil::appendUrlParameterString($a_str, $par."=".$value);
			}
		}

		return $a_str;
	}

	function appendTransitClasses($a_str)
	{
		if (is_array($_GET["cmdTransit"]))
		{
			reset($_GET["cmdTransit"]);
			foreach ($_GET["cmdTransit"] as $transit)
			{
				$a_str = ilUtil::appendUrlParameterString($a_str, "cmdTransit[]=".$transit);
			}
		}
		return $a_str;
	}

	function getTransitArray()
	{
		$trans_arr = array();
		if (is_array($_GET["cmdTransit"]))
		{
			reset($_GET["cmdTransit"]);
			foreach ($_GET["cmdTransit"] as $key => $transit)
			{
				$trans_arr["cmdTransit[".$key."]"] = $transit;
			}
		}
		return $trans_arr;
	}

	function addTransit($a_class)
	{
		$a_class = strtolower($a_class);
		$_GET["cmdTransit"][] = $a_class;
	}

	function getParameterArray(&$a_gui_obj, $a_cmd = "", $a_incl_transit = true)
	{
		$par_arr = $this->getParameterArrayByClass(strtolower(get_class($a_gui_obj)), $a_cmd,
			$trans_arr);

		return $par_arr;
	}

	/**
	*
	*/
	function getParameterArrayByClass($a_class, $a_cmd = "", $a_transits = "")
	{
//echo "<br>getparameter for $a_class";
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
//echo "<br>-$class-";
			$class = strtolower($class);
			$nr = $this->getNodeIdForTargetClass($nr, $class);
			$target_class = $class;
//echo "-$nr-";
		}

		$path = $this->getPathNew(1, $nr);
//echo "$nr"; 
//var_dump($path);
		$params = array();

		// append parameters of parent classes
		foreach($path as $node_id)
		{
			$class = $this->call_node[$node_id]["class"];
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


} // END class.ilCtrl
?>
