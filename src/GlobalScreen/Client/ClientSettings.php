<?php

namespace ILIAS\GlobalScreen\Client;

use JsonSerializable;

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
 * Class Client
 * @package ILIAS\GlobalScreen\Client
 */
class ClientSettings implements JsonSerializable
{
    private bool $hashing = true;
    private bool $logging = false;
    
    public function setHashing(bool $hashing) : self
    {
        $this->hashing = $hashing;
        
        return $this;
    }
    
    public function setLogging(bool $logging) : self
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
