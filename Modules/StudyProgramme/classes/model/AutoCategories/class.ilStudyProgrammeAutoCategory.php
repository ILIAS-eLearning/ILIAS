<?php

declare(strict_types = 1);

/**
 * Class ilStudyProgrammeAutoCategory
 *
 * @author: Nils Haagen <nils.haagen@concepts-and-training.de>
 */
class ilStudyProgrammeAutoCategory
{
	/**
	 * @var int
	 */
	protected $prg_obj_id;

	/**
	 * @var int
	 */
	protected $category_ref_id;

	/**
	 * @var string
	 */
	protected $title;

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
		int $category_ref_id,
		int $last_edited_usr_id,
		\DateTimeImmutable $last_edited
	) {
		$this->prg_obj_id = $prg_obj_id;
		$this->category_ref_id = $category_ref_id;

		$this->title = $title;
		$this->last_edited_usr_id = $last_edited_usr_id;
		$this->last_edited = $last_edited;
	}

	public function getObjId(): int
	{
		return $this->prg_obj_id;
	}

	public function getCategoryRefId(): int
	{
		return $this->category_ref_id;
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
