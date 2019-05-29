<?php namespace ILIAS\GlobalScreen\BootLoader;

use ILIAS\ArtifactBuilder\AbstractArtifactBuilder;
use ILIAS\ArtifactBuilder\ArtifactBuilder;
use ILIAS\ArtifactBuilder\Artifacts\Artifact;
use ILIAS\ArtifactBuilder\Artifacts\ClassNameCollectionArtifact;
use ILIAS\ArtifactBuilder\Generators\InterfaceFinder;
use ILIAS\GlobalScreen\Scope\MainMenu\Provider\StaticMainMenuProvider;
use ILIAS\GlobalScreen\Scope\MetaBar\Provider\StaticMetaBarProvider;
use ILIAS\GlobalScreen\Scope\Tool\Provider\DynamicToolProvider;

/**
 * Class BuildBootLoader
 *
 * @package ILIAS\GlobalScreen\BootLoader
 */
class BuildBootLoader extends AbstractArtifactBuilder implements ArtifactBuilder
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
        ];

        foreach ($i as $interface) {
            $this->io()->write("Checking Interface $interface");

            $i = new InterfaceFinder($interface);
            $this->class_names[$interface] = iterator_to_array($i->getMatchingClassNames());
        }
    }


    /**
     * @inheritDoc
     */
    public function getArtifact() : Artifact
    {
        $this->io()->write("Storing classnames to global_screen_providers.php");

        return new ClassNameCollectionArtifact("global_screen_providers", $this->class_names);
    }
}



