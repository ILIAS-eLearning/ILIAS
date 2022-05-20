<?php namespace ILIAS\GlobalScreen\Scope\Layout\MetaContent\MetaData;

/**
 * Class MetaDataCollection
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class MetaDatum
{
    /**
     * @var string
     */
    protected $key;
    /**
     * @var string
     */
    protected $value;
    
    public function __construct(string $key, string $value)
    {
        $this->key = $key;
        $this->value = $value;
    }
    
    public function getKey() : string
    {
        return $this->key;
    }
    
    public function getValue() : string
    {
        return $this->value;
    }
    
}
