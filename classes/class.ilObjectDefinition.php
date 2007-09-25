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
* parses the objects.xml
* it handles the xml-description of all ilias objects
*
* @author Stefan Meyer <smeyer@databay>
* @version $Id$
*
* @extends PEAR
*/
class ilObjectDefinition extends ilSaxParser
{
	/**
	* // TODO: var is not used
	* object id of specific object
	* @var obj_id
	* @access private
	*/
	var $obj_id;

	/**
	* parent id of object
	* @var parent id
	* @access private
	*/
	var $parent;

	/**
	* array representation of objects
	* @var objects
	* @access private
	*/
	var $obj_data;

	/**
	* Constructor
	* 
	* setup ILIAS global object
	* @access	public
	*/
	function ilObjectDefinition()
	{
		parent::ilSaxParser(ILIAS_ABSOLUTE_PATH."/objects.xml");
	}

// PUBLIC METHODS

	/**
	* get object definition by type
	*
	* @param	string	object type
	* @access	public
	*/
	function getDefinition($a_obj_name)
	{
		return $this->obj_data[$a_obj_name];
	}

	/**
	* get class name by type
	*
	* @param	string	object type
	* @access	public
	*/
	function getClassName($a_obj_name)
	{
		return $this->obj_data[$a_obj_name]["class_name"];
	}


	/**
	* get location by type
	*
	* @param	string	object type
	* @access	public
	*/
	function getLocation($a_obj_name)
	{
		return $this->obj_data[$a_obj_name]["location"];
	}


	/**
	* get module by type
	*
	* @param	string	object type
	* @access	public
	*/
	function getModule($a_obj_name)
	{
		return $this->obj_data[$a_obj_name]["module"];
	}


	/**
	* should the object get a checkbox (needed for 'cut','copy' ...)
	*
	* @param	string	object type
	* @access	public
	*/
	function hasCheckbox($a_obj_name)
	{
		return (bool) $this->obj_data[$a_obj_name]["checkbox"];
	}
	
	/**
	* get translation type (sys, db or 0)s
	*
	* @param	string	object type
	* @access	public
	*/
	function getTranslationType($a_obj_name)
	{
		global $ilDB;
		
		if ($a_obj_name == "root")
		{
			if (!isset($this->root_trans_type))
			{
				$q = "SELECT count(*) as cnt FROM object_translation WHERE obj_id = ".
					$ilDB->quote(ROOT_FOLDER_ID);
				$set = $ilDB->query($q);
				$rec = $set->fetchRow(DB_FETCHMODE_ASSOC);
				if($rec["cnt"] > 0)
				{
					$this->root_trans_type = "db";
				}
				else
				{
					$this->root_trans_type = $this->obj_data[$a_obj_name]["translate"];
				}
			}
			return $this->root_trans_type;
		}
		
		return $this->obj_data[$a_obj_name]["translate"];
	}
	

	/**
	* Does object permits stopping inheritance?
	*
	* @param	string	object type
	* @access	public
	*/
	function stopInheritance($a_obj_name)
	{
		return (bool) $this->obj_data[$a_obj_name]["inherit"];
	}

	/**
	* get properties by type
	*
	* @param	string	object type
	* @access	public
	*/
	function getProperties($a_obj_name)
	{
		// dirty hack, has to be implemented better, if ilias.php
		// is established
		if (defined("ILIAS_MODULE") || $_GET["baseClass"] != "")
		{
			$props = array();
			if (is_array($this->obj_data[$a_obj_name]["properties"]))
			{
				foreach ($this->obj_data[$a_obj_name]["properties"] as $data => $prop)
				{
					if ($prop["module"] != "n")
					{
						$props[$data] = $prop;
					}
				}
			}
			return $props;
		}
		else
		{
			$props = array();
			if (is_array($this->obj_data[$a_obj_name]["properties"]))
			{
				foreach ($this->obj_data[$a_obj_name]["properties"] as $data => $prop)
				{
					if ($prop["module"] != 1)
					{
						$props[$data] = $prop;
					}
				}
			}
			return $props;
		}
	}

	/**
	* get devmode status by type
	*
	* @param	string	object type
	* @access	public
	*/
	function getDevMode($a_obj_name)
	{
		// always return false if devmode is enabled
		if (DEVMODE)
		{
			return false;
		}
		
		return (bool) $this->obj_data[$a_obj_name]["devmode"];
	}

	/**
	* get all object types in devmode
	*
	* @access	public
	* @return	array	object types set to development
	*/
	function getDevModeAll()
	{
		// always return empty array if devmode is enabled
		if (DEVMODE)
		{
			return array();
		}

		$types = array_keys($this->obj_data);
		
		foreach ($types as $type)
		{
			if ($this->getDevMode($type))
			{
				$devtypes[] = $type;
			}
		}

		return $devtypes ? $devtypes : array();
	}

	/**
	* get RBAC status by type
	* returns true if object type is a RBAC object type
	*
	* @param	string	object type
	* @access	public
	*/
	function isRBACObject($a_obj_name)
	{
		return (bool) $this->obj_data[$a_obj_name]["rbac"];
	}

	/**
	* get all RBAC object types
	*
	* @access	public
	* @return	array	object types set to development
	*/
	function getAllRBACObjects()
	{
		$types = array_keys($this->obj_data);
		
		foreach ($types as $type)
		{
			if ($this->isRBACObject($type))
			{
				$rbactypes[] = $type;
			}
		}

		return $rbactypes ? $rbactypes : array();
	}

	/**
	* get all object types
	*
	* @access	public
	* @return	array	object types
	*/
	function getAllObjects()
	{
		return array_keys($this->obj_data);
	}

	/**
	* checks if linking of an object type is allowed
	*
	* @param	string	object type
	* @access	public
	*/
	function allowLink($a_obj_name)
	{
		return (bool) $this->obj_data[$a_obj_name]["allow_link"];
	}

	/**
	* checks if copying of an object type is allowed
	*
	* @param	string	object type
	* @access	public
	*/
	function allowCopy($a_obj_name)
	{
		return (bool) $this->obj_data[$a_obj_name]["allow_copy"];
	}
	
	/**
	 * get content item sorting modes
	 *
	 * @access public
	 * @param 
	 * 
	 */
	public function getContentItemSortingModes($a_obj_name)
	{
	 	if(isset($this->obj_data[$a_obj_name]['sorting']))
	 	{
	 		return $this->obj_data[$a_obj_name]['sorting']['modes'] ? $this->obj_data[$a_obj_name]['sorting']['modes'] : array(); 
	 	}
	 	return array();
	}
	
	/**
	* get all subobjects by type
	*
	* @param	string	object type
	* @param	boolean	filter disabled objects? (default: true)
	* @access	public
	* @return	array	list of allowed object types
	*/
	function getSubObjects($a_obj_type,$a_filter = true)
	{
		$subs = array();

		if ($subobjects = $this->obj_data[$a_obj_type]["subobjects"])
		{
			// Filter some objects e.g chat object are creatable if chat is active
			if ($a_filter)
			{
				$this->__filterObjects($subobjects);
			}

			foreach ($subobjects as $data => $sub)
			{
				if ($sub["module"] != "n")
				{
					$subs[$data] = $sub;
				}
			}

			return $subs;
		}

		return $subs;
	}

	/**
	* Get all subobjects by type.
        * This function returns all subobjects allowed by the provided object type
        * and all its subobject types recursively.
        *
        * This function is used to create local role templates. It is important,
        * that we do not filter out any objects here!
        *
	*
	* @param	string	object type
	* @access	public
	* @return	array	list of allowed object types
	*/
	function getSubObjectsRecursively($a_obj_type)
	{
		// This associative array is used to collect all subobject types.
		// key=>type, value=data
		$recursivesubs = array();

		// This array is used to keep track of the object types, we
		// need to call function getSubobjects() for.
		$to_do = array($a_obj_type);

		// This array is used to keep track of the object types, we
		// have called function getSubobjects() already. This is to
		// prevent endless loops, for object types that support 
		// themselves as subobject types either directly or indirectly.
		$done = array();

		while (count($to_do) > 0)
		{
			$type = array_pop($to_do);
			$done[] = $type;
			$subs = $this->getSubObjects($type);
			foreach ($subs as $subtype => $data)
			{
				$recursivesubs[$subtype] = $data;
				if (! in_array($subtype, $done)
				&& ! in_array($subtype, $to_do))
				{
					$to_do[] = $subtype;
				}
			}
		}

		return $recursivesubs;
	}

	/**
	* get all subjects except (rolf) of the adm object
	* This is neceesary for filtering these objects in role perm view.
	* e.g It it not necessary to view/edit role permission for the usrf object since it's not possible to create a new one
	*
	* @param	string	object type
 	* @access	public
	* @return	array	list of object types to filter
	*/
	function getSubobjectsToFilter($a_obj_type = "adm")
	{
		foreach($this->obj_data[$a_obj_type]["subobjects"] as $key => $value)
		{
			switch($key)
			{
				case "rolf":
					// DO NOTHING
					break;

				default:
					$tmp_subs[] = $key;
			}
		}
		// ADD adm and root object
		$tmp_subs[] = "adm";
		$tmp_subs[] = "root";

		return $tmp_subs ? $tmp_subs : array();
	}
		
	/**
	* get only creatable subobjects by type
	*
	* @param	string	object type
 	* @access	public
	* @return	array	list of createable object types
	*/
	function getCreatableSubObjects($a_obj_type)
	{
		$subobjects = $this->getSubObjects($a_obj_type);

		// remove role folder object from list 
		unset($subobjects["rolf"]);
		
		$sub_types = array_keys($subobjects);

		// remove object types in development from list
		foreach ($sub_types as $type)
		{
			if ($this->getDevMode($type))
			{
				unset($subobjects[$type]);
			}
		}

		return $subobjects;
	}

	/**
	* get possible actions by type
	*
	* @param	string	object type
 	* @access	public
	*/
	function getActions($a_obj_name)
	{
		$ret = (is_array($this->obj_data[$a_obj_name]["actions"])) ?
			$this->obj_data[$a_obj_name]["actions"] :
			array();
		return $ret;
	}

	/**
	* get default property by type
	*
	* @param	string	object type
	* @access	public
	*/
	function getFirstProperty($a_obj_name)
	{
		if (defined("ILIAS_MODULE"))
		{
			foreach ($this->obj_data[$a_obj_name]["properties"] as $data => $prop)
			{
				if($prop["module"] != "n")
				{
					return $data;
				}
			}
		}
		else
		{
			foreach ($this->obj_data[$a_obj_name]["properties"] as $data => $prop)
			{
				if ($prop["module"] != 1)
				{
					return $data;
				}
			}
		}
	}

	/**
	* get name of property by type
	*
	* @param	string	object type
	* @access	public
	*/
	function getPropertyName($a_cmd, $a_obj_name)
	{
		return $this->obj_data[$a_obj_name]["properties"][$a_cmd]["lng"];
	}

	/**
	* get a string of all subobjects by type
	*
	* @param	string	object type
	* @access	public
	*/
	function getSubObjectsAsString($a_obj_type)
	{
		$string = "";

		if (is_array($this->obj_data[$a_obj_type]["subobjects"]))
		{
			$data = array_keys($this->obj_data[$a_obj_type]["subobjects"]);

			$string = "'".implode("','", $data)."'";
		}
		
		return $string;
	}

	/**
	* get all subobjects that may be imported
	*
	* @param	string	object type
	* @access	public
	*/
	function getImportObjects($a_obj_type)
	{
		$imp = array();

		if (is_array($this->obj_data[$a_obj_type]["subobjects"]))
		{
			foreach ($this->obj_data[$a_obj_type]["subobjects"] as $sub)
			{
				if ($sub["import"] == 1)
				{
					$imp[] = $sub["name"];
				}
			}
		}

		return $imp;
	}
	
	/**
	 * Check if object type is container ('crs','fold','grp' ...)
	 *
	 * @access public
	 * @param string object type
	 * @return bool
	 * 
	 */
	public function isContainer($a_obj_name)
	{
		if(!is_array($this->obj_data[$a_obj_name]['subobjects']))
		{
			return false;
		}
		return count($this->obj_data[$a_obj_name]['subobjects']) > 1 ? true : false;
	}

// PRIVATE METHODS

	/**
	* set event handler
	*
	* @param	ressouce	internal xml_parser_handler
	* @access	private
	*/
	function setHandlers($a_xml_parser)
	{
		xml_set_object($a_xml_parser,$this);
		xml_set_element_handler($a_xml_parser,'handlerBeginTag','handlerEndTag');
		xml_set_character_data_handler($a_xml_parser,'handlerCharacterData');
	}

	/**
	* start tag handler
	*
	* @param	ressouce	internal xml_parser_handler
	* @param	string		element tag name
	* @param	array		element attributes
	* @access	private
	*/
	function handlerBeginTag($a_xml_parser,$a_name,$a_attribs)
	{
		switch ($a_name)
		{
			case 'objects':
				$this->current_tag = '';
				break;
			case 'object':
				$this->parent_tag_name = $a_attribs["name"];
				$this->current_tag = '';
				$this->obj_data["$a_attribs[name]"]["name"] = $a_attribs["name"];
				$this->obj_data["$a_attribs[name]"]["class_name"] = $a_attribs["class_name"];
				$this->obj_data["$a_attribs[name]"]["location"] = $a_attribs["location"];
				$this->obj_data["$a_attribs[name]"]["checkbox"] = $a_attribs["checkbox"];
				$this->obj_data["$a_attribs[name]"]["inherit"] = $a_attribs["inherit"];
				$this->obj_data["$a_attribs[name]"]["module"] = $a_attribs["module"];
				$this->obj_data["$a_attribs[name]"]["translate"] = $a_attribs["translate"];
				$this->obj_data["$a_attribs[name]"]["devmode"] = $a_attribs["devmode"];
				$this->obj_data["$a_attribs[name]"]["allow_link"] = $a_attribs["allow_link"];
				$this->obj_data["$a_attribs[name]"]["allow_copy"] = $a_attribs["allow_copy"];
				$this->obj_data["$a_attribs[name]"]["rbac"] = $a_attribs["rbac"];
				$this->obj_data["$a_attribs[name]"]["system"] = $a_attribs["system"];
				$this->obj_data["$a_attribs[name]"]["sideblock"] = $a_attribs["sideblock"];
				break;
			case 'subobj':
				$this->current_tag = "subobj";
				$this->current_tag_name = $a_attribs["name"];
				$this->obj_data[$this->parent_tag_name]["subobjects"][$this->current_tag_name]["name"] = $a_attribs["name"];
				// NUMBER OF ALLOWED SUBOBJECTS (NULL means no limit)
				$this->obj_data[$this->parent_tag_name]["subobjects"][$this->current_tag_name]["max"] = $a_attribs["max"];
				// also allow import ("1" means yes)
				$this->obj_data[$this->parent_tag_name]["subobjects"][$this->current_tag_name]["import"] = $a_attribs["import"];
				$this->obj_data[$this->parent_tag_name]["subobjects"][$this->current_tag_name]["module"] = $a_attribs["module"];
				break;
			case 'property':
				$this->current_tag = "property";
				$this->current_tag_name = $a_attribs["name"];
				$this->obj_data[$this->parent_tag_name]["properties"][$this->current_tag_name]["name"] = $a_attribs["name"];
				$this->obj_data[$this->parent_tag_name]["properties"][$this->current_tag_name]["module"] = $a_attribs["module"];
				break;
			case 'action':
				$this->current_tag = "action";
				$this->current_tag_name = $a_attribs["name"];
				$this->obj_data[$this->parent_tag_name]["actions"][$this->current_tag_name]["name"] = $a_attribs["name"];
				break;
				
			case 'sorting':
				$this->current_tag = 'sorting';
				$this->obj_data[$this->parent_tag_name]['sorting']['modes'][] = $a_attribs['mode'];
				break;
		}
	}

	/**
	* end tag handler
	*
	* @param	ressouce	internal xml_parser_handler
	* @param	string		data
	* @access	private
	*/
	function handlerCharacterData($a_xml_parser,$a_data)
	{
		// DELETE WHITESPACES AND NEWLINES OF CHARACTER DATA
		$a_data = preg_replace("/\n/","",$a_data);
		$a_data = preg_replace("/\t+/","",$a_data);

		if (!empty($a_data))
		{
			switch ($this->current_tag)
			{
				case "subobj":
					$this->obj_data[$this->parent_tag_name]["subobjects"][$this->current_tag_name]["lng"] .= $a_data;
					break;
				case "action" :
					$this->obj_data[$this->parent_tag_name]["actions"][$this->current_tag_name]["lng"] .= $a_data;
					break;
				case "property" :
					$this->obj_data[$this->parent_tag_name]["properties"][$this->current_tag_name]["lng"] .= $a_data;
					break;
				default:
					break;
			}
		}
	}

	/**
	* end tag handler
	* 
	* @param	ressouce	internal xml_parser_handler
	* @param	string		element tag name
	* @access	private
	*/
	function handlerEndTag($a_xml_parser,$a_name)
	{
		$this->current_tag = '';
		$this->current_tag_name = '';
	}

	function __filterObjects(&$subobjects)
	{
		foreach($subobjects as $type => $data)
		{
			switch($type)
			{
				case "chat":
					if(!$this->ilias->getSetting("chat_active"))
					{
						unset($subobjects[$type]);
					}
					break;

				case "icrs":
					if(!$this->ilias->getSetting("ilinc_active"))
					{
						unset($subobjects[$type]);
					}
					break;					

				default:
					// DO NOTHING
			}
		}
	}
	
	/**
	* checks if object type is a system object
	* 
	* system objects are those object types that are only used for
	* internal purposes and to keep the object type model consistent.
	* Typically they are used in the administation, exist only once
	* and may contain only specific object types.
	* To mark an object type as a system object type, use 'system=1'
	* in the object definition in objects.xml
	*
	* @param	string	object type
	* @access	public
	*/
	function isSystemObject($a_obj_name)
	{
		return (bool) $this->obj_data[$a_obj_name]["system"];
	}
	
	/**
	* Check, whether object type is a side block.
	*
	* @param	string		object type
	* @return	boolean		side block true/false
	*/
	function isSideBlock($a_obj_name)
	{
		return (bool) $this->obj_data[$a_obj_name]["sideblock"];
	}

}
?>
