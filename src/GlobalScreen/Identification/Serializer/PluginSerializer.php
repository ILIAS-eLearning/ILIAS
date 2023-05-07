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
use ILIAS\GlobalScreen\Identification\NullPluginIdentification;
use ILIAS\GlobalScreen\Identification\PluginIdentification;
use ILIAS\GlobalScreen\Identification\PluginIdentificationProvider;
use ILIAS\GlobalScreen\Provider\ProviderFactory;
use LogicException;

/**
 * Class PluginSerializer
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class PluginSerializer implements SerializerInterface
{
    protected const DIVIDER = '|';

    /**
     * @inheritdoc
     */
    public function serialize(IdentificationInterface $identification) : string
    {
        /**
         * @var $identification PluginIdentification
         */
        $divider = self::DIVIDER;

        $str = "{$identification->getPluginId()}{$divider}{$identification->getClassName()}{$divider}{$identification->getInternalIdentifier()}";

        if (strlen($str) > SerializerInterface::MAX_LENGTH) {
            throw new LogicException("Serialized Identifications MUST be shorter than " . SerializerInterface::MAX_LENGTH . " characters");
        }

        return $str;
    }

    /**
     * @inheritdoc
     */
    public function unserialize(string $serialized_string, IdentificationMap $map, ProviderFactory $provider_factory) : IdentificationInterface
    {
        [$plugin_id, $class_name, $internal_identifier] = explode(self::DIVIDER, $serialized_string);

        if (!$provider_factory->isInstanceCreationPossible($class_name) || !$provider_factory->isRegistered($class_name)) {
            return new NullPluginIdentification($plugin_id, $serialized_string, $internal_identifier);
        }

        $f = new PluginIdentificationProvider($provider_factory->getProviderByClassName($class_name), $plugin_id, $this, $map);

        return $f->identifier($internal_identifier);
    }

    /**
     * @inheritDoc
     */
    public function canHandle(string $serialized_identification) : bool
    {
        return preg_match('/(.*?)\|(.*?)\|(.*)/m', $serialized_identification) > 0;
    }
}
