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

namespace ILIAS\User\Profile;

use ILIAS\User\Settings\DataRepository as SettingsDataRepository;
use ILIAS\User\Profile\Fields\ConfigurationRepository as ProfileFieldsConfigurationRepository;
use ILIAS\User\Search\AutocompleteQuery;

interface DataRepository
{
    public function getDefault(): Data;
    public function getSingle(int $id): Data;
    /**
     * @param list<int> $user_ids
     * @return \Generator<int, Data>
     */
    public function getMultiple(array $user_ids): \Generator;
    public function store(Data $user_data): void;
    public function deleteForFieldIdentifier(string $identifier): void;
    public function deleteForUser(int $usr_id): void;
    public function storePasswordFor(
        int $usr_id,
        string $password,
        string $encoding_type,
        ?string $salt
    ): void;
    public function storeLoginFor(
        int $usr_id,
        string $login
    ): void;
    public function storeLastVisitedFor(
        int $usr_id,
        array $last_visited
    ): void;
    /**
     * @return list<\ILIAS\User\Search\AutocompleteItem>
     */
    public function searchUsers(
        SettingsDataRepository $settings_data_repository,
        ProfileFieldsConfigurationRepository $profile_fields_config_repo,
        AutocompleteQuery $search_term
    ): array;
    public function getProfileDataQuery(array $select_fields): DataQuery;
    /**
     * @return array{cnt: int, set: list<array<string, mixed>>}
     */
    public function getCountAndRecordsForQuery(
        DataQuery $query,
        int $offset,
        int $limit
    ): array;
}
