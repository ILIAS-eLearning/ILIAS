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

namespace ILIAS\HTTP\Path;

use ILIAS\Data\URI;

/**
 * The http_path configured in the ilias.ini.
 *
 * @author Fabian Schmid <fabian@sr.solutions>
 */
final class IniHttpPathProvider implements HttpPathProvider
{
    use StringToHttpPath;

    public function __construct(
        private \ilIniFile $ilias_ini
    ) {
    }

    public function getHttpPath(): ?URI
    {
        return $this->toHttpPath((string) $this->ilias_ini->readVariable('server', 'http_path'));
    }
}
