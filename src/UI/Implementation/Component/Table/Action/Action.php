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

namespace ILIAS\UI\Implementation\Component\Table\Action;

use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Component\Table\Action as I;
use ILIAS\UI\Component\Signal;
use ILIAS\Data\URI;

abstract class Action implements I\Action
{
    use ComponentHelper;

    protected Signal|URI $target;

    public function __construct(
        protected string $label,
        protected string $parameter_name,
        Signal|URI $target
    ) {
        $check = [$target];
        $valid = [Signal::class, URI::class];
        $this->checkArgListElements("target", $check, $valid);
        $this->target = $target;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getParameterName(): string
    {
        return $this->parameter_name;
    }

    /*
     * @inheritdoc
     */
    public function getTarget(): Signal|URI
    {
        return $this->target;
    }

    public function withRowId(string $value): self
    {
        $clone = clone $this;

        $target = $clone->getTarget();
        $param = $clone->getParameterName();

        if ($target instanceof Signal) {
            $target->addOption($param, $value);
        }
        if ($target instanceof URI) {
            $target = $target->withParameter($param, $value);
        }
        $clone->target = $target;
        return $clone;
    }
}
