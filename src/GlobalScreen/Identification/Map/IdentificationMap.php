<?php namespace ILIAS\GlobalScreen\Identification\Map;

use ILIAS\GlobalScreen\Identification\IdentificationInterface;

/**
 * Class IdentificationMap
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class IdentificationMap
{

    /**
     * @var IdentificationInterface[]
     */
    protected static $map = [];


    /**
     * @param IdentificationInterface $identification
     */
    public function addToMap(IdentificationInterface $identification)
    {
        self::$map[$identification->serialize()] = $identification;
    }


    /**
     * @param string $serialized
     *
     * @return bool
     */
    public function isInMap(string $serialized) : bool
    {
        return isset(self::$map[$serialized]);
    }


    /**
     * @param string $serialized
     *
     * @return IdentificationInterface
     */
    public function getFromMap(string $serialized) : IdentificationInterface
    {
        return self::$map[$serialized];
    }
}
