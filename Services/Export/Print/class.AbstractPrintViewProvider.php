<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

namespace ILIAS\Export;

/**
 *
 * @author Alexander Killing <killing@leifos.de>
 */
abstract class AbstractPrintViewProvider implements PrintViewProvider
{
    const PRINT = "print";
    const OFFLINE = "offline";

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

    /**
     * @inheritDoc
     */
    public function getSelectionForm()
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
