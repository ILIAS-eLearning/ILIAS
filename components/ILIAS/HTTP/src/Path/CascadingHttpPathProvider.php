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
 * Asks the given providers in order and returns the first http path one of them knows.
 *
 * @author Fabian Schmid <fabian@sr.solutions>
 */
final class CascadingHttpPathProvider implements HttpPathProvider
{
    /**
     * @var HttpPathProvider[]
     */
    private array $providers;

    public function __construct(HttpPathProvider ...$providers)
    {
        $this->providers = $providers;
    }

    public function getHttpPath(): ?URI
    {
        foreach ($this->providers as $provider) {
            $http_path = $provider->getHttpPath();
            if ($http_path !== null) {
                return $http_path;
            }
        }
        return null;
    }
}
