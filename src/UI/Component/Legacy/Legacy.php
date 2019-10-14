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

    /**
     * Get a legacy component like this, but with an additional signal with custom JavaScript code
     *
     * @param $signal_name
     * @param $js_code
     * @return Legacy
     */
    public function withCustomSignal(string $signal_name, string $js_code) : Legacy;

    /**
     * Get signal with custom JavaScript code
     *
     * @param $signal_name
     * @return \ILIAS\UI\Component\Signal
     */
    public function getCustomSignal(string $signal_name) : \ILIAS\UI\Component\Signal;
}
