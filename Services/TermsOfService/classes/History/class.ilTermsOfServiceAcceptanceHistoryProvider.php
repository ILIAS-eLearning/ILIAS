<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTermsOfServiceAcceptanceHistoryProvider
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceAcceptanceHistoryProvider extends \ilTermsOfServiceTableDatabaseDataProvider
{
	/**
	 * @inheritdoc
	 */
	protected function getSelectPart(array $params, array $filter): string
	{
		$fields = array(
			'tos_acceptance_track.tosv_id',
			'ud.usr_id',
			'ud.login',
			'ud.firstname',
			'ud.lastname',
			'tos_acceptance_track.ts',
			'tos_versions.src',
			'tos_versions.text'
		);

		return implode(', ', $fields);
	}

	/**
	 * @inheritdoc
	 */
	protected function getFromPart(array $params, array $filter): string
	{
		$joins = array(
			'INNER JOIN tos_acceptance_track ON tos_acceptance_track.usr_id = ud.usr_id',
			'INNER JOIN tos_versions ON tos_versions.id = tos_acceptance_track.tosv_id',
		);

		return 'usr_data ud ' . implode(' ', $joins);
	}

	/**
	 * @inheritdoc
	 */
	protected function getWherePart(array $params, array $filter): string
	{
		$where = array();

		if (isset($filter['query']) && strlen($filter['query'])) {
			$where[] = '(' . implode(' OR ', array(
					$this->db->like('ud.login', 'text', '%' . $filter['query'] . '%'),
					$this->db->like('ud.firstname', 'text', '%' . $filter['query'] . '%'),
					$this->db->like('ud.lastname', 'text', '%' . $filter['query'] . '%'),
					$this->db->like('ud.email', 'text', '%' . $filter['query'] . '%')
				)) . ')';
		}

		if (isset($filter['period']) && is_array($filter['period'])) {
			$where[] = '(' . implode(' AND ', array(
					'tos_acceptance_track.ts >= ' . $this->db->quote($filter['period']['start'], 'integer'),
					'tos_acceptance_track.ts <= ' . $this->db->quote($filter['period']['end'], 'integer')
				)) . ')';
		}

		return implode(' AND ', $where);
	}

	/**
	 * @inheritdoc
	 */
	protected function getGroupByPart(array $params, array $filter): string
	{
		return '';
	}

	/**
	 * @inheritdoc
	 */
	protected function getHavingPart(array $params, array $filter): string
	{
		return '';
	}

	/**
	 * @inheritdoc
	 */
	protected function getOrderByPart(array $params, array $filter): string
	{
		if (isset($params['order_field'])) {
			if (!is_string($params['order_field'])) {
				throw new InvalidArgumentException('Please provide a valid order field.');
			}

			if (!in_array($params['order_field'], array('login', 'firstname', 'lastname', 'src', 'ts'))) {
				throw new InvalidArgumentException('Please provide a valid order field.');
			}

			if ($params['order_field'] == 'ts') {
				$params['order_field'] = 'tos_acceptance_track.ts';
			}

			if (!isset($params['order_direction'])) {
				$params['order_direction'] = 'ASC';
			} else {
				if (!in_array(strtolower($params['order_direction']), array('asc', 'desc'))) {
					throw new InvalidArgumentException('Please provide a valid order direction.');
				}
			}

			return $params['order_field'] . ' ' . $params['order_direction'];
		}

		return '';
	}
}
