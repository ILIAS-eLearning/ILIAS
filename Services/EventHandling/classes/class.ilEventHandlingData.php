<?php

interface ilEventHandlingData
{
    /**
     * Check if an event exists
     */
    public function hasEvent(string $component, string $type, string $type_specification) : bool;

    /**
     * Get the event with the given component, type and type specification
     * @throws \InvalidArgumentException if event does not exist
     */
    public function getEvent(string $component, string $type, string $type_specification) : array;

    /**
     * Get all events of the given type
     * @throws \InvalidArgumentException if no events of this type exist
     */
    public function getEventsByType(string $type) : Iterator;

    /**
     * Get the name of a component for which an event exists. The component name is prefixed with either Modules/ or Services/
     * @throws \InvalidArgumentException if component does not exist
     */
    public function getEventComponent(string $id) : string;

    /**
     * Get the type of an Event.
     * @throws \InvalidArgumentException if component does not exist
     */
    public function getEventType(string $id) : string;

    /**
     * Get the specification of an event type - i.e. the target of a listen event or the action of a raise event.
     * @throws \InvalidArgumentException if component does not exist
     */
    public function getEventTypeSpecification(string $id) : string;

}