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
* This class provides processing control methods
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
	*
	*/
	function ilCtrl()
	{
		global $ilBench;

		$this->bench =& $ilBench;
		$this->transit = array();
//echo "<br><br>:".$_GET["cmdTransit"].":";
		if (is_array($_GET["cmdTransit"]))
		{
			foreach($_GET["cmdTransit"] as $transClass)
			{
//echo "<br>:$transClass:";
				$this->transit[] = strtolower($transClass);
			}
		}
	}

	function getNextTransit()
	{
		reset($this->transit);
		foreach($this->transit as $transClass)
		{
			if ($transClass != "")
			{
//echo "<br><br>".$transClass;
				return strtolower($transClass);
			}
		}

		return false;
	}

	function removeTransit()
	{
		reset($this->transit);
		foreach($this->transit as $key => $transClass)
		{
			if ($transClass != "")
			{
				$this->transit[$key] = "";
			}
		}
	}

	function getCallStructure($a_class)
	{
		$this->called_forward[$a_class] = $a_class;

		$methods = get_class_methods($a_class);

		if (!is_array($methods))
		{
			$a_class = strtolower($a_class);
			if ($this->parent[$a_class][0] == "")
			{
				echo "<b>Error in ilCtrl::getCallStructure():</b><br> $a_class is not included!";
			}
			else
			{
				echo "<b>Error in ilCtrl::getCallStructure:</b><br> $a_class is not included within ".
					$this->parent[$a_class][0]."!<br><br>";
				echo "$a_class is returned by ".$this->parent[$a_class][0]."::_forwards()".
					" but $a_class is not included at the top of ".$this->parent[$a_class][0].
					" class file.";
			}
			exit;
		}

		if (in_array(strtolower("_forwards"), $methods))
		{
			$forw = call_user_func(array($a_class, "_forwards"));
			$this->forwards($a_class, $forw);
			if (is_array($forw))
			{
				foreach($forw as $forw_class)
				{
					if (!isset($this->called_forward[$forw_class]))
					{
						$this->getCallStructure($forw_class);
					}
				}
			}
		}

		$this->root_class = $a_class;
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
	*/
	function saveParameter(&$a_obj, $a_parameter)
	{
		if (is_array($a_parameter))
		{
			foreach($a_parameter as $parameter)
			{
				$this->save_parameter[get_class($a_obj)][] = $parameter;
			}
		}
		else
		{
			$this->save_parameter[strtolower(get_class($a_obj))][] = $a_parameter;
		}
	}

	/**
	* set a parameter (note: if this is also a saved parameter, the saved
	* value will be overwritten)
	*/
	function setParameter(&$a_obj, $a_parameter, $a_value)
	{
		$this->parameter[strtolower(get_class($a_obj))][$a_parameter] = $a_value;
	}

	/**
	* set a parameter (note: if this is also a saved parameter, the saved
	* value will be overwritten)
	*/
	function setParameterByClass($a_class, $a_parameter, $a_value)
	{
		$this->parameter[strtolower($a_class)][$a_parameter] = $a_value;
	}

	/**
	* get next class
	*/
	function getNextClass($a_gui_obj)
	{
//echo "<br>getNextClass::"; flush();
		$path = array();
		$this->getPath($path, get_class($a_gui_obj));

		$next = $path[1];

		if ($this->getNextTransit() == $next)
		{
			$this->removeTransit();
		}
//echo "<br><b>".get_class($a_gui_obj).":".$this->getCmdClass().":</b>";
//foreach($path as $a_next) { echo "<br>->".$a_next; } echo "<br>";
		return $next;
	}

	function getPath(&$path, $a_class, $a_target_class = "", $a_transits = "")
	{
		$this->bench->start("GUIControl", "getPath");
//echo "<br>"; var_dump($a_transits);
		if ($a_target_class == "")
		{
			$a_target_class = $this->getCmdClass();
		}
		if ($a_target_class == "")
		{
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
	}

	/**
	* private
	*/
	function searchNext(&$a_path, $a_class, $a_target_class, $c_path = "")
	{
//echo "SN";
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
				$a_path = $c_path;
				return true;
			}
		}

//echo "<br>...$a_class:$targetClass:";

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
						$c_path[] = $next_class;
						$a_path = $c_path;
						return true;
					}
					else
					{
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
			foreach($this->forward[$a_class] as $next_class)
			{
				$c_path[] = $next_class;
				if ($this->searchNext($a_path, $next_class, $a_target_class, $c_path))
				{
					return true;
				}
			}
		}
		return false;
	}

	/**
	* target script name
	*/
	function setTargetScript($a_target_script)
	{
		$this->target_script = $a_target_script;
	}

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
			$cmd = key($_POST["cmd"]);
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
//echo "<br><b>setCmdClass:$a_cmd_class:</b>";
		$_GET["cmdClass"] = $a_cmd_class;
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
		$script =  $this->getFormActionByClass(get_class($a_gui_obj), $a_transits, $a_prepend_transits);
		return $script;
	}

	function getFormActionByClass($a_class, $a_transits = "", $a_prepend_transits = false)
	{
		$script = $this->getLinkTargetByClass($a_class, "post", $a_transits, $a_prepend_transits);
		return $script;
	}

	function redirect(&$a_gui_obj, $a_cmd = "")
	{
//echo "<br>class:".get_class($a_gui_obj).":";
		$script = $this->getLinkTargetByClass(get_class($a_gui_obj), $a_cmd);
//echo "<br>script:$script:";
		ilUtil::redirect($script);
	}

	function redirectByClass($a_class, $a_cmd = "")
	{
//echo "<br>class:".get_class($a_gui_obj).":";
		$script = $this->getLinkTargetByClass($a_class, $a_cmd);
//echo "<br>script:$script:";
		ilUtil::redirect($script);
	}

	function getLinkTarget(&$a_gui_obj, $a_cmd = "")
	{
//echo "<br>getLinkTarget";
		$script = $this->getLinkTargetByClass(get_class($a_gui_obj), $a_cmd);
		return $script;
	}

	function getLinkTargetByClass($a_class, $a_cmd  = "", $a_transits = "", $a_prepend_transits = false)
	{
		$a_class = strtolower($a_class);
//echo "<br>:".strtolower($_GET["cmdClass"]).":".$a_class.":";
		$transits = array();
		if((strtolower($_GET["cmdClass"]) == $a_class) ||
			$a_prepend_transits)
		{
			$transits = $this->getTransitArray();
//echo "<br>:"; var_dump($transits);
		}

		if(is_array($a_transits))
		{
			foreach($a_transits as $transit)
			{
				$transits[] = $transit;
			}
		}


//echo "<br>getLinkTargetByClass";
		$cmd_str = ($a_cmd != "")
			? "&cmd=".$a_cmd
			: "";
		$script = $this->getTargetScript();
		$script = $this->getUrlParameters($a_class, $script, $a_cmd, $transits);

		if(is_array($transits))
		{
			foreach($transits as $transit)
			{
				$script = ilUtil::appendUrlParameterString($script, "cmdTransit[]=".$transit);
//echo "<br>append:$transit:";
			}
		}
//echo $script.":$a_class:<br>";
		return $script;
	}

	/**
	* set return command
	*/
	function setReturn(&$a_gui_obj, $a_cmd)
	{
		$script = $this->getTargetScript();
		$script = $this->getUrlParameters(get_class($a_gui_obj), $script, $a_cmd);
//echo "<br>setReturn:".get_class($a_gui_obj).":".$script.":<br>";
		$this->return[get_class($a_gui_obj)] = $script;
	}

	/**
	* set return command
	*/
	function setReturnByClass($a_class, $a_cmd)
	{
		$script = $this->getTargetScript();
		$script = $this->getUrlParameters(strtolower($a_class), $script, $a_cmd);
//echo "<br>setReturn:".get_class($a_gui_obj).":".$script.":<br>";
		$this->return[strtolower($a_class)] = $script;
	}

	/**
	* redirects to next parent class that used setReturn
	*/
	function returnToParent(&$a_gui_obj)
	{
		$script = $this->getParentReturn($a_gui_obj);
		$script = ilUtil::appendUrlParameterString($script,
			"redirectSource=".get_class($a_gui_obj));
		ilUtil::redirect($script);
	}

	function getRedirectSource()
	{
		return $_GET["redirectSource"];
	}

	function getParentReturn(&$a_gui_obj)
	{
		return $this->getParentReturnByClass(get_class($a_gui_obj));
	}

	function getParentReturnByClass($a_class)
	{
		$ret_class = $this->searchReturnClass($a_class);
		if($ret_class)
		{
			return $this->return[$ret_class];
		}
	}

	function searchReturnClass($a_class)
	{
		$path = array();
		$this->getPath(&$path, strtolower($this->root_class), $a_class, $_GET["cmdTransit"]);
		for($i = count($path)-2; $i>=0; $i--)
		{
			if ($this->return[$path[$i]] != "")
			{
				return $path[$i];
			}
		}

		return false;

		// append parameters of parent classes
		if (is_array($this->parent[$a_class]))
		{
			foreach($this->parent[$a_class] as $parent)
			{
				if ($this->return[$parent] != "")
				{
					return $parent;
				}
			}
			foreach($this->parent[$a_class] as $parent)
			{
				$par_ret = $this->searchReturnClass($parent);
				if ($par_ret != "")
				{
					return $par_ret;
				}
			}
		}
		return false;
	}

	function getUrlParameters($a_class, $a_str, $a_cmd = "", $a_transits = "")
	{
//echo "<br>getUrlParameters";
//echo "<br>"; var_dump($a_transits);
		$a_class = strtolower($a_class);

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
		$_GET["cmdTransit"][] = $a_class;
	}

	function getParameterArray(&$a_gui_obj, $a_cmd = "", $a_incl_transit = true)
	{
		if ($a_incl_transit)
		{
			$trans_arr = $this->getTransitArray();
		}
		$par_arr = $this->getParameterArrayByClass(get_class($a_gui_obj), $a_cmd,
			$trans_arr);

		if ($a_incl_transit)
		{
			$par_arr = array_merge($par_arr, $trans_arr);
		}
		return $par_arr;
	}

	function getParameterArrayByClass($a_class, $a_cmd = "", $a_transits = "")
	{
		$a_class = strtolower($a_class);

//echo "-$a_class-";
		if ($a_class == "")
		{
			return array();
		}

		// get root class
		$root = $a_class;
		while (isset($this->parent[$root][0]))
		{
			$root = $this->parent[$root][0];
		}
//echo "<br>:$a_class:$root:";
		$path = array();
		$this->getPath($path, $root, $a_class, $a_transits);

		$params = array();

		// append parameters of parent classes
		foreach($path as $class)
		{
//echo "<br>->:$class:";
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

		$params["cmdClass"] = $a_class;

		return $params;
	}


} // END class.ilCtrl
?>
