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
        ];
    /**
     * @var bool
     */
    private $hashing = true;
    /**
     * @var array
     */
    private $store_state_for_levels = [ItemState::LEVEL_OF_TOPITEM, ItemState::LEVEL_OF_TOOL, ItemState::LEVEL_OF_SUBITEM];


    /**
     * @param array $clear_states_for_levels
     *
     * @return ClientSettings
     */
    public function setClearStatesForlevels(array $clear_states_for_levels) : ClientSettings
    {
        $this->clear_states_for_levels = $clear_states_for_levels;

        return $this;
    }


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
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        return [
            'clear_states_for_levels' => $this->clear_states_for_levels,
            'hashing'                 => $this->hashing,
            'store_state_for_levels'  => $this->store_state_for_levels,
        ];
    }
}