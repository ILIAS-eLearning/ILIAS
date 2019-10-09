<?php
/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */


namespace ILIAS\UI\Component\Legacy;

/**
 * Interface Legacy
 * @package ILIAS\UI\Component\Legacy
 */
interface Legacy extends \ILIAS\UI\Component\Component
{
    /**
     * Get content as string stored in this component.
     *
     * @return	string
     */
    public function getContent();
}
