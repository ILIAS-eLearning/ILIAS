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
namespace ILIAS\GlobalScreen\Identification\Serializer;

use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\Identification\Map\IdentificationMap;
use ILIAS\GlobalScreen\Provider\ProviderFactory;
use LogicException;

/**
 * Interface SerializerInterface
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface SerializerInterface
{
    public const MAX_LENGTH = 255;

    /**
     * The string MUST be shorter than 64 characters
     * @param IdentificationInterface $identification
     * @return string
     * @throws LogicException whn longer than 64 characters
     */
    public function serialize(IdentificationInterface $identification) : string;

    /**
     * @param string            $serialized_string
     * @param IdentificationMap $map
     * @param ProviderFactory   $provider_factory
     * @return IdentificationInterface
     */
    public function unserialize(string $serialized_string, IdentificationMap $map, ProviderFactory $provider_factory) : IdentificationInterface;

    /**
     * @param string $serialized_identification
     * @return bool
     */
    public function canHandle(string $serialized_identification) : bool;
}
