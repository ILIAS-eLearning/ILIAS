<?php

declare(strict_types = 1);

/**
* Class ilStudyProgrammeAutoMembershipSource
*
* @author: Nils Haagen <nils.haagen@concepts-and-training.de>
*/
class ilStudyProgrammeAutoMembershipSource
{
	const TYPE_ROLE = 'rol';
	const TYPE_GROUP = 'grp';
	const TYPE_COURSE = 'crs';
	const TYPE_ORGU = 'orgu';

	const SOURCE_MAPPING = [
		self::TYPE_ROLE => ilStudyProgrammeAssignment::AUTO_ASSIGNED_BY_ROLE,
		self::TYPE_GROUP => ilStudyProgrammeAssignment::AUTO_ASSIGNED_BY_GROUP,
		self::TYPE_COURSE => ilStudyProgrammeAssignment::AUTO_ASSIGNED_BY_COURSE,
		self::TYPE_ORGU => ilStudyProgrammeAssignment::AUTO_ASSIGNED_BY_ORGU
	];


	/**
	 * @var int
	 */
	protected $prg_obj_id;

	/**
	 * @var string 	one of the TYPE_-constants
	 */
	protected $source_type;

	/**
	 * @var int
	 */
	protected $source_id;

	/**
	 * @var bool
	 */
	protected $enabled;

	/**
	 * @var int
	 */
	protected $last_edited_usr_id;

	/**
	 * @var \DateTimeImmutable
	 */
	protected $last_edited;

	public function __construct(
		int $prg_obj_id,
		string $source_type,
		int $source_id,
		bool $enabled,
		int $last_edited_usr_id,
		\DateTimeImmutable $last_edited
	) {
		if(! in_array($source_type, [
			self::TYPE_ROLE,
			self::TYPE_GROUP,
			self::TYPE_COURSE,
			self::TYPE_ORGU
		])) {
			throw new \InvalidArgumentException("Invalid source-type: " .$source_type, 1);
		}

		$this->prg_obj_id = $prg_obj_id;
		$this->source_type = $source_type;
		$this->source_id = $source_id;
		$this->enabled = $enabled;
		$this->last_edited_usr_id = $last_edited_usr_id;
		$this->last_edited = $last_edited;
	}

	public function getPrgObjId(): int
	{
		return $this->prg_obj_id;
	}

	public function getSourceType(): string
	{
		return $this->source_type;
	}

	public function getSourceId(): int
	{
		return $this->source_id;
	}

	public function isEnabled(): bool
	{
		return $this->enabled;
	}

	public function getLastEditorId(): int
	{
		return $this->last_edited_usr_id;
	}

	public function getLastEdited(): \DateTimeImmutable
	{
		return $this->last_edited;
	}

}
