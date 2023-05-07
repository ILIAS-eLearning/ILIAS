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
namespace ILIAS\GlobalScreen\Identification;

use ILIAS\GlobalScreen\Identification\Map\IdentificationMap;
use ILIAS\GlobalScreen\Identification\Serializer\SerializerFactory;
use ILIAS\GlobalScreen\Provider\Provider;
use ILIAS\GlobalScreen\Provider\ProviderFactory;

/**
 * Class IdentificationFactory
 * All elements in the GlobalScreen service must be identifiable for the supplying
 * components mentioned in the readme. The GlobalScreen service uses this identification, for
 * example, for parent/child relationships. The identification is also forwarded
 * to the UI service or to the instance that then renders the GlobalScreen elements. This
 * means that the identification can be used there again, for example, to
 * generate unique IDs for the online help.
 * There will be at least two IdentificationProvider, one for core components
 * and one for plugins. This factory allows to acces both.
 * The identification you get can be serialized and is used e.g. to store in
 * database and cache. you don't need to take care of storing this.
 * Since you are passing some identifiers as a string such as 'personal_desktop'
 * the GlobalScreen-Services must take care after naming collisions. Therefore you always
 * pass your Provider (or even the Plugin-Class in case of Plugins) and the GlobalScreen-
 * Services will use this information to generate unique identifications.
 * Currently Identifications are only used for the GlobalScreen-MainMenu-Elements.
 * Other like Footer may follow.
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class IdentificationFactory
{
    /**
     * @var \ILIAS\GlobalScreen\Provider\ProviderFactory
     */
    protected $provider_factory;
    /**
     * @var \ILIAS\GlobalScreen\Identification\Serializer\SerializerFactory
     */
    protected $serializer_factory;
    /**
     * @var \ILIAS\GlobalScreen\Identification\Map\IdentificationMap
     */
    protected $map;

    /**
     * IdentificationFactory constructor.
     * @param ProviderFactory $provider_factory
     */
    final public function __construct(ProviderFactory $provider_factory)
    {
        $this->serializer_factory = new SerializerFactory();
        $this->map = new IdentificationMap();
        $this->provider_factory = $provider_factory;
    }

    /**
     * Returns a IdentificationProvider for core components, only a Provider
     * is needed.
     */
    final public function core(Provider $provider) : CoreIdentificationProvider
    {
        return new CoreIdentificationProvider($provider, $this->serializer_factory->core(), $this->map);
    }

    /**
     * Returns a IdentificationProvider for core tools, only a Provider
     * is needed.
     */
    final public function tool(Provider $provider) : ToolIdentificationProvider
    {
        return new ToolIdentificationProvider($provider, $this->serializer_factory->core(), $this->map);
    }

    /**
     * Returns a IdentificationProvider for ILIAS-Plugins which takes care of
     * the plugin_id for further identification where a provided GlobalScreen-element
     * comes from (e.g. to disable or delete all elements when a plugin is
     * deleted or deactivated).
     */
    final public function plugin(string $plugin_id, Provider $provider) : PluginIdentificationProvider
    {
        return new PluginIdentificationProvider($provider, $plugin_id, $this->serializer_factory->plugin(), $this->map);
    }

    /**
     * @param $serialized_string
     * @return IdentificationInterface
     */
    final public function fromSerializedIdentification($serialized_string) : IdentificationInterface
    {
        if ($serialized_string === null || $serialized_string === "") {
            return new NullIdentification();
        }
        if ($this->map->isInMap($serialized_string)) {
            return $this->map->getFromMap($serialized_string);
        }

        return $this->serializer_factory->fromSerializedIdentification($serialized_string)->unserialize($serialized_string, $this->map, $this->provider_factory);
    }
}
