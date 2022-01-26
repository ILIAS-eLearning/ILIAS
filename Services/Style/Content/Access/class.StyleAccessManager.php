<?php

/* Copyright (c) 1998-2021 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Style\Content\Access;

/**
 * Manages access to content style editing
 * @author Alexander Killing <killing@leifos.de>
 */
class StyleAccessManager
{
    /**
     * @var bool
     */
    protected $enable_write;

    /**
     * @var int
     */
    protected $ref_id;

    /**
     * @var int
     */
    protected $user_id;

    /**
     * @var \ilRbacSystem
     */
    protected $rbacsystem;

    /**
     * StyleAccessManager constructor.
     * @param \ilRbacSystem|null $rbacsystem
     * @param int                $ref_id
     * @param int                $user_id
     */
    public function __construct(\ilRbacSystem $rbacsystem = null, int $ref_id = 0, int $user_id = 0)
    {
        /** @var \ILIAS\DI\Container $DIC */
        global $DIC;

        $this->rbacsystem = (!is_null($rbacsystem))
            ? $rbacsystem
            : $DIC->rbac()->system();
        $this->ref_id = $ref_id;
        $this->user_id = $user_id;
    }

    /**
     * Force write status
     * @param bool $write
     */
    public function enableWrite(bool $write) : void
    {
        $this->enable_write = $write;
    }

    /**
     * Check write
     * @return bool
     */
    public function checkWrite() : bool
    {
        $rbacsystem = $this->rbacsystem;

        return ($this->enable_write ||
            $rbacsystem->checkAccessOfUser(
                $this->user_id,
                "write",
                $this->ref_id
            ) ||
            $rbacsystem->checkAccessOfUser(
                $this->user_id,
                "sty_write_content",
                $this->ref_id
            )
        );
    }
}
