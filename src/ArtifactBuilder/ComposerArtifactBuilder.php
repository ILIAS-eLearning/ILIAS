<?php namespace ILIAS\ArtifactBuilder;

use Composer\Script\Event;
use ILIAS\ArtifactBuilder\Artifacts\ClassNameCollectionArtifact;
use ILIAS\ArtifactBuilder\Generators\InterfaceFinder;
use ILIAS\ArtifactBuilder\IO\ComposerIO;

/**
 * Interface ArtifactBuilder
 *
 * @package ILIAS\ArtifactBuilder
 */
class ComposerArtifactBuilder
{

    /**
     * @param Event $event
     */
    public final static function run(Event $event)
    {
        // Get all ArtifactBuilds and run them
        $current_dir = getcwd();
        $root = substr(__FILE__, 0, strpos(__FILE__, "/src"));
        chdir($root);
        require_once('./libs/composer/vendor/autoload.php');

        //
        $i = new InterfaceFinder(ArtifactBuilder::class);
        /**
         * @var $instance   ArtifactBuilder
         * @var $class_name ArtifactBuilder
         */
        foreach ($i->getMatchingClassNames() as $class_name) {
            $event->getIO()->write("running $class_name");
            $instance = $class_name::getInstance();
            $instance->injectIO(new ComposerIO($event->getIO()));
            $instance->run();
            $instance->getArtifact()->save();
        }

        /**
         * @var $class_name ArtifactBuilder
         */
        $a = new ClassNameCollectionArtifact('artifact_builders', iterator_to_array($i->getMatchingClassNames()));
        $a->save();

        $event->getIO()->write('finished');

        chdir($current_dir);
    }
}
