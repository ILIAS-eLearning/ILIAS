<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class ilDataCollectionFieldProp
*
* @author Martin Studer <ms@studer-raimann.ch>
* @author Marcel Raimann <mr@studer-raimann.ch>
* @author Fabian Schmid <fs@studer-raimann.ch>
 * @author Oskar Truffer <ot@studer-raimann.ch>
* @version $Id: 
*
* @ingroup ModulesDataCollection
*/
class ilDataCollectionFieldProp
{
	protected $id; // [int]
	protected $datatype_property_id; //[int]
	protected $value; //[string]
	protected $field_id; // [int]


	/**
	 * Constructor
	 *
	 * @param  int datatype_id
	 *
	 */
	public function __construct($a_id = 0)
	{
	if ($a_id != 0) 
	{
		$this->id = $a_id;
		$this->doRead();
	}
	}

	/**
	 * Set id
	 *
	 * @param int $a_id
	 */
	public function setId($a_id)
	{
		$this->id = $a_id;
	}

	/**
	 * Get id
	 *
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * Set property id
	 *
	 * @param int $a_id
	 */
	public function setDatatypePropertyId($a_id)
	{
		$this->datatype_property_id = $a_id;
	}

	/**
	 * Get property id
	 *
	 * @return int
	 */
	public function getDatatypePropertyId()
	{
		return $this->datatype_property_id;
	}

	/**
	 * Set value
	 *
	 * @param string $a_value
	 */
	public function setValue($a_value)
	{
		$this->value = $a_value;
	}

	/**
	 * Get value
	 *
	 * @return string
	 */
	public function getValue()
	{
		return $this->value;
	}

	/**
	 * Set field id
	 *
	 * @param int $a_id
	 */
	public function setFieldId($a_id)
	{
		$this->field_id = $a_id;
	}

	/**
	 * Get field id
	 *
	 * @return int
	 */
	public function getFieldId()
	{
		return $this->field_id;
	}


	/**
	 * Read Datatype
	 */
	public function doRead()
	{
		global $ilDB;

		$query = "SELECT * FROM il_dcl_field_prop WHERE id = ".$ilDB->quote($this->getId(),"integer");
		$set = $ilDB->query($query);
		$rec = $ilDB->fetchAssoc($set);

		$this->setDatatypePropertyId($rec["datatype_prop_id"]);
		$this->setValue($rec["value"]);
		$this->setFieldId($rec["field_id"]);
		
	}


	/**
	 * Create new field property
	 */
	public function doCreate()
	{
		global $ilDB;

		$id = $ilDB->nextId("il_dcl_field_prop");
		$this->setId($id);
		$query = "INSERT INTO il_dcl_field_prop (".
			"id".
			", datatype_prop_id".
			", field_id".
			", value".
			" ) VALUES (".
			$ilDB->quote($this->getId(), "integer")
			.",".$ilDB->quote($this->getDatatypePropertyId(), "integer")
			.",".$ilDB->quote($this->getFieldId(), "integer")
			.",".$ilDB->quote($this->getValue(), "text")
			.")";
		$ilDB->manipulate($query);
	}


	/**
	 * Update field property
	 */
	public function doUpdate()
	{
		global $ilDB;
        /** @var ilDB $ilDB */

        $sql = "SELECT * FROM il_dcl_field_prop WHERE datatype_prop_id = " . $ilDB->quote($this->getDatatypePropertyId(), 'integer') .
               " AND field_id = " . $ilDB->quote($this->getFieldId(), 'integer');
        $set = $ilDB->query($sql);
        if (!$ilDB->numRows($set)) {
            $this->doCreate();
            return;
        }

		$ilDB->update("il_dcl_field_prop", array(
				"datatype_prop_id" => array("integer", $this->getDatatypePropertyId()),
				"field_id" => array("integer", $this->getFieldId()),
				"value" => array("text", $this->getValue())
			), array(
				"datatype_prop_id" => array("integer", $this->getDatatypePropertyId()),
				"field_id" => array("integer", $this->getFieldId())
			));
	}
}


?>