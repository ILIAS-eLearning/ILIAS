<?php namespace ILIAS\GlobalScreen\Identification\Map;

use ILIAS\GlobalScreen\Identification\IdentificationInterface;

/******************************************************************************
 * This file is part of ILIAS, a powerful learning management system.
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *****************************************************************************/

/**
 * Class IdentificationMap
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class IdentificationMap
{
    protected static array $map = [];
    
    public function addToMap(IdentificationInterface $identification) : void
    {
        self::$map[$identification->serialize()] = $identification;
    }
    
    public function isInMap(string $serialized) : bool
    {
        return isset(self::$map[$serialized]);
    }
    
    public function getFromMap(string $serialized) : IdentificationInterface
    {
        return self::$map[$serialized];
    }
}
