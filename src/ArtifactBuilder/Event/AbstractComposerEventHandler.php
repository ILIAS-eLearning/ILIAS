<?php namespace ILIAS\ArtifactBuilder;

use Composer\Script\Event as ComposerEvent;
use ILIAS\ArtifactBuilder\Event\ComposerEventWrapper;
use ILIAS\ArtifactBuilder\Event\EventHandler;
use ILIAS\ArtifactBuilder\Caller\EventWrapper;
use ILIAS\ArtifactBuilder\IO\ComposerIO;
use ILIAS\ArtifactBuilder\IO\IO;

/**
 * Class AbstractComposerEventHandler
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
abstract class AbstractComposerEventHandler implements EventHandler
{

    /**
     * @var
     */
    private $io;
    /**
     * @var EventWrapper
     */
    private $event;


    /**
     * AbstractComposerEventHandler constructor.
     *
     * @param ComposerEvent $composer_event
     */
    public function __construct(ComposerEvent $composer_event)
    {
        $this->event = new ComposerEventWrapper($composer_event);
        $this->io = new ComposerIO($composer_event->getIO());
    }


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
    public function getEvent() : EventWrapper
    {
        return $this->event;
    }
}
