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

use ILIAS\Data\Result;
use ILIAS\UI\Component as C;
use ILIAS\UI\Component\Signal;
use ILIAS\UI\Implementation\Component\Input\GroupInternal;
use ILIAS\UI\Implementation\Component\Input\Group as GroupInternals;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\Data\Factory as DataFactory;
use ilLanguage;
use ILIAS\Refinery\Constraint;
use Closure;
use ILIAS\Data\Result\Ok;
use Generator;
use ILIAS\UI\Implementation\Component\Input\NameSource;
use ILIAS\UI\Implementation\Component\Input\InputData;

/**
 * This implements the group input.
 */
class Group extends FormInput implements C\Input\Field\Group, GroupInternal
{
    use GroupInternals;
    protected ilLanguage $lng;

    /**
     * @param \ILIAS\UI\Component\Input\Input[] $inputs
     */
    public function __construct(
        DataFactory $data_factory,
        \ILIAS\Refinery\Factory $refinery,
        ilLanguage $lng,
        array $inputs,
        string $label,
        ?string $byline = null
    ) {
        parent::__construct($data_factory, $refinery, $label, $byline);
        $this->checkInputListElements('inputs', $inputs, [C\Input\Container\Form\FormInput::class]);
        $this->setInputs($inputs);
        $this->lng = $lng;
    }

    public function withDisabled(bool $is_disabled): self
    {
        $clone = parent::withDisabled($is_disabled);
        $clone->setInputs(array_map(fn ($i) => $i->withDisabled($is_disabled), $this->getInputs()));
        return $clone;
    }

    public function withRequired(bool $is_required, ?Constraint $requirement_constraint = null): self
    {
        $clone = parent::withRequired($is_required, $requirement_constraint);
        $clone->setInputs(array_map(fn ($i) => $i->withRequired($is_required, $requirement_constraint), $this->getInputs()));
        return $clone;
    }

    public function isRequired(): bool
    {
        if ($this->is_required) {
            return true;
        }
        foreach ($this->getInputs() as $input) {
            if ($input->isRequired()) {
                return true;
            }
        }
        return false;
    }

    public function withOnUpdate(Signal $signal): self
    {
        $clone = parent::withOnUpdate($signal);
        $clone->setInputs(array_map(fn ($i) => $i->withOnUpdate($signal), $this->getInputs()));
        return $clone;
    }

    /**
     * @inheritdoc
     */
    protected function getConstraintForRequirement(): ?Constraint
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    public function getUpdateOnLoadCode(): Closure
    {
        return function () {
            /*
             * Currently, there is no use case for Group here. The single Inputs
             * within the Group are responsible for handling getUpdateOnLoadCode().
             */
        };
    }

    /**
     * @inheritdoc
     */
    public function withNameFrom(NameSource $source, ?string $parent_name = null): self
    {
        /** @var $clone self */
        $clone = parent::withNameFrom($source, $parent_name);
        $clone->setInputs($this->nameInputs($source, $clone->getName()));
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getContent(): Result
    {
        if (empty($this->getInputs())) {
            return new Ok([]);
        }
        return parent::getContent();
    }

    /**
     * @inheritDoc
     */
    protected function setError(string $error): void
    {
        $this->error = $error;
    }

    protected function getLanguage(): \ilLanguage
    {
        return $this->lng;
    }

    protected function getDataFactory(): DataFactory
    {
        return $this->data_factory;
    }
}
