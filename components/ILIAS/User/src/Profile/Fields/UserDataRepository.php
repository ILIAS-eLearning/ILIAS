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

namespace ILIAS\User\Profile\Fields;

use ILIAS\ResourceStorage\Identification\ResourceIdentification;

class UserDataRepository
{
    private const string USER_BASE_TABLE = 'usr_profile_data';
    private const string USER_VALUES_TABLE = 'usr_profile_data';

    public function __construct(
        private readonly \ilDBInterface $db
    ) {
    }

    /**
     *
     * @param array<int> $user_ids
     * @return Generator<UserData>
     */
    public function getFor(array $user_ids): \Generator
    {
        $query = $this->db->query(
            'SELECT * FROM ' . self::USER_BASE_TABLE
                . " WHERE {$this->db->in('usr_id', $user_ids)}"
        );

        $prepared_query = $this->db->prepare('SELECT field_id, value FROM '
            . self::USER_VALUES_TABLE . ' WHERE usr_id = ?');

        while(($base_data = $this->db->fetchObject($query)) !== null) {
            $additional_data = $this->db->execute($prepared_query, $base_data->usr_id);
            yield new UserData(
                $base_data->usr_id,
                $base_data->login,
                new ResourceIdentification($base_data->rid),
                $base_data->firstname,
                $base_data->lastname,
                $base_data->title,
                $base_data->gender,
                $base_data->birthday,
                $base_data->institution,
                $base_data->department,
                $base_data->street,
                $base_data->city,
                $base_data->zipcode,
                $base_data->country,
                $base_data->email,
                $base_data->second_email,
                $base_data->phone_office,
                $base_data->phone_home,
                $base_data->phone_mobil,
                $base_data->fax,
                $base_data->matriculation,
                $base_data->referral_comment,
                [
                    'latitude' => $base_data->latitude,
                    'longitude' => $base_data->longitude,
                    'zoom' => $base_data->loc_zoom
                ],
                array_reduce(
                    $additional_data,
                    static function(array $c, \stdClass $v): array {
                        if (!array_key_exists($v->field_id, $c)) {
                            $c[$v->field_id] = [];
                        }
                        $c[$v->field_id][] = $v->value;
                        return $c;
                    },
                    []
                )
            );
        }

        $this->db->free($prepared_query);
    }

    public function update(UserData $user_data): void
    {
        ;
    }

    public function deleteForFieldIdentifier(string $identifier): void
    {
        $this->db->manipulate(
            'DELETE FROM ' . self::USER_VALUES_TABLE . " WHERE field_id='{$identifier}'"
        );
    }
}
