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

interface DataRepository
{
    /**
     * @return array<string, string|null>
     */
    public function getFor(int $user_id): array;
    public function deleteFor(int $user_id): void;
    public function deleteSingleFor(int $user_id, string $key): void;
    public function storeFor(int $user_id, array $settings_array): void;
    public function storeSingleFor(
        int $user_id,
        string $key,
        string $value
    ): void;
    public function getSearchSelectConditionalOnVisibility(
        string $profile_data_table_name,
        string $login_data_column_name,
        string $firstname_data_column_name,
        string $lastname_data_column_name,
        string $primary_email_data_column_name,
        string $secondary_email_data_column_name
    ): string;
}
