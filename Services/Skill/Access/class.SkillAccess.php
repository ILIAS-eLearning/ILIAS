<?php

/* Copyright (c) 1998-2021 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Skill\Access;

/**
 * Skill access
 * @author Alexander Killing <killing@leifos.de>
 */
class SkillAccess
{
    /**
     * @var \ilAccessHandler
     */
    protected $access;

    /**
     * Constructor
     */
    public function __construct(\ilAccessHandler $access)
    {
        $this->access = $access;
    }
}