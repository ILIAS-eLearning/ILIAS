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
namespace ILIAS\GlobalScreen\Identification\Map;

use ILIAS\GlobalScreen\Identification\IdentificationInterface;

/**
 * Class IdentificationMap
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class IdentificationMap
{
    /**
     * @var mixed[]
     */
    protected static $map = [];

    public function addToMap(IdentificationInterface $identification) : void
    {
        self::$map[$identification->serialize()] = $identification;
    }

    public function isInMap(string $serialized) : bool
    {
        return isset(self::$map[$serialized]);
    }

    public function getFromMap(string $serialized) : IdentificationInterface
    {
        return self::$map[$serialized];
    }
}
