<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTermsOfServiceTableDatabaseDataProvider
 * @author Michael Jansen <mjansen@databay.de>
 */
abstract class ilTermsOfServiceTableDatabaseDataProvider implements \ilTermsOfServiceTableDataProvider
{
    /** @var ilDBInterface */
    protected $db;

    /**
     * ilTermsOfServiceTableDatabaseDataProvider constructor.
     * @param \ilDBInterface $db
     */
    public function __construct(\ilDBInterface $db)
    {
        $this->db = $db;
    }

    /**
     * @param array $params
     * @param array $filter
     * @return string
     */
    abstract protected function getSelectPart(array $params, array $filter) : string;

    /**
     * @param array $params
     * @param array $filter
     * @return string
     */
    abstract protected function getFromPart(array $params, array $filter) : string;

    /**
     * @param array $params
     * @param array $filter
     * @return string
     */
    abstract protected function getWherePart(array $params, array $filter) : string;

    /**
     * @param array $params
     * @param array $filter
     * @return string
     */
    abstract protected function getGroupByPart(array $params, array $filter) : string;

    /**
     * @param array $params
     * @param array $filter
     * @return string
     * @abstract
     */
    abstract protected function getHavingPart(array $params, array $filter) : string;

    /**
     * @param array $params
     * @param array $filter
     * @return string
     */
    abstract protected function getOrderByPart(array $params, array $filter) : string;

    /**
     * @param array $params
     * @param array $filter
     * @return array
     * @throws \InvalidArgumentException
     */
    public function getList(array $params, array $filter) : array
    {
        $data = array(
            'items' => array(),
            'cnt' => 0
        );

        $select = $this->getSelectPart($params, $filter);
        $where = $this->getWherePart($params, $filter);
        $from = $this->getFromPart($params, $filter);
        $order = $this->getOrderByPart($params, $filter);
        $group = $this->getGroupByPart($params, $filter);
        $having = $this->getHavingPart($params, $filter);

        if (isset($params['limit'])) {
            if (!is_numeric($params['limit'])) {
                throw new \InvalidArgumentException('Please provide a valid numerical limit.');
            }

            if (!isset($params['offset'])) {
                $params['offset'] = 0;
            } else {
                if (!is_numeric($params['offset'])) {
                    throw new \InvalidArgumentException('Please provide a valid numerical offset.');
                }
            }

            $this->db->setLimit($params['limit'], $params['offset']);
        }

        $where = strlen($where) ? 'WHERE ' . $where : '';
        $query = "SELECT {$select} FROM {$from} {$where}";

        if (strlen($group)) {
            $query .= " GROUP BY {$group}";
        }

        if (strlen($having)) {
            $query .= " HAVING {$having}";
        }

        if (strlen($order)) {
            $query .= " ORDER BY {$order}";
        }

        $res = $this->db->query($query);
        while ($row = $this->db->fetchAssoc($res)) {
            $data['items'][] = $row;
        }

        if (isset($params['limit'])) {
            $cnt_sql = "SELECT COUNT(*) cnt FROM ({$query}) subquery";
            $row_cnt = $this->db->fetchAssoc($this->db->query($cnt_sql));
            $data['cnt'] = $row_cnt['cnt'];
        }

        return $data;
    }
}
