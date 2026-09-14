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

namespace ILIAS\Data\Privacy\Source\Known;

use ILIAS\Data\Privacy\Source\DbTableColumn;
use ILIAS\Data\Privacy\Source\DbTableColumns;

/**
 * Catalogue of known personal data columns in usr_data.
 *
 * Use these named getters instead of instantiating {@see DbTableColumn}
 * with string literals (enforced by the PreferKnownSourcesRule PHPStan
 * rule). Obtain the instance via
 * {@see \ILIAS\Data\Privacy\Services::sources()}.
 */
final readonly class UserSources
{
    private const string TABLE = 'usr_data';

    /**
     * The residential address as one compound value.
     */
    public function postalAddress(): DbTableColumns
    {
        return new DbTableColumns(self::TABLE, 'street', 'city', 'zipcode', 'country');
    }

    public function userId(): DbTableColumn
    {
        return new DbTableColumn(self::TABLE, 'usr_id');
    }

    public function login(): DbTableColumn
    {
        return new DbTableColumn(self::TABLE, 'login');
    }

    public function externalAccount(): DbTableColumn
    {
        return new DbTableColumn(self::TABLE, 'ext_account');
    }

    public function email(): DbTableColumn
    {
        return new DbTableColumn(self::TABLE, 'email');
    }

    public function secondEmail(): DbTableColumn
    {
        return new DbTableColumn(self::TABLE, 'second_email');
    }

    public function phoneOffice(): DbTableColumn
    {
        return new DbTableColumn(self::TABLE, 'phone_office');
    }

    public function phoneHome(): DbTableColumn
    {
        return new DbTableColumn(self::TABLE, 'phone_home');
    }

    public function phoneMobile(): DbTableColumn
    {
        return new DbTableColumn(self::TABLE, 'phone_mobile');
    }

    public function fax(): DbTableColumn
    {
        return new DbTableColumn(self::TABLE, 'fax');
    }

    public function firstname(): DbTableColumn
    {
        return new DbTableColumn(self::TABLE, 'firstname');
    }

    public function lastname(): DbTableColumn
    {
        return new DbTableColumn(self::TABLE, 'lastname');
    }

    public function title(): DbTableColumn
    {
        return new DbTableColumn(self::TABLE, 'title');
    }

    public function gender(): DbTableColumn
    {
        return new DbTableColumn(self::TABLE, 'gender');
    }

    public function institution(): DbTableColumn
    {
        return new DbTableColumn(self::TABLE, 'institution');
    }

    public function department(): DbTableColumn
    {
        return new DbTableColumn(self::TABLE, 'department');
    }

    public function street(): DbTableColumn
    {
        return new DbTableColumn(self::TABLE, 'street');
    }

    public function city(): DbTableColumn
    {
        return new DbTableColumn(self::TABLE, 'city');
    }

    public function zipcode(): DbTableColumn
    {
        return new DbTableColumn(self::TABLE, 'zipcode');
    }

    public function country(): DbTableColumn
    {
        return new DbTableColumn(self::TABLE, 'country');
    }

    public function birthday(): DbTableColumn
    {
        return new DbTableColumn(self::TABLE, 'birthday');
    }

    public function hobby(): DbTableColumn
    {
        return new DbTableColumn(self::TABLE, 'hobby');
    }

    public function matriculation(): DbTableColumn
    {
        return new DbTableColumn(self::TABLE, 'matriculation');
    }

    public function clientIp(): DbTableColumn
    {
        return new DbTableColumn(self::TABLE, 'client_ip');
    }

    public function lastLogin(): DbTableColumn
    {
        return new DbTableColumn(self::TABLE, 'last_login');
    }

    public function lastPasswordChange(): DbTableColumn
    {
        return new DbTableColumn(self::TABLE, 'last_password_change');
    }
}
