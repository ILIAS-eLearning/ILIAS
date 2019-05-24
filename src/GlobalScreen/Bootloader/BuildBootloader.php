<?php namespace ILIAS\GlobalScreen\BootLoader;

use Composer\Script\Event;
use ILIAS\Collector\AbstractComposerEventHandler;
use ILIAS\Collector\AbstractComposerScript;
use ILIAS\Collector\Artifacts\ClassNameCollectionArtifact;
use ILIAS\Collector\Artifacts\Artifact;
use ILIAS\Collector\EventHandler;
use ILIAS\GlobalScreen\Scope\MainMenu\Provider\StaticMainMenuProvider;
use ILIAS\GlobalScreen\Scope\MetaBar\Provider\StaticMetaBarProvider;
use ILIAS\GlobalScreen\Scope\Tool\Provider\DynamicToolProvider;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

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
                    $services = new InterfaceFinder($interface, "./Services");
                    $modules = new InterfaceFinder($interface, "./Modules");
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
                return new ClassNameCollectionArtifact("global_screen_bootloader", $this->class_names);
            }
        };
    }
}



