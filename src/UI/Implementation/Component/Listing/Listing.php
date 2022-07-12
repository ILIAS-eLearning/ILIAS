<?php declare(strict_types=1);

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
use ILIAS\UI\Implementation\Component\ComponentHelper;

/**
 * Class Listing
 * @package ILIAS\UI\Implementation\Component\Listing\Listing
 */
class Listing implements C\Listing\Listing
{
    use ComponentHelper;

    private array $items;

    /**
     * Listing constructor.
     */
    public function __construct(array $items)
    {
        $types = array('string',C\Component::class);
        $this->checkArgListElements("items", $items, $types);
        $this->items = $items;
    }

    /**
     * @inheritdoc
     */
    public function withItems(array $items) : C\Listing\Listing
    {
        $types = array('string',C\Component::class);
        $this->checkArgListElements("items", $items, $types);

        $clone = clone $this;
        $clone->items = $items;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getItems() : array
    {
        return $this->items;
    }
}
