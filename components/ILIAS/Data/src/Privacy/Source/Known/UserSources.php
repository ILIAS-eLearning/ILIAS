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
use ILIAS\Data\Privacy\Source\SourceCatalogue;

/**
 * Catalogue of the personal data columns in usr_data. Entries are added
 * together with the privacy data type that needs them.
 *
 * Use these named getters instead of instantiating {@see DbTableColumn}
 * with string literals (checked by the PreferKnownSourcesRule PHPStan
 * rule). Available via {@see \ILIAS\Data\Privacy\Source\Sources::user()}.
 */
readonly class UserSources implements SourceCatalogue
{
    private const string TABLE = 'usr_data';

    /**
     * The residential address as one compound value.
     */
    public function postalAddress(): DbTableColumns
    {
        return new DbTableColumns(self::TABLE, 'street', 'city', 'zipcode', 'country');
    }

    /**
     * The name of a person as one compound value.
     */
    public function personName(): DbTableColumns
    {
        return new DbTableColumns(self::TABLE, 'firstname', 'lastname', 'title');
    }

    /**
     * Institution and department as one compound value.
     */
    public function affiliation(): DbTableColumns
    {
        return new DbTableColumns(self::TABLE, 'institution', 'department');
    }

    /**
     * The location a person entered for themselves.
     */
    public function geoLocation(): DbTableColumns
    {
        return new DbTableColumns(self::TABLE, 'latitude', 'longitude', 'loc_zoom');
    }

    public function login(): DbTableColumn
    {
        return new DbTableColumn(self::TABLE, 'login');
    }
}
