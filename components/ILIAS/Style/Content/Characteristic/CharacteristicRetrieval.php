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

namespace ILIAS\Style\Content\Characteristic;

use ILIAS\Data\Order;
use ILIAS\Data\Range;
use ILIAS\Repository\RetrievalBase;
use ILIAS\Repository\RetrievalInterface;
use ILIAS\Style\Content\CharacteristicManager;
use ilObjStyleSheet;

class CharacteristicRetrieval implements RetrievalInterface
{
    use RetrievalBase;

    public function __construct(
        protected CharacteristicManager $manager,
        protected string $super_type
    ) {
    }

    public function getData(
        array $fields,
        ?Range $range = null,
        ?Order $order = null,
        array $filter = [],
        array $parameters = []
    ): \Generator {
        $data = [];
        foreach ($this->manager->getBySuperType($this->super_type) as $characteristic) {
            $type = $characteristic->getType();
            $tag = ilObjStyleSheet::_determineTag($type);
            $class = $characteristic->getCharacteristic();

            $data[] = [
                "id" => $type . "." . $tag . "." . $class,
                "obj" => $characteristic
            ];
        }

        $data = $this->applyOrder($data, $order);
        $data = $this->applyRange($data, $range);

        yield from $data;
    }

    public function count(
        array $filter,
        array $parameters
    ): int {
        return count($this->manager->getBySuperType($this->super_type));
    }

    public function isFieldNumeric(string $field): bool
    {
        return false;
    }
}
