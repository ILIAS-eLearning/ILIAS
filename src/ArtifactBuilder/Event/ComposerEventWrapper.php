<?php namespace ILIAS\ArtifactBuilder\Event;

use Composer\Script\Event;
use ILIAS\ArtifactBuilder\Caller\EventWrapper;

/**
 * Interface Event
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ComposerEventWrapper implements EventWrapper
{

    /**
     * @var string
     */
    protected $name;


    /**
     * ComposerEventWrapper constructor.
     *
     * @param Event $composer_event
     */
    public function __construct(Event $composer_event)
    {
        $this->name = $composer_event->getName();
    }


    /**
     * @return string
     */
    public function getName() : string
    {
        return $this->name;
    }
}
