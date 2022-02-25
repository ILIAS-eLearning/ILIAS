<?php declare(strict_types=1);

class ilQTIXMLParserArray implements ArrayAccess
{
    /**
     * @var array [[XMLParser|resource<xml>, int]]
     */
    private array $bag;

    /**
     * @param XMLParser|resource<xml> $offset
     */
    public function offsetExists($offset) : bool
    {
        return null !== $this->offsetGet($offset);
    }

    /**
     * @param XMLParser|resource<xml> $offset
     * @return int|null
     */
    public function offsetGet($offset)
    {
        return array_filter($this->bag, $this->offsetEquals($offset))[0][1] ?? null;
    }

    /**
     * @param XMLParser|resource<xml> $offset
     * @param int $value
     */
    public function offsetSet($offset, $value) : void
    {
        if (! $offset instanceof XMLParser || !is_resource($offset)) {
            throw new InvalidArgumentException('Only instances of XMLParser or XML resources can be used as keys.');
        } elseif (!is_int($value)) {
            throw new InvalidArgumentException('Only integers can be used as value.');
        }

        if ($this->offsetExists($offset)) {
            $this->offsetUnset($offset);
        }
        $this->bag[] = [$offset, $value];
    }

    /**
     * @param XMLParser|resource<xml> $offset
     */
    public function offsetUnset($offset) : void
    {
        $this->bag = array_filter($this->bag, $this->offsetUnEquals($offset));
    }

    /**
     * @param XMLParser|resource<xml> $offset
     * @return callable [XMLParser|resource<xml>, int] -> bool
     */
    private function offsetEquals($offset) : callable
    {
        return static function (array $pair) use ($offset) : bool {
            return $pair[0] === $offset;
        };
    }

    /**
     * @param XMLParser|resource<xml> $offset
     * @return callable [XMLParser|resource<xml>, int] -> bool
     */
    private function offsetUnEquals($offset) : callable
    {
        return static function (array $pair) use ($offset) : bool {
            return $pair[0] !== $offset;
        };
    }
}
