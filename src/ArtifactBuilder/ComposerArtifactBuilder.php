<?php namespace ILIAS\ArtifactBuilder;

use Composer\Script\Event;
use ILIAS\ArtifactBuilder\Artifact\ArrayToFileArtifact;
use ILIAS\ArtifactBuilder\Generators\ImplementationOfInterfaceFinder;

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

        // Find all ArtifactBuilders
        $i = new ImplementationOfInterfaceFinder(ArtifactBuilder::class);
        /**
         * @var $instance   ArtifactBuilder
         * @var $class_name ArtifactBuilder
         */
        foreach ($i->getMatchingClassNames() as $class_name) {
            $instance = new $class_name();
            $instance->run();
            $instance->getArtifact()->save();
        }

        /**
         * @var $class_name ArtifactBuilder
         */
        $a = new ArrayToFileArtifact("src/ArtifactBuilder", 'artifact_builders', iterator_to_array($i->getMatchingClassNames()));
        // $a->save();

        chdir($current_dir);
    }
}
