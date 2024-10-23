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

namespace ILIAS\Poll\Image\Repository\Element;

use ILIAS\Poll\Image\I\Repository\Element\HandlerInterface as ilPollImageRepositoryElementInterface;
use ILIAS\Poll\Image\I\Repository\Element\Wrapper\FactoryInterface as ilPollImageRepositoryElementWrapperFactoryInterface;
use ILIAS\Poll\Image\I\Repository\Element\Wrapper\IRSS\HandlerInterface as ilPollImageRepositoryElmentIRSSWrapperInterface;
use ILIAS\Poll\Image\I\Repository\Key\HandlerInterface as ilPollImageRepositoryKeyInterface;
use ILIAS\Poll\Image\I\Repository\Values\HandlerInterface as ilPollImageRepositoryValuesInterface;

class Handler implements ilPollImageRepositoryElementInterface
{
    protected ilPollImageRepositoryElementWrapperFactoryInterface $wrapper;
    protected ilPollImageRepositoryValuesInterface $values;
    protected ilPollImageRepositoryKeyInterface $key;

    public function __construct(
        ilPollImageRepositoryElementWrapperFactoryInterface $wrapper
    ) {
        $this->wrapper = $wrapper;
    }

    public function withKey(
        ilPollImageRepositoryKeyInterface $key
    ): ilPollImageRepositoryElementInterface {
        $clone = clone $this;
        $clone->key = $key;
        return $clone;
    }

    public function withValues(
        ilPollImageRepositoryValuesInterface $values
    ): ilPollImageRepositoryElementInterface {
        $clone = clone $this;
        $clone->values = $values;
        return $clone;
    }

    public function getKey(): ilPollImageRepositoryKeyInterface
    {
        return $this->key;
    }

    public function getValues(): ilPollImageRepositoryValuesInterface
    {
        return $this->values;
    }

    public function getIRSS(): ilPollImageRepositoryElmentIRSSWrapperInterface
    {
        return $this->wrapper->irss()->handler()->withResourceIdSerialized($this->getValues()->getResourceIdSerialized());
    }

    public function isValid(): bool
    {
        return isset($this->key) and $this->getKey()->isValid();
    }
}
