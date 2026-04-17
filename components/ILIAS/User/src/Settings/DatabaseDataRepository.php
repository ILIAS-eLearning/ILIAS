<?php

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

declare(strict_types=1);

namespace ILIAS\User\Settings;

class DatabaseDataRepository implements DataRepository
{
    private const string TABLE_NAME = 'usr_pref';
    public function __construct(
        private readonly \ilDBInterface $db
    ) {
    }

    public function getFor(int $user_id): array
    {
        $query = $this->db->queryF(
            'SELECT * FROM ' . self::TABLE_NAME . ' WHERE usr_id = %s',
            [\ilDBConstants::T_INTEGER],
            [$user_id]
        );

        $setting_values = [];
        while ($row = $this->db->fetchObject($query)) {
            $setting_values[$row->keyword] = $row->value;
        }

        return $setting_values;
    }

    public function deleteFor(int $user_id): void
    {
        $this->db->manipulateF(
            'DELETE FROM ' . self::TABLE_NAME . ' WHERE usr_id = %s',
            [\ilDBConstants::T_INTEGER],
            [$user_id]
        );
    }

    public function deleteSingleFor(int $user_id, string $key): void
    {
        $this->db->manipulateF(
            'DELETE FROM ' . self::TABLE_NAME . ' WHERE usr_id = %s AND keyword = %s',
            [\ilDBConstants::T_INTEGER, \ilDBConstants::T_TEXT],
            [$user_id, $key]
        );
    }

    public function storeFor(int $user_id, array $settings_array): void
    {
        $query = 'INSERT INTO ' . self::TABLE_NAME . ' VALUES ' . implode(
            ', ',
            array_map(
                fn(string $v) => '('
                    . $this->db->quote($user_id, \ilDBConstants::T_INTEGER) . ', '
                    . $this->db->quote($v, \ilDBConstants::T_TEXT) . ', '
                    . $this->db->quote($settings_array[$v], \ilDBConstants::T_TEXT)
                    . ')',
                array_keys($settings_array)
            )
        );
        $this->db->manipulate($query);
    }

    public function storeSingleFor(
        int $user_id,
        string $key,
        string $value
    ): void {
        $this->db->replace(
            self::TABLE_NAME,
            [
                'usr_id' => [\ilDBConstants::T_INTEGER, $user_id],
                'keyword' => [\ilDBConstants::T_TEXT, $key],
            ],
            [
                'value' => [\ilDBConstants::T_TEXT, $value]
            ]
        );
    }

    public function getSearchSelectConditionalOnVisibility(
        string $profile_data_table_name,
        string $login_data_column_name,
        string $firstname_data_column_name,
        string $lastname_data_column_name,
        string $primary_email_data_column_name,
        string $secondary_email_data_column_name
    ): string {
        return 'SELECT ' . $this->buildConditionalSearchFieldString(
            $login_data_column_name,
            $firstname_data_column_name,
            $lastname_data_column_name,
            $primary_email_data_column_name,
            $secondary_email_data_column_name
        ) . " FROM {$profile_data_table_name}" . PHP_EOL
            . $this->buildConditionalSearchJoinString($profile_data_table_name);
    }

    private function buildConditionalSearchFieldString(
        string $login_data_column_name,
        string $firstname_data_column_name,
        string $lastname_data_column_name,
        string $primary_email_data_column_name,
        string $secondary_email_data_column_name
    ): string {
        return implode(
            ', ',
            [
                $login_data_column_name,
                sprintf(
                    "(CASE WHEN ({$firstname_data_column_name} IS NOT NULL" . PHP_EOL
                    . 'AND (profilevisibility.value = %s OR profilevisibility.value = %s))' . PHP_EOL
                    . "THEN {$firstname_data_column_name} ELSE '' END) {$firstname_data_column_name}",
                    $this->db->quote('y', \ilDBConstants::T_TEXT),
                    $this->db->quote('g', \ilDBConstants::T_TEXT)
                ),
                sprintf(
                    "(CASE WHEN ({$lastname_data_column_name} IS NOT NULL" . PHP_EOL
                    . 'AND (profilevisibility.value = %s OR profilevisibility.value = %s))' . PHP_EOL
                    . "THEN {$lastname_data_column_name} ELSE '' END) {$lastname_data_column_name}",
                    $this->db->quote('y', \ilDBConstants::T_TEXT),
                    $this->db->quote('g', \ilDBConstants::T_TEXT)
                ),
                sprintf(
                    "(CASE WHEN ({$primary_email_data_column_name} IS NOT NULL" . PHP_EOL
                    . 'AND (profilevisibility.value = %s OR profilevisibility.value = %s)' . PHP_EOL
                    . 'AND primaryemailvisibility.value = %s)' . PHP_EOL
                    . "THEN {$primary_email_data_column_name} ELSE '' END) {$primary_email_data_column_name}",
                    $this->db->quote('y', \ilDBConstants::T_TEXT),
                    $this->db->quote('g', \ilDBConstants::T_TEXT),
                    $this->db->quote('y', \ilDBConstants::T_TEXT)
                ),
                sprintf(
                    "(CASE WHEN ({$secondary_email_data_column_name} IS NOT NULL" . PHP_EOL
                    . 'AND (profilevisibility.value = %s OR profilevisibility.value = %s)' . PHP_EOL
                    . 'AND secondaryemailvisibility.value = %s)' . PHP_EOL
                    . "THEN {$secondary_email_data_column_name} ELSE '' END) {$secondary_email_data_column_name}",
                    $this->db->quote('y', \ilDBConstants::T_TEXT),
                    $this->db->quote('g', \ilDBConstants::T_TEXT),
                    $this->db->quote('y', \ilDBConstants::T_TEXT)
                )
            ]
        );
    }

    private function buildConditionalSearchJoinString(
        string $profile_data_table_name
    ): string {
        return 'LEFT OUTER JOIN ' . self::TABLE_NAME . ' profilevisibility' . PHP_EOL
            . "ON profilevisibility.usr_id = {$profile_data_table_name}.usr_id" . PHP_EOL
            . 'AND profilevisibility.keyword = ' . $this->db->quote('public_profile', \ilDBConstants::T_TEXT) . PHP_EOL
            . 'LEFT OUTER JOIN ' . self::TABLE_NAME . ' firstnamevisibility' . PHP_EOL
            . "ON firstnamevisibility.usr_id = {$profile_data_table_name}.usr_id" . PHP_EOL
            . 'AND firstnamevisibility.keyword = ' . $this->db->quote('public_firstname', \ilDBConstants::T_TEXT) . PHP_EOL
            . 'LEFT OUTER JOIN ' . self::TABLE_NAME . ' lastnamevisibility' . PHP_EOL
            . "ON lastnamevisibility.usr_id = {$profile_data_table_name}.usr_id" . PHP_EOL
            . 'AND lastnamevisibility.keyword = ' . $this->db->quote('public_lastname', \ilDBConstants::T_TEXT) . PHP_EOL
            . 'LEFT OUTER JOIN ' . self::TABLE_NAME . ' primaryemailvisibility' . PHP_EOL
            . "ON primaryemailvisibility.usr_id = {$profile_data_table_name}.usr_id" . PHP_EOL
            . 'AND primaryemailvisibility.keyword = ' . $this->db->quote('public_email', \ilDBConstants::T_TEXT) . PHP_EOL
            . 'LEFT OUTER JOIN ' . self::TABLE_NAME . ' secondaryemailvisibility' . PHP_EOL
            . "ON secondaryemailvisibility.usr_id = {$profile_data_table_name}.usr_id" . PHP_EOL
            . 'AND secondaryemailvisibility.keyword = ' . $this->db->quote('public_second_email', \ilDBConstants::T_TEXT);
    }
}
