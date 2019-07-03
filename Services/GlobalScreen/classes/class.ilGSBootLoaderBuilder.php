<?php

use ILIAS\ArtifactBuilder\Artifact\ArrayToFileArtifact;
use ILIAS\ArtifactBuilder\Artifact\Artifact;
use ILIAS\ArtifactBuilder\ArtifactBuilder;
use ILIAS\ArtifactBuilder\Generators\ImplementationOfInterfaceFinder;
use ILIAS\GlobalScreen\Scope\Layout\Provider\FinalModificationProvider;
use ILIAS\GlobalScreen\Scope\MainMenu\Provider\StaticMainMenuProvider;
use ILIAS\GlobalScreen\Scope\MetaBar\Provider\StaticMetaBarProvider;
use ILIAS\GlobalScreen\Scope\Tool\Provider\DynamicToolProvider;

/**
 * Class ilGSBootLoaderBuilder
 *
 * @package ILIAS\GlobalScreen\BootLoader
 */
class ilGSBootLoaderBuilder implements ArtifactBuilder
{

    /**
     * @var array
     */
    protected $class_names = [];


    public function run() : void
    {
        $i = [
            StaticMainMenuProvider::class,
            StaticMetaBarProvider::class,
            DynamicToolProvider::class,
            FinalModificationProvider::class,
        ];

        foreach ($i as $interface) {
            $i = new ImplementationOfInterfaceFinder($interface);
            $this->class_names[$interface] = iterator_to_array($i->getMatchingClassNames());
        }
    }


    /**
     * @inheritDoc
     */
    public function getArtifact() : Artifact
    {
        return new ArrayToFileArtifact("Services/GlobalScreen/artifacts", "global_screen_providers", $this->class_names);
    }
}



