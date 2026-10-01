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

namespace ILIAS\HTTP\Setup;

use ILIAS\Setup\Artifact\ArrayArtifact;
use ILIAS\Setup\Environment;
use ILIAS\Setup\Objective;

/**
 * Stores the http_path of the ilias.ini as artifact, so it can be read in every
 * context without initialising ILIAS, see \ILIAS\HTTP\Path\ArtifactHttpPathProvider.
 *
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class HttpPathArtifactObjective implements Objective
{
    public const KEY = 'http_path';

    public static function PATH(): string
    {
        return realpath(__DIR__ . '/../../../../../public/data/') . '/http_path.php';
    }

    public function getHash(): string
    {
        return hash('sha256', self::class);
    }

    public function getLabel(): string
    {
        return 'Build http_path Static Config';
    }

    public function isNotable(): bool
    {
        return true;
    }

    public function getPreconditions(Environment $environment): array
    {
        return [
            new \ilIniFilesLoadedObjective(),
        ];
    }

    public function achieve(Environment $environment): Environment
    {
        $path = self::PATH();
        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }
        file_put_contents($path, $this->build($environment)->serialize());

        return $environment;
    }

    public function isApplicable(Environment $environment): bool
    {
        return true;
    }

    public function build(Environment $environment): ArrayArtifact
    {
        $ilias_ini = $environment->getResource(Environment::RESOURCE_ILIAS_INI);
        $http_path = $ilias_ini instanceof \ilIniFile
            ? rtrim((string) $ilias_ini->readVariable('server', 'http_path'), '/')
            : '';

        return new ArrayArtifact($http_path === '' ? [] : [self::KEY => $http_path]);
    }
}
