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
 
namespace ILIAS\UI\Implementation\Component\Listing\CharacteristicValue;

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use InvalidArgumentException;

/**
 * Class Text
 * @package ILIAS\UI\Implementation\Component\Listing\CharacteristicValue
 */
class Text implements C\Listing\CharacteristicValue\Text
{
    use ComponentHelper;

    protected array $items;

    public function __construct(array $items)
    {
        $this->validateItems($items);
        $this->items = $items;
    }

    /**
     * @param array $items
     */
    private function validateItems(array $items) : void
    {
        if (!count($items)) {
            throw new InvalidArgumentException('expected non empty array, got empty array');
        }

        $this->checkArgList(
            "Characteristic Value List Items",
            $items,
            function ($k, $v) {
                if (!is_string($k) || !strlen($k)) {
                    return false;
                }

                if (!is_string($v) && !strlen($v)) {
                    return false;
                }

                return true;
            },
            fn ($k, $v) => "expected keys of type string and values of type string, got ($k => $v)"
        );
    }

    /**
     * @inheritdoc
     */
    public function getItems() : array
    {
        return $this->items;
    }
}
