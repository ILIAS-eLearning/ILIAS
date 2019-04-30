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
	 */
	protected $id;

	/**
	 *
	 * @var int
	 */
	protected $type_id = 0;

	/**
	 *
	 * @var int
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