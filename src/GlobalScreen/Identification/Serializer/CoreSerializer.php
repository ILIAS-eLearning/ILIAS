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

namespace ILIAS\GlobalScreen\Identification\Serializer;

use ILIAS\GlobalScreen\Identification\CoreIdentificationProvider;
use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\Identification\LostIdentification;
use ILIAS\GlobalScreen\Identification\Map\IdentificationMap;
use ILIAS\GlobalScreen\Provider\ProviderFactory;

/**
 * Class CoreSerializer
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class CoreSerializer implements SerializerInterface
{
    public const DIVIDER = '|';

    public function serialize(IdentificationInterface $identification): string
    {
        $divider = self::DIVIDER;

        $str = "{$identification->getClassName()}{$divider}{$identification->getInternalIdentifier()}";

        if (strlen($str) > SerializerInterface::MAX_LENGTH) {
            throw new \LogicException("Serialized Identifications MUST be shorter than " . SerializerInterface::MAX_LENGTH . " characters");
        }

        return $str;
    }

    /**
     * @inheritdoc
     */
    public function unserialize(string $serialized_string, IdentificationMap $map, ProviderFactory $provider_factory): IdentificationInterface
    {
        list($class_name, $internal_identifier) = explode(self::DIVIDER, $serialized_string);

        if (!$provider_factory->isInstanceCreationPossible($class_name) || !$provider_factory->isRegistered($class_name)) {
            return new LostIdentification($serialized_string);
        }

        $f = new CoreIdentificationProvider($provider_factory->getProviderByClassName($class_name), $this, $map);

        return $f->identifier($internal_identifier);
    }

    /**
     * @inheritDoc
     */
    public function canHandle(string $serialized_identification): bool
    {
        return preg_match('/(.*?)\|(.*)/m', $serialized_identification) > 0;
    }
}
