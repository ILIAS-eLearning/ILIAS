<?php declare(strict_types = 1);

/**
 * Class ilStudyProgrammeAdvancedMetadataRecord
 *
 * @author Michael Herren <mh@studer-raimann.ch>
 * @version 1.0.0
 */
class ilStudyProgrammeAdvancedMetadataRecord
{

	/**
	 *
	 * @var int
	 *
	 * @con_is_primary  true
	 * @con_sequence    true
	 * @con_is_unique   true
	 * @con_has_field   true
	 * @con_fieldtype   integer
	 * @con_length      4
	 */
	protected $id;

	/**
	 *
	 * @var int
	 *
	 * @con_has_field   true
	 * @con_fieldtype   integer
	 * @con_length      4
	 */
	protected $type_id = 0;

	/**
	 *
	 * @var int
	 *
	 * @con_has_field   true
	 * @con_fieldtype   integer
	 * @con_length      4
	 */
	protected $rec_id = 0;


	public function __construct(int $id)
	{
		$this->id = $id;
	}

	/**
	 * @return int
	 */
	public function getId() : int
	{
		return $this->id;
	}


	/**
	 * @param int $id
	 */
	public function setId(int $id)
	{
		$this->id = $id;
	}


	/**
	 * @return int
	 */
	public function getTypeId() : int
	{
		return $this->type_id;
	}


	/**
	 * @param int $type_id
	 */
	public function setTypeId(int $type_id = null)
	{
		$this->type_id = $type_id;
	}


	/**
	 * @return int
	 */
	public function getRecId() : int
	{
		return $this->rec_id;
	}


	/**
	 * @param int $rec_id
	 */
	public function setRecId(int $rec_id = null)
	{
		$this->rec_id = $rec_id;
	}

}