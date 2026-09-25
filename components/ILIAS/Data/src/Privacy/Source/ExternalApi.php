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

/**
 * The value was received from an external system (LDAP, Shibboleth,
 * SOAP/XML import, ...).
 */
final readonly class ExternalApi implements Source
{
    /**
     * @param string $service e.g. "shibboleth", "ldap", "xml_import"
     * @param string $field   the attribute/field name in that system
     */
    public function __construct(
        private string $service,
        private string $field,
    ) {
    }

    public function getService(): string
    {
        return $this->service;
    }

    public function getField(): string
    {
        return $this->field;
    }

    public function describe(): string
    {
        return "api:{$this->service}.{$this->field}";
    }
}
