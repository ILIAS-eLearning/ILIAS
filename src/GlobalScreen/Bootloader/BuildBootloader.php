<?php namespace ILIAS\GlobalScreen\BootLoader;

use Composer\Script\Event;
use ILIAS\ArtifactBuilder\AbstractComposerEventHandler;
use ILIAS\ArtifactBuilder\AbstractComposerScript;
use ILIAS\ArtifactBuilder\Artifacts\Artifact;
use ILIAS\ArtifactBuilder\Artifacts\ClassNameCollectionArtifact;
use ILIAS\ArtifactBuilder\EventHandler;
use ILIAS\GlobalScreen\Scope\MainMenu\Provider\StaticMainMenuProvider;
use ILIAS\GlobalScreen\Scope\MetaBar\Provider\StaticMetaBarProvider;
use ILIAS\GlobalScreen\Scope\Tool\Provider\DynamicToolProvider;

/**
 * Class BuildBootLoader
 *
 * @package ILIAS\GlobalScreen\BootLoader
 */
class BuildBootLoader extends AbstractComposerScript
{

    /**
     * @inheritDoc
     */
    protected static function getEventHandler(Event $event) : EventHandler
    {

        return new class($event) extends AbstractComposerEventHandler implements EventHandler
        {

            /**
             * @var array
             */
            protected $class_names = [];


            /**
             * @inheritDoc
             */
            public function run() : void
            {
                $i = [
                    StaticMainMenuProvider::class,
                    StaticMetaBarProvider::class,
                    DynamicToolProvider::class,
                ];

                foreach ($i as $interface) {
                    $this->io()->write("Check ./Services for Interface {$interface}");
                    $services = new InterfaceFinder($interface, "./Services");
                    $this->io()->write("Check ./Modules for Interface {$interface}");
                    $modules = new InterfaceFinder($interface, "./Modules");
                    $this->io()->write("Check ./src for Interface {$interface}");
                    $src = new InterfaceFinder($interface, "./src");
                    $this->class_names[$interface] = array_merge(
                        $services->getMatchingClassNames(),
                        $modules->getMatchingClassNames(),
                        $src->getMatchingClassNames()
                    );
                }
            }


            /**
             * @inheritDoc
             */
            public function getArtifact() : Artifact
            {
                $this->io()->write("Storing classnames to global_screen_bootloader.php");

                return new ClassNameCollectionArtifact("global_screen_bootloader", $this->class_names);
            }
        };
    }
}



