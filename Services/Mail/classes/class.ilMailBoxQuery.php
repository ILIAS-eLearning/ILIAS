<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Mail query class.
 *
 * @author Michael Jansen <mjansen@databay.de>
 * @version $Id$
 * @ingroup ServicesMail
 *
 */
class ilMailBoxQuery
{
    public static $folderId = -1;
    public static $userId = -1;
    public static $limit = 0;
    public static $offset = 0;
    public static $orderDirection = '';
    public static $orderColumn = '';
    public static $filter = array();
    public static $filtered_ids = array();

    /**
     * _getMailBoxListData
     *
     * @access	public
     * @static
     * @return	array	Array of mails
     *
     */
    public static function _getMailBoxListData()
    {
        global $DIC;

        $mails = ['cnt' => 0, 'cnt_unread' => 0, 'set' => []];

        $filter = [
            'mail_filter_sender'     => 'CONCAT(CONCAT(firstname, lastname), login)',
            'mail_filter_recipients' => 'CONCAT(CONCAT(rcp_to, rcp_cc), rcp_bcc)',
            'mail_filter_subject'    => 'm_subject',
            'mail_filter_body'       => 'm_message',
            'mail_filter_attach'     => ''
        ];

        $filter_parts = [];
        if (isset(self::$filter['mail_filter']) && strlen(self::$filter['mail_filter'])) {
            foreach ($filter as $key => $column) {
                if (strlen($column) && isset(self::$filter[$key]) && (int) self::$filter[$key]) {
                    $filter_parts[] = $DIC->database()->like(
                        $column,
                        'text',
                        '%%' . self::$filter['mail_filter'] . '%%',
                        false
                    );
                }
            }
        }

        $filter_qry = '';
        if ($filter_parts) {
            $filter_qry = 'AND (' . implode(' OR ', $filter_parts) . ')';
        }

        if (isset(self::$filter['mail_filter_only_unread']) && self::$filter['mail_filter_only_unread']) {
            $filter_qry .= ' AND m_status = ' . $DIC->database()->quote('unread', 'text') . ' ';
        }

        if (isset(self::$filter['mail_filter_only_with_attachments']) && self::$filter['mail_filter_only_with_attachments']) {
            $filter_qry .= ' AND attachments != ' . $DIC->database()->quote(serialize(null), 'text') . ' ';
        }

        if (isset(self::$filter['period']) && is_array(self::$filter['period'])) {
            $dateFilterParts = [];

            if (null !== self::$filter['period']['start']) {
                $dateFilterParts[] = 'send_time >= ' . $DIC->database()->quote(
                    (new \DateTimeImmutable('@' . self::$filter['period']['start']))->format('Y-m-d 00:00:00'),
                    'timestamp'
                );
            }

            if (null !== self::$filter['period']['end']) {
                $dateFilterParts[] = 'send_time <= ' . $DIC->database()->quote(
                    (new \DateTimeImmutable('@' . self::$filter['period']['end']))->format('Y-m-d 23:59:59'),
                    'timestamp'
                );
            }

            if (count($dateFilterParts) > 0) {
                $filter_qry .= ' AND (' . implode(' AND ', $dateFilterParts) . ') ';
            }
        }

        // count query
        $queryCount = 'SELECT COUNT(mail_id) cnt FROM mail '
                    . 'LEFT JOIN usr_data ON usr_id = sender_id '
                    . 'WHERE user_id = %s '
                    . 'AND ((sender_id > 0 AND sender_id IS NOT NULL AND usr_id IS NOT NULL) OR (sender_id = 0 OR sender_id IS NULL)) '
                    . 'AND folder_id = %s '
                    . $filter_qry;

        if (self::$filtered_ids) {
            $queryCount .= ' AND ' . $DIC->database()->in('mail_id', self::$filtered_ids, false, 'integer') . ' ';
        }

        $queryCount .= ' UNION ALL '
                    . 'SELECT COUNT(mail_id) cnt FROM mail '
                    . 'LEFT JOIN usr_data ON usr_id = sender_id '
                    . 'WHERE user_id = %s '
                    . 'AND ((sender_id > 0 AND sender_id IS NOT NULL AND usr_id IS NOT NULL) OR (sender_id = 0 OR sender_id IS NULL)) '
                    . 'AND folder_id = %s '
                    . $filter_qry . ' '
                    . 'AND m_status = %s';

        if (self::$filtered_ids) {
            $queryCount .= ' AND ' . $DIC->database()->in('mail_id', self::$filtered_ids, false, 'integer') . ' ';
        }

        $res = $DIC->database()->queryF(
            $queryCount,
            ['integer', 'integer', 'integer', 'integer', 'text'],
            [self::$userId, self::$folderId, self::$userId, self::$folderId, 'unread']
        );

        $counter = 0;
        while ($cnt_row = $DIC->database()->fetchAssoc($res)) {
            if ($counter === 0) {
                $mails['cnt'] = $cnt_row['cnt'];
            } else {
                if ($counter === 1) {
                    $mails['cnt_unread'] = $cnt_row['cnt'];
                } else {
                    break;
                }
            }

            ++$counter;
        }

        $sortColumn = '';
        $firstnameSelection = '';
        if (self::$orderColumn === 'from') {
            // Because of the user id of automatically generated mails and ordering issues we have to do some magic
            $firstnameSelection = '
				,(CASE
					WHEN (usr_id = ' . ANONYMOUS_USER_ID . ') THEN firstname 
					ELSE ' . $DIC->database()->quote(ilMail::_getIliasMailerName(), 'text') . '
				END) fname
			';
        }

        // item query
        $query = 'SELECT mail.*' . $sortColumn . ' ' . $firstnameSelection . ' FROM mail '
               . 'LEFT JOIN usr_data ON usr_id = sender_id '
               . 'AND ((sender_id > 0 AND sender_id IS NOT NULL AND usr_id IS NOT NULL) OR (sender_id = 0 OR sender_id IS NULL)) '
               . 'WHERE user_id = %s '
               . $filter_qry . ' '
               . 'AND folder_id = %s';

        if (self::$filtered_ids) {
            $query .= ' AND ' . $DIC->database()->in('mail_id', self::$filtered_ids, false, 'integer') . ' ';
        }

        $orderDirection = 'ASC';
        if (in_array(strtolower(self::$orderDirection), array('desc', 'asc'))) {
            $orderDirection = self::$orderDirection;
        }

        if (self::$orderColumn === 'from') {
            $query .= ' ORDER BY '
                    . ' fname ' . $orderDirection . ', '
                    . ' lastname ' . $orderDirection . ', '
                    . ' login ' . $orderDirection . ', '
                    . ' import_name ' . $orderDirection;
        } elseif (strlen(self::$orderColumn) > 0) {
            if (
                !in_array(strtolower(self::$orderColumn), ['m_subject', 'send_time', 'rcp_to']) &&
                !$DIC->database()->tableColumnExists('mail', strtolower(self::$orderColumn))) {
                // @todo: Performance problem...
                self::$orderColumn = 'send_time';
            }

            $query .= ' ORDER BY ' . strtolower(self::$orderColumn) . ' ' . $orderDirection;
        } else {
            $query .= ' ORDER BY send_time DESC';
        }

        $DIC->database()->setLimit(self::$limit, self::$offset);
        $res = $DIC->database()->queryF(
            $query,
            ['integer', 'integer'],
            [self::$userId, self::$folderId]
        );
        while ($row = $DIC->database()->fetchAssoc($res)) {
            $row['attachments'] = unserialize(stripslashes($row['attachments']));
            $row['m_type'] = unserialize(stripslashes($row['m_type']));
            $mails['set'][] = $row;
        }

        return $mails;
    }
}
