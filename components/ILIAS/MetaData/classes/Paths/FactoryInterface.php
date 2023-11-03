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

namespace ILIAS\MetaData\Paths;

use ILIAS\MetaData\Elements\Base\BaseElementInterface;

interface FactoryInterface
{
    public function fromString(string $string): PathInterface;

    /**
     * Returns absolute path from root to the given element.
     * If leads_to_exactly one is set true, it will add
     * mdid filters where possible such that the path only
     * leads to that specific element and not also others of
     * the same type and position.
     */
    public function toElement(
        BaseElementInterface $to,
        bool $leads_to_exactly_one = false
    ): PathInterface;

    /**
     * Returns relative path between two given elements.
     * If leads_to_exactly one is set true, it tries to add
     * mdid filters where possible such that the path only
     * leads to that specific element and not also others of
     * the same type and position.
     */
    public function betweenElements(
        BaseElementInterface $from,
        BaseElementInterface $to,
        bool $leads_to_exactly_one = false
    ): PathInterface;

    public function custom(): BuilderInterface;
}
