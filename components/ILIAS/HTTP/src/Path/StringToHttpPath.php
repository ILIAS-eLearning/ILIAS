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
 * @author Fabian Schmid <fabian@sr.solutions>
 */
trait StringToHttpPath
{
    private function toHttpPath(?string $http_path): ?URI
    {
        if ($http_path === null || trim($http_path) === '') {
            return null;
        }
        try {
            return new URI(rtrim(trim($http_path), '/'));
        } catch (\InvalidArgumentException|\TypeError) {
            return null;
        }
    }
}
