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
* @package ilias-core
*/
class ilCtrl
{
	var $target_script;
	var $forward;			// forward array
	var $parent;			// parent array (reverse forward)
	var $save_parameter;	// save parameter array
	var $return;			// return commmands

	/**
	* control class constructor
	*/
	function ilCtrl()
	{
		global $ilBench;

		$this->bench =& $ilBench;
		$this->transit = array();

		$this->location = array();
		$this->tab = array();
		$this->current_node = 0;

	}


	/**
	* forward flow of control to next gui class
	* this invokes the executeCommand() method of the
	* gui object that is passed via reference
	*
	* @param	object		$a_gui_object		gui object that should receive
	*											the flow of control
	*
	* @return	mixed		return data of invoked executeCommand() method
	*/
	function &forwardCommand(&$a_gui_object)
	{
		$class = strtolower(get_class($a_gui_object));
//echo "<br>wanna forward from :".$this->current_node.": to :$class:";
		$nr = $this->getNodeIdForTargetClass($this->current_node, $class);
		if ($nr > 0)
		{
//echo "<br>-> forwarding to class:<b>$class</b>, command:".$this->getCmd()."():"; flush();
			$this->current_node = $nr;
			return $a_gui_object->executeCommand();
		}
		echo "ERROR: Can't forward to class $class."; exit;
//echo "end forward<br>";
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

		echo "ERROR: Can't find target class $a_class for node $a_par_node.<br>"; exit;
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
	function addLocation($a_title, $a_link, $a_target = "")
	{
		$this->location[] = array("title" => $a_title,
			"link" => $a_link, "target" => $a_target);
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
	* get call structure of class context
	*/
	function getCallStructure($a_class, $a_nr = 0, $a_parent = 0)
	{
		global $ilDB;

		$a_class = strtolower($a_class);

		$a_nr++;
		$this->call_node[$a_nr] = array("class" => $a_class, "parent" => $a_parent);
//echo "nr:$a_nr:class:$a_class:parent:$a_parent:<br>";
		$q = "SELECT * FROM ctrl_calls WHERE parent=".
			$ilDB->quote(strtolower($a_class)).
			" ORDER BY child";

		$call_set = $ilDB->query($q);
		//$forw = array();
		$a_parent = $a_nr;
		while($call_rec = $call_set->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$a_nr = $this->getCallStructure($call_rec["child"], $a_nr, $a_parent);
			$forw[] = $call_rec["child"];
		}
		$this->forwards($a_class, $forw);
//echo "<br><br>forwards:".$a_class."<br>"; var_dump($forw);

		$this->root_class = $a_class;
		return $a_nr;
	}

	/**
	* stores which classes forward to which other classes
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
	* set parameters that must be saved in forms an links
	*
	* @param	object	$a_obj		 command object for that the parameter should be saved
	*								 between multiple http requests
	* @param	string	$a_parameter parameter name
	* @access	public
	*/
	function saveParameter(&$a_obj, $a_parameter)
	{
		if (is_array($a_parameter))
		{
			foreach($a_parameter as $parameter)
			{
				$this->save_parameter[strtolower(get_class($a_obj))][] = $parameter;
			}
		}
		else
		{
			$this->save_parameter[strtolower(get_class($a_obj))][] = $a_parameter;
		}
	}

	/**
	* Set a parameter (note: if this is also a saved parameter, the saved
	* value will be overwritten).
	*/
	function setParameter(&$a_obj, $a_parameter, $a_value)
	{
		$this->parameter[strtolower(get_class($a_obj))][$a_parameter] = $a_value;
	}

	/**
	* Set a parameter (note: if this is also a saved parameter, the saved
	* value will be overwritten).
	*/
	function setParameterByClass($a_class, $a_parameter, $a_value)
	{
		$this->parameter[strtolower($a_class)][$a_parameter] = $a_value;
	}


	/**
	* Get next class of the way from the current class
	* to the target command class (this is the class that should
	* be instantiated and be invoked via $ilCtrl->forwardCommand($class)
	* next).
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
					", Target:".$a_target_node; exit;
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

	/*
	function getPath(&$path, $a_class, $a_target_class = "", $a_transits = "")
	{

		// new path
		if(!empty($this->call_node))
		{
			//$path = $this->getPathNew($a_class, $a_target_class);
		}

		$this->call_node[$nr] = array("class" => $a_class, "parent" => $a_parent);

		$this->bench->start("GUIControl", "getPath");
//echo "<br>"; var_dump($a_transits);
		if ($a_target_class == "")
		{
			$a_target_class = $this->getCmdClass();
		}
		if ($a_target_class == "")
		{
//echo "1:$a_class<br>";
			$path = array($a_class);
			return;
		}
//echo "<br><b>FROM:".$a_class.":TO:".$a_target_class.":</b>";
//echo "1";
		$this->store_transit = $this->transit;
		if (is_array($a_transits))
		{
			$this->transit = $a_transits;
		}
		else
		{
			if ($a_target_class != $this->getCmdClass())
			{
//echo "<br>:$a_target_class:".$this->getCmdClass().":DELTRANSIT";
				$this->transit = array();
			}
		}
//echo "<br>"; var_dump($this->transit);
		$next = $this->searchNext($path, $a_class, $a_target_class, array($a_class));
		$this->transit = $this->store_transit;
//foreach($path as $a_next) { echo "<br>->".$a_next; } echo "<br>";
		$this->bench->stop("GUIControl", "getPath");
	}*/

	/**
	* private
	*/
	/*
	function searchNext(&$a_path, $a_class, $a_target_class, $c_path = "")
	{
		$a_target_class = strtolower($a_target_class);
		$a_class = strtolower($a_class);

		if ($targetClass = $this->getNextTransit())
		{
			if ($a_class == $targetClass)
			{
				$this->removeTransit();
			}
		}
		else
		{
			$targetClass = $a_target_class;
			if ($a_class == $a_target_class)
			{
//echo "2:$c_class<br>";
				$a_path = $c_path;
				return true;
			}
		}

//echo "<br>...$a_class:$targetClass:<br>";

		// recursively search each forward
		if (is_array($this->forward[$a_class]))
		{
			reset($this->forward[$a_class]);
			foreach($this->forward[$a_class] as $next_class)
			{
				if ($next_class == strtolower($targetClass))
				{
					// found command class
					if ($next_class == $a_target_class)
					{
//echo "3:$next_class<br>";
						$c_path[] = $next_class;
						$a_path = $c_path;
						return true;
					}
					else
					{
//echo "4:$next_class<br>";
						// found a transit class
						$c_path[] = $next_class;
						$this->removeTransit();					// remove transit
						if ($this->searchNext($a_path, $next_class, $a_target_class, $c_path))
						{
							return true;
						}
						return false;
					}
				}
			}
			reset($this->forward[$a_class]);
//echo "4a:($a_class)<br";
			foreach($this->forward[$a_class] as $next_class)
			{
//echo "5:$a_class:$next_class<br>";
				$c_path[] = $next_class;
				if ($this->searchNext($a_path, $next_class, $a_target_class, $c_path))
				{
//echo "6:YES:"; var_dump($a_path);
					return true;
				}
			}
		}
		return false;
	}*/


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
	* determines current get/post command
	*/
	function getCmd($a_default_cmd = "")
	{
		$cmd = $_GET["cmd"];
		if($cmd == "post")
		{
			$cmd = @key($_POST["cmd"]);
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

//echo "<br><b>setCmdClass:$a_cmd_class:</b>";
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

	function getFormAction(&$a_gui_obj, $a_transits = "", $a_prepend_transits = false)
	{
		$script =  $this->getFormActionByClass(strtolower(get_class($a_gui_obj)), $a_transits, $a_prepend_transits);
		return $script;
	}

	function getFormActionByClass($a_class, $a_transits = "", $a_prepend_transits = false)
	{
		$a_class = strtolower($a_class);

		$script = $this->getLinkTargetByClass($a_class, "post", $a_transits, $a_prepend_transits);
		return $script;
	}

	function redirect(&$a_gui_obj, $a_cmd = "")
	{
//echo "<br>class:".get_class($a_gui_obj).":";
		$script = $this->getLinkTargetByClass(strtolower(get_class($a_gui_obj)), $a_cmd);
//echo "<br>script:$script:";
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


	/**
	* get link target for (current) gui class
	*
	* @param	object		$a_gui_obj		(current) gui object (usually $this)
	* @param	string		$a_cmd			command
	*
	* @return	string		target link
	*/
	function getLinkTarget(&$a_gui_obj, $a_cmd = "")
	{
//echo "<br>getLinkTarget";
		$script = $this->getLinkTargetByClass(strtolower(get_class($a_gui_obj)), $a_cmd);
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
	function getLinkTargetByClass($a_class, $a_cmd  = "", $a_transits = "", $a_prepend_transits = false)
	{
		// note: $a_class may be an array
		//$a_class = strtolower($a_class);

//echo "<br>getLinkTargetByClass";
		$script = $this->getTargetScript();
		$script = $this->getUrlParameters($a_class, $script, $a_cmd, $transits);

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
			$a_str = ilUtil::appendUrlParameterString($a_str, $par."=".$value);
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

		return $params;
	}


} // END class.ilCtrl
?>
