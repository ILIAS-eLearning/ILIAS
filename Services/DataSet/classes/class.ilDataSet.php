<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* A dataset contains in data in a common structure that can be
* shared and transformed for different purposes easily, examples
* - transform associative arrays into (set-)xml and back (e.g. for import/export)
* - transform assiciative arrays into json and back (e.g. for ajax requests)
*
* The general structure is:
* - entity name (many times this corresponds to a table name)
* - structure (this is a set of field names and types pairs)
*   currently supported types: text, integer, timestamp
*   planned: date, time, clob
*   types correspond to db types, see
*   http://www.ilias.de/docu/goto.php?target=pg_25354_42&client_id=docu
* - records (similar to records of a database query; associative arrays)
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
* @ingroup 
*/
abstract class ilDataSet
{
	/**
	 * Constructor
	 */
	function __construct()
	{
	}
	
	/**
	 * Init
	 *
	 * @param	string		(abstract) entity name 
	 * @param	string		version string, always the ILIAS release
	 * 						versions that defined the a structure
	 * 						or made changes to it, never use another
	 * 						version. Example: structure is defined
	 * 						in 4.1.0 and changed in 4.3.0 -> use these
	 * 						values only, not 4.2.0 (ask for the 4.1.0
	 * 						version in ILIAS 4.2.0)
	 */
	final public function init($a_entity, $a_version)
	{
		$this->entity = $a_entity;
		$this->version = $a_version;
		$this->data = array();
	}
	
	/**
	 * Get supported version
	 * 
	 * @return	array		array of supported version
	 */
	abstract public function getSupportedVersions($a_entity);
		
	/**
	 * Get (abstract) types for (abstract) field names.
	 * Please note that the abstract fields/types only depend on
	 * the version! Not on a choosen representation!
	 * 
	 * @return	array		types array, e.g.
	 * array("field_1" => "text", "field_2" => "integer", ...) 
	 */
	abstract protected function getTypes();
	
	/**
	 * Read data from DB. This should result in the
	 * abstract field structure of the version set in the constructor.
	 * 
	 * @param	array	where clause array (flexible)
	 */
	abstract function readData($a_where);
	
	/**
	 * Get data from query.This is a standard procedure,
	 * all db field names are directly mapped to abstract fields.
	 * 
	 * @param
	 * @return
	 */
	function getDirectDataFromQuery($a_query)
	{
		global $ilDB;
		
		$set = $ilDB->query($a_query);
		$this->data = array();
		while ($rec  = $ilDB->fetchAssoc($set))
		{
			$this->data[] = $rec;
		}
	}
			
	/**
	 * Get json representation
	 */
	final function getJsonRepresentation()
	{
		if ($this->version === false)
		{
			return false;
		}
		
		$arr["entity"] = $this->getJsonEntityName();
		$arr["version"] = $this->version;
		$arr["install_id"] = IL_INST_ID;
		$arr["install_url"] = ILIAS_HTTP_PATH;
		$arr["types"] = $this->getJsonTypes();
		$arr["set"] = array();
		foreach ($this->data as $d)
		{
			$arr["set"][] = $this->getJsonRecord($d);
		}
		
		include_once("./Services/JSON/classes/class.ilJsonUtil.php");

		return ilJsonUtil::enocde($arr);
	}

	/**
	 * Get xml representation
	 * 	<entity_set name="table_name" version="4.0.1" install_id="123" >
	 * 	<types>
	 *		<ftype name="field_1" type="text" />
	 *		<ftype name="field_2" type="date" />
	 *		<ftype name="field_3" type="integer" />
	 *	</types>
	 *	<set>
	 *		<rec>
	 *			<field name="field_1">content</field>
	 *			<field name="field_2">my_date</field>
	 *			<field name="field_3">my_number</field>
	 *		</rec>
	 *		...
	 *	</set>
	 *  </entity_set>
	 */
	final function getXmlRepresentation()
	{
		if ($this->version === false)
		{
			return false;
		}
		
		include_once "./Services/Xml/classes/class.ilXmlWriter.php";
		$writer = new ilXmlWriter();
		$writer->xmlStartTag('entity_set',
			array("name" => $this->getXmlEntityName(),
			"version" => $this->version,
			"install_id" => IL_INST_ID,
			"install_url" => ILIAS_HTTP_PATH));
			
		$writer->xmlStartTag("types");
		foreach ($this->getXmlTypes() as $f => $t)
		{
			$writer->xmlElement('ftype',
				array("name" => $f, "type" => $t));
		}
		$writer->xmlEndTag("types");
		
		$writer->xmlStartTag("set");
		foreach ($this->data as $d)
		{
			$writer->xmlStartTag("rec");
			foreach ($this->getXmlRecord($d) as $f => $c)
			{
				// alternatice: generic element
				//$writer->xmlElement('field',
				//	array("name" => $f), $c);
				
				// this changes schema/dtd
				$writer->xmlElement($f,
					array(), $c);
			}
			$writer->xmlEndTag("rec");
		}
		$writer->xmlEndTag("set");
		$writer->xmlEndTag("entity_set");
		
		return $writer->xmlDumpMem(false);
	}
	
	
	/**
	 * Get xml record for version
	 *
	 * @param	array	abstract data record
	 * @return	array	xml record
	 */
	function getXmlRecord($a_set)
	{
		return $a_set;		
	}
	
	/**
	 * Get json record for version
	 *
	 * @param	array	abstract data record
	 * @return	array	json record
	 */
	function getJsonRecord($a_set)
	{
		return $a_set;		
	}
	
	/**
	 * Get xml types
	 *
	 * @return	array	types array for xml/version set in constructor
	 */
	function getXmlTypes()
	{
		return $this->getTypes();
	}
	
	/**
	 * Get json types
	 *
	 * @return	array	types array for json/version set in constructor
	 */
	function getJsonTypes()
	{
		return $this->getTypes();
	}
	
	/**
	 * Get entity name for xml
	 * (may be overwritten)
	 * 
	 * @return	string		
	 */
	function getXMLEntityName()
	{
		return $this->entity;
	}
	
	/**
	 * Get entity name for json
	 * (may be overwritten)
	 */
	function getJsonEntityName()
	{
		return $this->entity;
	}
}

?>