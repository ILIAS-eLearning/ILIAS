<?php namespace ILIAS\ArtifactBuilder;

use ILIAS\ArtifactBuilder\Generators\ImplementationOfInterfaceFinder;

/**
 * Class MainArtifactBuilderFactory
 *
 * This ArtifactBuilderFactory collects all ArtifactBuilderFactories in the
 * ILIAS Code Base
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class MainArtifactBuilderFactory implements ArtifactBuilderFactory
{

    /**
     * @return ArtifactBuilder[]
     */
    public function getArtifactBuilders() : array
    {
        // Find all ArtifactBuilders
        $i = new ImplementationOfInterfaceFinder(ArtifactBuilderFactory::class);
        /**
         * @var $artifact_builder           ArtifactBuilder
         * @var $builder_factory            ArtifactBuilderFactory
         */
        $builders = [];
        foreach ($i->getMatchingClassNames() as $builder_factory) {
            if ($builder_factory === self::class) {
                continue;
            }
            $builder_factory = new $builder_factory();
            foreach ($builder_factory->getArtifactBuilders() as $artifact_builder) {
                $builders[] = $artifact_builder;
            }
        }

        return $builders;
    }
}
