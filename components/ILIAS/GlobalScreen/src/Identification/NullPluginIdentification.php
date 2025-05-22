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

/**
 * Class NullPluginIdentification
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class NullPluginIdentification implements IdentificationInterface
{
    /**
     * NullPluginIdentification constructor.
     * @param string $plugin_id
     * @param string $identification
     * @param string $internal_identifier
     */
    public function __construct(private string $plugin_id, private string $identification = "", private string $internal_identifier = "")
    {
    }

    /**
     * @inheritDoc
     */
    public function serialize(): string
    {
        return $this->identification;
    }

    /**
     * @inheritDoc
     */
    public function unserialize($serialized): void
    {
        // nothing to do
    }

    /**
     * @inheritDoc
     */
    public function getClassName(): string
    {
        return $this->plugin_id;
    }

    /**
     * @inheritDoc
     */
    public function getInternalIdentifier(): string
    {
        return $this->internal_identifier;
    }

    /**
     * @inheritDoc
     */
    public function getProviderNameForPresentation(): string
    {
        return $this->plugin_id;
    }

    public function __serialize(): array
    {
        return ['data' => $this->serialize()];
    }

    public function __unserialize(array $data): void
    {
        $this->unserialize($data['data']);
    }
}
