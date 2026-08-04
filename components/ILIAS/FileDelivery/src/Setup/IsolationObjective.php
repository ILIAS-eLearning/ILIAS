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

use ILIAS\Setup\Artifact;
use ILIAS\Setup\Artifact\ArrayArtifact;
use ILIAS\Setup\Environment;
use ILIAS\Setup\UnachievableException;
use ILIAS\FileDelivery\Isolation\IsolationConfig;

/**
 * Writes the IRSS User Content Isolation settings to a static PHP artefact
 * so the FileDelivery runtime can read them without a DB connection.
 *
 * The ILIAS domain is not part of the admin config: it is derived from the
 * installed http_path at build time and baked into the artefact. The runtime
 * therefore never reads ilias.ini.php to obtain it.
 *
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class IsolationObjective extends BuildStaticConfigStoredObjective
{
    public function __construct(
        private readonly bool $activated = false,
        private readonly ?string $content_domain = null,
    ) {
    }

    public function getArtifactName(): string
    {
        return 'isolation';
    }

    #[\Override]
    public function getPreconditions(Environment $environment): array
    {
        // ilias.ini.php must be loaded as a resource so we can read http_path;
        // and http_path must have been written into it before we read it.
        $preconditions = [new \ilIniFilesLoadedObjective()];
        if ($environment->hasConfigFor('http')) {
            $preconditions[] = new \ilHttpConfigStoredObjective($environment->getConfigFor('http'));
        }
        return $preconditions;
    }

    #[\Override]
    public function buildIn(Environment $env): Artifact
    {
        $ilias_domain = $this->readIliasDomain($env);

        if ($this->activated
            && $this->content_domain !== null
            && $ilias_domain !== null
            && strcasecmp(
                (string) parse_url($this->content_domain, PHP_URL_HOST),
                (string) parse_url($ilias_domain, PHP_URL_HOST)
            ) === 0
        ) {
            throw new UnachievableException(
                'IRSS User Content Isolation requires the content domain to differ from the '
                . 'ILIAS domain (http_path); serving user content from the same host defeats '
                . 'the isolation.'
            );
        }

        return new ArrayArtifact([
            IsolationConfig::KEY_ACTIVATED => $this->activated,
            IsolationConfig::KEY_CONTENT_DOMAIN => $this->content_domain,
            IsolationConfig::KEY_ILIAS_DOMAIN => $ilias_domain,
        ]);
    }

    public function build(): Artifact
    {
        // Used only when no Environment is available (e.g. tests). The ILIAS
        // domain is resolved in buildIn() from http_path, so it stays null here.
        return new ArrayArtifact([
            IsolationConfig::KEY_ACTIVATED => $this->activated,
            IsolationConfig::KEY_CONTENT_DOMAIN => $this->content_domain,
            IsolationConfig::KEY_ILIAS_DOMAIN => null,
        ]);
    }

    /**
     * Read the installed http_path from ilias.ini.php and reduce it to a bare
     * origin to be used as the CORS allow-origin of asset responses.
     */
    private function readIliasDomain(Environment $env): ?string
    {
        $ini = $env->getResource(Environment::RESOURCE_ILIAS_INI);
        if (!$ini instanceof \ilIniFile) {
            return null;
        }

        return IsolationConfig::originFromUrl(
            $ini->readVariable('server', 'http_path') ?: null
        );
    }
}
