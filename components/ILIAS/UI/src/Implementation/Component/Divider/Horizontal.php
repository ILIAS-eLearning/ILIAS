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

namespace ILIAS\UI\Implementation\Component\Divider;

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component\ComponentHelper;

/**
 * Horizontal Divider
 */
class Horizontal implements C\Divider\Horizontal
{
    use ComponentHelper;

    protected ?string $label = null;

    public function __construct()
    {
    }

    /**
     * @inheritdoc
     */
    public function getLabel(): ?string
    {
        return $this->label;
    }

    /**
     * @inheritdoc
     */
    public function withLabel(string $label): C\Divider\Horizontal
    {
        $this->checkStringArg("label", $label);
        $clone = clone $this;
        $clone->label = $label;
        return $clone;
    }
}
