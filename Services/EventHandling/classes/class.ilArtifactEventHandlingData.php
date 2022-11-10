<?php

class ilArtifactEventHandlingData
{
    public const EVENT_HANDLING_DATA_PATH = "Services/EventHandling/artifacts/event_handling_data.php";

    protected array $event_handling_data;

    public function __construct()
    {
        $this->event_handling_data = $this->readEventHandlingData();
    }

    /**
     * Read the event data stored in the artifact
     * @return array
     */
    protected function readEventHandlingData() : array
    {
        return require self::EVENT_HANDLING_DATA_PATH;
    }

    /**
     * Check if an event exists
     */
    public function hasEvent(string $component, string $type, string $type_specification) : bool
    {
        return in_array(
            [
                "component"             => $component,
                "type"                  => $type,
                "type_specification"    => $type_specification
            ],
            $this->event_handling_data,
            true
        );
    }

    /**
     * Get the event with the given component, type and type specification
     * @throws \InvalidArgumentException if event does not exist
     */
    public function getEvent(string $component, string $type, string $type_specification) : array
    {
        if($this->hasEvent($component, $type, $type_specification))
        {
            return [
                "component"             => $component,
                "type"                  => $type,
                "type_specification"    => $type_specification
            ];
        }

        throw new \InvalidArgumentException(
            "There is no event with the component \"" . $component . "\", type \"" . $type
            . "\" and type specification \"" . $type_specification . "\"."
        );
    }

    /**
     * Get all events of the given type
     * @throws \InvalidArgumentException if no events of this type exist
     */
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
}