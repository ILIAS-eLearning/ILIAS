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
 * Class ilMailAutoCompleteUserProvider
 */
class ilMailAutoCompleteUserProvider extends ilMailAutoCompleteRecipientProvider
{
    /**
     * @return array{login: string, firstname: string, lastname:string}
     */
    public function current(): array
    {
        return [
            'login' => $this->data['login'],
            'firstname' => $this->data['firstname'],
            'lastname' => $this->data['lastname'],
        ];
    }

    public function key(): string
    {
        return $this->data['login'];
    }

    public function rewind(): void
    {
        if ($this->res) {
            $this->db->free($this->res);
            $this->res = null;
        }

        $select_part = $this->getSelectPart();
        $where_part = $this->getWherePart($this->quoted_term);
        $order_by_part = $this->getOrderByPart();
        $query = implode(" ", [
            'SELECT ' . $select_part,
            'FROM ' . $this->getFromPart(),
            $where_part ? 'WHERE ' . $where_part : '',
            $order_by_part ? 'ORDER BY ' . $order_by_part : '',
        ]);

        $this->res = $this->db->query($query);
    }

    protected function getSelectPart(): string
    {
        $fields = [
            'login',
            sprintf(
                "(CASE WHEN (profpref.value = %s OR profpref.value = %s) " .
                "THEN firstname ELSE '' END) firstname",
                $this->db->quote('y', 'text'),
                $this->db->quote('g', 'text')
            ),
            sprintf(
                "(CASE WHEN (profpref.value = %s OR profpref.value = %s) " .
                "THEN lastname ELSE '' END) lastname",
                $this->db->quote('y', 'text'),
                $this->db->quote('g', 'text')
            ),
            sprintf(
                "(CASE WHEN ((profpref.value = %s OR profpref.value = %s) " .
                "AND pubemail.value = %s) THEN email ELSE '' END) email",
                $this->db->quote('y', 'text'),
                $this->db->quote('g', 'text'),
                $this->db->quote('y', 'text')
            ),
        ];

        $fields[] = 'profpref.value profile_value';
        $fields[] = 'pubemail.value email_value';

        return implode(', ', $fields);
    }

    protected function getFromPart(): string
    {
        $joins = [];

        $joins[] = '
			LEFT JOIN usr_pref profpref
			ON profpref.usr_id = usr_data.usr_id
			AND profpref.keyword = ' . $this->db->quote('public_profile', 'text');

        $joins[] = '
			LEFT JOIN usr_pref pubemail
			ON pubemail.usr_id = usr_data.usr_id
			AND pubemail.keyword = ' . $this->db->quote('public_email', 'text');

        return 'usr_data ' . implode(' ', $joins);
    }

    protected function getWherePart(string $search_query): string
    {
        $outer_conditions = [];
        $outer_conditions[] = 'usr_data.usr_id != ' . $this->db->quote(ANONYMOUS_USER_ID, 'integer');
        $outer_conditions[] = 'usr_data.active != ' . $this->db->quote(0, 'integer');

        $field_conditions = [];
        foreach ($this->getFields() as $field) {
            $field_condition = $this->getQueryConditionByFieldAndValue($field, $search_query);

            if ('email' === $field) {
                // If privacy should be respected,
                // the profile setting of every user concerning the email address has to be
                // respected (in every user context, no matter if the user is 'logged in' or 'anonymous').
                $email_query = [];
                $email_query[] = $field_condition;
                $email_query[] = 'pubemail.value = ' . $this->db->quote('y', 'text');
                $field_conditions[] = '(' . implode(' AND ', $email_query) . ')';
            } else {
                $field_conditions[] = $field_condition;
            }
        }

        // If the current user context ist 'logged in' and privacy should be respected,
        // all fields >>>except the login<<<
        // should only be searchable if the users' profile is published (y oder g)
        // In 'anonymous' context we do not need this additional conditions,
        // because we checked the privacy setting in the condition above: profile = 'g'
        if ($field_conditions) {
            $fields = '(' . implode(' OR ', $field_conditions) . ')';

            $field_conditions = ['(' . implode(' AND ', [
                $fields,
                $this->db->in('profpref.value', ['y', 'g'], false, 'text'),
            ]) . ')'];
        }

        // The login field must be searchable regardless (for 'logged in' users) of any privacy settings.
        // We handled the general condition for 'anonymous' context above: profile = 'g'
        $field_conditions[] = $this->getQueryConditionByFieldAndValue('login', $search_query);

        if (ilUserAccountSettings::getInstance()->isUserAccessRestricted()) {
            $outer_conditions[] = $this->db->in(
                'time_limit_owner',
                ilUserFilter::getInstance()->getFolderIds(),
                false,
                'integer'
            );
        }

        if ($field_conditions) {
            $outer_conditions[] = '(' . implode(' OR ', $field_conditions) . ')';
        }

        return implode(' AND ', $outer_conditions);
    }

    protected function getOrderByPart(): string
    {
        return 'login ASC';
    }

    protected function getQueryConditionByFieldAndValue(string $field, $a_str): string
    {
        return $this->db->like($field, 'text', $a_str . '%');
    }

    /**
     * @return string[]
     */
    protected function getFields(): array
    {
        $available_fields = [];
        foreach (['firstname', 'lastname'] as $field) {
            if (ilUserSearchOptions::_isEnabled($field)) {
                $available_fields[] = $field;
            }
        }
        return $available_fields;
    }
}
