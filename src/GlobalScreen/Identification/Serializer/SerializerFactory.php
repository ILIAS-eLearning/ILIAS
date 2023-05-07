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

use InvalidArgumentException;

/**
 * Class SerializerFactory
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class SerializerFactory
{
    /**
     * @var \ILIAS\GlobalScreen\Identification\Serializer\CoreSerializer
     */
    private static $core_instance;
    /**
     * @var \ILIAS\GlobalScreen\Identification\Serializer\PluginSerializer
     */
    private static $plugin_instance;

    public function core() : CoreSerializer
    {
        if (!isset(self::$core_instance)) {
            self::$core_instance = new CoreSerializer();
        }

        return self::$core_instance;
    }

    public function plugin() : PluginSerializer
    {
        if (!isset(self::$plugin_instance)) {
            self::$plugin_instance = new PluginSerializer();
        }

        return self::$plugin_instance;
    }

    /**
     * @param string $serialized_identification
     * @return SerializerInterface
     */
    public function fromSerializedIdentification(string $serialized_identification) : SerializerInterface
    {
        $plugin = $this->plugin();
        if ($plugin->canHandle($serialized_identification)) {
            return $plugin;
        }

        $core = $this->core();
        if ($core->canHandle($serialized_identification)) {
            return $core;
        }

        throw new InvalidArgumentException("Nobody can handle serialized identification '$serialized_identification'.");
    }
}
