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

namespace ILIAS\UI\Implementation\Component\Link;

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component\ComponentHelper;

/**
 * This implements commonalities between Links
 */
abstract class Link implements C\Link\Link
{
    use ComponentHelper;

    protected string $action;
    protected ?bool $open_in_new_viewport = null;

    public function __construct(string $action)
    {
        $this->action = $action;
    }

    /**
     * @inheritdoc
     */
    public function getAction(): string
    {
        return $this->action;
    }

    /**
     * @inheritdoc
     */
    public function withOpenInNewViewport(bool $open_in_new_viewport): C\Link\Link
    {
        $clone = clone $this;
        $clone->open_in_new_viewport = $open_in_new_viewport;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getOpenInNewViewport(): ?bool
    {
        return $this->open_in_new_viewport;
    }
}
