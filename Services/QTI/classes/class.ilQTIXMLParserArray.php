<?php declare(strict_types=1);

class ilQTIXMLParserArray implements ArrayAccess
{
    /**
     * @var array [[XMLParser, int]]
     */
    private array $bag;

    public function offsetExists($offset) : bool
    {
        return null !== $this->offsetGet($offset);
    }

    public function offsetGet($offset)
    {
        return array_filter($this->bag, $this->offsetEquals($offset))[0][1] ?? null;
    }

    public function offsetSet($offset, $value) : void
    {
        if (! $offset instanceof XMLParser) {
            throw new InvalidArgumentException('Only instances of XMLParser\' can be used as keys.');
        } elseif (!is_int($value)) {
            throw new InvalidArgumentException('Only integers can be used as value.');
        }

        if ($this->offsetExists($offset)) {
            $this->offsetUnset($offset);
        }
        $this->bag[] = [$offset, $value];
    }

    public function offsetUnset($offset) : void
    {
        $this->bag = array_filter($this->bag, $this->offsetUnEquals($offset));
    }

    private function offsetEquals(XMLParser $offset) : array
    {
        return static function (array $pair) use ($offset) : bool {
            return $pair[0] === $offset;
        };
    }

    private function offsetUnEquals(XMLParser $offset) : array
    {
        return static function (array $pair) use ($offset) : bool {
            return $pair[0] !== $offset;
        };
    }
}
