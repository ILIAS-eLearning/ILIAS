<?php

namespace ILIAS\GlobalScreen\Client;

use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Renderer\Hasher;

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
 * Class ItemState
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ItemState
{
    use Hasher;
    
    public const LEVEL_OF_TOOL = 1;
    public const LEVEL_OF_TOPITEM = 2;
    public const LEVEL_OF_SUBITEM = 10;
    public const COOKIE_NS_GS = 'gs_active_items';
    private IdentificationInterface $identification;
    private array $storage = [];
    
    /**
     * ItemState constructor.
     * @param IdentificationInterface $identification
     */
    public function __construct(IdentificationInterface $identification)
    {
        $this->identification = $identification;
        $this->storage        = $this->getStorage();
    }
    
    public function isItemActive() : bool
    {
        $hash = $this->hash($this->identification->serialize());
        $b    = isset($this->storage[$hash]) && $this->storage[$hash] == true;
        
        return $b;
    }
    
    /**
     * @return mixed[]
     */
    public function getStorage() : array
    {
        static $json_decode;
        if (!isset($json_decode)) {
            $json_decode = json_decode($_COOKIE[self::COOKIE_NS_GS], true);
            $json_decode = is_array($json_decode) ? $json_decode : [];
        }
        
        return $json_decode;
    }
}
