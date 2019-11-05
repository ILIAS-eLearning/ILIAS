<?php
/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */


namespace ILIAS\UI\Component\Legacy;

use ILIAS\UI\Component\Signal;

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

    /**
     * Get a legacy component like this, but with an additional signal with custom JavaScript code
     *
     * @deprecated Should only be used to connect legacy components. Will be removed in the future. Use at your own risk
     * @param $signal_name
     * @param $js_code
     * @return Legacy
     */
    public function withCustomSignal(string $signal_name, string $js_code) : Legacy;

    /**
     * Get signal with custom JavaScript code
     *
     * @deprecated Should only be used to connect legacy components. Will be removed in the future. Use at your own risk
     * @param $signal_name
     * @throws \InvalidArgumentException
     * @return Signal
     */
    public function getCustomSignal(string $signal_name);
}
