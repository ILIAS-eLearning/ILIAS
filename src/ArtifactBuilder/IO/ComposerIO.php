<?php namespace ILIAS\ArtifactBuilder\IO;

/**
 * Class ComposerIO
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ComposerIO implements IOInterface
{

    /**
     * @var \Composer\IO\IOInterface
     */
    private $composer_io;


    /**
     * ComposerIO constructor.
     *
     * @param \Composer\IO\IOInterface $composer_io
     */
    public function __construct(\Composer\IO\IOInterface $composer_io) { $this->composer_io = $composer_io; }


    /**
     * @inheritDoc
     */
    public function write(string $output) : void
    {
        $this->composer_io->write($output);
    }
}
