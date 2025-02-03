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

namespace ILIAS\AdvancedMetaData\Data\FieldDefinition\TypeSpecificData\Select;

use ILIAS\AdvancedMetaData\Data\FieldDefinition\TypeSpecificData\TypeSpecificDataImplementation;
use ILIAS\AdvancedMetaData\Data\FieldDefinition\Type;

class SelectSpecificDataImplementation extends TypeSpecificDataImplementation implements SelectSpecificData
{
    /**
     * @var Option[]
     */
    protected array $options;

    public function __construct(
        ?int $field_id = null,
        Option ...$options
    ) {
        parent::__construct($field_id);

        usort($options, function (Option $a, Option $b) {
            return $a->getPosition() <=> $b->getPosition();
        });
        $this->options = $options;
    }

    public function isTypeSupported(Type $type): bool
    {
        return $type === Type::SELECT || $type === Type::SELECT_MULTI;
    }

    protected function getSubData(): \Generator
    {
        yield from $this->getOptions();
    }

    public function hasOptions(): bool
    {
        return !empty($this->options);
    }

    public function getOptions(): \Generator
    {
        yield from $this->options;
    }

    public function getOption(int $option_id): ?Option
    {
        foreach ($this->options as $option) {
            if ($option->optionID() === $option_id) {
                return $option;
            }
        }
        return null;
    }

    public function removeOption(int $option_id): void
    {
        foreach ($this->options as $key => $option) {
            if ($option->optionID() !== $option_id) {
                continue;
            }
            unset($this->options[$key]);
            $this->markAsChanged();
        }
    }

    public function addOption(): Option
    {
        $option = new OptionImplementation(0);
        $this->options[] = $option;
        return $option;
    }
}
