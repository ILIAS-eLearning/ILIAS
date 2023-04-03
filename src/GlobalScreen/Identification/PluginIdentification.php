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

use ILIAS\GlobalScreen\Identification\Serializer\SerializerInterface;

/**
 * Class PluginIdentification
 * @see    IdentificationFactory
 * This is a implementation of IdentificationInterface for usage in Plugins
 * (they will get them through the factory or through ilPlugin).
 * This a Serializable and will be used to store in database and cache.
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class PluginIdentification extends AbstractIdentification implements IdentificationInterface
{
    /**
     * @var string
     */
    protected $plugin_id = "";

    /**
     * @inheritDoc
     */
    public function __construct(
        string $plugin_id,
        string $internal_identifier,
        string $classname,
        SerializerInterface $serializer,
        string $provider_presentation_name
    ) {
        parent::__construct(
            $internal_identifier,
            $classname,
            $serializer,
            $provider_presentation_name
        );
        $this->plugin_id = $plugin_id;
    }

    /**
     * @return string
     */
    public function getPluginId() : string
    {
        return $this->plugin_id;
    }

    /**
     * @inheritDoc
     */
    public function getProviderNameForPresentation() : string
    {
        return $this->plugin_id;
    }
}
