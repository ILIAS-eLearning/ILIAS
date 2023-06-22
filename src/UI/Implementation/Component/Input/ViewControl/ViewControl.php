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

namespace ILIAS\UI\Implementation\Component\Input\ViewControl;

use ILIAS\UI\Component\Input\ViewControl as VCInterface;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Implementation\Component\Triggerer;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;
use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;
use ILIAS\UI\Component\Signal;
use ILIAS\Refinery\Transformation;
use ILIAS\Refinery\Constraint;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\UI\Implementation\Component\Input\NameSource;
use ILIAS\UI\Implementation\Component\Input\Field\InputInternal;
use ILIAS\UI\Implementation\Component\Input\InputData;
use ILIAS\Data\Result;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\UI\Implementation\Component\Input\Input;

abstract class ViewControl extends Input implements VCInterface\ViewControl, InputInternal
{
    use ComponentHelper;
    use JavaScriptBindable;
    use Triggerer;

    protected Signal $change_signal;
    protected $value;
    protected ?Result $content = null;
    protected ?string $name = null;
    protected bool $is_disabled = false;

    /**
     * @var Transformation[]
     */
    protected array $operations = [];

    public function withOnChange(Signal $change_signal): self
    {
        $clone = clone $this;
        $clone->change_signal = $change_signal;
        return $clone;
    }

    public function getOnChangeSignal(): ?Signal
    {
        return $this->change_signal ?? null;
    }

    abstract protected function getDefaultValue(): string;

    protected function getConstraintForRequirement(): ?Constraint
    {
        return null;
    }
}
