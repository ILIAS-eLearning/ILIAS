<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Stopwatch;

/**
 * Stopwatch section.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class Section
{
    /**
     * @var StopwatchEvent[]
     */
    private $events = [];

    /**
     * @var float|null
     */
    private $origin;

    /**
     * @var bool
     */
    private $morePrecision;

    /**
     * @var string
     */
    private $id;

    /**
     * @var Section[]
     */
    private $children = [];

    /**
     * @param float|null $origin        Set the origin of the events in this section, use null to set their origin to their start time
     * @param bool       $morePrecision If true, time is stored as float to keep the original microsecond precision
     */
    public function __construct($origin = null, $morePrecision = false)
    {
        $this->origin = is_numeric($origin) ? $origin : null;
        $this->morePrecision = $morePrecision;
    }

    /**
     * Returns the child section.
     *
     * @param string $id The child section identifier
     *
     * @return self|null The child section or null when none found
     */
    public function get($id)
    {
        foreach ($this->children as $child) {
            if ($id === $child->getId()) {
                return $child;
            }
        }

        return null;
    }

    /**
     * Creates or re-opens a child section.
     *
     * @param string|null $id Null to create a new section, the identifier to re-open an existing one
     *
     * @return self
     */
    public function open($id)
    {
        if (null === $session = $this->get($id)) {
            $session = $this->children[] = new self(microtime(true) * 1000, $this->morePrecision);
        }

        return $session;
    }

    /**
     * @return string The identifier of the section
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Sets the session identifier.
     *
     * @param string $id The session identifier
     *
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Starts an event.
     *
     * @param string      $name     The event name
     * @param string|null $category The event category
     *
     * @return StopwatchEvent The event
     */
    public function startEvent($name, $category)
    {
        if (!isset($this->events[$name])) {
            $this->events[$name] = new StopwatchEvent($this->origin ?: microtime(true) * 1000, $category, $this->morePrecision);
        }

        return $this->events[$name]->start();
    }

    /**
     * Checks if the event was started.
     *
     * @param string $name The event name
     *
     * @return bool
     */
    public function isEventStarted($name)
    {
        return isset($this->events[$name]) && $this->events[$name]->isStarted();
    }

    /**
     * Stops an event.
     *
     * @param string $name The event name
     *
     * @return StopwatchEvent The event
     *
     * @throws \LogicException When the event has not been started
     */
    public function stopEvent($name)
    {
        if (!isset($this->events[$name])) {
            throw new \LogicException(sprintf('Event "%s" is not started.', $name));
        }

        return $this->events[$name]->stop();
    }

    /**
     * Stops then restarts an event.
     *
     * @param string $name The event name
     *
     * @return StopwatchEvent The event
     *
     * @throws \LogicException When the event has not been started
     */
    public function lap($name)
    {
        return $this->stopEvent($name)->start();
    }

    /**
     * Returns a specific event by name.
     *
     * @param string $name The event name
     *
     * @return StopwatchEvent The event
     *
     * @throws \LogicException When the event is not known
     */
    public function getEvent($name)
    {
        if (!isset($this->events[$name])) {
            throw new \LogicException(sprintf('Event "%s" is not known.', $name));
        }

        return $this->events[$name];
    }

    /**
     * Returns the events from this section.
     *
     * @return StopwatchEvent[] An array of StopwatchEvent instances
     */
    public function getEvents()
    {
        return $this->events;
    }
}
