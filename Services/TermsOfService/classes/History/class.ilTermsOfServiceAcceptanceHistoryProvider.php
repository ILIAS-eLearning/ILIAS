<?php

declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
 * Class ilTermsOfServiceAcceptanceHistoryProvider
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceAcceptanceHistoryProvider extends ilTermsOfServiceTableDatabaseDataProvider
{
    protected function getSelectPart(array $params, array $filter): string
    {
        $fields = [
            'tos_acceptance_track.tosv_id',
            'tos_acceptance_track.criteria',
            'tos_acceptance_track.ts',
            'ud.usr_id',
            'ud.login',
            'ud.firstname',
            'ud.lastname',
            '(CASE WHEN tos_documents.title IS NOT NULL THEN tos_documents.title ELSE tos_versions.title END) title',
            'tos_versions.text',
        ];

        return implode(', ', $fields);
    }

    protected function getFromPart(array $params, array $filter): string
    {
        $joins = [
            'INNER JOIN tos_acceptance_track ON tos_acceptance_track.usr_id = ud.usr_id',
            'INNER JOIN tos_versions ON tos_versions.id = tos_acceptance_track.tosv_id',
            'LEFT JOIN tos_documents ON tos_documents.id = tos_versions.doc_id',
        ];

        return 'usr_data ud ' . implode(' ', $joins);
    }

    protected function getWherePart(array $params, array $filter): string
    {
        $where = [];

        if (isset($filter['query']) && is_string($filter['query']) && $filter['query'] !== '') {
            $where[] = '(' . implode(' OR ', [
                    $this->db->like('ud.login', 'text', '%' . $filter['query'] . '%'),
                    $this->db->like('ud.firstname', 'text', '%' . $filter['query'] . '%'),
                    $this->db->like('ud.lastname', 'text', '%' . $filter['query'] . '%'),
                    $this->db->like('ud.email', 'text', '%' . $filter['query'] . '%')
                ]) . ')';
        }

        if (isset($filter['period']) && is_array($filter['period'])) {
            $dateFilterParts = [];

            if (null !== $filter['period']['start']) {
                $dateFilterParts[] = 'tos_acceptance_track.ts >= ' . $this->db->quote(
                    $filter['period']['start'],
                    'integer'
                );
            }

            if (null !== $filter['period']['end']) {
                $dateFilterParts[] = 'tos_acceptance_track.ts <= ' . $this->db->quote(
                    $filter['period']['end'],
                    'integer'
                );
            }

            if (count($dateFilterParts) > 0) {
                $where[] = '(' . implode(' AND ', $dateFilterParts) . ')';
            }
        }

        return implode(' AND ', $where);
    }

    protected function getGroupByPart(array $params, array $filter): string
    {
        return '';
    }

    protected function getHavingPart(array $params, array $filter): string
    {
        return '';
    }

    protected function getOrderByPart(array $params, array $filter): string
    {
        if (isset($params['order_field'])) {
            if (!is_string($params['order_field'])) {
                throw new InvalidArgumentException('Please provide a valid order field.');
            }

            if (in_array($params['order_field'], ['lng', 'src'])) {
                // Maybe necessary because of of migrated (from < 5.4.x) ILIAS installations
                $params['order_field'] = 'ts';
            }

            if (!in_array($params['order_field'], ['login', 'firstname', 'lastname', 'title', 'ts'])) {
                throw new InvalidArgumentException('Please provide a valid order field.');
            }

            if ($params['order_field'] === 'ts') {
                $params['order_field'] = 'tos_acceptance_track.ts';
            }

            if (!isset($params['order_direction'])) {
                $params['order_direction'] = 'ASC';
            } elseif (!in_array(strtolower($params['order_direction']), ['asc', 'desc'])) {
                throw new InvalidArgumentException('Please provide a valid order direction.');
            }

            return $params['order_field'] . ' ' . $params['order_direction'];
        }

        return '';
    }
}
