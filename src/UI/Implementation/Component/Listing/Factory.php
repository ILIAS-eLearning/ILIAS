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

use ILIAS\UI\Component\Listing as L;

/**
 * Class Factory
 * @package ILIAS\UI\Implementation\Component\Listing
 */
class Factory implements L\Factory
{
    /**
     * @inheritdoc
     */
    public function unordered(array $items) : L\Unordered
    {
        return new Unordered($items);
    }

    /**
     * @inheritdoc
     */
    public function ordered(array $items) : L\Ordered
    {
        return new Ordered($items);
    }

    /**
     * @inheritdoc
     */
    public function descriptive(array $items) : L\Descriptive
    {
        return new Descriptive($items);
    }

    /**
     * @inheritdoc
     */
    public function workflow() : L\Workflow\Factory
    {
        return new Workflow\Factory();
    }

    /**
     * @inheritdoc
     */
    public function characteristicValue() : L\CharacteristicValue\Factory
    {
        return new CharacteristicValue\Factory();
    }
}
