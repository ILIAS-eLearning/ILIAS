<?php

class ilNotificationHandlerIterator implements Iterator
{
    private $items = array();
    private $index = 0;

    public function __construct(array $items = array())
    {
        $this->items = $items;
    }

    public function addItem(ilNotificationHandler $handler)
    {
        $this->items[] = $handler;
    }

    /**
     *
     * @return ilNotificationHandler
     */
    public function current()
    {
        return $this->items[$this->index];
    }

    /**
     *
     * @return integer
     */
    public function key()
    {
        return $this->index;
    }

    public function next()
    {
        $this->index++;
    }

    public function rewind()
    {
        $this->index = 0;
    }

    /**
     *
     * @return boolean
     */
    public function valid()
    {
        return $this->index < count($this->items);
    }
}
