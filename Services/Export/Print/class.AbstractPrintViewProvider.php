<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

namespace ILIAS\Export;

use ilPropertyFormGUI;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
abstract class AbstractPrintViewProvider implements PrintViewProvider
{
    public const PRINT = "print";
    public const OFFLINE = "offline";

    /**
     * @var bool
     */
    protected $offline = false;

    /**
     * Set output mode
     * @param string $a_val self::PRINT|self::OFFLINE
     */
    public function setOffline(bool $offline)
    {
        $this->offline = $offline;
    }

    /**
     * Get output mode
     * @return string self::PRINT|self::OFFLINE
     */
    protected function getOutputMode() : string
    {
        return $this->offline
            ? self::OFFLINE
            : self::PRINT;
    }

    public function getOnSubmitCode() : string
    {
        return "";
    }

    public function autoPageBreak() : bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function getSelectionForm() : ?ilPropertyFormGUI
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function getPages() : array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getTemplateInjectors() : array
    {
        return [];
    }
}
