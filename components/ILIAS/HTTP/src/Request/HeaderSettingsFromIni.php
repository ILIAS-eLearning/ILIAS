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

namespace ILIAS\HTTP\Request;

use ILIAS\Environment\Configuration\Installation\IliasIni;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class HeaderSettingsFromIni implements HeaderSettings
{
    public function __construct(
        private IliasIni $ilias_ini
    ) {
    }

    public function isHTTPSDetectionEnabled(): bool
    {
        return $this->ilias_ini->isAutoHttpsDetectEnabled();
    }

    public function getHTTPDetectionHeaderName(): ?string
    {
        return $this->ilias_ini->getAutoHttpsDetectHeaderName();
    }

    public function getHTTPDetectionHeaderValue(): ?string
    {
        return $this->ilias_ini->getAutoHttpsDetectHeaderValue();
    }
}
