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
namespace ILIAS\GlobalScreen\ScreenContext;

use ILIAS\Data\ReferenceId;
use ILIAS\GlobalScreen\ScreenContext\AdditionalData\Collection;

/**
 * Class BasicScreenContext
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class BasicScreenContext implements ScreenContext
{
    /**
     * @var \ILIAS\Data\ReferenceId
     */
    protected $reference_id;
    /**
     * @var \ILIAS\GlobalScreen\ScreenContext\AdditionalData\Collection
     */
    protected $additional_data;
    /**
     * @var string
     */
    protected $context_identifier = '';

    /**
     * BasicScreenContext constructor.
     * @param string $context_identifier
     */
    public function __construct(string $context_identifier)
    {
        $this->context_identifier = $context_identifier;
        $this->additional_data = new Collection();
        $this->reference_id = new ReferenceId(0);
    }

    /**
     * @inheritDoc
     */
    public function hasReferenceId() : bool
    {
        return $this->reference_id->toInt() > 0;
    }

    /**
     * @inheritDoc
     */
    public function getReferenceId() : ReferenceId
    {
        return $this->reference_id;
    }

    /**
     * @inheritDoc
     */
    public function withReferenceId(ReferenceId $reference_id) : ScreenContext
    {
        $clone = clone $this;
        $clone->reference_id = $reference_id;

        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function withAdditionalData(Collection $collection) : ScreenContext
    {
        $clone = clone $this;
        $clone->additional_data = $collection;

        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function getAdditionalData() : Collection
    {
        return $this->additional_data;
    }

    /**
     * @inheritDoc
     */
    public function addAdditionalData(string $key, $value) : ScreenContext
    {
        $this->additional_data->add($key, $value);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getUniqueContextIdentifier() : string
    {
        return $this->context_identifier;
    }
}
