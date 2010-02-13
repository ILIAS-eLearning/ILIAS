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
class ilDataSet
{
	/**
	 * Constructor
	 * 
	 * @param	string		version string, always the ILIAS release
	 * 						versions that defined the a structure
	 * 						or made changes to it, never use another
	 * 						version. Example: structure is defined
	 * 						in 4.1.0 and changed in 4.3.0 -> use these
	 * 						values only, not 4.2.0 (ask for the 4.1.0
	 * 						version in ILIAS 4.2.0)
	 */
	function __construct($a_version)
	{
		$this->version = false;
		if (in_array($a_version, $this->getSupportedVersions()))
		{
			$this->version = $a_version;
		}
	}
	
	/**
	 * Get supported version
	 * 
	 * @return	array		array of supported version
	 */
	abstract function getSupportedVersions();
	
	/**
	 * Get (abstract) entity name
	 *
	 * @return	string		entity name
	 */
	abstract function getEntityName();
	
	/**
	 * Get (abstract) types for (abstract) field names.
	 * Please note that the abstract fields/types only depend on
	 * the version! Not on a choosen representation!
	 * 
	 * @return	array		types array, e.g.
	 * array("field_1" => "text", "field_2" => "integer", ...) 
	 */
	abstract function getTypes();
	
	/**
	 * Read data from DB. This should result in the
	 * abstract field structure of the version set in the constructor.
	 * 
	 * @param	array	where clause array (flexible)
	 */
	abstract function readData($a_where);
	
	/**
	 * Get version
	 *
	 * @param
	 * @return
	 */
	function getVersion()
	{
		return $this->version;
	}
	
	/**
	 * Get XML representation
	 */
	final function getXMLRepresentation()
	{
		if ($this->version === false)
		{
			return false;
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
		$arr["version"] = $this->getVersion();
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
	 */
	final function getXmlRepresentation()
	{
		if ($this->version === false)
		{
			return false;
		}
		
		$arr["entity"] = $this->getXmlEntityName();
		$arr["version"] = $this->getVersion();
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
		return $this->getEntityName();
	}
	
	/**
	 * Get entity name for json
	 * (may be overwritten)
	 */
	function getJsonEntityName()
	{
		return $this->getEntityName();
	}
}

/*

DB

table_name
field_1(text) => content,
field_2(date) => my_date,
field_3(integer) => my_number

Array

$set = array(
			"entity" => "table_name",
			"version" => "4.0.1",
			"install_id" => "1234",
			"install_url" => "http://www.ilias.unibw-hamburg.de",
			"types" => array(
			"field_1" => "text",
			"field_2" => "date",
			"field_3" => "integer"
			),
			"set" => array(
				array("field_1" => "content",
					"field_2" => "my_date",
					"field_3" => "my_number"),
				...
				)
			);

XML

<entity_set name="table_name" version="4.0.1">
	<types>
		<ftype name="field_1" type="text" />
		<ftype name="field_2" type="date" />
		<ftype name="field_3" type="integer" />
	</types>
	<set>
		<rec entity="table_name">
			<field name="field_1">content</field>
			<field name="field_2">my_date</field>
			<field name="field_3">my_number</field>
		</rec>
		...
	</set>
</entity_set>

JSON

{ name: 'table_name',
  types: {field_1: 'text', field_2: 'date', field_3: 'integer'},
  set: {
}

*/
?>
