<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTermsOfServiceDocument
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceDocument extends ActiveRecord implements \ilTermsOfServiceSignableDocument
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
	 * @var \ilTermsOfServiceDocumentCriterionAssignment[]
	 */
	protected $initialCriteria = [];

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
	public function getText(): string
	{
		return $this->text;
	}

	/**
	 * @inheritdoc
	 */
	public function getTitle(): string
	{
		return $this->title;
	}

	/**
	 * @inheritdoc
	 */
	public function getId(): int
	{
		return $this->id;
	}

	/**
	 * @inheritdoc
	 */
	public function create()
	{
		$this->setCreationTs(time());

		parent::create();

		// Not saved on creation, not supported by workflow
		$this->initialCriteria = $this->criteria;
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

		foreach ($this->criteria as $criterionAssignment) {
			/** @var $criterionAssignment \ilTermsOfServiceDocumentCriterionAssignment */
			$criterionAssignment->setDocId($this->getId());
			$criterionAssignment->store();
		}

		foreach ($this->initialCriteria as $key => $criterionAssignment) {
			/** @var $criterionAssignment \ilTermsOfServiceDocumentCriterionAssignment */
			$found = array_filter($this->criteria, function(\ilTermsOfServiceDocumentCriterionAssignment $criterionToMatch) use ($criterionAssignment) {
				return $criterionToMatch->getId() == $criterionAssignment->getId();
			});

			if (0 === count($found)) {
				$criterionAssignment->delete();
			}
		}

		$this->initialCriteria = $this->criteria;

		parent::update();
	}


	/**
	 * @inheritdoc
	 */
	public function delete()
	{
		foreach ($this->criteria as $criterionAssignment) {
			/** @var $criterionAssignment \ilTermsOfServiceDocumentCriterionAssignment */
			$criterionAssignment->delete();
		}

		parent::delete();
	}

	/**
	 * @inheritdoc
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
	 * @param \ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment
	 */
	public function detachCriterion(\ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment)
	{
		if (isset($this->criteria[$criterionAssignment->getId()])) {
			unset($this->criteria[$criterionAssignment->getId()]);
		}
	}

	/**
	 * Reads all criterion assignments from database
	 */
	public function fetchAllCriterionAssignments()
	{
		if (!$this->criteriaFetched) {
			$this->criteriaFetched = true;

			$this->initialCriteria = [];
			$this->criteria = [];

			$criteria = \ilTermsOfServiceDocumentCriterionAssignment::where(array('doc_id' => $this->getId()))->get();
			foreach ($criteria as $criterionAssignment) {
				/** @var $criterionAssignment \ilTermsOfServiceDocumentCriterionAssignment */
				$this->criteria[$criterionAssignment->getId()] = $criterionAssignment;
			}

			$this->initialCriteria = $this->criteria;
		}
	}
}