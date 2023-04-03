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
 * Class LostIdentification
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class LostIdentification implements IdentificationInterface
{
    /**
     * @var string
     */
    private $serialized_string;

    /**
     * NullIdentification constructor.
     * @param IdentificationInterface $wrapped_identification
     */
    public function __construct(string $serialized_string = null)
    {
        $this->serialized_string = $serialized_string;
    }

    /**
     * @inheritDoc
     */
    public function serialize() : string
    {
        return $this->serialized_string;
    }

    /**
     * @inheritDoc
     */
    public function unserialize($serialized) : void
    {
    }

    /**
     * @inheritDoc
     */
    public function getClassName() : string
    {
        return "Lost";
    }

    /**
     * @inheritDoc
     */
    public function getInternalIdentifier() : string
    {
        return "Lost";
    }

    /**
     * @inheritDoc
     */
    public function getProviderNameForPresentation() : string
    {
        return "Lost";
    }

    /**
     * @return array{data: string}
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
