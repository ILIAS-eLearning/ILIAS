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

namespace ILIAS\UI\Implementation\Component\Input\Container;

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component as CI;
use ILIAS\UI\Implementation\Component\Input\NameSource;
use ILIAS\UI\Component\Input\InputData;
use ILIAS\Refinery\Transformation;
use Psr\Http\Message\ServerRequestInterface;
use ILIAS\Data\Result;

/**
 * This implements commonalities between all forms.
 */
abstract class Container implements C\Input\Container\Container
{
    use CI\ComponentHelper;

    protected C\Input\Group $input_group;
    protected ?Transformation $transformation = null;
    protected ?string $error = null;
    protected ?string $dedicated_name = null;
    protected ?Result $result = null;

    /**
     * For the implementation of NameSource.
     */
    public function __construct(protected NameSource $name_source)
    {
    }

    /**
     * @inheritdoc
     */
    public function getInputs(): array
    {
        return $this->getInputGroup()->getInputs();
    }

    /**
     * @inheritdoc
     */
    public function withRequest(ServerRequestInterface $request): self
    {
        return $this->withInput(
            $this->extractRequestData($request)
        );
    }

    public function withInput(InputData $input_data): self
    {
        $clone = clone $this;
        $clone->input_group = $this->getInputGroup()->withInput($input_data)->withNameFrom($this->name_source->withReset());
        $clone->result = $clone->input_group->getContent();

        if (!$clone->result->isok()) {
            $clone->setError($clone->result->error());
        }

        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function withAdditionalTransformation(Transformation $trafo): self
    {
        $clone = clone $this;
        $clone->input_group = $this->getInputGroup()->withAdditionalTransformation($trafo);

        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getError(): ?string
    {
        return $this->error;
    }

    protected function setError(string $error): void
    {
        $this->error = $error;
    }

    /**
     * @inheritdoc
     */
    public function getData()
    {
        if (null !== $this->result && $this->result->isOK()) {
            return $this->result->value();
        }
        return null;
    }

    public function getDedicatedName(): ?string
    {
        return $this->dedicated_name;
    }

    public function withDedicatedName(string $dedicated_name): self
    {
        $clone = clone $this;
        $clone->dedicated_name = $dedicated_name;
        $clone->input_group = $clone->input_group
            ->withDedicatedName($dedicated_name)
            ->withNameFrom($clone->name_source->withReset());
        return $clone;
    }

    public function getInputGroup(): C\Input\Group
    {
        return $this->input_group;
    }

    /**
     * This setter should be used in the constructor only, to initialize the group input property.
     */
    protected function setInputGroup(C\Input\Group $input_group): void
    {
        $this->input_group = $input_group->withNameFrom($this->name_source->withReset());
    }

    /**
     * Returns the extracted data from the given server request. This methods has been introduced
     * since different containers may allow different request methods.
     */
    abstract protected function extractRequestData(ServerRequestInterface $request): InputData;

    public function getSubComponents(): array
    {
        return $this->getInputs();
    }
}
