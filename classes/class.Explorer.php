<?php
/**
 * Class Explorer 
 * class for explorer view in admin frame
 * @author Stefan Meyer <smeyer@databay.de>
 * @version $Id$
 * @package ilias-core
 * @todo maybe only container should be visible, because the number oj objects could be to big for recursion
 * implement a sort function
 */
class Explorer extends PEAR
{
	/**
	* ilias object
	* @var object Ilias
	* @access public
	*/
	var $ilias;

	/**
	* output
	* @var string
	* @access public
	*/
	var $output;

	/**
	* contains format options
	* @var array
	* @access public
	*/
	var $format_options;
	
	/**
	* tree
	* @var object Tree
	* @access public
	*/
	var $tree;
	
	/**
	* Constructor
	* @access public
	* @param object Ilias
	*
	*/
	function Explorer(&$a_ilias)
	{
		$this->PEAR();
		$this->ilias = $a_ilias;
		$this->output = "";
		
		$this->tree = new Tree(1,0);
	}
	
	/**
	* Creates output for explorer view in admin menue
	* recursive method
	* @param int
	* @param int
	* @access public
	* @return string
	*/
	function setOutput($a_depth,$a_parent)
	{
		global $rbacadmin, $rbacsystem, $expanded;

		if($objects =  $this->tree->getChildsByDepth($a_depth,$a_parent))
		{
			$tab = ++$a_depth - 2;
			// Maybe call a lexical sort function for the child objects
			foreach($objects as $key => $object)
			{
				if($rbacsystem->checkAccess('visible',$object["id"],$object["parent"]))
				{
					$this->format_options["$object[id]"]["parent"] = $object["parent"];
					$this->format_options["$object[id]"]["title"] = $object["title"];
					$this->format_options["$object[id]"]["type"] = $object["type"];
					$this->format_options["$object[id]"]["depth"] = $tab;
					$this->format_options["$object[id]"]["container"] = false;
					$this->format_options["$object[id]"]["visible"]	  = true;

					// Create prefix array
					for($i=0;$i<$tab;++$i)
					{
						 $this->format_options["$object[id]"]["tab"][] = 'blank';
					}

					// only if parent is expanded and visible, object is visible
					if($object["id"] != 1 and (!in_array($object["parent"],$expanded) 
					   or !$this->format_options["$object[parent]"]["visible"]))
					{
						$this->format_options["$object[id]"]["visible"] = false;
					}
						
					// if object exists parent is container
					if($object["id"] != 1)
					{
						$this->format_options["$object[parent]"]["container"] = true;
						if(in_array($object["parent"],$expanded))
						{
							$this->format_options["$object[parent]"]["tab"][($tab-2)] = 'minus';
						}
						else
							$this->format_options["$object[parent]"]["tab"][($tab-2)] = 'plus';
					}
					// Recursive
					$this->setOutput($a_depth,$object["id"]);
				}
			}
		}
	}

	/**
	* Creates output
	* recursive method
	* @access public
	* @return string
	*/
	function getOutput()
	{
		$this->format_options[1]["tab"] = array();
		
		$depth = $this->tree->getMaximumDepth();
		
		$tmp = $this->format_options;
		unset($this->format_options);
		$i=0;
		foreach($tmp as $key => $option)
		{
			$this->format_options[$i] = $option;
			$this->format_options[$i++]["obj_id"] = $key;
		}

		for($i=0;$i<$depth;++$i)
		{
			$this->createLines($i);
		}
		foreach($this->format_options as $key => $options)
		{
			if($options["visible"] or $key == 0 )
			{
				$this->formatObject($options["obj_id"],$options);
			}
		}
		return implode('',$this->output);
	}
	
	
	/**
	* Creates output
	* recursive method
	* @param int
	* @param int
	* @access public
	* @return string
	*/
	function formatObject($a_obj_id,$a_option)
	{
		$tmp = '';
		$tmp  .= "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\">\n";
		$tmp  .= "<tr>\n";

		foreach($a_option["tab"] as $picture)
		{
			if($picture == 'plus')
			{
				$target = $this->createTarget('+',$a_obj_id);

				// create expand href
				$tmp .= "<td nowrap align=\"left\"><a href=\"".$target."\"><img src=\"./images/browser/".
					$picture.".gif\" border=\"0\"></a></td>";
			}
			if($picture == 'minus')
			{
				$target = $this->createTarget('-',$a_obj_id);

				// create href
				$tmp .= "<td nowrap align=\"left\"><a href=\"".$target."\"><img src=\"./images/browser/".
					$picture.".gif\" border=\"0\"></a></td>";
			}
			if($picture == 'blank' or $picture == 'winkel' 
			   or $picture == 'hoch' or $picture == 'quer' or $picture == 'ecke')
			{
				$tmp .= "<td nowrap align=\"left\"><img src=\"./images/browser/".
					$picture.".gif\" border=\"0\"></td>";
			}
		}
		$tmp  .= "<td nowrap align=\"left\"><img src=\"./images/icon_".$a_option["type"].".gif\" border=\"0\"></td>\n";
		$tmp  .= "<td nowrap align=\"left\"><a href=\"content.php?obj_id=".$a_obj_id.
			"&parent=".$a_option["parent"]."\" target=\"content\">".$a_option["title"]."</a></td>\n";
		$tmp  .= "</tr>\n";
		$tmp  .= "</table>\n";
		$this->output[] = $tmp;
	}
	
/**
 * Creates Get Parameter
 * @access private
 * @param string
 * @param int
 * @return string
 */
	function createTarget($a_type,$a_obj_id)
	{
		global $expanded;

		$tmp_expanded = $expanded;

		if($a_type == '+')
		{
			return $_SERVER["REQUEST_URI"]."|".$a_obj_id;
		}
		if($a_type == '-')
		{
			$tmp = "?expand=";
			$tmp_expanded = array_flip($tmp_expanded);
			unset($tmp_expanded["$a_obj_id"]);
			$tmp_expanded = array_flip($tmp_expanded);
			
			return $tmp.implode('|',$tmp_expanded);
		}
	}
/**
 * Creates lines for explorer view
 * @access private
 * @param int 
 */
	function createLines($a_depth)
	{
		for($i=0;$i<count($this->format_options);++$i)
		{
			if($this->format_options[$i]["depth"] == $a_depth+1
			   and !$this->format_options[$i]["container"]
				and $this->format_options[$i]["depth"] != 1)
			{
				$this->format_options[$i]["tab"]["$a_depth"] = "quer";
			}
			if($this->format_options[$i]["depth"] == $a_depth+2)
			{
				if($this->is_in_array($i+1,$this->format_options[$i]["depth"]))
				{
					$this->format_options[$i]["tab"]["$a_depth"] = "winkel";
				}
				else
				{
					$this->format_options[$i]["tab"]["$a_depth"] = "ecke";
				}
			}
			if($this->format_options[$i]["depth"] > $a_depth+2)
			{
				if($this->is_in_array($i+1,$a_depth+2))
				{
					$this->format_options[$i]["tab"]["$a_depth"] = "hoch";
				}
			}
		}
	}
/**
 *
 * @access private
 * @param int
 * @param int
 * @return bool
 */
	function is_in_array($a_start,$a_depth)
	{
		for($i=$a_start;$i<count($this->format_options);++$i)
		{
			if($this->format_options[$i]["depth"] < $a_depth)
			{
				break;
			}
			if($this->format_options[$i]["depth"] == $a_depth)
			{
				return true;
			}
		}
		return false;
	}
}
?>
