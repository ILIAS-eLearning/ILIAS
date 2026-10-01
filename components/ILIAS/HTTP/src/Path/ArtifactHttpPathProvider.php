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
use ILIAS\HTTP\Setup\HttpPathArtifactObjective;

/**
 * The http_path the setup stored as artifact, readable in every context without
 * any initialisation of ILIAS.
 *
 * @author Fabian Schmid <fabian@sr.solutions>
 */
final class ArtifactHttpPathProvider implements HttpPathProvider
{
    use StringToHttpPath;

    private bool $read = false;
    private ?URI $http_path = null;

    public function __construct(
        private ?string $artifact_path = null
    ) {
    }

    public function getHttpPath(): ?URI
    {
        if ($this->read) {
            return $this->http_path;
        }
        $this->read = true;

        $path = $this->artifact_path ?? HttpPathArtifactObjective::PATH();
        if (!is_readable($path)) {
            return null;
        }
        $data = require $path;

        return $this->http_path = $this->toHttpPath(
            is_array($data) ? ($data[HttpPathArtifactObjective::KEY] ?? null) : null
        );
    }
}
