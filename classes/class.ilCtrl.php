<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/

/**
* This class provides processing control methods.
* A global instance is available via variable $ilCtrl
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*/
class ilCtrl
{
	var $target_script;
	var $forward;			// forward array
	var $parent;			// parent array (reverse forward)
	var $save_parameter;	// save parameter array
	var $return;			// return commmands
	var $call_hist = array();	// calling history

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
		$q = "SELECT * FROM module_class WHERE LOWER(class) = ".
			$ilDB->quote($baseClass);
		$mc_set = $ilDB->query($q);
		$mc_rec = $mc_set->fetchRow(DB_FETCHMODE_ASSOC);
		$module = $mc_rec["module"];
		$class = $mc_rec["class"];
		$class_dir = $mc_rec["dir"];
		
		if ($module != "")
		{
			// get module information
			$q = "SELECT * FROM module WHERE name = ".
				$ilDB->quote($module);
	
			$m_set = $ilDB->query($q);
			$m_rec = $m_set->fetchRow(DB_FETCHMODE_ASSOC);
			$this->module_dir = $m_rec["dir"];
			include_once $this->module_dir."/".$class_dir."/class.".$class.".php";
		}
		else		// check whether class belongs to a service
		{
			// get class information
			$q = "SELECT * FROM service_class WHERE LOWER(class) = ".
				$ilDB->quote($baseClass);

			$mc_set = $ilDB->query($q);
			$mc_rec = $mc_set->fetchRow(DB_FETCHMODE_ASSOC);
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
			$q = "SELECT * FROM service WHERE name = ".
				$ilDB->quote($service);
	
			$m_set = $ilDB->query($q);
			$m_rec = $m_set->fetchRow(DB_FETCHMODE_ASSOC);
			$this->service_dir = $m_rec["dir"];
			
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
//echo "<br>forwarding to: -$class-";
		$nr = $this->getNodeIdForTargetClass($this->current_node, $class);
		if ($nr > 0)
		{
			$current_node = $this->current_node;
			
			$this->current_node = $nr;

			if (DEVMODE == "1")
			{
				$this->call_hist[] = get_class($a_gui_object);
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
				$this->call_hist[] = get_class($a_gui_object);
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

		echo "ERROR: Can't find target class $a_class for node $a_par_node ".
			"(".$this->call_node[$a_par_node]["class"].").<br>";
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
			$q = "SELECT * FROM ctrl_structure WHERE root_class = ".
				$ilDB->quote($a_class);
			$set = $ilDB->query($q);
			$rec = $set->fetchRow(DB_FETCHMODE_ASSOC);
			$this->call_node = unserialize($rec["call_node"]);
			$this->forward = unserialize($rec["forward"]);
			$this->parent = unserialize($rec["parent"]);
			$this->root_class = $a_class;
		}
		else
		{
			$this->readCallStructure($a_class, $a_nr, $a_parent);
		}
		
		// check whether command node and command class fit together
		if ($_GET["cmdNode"] > 0)
		{
			if (strtolower($this->call_node[$_GET["cmdNode"]]["class"]) !=
				strtolower($_GET["cmdClass"]))
			{
				if (DEVMODE)
				{
					die ("Internal Error: ilCtrl Node Error.");
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
					ilUtil::sendInfo("Sorry, but the request includes invalid parameters." ,true);
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
		
		$q = "DELETE FROM ctrl_structure";
		$ilDB->query($q);
		
		foreach ($this->stored_trees as $root_gui_class)
		{
			$this->call_node = array();
			$this->forward = array();
			$this->parent = array();
			$this->readCallStructure($root_gui_class);
			$q = "INSERT INTO ctrl_structure (root_class, call_node, forward, parent) VALUES (".
				$ilDB->quote($root_gui_class).",".
				$ilDB->quote(serialize($this->call_node)).",".
				$ilDB->quote(serialize($this->forward)).",".
				$ilDB->quote(serialize($this->parent)).")";
			$ilDB->query($q);
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
		$q = "SELECT * FROM ctrl_calls WHERE parent=".
			$ilDB->quote(strtolower($a_class)).
			" ORDER BY child";

		$call_set = $ilDB->query($q);
		//$forw = array();
		$a_parent = $a_nr;
		while($call_rec = $call_set->fetchRow(DB_FETCHMODE_ASSOC))
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
				$this->forward[$a_from_class][] = strtolower($to_class);
				$this->parent[strtolower($to_class)][] = $a_from_class;
			}
		}
		else
		{
			$this->forward[strtolower(get_class($a_obj))][] = strtolower($a_to_class);
			$this->parent[strtolower($a_to_class)][] = strtolower(get_class($a_obj));
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

		$q = "SELECT * FROM ctrl_classfile WHERE class = ".$ilDB->quote($a_class_name);

		$class_set = $ilDB->query($q);
		$class_rec = $class_set->fetchRow(DB_FETCHMODE_ASSOC);

		return $class_rec["file"];
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
	*/
	function getCmd($a_default_cmd = "")
	{
		$cmd = $_GET["cmd"];
		if($cmd == "post")
		{
			if (is_array($_POST["cmd"]))
			{
				reset($_POST["cmd"]);
			}
			$cmd = @key($_POST["cmd"]);
			if($cmd == "" && isset($_POST["select_cmd"]))		// selected command in multi-list (table2)
			{
				$cmd = $_POST["selected_cmd"];
			}
			if($cmd == "")
			{
				$cmd = $_GET["fallbackCmd"];
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
	*/
	function getFormAction(&$a_gui_obj, $a_fallback_cmd = "")
	{
		$script =  $this->getFormActionByClass(strtolower(get_class($a_gui_obj)),
			$a_fallback_cmd);
		return $script;
	}

	/**
	* get form action url for gui class name
	*
	* @param	string		$a_class		gui class name
	*/
	function getFormActionByClass($a_class, $a_fallback_cmd = "")
	{
		$a_class = strtolower($a_class);

		$script = $this->getLinkTargetByClass($a_class, "post");
		if ($a_fallback_cmd != "")
		{
			$script = ilUtil::appendUrlParameterString($script, "fallbackCmd=".$a_fallback_cmd);
		}
		return $script;
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
	* @param	array		$a_transits		transit classes (deprecated)
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
//echo "<br>searchReturnClass".$a_class;
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
