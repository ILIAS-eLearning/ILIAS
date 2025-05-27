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

use ILIAS\Setup;

class ilEventHandlingBuildEventInfoObjective extends Setup\Artifact\BuildArtifactObjective
{
    public function __construct(
        protected array $event_definitions
    ) {
    }

    public function getArtifactName(): string
    {
        return "event_handling_data";
    }

    private ?ilComponentRepository $component_repository = null;

    public function getPreconditions(Setup\Environment $environment): array
    {
        return [
            new ilStaticComponentRepositoryExistsObjective(),
        ];
    }

    public function achieve(Setup\Environment $environment): Setup\Environment
    {
        $this->component_repository = $environment->getResource(Setup\Environment::RESOURCE_COMPONENT_REPOSITORY);

        return parent::achieve($environment);
    }

    public function build(): Setup\Artifact
    {
        $defs = array_map(
            fn($def) => $def->toArray(),
            $this->event_definitions
        );
        return new Setup\Artifact\ArrayArtifact($defs);
    }
}
