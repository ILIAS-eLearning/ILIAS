<?php

use ILIAS\ArtifactBuilder\ArtifactBuilderFactory;

/**
 * Class ilGSArtifactBuilderFactory
 *
 * @package ILIAS\GlobalScreen\BootLoader
 */
class ilGSArtifactBuilderFactory implements ArtifactBuilderFactory
{

    /**
     * @inheritDoc
     */
    public function getArtifactBuilders() : array
    {
        return [new ilGSBootLoaderBuilder()];
    }
}



