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
     * @var bool
     */
    private $hashing = true;
    /**
     * @var bool
     */
    private $logging = false;


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
            'hashing' => $this->hashing,
            'logging' => $this->logging,
        ];
    }
}
