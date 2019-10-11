<?php

namespace ILIAS\GlobalScreen\Client;

use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Renderer\Hasher;

/**
 * Class ItemState
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ItemState
{

    use Hasher;
    const LEVEL_OF_TOOL = 1;
    const LEVEL_OF_TOPITEM = 2;
    const LEVEL_OF_SUBITEM = 10;
    const COOKIE_NS_GS = 'gs_active_items';
    /**
     * @var IdentificationInterface
     */
    private $identification;
    /**
     * @var array
     */
    private $storage = [];


    /**
     * ItemState constructor.
     *
     * @param IdentificationInterface $identification
     */
    public function __construct(IdentificationInterface $identification)
    {
        $this->identification = $identification;
        $this->storage = $this->getStorage();
    }


    public function isItemActive() : bool
    {
        $hash = $this->hash($this->identification->serialize());
        $b = isset($this->storage[$hash]) && $this->storage[$hash] == true;

        return $b;
    }


    /**
     * @return mixed
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
