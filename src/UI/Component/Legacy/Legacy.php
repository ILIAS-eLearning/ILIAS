<?php declare(strict_types=1);

/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Legacy;

use ILIAS\UI\Component\JavaScriptBindable;
use ILIAS\UI\Component\Signal;
use ILIAS\UI\Component\Component;

/**
 * Interface Legacy
 * @package ILIAS\UI\Component\Legacy
 */
interface Legacy extends Component, JavaScriptBindable
{
    /**
     * Get content as string stored in this component.
     */
    public function getContent() : string;

    /**
     * Get a legacy component like this, but with an additional signal with custom JavaScript code
     *
     * @deprecated Should only be used to connect legacy components. Will be removed in the future. Use at your own risk
     */
    public function withCustomSignal(string $signal_name, string $js_code) : Legacy;

    /**
     * Get signal with custom JavaScript code
     *
     * @deprecated Should only be used to connect legacy components. Will be removed in the future. Use at your own risk
     * @throws \InvalidArgumentException
     */
    public function getCustomSignal(string $signal_name) : Signal;
}
