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

namespace ILIAS\Questions\Presentation\Layout;

use ILIAS\Refinery\Transformation;
use ILIAS\UI\Component\Input\Field\Section;

/**
 * This is a InputBuilder for multipart forms. It allows you to carry the necessary
 * information from one page of the form to the next in the session.
 */
class InputsBuilderSession implements InputsBuilder
{
    private ?string $carry = null;

    /**
     * @param Transformation $to_inputs This Transformation receives the
     * value previously set by `self::withCarry()` if it is available, otherwise
     * the value will be null.
     */
    public function __construct(
        private readonly string $storage_key,
        private readonly Transformation $to_inputs
    ) {
    }

    public function withCarry(
        string $carry
    ): self {
        $clone = clone $this;
        $clone->carry = $carry;
        return $clone;
    }

    public function persistCarry(): void
    {
        if ($this->carry === null) {
            $this->loadCarryFromSessionAndClear();
        }
        \ilSession::set($this->storage_key, $this->carry);
    }

    public function resetCarry(): void
    {
        \ilSession::clear($this->storage_key);
    }

    public function retrieveCarry(
        Transformation $transformation
    ): mixed {
        if ($this->carry === null) {
            $this->loadCarryFromSessionAndClear();
        }

        return $transformation->transform($this->carry);
    }

    public function getInputs(): Section
    {
        if ($this->carry === null) {
            $this->loadCarryFromSessionAndClear();
        }
        return $this->to_inputs->transform($this->carry);
    }

    private function loadCarryFromSessionAndClear(): void
    {
        $this->carry = \ilSession::get($this->storage_key);
        \ilSession::clear($this->storage_key);
    }
}
