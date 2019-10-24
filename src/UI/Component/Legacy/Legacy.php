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

    /**
     * Get a list of all registered signals and their custom JavaScript code. The list is an associative array, where
     * the key for each item is the given custom name. Each item of this list is an associative array itself.
     *
     * The items in this list have the following structure:
     * item = array (
     *     'signal'  => $signal  : Signal
     *     'js_code' => $js_code : String
     * )
     *
     * @deprecated Should only be used to connect legacy components. Will be removed in the future. Use at your own risk
     * @return array
     */
    public function getAllSignals() : array;
}
