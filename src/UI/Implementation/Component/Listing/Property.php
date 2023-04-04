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
        //$types = array('string',C\Component::class);
        //$this->checkArgListElements("items", $items, $types);
        $this->items = [];
    }

    /**
     * @inheritdoc
     */
    public function withItems(array $items): C\Listing\Listing
    {
        //$types = array('string',C\Component::class);
        //$this->checkArgListElements("items", $items, $types);

        $clone = clone $this;
        $clone->items = $items;
        return $clone;
    }

    public function withProperty(string $label, $value, bool $show_label = true)
    {
        $clone = clone $this;
        $clone->items[] = [$label, $value, $show_label];
        return $clone;
    }
}
