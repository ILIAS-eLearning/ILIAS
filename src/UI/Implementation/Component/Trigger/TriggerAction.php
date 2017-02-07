<?php


namespace ILIAS\UI\Implementation\Component\Trigger;

use Doctrine\Instantiator\Exception\InvalidArgumentException;

/**
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 */
class TriggerAction implements \ILIAS\UI\Component\Trigger\TriggerAction
{

    /**
     * @var \ILIAS\UI\Component\Component
     */
    protected $component;

    /**
     * @var string
     */
    protected $event = 'click';

    /**
     * @var \Closure
     */
    protected $js_binding;

    /**
     * @var array
     */
    protected static $events = array('click', 'hover', 'change', 'dblclick');

    /**
     * @param \ILIAS\UI\Component\Component $component
     * @param string $event
     */
    public function __construct(\ILIAS\UI\Component\Component $component, $event = 'click')
    {
        $this->component = $component;
        $this->setEvent($event);
    }


    /**
     * @inheritdoc
     */
    public function getComponent()
    {
        return $this->component;
    }


    /**
     * @inheritdoc
     */
    public function getEvent()
    {
        return $this->event;
    }


    /**
     * @inheritdoc
     */
    public function setEvent($event)
    {
        if (!in_array($event, static::$events)) {
            throw new InvalidArgumentException("$event is not supported, use one of " . implode(', ', static::$events));
        }
        $this->event = $event;
    }


    /**
     * @inheritdoc
     */
    public function setJavascriptBinding(\Closure $closure)
    {
        $this->js_binding = $closure;
    }


    /**
     * @inheritdoc
     */
    public function getJavascriptBinding()
    {
        return $this->js_binding;
    }

}