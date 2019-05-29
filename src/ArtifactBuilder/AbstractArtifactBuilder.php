<?php namespace ILIAS\ArtifactBuilder;

use ILIAS\ArtifactBuilder\IO\IO;

/**
 * Interface ArtifactBuilder
 *
 * @package ILIAS\ArtifactBuilder
 */
abstract class AbstractArtifactBuilder implements ArtifactBuilder
{

    /**
     * @var IO
     */
    private $io;


    /**
     * @inheritDoc
     */
    public function io() : IO
    {
        return $this->io;
    }


    /**
     * @inheritDoc
     */
    public static function getInstance() : ArtifactBuilder
    {
        return new static();
    }


    /**
     * @inheritDoc
     */
    public function injectIO(IO $IO) : void
    {
        $this->io = $IO;
    }
}
