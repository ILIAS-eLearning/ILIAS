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

use ILIAS\Data\Privacy\Source\Known\KnownSources;

/**
 * Factory for the available sources, including the catalogue of known
 * ILIAS personal data columns inherited from {@see KnownSources}
 * (e.g. `$sources->user()->postalAddress()`).
 *
 * Obtain it via {@see \ILIAS\Data\Privacy\Services::sources()}, via
 * `$pull[Sources::class]` in a component bootstrap, or via
 * `$DIC[Sources::class]` in legacy code.
 *
 * Deliberately not offered here: ad-hoc DbTableColumn(s) construction —
 * database columns must be registered in the KnownSources catalogue.
 */
class Sources extends KnownSources
{
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

    public function sessionData(string $key): SessionData
    {
        return new SessionData($key);
    }

    /**
     * Never use this in new code — state the real source instead.
     */
    public function legacy(string $hint = 'unclassified'): LegacySource
    {
        return new LegacySource($hint);
    }
}
