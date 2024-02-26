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
        $processor = new \ilEventHandlingDefinitionProcessor();

        // Plugins behave slightly differently from core components: they do not always have a plugin.xml and the
        // `ilComponentDefinitionReader` does not read them at all. Therefore, we overwrite the reader at this point
        // and supplement it with the information from the existing plugins.

        $plugin_and_components_reader = new class ($this->component_repository, $processor) extends
            \ilComponentDefinitionReader {
            public function __construct(
                private ilComponentRepository $component_repository,
                ilComponentDefinitionProcessor ...$processor,
            ) {
                parent::__construct(...$processor);
            }

            protected function getComponents(): \Iterator
            {
                yield from parent::getComponents();

                foreach ($this->component_repository->getPlugins() as $plugin) {
                    $xml_plugin_path = $plugin->getPath() . '/plugin.xml';
                    if (!file_exists($xml_plugin_path)) {
                        continue;
                    }
                    yield [
                        'Plugins', // Plugins are generally handled in ilAppEventHandler with the prefix "Plugins".
                        $plugin->getName(),
                        $xml_plugin_path,
                    ];
                }
            }
        };

        $plugin_and_components_reader->purge();
        $plugin_and_components_reader->readComponentDefinitions();

        return new Setup\Artifact\ArrayArtifact($processor->getData());
    }
}
