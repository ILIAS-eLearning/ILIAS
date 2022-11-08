<?php

class ilArtifactEventHandlingData implements ilEventHandlingData
{
    public const EVENT_HANDLING_DATA_PATH = "Services/EventHandling/artifacts/event_handling_data.php";

    protected array $event_handling_data;

    public function __construct()
    {
        $this->event_handling_data = $this->readEventHandlingData();
    }

    protected function readEventHandlingData() : array
    {
        return require self::EVENT_HANDLING_DATA_PATH;
    }

    public function hasEvent(string $component, string $type, string $type_specification) : bool
    {
        $has_event = false;
        foreach ($this->event_handling_data AS $event_key => $event_values)
        {
            $same_component             = ($event_values["component"] == $component) ? true : false;
            $same_type                  = ($event_values["type"] == $type) ? true : false;
            $same_type_specification    = ($event_values["type_specification"] == $type_specification) ? true : false;
            if($same_component && $same_type && $same_type_specification)
            {
                $has_event = true;
            }
        }
        return $has_event;
    }

    public function getEvent(string $component, string $type, string $type_specification) : array
    {
        $event = NULL;
        foreach ($this->event_handling_data AS $event_key => $event_values)
        {
            $same_component             = ($event_values["component"] == $component) ? true : false;
            $same_type                  = ($event_values["type"] == $type) ? true : false;
            $same_type_specification    = ($event_values["type_specification"] == $type_specification) ? true : false;
            if($same_component && $same_type && $same_type_specification)
            {
                $event = $event_values;
            }
        }
        if($event == NULL)
        {
            throw new \InvalidArgumentException(
                "There is no event with the component \"" . $component . "\", type \"" . $type
                . "\" and type specification \"" . $type_specification . "\"."
            );
        }
        return $event_values;
    }

    public function getEventsByType(string $type) : Iterator
    {
        foreach ($this->event_handling_data AS $event_key => $event_values)
        {
            if($event_values["type"] == $type)
            {
                yield $this->event_handling_data[$event_key];
            }
        }
    }

    public function getEventComponent(string $id) : string
    {
        return $this->event_handling_data[$id]["component"];
    }

    public function getEventType(string $id) : string
    {
        return $this->event_handling_data[$id]["type"];
    }

    public function getEventTypeSpecification(string $id) : string
    {
        return $this->event_handling_data[$id]["type_specification"];
    }
}