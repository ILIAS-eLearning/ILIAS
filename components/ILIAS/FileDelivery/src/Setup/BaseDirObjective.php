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

namespace ILIAS\FileDelivery\Setup;

use ILIAS\Setup\Environment;
use ILIAS\Setup\Artifact;
use ILIAS\Setup\Artifact\ArrayArtifact;
use ILIAS\Setup\Artifact\BuildArtifactObjective;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class BaseDirObjective extends BuildArtifactObjective
{
    public const BASE_DIR = './src/FileDelivery/artifacts/base_dir.php';

    protected array $data = [];

    public function getArtifactPath(): string
    {
        return self::BASE_DIR;
    }

    #[\Override]
    public function buildIn(Environment $env): Artifact
    {
        $ilias_ini = $env->getResource(Environment::RESOURCE_ILIAS_INI);
        if ($ilias_ini instanceof \ilIniFile) {
            $base_dir = $ilias_ini->readVariable('clients', 'datadir');
            $client_id = $ilias_ini->readVariable('clients', 'default');
            if (!empty($base_dir) && !empty($client_id)) {
                $this->data['base_dir'] = rtrim(rtrim($base_dir, '/') . '/' . $client_id, '/') . '/';
            }
        }
        return $this->build();
    }

    public function getArtifactName(): string
    {
        return 'base_dir';
    }

    public function build(): Artifact
    {
        return new ArrayArtifact($this->data);
    }

    #[\Override]
    public function getPreconditions(Environment $environment): array
    {
        return [
            new \ilIniFilesLoadedObjective(),
        ];
    }

    public static function get(): ?string
    {
        static $base;

        if ($base !== null) {
            return $base;
        }

        if (is_readable(self::PATH())) {
            $data = require self::PATH();
        }

        return $base = $data['base_dir'] ?? null;
    }
}
