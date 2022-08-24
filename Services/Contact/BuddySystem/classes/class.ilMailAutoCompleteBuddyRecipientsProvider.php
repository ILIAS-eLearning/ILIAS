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
 * Class ilMailAutoCompleteBuddyRecipientsProvider
 */
class ilMailAutoCompleteBuddyRecipientsProvider extends ilMailAutoCompleteUserProvider
{
    /**
     * @return string
     */
    protected function getFromPart(): string
    {
        $joins = [];

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

        return 'usr_data ' . implode(' ', $joins);
    }
}
