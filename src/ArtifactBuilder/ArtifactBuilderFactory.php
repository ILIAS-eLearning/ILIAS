<?php namespace ILIAS\ArtifactBuilder;

/**
 * Interface ArtifactBuilderFactory
 *
 * You can implement your ArtifactBuilderFactory everywhere in the ILIAS Source
 * Code (Services, Modules, src).
 *
 * All ArtifactBuilderFactories will be collected by the @see MainArtifactBuilderFactory
 *
 * @package ILIAS\ArtifactBuilder
 */
interface ArtifactBuilderFactory
{

    /**
     * @return ArtifactBuilder[] You can return as many ArtifactBuilders you want.
     *                           They will be run one by one.
     *
     * @see ArtifactBuilder
     */
    public function getArtifactBuilders() : array;
}
