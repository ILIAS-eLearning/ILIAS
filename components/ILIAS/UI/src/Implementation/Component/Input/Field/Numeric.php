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

namespace ILIAS\UI\Implementation\Component\Input\Field;

use ILIAS\Data\Factory as DataFactory;
use ILIAS\Refinery\Transformation;
use ILIAS\UI\Component as C;
use ILIAS\Refinery\Constraint;
use Closure;
use ILIAS\Refinery\ConstraintViolationException;

/**
 * This implements the numeric input.
 */
class Numeric extends FormInput implements C\Input\Field\Numeric
{
    protected int|float $stepsize = 1;

    public function __construct(
        DataFactory $data_factory,
        \ILIAS\Refinery\Factory $refinery,
        string $label,
        ?string $byline
    ) {
        parent::__construct($data_factory, $refinery, $label, $byline);
        $this->setAdditionalTransformation($this->getStandardTrafoInt());
    }

    /**
     * @inheritdoc
     */
    protected function isClientSideValueOk($value): bool
    {
        return is_numeric($value) || $value === "";
    }

    /**
     * @inheritdoc
     */
    protected function getConstraintForRequirement(): ?Constraint
    {
        if ($this->requirement_constraint !== null) {
            return $this->requirement_constraint;
        }

        return $this->refinery->numeric()->isNumeric();
    }

    /**
     * @inheritdoc
     */
    public function getUpdateOnLoadCode(): Closure
    {
        return fn($id) => "$('#$id').on('input', function(event) {
				il.UI.input.onFieldUpdate(event, '$id', $('#$id').val());
			});
			il.UI.input.onFieldUpdate(event, '$id', $('#$id').val());";
    }

    /**
     * @inheritdoc
     */
    public function isComplex(): bool
    {
        return false;
    }

    public function withStepSize(int|float $stepsize = 1): self
    {
        $clone = clone $this;
        $clone->stepsize = $stepsize;

        if (is_int($stepsize) && is_float($this->stepsize)) {
            $clone->operations = [$this->getStandardTrafoInt()];
        }

        if (is_float($stepsize) && is_int($this->stepsize)) {
            $clone->operations = [$this->getStandardTrafoFloat()];
        }
        return $clone;
    }

    private function getStandardTrafoInt(): Transformation
    {
        return $this->refinery->byTrying([
            $this->refinery->kindlyTo()->null(),
            $this->refinery->kindlyTo()->int()
        ]);
    }
    private function getStandardTrafoFloat(): Transformation
    {
        return $this->refinery->byTrying([
            $this->refinery->kindlyTo()->null(),
            $this->refinery->kindlyTo()->float()
        ]);
    }

    public function getStepSize(): int|float
    {
        return $this->stepsize;
    }

}
