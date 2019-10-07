<?php

namespace ILIAS\GlobalScreen\Client;

use JsonSerializable;

/**
 * Class Client
 *
 * @package ILIAS\GlobalScreen\Client
 */
class ClientSettings implements JsonSerializable
{

    /**
     * @var array
     */
    private $clear_states_for_levels
        = [
            ItemState::LEVEL_OF_TOPITEM => [ItemState::LEVEL_OF_TOPITEM],
            ItemState::LEVEL_OF_TOOL    => [ItemState::LEVEL_OF_TOPITEM, ItemState::LEVEL_OF_TOOL],
            ItemState::LEVEL_OF_SUBITEM => [],
        ];
    /**
     * @var bool
     */
    private $hashing = true;
    /**
     * @var bool
     */
    private $logging = false;
    /**
     * @var array
     */
    private $store_state_for_levels
        = [
            ItemState::LEVEL_OF_TOPITEM,
            ItemState::LEVEL_OF_TOOL,
            ItemState::LEVEL_OF_SUBITEM,
        ];


    /**
     * @param array $store_state_for_levels
     *
     * @return ClientSettings
     */
    public function setStoreStateForLevels(array $store_state_for_levels) : ClientSettings
    {
        $this->store_state_for_levels = $store_state_for_levels;

        return $this;
    }


    /**
     * @param bool $hashing
     *
     * @return ClientSettings
     */
    public function setHashing(bool $hashing) : ClientSettings
    {
        $this->hashing = $hashing;

        return $this;
    }


    /**
     * @param bool $logging
     *
     * @return ClientSettings
     */
    public function setLogging(bool $logging) : ClientSettings
    {
        $this->logging = $logging;

        return $this;
    }


    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        return [
            'clear_states_for_levels' => $this->clear_states_for_levels,
            'hashing'                 => $this->hashing,
            'logging'                 => $this->logging,
            'store_state_for_levels'  => $this->store_state_for_levels,
        ];
    }
}