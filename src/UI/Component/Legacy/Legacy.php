<?php

declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

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
    public function getContent(): string;

    /**
     * Get a legacy component like this, but with an additional signal with custom JavaScript code
     *
     * @deprecated Should only be used to connect legacy components. Will be removed in the future. Use at your own risk
     */
    public function withCustomSignal(string $signal_name, string $js_code): Legacy;

    /**
     * Get signal with custom JavaScript code
     *
     * @deprecated Should only be used to connect legacy components. Will be removed in the future. Use at your own risk
     * @throws \InvalidArgumentException
     */
    public function getCustomSignal(string $signal_name): Signal;
}
