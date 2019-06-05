<?php

declare(strict_types = 1);

/**
 * Class ilStudyProgrammeAutoCategoryDBRepository
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 */
class ilStudyProgrammeAutoCategoryDBRepository implements ilStudyProgrammeAutoCategoryRepository
{
	const TABLE = 'prg_auto_content';
	const FIELD_PRG_OBJ_ID = 'prg_obj_id';
	const FIELD_CAT_REF_ID = 'cat_ref_id';
	const FIELD_TITLE = 'title';
	const FIELD_EDITOR_ID = 'last_usr_id';
	const FIELD_LAST_EDITED = 'last_edited';

	/**
	 * @var int
	 */
	protected $current_usr_id;

	public function __construct(
		ilDBInterface $db,
		int $current_usr_id
	) {

	}

	/**
	 * @inheritdoc
	 */
	public function readFor(int $prg_obj_id): array
	{
		$query = 'SELECT '
			.self::FIELD_PRG_OBJ_ID .','
			.self::FIELD_CAT_REF_ID .','
			.self::FIELD_TITLE .','
			.self::FIELD_EDITOR_ID .','
			.self::FIELD_LAST_EDITED
			.PHP_EOL.'FROM '.self::TABLE
			.PHP_EOL.'WHERE '.self::FIELD_PRG_OBJ_ID .' = '
			.$this->db->quote('integer', $prg_obj_id);

		$res = $this->db->query($q);
		$ret = [];
		while($rec = $this->db->fetchAssoc($res)) {
			$ret[] = $this->create(
				$rec[self::FIELD_PRG_OBJ_ID],
				$rec[self::FIELD_CAT_REF_ID],
				$rec[self::FIELD_TITLE],
				$rec[self::FIELD_EDITOR_ID],
				$rec[self::FIELD_LAST_EDITED]
			);
		}
		return $ret;
	}

	protected function create(
		int $prg_obj_id,
		int $category_ref_id,
		string $title,
		int $last_edited_usr_id,
		\DateTimeImmutable $last_edited
	): ilStudyProgrammeAutoCategory	{
		return new ilStudyProgrammeAutoCategory(
			$prg_obj_id,
			$category_ref_id,
			$title,
			$last_edited_usr_id,
			$last_edited
		);
	}

	/**
	 * @inheritdoc
	 */
	public function update(ilStudyProgrammeAutoCategory $ac)
	{
		$ilAtomQuery = $ilDB->buildAtomQuery();
		$ilAtomQuery->addTableLock(self::TABLE);

		$current_usr_id = $this->current_usr_id;
		$ilAtomQuery->addQueryCallable(
			function(ilDBInterface $db) use ($ac, $current_usr_id)  {
				$query = 'DELETE FROM ' .self::TABLE
					.PHP_EOL.'WHERE prg_obj_id = ' .$ac->getObjId()
					.PHP_EOL.'AND cat_ref_id = ' .$ac->getCategoryRefId();
				$db->query($query);

				$now = new \DateTimeImmutable();
				$db->insert(
					self::TABLE,
					[
						self::FIELD_PRG_OBJ_ID => ['integer', $ac->getObjId()],
						self::FIELD_CAT_REF_ID => ['integer', $ac->getCategoryRefId()],
						self::FIELD_TITLE => ['text', $ac->getTitle()],
						self::FIELD_EDITOR_ID => ['integer', $current_usr_id],
						self::FIELD_LAST_EDITED => ['timestamp', $now]
					]
				);
			}
		);
		$ilAtomQuery->run();

	}

	/**
	 * @inheritdoc
	 */
	public function delete(int $prg_obj_id, int $cat_ref_id)
	{

	}

	/**
	 * @inheritdoc
	 */
	public function deleteFor(int $prg_obj_id)
	{

	}

}
