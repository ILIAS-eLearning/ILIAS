<?php

declare(strict_types=1);

/**
 * Storage for ilLSPostConditions
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 */
class ilLSPostConditionDB
{
	const TABLE_NAME = 'post_conditions';

	/**
	 * @var ilDBInterface
	 */
	protected $db;

	public function __construct(\ilDBInterface $db)
	{
		$this->db = $db;
	}

	/**
	 * @return ilLSPostCondition[]
	 */
	public function select(array $ref_ids): array
	{
		if(count($ref_ids) === 0) {
			return [];
		}

		$data = [];
		$query = "SELECT ref_id, condition_type, value" .PHP_EOL
			."FROM ".static::TABLE_NAME .PHP_EOL
			."WHERE ref_id IN ("
			.implode(',', $ref_ids)
			.")";

		$result = $this->db->query($query);
		while ($row = $this->db->fetchAssoc($result)) {
			$data[$row['ref_id']] = [(int)$row['condition_type'], (int)$row['value']];
		}

		$conditions = [];
		foreach ($ref_ids as $ref_id) {
			//always-condition, standard
			$type = LSPostConditionTypesDB::TYPE_ALWAYS;
			$value = null;

			//if from db: proper values
			if(array_key_exists($ref_id, $data)) {
				list($type, $value) = $data[$ref_id];
			}
			$conditions[] = new \ilLSPostCondition($ref_id, $type, $value);
		}
		return $conditions;
	}

	public function delete(array $ref_ids)
	{
		if(count($ref_ids) === 0) {
			return;
		}

		$query = "DELETE FROM ".static::TABLE_NAME .PHP_EOL
			."WHERE ref_id IN ("
			.implode(',', $ref_ids)
			.")";
		$this->db->manipulate($query);
	}

	protected function insert(array $ls_post_conditions)
	{
		foreach ($ls_post_conditions as $condition) {
			$values = array(
				"ref_id" => array("integer", $condition->getRefId()),
				"condition_type" => array("integer", $condition->getConditionType())
			);
			$this->db->insert(static::TABLE_NAME, $values);
		}
	}

	/**
	 * @param ilLSPostCondition[]
	 */
	public function upsert(array $ls_post_conditions)
	{
		if(count($ls_post_conditions) === 0) {
			return;
		}

		$ref_ids = array_map(
			function($condition) {
				return (int)$condition->getRefId();
			},
			$ls_post_conditions
		);

		$ilAtomQuery = $this->db->buildAtomQuery();
		$ilAtomQuery->addTableLock(static::TABLE_NAME);
		$ilAtomQuery->addQueryCallable(
			function(\ilDBInterface $db) use ($ref_ids, $ls_post_conditions) {
				$this->delete($ref_ids);
				$this->insert($ls_post_conditions);
			}
		);
		$ilAtomQuery->run();
	}
}
