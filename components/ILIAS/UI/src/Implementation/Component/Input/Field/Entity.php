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

use Closure;
use ILIAS\Language\Language;
use ILIAS\UI\Component as C;
use ILIAS\Refinery\Constraint;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Refinery\Factory as Refinery;

class Entity extends HasDynamicInputs implements C\Input\Field\Entity
{
    public function __construct(
        Language $language,
        DataFactory $data_factory,
        Refinery $refinery,
        C\Input\Container\Form\FormInput $dynamic_input_template,
        protected C\Entity\EntityRetrieval $entity_retrieval,
        string $label,
        ?string $byline = null
    ) {
        parent::__construct(
            $language,
            $data_factory,
            $refinery,
            $dynamic_input_template,
            $label,
            $byline
        );
    }

    public function getEntityRetrieval(): C\Entity\EntityRetrieval
    {
        return $this->entity_retrieval;
    }

    public function getUpdateOnLoadCode(): Closure
    {
        return static fn($id) => '';
    }

    protected function getConstraintForRequirement(): ?Constraint
    {
        if ($this->requirement_constraint !== null) {
            return $this->requirement_constraint;
        }

        return $this->refinery->custom()->constraint(
            static fn($value) => is_array($value) && 1 <= count($value),
            sprintf($this->language->txt('not_greater_than_or_equal'), 1)
        );
    }

    protected function isClientSideValueOk($value): bool
    {
        if (!is_array($value)) {
            return false;
        }
        foreach ($value as $_value) {
            if (!is_string($_value) && !is_int($_value)) {
                return false;
            }
        }

        return parent::isClientSideValueOk($value);
    }
}
