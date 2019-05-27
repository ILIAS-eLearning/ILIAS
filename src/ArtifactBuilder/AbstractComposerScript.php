<?php namespace ILIAS\ArtifactBuilder;

use Composer\Script\Event;
use ILIAS\ArtifactBuilder\Event\EventHandler;

/**
 * Class AbstractComposerScript
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
abstract class AbstractComposerScript
{

    /**
     * @param Event $event
     */
    public final static function handleEvent(Event $event)
    {
        $event_handler = static::getEventHandler($event);
        $event_handler->run();
        $event_handler->getArtifact()->save();
    }


    /**
     * @param Event $event
     *
     * @return EventHandler
     */
    abstract protected static function getEventHandler(Event $event) : EventHandler;
}
