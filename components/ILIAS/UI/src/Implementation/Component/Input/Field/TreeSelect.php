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

namespace ILIAS\UI\Implementation\Component\Input\Field;

use ILIAS\UI\Component as C;
use ILIAS\Language\Language;
use ILIAS\Refinery\Constraint;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Data\Factory as DataFactory;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class TreeSelect extends HasDynamicInputs implements C\Input\Field\TreeSelect
{
    public function __construct(
        Language $language,
        DataFactory $data_factory,
        Refinery $refinery,
        C\Input\Container\Form\FormInput $dynamic_input_template,
        protected C\Input\Field\Node\NodeRetrieval $node_retrieval,
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

    public function getNodeRetrieval(): C\Input\Field\Node\NodeRetrieval
    {
        return $this->node_retrieval;
    }

    public function getUpdateOnLoadCode(): \Closure
    {
        return static fn($id) => '';
    }

    protected function getConstraintForRequirement(): ?Constraint
    {
        if ($this->requirement_constraint !== null) {
            return $this->requirement_constraint;
        }

        return $this->refinery->logical()->logicalOr([
            $this->refinery->numeric()->isNumeric(),
            $this->refinery->string()->hasMinLength(1),
        ])->withProblemBuilder(fn($txt, $value) => sprintf($txt('not_greater_than'), 0));
    }

    protected function isClientSideValueOk($value): bool
    {
        if (!is_string($value) && !is_int($value)) {
            return false;
        }
        return parent::isClientSideValueOk($value);
    }
}
