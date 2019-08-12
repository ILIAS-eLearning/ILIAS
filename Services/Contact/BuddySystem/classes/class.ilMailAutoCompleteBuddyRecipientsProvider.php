<?php declare(strict_types=1);
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilMailAutoCompleteBuddyRecipientsProvider
 */
class ilMailAutoCompleteBuddyRecipientsProvider extends ilMailAutoCompleteUserProvider
{
    /**
     * @return string
     */
    protected function getFromPart()
    {
        $joins = array();

        $joins[] = implode(' ', [
            'INNER JOIN buddylist',
            'ON ((',
            'buddylist.usr_id = usr_data.usr_id AND',
            'buddylist.buddy_usr_id = ' . $this->db->quote($this->user_id, 'integer'),
            ') OR (',
            'buddylist.buddy_usr_id = usr_data.usr_id AND',
            'buddylist.usr_id = ' . $this->db->quote($this->user_id, 'integer'),
            '))',
        ]);

        $joins[] = implode(' ', [
            'LEFT JOIN usr_pref profpref',
            'ON profpref.usr_id = usr_data.usr_id',
            'AND profpref.keyword = ' . $this->db->quote('public_profile', 'text'),
        ]);

        $joins[] = implode(' ', [
            'LEFT JOIN usr_pref pubemail',
            'ON pubemail.usr_id = usr_data.usr_id',
            'AND pubemail.keyword = ' . $this->db->quote('public_email', 'text'),
        ]);

        if ($joins) {
            return 'usr_data ' . implode(' ', $joins);
        } else {
            return 'usr_data ';
        }
    }
}