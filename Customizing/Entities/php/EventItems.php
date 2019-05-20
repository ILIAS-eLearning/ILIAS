<?php



/**
 * EventItems
 */
class EventItems
{
    /**
     * @var int
     */
    private $eventId = '0';

    /**
     * @var int
     */
    private $itemId = '0';


    /**
     * Set eventId.
     *
     * @param int $eventId
     *
     * @return EventItems
     */
    public function setEventId($eventId)
    {
        $this->eventId = $eventId;

        return $this;
    }

    /**
     * Get eventId.
     *
     * @return int
     */
    public function getEventId()
    {
        return $this->eventId;
    }

    /**
     * Set itemId.
     *
     * @param int $itemId
     *
     * @return EventItems
     */
    public function setItemId($itemId)
    {
        $this->itemId = $itemId;

        return $this;
    }

    /**
     * Get itemId.
     *
     * @return int
     */
    public function getItemId()
    {
        return $this->itemId;
    }
}
