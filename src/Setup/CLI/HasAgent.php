<?php
/* Copyright (c) 2020 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Setup\CLI;

use ILIAS\Setup\Agent;

/**
 * Add this to an Command that has an agent.
 */
trait HasAgent
{
    /**
     * @var callable
     */
    protected $lazy_agent = null;

    /**
     * @var Agent|null
     */
    protected $agent = null;

    protected function getAgent() : Agent
    {
        if ($this->agent !== null) {
            return $this->agent;
        }
        if (!is_callable($this->lazy_agent)) {
            throw new \LogicException("\$this->lazy_agent not initialized properly.");
        }
        $this->agent = ($this->lazy_agent)();
        return $this->agent;
    }
}
