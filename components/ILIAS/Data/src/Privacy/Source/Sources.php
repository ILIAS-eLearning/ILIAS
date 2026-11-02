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

namespace ILIAS\Data\Privacy\Source;

use ILIAS\Data\Privacy\Source\Known\UserSources;

/**
 * Factory for the available sources, including the catalogue of the user
 * account (e.g. `$sources->user()->postalAddress()`).
 *
 * A stateless factory: create it with `new` or inject it.
 *
 * Deliberately not offered here: ad-hoc DbTableColumn(s) construction —
 * database columns are named in a {@see SourceCatalogue}.
 */
class Sources
{
    /**
     * The personal data columns of the user account, which all components
     * share. Every other component keeps its own catalogue.
     */
    public function user(): UserSources
    {
        return new UserSources();
    }

    /**
     * @param string $context e.g. "profile_form", "registration_form"
     */
    public function userInput(string $context): UserInput
    {
        return new UserInput($context);
    }

    /**
     * @param string $service e.g. "shibboleth", "ldap", "xml_import"
     * @param string $field   the attribute/field name in that system
     */
    public function externalApi(string $service, string $field): ExternalApi
    {
        return new ExternalApi($service, $field);
    }
}
