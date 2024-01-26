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
 * Class NullIdentification
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class NullIdentification implements IdentificationInterface
{
    /**
     * @var \ILIAS\GlobalScreen\Identification\IdentificationInterface|null
     */
    protected $wrapped_identification;

    /**
     * NullIdentification constructor.
     * @param IdentificationInterface $wrapped_identification
     */
    public function __construct(IdentificationInterface $wrapped_identification = null)
    {
        $this->wrapped_identification = $wrapped_identification;
    }

    /**
     * @inheritDoc
     */
    public function serialize()
    {
        if ($this->wrapped_identification !== null) {
            return $this->wrapped_identification->serialize();
        }

        return "";
    }

    /**
     * @inheritDoc
     */
    public function unserialize($serialized) : void
    {
        // noting to do
    }

    /**
     * @inheritDoc
     */
    public function getClassName() : string
    {
        if ($this->wrapped_identification !== null) {
            return $this->wrapped_identification->getClassName();
        }

        return "Null";
    }

    /**
     * @inheritDoc
     */
    public function getInternalIdentifier() : string
    {
        if ($this->wrapped_identification !== null) {
            return $this->wrapped_identification->getInternalIdentifier();
        }

        return "Null";
    }

    /**
     * @inheritDoc
     */
    public function getProviderNameForPresentation() : string
    {
        if ($this->wrapped_identification !== null) {
            return $this->wrapped_identification->getProviderNameForPresentation();
        }

        return "Null";
    }

    /**
     * @return array{data: string|null}
     */
    public function __serialize() : array
    {
        return ['data' => $this->serialize()];
    }

    public function __unserialize(array $data) : void
    {
        $this->unserialize($data['data']);
    }
}
