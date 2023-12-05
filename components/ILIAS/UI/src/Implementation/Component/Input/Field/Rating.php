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

use ILIAS\UI\Component as C;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Refinery\Constraint;
use ILIAS\Refinery\Custom\Transformation;
use ILIAS\Data\FiveStarRatingScale;

class Rating extends FormInput implements C\Input\Field\Rating
{
    protected ?string $text = null;
    protected ?float $current_average = null;

    public function __construct(
        DataFactory $data_factory,
        \ILIAS\Refinery\Factory $refinery,
        string $label,
        ?string $byline
    ) {
        parent::__construct($data_factory, $refinery, $label, $byline);
        $this->setAdditionalTransformation($this->getFiveStarRatingScaleTransformation());
    }

    protected function getFiveStarRatingScaleTransformation(): Transformation
    {
        return $this->refinery->custom()->transformation(
            static function ($v): ?FiveStarRatingScale {
                if(is_null($v) || $v instanceof FiveStarRatingScale) {
                    return $v;
                }
                return FiveStarRatingScale::from((int)$v);
            }
        );
    }

    public function withAdditionalText(?string $text): static
    {
        $clone = clone $this;
        $clone->text = $text;
        return $clone;
    }

    public function getAdditionalText(): ?string
    {
        return $this->text;
    }

    public function withValue($value): self
    {
        if(! $value instanceof FiveStarRatingScale) {
            $value = $this->getFiveStarRatingScaleTransformation()->transform($value);
        }
        return parent::withValue($value);
    }

    public function isClientSideValueOk($value): bool
    {
        return is_null($value) || is_numeric($value) || $value instanceof FiveStarRatingScale;
    }

    protected function getConstraintForRequirement(): ?Constraint
    {
        if ($this->requirement_constraint !== null) {
            return $this->requirement_constraint;
        }
        return $this->refinery->custom()->constraint(
            static fn($v) => $v instanceof FiveStarRatingScale && $v->value > 0,
            'no rating given'
        );
    }

    public function getUpdateOnLoadCode(): \Closure
    {
        return fn($id) => "$('#$id').on('input', function(event) {
                il.UI.input.onFieldUpdate(event, '$id', $('#$id').val());
            });
            il.UI.input.onFieldUpdate(event, '$id', $('#$id').val());";
    }

    public function withCurrentAverage(?float $current_average): static
    {
        if($current_average < 0 || $current_average > 5) {
            throw new \InvalidArgumentException('current_average must be between 0 and 5');
        }
        $clone = clone $this;
        $clone->current_average = $current_average;
        return $clone;
    }

    public function getCurrentAverage(): ?float
    {
        return $this->current_average;
    }

}
