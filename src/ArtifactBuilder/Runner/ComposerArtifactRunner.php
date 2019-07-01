<?php namespace ILIAS\ArtifactBuilder\Runner;

use Composer\Script\Event;
use ILIAS\ArtifactBuilder\MainArtifactBuilderFactory;

/**
 * Class ComposerArtifactRunner
 *
 * This class is called whenever `composer install` or `composer dump-autoload`
 * is called. It runs every ArtifactBuilder which is found in the ILIAS CodeBase.
 *
 * @package ILIAS\ArtifactBuilder
 */
class ComposerArtifactRunner
{

    public final static function run()
    {
        // Get all ArtifactBuilds and run them
        $current_dir = getcwd();
        $root = substr(__FILE__, 0, strpos(__FILE__, "/src"));
        chdir($root);
        require_once('./libs/composer/vendor/autoload.php');

        $f = new MainArtifactBuilderFactory();

        foreach ($f->getArtifactBuilders() as $artifact_builder) {
            $artifact_builder->run();
            $artifact_builder->getArtifact()->save();
        }

        chdir($current_dir);
    }
}
