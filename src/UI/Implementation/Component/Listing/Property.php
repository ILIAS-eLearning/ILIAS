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

namespace ILIAS\UI\Implementation\Component\Listing;

use ILIAS\UI\Component as C;

/**
 * Property listing
 * @package ILIAS\UI\Implementation\Component\Listing\Listing
 */
class Property extends Listing implements C\Listing\Property
{
    public function __construct()
    {
        $this->items = [];
    }

    /**
     * @inheritdoc
     */
    public function withItems(array $items): C\Listing\Listing
    {
        $clone = clone $this;
        $clone->items = [];
        foreach ($items as $item) {
            $clone = $clone->withProperty(...$item);
        }
        return $clone;
    }

    public function withProperty(string $label, $value, bool $show_label = true): self
    {
        $clone = clone $this;
        $clone->items[] = [$label, $value, $show_label];
        return $clone;
    }
}
