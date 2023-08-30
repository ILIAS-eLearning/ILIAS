<?php

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

declare(strict_types=1);

namespace ILIAS\UI\Implementation\Component\Listing;

use ILIAS\UI\Component\Listing as IListing;
use ILIAS\UI\Component\Symbol\Symbol;
use ILIAS\UI\Component\Legacy\Legacy;
use ILIAS\UI\Component\Link\Standard as StandardLink;
use ILIAS\UI\Implementation\Component\ComponentHelper;

/**
 * Property listing
 * @package ILIAS\UI\Implementation\Component\Listing\Listing
 */
class Property extends Listing implements IListing\Property
{
    use ComponentHelper;
    protected const ALLOWED_VALUE_TYPES = [
        Symbol::class,
        Legacy::class,
        StandardLink::class
    ];

    public function __construct()
    {
        $this->items = [];
    }

    /**
     * @inheritdoc
     */
    public function withItems(array $items): self
    {
        $clone = clone $this;
        $clone->items = [];
        foreach ($items as $item) {
            $clone = $clone->withProperty(...$item);
        }
        return $clone;
    }

    public function withProperty(
        string $label,
        string | Symbol | Legacy | StandardLink $value,
        bool $show_label = true
    ): self {
        if (is_array($value)) {
            $this->checkArgListElements("value", $value, self::ALLOWED_VALUE_TYPES);
        }
        $clone = clone $this;
        $clone->items[] = [$label, $value, $show_label];
        return $clone;
    }
}
