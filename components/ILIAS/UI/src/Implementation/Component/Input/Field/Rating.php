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

/**
 * This implements the Rating Input
 */
class Rating extends FormInput implements C\Input\Field\Rating
{
    protected ?string $text = null;

    /**
     * @var string[]
     */
    protected array $option_labels = [];

    public function __construct(
        DataFactory $data_factory,
        \ILIAS\Refinery\Factory $refinery,
        string $label,
        ?string $byline
    ) {
        parent::__construct($data_factory, $refinery, $label, $byline);
    }

    public function withQuestionText(?string $text): self
    {
        $clone = clone $this;
        $clone->text = $text;
        return $clone;
    }
    public function getQuestionText(): ?string
    {
        return $this->text;
    }

    public function withOptionLabels(string ...$option_labels): self
    {
        $clone = clone $this;
        $clone->option_labels = $option_labels;
        return $clone;
    }

    /**
     * @return string[]
     */
    public function getOptionLabels(): array
    {
        return $this->option_labels;
    }


    /**
     * @inheritdoc
     */
    public function isClientSideValueOk($value): bool
    {
        return is_numeric($value) || $value === "" || $value === null;
    }

    /**
     * @inheritdoc
     */
    protected function getConstraintForRequirement(): ?Constraint
    {
        if ($this->requirement_constraint !== null) {
            return $this->requirement_constraint;
        }
        return $this->refinery->int()->isGreaterThan(0);
    }

    /**
     * @inheritdoc
     */
    public function getUpdateOnLoadCode(): \Closure
    {
        return fn($id) => "$('#$id').on('input', function(event) {
                il.UI.input.onFieldUpdate(event, '$id', $('#$id').val());
            });
            il.UI.input.onFieldUpdate(event, '$id', $('#$id').val());";
    }
}
