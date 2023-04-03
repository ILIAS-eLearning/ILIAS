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
use ILIAS\GlobalScreen\Identification\Serializer\SerializerInterface;
use ILIAS\GlobalScreen\Provider\Provider;

/**
 * Class PluginIdentificationProvider
 * @see    IdentificationProviderInterface
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class PluginIdentificationProvider extends AbstractIdentificationProvider implements IdentificationProviderInterface
{
    /**
     * @var string
     */
    protected $plugin_id = "";

    /**
     * PluginIdentificationProvider constructor.
     * @param Provider            $provider
     * @param string              $plugin_id
     * @param SerializerInterface $serializer
     * @param IdentificationMap   $map
     */
    public function __construct(
        Provider $provider,
        string $plugin_id,
        SerializerInterface $serializer,
        IdentificationMap $map
    ) {
        parent::__construct($provider, $serializer, $map);
        $this->plugin_id = $plugin_id;
    }

    /**
     * @inheritdoc
     */
    public function identifier(string $identifier_string) : IdentificationInterface
    {
        if (isset(self::$instances[$identifier_string])) {
            return self::$instances[$identifier_string];
        }

        $identification = new PluginIdentification(
            $this->plugin_id,
            $identifier_string,
            $this->class_name,
            $this->serializer,
            $this->provider->getProviderNameForPresentation()
        );
        $this->map->addToMap($identification);

        return self::$instances[$identifier_string] = $identification;
    }
}
