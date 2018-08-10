<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTermsOfServiceDocument
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceDocument extends ActiveRecord
{
	const TABLE_NAME = 'tos_documents';

	/**
	 * @var string
	 * @db_has_field        true
	 * @db_fieldtype        integer
	 * @db_length           4
	 * @db_is_primary       true
	 * @con_sequence        true
	 */
	protected $id;

	/**
	 * @var int
	 * @con_has_field   true
	 * @con_fieldtype   integer
	 * @con_length      4
	 */
	protected $creation_ts = 0;

	/**
	 * @var int
	 * @con_has_field   true
	 * @con_fieldtype   integer
	 * @con_length      4
	 */
	protected $modification_ts = 0;

	/**
	 * @var int
	 * @con_has_field   true
	 * @con_fieldtype   integer
	 * @con_length      4
	 */
	protected $owner_usr_id = 0;

	/**
	 * @var int
	 * @con_has_field   true
	 * @con_fieldtype   integer
	 * @con_length      4
	 */
	protected $last_modified_usr_id = '';

	/**
	 * @var int
	 * @con_has_field   true
	 * @con_fieldtype   integer
	 * @con_length      4
	 */
	protected $sorting = 0;

	/**
	 * @var string
	 * @db_has_field        true
	 * @db_fieldtype        text
	 * @db_length           255
	 */
	protected $title = '';

	/**
	 * @var string
	 * @db_has_field        true
	 * @db_fieldtype        clob
	 */
	protected $text = '';

	/**
	 * @var \ilTermsOfServiceDocumentCriterionAssignment[]
	 */
	protected $criteria = [];

	/**
	 * @var bool
	 */
	private $criteriaFetched = false;

	/**
	 * @inheritdoc
	 */
	static function returnDbTableName()
	{
		return self::TABLE_NAME;
	}

	/**
	 * @inheritdoc
	 */
	public function create()
	{
		$this->setCreationTs(time());

		parent::create();
	}

	/**
	 * @inheritdoc
	 */
	public function read()
	{
		$this->fetchAllCriterionAssignments();
	}

	/**
	 * @inheritdoc
	 */
	public function buildFromArray(array $array)
	{
		$document = parent::buildFromArray($array);

		$this->fetchAllCriterionAssignments();

		return $document;
	}


	/**
	 * @inheritdoc
	 */
	public function update()
	{
		$this->setModificationTs(time());

		/** @var $criterionAssignment ilTermsOfServiceDocumentCriterionAssignment */
		foreach ($this->criteria as $criterionAssignment) {
			$criterionAssignment->setDocId($this->getId());
			$criterionAssignment->store();
		}

		parent::update();
	}


	/**
	 * @inheritdoc
	 */
	public function delete()
	{
		/** @var $criterionAssignment \ilTermsOfServiceDocumentCriterionAssignment */
		foreach ($this->criteria as $criterionAssignment) {
			$criterionAssignment->delete();
		}

		parent::delete();
	}

	/**
	 * @return \ilTermsOfServiceDocumentCriterionAssignment[]
	 */
	public function getCriteria(): array
	{
		return $this->criteria;
	}

	/**
	 * @param \ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment
	 */
	public function attachCriterion(\ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment)
	{
		$this->criteria[] = $criterionAssignment;
	}

	/**
	 * Reads all criterion assignments from database
	 */
	public function fetchAllCriterionAssignments()
	{
		if (!$this->criteriaFetched) {
			$this->criteriaFetched = true;

			$this->criteria = [];

			$criteria = \ilTermsOfServiceDocumentCriterionAssignment::where(array('doc_id' => $this->getId()))->get();
			/** @var $criterionAssignment ilTermsOfServiceDocumentCriterionAssignment */
			foreach ($criteria as $criterionAssignment) {
				$this->criteria[$criterionAssignment->getId()] = $criterionAssignment;
			}
		}
	}
}