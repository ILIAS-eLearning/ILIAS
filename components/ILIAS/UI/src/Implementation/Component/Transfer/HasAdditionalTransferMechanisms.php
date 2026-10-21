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
 */

declare(strict_types=1);

namespace ILIAS\UI\Implementation\Component\Transfer;

use ILIAS\UI\Component as C;

/**
 * Implements {@see C\Transfer\HasAdditionalTransferMechanism}
 */
trait HasAdditionalTransferMechanisms
{
    /**
     * Using string offsets ensures that insertion order is preserved in case
     * an entry is added multiple times. This is important for composability.
     *
     * @var array<string, C\Transfer\TransferMechanism> (mechanism-name => mechanism)
     */
    protected array $transfer_mechanisms = [];

    /**
     * Implements {@see C\Transfer\HasAdditionalTransferMechanism::withAdditionalTransferMechanism()}
     */
    public function withAdditionalTransferMechanism(C\Transfer\TransferMechanism ...$transfer_mechanisms): static
    {
        $clone = clone $this;
        foreach ($transfer_mechanisms as $transfer_mechanism) {
            $clone->transfer_mechanisms[$transfer_mechanism->name] = $transfer_mechanism;
        }
        return $clone;
    }

    /**
     * Sets the transfer mechanisms of this instance. This should be used only
     * inside the constructor to initialise the property, as it overwrites the
     * property otherwise.
     *
     * @param C\Transfer\TransferMechanism[] $transfer_mechanisms
     */
    public function setTransferMechanisms(array $transfer_mechanisms): void
    {
        foreach ($transfer_mechanisms as $transfer_mechanism) {
            $this->transfer_mechanisms[$transfer_mechanism->name] = $transfer_mechanism;
        }
    }

    /** @return array<string, C\Transfer\TransferMechanism> */
    public function getTransferMechanisms(): array
    {
        return $this->transfer_mechanisms;
    }
}
