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

namespace ILIAS\UI\Implementation\Component\Input\ViewControl;

use ILIAS\UI\Component\Input\Group;
use ILIAS\Refinery\Transformation;
use ILIAS\Data\Result;
use ILIAS\UI\Implementation\Component\Input\InputData;
use ILIAS\UI\Implementation\Component\Input\NameSource;
use ILIAS\UI\Implementation\Component\Input\Input;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
trait GroupDecorator
{
    protected Group $input_group;

    /**
     * @inheritDoc
     */
    public function getValue()
    {
        return $this->input_group->getValue();
    }

    /**
     * @inheritDoc
     */
    public function withValue($value): Input
    {
        $clone = clone $this;
        $clone->setInputGroup($clone->getInputGroup()->withValue($value));
        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function withAdditionalTransformation(Transformation $trafo): Input
    {
        $clone = clone $this;
        $clone->setInputGroup($clone->getInputGroup()->withAdditionalTransformation($trafo));
        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function withNameFrom(NameSource $source, ?string $parent_name = null): Input
    {
        $clone = clone $this;
        $clone->setInputGroup($clone->getInputGroup()->withNameFrom($source, $parent_name));
        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function withInput(InputData $input): Input
    {
        $clone = clone $this;
        $clone->setInputGroup($clone->getInputGroup()->withInput($input));
        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function getContent(): Result
    {
        return $this->input_group->getContent();
    }

    /**
     * @inheritDoc
     */
    public function isClientSideValueOk($value): bool
    {
        return $this->input_group->isClientSideValueOk($value);
    }

    public function getInputGroup(): Group
    {
        return $this->input_group;
    }

    protected function setInputGroup(Group $input_group): void
    {
        $this->input_group = $input_group;
    }
}
