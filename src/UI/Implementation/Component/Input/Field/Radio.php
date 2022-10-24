<?php

declare(strict_types=1);

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

namespace ILIAS\UI\Implementation\Component\Input\Field;

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component\Input\InputData;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;
use ILIAS\UI\Implementation\Component\Triggerer;
use ILIAS\Refinery\Constraint;
use LogicException;
use Closure;

/**
 * This implements the radio input.
 */
class Radio extends Input implements C\Input\Field\Radio
{
    use JavaScriptBindable;
    use Triggerer;

    /**
     * @var array <string,string> {$value => $label}
     */
    protected array $options = [];

    /**
     * @var array <string,array> {$option_value => $bylines}
     */
    protected array $bylines = [];

    /**
     * @inheritdoc
     */
    protected function isClientSideValueOk($value): bool
    {
        return ($value === '' || array_key_exists($value, $this->getOptions()));
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
    public function withOption(string $value, string $label, string $byline = null): C\Input\Field\Radio
    {
        $clone = clone $this;
        $clone->options[$value] = $label;
        if (!is_null($byline)) {
            $clone->bylines[$value] = $byline;
        }
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getOptions(): array
    {
        return $this->options;
    }


    public function getBylineFor(string $value): ?string
    {
        if (!array_key_exists($value, $this->bylines)) {
            return null;
        }
        return $this->bylines[$value];
    }

    /**
     * @inheritdoc
     */
    public function withInput(InputData $input): C\Input\Field\Input
    {
        if ($this->getName() === null) {
            throw new LogicException("Can only collect if input has a name.");
        }
        if (!$this->isDisabled()) {
            $value = $input->getOr($this->getName(), "");
            $clone = $this->withValue($value);
        } else {
            $value = $this->getValue();
            $clone = $this;
        }

        $clone->content = $this->applyOperationsTo($value);
        if ($clone->content->isError()) {
            return $clone->withError("" . $clone->content->error());
        }

        $clone->content = $this->applyOperationsTo($value);

        if ($clone->getError()) {
            $clone->content = $clone->data_factory->error($clone->getError());
        }

        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getUpdateOnLoadCode(): Closure
    {
        return fn ($id) => "$('#$id').on('input', function(event) {
				il.UI.input.onFieldUpdate(event, '$id', $('#$id input:checked').val());
			});
			il.UI.input.onFieldUpdate(event, '$id', $('#$id input:checked').val());";
    }
}
