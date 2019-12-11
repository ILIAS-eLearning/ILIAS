<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Component definition reader (reads common tags in module.xml and service.xml files)
* Name is misleading and should be ilComponentDefReader instead.
*
* Reads reads module information of modules.xml files into db
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
*/
class ilObjDefReader extends ilSaxParser
{
	protected $component_id;
	
	protected $readers = array(
		"copage" => array("class" => "ilCOPageDefReader")
		);
	protected $current_reader = null;
	
	protected $in_mail_templates = false;

	/**
	 * @var \ilDBInterface|null
	 */
	protected $db;

	/**
	 * @var array
	 */
	protected $mail_templates_by_component = array();

	function __construct($a_path, $a_name, $a_type, \ilDBInterface $db = null)
	{
		if ($db !== null) {
			$this->db = $db;
		}
		else {
			global $ilDB;
			$this->db = $ilDB;
		}
		// init specialized readers
		foreach ($this->readers as $k => $reader)
		{
			$class = $reader["class"];
			$this->readers[$k]["reader"] = new $class($this->db);
		}
		
		$this->name = $a_name;
		$this->type = $a_type;

		parent::__construct($a_path);
	}
	
	function setHandlers($a_xml_parser)
	{
		xml_set_object($a_xml_parser,$this);
		xml_set_element_handler($a_xml_parser,'handlerBeginTag','handlerEndTag');
		xml_set_character_data_handler($a_xml_parser,'handlerCharacterData');
	}

	/**
	* clear the tables
	*/
	function clearTables()
	{
		$this->db->manipulate("DELETE FROM il_object_def");
		
		$this->db->manipulate("DELETE FROM il_object_subobj");
		
		$this->db->manipulate("DELETE FROM il_object_group");

		$this->db->manipulate("DELETE FROM il_pluginslot");
		
		$this->db->manipulate("DELETE FROM il_component");

		// Keep the plugin listeners in the table
		// This avoids reading them in the setup
		// ilPluginReader is called in the plugin administration
		$this->db->manipulate("DELETE FROM il_event_handling WHERE component NOT LIKE 'Plugins/%'");
		
		$this->db->manipulate("DELETE FROM il_object_sub_type");
		
		foreach ($this->readers as $k => $reader)
		{
			$this->readers[$k]["reader"]->clearTables();
		}
	}

	/**
	* Delete an object definition (this is currently needed for test cases)
	*/
	static function deleteObjectDefinition($a_id)
	{
		$this->db->manipulateF("DELETE FROM il_object_def WHERE id = %s",
			array("text"), array($a_id));
		
		$this->db->manipulateF("DELETE FROM il_object_subobj WHERE parent = %s OR subobj = %s",
			array("text", "text"), array($a_id, $a_id));
	}
	
	/**
	 * Start tag handler
	 *
	 * @param ressouce internal xml_parser_handler
	 * @param string element tag name
	 * @param array element attributes
	 */
	function handlerBeginTag($a_xml_parser,$a_name,$a_attribs)
	{
		$this->current_tag = $a_name;
		
		// check if a special reader needs to be activated
		if (isset($this->readers[$a_name]))
		{
			$this->current_reader = $a_name;
		}
		
		// call special reader
		if ($this->current_reader != "")
		{
			$this->readers[$this->current_reader]["reader"]->handlerBeginTag($a_xml_parser,$a_name,$a_attribs,
				$this->current_component);
		}
		else
		{
			// default handling
			switch ($a_name)
			{
				case 'object':
	
					// if attributes are not given, set default (repository only)
					if(($a_attribs["repository"] ?? null) === NULL)
					{
						$a_attribs["repository"] = true;
					}
					if(($a_attribs["workspace"] ?? null) === NULL)
					{
						$a_attribs["workspace"] = false;
					}
	
					$this->current_object = $a_attribs["id"];
					$this->db->manipulateF("INSERT INTO il_object_def (id, class_name, component,location,".
						"checkbox,inherit,translate,devmode,allow_link,allow_copy,rbac,default_pos,".
						"default_pres_pos,sideblock,grp,system,export,repository,workspace,administration,".
						"amet,orgunit_permissions,lti_provider,offline_handling) VALUES ".
						"(%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s)",
						array("text", "text", "text", "text", "integer", "integer", "text", "integer","integer","integer",
							"integer","integer","integer","integer", "text", "integer", "integer", "integer", "integer",
							'integer','integer','integer','integer','integer'),
						array(
							$a_attribs["id"],
							$a_attribs["class_name"],
							$this->current_component,
							$this->current_component."/".$a_attribs["dir"],
							(int) ($a_attribs["checkbox"] ?? null),
							(int) ($a_attribs["inherit"] ?? null),
							$a_attribs["translate"] ?? null,
							(int) ($a_attribs["devmode"] ?? null),
							(int) ($a_attribs["allow_link"] ?? null),
							(int) ($a_attribs["allow_copy"] ?? null),
							(int) ($a_attribs["rbac"] ?? null),
							(int) ($a_attribs["default_pos"] ?? null),
							(int) ($a_attribs["default_pres_pos"] ?? null),
							(int) ($a_attribs["sideblock"] ?? null),
							$a_attribs["group"] ?? null,
							(int) ($a_attribs["system"] ?? null),
							(int) ($a_attribs["export"] ?? null),
							(int) ($a_attribs["repository"] ?? null),
							(int) ($a_attribs["workspace"] ?? null),
							(int) ($a_attribs['administration'] ?? null),
							(int) ($a_attribs['amet'] ?? null),
							(int) ($a_attribs['orgunit_permissions'] ?? null),
							(int) ($a_attribs['lti_provider'] ?? null),
							(int) ($a_attribs['offline_handling'] ?? null)
						));
					break;
				
				case "subobj":
					$this->db->manipulateF("INSERT INTO il_object_subobj (parent, subobj, mmax) VALUES (%s,%s,%s)",
						array("text", "text", "integer"),
						array($this->current_object, $a_attribs["id"], (int) ($a_attribs["max"] ?? null)));
					break;
	
				case "parent":
					$this->db->manipulateF("INSERT INTO il_object_subobj (parent, subobj, mmax) VALUES (%s,%s,%s)",
						array("text", "text", "integer"),
						array($a_attribs["id"], $this->current_object, (int) ($a_attribs["max"] ?? null)));
					break;
	
				case "objectgroup":
					$this->db->manipulateF("INSERT INTO il_object_group (id, name, default_pres_pos) VALUES (%s,%s,%s)",
						array("text", "text", "integer"),
						array($a_attribs["id"], $a_attribs["name"], $a_attribs["default_pres_pos"]));
					break;
	
				case "pluginslot":
					$this->current_object = $a_attribs["id"];
					$q = "INSERT INTO il_pluginslot (component, id, name) VALUES (".
						$this->db->quote($this->current_component, "text").",".
						$this->db->quote($a_attribs["id"], "text").",".
						$this->db->quote($a_attribs["name"], "text").")";
					$this->db->manipulate($q);
					break;
				
				case "event":
					$component = $a_attribs["component"] ?? null;
					if(!$component)
					{
						$component = $this->current_component;
					}				
					$q = "INSERT INTO il_event_handling (component, type, id) VALUES (".
						$this->db->quote($component, "text").",".
						$this->db->quote($a_attribs["type"], "text").",".
						$this->db->quote($a_attribs["id"], "text").")";
					$this->db->manipulate($q);
					break;
					
				case "cron":				
					$component = $a_attribs["component"] ?? null;
					if(!$component)
					{
						$component = $this->current_component;
					}				
					ilCronManager::updateFromXML($component, $a_attribs["id"], $a_attribs["class"], ($a_attribs["path"] ?? null));		
					$this->has_cron[$component][] = $a_attribs["id"];
					break;
	
				case 'mailtemplates':
					$this->in_mail_templates = true;
					break;

				case 'context':
					if(!$this->in_mail_templates)
					{
						break;
					}

					$component = $a_attribs['component'] ?? null;
					if(!$component)
					{
						$component = $this->current_component;
					}

					ilMailTemplateContextService::insertFromXML(
						$component,
						$a_attribs['id'],
						$a_attribs['class'],
						$a_attribs['path'] ?? null
					);
					$this->mail_templates_by_component[$component][] = $a_attribs["id"];
					break;
	
				case "sub_type":
					$this->db->manipulate("INSERT INTO il_object_sub_type ".
						"(obj_type, sub_type, amet) VALUES (".
						$this->db->quote($this->current_object, "text").",".
						$this->db->quote($a_attribs["id"], "text").",".
						$this->db->quote($a_attribs["amet"], "integer").
						")");
					break;

				case 'systemcheck':
					
					include_once './Services/SystemCheck/classes/class.ilSCGroups.php';
					ilSCGroups::getInstance()->updateFromComponentDefinition($this->getComponentId());
					break;
				
				case 'systemcheck_task':
					include_once './Services/SystemCheck/classes/class.ilSCGroups.php';
					$group_id = ilSCGroups::getInstance()->lookupGroupByComponentId($this->getComponentId());
					
					include_once './Services/SystemCheck/classes/class.ilSCTasks.php';
					$tasks = ilSCTasks::getInstanceByGroupId($group_id);
					$tasks->updateFromComponentDefinition($a_attribs['identifier']);
					break;

				case "secure_path":
					require_once('./Services/WebAccessChecker/classes/class.ilWACSecurePath.php');
					try {
						$ilWACSecurePath = ilWACSecurePath::findOrFail($a_attribs["path"]);
					} catch (arException $e) {
						$ilWACSecurePath = new ilWACSecurePath();
						$ilWACSecurePath->setPath($a_attribs["path"]);
						$ilWACSecurePath->create();
					}
					$ilWACSecurePath->setCheckingClass($a_attribs["checking-class"]);
					$ilWACSecurePath->setInSecFolder((bool)($a_attribs["in-sec-folder"] ?? null));
					$ilWACSecurePath->setComponentDirectory(dirname($this->xml_file));
					$ilWACSecurePath->update();
					break;
				
				case 'logging':
					include_once './Services/Logging/classes/class.ilLogComponentLevels.php';
					ilLogComponentLevels::updateFromXML($this->getComponentId());					
					break;
				
				case 'badges':
					include_once "Services/Badge/classes/class.ilBadgeHandler.php";
					ilBadgeHandler::updateFromXML($this->getComponentId());
					$this->has_badges[] = $this->getComponentId();
					break;

				case 'pdfpurpose':
					require_once './Services/PDFGeneration/classes/class.ilPDFCompInstaller.php';
					ilPDFCompInstaller::updateFromXML($this->current_component, $a_attribs['name'], $a_attribs['preferred']);
					break;
			}
		}
	}
			
	/**
	 * End tag handler
	 * 
	 * @param object internal xml_parser_handler
	 * @param string element tag name
	 */
	function handlerEndTag($a_xml_parser,$a_name)
	{
		// call special reader
		if ($this->current_reader != "")
		{
			$this->readers[$this->current_reader]["reader"]->handlerEndTag($a_xml_parser,$a_name);
		}
		else
		{
			if($a_name == "module" || $a_name == "service")
			{
				ilCronManager::clearFromXML($this->current_component, 
					($this->has_cron[$this->current_component] ?? []));

				ilMailTemplateContextService::clearFromXml($this->current_component, ($this->mail_templates_by_component[$this->current_component] ?? []));
				
				if(!in_array($this->getComponentId(), ($this->has_badges ?? [])))
				{
					include_once "Services/Badge/classes/class.ilBadgeHandler.php";
					ilBadgeHandler::clearFromXml($this->getComponentId());
				}
			}
		}
		
		// check if a special reader needs to be activated
		if (isset($this->readers[$a_name]))
		{
			$this->current_reader = null;
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
				case '':
			}
		}
	}
	
	/**
	 * Set from module or service reader
	 */
	public function setComponentId($a_component_id)
	{
		$this->component_id = $a_component_id;
	}
	
	/**
	 * Get component id
	 * @return type
	 */
	public function getComponentId()
	{
		return $this->component_id;
	}
}
