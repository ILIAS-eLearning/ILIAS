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
 
namespace ILIAS\UI\Implementation\Component\Chart;

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component\ComponentHelper;

/**
 * Class ScaleBar
 * @package ILIAS\UI\Implementation\Component\Listing\Descriptive
 */
class ScaleBar implements C\Chart\ScaleBar
{
    use ComponentHelper;

    protected array $items;

    public function __construct($items)
    {
        $this->items = $items;
    }

    /**
     * @inheritdoc
     */
    public function withItems(array $items) : C\Chart\ScaleBar
    {
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
