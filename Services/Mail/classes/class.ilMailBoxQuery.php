<?php declare(strict_types=1);

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
 * Mail query class.
 *
 * @author Michael Jansen <mjansen@databay.de>
 * @ingroup ServicesMail
 *
 */
class ilMailBoxQuery
{
    public static int $folderId = -1;
    public static int $userId = -1;
    public static int $limit = 0;
    public static int $offset = 0;
    public static string $orderDirection = '';
    public static string $orderColumn = '';
    public static array $filter = [];
    public static array $filtered_ids = [];

    /**
     * @return array{set: array[], cnt: int, cnt_unread: int}
     * @throws Exception
     */
    public static function _getMailBoxListData() : array
    {
        global $DIC;

        $mails = ['cnt' => 0, 'cnt_unread' => 0, 'set' => []];

        $filter = [
            'mail_filter_sender' => 'CONCAT(CONCAT(firstname, lastname), login)',
            'mail_filter_recipients' => 'CONCAT(CONCAT(rcp_to, rcp_cc), rcp_bcc)',
            'mail_filter_subject' => 'm_subject',
            'mail_filter_body' => 'm_message',
            'mail_filter_attach' => '',
        ];

        $filter_parts = [];
        if (
            isset(self::$filter['mail_filter']) &&
            is_string(self::$filter['mail_filter']) &&
            self::$filter['mail_filter'] !== ''
        ) {
            foreach ($filter as $key => $column) {
                if ($column !== '' && isset(self::$filter[$key]) && (int) self::$filter[$key]) {
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

        if (
            isset(self::$filter['mail_filter_only_with_attachments']) &&
            self::$filter['mail_filter_only_with_attachments']
        ) {
            $filter_qry .= ' AND attachments != ' . $DIC->database()->quote(serialize(null), 'text') . ' ';
        }

        if (isset(self::$filter['mail_filter_only_user_mails']) && self::$filter['mail_filter_only_user_mails']) {
            $filter_qry .= ' AND sender_id != ' . $DIC->database()->quote(ANONYMOUS_USER_ID, ilDBConstants::T_INTEGER) . ' ';
        }

        if (isset(self::$filter['period']) && is_array(self::$filter['period'])) {
            $dateFilterParts = [];

            if (null !== self::$filter['period']['start']) {
                $dateFilterParts[] = 'send_time >= ' . $DIC->database()->quote(
                    (new DateTimeImmutable(
                        '@' . self::$filter['period']['start']
                    ))->format('Y-m-d 00:00:00'),
                    'timestamp'
                );
            }

            if (null !== self::$filter['period']['end']) {
                $dateFilterParts[] = 'send_time <= ' . $DIC->database()->quote(
                    (new DateTimeImmutable(
                        '@' . self::$filter['period']['end']
                    ))->format('Y-m-d 23:59:59'),
                    'timestamp'
                );
            }

            if (count($dateFilterParts) > 0) {
                $filter_qry .= ' AND (' . implode(' AND ', $dateFilterParts) . ') ';
            }
        }

        $queryCount = 'SELECT COUNT(mail_id) cnt FROM mail '
                    . 'LEFT JOIN usr_data ON usr_id = sender_id '
                    . 'WHERE user_id = %s '
                    . 'AND ((sender_id > 0 AND sender_id IS NOT NULL '
                    . 'AND usr_id IS NOT NULL) OR (sender_id = 0 OR sender_id IS NULL)) '
                    . 'AND folder_id = %s '
                    . $filter_qry;

        if (self::$filtered_ids) {
            $queryCount .= ' AND ' . $DIC->database()->in(
                'mail_id',
                self::$filtered_ids,
                false,
                'integer'
            ) . ' ';
        }

        $queryCount .= ' UNION ALL '
                    . 'SELECT COUNT(mail_id) cnt FROM mail '
                    . 'LEFT JOIN usr_data ON usr_id = sender_id '
                    . 'WHERE user_id = %s '
                    . 'AND ((sender_id > 0 AND sender_id IS NOT NULL '
                    . 'AND usr_id IS NOT NULL) OR (sender_id = 0 OR sender_id IS NULL)) '
                    . 'AND folder_id = %s '
                    . $filter_qry . ' '
                    . 'AND m_status = %s';

        if (self::$filtered_ids) {
            $queryCount .= ' AND ' . $DIC->database()->in(
                'mail_id',
                self::$filtered_ids,
                false,
                'integer'
            ) . ' ';
        }

        $res = $DIC->database()->queryF(
            $queryCount,
            ['integer', 'integer', 'integer', 'integer', 'text'],
            [self::$userId, self::$folderId, self::$userId, self::$folderId, 'unread']
        );

        $counter = 0;
        while ($cnt_row = $DIC->database()->fetchAssoc($res)) {
            if ($counter === 0) {
                $mails['cnt'] = (int) $cnt_row['cnt'];
            } elseif ($counter === 1) {
                $mails['cnt_unread'] = (int) $cnt_row['cnt'];
            } else {
                break;
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

        $query = 'SELECT mail.*' . $sortColumn . ' ' . $firstnameSelection . ' FROM mail '
               . 'LEFT JOIN usr_data ON usr_id = sender_id '
               . 'AND ((sender_id > 0 AND sender_id IS NOT NULL '
               . 'AND usr_id IS NOT NULL) OR (sender_id = 0 OR sender_id IS NULL)) '
               . 'WHERE user_id = %s '
               . $filter_qry . ' '
               . 'AND folder_id = %s';

        if (self::$filtered_ids) {
            $query .= ' AND ' . $DIC->database()->in(
                'mail_id',
                self::$filtered_ids,
                false,
                'integer'
            ) . ' ';
        }

        $orderDirection = 'ASC';
        if (in_array(strtolower(self::$orderDirection), ['desc', 'asc'], true)) {
            $orderDirection = self::$orderDirection;
        }

        if (self::$orderColumn === 'from') {
            $query .= ' ORDER BY '
                    . ' fname ' . $orderDirection . ', '
                    . ' lastname ' . $orderDirection . ', '
                    . ' login ' . $orderDirection . ', '
                    . ' import_name ' . $orderDirection;
        } elseif (self::$orderColumn !== '') {
            if (
                !in_array(strtolower(self::$orderColumn), ['m_subject', 'send_time', 'rcp_to'], true) &&
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
            if (isset($row['attachments'])) {
                $row['attachments'] = (array) unserialize(
                    stripslashes($row['attachments']),
                    ['allowed_classes' => false]
                );
            } else {
                $row['attachments'] = [];
            }

            if (isset($row['mail_id'])) {
                $row['mail_id'] = (int) $row['mail_id'];
            }

            if (isset($row['user_id'])) {
                $row['user_id'] = (int) $row['user_id'];
            }

            if (isset($row['folder_id'])) {
                $row['folder_id'] = (int) $row['folder_id'];
            }

            if (isset($row['sender_id'])) {
                $row['sender_id'] = (int) $row['sender_id'];
            }

            $mails['set'][] = $row;
        }

        return $mails;
    }
}
